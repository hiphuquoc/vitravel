<?php

declare(strict_types=1);

namespace App\Services\Purge;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Package;
use App\Models\PriceGuestType;
use App\Models\PriceRate;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TourCategory;
use App\Models\TravelStyle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Liệt kê trang/entity đang liên kết trước khi xóa — dùng cho modal xác nhận admin.
 */
final class EntityDeleteImpactService
{
    private const ITEM_LIMIT = 40;

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forModel(Model $model, string $locale = 'vi'): array
    {
        return match (true) {
            $model instanceof Country => $this->forCountry($model, $locale),
            $model instanceof CruiseType => $this->forCruiseType($model, $locale),
            $model instanceof TourCategory => $this->forTourCategory($model, $locale),
            $model instanceof ServiceCategory => $this->forServiceCategory($model, $locale),
            $model instanceof BlogCategory => $this->forBlogCategory($model, $locale),
            $model instanceof PriceGuestType => $this->forPriceGuestType($model, $locale),
            $model instanceof TravelStyle => $this->forTravelStyle($model, $locale),
            default => [
                'linked_count' => 0,
                'groups' => [],
                'warning' => 'Xóa mục này? Thao tác không hoàn tác được.',
            ],
        };
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forCountry(Country $country, string $locale = 'vi'): array
    {
        $id = (int) $country->id;

        $primaryPackages = Package::query()
            ->with('translations')
            ->where('country_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $pivotPackageIds = DB::table('package_country')
            ->where('country_id', $id)
            ->pluck('package_id')
            ->map(fn ($v) => (int) $v)
            ->all();
        $primaryIds = $primaryPackages->pluck('id')->map(fn ($v) => (int) $v)->all();
        $extraPivotIds = array_values(array_diff($pivotPackageIds, $primaryIds));

        $pivotPackages = $extraPivotIds === []
            ? collect()
            : Package::query()
                ->with('translations')
                ->whereIn('id', array_slice($extraPivotIds, 0, self::ITEM_LIMIT))
                ->orderByDesc('id')
                ->get();

        $categories = TourCategory::query()
            ->with('translations')
            ->where('country_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $services = Service::query()
            ->with('translations')
            ->where('country_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $articles = Article::query()
            ->with('translations')
            ->where('country_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $blogCategories = BlogCategory::query()
            ->with('translations')
            ->where('country_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $groups = array_values(array_filter([
            $this->group(
                'packages_primary',
                'Tour / du thuyền (điểm đến chính)',
                'Gỡ điểm đến chính khỏi các gói này',
                $primaryPackages->map(fn (Package $p) => $this->packageItem($p, $locale)),
                Package::query()->where('country_id', $id)->count(),
            ),
            $this->group(
                'packages_pivot',
                'Tour / du thuyền (điểm đến phụ)',
                'Gỡ khỏi danh sách điểm đến phụ',
                $pivotPackages->map(fn (Package $p) => $this->packageItem($p, $locale)),
                count($extraPivotIds),
            ),
            $this->group(
                'tour_categories',
                'Danh mục tour',
                'Gỡ điểm đến khỏi danh mục',
                $categories->map(fn (TourCategory $c) => $this->item(
                    (int) $c->id,
                    (string) ($c->translation($locale)?->name ?: "#{$c->id}"),
                    'tour_category',
                    "/tours/categories/form/?id={$c->id}",
                    $c->translation($locale)?->slug,
                )),
                TourCategory::query()->where('country_id', $id)->count(),
            ),
            $this->group(
                'services',
                'Dịch vụ / lưu trú',
                'Gỡ điểm đến khỏi dịch vụ',
                $services->map(fn (Service $s) => $this->serviceItem($s, $locale)),
                Service::query()->where('country_id', $id)->count(),
            ),
            $this->group(
                'articles',
                'Bài viết',
                'Gỡ điểm đến khỏi bài viết',
                $articles->map(fn (Article $a) => $this->articleItem($a, $locale)),
                Article::query()->where('country_id', $id)->count(),
            ),
            $this->group(
                'blog_categories',
                'Danh mục blog',
                'Gỡ điểm đến khỏi danh mục blog',
                $blogCategories->map(fn (BlogCategory $c) => $this->item(
                    (int) $c->id,
                    (string) ($c->translation($locale)?->name ?: "#{$c->id}"),
                    'blog_category',
                    "/content/blog-categories/form/?id={$c->id}",
                    $c->translation($locale)?->slug,
                )),
                BlogCategory::query()->where('country_id', $id)->count(),
            ),
        ]));

        return $this->payload($groups, 'điểm đến / khu vực');
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forCruiseType(CruiseType $type, string $locale = 'vi'): array
    {
        $slug = (string) $type->slug;
        $packages = Package::query()
            ->with('translations')
            ->where('cruise_type', $slug)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $groups = array_values(array_filter([
            $this->group(
                'packages',
                'Du thuyền đang gắn loại này',
                'Gỡ loại du thuyền khỏi các gói (không xóa gói)',
                $packages->map(fn (Package $p) => $this->packageItem($p, $locale)),
                Package::query()->where('cruise_type', $slug)->count(),
            ),
        ]));

        return $this->payload($groups, 'danh mục du thuyền');
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forTourCategory(TourCategory $category, string $locale = 'vi'): array
    {
        $id = (int) $category->id;
        $packages = $category->packages()
            ->with('translations')
            ->orderByDesc('packages.id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $groups = array_values(array_filter([
            $this->group(
                'packages',
                'Tour đang gắn danh mục',
                'Gỡ liên kết danh mục khỏi các gói (không xóa gói)',
                $packages->map(fn (Package $p) => $this->packageItem($p, $locale)),
                $category->packages()->count(),
            ),
        ]));

        return $this->payload($groups, 'danh mục tour');
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forServiceCategory(ServiceCategory $category, string $locale = 'vi'): array
    {
        $id = (int) $category->id;

        $pivotServices = $category->services()
            ->with('translations')
            ->orderByDesc('services.id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $directServices = Service::query()
            ->with('translations')
            ->where('service_category_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $pivotIds = $pivotServices->pluck('id')->map(fn ($v) => (int) $v)->all();
        $directOnly = $directServices->filter(fn (Service $s) => ! in_array((int) $s->id, $pivotIds, true));

        $merged = $pivotServices->concat($directOnly)->take(self::ITEM_LIMIT);
        $allPivotIds = $category->services()->pluck('services.id')->map(fn ($v) => (int) $v)->all();
        $allDirectIds = Service::query()->where('service_category_id', $id)->pluck('id')->map(fn ($v) => (int) $v)->all();
        $total = count(array_unique(array_merge($allPivotIds, $allDirectIds)));

        $groups = array_values(array_filter([
            $this->group(
                'services',
                'Dịch vụ đang gắn danh mục',
                'Gỡ liên kết danh mục khỏi các dịch vụ (không xóa dịch vụ)',
                $merged->map(fn (Service $s) => $this->serviceItem($s, $locale)),
                $total,
            ),
        ]));

        return $this->payload($groups, 'danh mục dịch vụ');
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forBlogCategory(BlogCategory $category, string $locale = 'vi'): array
    {
        $id = (int) $category->id;
        $articles = Article::query()
            ->with('translations')
            ->where('blog_category_id', $id)
            ->orderByDesc('id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $groups = array_values(array_filter([
            $this->group(
                'articles',
                'Bài viết đang gắn danh mục',
                'Gỡ danh mục khỏi bài viết (không xóa bài)',
                $articles->map(fn (Article $a) => $this->articleItem($a, $locale)),
                Article::query()->where('blog_category_id', $id)->count(),
            ),
        ]));

        return $this->payload($groups, 'danh mục blog');
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forPriceGuestType(PriceGuestType $type, string $locale = 'vi'): array
    {
        $id = (int) $type->id;
        $rates = PriceRate::query()
            ->with(['period.table.priceable'])
            ->where('guest_type_id', $id)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        /** @var Collection<int, array<string, mixed>> $byPriceable */
        $byPriceable = collect();
        foreach ($rates as $rate) {
            $table = $rate->period?->table;
            $priceable = $table?->priceable;
            if (! $priceable instanceof Model) {
                continue;
            }
            $key = $table->priceable_type.':'.$table->priceable_id;
            if ($byPriceable->has($key)) {
                continue;
            }
            if ($priceable instanceof Package) {
                $priceable->loadMissing('translations');
                $byPriceable->put($key, $this->packageItem($priceable, $locale));
            } elseif ($priceable instanceof Service) {
                $priceable->loadMissing('translations');
                $byPriceable->put($key, $this->serviceItem($priceable, $locale));
            } else {
                $byPriceable->put($key, $this->item(
                    (int) $priceable->getKey(),
                    class_basename($priceable).' #'.$priceable->getKey(),
                    'priceable',
                    null,
                ));
            }
        }

        $items = $byPriceable->take(self::ITEM_LIMIT)->values();
        $rateCount = PriceRate::query()->where('guest_type_id', $id)->count();

        $groups = array_values(array_filter([
            $this->group(
                'priceables',
                'Chương trình / dịch vụ đang dùng đối tượng này trong bảng giá',
                "Xóa {$rateCount} dòng giá gắn đối tượng khách này",
                $items,
                $byPriceable->count(),
            ),
        ]));

        return $this->payload($groups, 'đối tượng khách');
    }

    /** @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string} */
    public function forTravelStyle(TravelStyle $style, string $locale = 'vi'): array
    {
        $packages = $style->packages()
            ->with('translations')
            ->orderByDesc('packages.id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $groups = array_values(array_filter([
            $this->group(
                'packages',
                'Tour / du thuyền đang gắn phong cách',
                'Gỡ liên kết phong cách khỏi các gói (không xóa gói)',
                $packages->map(fn (Package $p) => $this->packageItem($p, $locale)),
                $style->packages()->count(),
            ),
        ]));

        return $this->payload($groups, 'phong cách du lịch');
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return array{linked_count: int, groups: list<array<string, mixed>>, warning: string}
     */
    private function payload(array $groups, string $entityLabel): array
    {
        $linkedCount = array_sum(array_map(fn (array $g) => (int) ($g['total'] ?? 0), $groups));

        $warning = $linkedCount > 0
            ? "Có {$linkedCount} mục đang liên kết tới {$entityLabel}. Xác nhận sẽ gỡ sạch quan hệ rồi xóa."
            : "Không có trang liên kết. Xóa {$entityLabel}? Thao tác không hoàn tác được.";

        return [
            'linked_count' => $linkedCount,
            'groups' => $groups,
            'warning' => $warning,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>|\Illuminate\Support\Enumerable  $items
     * @return array<string, mixed>|null
     */
    private function group(string $key, string $label, string $actionHint, $items, int $total): ?array
    {
        if ($total <= 0) {
            return null;
        }

        $list = $items instanceof Collection ? $items->values()->all() : array_values(iterator_to_array($items));

        return [
            'key' => $key,
            'label' => $label,
            'action_hint' => $actionHint,
            'total' => $total,
            'items' => $list,
        ];
    }

    /** @return array<string, mixed> */
    private function item(int $id, string $title, string $type, ?string $adminHref, ?string $slug = null): array
    {
        return [
            'id' => $id,
            'title' => $title !== '' ? $title : "#{$id}",
            'type' => $type,
            'admin_href' => $adminHref,
            'slug' => $slug,
        ];
    }

    /** @return array<string, mixed> */
    private function packageItem(Package $package, string $locale): array
    {
        $title = (string) ($package->translation($locale)?->title ?: $package->code ?: "#{$package->id}");
        $isCruise = $package->type === Package::TYPE_CRUISE;
        $href = $isCruise
            ? "/cruises/packages/form/?id={$package->id}"
            : "/tours/packages/form/?id={$package->id}";

        return $this->item(
            (int) $package->id,
            $title,
            $isCruise ? 'cruise' : 'tour',
            $href,
            $package->seoEntry?->translation($locale)?->slug
                ?? $package->translation($locale)?->slug
                ?? null,
        );
    }

    /** @return array<string, mixed> */
    private function serviceItem(Service $service, string $locale): array
    {
        $title = (string) ($service->translation($locale)?->title ?: "#{$service->id}");
        $cluster = (string) ($service->cluster ?: 'stay');
        $href = "/services/products/form/?id={$service->id}&cluster={$cluster}";

        return $this->item(
            (int) $service->id,
            $title,
            'service',
            $href,
            $service->seoEntry?->translation($locale)?->slug,
        );
    }

    /** @return array<string, mixed> */
    private function articleItem(Article $article, string $locale): array
    {
        $title = (string) ($article->translation($locale)?->title ?: "#{$article->id}");

        return $this->item(
            (int) $article->id,
            $title,
            'article',
            "/content/articles/form/?id={$article->id}",
            $article->seoEntry?->translation($locale)?->slug,
        );
    }
}
