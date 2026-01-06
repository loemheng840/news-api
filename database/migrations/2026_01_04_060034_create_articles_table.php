<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug', 191)->unique();
            $table->longText('content');
            $table->string('thumbnail')->nullable();

            $table->enum('status', ['DRAFT','PUBLISHED','ARCHIVED'])
                  ->default('DRAFT');

            $table->unsignedBigInteger('views')->default(0);

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->foreignId('author_id')
                  ->nullable()
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};