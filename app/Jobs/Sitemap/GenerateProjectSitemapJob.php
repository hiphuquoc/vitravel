<?php

declare(strict_types=1);

namespace App\Jobs\Sitemap;

use App\Models\Project;
use App\Services\Sitemap\SitemapGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProjectSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 1800;

    public function __construct(public readonly int $projectId) {}

    public function handle(SitemapGenerator $generator): void
    {
        $project = Project::query()->find($this->projectId);
        if (! $project || ! $project->is_active) {
            return;
        }

        $stats = $generator->generateForProject($project);
        Log::info('sitemap.generated', [
            'project_id' => $project->id,
            'project_code' => $project->code,
            'stats' => $stats,
        ]);
    }
}
