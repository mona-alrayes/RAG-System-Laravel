<?php

use App\Enums\DocumentStatus;
use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('processes a document and stores returned chunks', function () {
    /*
     * Use an isolated fake private disk so the test does not interact
     * with real application files.
     */
    Storage::fake('private');

    /*
     * Enable the AI service during this test and redirect requests
     * to a fake URL handled by Http::fake().
     */
    config([
        'services.ai_services.enabled' => true,
        'services.ai_services.base_url' => 'http://ai-service.test',
        'services.ai_services.timeout' => 30,
        'services.ai_services.max_chunks_per_document' => 100,
    ]);

    $user = User::factory()->create();

    $filePath = 'documents/test-document.pdf';

    /*
     * Create the physical fake file expected by AiServiceClient.
     */
    Storage::disk('private')->put(
        $filePath,
        'fake pdf content'
    );

    $document = Document::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'test-document.pdf',
        'stored_name' => 'test-document.pdf',
        'file_path' => $filePath,
        'file_type' => 'pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 100,
        'status' => DocumentStatus::Pending,
        'failure_reason' => null,
        'total_pages' => null,
        'total_chunks' => 0,
        'qdrant_collection' => null,
        'processed_at' => null,
    ]);

    /*
     * Simulate the successful response expected from FastAPI.
     *
     * The "status" field is required by AiServiceClient.
     */
    Http::fake([
        'http://ai-service.test/api/v1/documents/process' =>
            Http::response([
                'status' => DocumentStatus::Completed->value,
                'document_id' => $document->id,
                'total_pages' => 2,
                'total_chunks' => 2,
                'qdrant_collection' => 'documents',
                'chunks' => [
                    [
                        'chunk_index' => 0,
                        'content' => 'المقطع الأول من الوثيقة.',
                        'page_number' => 1,
                        'vector_id' =>
                            "document-{$document->id}-chunk-0",
                        'metadata' => [
                            'language' => 'ar',
                            'source' => 'test-document.pdf',
                        ],
                    ],
                    [
                        'chunk_index' => 1,
                        'content' => 'المقطع الثاني من الوثيقة.',
                        'page_number' => 2,
                        'vector_id' =>
                            "document-{$document->id}-chunk-1",
                        'metadata' => [
                            'language' => 'ar',
                            'source' => 'test-document.pdf',
                        ],
                    ],
                ],
            ], 200),
    ]);

    /*
     * Execute the job synchronously inside the test.
     */
    $job = new ProcessDocument($document->id);

    $job->handle(
        app(DocumentProcessingService::class)
    );

    /*
     * Reload the model to retrieve the latest database values.
     */
    $document->refresh();

    /*
     * Verify the final document state.
     */
    expect($document->status)
        ->toBe(DocumentStatus::Completed)
        ->and($document->failure_reason)
        ->toBeNull()
        ->and($document->total_pages)
        ->toBe(2)
        ->and($document->total_chunks)
        ->toBe(2)
        ->and($document->qdrant_collection)
        ->toBe('documents')
        ->and($document->processed_at)
        ->not->toBeNull();

    /*
     * Verify that the returned chunks were persisted.
     */
    $this->assertDatabaseHas('document_chunks', [
        'document_id' => $document->id,
        'chunk_index' => 0,
        'content' => 'المقطع الأول من الوثيقة.',
        'page_number' => 1,
        'vector_id' => "document-{$document->id}-chunk-0",
    ]);

    $this->assertDatabaseHas('document_chunks', [
        'document_id' => $document->id,
        'chunk_index' => 1,
        'content' => 'المقطع الثاني من الوثيقة.',
        'page_number' => 2,
        'vector_id' => "document-{$document->id}-chunk-1",
    ]);

    /*
     * Ensure Laravel sent exactly one request to the expected endpoint.
     */
    Http::assertSentCount(1);

    Http::assertSent(
        fn ($request): bool => $request->url()
            === 'http://ai-service.test/api/v1/documents/process'
            && $request->method() === 'POST'
    );
});

it('marks the document as failed after final job failure', function () {
    $document = Document::factory()->create([
        'status' => DocumentStatus::Processing,
        'failure_reason' => null,
        'processed_at' => null,
    ]);

    $job = new ProcessDocument($document->id);

    /*
     * Simulate Laravel calling failed() after all retry attempts
     * have been exhausted.
     */
    $job->failed(
        new RuntimeException('AI service is unavailable.')
    );

    $document->refresh();

    expect($document->status)
        ->toBe(DocumentStatus::Failed)
        ->and($document->failure_reason)
        ->toBe('AI service is unavailable.')
        ->and($document->processed_at)
        ->toBeNull();
});

it('does not mark a completed document as failed', function () {
    $document = Document::factory()->create([
        'status' => DocumentStatus::Completed,
        'failure_reason' => null,
        'processed_at' => now(),
    ]);

    $originalProcessedAt = $document->processed_at;

    $job = new ProcessDocument($document->id);

    /*
     * An old failed job must not overwrite a document that has already
     * completed successfully.
     */
    $job->failed(
        new RuntimeException('Old queue attempt failed.')
    );

    $document->refresh();

    expect($document->status)
        ->toBe(DocumentStatus::Completed)
        ->and($document->failure_reason)
        ->toBeNull()
        ->and($document->processed_at?->equalTo($originalProcessedAt))
        ->toBeTrue();
});