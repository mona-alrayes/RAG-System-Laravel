<?php

namespace App\Jobs;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Services\Documents\DocumentProcessingService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Process an uploaded document asynchronously.
 *
 * This job coordinates queued execution only. The actual document
 * processing workflow is delegated to DocumentProcessingService.
 *
 * Responsibilities:
 *
 * - Prevent duplicate queued jobs for the same document.
 * - Execute document processing outside the HTTP request lifecycle.
 * - Retry temporary processing failures using progressive backoff.
 * - Mark the document as failed only after all retry attempts are exhausted.
 *
 * The document remains in the "processing" state between retry attempts.
 * This prevents temporary network or FastAPI failures from being exposed
 * as permanent failures before Laravel finishes all configured retries.
 */
#[UniqueFor(3600)]
final class ProcessDocument implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Maximum number of execution attempts.
     *
     * The first execution counts as one attempt. Therefore, this value
     * allows the initial attempt followed by two retry attempts.
     */
    public int $tries = 3;

    /**
     * Maximum execution time for a single attempt, in seconds.
     *
     * The queue connection's retry_after value must be greater than this
     * timeout to prevent the same job from being executed simultaneously
     * by multiple workers.
     */
    public int $timeout = 600;

    /**
     * Permanently fail the job when a worker timeout occurs.
     *
     * Without this option, timeout handling may depend on the queue worker
     * configuration and the job may not immediately enter the failed state.
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new document-processing job.
     *
     * Only the document ID is serialized into the queue payload. The latest
     * document state is retrieved from the database when the job executes.
     */
    public function __construct(
        public readonly int $documentId,
    ) {
        $this->onQueue('documents');
    }

    /**
     * Return the unique lock identifier for this document.
     *
     * Laravel will reject dispatching another ProcessDocument job with
     * the same document ID while the current job is queued, executing,
     * waiting for a retry, or has not yet permanently failed.
     */
    public function uniqueId(): string
    {
        return "document-processing:{$this->documentId}";
    }

    /**
     * Define the delay before each retry attempt.
     *
     * Attempt flow:
     *
     * - First failure: retry after 30 seconds.
     * - Second failure: retry after 120 seconds.
     *
     * The third value is harmless but will normally not be used when
     * the maximum number of attempts is three.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [
            30,
            120,
            300,
        ];
    }

    /**
     * Execute the queued document-processing operation.
     *
     * The service container automatically injects
     * DocumentProcessingService.
     *
     * Exceptions are intentionally not caught here. Allowing them to
     * propagate tells Laravel that the attempt failed and should be retried
     * according to the configured attempts and backoff values.
     *
     * @throws Throwable
     */
    public function handle(
        DocumentProcessingService $processingService
    ): void {
        $document = Document::query()
            ->find($this->documentId);

        /*
         * The document may have been deleted after the job was dispatched
         * but before a queue worker executed it. In this case there is
         * nothing left to process, so the job may finish successfully.
         */
        if ($document === null) {
            Log::notice(
                'Document processing job skipped because the document no longer exists.',
                [
                    'document_id' => $this->documentId,
                ]
            );

            return;
        }

        /*
         * Completed documents are ignored by the normal processing job.
         *
         * Intentional reprocessing should first move the document back to
         * pending, or use a dedicated reprocessing command/service before
         * dispatching this job.
         *
         * This check protects against an old or stale job reprocessing a
         * document that has already completed successfully.
         */
        if ($document->status === DocumentStatus::Completed) {
            Log::info(
                'Document processing job skipped because the document is already completed.',
                [
                    'document_id' => $document->getKey(),
                ]
            );

            return;
        }

        /*
         * The service performs all domain operations:
         *
         * - Transitioning the document to processing.
         * - Calling FastAPI.
         * - Validating the FastAPI response.
         * - Persisting chunks transactionally.
         * - Transitioning the document to completed.
         */
        $processingService->process($document);
    }

    /**
     * Handle permanent job failure.
     *
     * Laravel invokes this method only after:
     *
     * - All configured attempts have failed.
     * - The job is manually failed.
     * - A timeout permanently fails the job.
     *
     * The update is restricted to documents that are still processing.
     * This prevents an old failed job from overwriting a document that was
     * completed, deleted, reset, or otherwise changed by another process.
     */
    public function failed(?Throwable $exception): void
    {
        $failureReason = Str::limit(
            value: $exception?->getMessage()
                ?? 'Document processing failed after all retry attempts.',
            limit: 2000,
            end: '...'
        );

        $updatedRows = Document::query()
            ->whereKey($this->documentId)
            ->where(
                'status',
                DocumentStatus::Processing->value
            )
            ->update([
                'status' => DocumentStatus::Failed->value,
                'failure_reason' => $failureReason,
                'processed_at' => null,
                'updated_at' => now(),
            ]);

        Log::error(
            'Document processing job permanently failed.',
            [
                'document_id' => $this->documentId,
                'document_marked_as_failed' => $updatedRows === 1,
                'failure_reason' => $failureReason,
                'exception' => $exception,
            ]
        );
    }
}
