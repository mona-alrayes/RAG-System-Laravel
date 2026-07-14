<?php

namespace App\Services\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Services\Ai\AiServiceClient;
use App\Services\Documents\Validation\ProcessDocumentResponseValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Coordinates the complete document-processing workflow.
 *
 * This service is responsible for:
 *
 * - Checking whether the external AI service is enabled.
 * - Moving the document to the processing state.
 * - Sending the document to FastAPI.
 * - Validating the response returned by FastAPI.
 * - Preparing document chunks for database persistence.
 * - Replacing old chunks with the newly processed chunks.
 * - Marking the document as completed.
 *
 * The service does not extract text, generate embeddings, or communicate
 * directly with Qdrant. Those responsibilities belong to the external
 * AI service and the AiServiceClient.
 */
final class DocumentProcessingService
{
    /**
     * Create a new document-processing service instance.
     */
    public function __construct(
        private readonly AiServiceClient $aiServiceClient,
        private readonly ProcessDocumentResponseValidator $responseValidator,
    ) {}

    /**
     * Process a document using the external FastAPI service.
     *
     * Processing lifecycle:
     *
     * pending/failed/completed
     *      ↓
     * processing
     *      ↓
     * completed
     *
     * If an exception occurs, it is intentionally allowed to propagate
     * to the caller. When this service is executed from a queued job,
     * the job should handle retries and mark the document as failed only
     * after all retry attempts have been exhausted.
     *
     * @throws \Throwable
     */
    public function process(Document $document): void
    {
        /*
         * During the Laravel-only development stage, FastAPI may be
         * disabled. In this case, processing is skipped and the document
         * remains in its current status, usually pending.
         */
        if (! $this->isAiServiceEnabled()) {
            Log::info(
                'Document processing skipped because AI service is disabled.',
                [
                    'document_id' => $document->getKey(),
                ]
            );

            return;
        }

        /*
         * The processing status is written before calling FastAPI so the
         * application can show that the document is currently being
         * handled by the background-processing system.
         */
        $document = $this->markAsProcessing($document);

        /*
         * Send the original document file to FastAPI.
         *
         * AiServiceClient is responsible for opening the stored file,
         * creating the multipart request, handling HTTP errors, and
         * returning the decoded JSON response as an array.
         */
        $response = $this->aiServiceClient
            ->processDocument($document);

        /*
         * Never trust an external service response without validation,
         * even when the service returned a successful HTTP status code.
         */
        $validatedResponse = $this->responseValidator
            ->validate(
                document: $document,
                response: $response
            );

        /*
         * Keep only the fields that Laravel actually persists.
         *
         * This prevents unexpected or additional FastAPI fields from
         * being passed directly into Eloquent mass-assignment operations.
         */
        $chunks = $this->prepareChunks(
            $validatedResponse['chunks']
        );

        /*
         * Persist all database changes atomically.
         *
         * Old chunks are not deleted until FastAPI has completed
         * successfully and its response has passed validation.
         */
        $this->persistProcessingResult(
            document: $document,
            totalPages: $validatedResponse['total_pages'],
            qdrantCollection: $validatedResponse['qdrant_collection'],
            chunks: $chunks
        );
    }

    /**
     * Determine whether the external AI service is enabled.
     */
    private function isAiServiceEnabled(): bool
    {
        return (bool) config(
            'services.ai_services.enabled',
            false
        );
    }

    /**
     * Move the document into the processing state.
     *
     * This operation is idempotent:
     *
     * - A pending document is moved to processing.
     * - A failed document may start a new processing cycle.
     * - A completed document may be reprocessed when explicitly allowed by
     *   the caller's workflow.
     * - A document already in processing remains in processing so that queue
     *   retry attempts can continue after temporary failures.
     *
     * Concurrent job dispatching is prevented primarily by the job's
     * ShouldBeUnique implementation. The database lock protects the state
     * transition itself from concurrent database updates.
     *
     * @throws RuntimeException
     */
    private function markAsProcessing(
        Document $document
    ): Document {
        return DB::transaction(
            function () use ($document): Document {
                $lockedDocument = Document::query()
                    ->lockForUpdate()
                    ->findOrFail($document->getKey());

                /*
             * A retry attempt receives a document that is already marked
             * as processing. This is valid and must not produce another
             * exception, otherwise all retries would fail immediately.
             */
                if (
                    $lockedDocument->status ===
                    DocumentStatus::Processing
                ) {
                    return $lockedDocument;
                }

                if (
                    ! $this->canStartProcessing(
                        $lockedDocument
                    )
                ) {
                    throw new RuntimeException(
                        sprintf(
                            'The document cannot be processed from status [%s].',
                            $this->getStatusValue(
                                $lockedDocument
                            )
                        )
                    );
                }

                $lockedDocument->forceFill([
                    'status' => DocumentStatus::Processing,
                    'failure_reason' => null,
                    'processed_at' => null,
                ])->save();

                return $lockedDocument->fresh();
            }
        );
    }

