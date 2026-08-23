<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use Illuminate\Console\Command;

/**
 * Command cào danh sách listing trong background process độc lập.
 */
class StayCrawlListCommand extends Command
{
    protected $signature = 'stay-crawl:list
        {jobId : ID stay_crawl_jobs}
        {--locale=vi}
        {--proxy : Fetch qua proxy Chrome}
        {--respect-robots : Tuân robots.txt}
        {--html-file= : HTML dump}
        {--max-pages=1 : Số trang tối đa}';

    protected $description = 'Chạy tiến trình cào danh sách khách sạn Booking.com ở chế độ background';

    public function handle(StayCrawlService $crawl): int
    {
        $jobId = (int) $this->argument('jobId');
        $this->line('['.now()->toIso8601String()."] stay-crawl:list start job={$jobId}");

        $job = StayCrawlJob::query()->with('category')->find($jobId);
        if (! $job) {
            $this->error("Job #{$jobId} không tồn tại.");

            return self::FAILURE;
        }

        if (! $this->bindProject($job)) {
            $crawl->failListStep($job, 'Không gắn được project cho job #'.$job->id);
            $this->error('Không gắn được project cho job #'.$job->id);

            return self::FAILURE;
        }

        $html = $this->loadHtmlFile();
        $finished = false;

        try {
            @set_time_limit(900);
            $result = $crawl->crawlListBackground(
                $job,
                $html,
                (bool) $this->option('respect-robots'),
                (int) $this->option('max-pages'),
                (bool) $this->option('proxy') || (bool) data_get($job->meta, 'use_proxy', false),
            );
            $finished = true;
            $urlsCount = count($result['urls'] ?? []);
            $this->line('['.now()->toIso8601String()."] stay-crawl:list done URLs={$urlsCount}");
        } catch (\Throwable $e) {
            $crawl->failListStep($job->fresh() ?? $job, $e->getMessage());
            $finished = true;
            $this->error('['.now()->toIso8601String().'] stay-crawl:list fail: '.$e->getMessage());

            throw $e;
        } finally {
            if (! $finished) {
                $crawl->failListStep($job->fresh() ?? $job, 'Tiến trình cào danh sách dừng đột ngột.');
            }
        }

        return self::SUCCESS;
    }

    private function bindProject(StayCrawlJob $job): bool
    {
        $projectId = (int) ($job->project_id ?: $job->category?->project_id);
        if ($projectId <= 0) {
            return false;
        }
        $project = Project::query()->find($projectId);
        if (! $project) {
            return false;
        }
        ProjectContext::set($project);

        return true;
    }

    private function loadHtmlFile(): ?string
    {
        $path = (string) $this->option('html-file');
        if ($path === '' || ! is_readable($path)) {
            return null;
        }
        $html = file_get_contents($path);
        @unlink($path);

        return is_string($html) && $html !== '' ? $html : null;
    }
}
