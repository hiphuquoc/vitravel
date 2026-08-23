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
        // 1. Tối ưu index bảng stay_crawl_items cho truy vấn worker & dashboard
        Schema::table('stay_crawl_items', function (Blueprint $table) {
            $table->index(['job_id', 'status', 'id'], 'idx_stay_items_job_status_id');
            $table->index(['status', 'crawled_at'], 'idx_stay_items_status_crawled');
        });

        // 2. Tối ưu index bảng services cho truy vấn trang danh mục & chi tiết
        Schema::table('services', function (Blueprint $table) {
            $table->index(['cluster', 'service_category_id', 'status', 'sort'], 'idx_services_cluster_cat_sort');
        });

        // 3. Tối ưu index bảng service_options (Hạng phòng)
        Schema::table('service_options', function (Blueprint $table) {
            $table->index(['service_id', 'capacity', 'price_from'], 'idx_svc_options_perf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stay_crawl_items', function (Blueprint $table) {
            $table->dropIndex('idx_stay_items_job_status_id');
            $table->dropIndex('idx_stay_items_status_crawled');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_cluster_cat_sort');
        });

        Schema::table('service_options', function (Blueprint $table) {
            $table->dropIndex('idx_svc_options_perf');
        });
    }
};
