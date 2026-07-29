<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tách ảnh listing banner (ngang dài) khỏi thumbnail trang chủ (banner_media_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('listing_banner_media_id')
                ->nullable()
                ->after('banner_media_id')
                ->constrained('media')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('listing_banner_media_id');
        });
    }
};
