<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'original_name',
    'stored_name',
    'title',
    'file_path',
    'file_type',
    'mime_type',
    'file_size',
    'sha256',
    'status',
    'failure_reason',
    'total_pages',
    'total_chunks',
    'qdrant_collection',
    'processed_at',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'user_id' => 'integer',
            'file_size' => 'integer',
            'total_pages' => 'integer',
            'total_chunks' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the document.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chunks associated with the document.
     *
     * @return HasMany<DocumentChunk, $this>
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }
}
