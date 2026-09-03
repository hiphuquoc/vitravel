<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Models\Media;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceTranslation;
use App\Support\ProjectContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sửa URL ảnh cứng trong HTML «Về chỗ nghỉ» (service_translations.content)
 * sau khi stay:migrate-crawler-media-folders đã chuyển file gallery|cover → crawler-*.
 *
 * Không đụng vitravel/services (upload admin thủ công).
 */
final class StayContentGalleryUrlRewriter
{
    /**
     * @param  (callable(array<string, mixed>): void)|null  $onProgress
     * @return array{
     *   projects: int,
     *   translations_scanned: int,
     *   translations_updated: int,
     *   urls_rewritten: int,
     *   urls_unmatched: int,
     *   attrs_updated: int,
     *   dry_run: bool
     * }
     */
    public function rewrite(bool $dryRun = false, ?string $projectCode = null, ?callable $onProgress = null): array
    {
        $stats = [
            'projects' => 0,
            'translations_scanned' => 0,
            'translations_updated' => 0,
            'urls_rewritten' => 0,
            'urls_unmatched' => 0,
            'attrs_updated' => 0,
            'dry_run' => $dryRun,
        ];

        if (! Schema::hasTable('service_translations') || ! Schema::hasTable('services')) {
            return $stats;
        }

        $projects = Project::query()
            ->when($projectCode !== null && $projectCode !== '', fn ($q) => $q->where('code', $projectCode))
            ->orderBy('id')
            ->get();

        foreach ($projects as $project) {
            ProjectContext::run($project, function () use (&$stats, $dryRun, $onProgress, $project): void {
                $stats['projects']++;
                $this->rewriteProject($project, $stats, $dryRun, $onProgress);
            });
        }

        ProjectContext::clear();

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    private function rewriteProject(Project $project, array &$stats, bool $dryRun, ?callable $onProgress): void
    {
        $projectId = (int) $project->id;
        $pathByBasename = $this->buildMediaPathIndex($projectId);

        $serviceIds = Service::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->where('cluster', Service::CLUSTER_STAY)
            ->pluck('id');

        if ($serviceIds->isEmpty()) {
            return;
        }

        ServiceTranslation::query()
            ->whereIn('service_id', $serviceIds)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (&$stats, $dryRun, $pathByBasename, $onProgress): void {
                foreach ($rows as $tr) {
                    $stats['translations_scanned']++;
                    $original = (string) $tr->content;
                    if (! str_contains($original, '/stays/gallery/') && ! str_contains($original, '/stays/cover/')) {
                        continue;
                    }

                    [$updated, $rewritten, $unmatched] = $this->rewriteHtml($original, $pathByBasename);
                    $stats['urls_rewritten'] += $rewritten;
                    $stats['urls_unmatched'] += $unmatched;

                    if ($updated !== $original) {
                        $stats['translations_updated']++;
                        if (! $dryRun) {
                            DB::table('service_translations')
                                ->where('id', $tr->id)
                                ->update(['content' => $updated, 'updated_at' => now()]);
                        }
                    }

                    if ($onProgress !== null) {
                        $onProgress(array_merge($stats, [
                            'service_translation_id' => (int) $tr->id,
                            'service_id' => (int) $tr->service_id,
                        ]));
                    }
                }
            });

        // attrs.photos[].url trên service (gallery gắn JSON) — cùng path cũ
        Service::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->where('cluster', Service::CLUSTER_STAY)
            ->orderBy('id')
            ->chunkById(100, function ($services) use (&$stats, $dryRun, $pathByBasename): void {
                foreach ($services as $service) {
                    $attrs = is_array($service->attrs) ? $service->attrs : [];
                    if ($attrs === []) {
                        continue;
                    }
                    [$newAttrs, $changed, $rewritten, $unmatched] = $this->rewriteAttrsTree($attrs, $pathByBasename);
                    $stats['urls_rewritten'] += $rewritten;
                    $stats['urls_unmatched'] += $unmatched;
                    if ($changed) {
                        $stats['attrs_updated']++;
                        if (! $dryRun) {
                            $service->forceFill(['attrs' => $newAttrs])->save();
                        }
                    }
                }
            });
    }

