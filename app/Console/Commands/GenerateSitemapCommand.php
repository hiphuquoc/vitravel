<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Sitemap\GenerateProjectSitemapJob;
use App\Models\Project;
use App\Services\Sitemap\SitemapGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Sinh sitemap XML đa dự án.
 *
 *   php artisan sitemap:generate
 *   php artisan sitemap:generate --project=vitravel
 *   php artisan sitemap:generate --queue
 */
class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate
                            {--project= : Chỉ 1 project code}
                            {--queue : Đẩy job queue thay vì chạy sync}
                            {--base-url= : Override base URL (https://domain.com)}';

    protected $description = 'Sinh sitemap XML theo từng project (multi-tenant, chunked)';

    public function handle(SitemapGenerator $generator): int
    {
        if (! Schema::hasTable('projects')) {
            $this->error('Chưa có bảng projects.');

            return self::FAILURE;
        }

        $code = $this->option('project') ? trim((string) $this->option('project')) : null;
        $query = Project::query()->active()->orderBy('id');
        if ($code) {
            $query->where('code', $code);
        }

        $projects = $query->get();
        if ($projects->isEmpty()) {
            $this->warn($code ? "Không tìm thấy project active: {$code}" : 'Không có project active.');

            return self::FAILURE;
        }

        $useQueue = (bool) $this->option('queue');
        $baseUrl = $this->option('base-url') ? rtrim((string) $this->option('base-url'), '/') : null;

        foreach ($projects as $project) {
            if ($useQueue) {
                GenerateProjectSitemapJob::dispatch($project->id);
                $this->info("Queued sitemap for project {$project->code} (#{$project->id})");

                continue;
            }

            $this->line("Generating sitemap: {$project->code} …");
            $stats = $generator->generateForProject($project, $baseUrl);
            $this->info(sprintf(
                '  OK %s — locales=%d types=%d files=%d urls=%d → %s',
                $project->code,
                $stats['locales'],
                $stats['types'],
                $stats['files'],
                $stats['urls'],
                $generator->projectRoot($project).'/sitemap.xml',
            ));
        }

        if ($useQueue) {
            $this->comment('Chạy queue worker: php artisan queue:work');
        }

        return self::SUCCESS;
    }
}
