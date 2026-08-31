<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceOption;
use App\Services\Purge\EntityPurgeService;

/**
 * @deprecated Dùng EntityPurgeService — giữ alias cho StayCrawlImporter & test.
 */
final class ServicePurgeService
{
    public function __construct(private readonly EntityPurgeService $purger) {}

    public function purge(Service $service): void
    {
        $this->purger->purgeService($service);
    }

    /**
     * Thu thập mọi media_id gắn dịch vụ (attachment + attrs gallery/room photos).
     *
     * @return list<int>
     */
    public function collectMediaIds(Service $service): array
    {
        $ids = [];

        foreach ($service->mediaAttachments ?? [] as $attachment) {
            $mid = (int) ($attachment->media_id ?? 0);
            if ($mid > 0) {
                $ids[] = $mid;
            }
        }

        $this->collectMediaIdsFromAttrs(is_array($service->attrs) ? $service->attrs : [], $ids);

        foreach ($service->options ?? [] as $option) {
            if (! $option instanceof ServiceOption) {
                continue;
            }
            $this->collectMediaIdsFromAttrs(is_array($option->attrs) ? $option->attrs : [], $ids);
        }

        if ($service->seoEntry?->og_image_id) {
            $ids[] = (int) $service->seoEntry->og_image_id;
        }

        return array_values(array_unique(array_filter($ids, fn (int $id) => $id > 0)));
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @param  list<int>  $ids
     */
    private function collectMediaIdsFromAttrs(array $attrs, array &$ids): void
    {
        foreach (['gallery_media_ids', 'cover_media_ids'] as $listKey) {
            if (! is_array($attrs[$listKey] ?? null)) {
                continue;
            }
            foreach ($attrs[$listKey] as $raw) {
                $mid = (int) $raw;
                if ($mid > 0) {
                    $ids[] = $mid;
                }
            }
        }

        foreach (['cover_media_id', 'media_id'] as $scalarKey) {
            $mid = (int) ($attrs[$scalarKey] ?? 0);
            if ($mid > 0) {
                $ids[] = $mid;
            }
        }

        foreach (['photos', 'gallery'] as $photoKey) {
            if (! is_array($attrs[$photoKey] ?? null)) {
                continue;
            }
            foreach ($attrs[$photoKey] as $photo) {
                if (is_array($photo)) {
                    $mid = (int) ($photo['media_id'] ?? 0);
                    if ($mid > 0) {
                        $ids[] = $mid;
                    }
                }
            }
        }
    }
}
