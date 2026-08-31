<?php

declare(strict_types=1);

namespace App\Services\Purge;

use App\Models\Faq;
use App\Models\MediaAttachment;
use App\Models\Review;
use App\Models\SeoEntry;
use App\Services\HtmlCacheService;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;

/**
 * Helpers dùng chung khi xóa cứng entity + media GCS orphan.
 */
final class PurgeSupport
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * @return list<string>
     */
    public function collectSeoCacheKeys(?SeoEntry $seo): array
    {
        if (! $seo) {
            return [];
        }

        $keys = [];
        foreach ($seo->translations ?? $seo->translations()->get() as $trans) {
            $slugFull = trim((string) ($trans->slug_full ?? ''));
            if ($slugFull === '' || str_contains($slugFull, '__trashed-') || str_contains($slugFull, '__orphaned-')) {
                continue;
            }
            $keys[] = HtmlCacheService::buildKey($slugFull);
        }

        return $keys;
    }

    /**
     * @param  list<int>  $mediaIds
     */
    public function purgeSeo(?SeoEntry $seo, array &$mediaIds): void
    {
        if (! $seo) {
            return;
        }

        if ($seo->og_image_id) {
            $this->pushMediaId((int) $seo->og_image_id, $mediaIds);
        }

        $seo->translations()->delete();
        $seo->delete();
    }

    public function purgeFaqs(string $morphType, int $entityId): void
    {
        Faq::query()
            ->where('faqable_type', $morphType)
            ->where('faqable_id', $entityId)
            ->each(function (Faq $faq): void {
                $faq->translations()->delete();
                $faq->delete();
            });
    }

    /**
     * @param  list<int>  $mediaIds
     */
    public function purgeMediaAttachments(string $morphType, int $entityId, array &$mediaIds): void
    {
        MediaAttachment::query()
            ->where('mediable_type', $morphType)
            ->where('mediable_id', $entityId)
            ->each(function (MediaAttachment $attachment) use (&$mediaIds): void {
                $this->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            });

        MediaAttachment::query()
            ->where('mediable_type', $morphType)
            ->where('mediable_id', $entityId)
            ->delete();
    }

    /**
     * @param  list<int>  $mediaIds
     */
    public function purgeReviews(string $morphType, int $entityId, array &$mediaIds): void
    {
        Review::query()
            ->with('mediaAttachments')
            ->where('reviewable_type', $morphType)
            ->where('reviewable_id', $entityId)
            ->each(function (Review $review) use (&$mediaIds): void {
                $this->pushMediaId((int) ($review->avatar_media_id ?? 0), $mediaIds);
                foreach ($review->mediaAttachments as $attachment) {
                    $this->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
                }
                MediaAttachment::query()
                    ->where('mediable_type', 'review')
                    ->where('mediable_id', $review->id)
                    ->delete();
                $review->delete();
            });
    }

    public function purgeTranslations(Model $model): void
    {
        if (method_exists($model, 'translations')) {
            $model->translations()->delete();
        }
    }

    /**
     * @param  list<int>  $mediaIds
     */
    public function pushMediaId(int $id, array &$mediaIds): void
    {
        if ($id > 0) {
            $mediaIds[] = $id;
        }
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @param  list<int>  $mediaIds
     */
    public function collectMediaIdsFromAttrs(array $attrs, array &$mediaIds): void
    {
        foreach (['gallery_media_ids', 'cover_media_ids'] as $listKey) {
            if (! is_array($attrs[$listKey] ?? null)) {
                continue;
            }
            foreach ($attrs[$listKey] as $raw) {
                $this->pushMediaId((int) $raw, $mediaIds);
            }
        }

        foreach (['cover_media_id', 'media_id', 'banner_media_id', 'listing_banner_media_id', 'image_media_id', 'avatar_media_id', 'thumbnail_media_id', 'video_media_id'] as $scalarKey) {
            $this->pushMediaId((int) ($attrs[$scalarKey] ?? 0), $mediaIds);
        }

        foreach (['photos', 'gallery'] as $photoKey) {
            if (! is_array($attrs[$photoKey] ?? null)) {
                continue;
            }
            foreach ($attrs[$photoKey] as $photo) {
                if (is_array($photo)) {
                    $this->pushMediaId((int) ($photo['media_id'] ?? 0), $mediaIds);
                }
            }
        }
    }

    /**
     * @param  list<int>  $mediaIds
     * @param  list<string>  $cacheKeys
     */
    public function finish(array $mediaIds, array $cacheKeys): void
    {
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
}