    /**
     * basename file → path GCS hiện tại (projects/.../stays/crawler-*/file.webp).
     *
     * @return array<string, string>
     */
    private function buildMediaPathIndex(int $projectId): array
    {
        $index = [];
        if (! Schema::hasTable('media')) {
            return $index;
        }

        Media::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->where(function ($q): void {
                $q->where('path', 'like', '%/stays/crawler-gallery/%')
                    ->orWhere('path', 'like', '%/stays/crawler-cover/%')
                    ->orWhere('path', 'like', '%/stays/crawler-room/%')
                    ->orWhere('path', 'like', '%/stays/gallery/%')
                    ->orWhere('path', 'like', '%/stays/cover/%');
            })
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$index): void {
                foreach ($rows as $media) {
                    $path = trim(str_replace('\\', '/', (string) $media->path), '/');
                    $base = basename($path);
                    if ($base !== '' && $base !== '.' && $base !== '/') {
                        // Ưu tiên crawler-* (path mới) — ghi đè gallery cũ nếu còn cả hai
                        $preferNew = str_contains($path, '/stays/crawler-');
                        if ($preferNew || ! isset($index[$base])) {
                            $index[$base] = $path;
                        }
                    }

                    $variants = is_array($media->meta['variants'] ?? null) ? $media->meta['variants'] : [];
                    foreach ($variants as $variant) {
                        if (! is_array($variant)) {
                            continue;
                        }
                        $vPath = trim(str_replace('\\', '/', (string) ($variant['path'] ?? '')), '/');
                        if ($vPath === '') {
                            continue;
                        }
                        $vBase = basename($vPath);
                        if ($vBase === '') {
                            continue;
                        }
                        $preferNew = str_contains($vPath, '/stays/crawler-');
                        if ($preferNew || ! isset($index[$vBase])) {
                            $index[$vBase] = $vPath;
                        }
                    }
                }
            });

        return $index;
    }

    /**
     * @param  array<string, string>  $pathByBasename
     * @return array{0: string, 1: int, 2: int} [html, rewritten, unmatched]
     */
    private function rewriteHtml(string $html, array $pathByBasename): array
    {
        $rewritten = 0;
        $unmatched = 0;

        $updated = preg_replace_callback(
            '#https?://[^\s"\'<>]+/stays/(gallery|cover)/[^\s"\'<>]+#i',
            function (array $m) use ($pathByBasename, &$rewritten, &$unmatched): string {
                $url = $m[0];
                $next = $this->rewriteUrl($url, $pathByBasename);
                if ($next === null) {
                    $unmatched++;

                    return $url;
                }
                if ($next !== $url) {
                    $rewritten++;
                }

                return $next;
            },
            $html
        );

        return [(string) $updated, $rewritten, $unmatched];
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @param  array<string, string>  $pathByBasename
     * @return array{0: array<string, mixed>, 1: bool, 2: int, 3: int}
     */
    private function rewriteAttrsTree(array $attrs, array $pathByBasename): array
    {
        $changed = false;
        $rewritten = 0;
        $unmatched = 0;

        $walk = function (&$node) use (&$walk, &$changed, &$rewritten, &$unmatched, $pathByBasename): void {
            if (is_string($node)) {
                if (str_contains($node, '/stays/gallery/') || str_contains($node, '/stays/cover/')) {
                    $next = $this->rewriteUrl($node, $pathByBasename);
                    if ($next === null) {
                        $unmatched++;
                    } elseif ($next !== $node) {
                        $node = $next;
                        $changed = true;
                        $rewritten++;
                    }
                }

                return;
            }
            if (! is_array($node)) {
                return;
            }
            foreach ($node as &$child) {
                $walk($child);
            }
            unset($child);
        };

        $walk($attrs);

        return [$attrs, $changed, $rewritten, $unmatched];
    }

    /**
     * @param  array<string, string>  $pathByBasename
     */
    private function rewriteUrl(string $url, array $pathByBasename): ?string
    {
        if (! preg_match('#^(https?://[^/]+)/(.+)/stays/(gallery|cover)/([^?\s#]+)#i', $url, $m)) {
            return null;
        }

        $host = $m[1];
        $beforeStays = trim($m[2], '/'); // projects/phuquoc
        $oldFolder = strtolower($m[3]); // gallery|cover
        $filename = $m[4];
        $basename = rawurldecode(basename(strtok($filename, '?') ?: $filename));

        $suffix = '';
        if (preg_match('/([?#].*)$/', $url, $sm)) {
            $suffix = $sm[1];
        }

        if (isset($pathByBasename[$basename])) {
            return $host.'/'.ltrim($pathByBasename[$basename], '/').$suffix;
        }

        // Fallback: đổi folder theo quy ước migrator (gallery→crawler-gallery, cover→crawler-cover)
        $newFolder = $oldFolder === 'cover' ? 'crawler-cover' : 'crawler-gallery';

        return $host.'/'.$beforeStays.'/stays/'.$newFolder.'/'.$filename.$suffix;
    }
}
