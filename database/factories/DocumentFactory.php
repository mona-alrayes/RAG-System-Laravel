<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        $extension = match (fake()->numberBetween(1, 3)) {
            1 => 'pdf',
            2 => 'docx',
            default => 'txt',
        };

        $storedName = fake()->uuid().'.'.$extension;
        $originalName = fake()->slug(3).'.'.$extension;

        return [
            'user_id' => User::factory(),
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'title' => fake()->sentence(3),
            'file_path' => $storedName,
            'file_type' => $extension,
            'mime_type' => match ($extension) {
                'pdf' => 'application/pdf',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
            },
            'file_size' => fake()->numberBetween(10_000, 5_000_000),
            'sha256' => hash('sha256', fake()->uuid()),
            'status' => DocumentStatus::Pending,
            'failure_reason' => null,
            'total_pages' => null,
            'total_chunks' => 0,
            'qdrant_collection' => null,
            'processed_at' => null,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn (): array => [
            'status' => DocumentStatus::Processing,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => DocumentStatus::Completed,
            'total_pages' => fake()->numberBetween(1, 100),
            'total_chunks' => fake()->numberBetween(1, 300),
            'qdrant_collection' => 'local_documents',
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => DocumentStatus::Failed,
            'failure_reason' => 'تعذر معالجة الوثيقة.',
        ]);
    }
}
