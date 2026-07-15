<?php

namespace App\Services\Ai;

use App\Models\Document;
use App\Services\Documents\DocumentProcessingService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * Client responsible for communicating with the external AI service.
 *
 * This service sends uploaded documents from Laravel's private storage
 * to the FastAPI document-processing endpoint using a multipart/form-data
 * HTTP request.
 *
 * The AI service is expected to:
 * - Receive the uploaded document.
 * - Extract its textual content.
 * - Split the content into chunks.
 * - Generate embeddings.
 * - Store vectors in the configured vector database.
 * - Return document-processing metadata as JSON.
 *
 * This class is responsible only for HTTP communication and basic response
 * validation. It does not update the document status or persist processing
 * results in the Laravel database.
 *
 * @see DocumentProcessingService
 */
class AiServiceClient
{
    /**
     * Send a document to the FastAPI document-processing service.
     *
     * The document file is retrieved from Laravel's private storage disk,
     * opened as a binary stream, and uploaded to the configured FastAPI
     * endpoint using a multipart/form-data request.
     *
     * The request includes the following form fields:
     * - document_id: The Laravel document identifier.
     * - user_id: The owner of the document.
     * - original_name: The original uploaded file name.
     * - sha256: The document checksum, when available.
     *
     * The request is retried up to three times when an HTTP transport
     * exception occurs. Before every retry, the file stream is rewound
     * so that the complete file can be uploaded again.
     *
     * Expected response structure:
     *
     * @example
     * [
     *     'status' => 'completed',
     *     'document_id' => 15,
     *     'total_pages' => 24,
     *     'total_chunks' => 130,
     *     'qdrant_collection' => 'user_documents',
     * ]
     *
     * @param  Document  $document
     *                              The document model whose physical file will be sent for
     *                              processing.
     * @return array<string, mixed>
     *                              The decoded JSON response returned by the AI service.
     *
     * @throws RuntimeException
     *                          Thrown when:
     *                          - The document file does not exist.
     *                          - The document file cannot be opened.
     *                          - The AI service base URL is not configured.
     * @throws UnexpectedValueException
     *                                  Thrown when the AI service returns:
     *                                  - A response that cannot be decoded into an array.
     *                                  - A JSON payload missing required fields.
     * @throws RequestException
     *                          Thrown when the AI service returns an unsuccessful HTTP
     *                          response after all retry attempts are exhausted.
     */
    public function processDocument(Document $document): array
    {
        /*
         * Retrieve the private storage disk used for uploaded documents.
         *
         * The "private" disk should be defined in config/filesystems.php
         * and must point to a directory that is not publicly accessible.
         */
        $disk = Storage::disk('private');

        /*
         * Verify that the physical document file still exists before
         * attempting to communicate with the AI service.
         */
        if (! $disk->exists($document->file_path)) {
            throw new RuntimeException(
                "Document file does not exist: {$document->file_path}"
            );
        }

        /*
         * Resolve the absolute filesystem path required by fopen().
         */
        $absolutePath = $disk->path($document->file_path);

        /*
         * Open the document as a read-only binary stream.
         *
         * Binary mode is used to ensure that document bytes are transmitted
         * without platform-specific text transformations.
         */
        $stream = fopen($absolutePath, 'rb');

        if ($stream === false) {
            throw new RuntimeException(
                "Unable to open document file: {$document->file_path}"
            );
        }

        /*
         * Read and normalize the FastAPI service base URL.
         *
         * The trailing slash is removed to avoid generating URLs containing
         * duplicate slashes.
         */
        $baseUrl = rtrim(
            (string) config('services.ai_services.base_url'),
            '/'
        );

        /*
         * The stream must be closed manually because the HTTP request has
         * not started yet and the method will terminate immediately.
         */
        if ($baseUrl === '') {
            fclose($stream);

            throw new RuntimeException(
                'AI service base URL is not configured.'
            );
        }

        /*
         * Define the maximum total request duration.
         *
         * Document extraction, chunking, embedding generation, and vector
         * storage may require significantly more time than a regular API
         * request, so the default timeout is set to 600 seconds.
         */
        $timeout = max(
            1,
            (int) config('services.ai_services.timeout', 600)
        );

        try {
            /*
             * Build and execute the multipart HTTP request.
             *
             * Connection timeout:
             * Limits the amount of time spent establishing the connection.
             *
             * Request timeout:
             * Limits the total duration of the processing request.
             *
             * Retry:
             * Retries the request up to three times with a one-second delay.
             * The stream is rewound before each retry because the previous
             * attempt may have already consumed part or all of the file.
             */
            $response = Http::acceptJson()
                ->connectTimeout(10)
                ->timeout($timeout)
                ->retry(
                    3,
                    1000,
                    function (
                        Throwable $exception,
                        PendingRequest $request
                    ) use ($stream): bool {
                        /*
                         * Reset the stream pointer before retrying the upload.
                         *
                         * Without rewind(), a retry may send an empty or
                         * partially consumed file.
                         */
                        if (is_resource($stream)) {
                            rewind($stream);
                        }

                        /*
                         * Returning true instructs Laravel to retry for every
                         * supported transport exception.
                         */
                        return true;
                    }
                )
                ->attach(
                    'file',
                    $stream,
                    basename($document->original_name)
                )
                ->post(
                    "{$baseUrl}/api/v1/documents/process",
                    [
                        'document_id' => (string) $document->id,
                        'user_id' => (string) $document->user_id,
                        'original_name' => $document->original_name,
                        'sha256' => $document->sha256 ?? '',
                    ]
                );

            /*
             * Convert unsuccessful HTTP responses into a RequestException.
             *
             * This includes responses such as:
             * - 400 Bad Request
             * - 422 Validation Error
             * - 500 Internal Server Error
             * - 503 Service Unavailable
             */
            $response->throw();

            /*
             * Decode the JSON response into a PHP array.
             */
            $data = $response->json();

            /*
             * Ensure that the response contains a JSON object or array.
             */
            if (! is_array($data)) {
                throw new UnexpectedValueException(
                    'The AI service returned an invalid JSON response.'
                );
            }

            /*
             * Perform minimum contract validation.
             *
             * Additional validation should preferably be delegated to a
             * dedicated response validator such as:
             *
             * ProcessDocumentResponseValidator.
             */
            if (
                ! isset($data['status']) ||
                ! array_key_exists('document_id', $data)
            ) {
                throw new UnexpectedValueException(
                    'The AI service response is missing required fields.'
                );
            }

            return $data;
        } finally {
            /*
             * Always release the file handle.
             *
             * The finally block runs whether the request succeeds, fails,
             * or throws an exception during response validation.
             */
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
}
