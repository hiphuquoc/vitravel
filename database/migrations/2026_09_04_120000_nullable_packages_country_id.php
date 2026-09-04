<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cho phép gỡ điểm đến chính khỏi package khi xóa Country (cascade unlink từ admin).
 * Trước đây country_id NOT NULL + restrictOnDelete → không xóa được điểm đến còn tour gắn.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('packages') || ! Schema::hasColumn('packages', 'country_id')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        // Tránh phụ thuộc doctrine/dbal cho ->change().
        DB::statement('ALTER TABLE packages MODIFY country_id BIGINT UNSIGNED NULL');

        Schema::table('packages', function (Blueprint $table) {
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('packages') || ! Schema::hasColumn('packages', 'country_id')) {
            return;
        }

        // Không thể restore NOT NULL nếu còn row null.
        DB::table('packages')->whereNull('country_id')->delete();

        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        DB::statement('ALTER TABLE packages MODIFY country_id BIGINT UNSIGNED NOT NULL');

        Schema::table('packages', function (Blueprint $table) {
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->restrictOnDelete();
        });
    }
};
