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
        Schema::create('manuscripts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('arxiv_id')->nullable()->index();
            $table->string('authors')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->text('abstract')->nullable();
            $table->json('keywords')->nullable();
            $table->string('source_filename');
            $table->string('file_path');
            $table->string('checksum', 64)->unique();
            $table->unsignedBigInteger('file_size');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manuscripts');
    }
};
