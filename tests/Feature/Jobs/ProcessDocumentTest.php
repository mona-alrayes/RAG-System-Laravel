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
    Storage::fake('private');

    config()->set('services.ai_services.enabled', true);
    config()->set(
        'services.ai_services.base_url',
        'http://ai-service.test'
    );

    $user = User::factory()->create();

    $filePath = 'documents/test-document.pdf';

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
    ]);

    Http::fake([
        'http://ai-service.test/api/v1/documents/process' => Http::response([
            'document_id' => $document->id,
            'total_pages' => 2,
            'qdrant_collection' => 'documents',
            'chunks' => [
                [
                    'chunk_index' => 0,
                    'content' => 'المقطع الأول من الوثيقة.',
                    'page_number' => 1,
                    'vector_id' => "document-{$document->id}-chunk-0",
                    'metadata' => [
                        'language' => 'ar',
                    ],
                ],
                [
                    'chunk_index' => 1,
                    'content' => 'المقطع الثاني من الوثيقة.',
                    'page_number' => 2,
                    'vector_id' => "document-{$document->id}-chunk-1",
                    'metadata' => [
                        'language' => 'ar',
                    ],
                ],
            ],
        ]),
    ]);

    $job = new ProcessDocument($document->id);

    $job->handle(
        app(DocumentProcessingService::class)
    );

    $document->refresh();

    expect($document->status)
        ->toBe(DocumentStatus::Completed)
        ->and($document->total_pages)
        ->toBe(2)
        ->and($document->total_chunks)
        ->toBe(2)
        ->and($document->qdrant_collection)
        ->toBe('documents')
        ->and($document->processed_at)
        ->not->toBeNull();

    $this->assertDatabaseHas('document_chunks', [
        'document_id' => $document->id,
        'chunk_index' => 0,
        'page_number' => 1,
    ]);

    $this->assertDatabaseHas('document_chunks', [
        'document_id' => $document->id,
        'chunk_index' => 1,
        'page_number' => 2,
    ]);
});

it('marks the document as failed after final job failure', function () {
    $document = Document::factory()->create([
        'status' => DocumentStatus::Processing,
    ]);

    $job = new ProcessDocument($document->id);

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