    /**
     * Determine whether processing may start from the current status.
     *
     * Supported cases:
     *
     * - Pending documents may be processed for the first time.
     * - Failed documents may be retried.
     * - Completed documents may be reprocessed.
     */
    private function canStartProcessing(
        Document $document
    ): bool {
        return in_array(
            $document->status,
            [
                DocumentStatus::Pending,
                DocumentStatus::Failed,
                DocumentStatus::Completed,
            ],
            true
        );
    }

    /**
     * Prepare validated chunks for database persistence.
     *
     * Only explicitly approved fields are copied. Text identifiers are
     * trimmed to prevent accidental leading or trailing whitespace.
     *
     * @param  array<int, array{
     *     chunk_index: int,
     *     content: string,
     *     page_number?: int|null,
     *     vector_id: string,
     *     metadata?: array<string, mixed>|null
     * }>  $chunks
     * @return array<int, array{
     *     chunk_index: int,
     *     content: string,
     *     page_number: int|null,
     *     vector_id: string,
     *     metadata: array<string, mixed>|null
     * }>
     */
    private function prepareChunks(array $chunks): array
    {
        return collect($chunks)
            ->map(
                static function (array $chunk): array {
                    return [
                        'chunk_index' => $chunk['chunk_index'],

                        'content' => trim(
                            $chunk['content']
                        ),

                        'page_number' => $chunk['page_number'] ?? null,

                        'vector_id' => trim(
                            $chunk['vector_id']
                        ),

                        'metadata' => $chunk['metadata'] ?? null,
                    ];
                }
            )
            ->values()
            ->all();
    }

    /**
     * Store the successfully processed document result.
     *
     * The operation is executed inside one database transaction:
     *
     * 1. Lock the document row.
     * 2. Confirm that it is still in processing status.
     * 3. Delete previously stored chunks.
     * 4. Insert newly processed chunks.
     * 5. Mark the document as completed.
     *
     * If any database operation fails, all database changes are rolled
     * back automatically, including deletion of the previous chunks.
     *
     * @param  array<int, array{
     *     chunk_index: int,
     *     content: string,
     *     page_number: int|null,
     *     vector_id: string,
     *     metadata: array<string, mixed>|null
     * }>  $chunks
     *
     * @throws RuntimeException
     */
    private function persistProcessingResult(
        Document $document,
        int $totalPages,
        string $qdrantCollection,
        array $chunks
    ): void {
        DB::transaction(
            function () use (
                $document,
                $totalPages,
                $qdrantCollection,
                $chunks
            ): void {
                $lockedDocument = Document::query()
                    ->lockForUpdate()
                    ->findOrFail($document->getKey());

                if (
                    $lockedDocument->status !==
                    DocumentStatus::Processing
                ) {
                    throw new RuntimeException(
                        sprintf(
                            'Cannot save the processing result because '
                                .'document [%d] is not in processing status.',
                            $lockedDocument->getKey()
                        )
                    );
                }

                /*
                 * Previous chunks are deleted only after the external
                 * processing operation and response validation succeed.
                 *
                 * Because deletion and insertion occur inside the same
                 * transaction, the old chunks are restored automatically
                 * if inserting the new chunks fails.
                 */
                $lockedDocument
                    ->chunks()
                    ->delete();

                $lockedDocument
                    ->chunks()
                    ->createMany($chunks);

                $lockedDocument->forceFill([
                    'status' => DocumentStatus::Completed,

                    'failure_reason' => null,

                    'total_pages' => $totalPages,

                    'total_chunks' => count($chunks),

                    'qdrant_collection' => trim(
                        $qdrantCollection
                    ),

                    'processed_at' => now(),
                ])->save();

                Log::info(
                    'Document processing completed successfully.',
                    [
                        'document_id' => $lockedDocument->getKey(),

                        'total_pages' => $totalPages,

                        'total_chunks' => count($chunks),

                        'qdrant_collection' => trim($qdrantCollection),
                    ]
                );
            }
        );
    }

    /**
     * Return the scalar value of the current document status.
     *
     * This helper supports both enum-casted and plain string status
     * attributes, making log and exception messages more defensive.
     */
    private function getStatusValue(
        Document $document
    ): string {
        $status = $document->status;

        if ($status instanceof DocumentStatus) {
            return $status->value;
        }

        return (string) $status;
    }
}
