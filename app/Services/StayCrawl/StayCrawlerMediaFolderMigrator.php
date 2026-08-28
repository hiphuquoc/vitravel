<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Models\Media;
use App\Models\MediaAttachment;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Services\MediaService;
use App\Support\ProjectContext;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Di chuyển ảnh crawler cũ từ stays/gallery|cover sang thư mục crawler riêng (multi-project).
 */
final class StayCrawlerMediaFolderMigrator
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * @return array{moved: int, skipped: int, errors: int, projects: int}
     */
    public function migrate(): array
    {
        $stats = ['moved' => 0, 'skipped' => 0, 'errors' => 0, 'projects' => 0];

        if (! Schema::hasTable('media') || ! Schema::hasTable('projects')) {
            return $stats;
        }

        $projects = Project::query()->orderBy('id')->get();
        foreach ($projects as $project) {
            ProjectContext::run($project, function () use (&$stats): void {
                $stats['projects']++;
                $this->migrateProject($stats);
            });
        }

        ProjectContext::clear();

        return $stats;
    }

    /** @param array{moved: int, skipped: int, errors: int, projects: int} $stats */
    private function migrateProject(array &$stats): void
    {
        $projectId = ProjectContext::id();
        if ($projectId === null) {
            return;
        }

        $roomMediaIds = $this->collectRoomMediaIds($projectId);
        $coverMediaIds = $this->collectCoverMediaIds($projectId);

        $oldPrefixes = [
            $this->media->projectPrefixedFolder('stays/gallery'),
            $this->media->projectPrefixedFolder('stays/cover'),
        ];

        $rows = Media::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->where(function ($q) use ($oldPrefixes) {
                foreach ($oldPrefixes as $prefix) {
                    $prefix = trim(str_replace('\\', '/', $prefix), '/');
                    $q->orWhere('path', 'like', $prefix.'/%');
                }
            })
            ->orderBy('id')
            ->get();

        foreach ($rows as $media) {
            try {
                $targetFolder = $this->classifyTargetFolder($media, $roomMediaIds, $coverMediaIds);
                if ($this->moveMediaToFolder($media, $targetFolder)) {
                    $stats['moved']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (Throwable) {
                $stats['errors']++;
            }
        }
    }

    /** @return array<int, true> */
    private function collectRoomMediaIds(int $projectId): array
    {
        $ids = [];
        if (! Schema::hasTable('service_options')) {
            return $ids;
        }

        ServiceOption::query()
            ->whereHas('service', fn ($q) => $q->withoutGlobalScope('project')->where('project_id', $projectId))
            ->select(['id', 'attrs'])
            ->orderBy('id')
            ->chunkById(200, function ($options) use (&$ids): void {
                foreach ($options as $option) {
                    $photos = is_array($option->attrs['photos'] ?? null) ? $option->attrs['photos'] : [];
                    foreach ($photos as $photo) {
                        if (! is_array($photo)) {
                            continue;
                        }
                        $id = (int) ($photo['media_id'] ?? 0);
                        if ($id > 0) {
                            $ids[$id] = true;
                        }
                    }
                }
            });

        return $ids;
    }

    /** @return array<int, true> */
    private function collectCoverMediaIds(int $projectId): array
    {
        $ids = [];
        if (! Schema::hasTable('media_attachments')) {
            return $ids;
        }

        $serviceIds = Service::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->where('cluster', Service::CLUSTER_STAY)
            ->pluck('id')
            ->all();

        if ($serviceIds === []) {
            return $ids;
        }

        MediaAttachment::query()
            ->where('role', 'cover')
            ->where('mediable_type', Service::class)
            ->whereIn('mediable_id', $serviceIds)
            ->pluck('media_id')
            ->each(function ($id) use (&$ids): void {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = true;
                }
            });

        return $ids;
    }

    /**
     * @param  array<int, true>  $roomMediaIds
     * @param  array<int, true>  $coverMediaIds
     */
    private function classifyTargetFolder(Media $media, array $roomMediaIds, array $coverMediaIds): string
    {
        $mediaId = (int) $media->id;
        $relative = $this->media->stripProjectPrefixFromPath((string) $media->path);

        if (str_starts_with($relative, 'stays/cover/') || isset($coverMediaIds[$mediaId])) {
            return (string) config('media.stays_crawler_cover', 'stays/crawler-cover');
        }

        if (isset($roomMediaIds[$mediaId])) {
            return (string) config('media.stays_crawler_room', 'stays/crawler-room');
        }

        $seoRole = (string) ($media->meta['seo_role'] ?? '');
        if (str_starts_with($seoRole, 'room')) {
            return (string) config('media.stays_crawler_room', 'stays/crawler-room');
        }

        return (string) config('media.stays_crawler_gallery', 'stays/crawler-gallery');
    }

    private function moveMediaToFolder(Media $media, string $targetFolder): bool
    {
        $targetBase = trim(str_replace('\\', '/', $this->media->projectPrefixedFolder($targetFolder)), '/');
        $currentPath = trim(str_replace('\\', '/', (string) $media->path), '/');

        if ($currentPath === $targetBase || str_starts_with($currentPath, $targetBase.'/')) {
            return false;
        }

        $oldBases = [
            trim(str_replace('\\', '/', $this->media->projectPrefixedFolder('stays/gallery')), '/'),
            trim(str_replace('\\', '/', $this->media->projectPrefixedFolder('stays/cover')), '/'),
        ];

        $matchedBase = null;
        foreach ($oldBases as $base) {
            if ($currentPath === $base || str_starts_with($currentPath, $base.'/')) {
                $matchedBase = $base;
                break;
            }
        }

        if ($matchedBase === null) {
            return false;
        }

        $filename = substr($currentPath, strlen($matchedBase) + 1);
        if ($filename === '' || str_contains($filename, '..')) {
            return false;
        }

        $newPath = $targetBase.'/'.$filename;
        if ($newPath === $currentPath) {
            return false;
        }

        $disk = Storage::disk($media->disk ?: $this->media->defaultDisk());
        $pathsToMove = [$currentPath];
        $variantUpdates = [];

        foreach (($media->meta['variants'] ?? []) as $name => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $variantPath = trim(str_replace('\\', '/', (string) ($variant['path'] ?? '')), '/');
            if ($variantPath === '' || ! str_starts_with($variantPath, $matchedBase.'/')) {
                continue;
            }
            $variantFile = substr($variantPath, strlen($matchedBase) + 1);
            $newVariantPath = $targetBase.'/'.$variantFile;
            $pathsToMove[] = $variantPath;
            $variantUpdates[$name] = array_merge($variant, ['path' => $newVariantPath]);
        }

        foreach (array_unique($pathsToMove) as $old) {
            $new = str_starts_with($old, $matchedBase.'/')
                ? $targetBase.'/'.substr($old, strlen($matchedBase) + 1)
                : null;
            if ($new === null) {
                continue;
            }
            if ($disk->exists($old)) {
                if ($disk->exists($new)) {
                    $disk->delete($new);
                }
                $disk->move($old, $new);
            }
        }

        $meta = is_array($media->meta) ? $media->meta : [];
        if ($variantUpdates !== []) {
            $meta['variants'] = array_replace($meta['variants'] ?? [], $variantUpdates);
        }

        $media->path = $newPath;
        $media->meta = $meta;
        $media->save();

        return true;
    }
}
