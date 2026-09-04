<?php

declare(strict_types=1);

namespace App\Services\Purge;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\Comment;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Destination;
use App\Models\ExperienceAlbum;
use App\Models\ExperienceAlbumPhoto;
use App\Models\ExperienceVideo;
use App\Models\HomeFeaturedCruise;
use App\Models\HomeFeaturedService;
use App\Models\HomeFeaturedTeamMember;
use App\Models\HomeFeaturedTour;
use App\Models\HomeSlide;
use App\Models\Media;
use App\Models\Office;
use App\Models\Package;
use App\Models\ReferencePerson;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\StayCrawlItem;
use App\Models\TeamMember;
use App\Models\TourCategory;
use App\Models\TravelStyle;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Xóa cứng mọi trang/entity admin — relations, SEO, media GCS orphan.
 */
final class EntityPurgeService
{
    public function __construct(
        private readonly PurgeSupport $support,
        private readonly MediaService $media,
    ) {}

    public function purge(Model $model): void
    {
        match (true) {
            $model instanceof Service => $this->purgeService($model),
            $model instanceof Package => $this->purgePackage($model),
            $model instanceof Article => $this->purgeArticle($model),
            $model instanceof Country => $this->purgeCountry($model),
            $model instanceof TourCategory => $this->purgeTourCategory($model),
            $model instanceof BlogCategory => $this->purgeBlogCategory($model),
            $model instanceof ServiceCategory => $this->purgeServiceCategory($model),
            $model instanceof CruiseType => $this->purgeCruiseType($model),
            $model instanceof TeamMember => $this->purgeTeamMember($model),
            $model instanceof Review => $this->purgeReview($model),
            $model instanceof ExperienceAlbum => $this->purgeExperienceAlbum($model),
            $model instanceof ExperienceVideo => $this->purgeExperienceVideo($model),
            $model instanceof HomeSlide => $this->purgeHomeSlide($model),
            $model instanceof Destination => $this->purgeDestination($model),
            $model instanceof Media => $this->purgeMedia($model),
            $model instanceof Office,
            $model instanceof ReferencePerson,
            $model instanceof TravelStyle,
            $model instanceof \App\Models\CompanyValue,
            $model instanceof \App\Models\ReasonToChooseUs => $this->purgeSimpleTranslatable($model),
            default => $this->purgeGeneric($model),
        };
    }

