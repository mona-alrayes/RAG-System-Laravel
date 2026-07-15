<?php

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
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('chunk_index');
            $table->longtext('content');
            $table->unsignedInteger('page_number')->nullable();
            $table->string('vector_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['document_id', 'chunk_index']);
            $table->index(['document_id', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};
