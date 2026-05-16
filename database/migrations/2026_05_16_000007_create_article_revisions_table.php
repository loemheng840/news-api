<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('change_note')->nullable();
            $table->integer('version');
            $table->timestamps();

            $table->unique(['article_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_revisions');
    }
};
