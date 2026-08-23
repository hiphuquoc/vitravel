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
        // 1. Tối ưu index bảng packages (Tour / Cruise đa địa điểm & listing)
        Schema::table('packages', function (Blueprint $table) {
            $table->index(['type', 'status', 'country_id', 'sort'], 'idx_packages_type_status_country');
            $table->index(['type', 'status', 'is_featured', 'sort'], 'idx_packages_type_featured');
        });

        // 2. Tối ưu index bảng articles (Tin tức / Cẩm nang)
        Schema::table('articles', function (Blueprint $table) {
            $table->index(['status', 'published_at', 'id'], 'idx_articles_status_pub');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('idx_packages_type_status_country');
            $table->dropIndex('idx_packages_type_featured');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('idx_articles_status_pub');
        });
    }
};
