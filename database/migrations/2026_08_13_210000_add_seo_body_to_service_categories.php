<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_categories', 'seo_body')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->longText('seo_body')->nullable()->after('intro');
            });
        }

        DB::table('service_categories')
            ->whereNull('seo_body')
            ->whereNotNull('intro')
            ->update(['seo_body' => DB::raw('intro')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_categories', 'seo_body')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropColumn('seo_body');
            });
        }
    }
};
