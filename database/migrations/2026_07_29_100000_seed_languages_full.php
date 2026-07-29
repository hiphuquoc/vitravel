<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Upsert full language list from config('language.list').
 * Idempotent — safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            return;
        }

        $this->ensureHreflangColumn();

        $oldZh = DB::table('languages')->where('code', 'zh')->first();
        $newZhCn = DB::table('languages')->where('code', 'zh-cn')->first();
        if ($oldZh && ! $newZhCn) {
            DB::table('languages')->where('id', $oldZh->id)->update([
                'code' => 'zh-cn',
                'name' => 'Tiếng Trung (Giản thể)',
                'name_native' => '简体中文',
                'updated_at' => now(),
            ]);
        }

        foreach (config('language.list', []) as $cfg) {
            $code = $cfg['code'];
            $payload = [
                'name' => $cfg['name'],
                'name_native' => $cfg['name_native'] ?? null,
                'flag' => $cfg['flag'] ?? null,
                'og_locale' => $cfg['og_locale'] ?? null,
                'dir' => $cfg['dir'] ?? 'ltr',
                'is_active' => ! empty($cfg['is_active']) ? 1 : 0,
                'is_default' => ! empty($cfg['is_default']) ? 1 : 0,
                'sort' => $cfg['sort'] ?? 0,
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('languages', 'hreflang')) {
                $payload['hreflang'] = $cfg['hreflang'] ?? $code;
            }

            $exists = DB::table('languages')->where('code', $code)->first();
            if ($exists) {
                DB::table('languages')->where('id', $exists->id)->update($payload);
            } else {
                $payload['code'] = $code;
                $payload['created_at'] = now();
                DB::table('languages')->insert($payload);
            }
        }

        $defaultCode = config('language.default_code', 'vi');
        DB::table('languages')->update(['is_default' => 0]);
        DB::table('languages')->where('code', $defaultCode)->update(['is_default' => 1, 'is_active' => 1]);

        Language::clearCache();
        Language::flushCache();

        try {
            Cache::forget('languages:active');
            Cache::forget('languages:all');
            Cache::forget('languages:default');
            Cache::forget('languages:active:v2');
            Cache::forget('languages:all:v2');
            Cache::forget('languages:default:v2');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function ensureHreflangColumn(): void
    {
        if (! Schema::hasColumn('languages', 'hreflang')) {
            try {
                Schema::table('languages', function (Blueprint $table) {
                    $table->string('hreflang', 16)->nullable()->after('og_locale');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        // không revert — sẽ làm mất ngôn ngữ active.
    }
};
