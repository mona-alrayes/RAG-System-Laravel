<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentChunk>
 */
class DocumentChunkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'chunk_index' => fake()->numberBetween(0, 100),
            'content' => fake()->paragraphs(3, true),
            'page_number' => fake()->optional()->numberBetween(1, 100),
            'vector_id' => fake()->uuid(),
            'metadata' => [
                'language' => 'ar',
                'source' => 'test-document.pdf',
            ],
        ];
    }
}
