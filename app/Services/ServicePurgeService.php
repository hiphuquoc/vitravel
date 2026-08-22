<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Faq;
use App\Models\HomeFeaturedService;
use App\Models\Media;
use App\Models\MediaAttachment;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\StayCrawlItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Xóa cứng chỗ nghỉ / dịch vụ kèm quan hệ + file media (GCS) orphan.
 * Dùng chung: admin DELETE service (stay) và crawler rerun=replace.
 */
final class ServicePurgeService
{
    public function __construct(private readonly MediaService $media) {}

    public function purge(Service $service): void
    {
        $serviceId = (int) $service->id;

        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($serviceId, &$mediaIds, &$cacheKeys): void {
            $service = Service::query()
                ->withTrashed()
                ->with([
                    'options',
                    'faqs.translations',
                    'mediaAttachments',
                    'seoEntry.translations',
                    'priceTable',
                ])
                ->find($serviceId);

            if (! $service) {
                return;
            }

            $mediaIds = $this->collectMediaIds($service);

            foreach ($service->seoEntry?->translations ?? [] as $trans) {
                $slugFull = trim((string) ($trans->slug_full ?? ''));
                if ($slugFull === '') {
                    continue;
                }
                $cacheKeys[] = HtmlCacheService::buildKey($slugFull);
            }

            $this->purgeReviewsForService($serviceId, $mediaIds);

            foreach ($service->options as $option) {
                $option->translations()->delete();
                $option->delete();
            }

            foreach ($service->faqs as $faq) {
                $faq->translations()->delete();
                $faq->delete();
            }
            // FAQ morph còn sót (chưa eager-load / soft state)
            Faq::query()
                ->where('faqable_type', 'service')
                ->where('faqable_id', $serviceId)
                ->each(function (Faq $faq): void {
                    $faq->translations()->delete();
                    $faq->delete();
                });

            $service->translations()->delete();

            MediaAttachment::query()
                ->where('mediable_type', 'service')
                ->where('mediable_id', $serviceId)
                ->delete();

            $price = $service->priceTable;
            if ($price) {
                $price->delete();
            }

            if ($seo = $service->seoEntry) {
                if ($seo->og_image_id) {
                    $mediaIds[] = (int) $seo->og_image_id;
                }
                $seo->translations()->delete();
                $seo->delete();
            }

            StayCrawlItem::query()->where('service_id', $serviceId)->update(['service_id' => null]);

            if (Schema::hasTable('home_featured_services')) {
                HomeFeaturedService::query()->where('service_id', $serviceId)->delete();
            }

            $service->forceDelete();
        });

        $mediaIds = array_values(array_unique(array_filter(
            array_map('intval', $mediaIds),
            fn (int $id) => $id > 0,
        )));

        $this->media->destroyOrphanMediaBatch($mediaIds);

        $cache = app(HtmlCacheService::class);
        foreach (array_unique($cacheKeys) as $key) {
            $cache->clear($key);
        }
    }

    /**
     * Thu thập mọi media_id gắn chỗ nghỉ (attachment + attrs gallery/room photos).
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

    /**
     * @param  list<int>  $mediaIds
     */
    private function purgeReviewsForService(int $serviceId, array &$mediaIds): void
    {
        $reviews = Review::query()
            ->withTrashed()
            ->with('mediaAttachments')
            ->where('reviewable_type', 'service')
            ->where('reviewable_id', $serviceId)
            ->get();

        foreach ($reviews as $review) {
            $avatar = (int) ($review->avatar_media_id ?? 0);
            if ($avatar > 0) {
                $mediaIds[] = $avatar;
            }
            foreach ($review->mediaAttachments as $attachment) {
                $mid = (int) ($attachment->media_id ?? 0);
                if ($mid > 0) {
                    $mediaIds[] = $mid;
                }
            }
            MediaAttachment::query()
                ->where('mediable_type', 'review')
                ->where('mediable_id', $review->id)
                ->delete();
            $review->forceDelete();
        }
    }
}
