<?php

use App\Services\StayCrawl\StayCrawlerMediaFolderMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Di chuyển ảnh crawler khách sạn cũ sang thư mục riêng (multi-project).
     *
     * Rất chậm khi đã cào nhiều KS — có thể chạy riêng:
     *   php artisan stay:migrate-crawler-media-folders
     * rồi đánh dấu migration đã chạy nếu cần.
     */
    public function up(): void
    {
        Log::info('stay_crawler_media_folders: bắt đầu (có thể mất lâu trên GCS)');

        $stats = app(StayCrawlerMediaFolderMigrator::class)->migrate(function (array $progress): void {
            $moved = (int) ($progress['moved'] ?? 0);
            if ($moved > 0 && $moved % 200 === 0) {
                Log::info('stay_crawler_media_folders: progress', $progress);
            }
        });

        Log::info('stay_crawler_media_folders: hoàn tất', $stats);
    }

    public function down(): void
    {
        // Không hoàn tác — path cũ stays/gallery|cover có thể đã bị ghi đè.
    }
};
