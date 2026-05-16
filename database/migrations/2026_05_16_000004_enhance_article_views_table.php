<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_views', function (Blueprint $table) {
            // Drop the unique constraint so multiple views per user are allowed
            $table->dropUnique(['article_id', 'user_id']);

            // Make user_id nullable for guest visitors
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Add new columns
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->unsignedTinyInteger('read_percent')->nullable();
            $table->unsignedInteger('time_on_page')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('article_views', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'ip_address', 'user_agent', 'referrer', 'read_percent', 'time_on_page']);

            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['article_id', 'user_id']);
        });
    }
};
