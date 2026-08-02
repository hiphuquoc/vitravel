<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thumbnail (card) tách khỏi banner listing trên cruise_types + service_categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cruise_types', function (Blueprint $table) {
            $table->foreignId('cover_media_id')
                ->nullable()
                ->after('banner_media_id')
                ->constrained('media')
                ->nullOnDelete();
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->foreignId('cover_media_id')
                ->nullable()
                ->after('banner_media_id')
                ->constrained('media')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cruise_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cover_media_id');
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cover_media_id');
        });
    }
};
