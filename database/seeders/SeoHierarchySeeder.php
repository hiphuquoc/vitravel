<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;
use App\Models\StaticPage;
use App\Services\SeoService;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bước cuối pipeline seed: gắn hub → country/cruise_type/blog_category → package/tour_category/article
 * và rebuild slug_full theo cha (tránh 404 khi listing có data nhưng URL chưa đúng cây SEO).
 *
 * Idempotent — chạy lại an toàn: `php artisan db:seed --class=SeoHierarchySeeder`
 */
class SeoHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureProjectContext();

        $seo = app(SeoService::class);
        $seo->rebuildPublicSeoTree(['vi', 'en']);
        $seo->purgeBadRedirects();

        $this->backfillNullProjectIds();
        $this->purgeDuplicateHubPages();
    }

    /**
     * When run alone (`db:seed --class=SeoHierarchySeeder`), ProjectSeeder may not have set context.
     */
    protected function ensureProjectContext(): void
    {
        if (ProjectContext::id() || ! Schema::hasTable('projects')) {
            return;
        }

        $project = Project::query()->where('code', ProjectSeed::profile())->first()
            ?? Project::query()->orderBy('id')->first();

        if ($project) {
            ProjectContext::set($project);
            $this->command?->info("Project context: {$project->code} (#{$project->id})");
        }
    }

    /**
     * Legacy hub rows created before BelongsToProject / ProjectContext stay project_id null.
     *
     * Chỉ nhận orphan thuộc project đang seed. Không stamp bản dịch SEO
     * của entry project khác (tránh unique slug + lẫn data phuquy/culaocham).
     */
    protected function backfillNullProjectIds(): void
    {
        $projectId = ProjectContext::id();
        if (! $projectId) {
            return;
        }

        $seoUpdated = SeoEntry::withoutGlobalScope('project')
            ->whereNull('project_id')
            ->update(['project_id' => $projectId]);

        $pagesUpdated = 0;
        if (Schema::hasTable('static_pages')) {
            $pagesUpdated = StaticPage::withoutGlobalScope('project')
                ->whereNull('project_id')
                ->update(['project_id' => $projectId]);
        }

        $transUpdated = 0;
        if (Schema::hasColumn('seo_entry_translations', 'project_id')) {
            $transUpdated = SeoEntryTranslation::query()
                ->whereNull('project_id')
                ->whereIn('seo_entry_id', function ($query) use ($projectId) {
                    $query->select('id')
                        ->from('seo_entries')
                        ->where('project_id', $projectId);
                })
                ->update(['project_id' => $projectId]);
        }

        if ($seoUpdated || $pagesUpdated || $transUpdated) {
            $this->command?->info(
                "Backfilled project_id={$projectId}: seo_entries={$seoUpdated}, static_pages={$pagesUpdated}, translations={$transUpdated}"
            );
        }
    }

    /**
     * After backfill, same hub template can exist twice (orphan + scoped). Keep the SEO parent with most children.
     */
    protected function purgeDuplicateHubPages(): void
    {
        $projectId = ProjectContext::id();
        if (! $projectId || ! Schema::hasTable('static_pages')) {
            return;
        }

        $hubTemplates = collect(config('seo.hubs', []))
            ->pluck('template')
            ->filter()
            ->unique()
            ->values();

        foreach ($hubTemplates as $template) {
            $pages = StaticPage::withoutGlobalScope('project')
                ->where('project_id', $projectId)
                ->where('template', $template)
                ->orderBy('id')
                ->get();

            if ($pages->count() <= 1) {
                continue;
            }

            $scored = $pages->map(function (StaticPage $page) {
                $seo = SeoEntry::withoutGlobalScope('project')
                    ->where('reference_type', 'static_page')
                    ->where('reference_id', $page->id)
                    ->first();

                $childCount = $seo
                    ? SeoEntry::withoutGlobalScope('project')->where('parent_id', $seo->id)->count()
                    : 0;

                return [
                    'page' => $page,
                    'seo' => $seo,
                    'child_count' => $childCount,
                ];
            });

            $keep = $scored->sort(function (array $a, array $b) {
                if ($a['child_count'] !== $b['child_count']) {
                    return $b['child_count'] <=> $a['child_count'];
                }

                return $b['page']->id <=> $a['page']->id;
            })->first();

            foreach ($scored as $row) {
                if ($row['page']->id === $keep['page']->id) {
                    continue;
                }

                DB::transaction(function () use ($row) {
                    if ($row['seo']) {
                        SeoEntryTranslation::query()
                            ->where('seo_entry_id', $row['seo']->id)
                            ->delete();
                        $row['seo']->delete();
                    }
                    $row['page']->delete();
                });

                $this->command?->warn(
                    "Purged duplicate hub static_page #{$row['page']->id} ({$template})"
                );
            }
        }
    }
}
