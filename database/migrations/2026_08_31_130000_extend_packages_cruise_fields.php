<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đồng bộ độ dài packages.cruise_type / boat_class với validation API và cruise_types.slug.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('packages')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'cruise_type')) {
                $table->string('cruise_type', 64)->nullable()->change();
            }
            if (Schema::hasColumn('packages', 'boat_class')) {
                $table->string('boat_class', 100)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('packages')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'cruise_type')) {
                $table->string('cruise_type', 32)->nullable()->change();
            }
            if (Schema::hasColumn('packages', 'boat_class')) {
                $table->string('boat_class', 32)->nullable()->change();
            }
        });
    }
};
