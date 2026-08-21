<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use Illuminate\Console\Command;

/**
 * Chạy 1 bước crawler stay ngoài request HTTP (admin poll process-next).
 */
class StayCrawlStepCommand extends Command
{
    protected $signature = 'stay-crawl:step
        {jobId : ID stay_crawl_jobs}
        {--locale=vi}
        {--proxy : Fetch qua proxy Chrome}
        {--respect-robots : Tuân robots.txt cho HTTP fallback}
        {--html-file= : HTML dump (chỉ bước đầu)}';

    protected $description = 'Chạy 1 bước crawler lưu trú (basic hoặc enrich gallery/phòng)';

    public function handle(StayCrawlService $crawl): int
    {
        $jobId = (int) $this->argument('jobId');
        $this->line('['.now()->toIso8601String()."] stay-crawl:step start job={$jobId}");

        $job = StayCrawlJob::query()->with('category')->find($jobId);
        if (! $job) {
            $this->error("Job #{$jobId} không tồn tại.");

            return self::FAILURE;
        }

        if (! $this->bindProject($job)) {
            $crawl->failStep($job, 'Không gắn được project cho job #'.$job->id);
            $this->error('Không gắn được project cho job #'.$job->id);

            return self::FAILURE;
        }

        $html = $this->loadHtmlFile();
        $finished = false;

        try {
            @set_time_limit(720);
            $result = $crawl->processNext(
                $job,
                (string) $this->option('locale'),
                $html,
                (bool) $this->option('respect-robots'),
                (bool) $this->option('proxy') || (bool) data_get($job->meta, 'use_proxy', false),
            );
            $crawl->finishStep($job->fresh() ?? $job, $result);
            $finished = true;
            $phase = (string) ($result['phase'] ?? '');
            $message = (string) ($result['message'] ?? '');
            $this->line('['.now()->toIso8601String()."] stay-crawl:step done phase={$phase} remaining=".((int) ($result['remaining'] ?? 0))." {$message}");
        } catch (\Throwable $e) {
            $crawl->failStep($job->fresh() ?? $job, $e->getMessage());
            $finished = true;
            $this->error('['.now()->toIso8601String().'] stay-crawl:step fail: '.$e->getMessage());

            throw $e;
        } finally {
            if (! $finished) {
                $crawl->failStep($job->fresh() ?? $job, 'Bước crawler dừng giữa chừng (không finishStep).');
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
