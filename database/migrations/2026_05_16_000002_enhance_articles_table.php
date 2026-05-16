<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First change the enum to include REVIEW
        DB::statement("ALTER TABLE articles DROP CONSTRAINT IF EXISTS articles_status_check");
        DB::statement("ALTER TABLE articles ALTER COLUMN status TYPE VARCHAR(20)");

        Schema::table('articles', function (Blueprint $table) {
            $table->text('excerpt')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false);
            $table->unsignedInteger('reading_time_minutes')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'is_featured', 'is_breaking', 'reading_time_minutes']);
            $table->dropSoftDeletes();
        });
    }
};
