<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đoạn SEO cuối trang hub listing (tours/cruises/dịch vụ) — quản lý admin, rỗng = ẩn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_page_translations', function (Blueprint $table) {
            $table->longText('seo_body')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('static_page_translations', function (Blueprint $table) {
            $table->dropColumn('seo_body');
        });
    }
};
