<?php

use App\Enums\DocumentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_name')->unique();
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->string('file_type', 20);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('sha256', 64)->nullable()->index();
            $table->string('status', 20)->default(DocumentStatus::Pending->value);
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('total_pages')->nullable();
            $table->unsignedBigInteger('total_chunks')->default(0);
            $table->string('qdrant_collection')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
            // $table->unique(['user_id', 'sha256']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
