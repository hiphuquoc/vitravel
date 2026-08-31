<?php

use App\Services\Purge\LegacySoftDeletePurgeService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drop cột deleted_at sau khi chạy: php artisan purge:legacy-soft-deletes
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (LegacySoftDeletePurgeService::tablesWithSoftDeleteColumn() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            $remaining = DB::table($table)->whereNotNull('deleted_at')->count();
            if ($remaining > 0) {
                throw new RuntimeException(
                    "Bảng {$table} còn {$remaining} row deleted_at. "
                    .'Chạy: php artisan purge:legacy-soft-deletes'
                );
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropSoftDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (LegacySoftDeletePurgeService::tablesWithSoftDeleteColumn() as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->softDeletes();
            });
        }
    }
};
