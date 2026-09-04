<?php

declare(strict_types=1);

use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Điểm đến (countries) chỉ gắn tour / lưu trú / vui chơi…
 * Di chuyển (train/ferry) và máy bay không còn relation country_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')
            ->whereIn('cluster', Service::CLUSTERS_WITHOUT_DESTINATION)
            ->whereNotNull('country_id')
            ->update(['country_id' => null]);
    }

    public function down(): void
    {
        // Irreversible data cleanup.
    }
};
