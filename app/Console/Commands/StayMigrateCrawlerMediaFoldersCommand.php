<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StayCrawl\StayCrawlerMediaFolderMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Di chuyển ảnh crawler khách sạn từ stays/gallery|cover sang thư mục riêng trên GCS.
 * Chạy riêng (có progress) thay vì chờ migrate im lặng — có thể mất vài giờ nếu nhiều KS.
 */
class StayMigrateCrawlerMediaFoldersCommand extends Command
{
    protected $signature = 'stay:migrate-crawler-media-folders
                            {--every=100 : In progress mỗi N file}';

    protected $description = 'Di chuyển ảnh crawler KS cũ sang stays/crawler-* (GCS move, có progress)';

    public function handle(StayCrawlerMediaFolderMigrator $migrator): int
    {
        if (! Schema::hasTable('media')) {
            $this->error('Chưa có bảng media.');

            return self::FAILURE;
        }

        $every = max(1, (int) $this->option('every'));
        $this->warn('Migration này move file trên GCS — có thể rất lâu nếu đã cào nhiều khách sạn.');
        $this->line('Theo dõi tiến độ bên dưới (moved / skipped / errors).');

        $stats = $migrator->migrate(function (array $progress) use ($every): void {
            $moved = (int) ($progress['moved'] ?? 0);
            if ($moved > 0 && $moved % $every === 0) {
                $this->line(sprintf(
                    '  … moved=%d skipped=%d errors=%d (media #%s)',
                    $moved,
                    (int) ($progress['skipped'] ?? 0),
                    (int) ($progress['errors'] ?? 0),
                    (string) ($progress['media_id'] ?? '?'),
                ));
            }
        });

        $this->newLine();
        $this->info(sprintf(
            'Xong: moved=%d skipped=%d errors=%d projects=%d',
            $stats['moved'],
            $stats['skipped'],
            $stats['errors'],
            $stats['projects'],
        ));

        return ($stats['errors'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