    public function purgeService(Service $service): void
    {
        $serviceId = (int) $service->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($serviceId, &$mediaIds, &$cacheKeys): void {
            $service = Service::query()
                ->with(['options', 'faqs.translations', 'mediaAttachments', 'seoEntry.translations', 'priceTable'])
                ->find($serviceId);

            if (! $service) {
                return;
            }

            foreach ($service->mediaAttachments as $attachment) {
                $this->support->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            }
            $this->support->collectMediaIdsFromAttrs(is_array($service->attrs) ? $service->attrs : [], $mediaIds);

            foreach ($service->options as $option) {
                if ($option instanceof ServiceOption) {
                    $this->support->collectMediaIdsFromAttrs(is_array($option->attrs) ? $option->attrs : [], $mediaIds);
                }
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($service->seoEntry));

            $this->support->purgeReviews('service', $serviceId, $mediaIds);

            foreach ($service->options as $option) {
                $option->translations()->delete();
                $option->delete();
            }

            $this->support->purgeFaqs('service', $serviceId);
            $this->support->purgeMediaAttachments('service', $serviceId, $mediaIds);
            $this->support->purgeTranslations($service);

            if ($service->categories()->exists()) {
                $service->categories()->detach();
            }

            $service->priceTable?->delete();
            $this->support->purgeSeo($service->seoEntry, $mediaIds);

            StayCrawlItem::query()->where('service_id', $serviceId)->update(['service_id' => null]);

            if (Schema::hasTable('home_featured_services')) {
                HomeFeaturedService::query()->where('service_id', $serviceId)->delete();
            }

            $service->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgePackage(Package $package): void
    {
        $packageId = (int) $package->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($packageId, &$mediaIds, &$cacheKeys): void {
            $package = Package::query()
                ->with([
                    'itineraryDays.translations',
                    'cabinTypes.translations',
                    'faqs.translations',
                    'mediaAttachments',
                    'seoEntry.translations',
                    'priceTable',
                ])
                ->find($packageId);

            if (! $package) {
                return;
            }

            foreach ($package->mediaAttachments as $attachment) {
                $this->support->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            }

            foreach ($package->itineraryDays as $day) {
                $this->support->pushMediaId((int) ($day->image_media_id ?? 0), $mediaIds);
                $day->translations()->delete();
                $day->delete();
            }

            foreach ($package->cabinTypes as $cabin) {
                $cabin->translations()->delete();
                $cabin->delete();
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($package->seoEntry));

            $this->support->purgeReviews('package', $packageId, $mediaIds);
            $this->support->purgeFaqs('package', $packageId);
            $this->support->purgeMediaAttachments('package', $packageId, $mediaIds);
            $this->support->purgeTranslations($package);

            $package->countries()->detach();
            $package->categories()->detach();
            $package->travelStyles()->detach();
            $package->destinations()->detach();
            $package->relatedPackages()->detach();

            if (Schema::hasTable('article_package')) {
                DB::table('article_package')->where('package_id', $packageId)->delete();
            }

            if (Schema::hasTable('home_featured_tours')) {
                HomeFeaturedTour::query()->where('package_id', $packageId)->delete();
            }
            if (Schema::hasTable('home_featured_cruises')) {
                HomeFeaturedCruise::query()->where('package_id', $packageId)->delete();
            }

            $package->priceTable?->delete();
            $this->support->purgeSeo($package->seoEntry, $mediaIds);

            $package->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeArticle(Article $article): void
    {
        $articleId = (int) $article->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($articleId, &$mediaIds, &$cacheKeys): void {
            $article = Article::query()
                ->with(['mediaAttachments', 'seoEntry.translations', 'faqs.translations'])
                ->find($articleId);

            if (! $article) {
                return;
            }

            foreach ($article->mediaAttachments as $attachment) {
                $this->support->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($article->seoEntry));

            $this->support->purgeFaqs('article', $articleId);
            $this->support->purgeMediaAttachments('article', $articleId, $mediaIds);
            $this->support->purgeTranslations($article);

            Comment::query()->where('article_id', $articleId)->delete();

            $article->contentTypeTags()->detach();
            $article->keywordTags()->detach();
            $article->relatedPackages()->detach();
            $article->relatedArticles()->detach();
            DB::table('article_related')->where('related_article_id', $articleId)->delete();

            $this->support->purgeSeo($article->seoEntry, $mediaIds);
            $article->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeCountry(Country $country): void
    {
        $countryId = (int) $country->id;

        $packageCount = Package::query()->where('country_id', $countryId)->count();
        if ($packageCount > 0) {
            throw ValidationException::withMessages([
                'id' => "Không thể xóa điểm đến: còn {$packageCount} tour/du thuyền đang gắn. Hãy gỡ hoặc xóa các gói trước.",
            ]);
        }

        $categoryCount = TourCategory::query()->where('country_id', $countryId)->count();
        if ($categoryCount > 0) {
            throw ValidationException::withMessages([
                'id' => "Không thể xóa điểm đến: còn {$categoryCount} danh mục tour đang gắn. Hãy gỡ hoặc xóa danh mục trước.",
            ]);
        }

        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($countryId, &$mediaIds, &$cacheKeys): void {
            $country = Country::query()
                ->with(['mediaAttachments', 'seoEntry.translations', 'faqs.translations'])
                ->find($countryId);

            if (! $country) {
                return;
            }

            $this->support->pushMediaId((int) ($country->banner_media_id ?? 0), $mediaIds);
            $this->support->pushMediaId((int) ($country->listing_banner_media_id ?? 0), $mediaIds);

            foreach ($country->mediaAttachments as $attachment) {
                $this->support->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($country->seoEntry));

            $this->support->purgeFaqs('country', $countryId);
            $this->support->purgeMediaAttachments('country', $countryId, $mediaIds);
            $this->support->purgeTranslations($country);

            // Quan hệ tùy chọn tới trang chi tiết / brand — gỡ FK, không xóa entity.
            Article::query()->where('country_id', $countryId)->update(['country_id' => null]);
            BlogCategory::query()->where('country_id', $countryId)->update(['country_id' => null]);
            Destination::query()->where('country_id', $countryId)->update(['country_id' => null]);
            Office::query()->where('country_id', $countryId)->update(['country_id' => null]);
            ReferencePerson::query()->where('country_id', $countryId)->update(['country_id' => null]);
            ExperienceVideo::query()->where('country_id', $countryId)->update(['country_id' => null]);
            ExperienceAlbum::query()->where('country_id', $countryId)->update(['country_id' => null]);
            Review::query()->where('country_id', $countryId)->update(['country_id' => null]);
            // Lưu trú / vui chơi / other có thể gắn điểm đến; train|ferry|flight đã không dùng.
            Service::query()->where('country_id', $countryId)->update(['country_id' => null]);
            DB::table('package_country')->where('country_id', $countryId)->delete();
            DB::table('home_featured_countries')->where('country_id', $countryId)->delete();
            DB::table('hero_pills')->where('country_id', $countryId)->update(['country_id' => null]);

            $this->support->purgeSeo($country->seoEntry, $mediaIds);
            $country->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeTourCategory(TourCategory $category): void
    {
        $categoryId = (int) $category->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($categoryId, &$mediaIds, &$cacheKeys): void {
            $category = TourCategory::query()
                ->with(['mediaAttachments', 'seoEntry.translations', 'faqs.translations'])
                ->find($categoryId);

            if (! $category) {
                return;
            }

            foreach ($category->mediaAttachments as $attachment) {
                $this->support->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($category->seoEntry));

            $this->support->purgeFaqs('tour_category', $categoryId);
            $this->support->purgeMediaAttachments('tour_category', $categoryId, $mediaIds);
            $this->support->purgeTranslations($category);

            $category->packages()->detach();

            $this->support->purgeSeo($category->seoEntry, $mediaIds);
            $category->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeBlogCategory(BlogCategory $category): void
    {
        $categoryId = (int) $category->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($categoryId, &$mediaIds, &$cacheKeys): void {
            $category = BlogCategory::query()
                ->with(['seoEntry.translations', 'faqs.translations'])
                ->find($categoryId);

            if (! $category) {
                return;
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($category->seoEntry));

            $this->support->purgeFaqs('blog_category', $categoryId);
            $this->support->purgeTranslations($category);

            Article::query()->where('blog_category_id', $categoryId)->update(['blog_category_id' => null]);

            $this->support->purgeSeo($category->seoEntry, $mediaIds);
            $category->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeServiceCategory(ServiceCategory $category): void
    {
        $categoryId = (int) $category->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($categoryId, &$mediaIds, &$cacheKeys): void {
            $category = ServiceCategory::query()
                ->with(['seoEntry.translations'])
                ->find($categoryId);

            if (! $category) {
                return;
            }

            $this->support->pushMediaId((int) ($category->banner_media_id ?? 0), $mediaIds);
            $this->support->pushMediaId((int) ($category->cover_media_id ?? 0), $mediaIds);

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($category->seoEntry));

            $category->services()->detach();
            Service::query()->where('service_category_id', $categoryId)->update(['service_category_id' => null]);

            $this->support->purgeSeo($category->seoEntry, $mediaIds);
            $category->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeCruiseType(CruiseType $type): void
    {
        $slug = (string) $type->slug;
        $linked = Package::query()->where('cruise_type', $slug)->count();
        if ($linked > 0) {
            throw ValidationException::withMessages([
                'id' => "Không thể xóa loại du thuyền: còn {$linked} gói cruise đang gắn.",
            ]);
        }

        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($type, &$mediaIds, &$cacheKeys): void {
            $row = CruiseType::query()->with(['seoEntry.translations'])->find($type->id);
            if (! $row) {
                return;
            }

            $this->support->pushMediaId((int) ($row->banner_media_id ?? 0), $mediaIds);
            $this->support->pushMediaId((int) ($row->cover_media_id ?? 0), $mediaIds);

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($row->seoEntry));
            $this->support->purgeSeo($row->seoEntry, $mediaIds);
            $row->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeTeamMember(TeamMember $member): void
    {
        $memberId = (int) $member->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($memberId, &$mediaIds, &$cacheKeys): void {
            $member = TeamMember::query()
                ->with(['seoEntry.translations', 'activityImages'])
                ->find($memberId);

            if (! $member) {
                return;
            }

            $this->support->pushMediaId((int) ($member->avatar_media_id ?? 0), $mediaIds);
            foreach ($member->activityImages as $img) {
                $this->support->pushMediaId((int) ($img->media_id ?? 0), $mediaIds);
            }

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($member->seoEntry));

            foreach ($member->achievements as $row) {
                $row->delete();
            }
            foreach ($member->skills as $row) {
                $row->delete();
            }
            foreach ($member->experiences as $exp) {
                $exp->items()->delete();
                $exp->delete();
            }
            foreach ($member->degrees as $degree) {
                $degree->items()->delete();
                $degree->delete();
            }
            $member->activityImages()->delete();

            $this->support->purgeTranslations($member);
            $this->support->purgeSeo($member->seoEntry, $mediaIds);

            if (Schema::hasTable('home_featured_team_members')) {
                HomeFeaturedTeamMember::query()->where('team_member_id', $memberId)->delete();
            }

            $member->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeReview(Review $review): void
    {
        $mediaIds = [];
        $reviewId = (int) $review->id;

        DB::transaction(function () use ($reviewId, &$mediaIds): void {
            $review = Review::query()->with('mediaAttachments')->find($reviewId);
            if (! $review) {
                return;
            }

            $this->support->pushMediaId((int) ($review->avatar_media_id ?? 0), $mediaIds);
            foreach ($review->mediaAttachments as $attachment) {
                $this->support->pushMediaId((int) ($attachment->media_id ?? 0), $mediaIds);
            }

            $this->support->purgeMediaAttachments('review', $reviewId, $mediaIds);
            $review->delete();
        });

        $this->support->finish($mediaIds, []);
    }

    public function purgeExperienceAlbum(ExperienceAlbum $album): void
    {
        $albumId = (int) $album->id;
        $mediaIds = [];

        DB::transaction(function () use ($albumId, &$mediaIds): void {
            $album = ExperienceAlbum::query()->with('photos')->find($albumId);
            if (! $album) {
                return;
            }

            $this->support->pushMediaId((int) ($album->cover_media_id ?? 0), $mediaIds);

            ExperienceAlbumPhoto::query()
                ->where('experience_album_id', $albumId)
                ->each(function (ExperienceAlbumPhoto $photo) use (&$mediaIds): void {
                    $this->support->pushMediaId((int) ($photo->media_id ?? 0), $mediaIds);
                    $photo->delete();
                });

            $this->support->purgeTranslations($album);
            $album->delete();
        });

        $this->support->finish($mediaIds, []);
    }

    public function purgeExperienceVideo(ExperienceVideo $video): void
    {
        $mediaIds = [];

        DB::transaction(function () use ($video, &$mediaIds): void {
            $row = ExperienceVideo::query()->find($video->id);
            if (! $row) {
                return;
            }

            $this->support->pushMediaId((int) ($row->thumbnail_media_id ?? 0), $mediaIds);
            $this->support->pushMediaId((int) ($row->video_media_id ?? 0), $mediaIds);

            $this->support->purgeTranslations($row);
            $row->delete();
        });

        $this->support->finish($mediaIds, []);
    }

    public function purgeHomeSlide(HomeSlide $slide): void
    {
        $mediaIds = [];

        DB::transaction(function () use ($slide, &$mediaIds): void {
            $row = HomeSlide::query()->find($slide->id);
            if (! $row) {
                return;
            }

            $this->support->pushMediaId((int) ($row->image_media_id ?? 0), $mediaIds);
            $this->support->pushMediaId((int) ($row->image_mobile_media_id ?? 0), $mediaIds);

            $row->translations()->delete();
            $row->delete();
        });

        $this->support->finish($mediaIds, []);
    }

    public function purgeDestination(Destination $destination): void
    {
        $destinationId = (int) $destination->id;
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($destinationId, &$mediaIds, &$cacheKeys): void {
            $destination = Destination::query()
                ->with(['seoEntry.translations'])
                ->find($destinationId);

            if (! $destination) {
                return;
            }

            $this->support->pushMediaId((int) ($destination->image_media_id ?? 0), $mediaIds);

            $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($destination->seoEntry));

            Article::query()->where('destination_id', $destinationId)->update(['destination_id' => null]);
            BlogCategory::query()->where('destination_id', $destinationId)->update(['destination_id' => null]);

            $this->support->purgeTranslations($destination);
            $this->support->purgeSeo($destination->seoEntry, $mediaIds);

            if (Schema::hasTable('package_destination')) {
                DB::table('package_destination')->where('destination_id', $destinationId)->delete();
            }

            $destination->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }

    public function purgeMedia(Media $media): void
    {
        $this->media->destroyMedia($media);
    }

    public function purgeSimpleTranslatable(Model $model): void
    {
        DB::transaction(function () use ($model): void {
            $fresh = $model->newQuery()->find($model->getKey());
            if (! $fresh) {
                return;
            }

            $this->support->purgeTranslations($fresh);
            $fresh->delete();
        });
    }

    public function purgeGeneric(Model $model): void
    {
        $mediaIds = [];
        $cacheKeys = [];

        DB::transaction(function () use ($model, &$mediaIds, &$cacheKeys): void {
            $fresh = $model->newQuery()->find($model->getKey());
            if (! $fresh) {
                return;
            }

            if (method_exists($fresh, 'translations')) {
                $fresh->translations()->delete();
            }

            if (method_exists($fresh, 'seoEntry') && $fresh->seoEntry) {
                $cacheKeys = array_merge($cacheKeys, $this->support->collectSeoCacheKeys($fresh->seoEntry));
                $this->support->purgeSeo($fresh->seoEntry, $mediaIds);
            }

            $fresh->delete();
        });

        $this->support->finish($mediaIds, $cacheKeys);
    }
}
