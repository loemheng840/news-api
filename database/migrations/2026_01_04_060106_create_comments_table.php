<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('article_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('user_name', 100)->nullable();
            $table->text('content');

            $table->enum('status', ['PENDING','APPROVED','REJECTED'])
                  ->default('PENDING');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};