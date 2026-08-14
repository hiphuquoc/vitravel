<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cruise_types', 'intro')) {
            Schema::table('cruise_types', function (Blueprint $table) {
                $table->text('intro')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('cruise_types', 'seo_body')) {
            Schema::table('cruise_types', function (Blueprint $table) {
                $table->longText('seo_body')->nullable()->after('intro');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cruise_types', function (Blueprint $table) {
            if (Schema::hasColumn('cruise_types', 'seo_body')) {
                $table->dropColumn('seo_body');
            }
            if (Schema::hasColumn('cruise_types', 'intro')) {
                $table->dropColumn('intro');
            }
        });
    }
};
