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
        // 1. Tối ưu index đa địa điểm (Multi-tenant by project_id) cho Crawler Items & Jobs
        Schema::table('stay_crawl_items', function (Blueprint $table) {
            $table->index(['project_id', 'job_id', 'status', 'id'], 'idx_stay_items_proj_job_status');
            $table->index(['project_id', 'status', 'crawled_at'], 'idx_stay_items_proj_status_crawled');
            $table->index(['project_id', 'service_id'], 'idx_stay_items_proj_service');
        });

        Schema::table('stay_crawl_jobs', function (Blueprint $table) {
            $table->index(['project_id', 'service_category_id', 'status', 'created_at'], 'idx_stay_jobs_proj_cat_status');
        });

        // 2. Tối ưu index đa địa điểm cho Địa danh & Tiện ích
        Schema::table('stay_places', function (Blueprint $table) {
            $table->index(['project_id', 'category', 'id'], 'idx_stay_places_proj_cat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stay_crawl_items', function (Blueprint $table) {
            $table->dropIndex('idx_stay_items_proj_job_status');
            $table->dropIndex('idx_stay_items_proj_status_crawled');
            $table->dropIndex('idx_stay_items_proj_service');
        });

        Schema::table('stay_crawl_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_stay_jobs_proj_cat_status');
        });

        Schema::table('stay_places', function (Blueprint $table) {
            $table->dropIndex('idx_stay_places_proj_cat');
        });
    }
};
