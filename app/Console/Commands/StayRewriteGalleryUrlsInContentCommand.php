<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StayCrawl\StayContentGalleryUrlRewriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Sửa src ảnh /stays/gallery|cover/ trong HTML «Về chỗ nghỉ» → path crawler-* hiện tại.
 *
 *   php artisan stay:rewrite-gallery-urls-in-content --dry-run
 *   php artisan stay:rewrite-gallery-urls-in-content --project=phuquoc
 */
class StayRewriteGalleryUrlsInContentCommand extends Command
{
    protected $signature = 'stay:rewrite-gallery-urls-in-content
                            {--dry-run : Chỉ thống kê, không ghi DB}
                            {--project= : Chỉ 1 project code (vd: phuquoc)}
                            {--every=50 : In progress mỗi N bản dịch đã quét}';

    protected $description = 'Rewrite URL ảnh gallery|cover trong content «Về chỗ nghỉ» sang stays/crawler-*';

    public function handle(StayContentGalleryUrlRewriter $rewriter): int
    {
        if (! Schema::hasTable('service_translations')) {
            $this->error('Chưa có bảng service_translations.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $project = trim((string) $this->option('project'));
        $every = max(1, (int) $this->option('every'));

        if ($dryRun) {
            $this->warn('DRY-RUN — không ghi DB.');
        }

        $this->line('Đang quét service_translations.content (+ attrs.photos) của cluster stay…');

        $stats = $rewriter->rewrite(
            dryRun: $dryRun,
            projectCode: $project !== '' ? $project : null,
            onProgress: function (array $progress) use ($every): void {
                $scanned = (int) ($progress['translations_scanned'] ?? 0);
                if ($scanned > 0 && $scanned % $every === 0) {
                    $this->line(sprintf(
                        '  … scanned=%d updated=%d rewritten=%d unmatched=%d (tr #%s)',
                        $scanned,
                        (int) ($progress['translations_updated'] ?? 0),
                        (int) ($progress['urls_rewritten'] ?? 0),
                        (int) ($progress['urls_unmatched'] ?? 0),
                        (string) ($progress['service_translation_id'] ?? '?'),
                    ));
                }
            },
        );

        $this->newLine();
        $this->info(sprintf(
            'Xong%s: projects=%d scanned=%d content_updated=%d attrs_updated=%d urls_rewritten=%d unmatched=%d',
            $dryRun ? ' (dry-run)' : '',
            $stats['projects'],
            $stats['translations_scanned'],
            $stats['translations_updated'],
            $stats['attrs_updated'],
            $stats['urls_rewritten'],
            $stats['urls_unmatched'],
        ));

        if ($stats['urls_unmatched'] > 0) {
            $this->warn('Có URL không map được qua bảng media — đã fallback đổi folder gallery→crawler-gallery / cover→crawler-cover khi có thể.');
        }

        return self::SUCCESS;
    }
}
