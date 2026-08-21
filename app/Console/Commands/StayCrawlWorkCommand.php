<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use Illuminate\Console\Command;

/**
 * Worker bền cho job danh mục dài ngày — lặp processNext đến hết hàng.
 * Pause: meta.worker.paused hoặc file storage/app/stay-crawl-pause-{jobId}.
 * Resume: xoá pause + chạy lại lệnh / API resume (spawn nếu heartbeat chết).
 */
class StayCrawlWorkCommand extends Command
{
    protected $signature = 'stay-crawl:work
        {jobId : ID stay_crawl_jobs}
        {--locale=vi}
        {--proxy : Fetch qua proxy Chrome}
        {--respect-robots : Tuân robots.txt cho HTTP fallback}
        {--max-steps=0 : Giới hạn bước (0 = không giới hạn)}
        {--sleep-ms= : Nghỉ giữa các bước (ms); mặc định config stay.crawl.worker_sleep_ms}';

    protected $description = 'Worker nền: cào lần lượt từng URL trong job (an toàn dừng/resume)';

    private bool $stopping = false;

    public function handle(StayCrawlService $crawl): int
    {
        $jobId = (int) $this->argument('jobId');
        $this->line('['.now()->toIso8601String()."] stay-crawl:work start job={$jobId}");

        $job = StayCrawlJob::query()->with('category')->find($jobId);
        if (! $job) {
            $this->error("Job #{$jobId} không tồn tại.");

            return self::FAILURE;
        }

        if (! $this->bindProject($job)) {
            $crawl->stopWorkerMeta($job, 'no_project');
            $this->error('Không gắn được project cho job #'.$job->id);

            return self::FAILURE;
        }

        $this->installSignalHandlers();

        $locale = (string) $this->option('locale');
        $useProxy = (bool) $this->option('proxy') || (bool) data_get($job->meta, 'use_proxy', false);
        $respectRobots = (bool) $this->option('respect-robots');
        $maxSteps = max(0, (int) $this->option('max-steps'));
        $sleepMs = (int) ($this->option('sleep-ms') ?: config('stay.crawl.worker_sleep_ms', 400));
        $sleepMs = max(0, min(60_000, $sleepMs));
        $steps = 0;
        $pauseFile = $this->pauseFilePath($job->id);

        $crawl->touchWorkerHeartbeat($job, [
            'pid' => getmypid() ?: null,
            'paused' => false,
            'stop_reason' => null,
            'log' => 'storage/logs/stay-crawl-work-'.$job->id.'.log',
        ]);

        try {
            while (! $this->stopping) {
                $job->refresh();

                if ($this->shouldPause($job, $pauseFile)) {
                    $crawl->touchWorkerHeartbeat($job, [
                        'paused' => true,
                        'phase' => 'paused',
                        'message' => 'Worker tạm dừng — chờ resume',
                    ]);
                    $this->line('['.now()->toIso8601String().'] paused — đợi resume (meta hoặc xoá '.$pauseFile.')');
                    usleep(2_000_000);

                    continue;
                }

                $crawl->touchWorkerHeartbeat($job, [
                    'paused' => false,
                    'phase' => 'running',
                ]);

                @set_time_limit(720);
                $result = $crawl->processNext($job, $locale, null, $respectRobots, $useProxy);
                $crawl->finishStep($job->fresh() ?? $job, $result);
                $steps++;

                $phase = (string) ($result['phase'] ?? '');
                $message = (string) ($result['message'] ?? '');
                $remaining = (int) ($result['remaining'] ?? 0);
                $this->line(
                    '['.now()->toIso8601String()."] step={$steps} phase={$phase} remaining={$remaining} {$message}"
                );

                $crawl->touchWorkerHeartbeat($job->fresh() ?? $job, [
                    'last_phase' => $phase,
                    'last_message' => $message,
                    'steps' => $steps,
                    'remaining' => $remaining,
                ]);

                if (! empty($result['done']) || $remaining === 0) {
                    $crawl->stopWorkerMeta($job->fresh() ?? $job, 'completed');
                    $this->info('['.now()->toIso8601String()."] stay-crawl:work done job={$jobId} steps={$steps}");

                    return self::SUCCESS;
                }

                if ($maxSteps > 0 && $steps >= $maxSteps) {
                    $crawl->stopWorkerMeta($job->fresh() ?? $job, 'max_steps');
                    $this->warn("Đạt --max-steps={$maxSteps} — dừng an toàn; chạy lại để tiếp tục.");

                    return self::SUCCESS;
                }

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }
        } catch (\Throwable $e) {
            $crawl->failStep($job->fresh() ?? $job, $e->getMessage());
            $crawl->stopWorkerMeta($job->fresh() ?? $job, 'error: '.$e->getMessage());
            $this->error('['.now()->toIso8601String().'] stay-crawl:work fail: '.$e->getMessage());

            return self::FAILURE;
        }

        $crawl->stopWorkerMeta($job->fresh() ?? $job, 'signal');
        $this->warn('['.now()->toIso8601String()."] stay-crawl:work stopped (signal) job={$jobId} — chạy lại lệnh để resume");

        return self::SUCCESS;
    }

    private function shouldPause(StayCrawlJob $job, string $pauseFile): bool
    {
        if ((bool) data_get($job->meta, 'worker.paused', false)) {
            return true;
        }

        return is_file($pauseFile);
    }

    private function pauseFilePath(int $jobId): string
    {
        return storage_path('app/stay-crawl-pause-'.$jobId);
    }

    private function installSignalHandlers(): void
    {
        if (! function_exists('pcntl_async_signals')) {
            return;
        }
        pcntl_async_signals(true);
        foreach ([SIGTERM, SIGINT] as $sig) {
            pcntl_signal($sig, function () {
                $this->stopping = true;
                $this->warn('Nhận tín hiệu dừng — hoàn tất bước hiện tại rồi thoát…');
            });
        }
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
}
