<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\StayCrawlJob;
use App\Services\StayCatalog\StayDiscoverService;
use App\Support\ProjectContext;
use Illuminate\Console\Command;

class StayCatalogDiscoverCommand extends Command
{
    protected $signature = 'stay-catalog:discover
        {jobId : ID stay_crawl_jobs discover}
        {--proxy : Chrome qua proxy}';

    protected $description = 'Chạy Chrome list_discover — lưu filter, không spawn list-crawl';

    public function handle(StayDiscoverService $discover): int
    {
        $jobId = (int) $this->argument('jobId');
        $job = StayCrawlJob::query()->find($jobId);
        if (! $job) {
            $this->error("Job #{$jobId} không tồn tại.");

            return self::FAILURE;
        }
        $project = Project::query()->find((int) $job->project_id) ?? Project::query()->first();
        if ($project) {
            ProjectContext::set($project);
        }

        try {
            $job = $discover->runDiscover($job, (bool) $this->option('proxy'));
            $this->info('Discover review filters='.$job->items_found.' status='.$job->status);
        } catch (\Throwable $e) {
            $job->status = StayCrawlJob::STATUS_FAILED;
            $job->error = $e->getMessage();
            $job->save();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
