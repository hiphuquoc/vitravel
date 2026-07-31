<?php

namespace App\Console\Commands;

use App\Models\SeoRedirect;
use App\Services\SeoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class FixSeoRedirectsCommand extends Command
{
    protected $signature = 'seo:fix-redirects
                            {--purge-all : Xóa toàn bộ redirect_info (cách nhanh hết ERR_TOO_MANY_REDIRECTS)}';

    protected $description = 'Dọn redirect_info hỏng (self-loop / vòng A↔B) gây ERR_TOO_MANY_REDIRECTS';

    public function handle(SeoService $seo): int
    {
        if (! Schema::hasTable('redirect_info')) {
            $this->warn('Bảng redirect_info chưa tồn tại.');

            return self::SUCCESS;
        }

        if ($this->option('purge-all')) {
            $count = SeoRedirect::query()->count();
            SeoRedirect::query()->delete();
            $this->info("Đã xóa toàn bộ {$count} redirect.");

            return self::SUCCESS;
        }

        $deleted = $seo->purgeBadRedirects();
        $this->info("Đã xóa {$deleted} redirect hỏng (self-loop / vòng 2 chiều / rỗng).");
        $this->line('Còn lại: '.SeoRedirect::query()->count().' dòng.');

        return self::SUCCESS;
    }
}
