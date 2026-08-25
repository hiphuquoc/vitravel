<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_category_service')) {
            Schema::create('service_category_service', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
                $table->unsignedSmallInteger('sort')->default(0);
                $table->timestamps();

                $table->unique(['service_id', 'service_category_id'], 'uniq_service_cat_svc');
                $table->index(['service_category_id', 'service_id'], 'idx_cat_service');
            });
        }

        // Backfill: Tự động chuyển đổi dữ liệu cũ từ service_category_id sang bảng pivot
        DB::table('services')
            ->select(['id', 'service_category_id'])
            ->whereNotNull('service_category_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $insert = [];
                $now = now();
                foreach ($rows as $row) {
                    if (! $row->service_category_id) {
                        continue;
                    }
                    $insert[] = [
                        'service_id' => $row->id,
                        'service_category_id' => $row->service_category_id,
                        'sort' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($insert !== []) {
                    DB::table('service_category_service')->insertOrIgnore($insert);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_category_service');
    }
};
