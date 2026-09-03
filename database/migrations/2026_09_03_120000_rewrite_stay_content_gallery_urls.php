<?php

use App\Services\StayCrawl\StayContentGalleryUrlRewriter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Sửa URL ảnh cứng trong HTML «Về chỗ nghỉ» sau khi file đã chuyển
 * stays/gallery|cover → stays/crawler-gallery|cover|room.
 *
 * Chỉ sửa DB (nhanh hơn migrate folder GCS). Có thể chạy riêng:
 *   php artisan stay:rewrite-gallery-urls-in-content
 *   php artisan stay:rewrite-gallery-urls-in-content --dry-run
 *   php artisan stay:rewrite-gallery-urls-in-content --project=phuquoc
 */
return new class extends Migration
{
    public function up(): void
    {
        Log::info('stay_content_gallery_urls: bắt đầu rewrite content/attrs');

        $stats = app(StayContentGalleryUrlRewriter::class)->rewrite(
            dryRun: false,
            projectCode: null,
            onProgress: function (array $progress): void {
                $updated = (int) ($progress['translations_updated'] ?? 0);
                if ($updated > 0 && $updated % 100 === 0) {
                    Log::info('stay_content_gallery_urls: progress', $progress);
                }
            },
        );

        Log::info('stay_content_gallery_urls: hoàn tất', $stats);
    }

    public function down(): void
    {
        // Không hoàn tác — URL cũ /stays/gallery/ trỏ file đã chuyển.
    }
};
