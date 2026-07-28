<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Command;

class GenerateMediaVariants extends Command
{
    protected $signature = 'media:generate-variants
                            {--id= : Chỉ regenerate một media id}
                            {--force : Chạy cả bản đã có variants}';

    protected $description = 'Sinh lại các phiên bản resize (thumb/card/lg) cho ảnh đã upload (GCS/local)';

    public function handle(MediaService $mediaService): int
    {
        $query = Media::query()->orderBy('id');

        if ($id = $this->option('id')) {
            $query->where('id', (int) $id);
        }

        $force = (bool) $this->option('force');
        $items = $query->get()->filter(function (Media $media) use ($force) {
            return $force || empty($media->meta['variants']);
        });

        $total = $items->count();
        if ($total === 0) {
            $this->info('Không có media cần regenerate.');

            return self::SUCCESS;
        }

        $this->info("Đang xử lý {$total} ảnh…");
        $bar = $this->output->createProgressBar($total);
        $ok = 0;
        $fail = 0;

        foreach ($items as $media) {
            if ($mediaService->regenerateVariants($media)) {
                $ok++;
            } else {
                $fail++;
                $this->newLine();
                $this->warn("Fail #{$media->id}: {$media->path}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Xong — thành công: {$ok}, lỗi: {$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
