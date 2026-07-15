<?php

namespace App\Models;

use Database\Factories\DocumentChunkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'document_id',
    'chunk_index',
    'content',
    'page_number',
    'vector_id',
    'metadata',
])]
class DocumentChunk extends Model
{
    /** @use HasFactory<DocumentChunkFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'document_id' => 'integer',
            'chunk_index' => 'integer',
            'page_number' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the document that owns the chunk.
     *
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Order chunks by their position.
     *
     * @param  Builder<DocumentChunk>  $query
     * @return Builder<DocumentChunk>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('chunk_index');
    }
}
