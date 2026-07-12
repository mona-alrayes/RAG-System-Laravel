<?php

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a document chunk belongs to a document', function () {
    $document = Document::factory()->create();

    $chunk = DocumentChunk::factory()
        ->for($document)
        ->create();

    expect($chunk->document)
        ->toBeInstanceOf(Document::class)
        ->and($chunk->document->is($document))
        ->toBeTrue();
});

test('a document has many chunks', function () {
    $document = Document::factory()->create();

    DocumentChunk::factory()
        ->count(3)
        ->for($document)
        ->create();

    expect($document->chunks)
        ->toHaveCount(3);
});

test('chunk metadata is cast to an array', function () {
    $chunk = DocumentChunk::factory()->create([
        'metadata' => [
            'language' => 'ar',
            'source' => 'example.pdf',
            'embedding_model' => 'multilingual-e5-base',
        ],
    ]);

    $chunk->refresh();

    expect($chunk->metadata)
        ->toBeArray()
        ->and($chunk->metadata['language'])
        ->toBe('ar')
        ->and($chunk->metadata['source'])
        ->toBe('example.pdf');
});

test('chunks can be ordered by their chunk index', function () {
    $document = Document::factory()->create();

    DocumentChunk::factory()->for($document)->create([
        'chunk_index' => 2,
    ]);

    DocumentChunk::factory()->for($document)->create([
        'chunk_index' => 0,
    ]);

    DocumentChunk::factory()->for($document)->create([
        'chunk_index' => 1,
    ]);

    $indexes = $document
        ->chunks()
        ->ordered()
        ->pluck('chunk_index')
        ->all();

    expect($indexes)->toBe([0, 1, 2]);
});

test('chunk index must be unique inside the same document', function () {
    $document = Document::factory()->create();

    DocumentChunk::factory()->for($document)->create([
        'chunk_index' => 0,
    ]);

    expect(
        fn () => DocumentChunk::factory()->for($document)->create([
            'chunk_index' => 0,
        ])
    )->toThrow(QueryException::class);
});

test('the same chunk index can exist in different documents', function () {
    $firstDocument = Document::factory()->create();
    $secondDocument = Document::factory()->create();

    DocumentChunk::factory()->for($firstDocument)->create([
        'chunk_index' => 0,
    ]);

    DocumentChunk::factory()->for($secondDocument)->create([
        'chunk_index' => 0,
    ]);

    expect(DocumentChunk::query()->count())
        ->toBe(2);
});

test('deleting a document deletes its chunks', function () {
    $document = Document::factory()->create();

    DocumentChunk::factory()
        ->count(3)
        ->for($document)
        ->create();

    $documentId = $document->id;

    $document->delete();

    expect(
        DocumentChunk::query()
            ->where('document_id', $documentId)
            ->exists()
    )->toBeFalse();
});
