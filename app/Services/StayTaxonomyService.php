<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Language;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\StayAmenity;
use App\Models\StayAmenityTranslation;
use App\Models\StayPlace;
use App\Models\StayPlaceTranslation;
use App\Support\StayDistance;
use App\Support\StayFacilities;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Quản lý Tags Tiện ích & Địa danh lân cận cho Khách sạn / Chỗ nghỉ.
 *
 * - findOrCreate: tra cứu case-insensitive, tự tạo mới nếu chưa có.
 * - Group keys linh động: bất kỳ nhóm nào từ crawler đều được lưu nguyên.
 * - sync: đồng bộ quan hệ pivot cho Service (khách sạn) và ServiceOption (hạng phòng).
 */
final class StayTaxonomyService
{
    /** @var array<string, int> Runtime cache: lowercased name -> stay_amenity_id */
    private array $amenityCache = [];

    /** @var array<string, int> Runtime cache: lowercased name -> stay_place_id */
    private array $placeCache = [];

    /**
     * Tìm hoặc tạo mới một Tag Tiện ích.
     * So sánh case-insensitive theo name translation.
     */
    public function findOrCreateAmenity(
        string $name,
        string $groupKey = 'general',
        bool $isHighlight = false,
        string $locale = 'vi',
        ?string $icon = null,
    ): StayAmenity {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Amenity name cannot be empty');
        }

        $cacheKey = mb_strtolower($name) . '|' . $groupKey;
        if (isset($this->amenityCache[$cacheKey])) {
            return StayAmenity::find($this->amenityCache[$cacheKey]);
        }

        $langId = Language::idByCode($locale);
        if (! $langId) {
            throw new \RuntimeException("Language '{$locale}' not found");
        }

        // Tìm kiếm case-insensitive bằng LOWER()
        $existing = StayAmenityTranslation::query()
            ->where('language_id', $langId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            $amenity = $existing->amenity;
            // Cập nhật group_key nếu cần (ưu tiên group cụ thể hơn general)
            if ($amenity->group_key === 'general' && $groupKey !== 'general') {
                $amenity->update(['group_key' => $groupKey]);
            }
            if ($isHighlight && ! $amenity->is_highlight) {
                $amenity->update(['is_highlight' => true]);
            }
            $this->amenityCache[$cacheKey] = $amenity->id;

            return $amenity;
        }

        // Tạo mới
        $amenity = StayAmenity::create([
            'group_key' => $groupKey,
            'icon' => $icon,
            'is_highlight' => $isHighlight,
            'sort' => 0,
        ]);

        StayAmenityTranslation::create([
            'stay_amenity_id' => $amenity->id,
            'language_id' => $langId,
            'name' => $name,
            'slug' => Str::slug($name) ?: Str::slug(Str::ascii($name)) ?: null,
        ]);

        $this->amenityCache[$cacheKey] = $amenity->id;

        return $amenity;
    }

    /**
     * Tìm hoặc tạo mới một Tag Địa danh lân cận.
     * So sánh case-insensitive theo name translation.
     */
    public function findOrCreatePlace(
        string $name,
        string $category = 'landmark',
        string $locale = 'vi',
        ?string $icon = null,
        ?int $projectId = null,
    ): StayPlace {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Place name cannot be empty');
        }

        $cacheKey = mb_strtolower($name) . '|' . $category;
        if (isset($this->placeCache[$cacheKey])) {
            return StayPlace::find($this->placeCache[$cacheKey]);
        }

        $langId = Language::idByCode($locale);
        if (! $langId) {
            throw new \RuntimeException("Language '{$locale}' not found");
        }

        $existing = StayPlaceTranslation::query()
            ->where('language_id', $langId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            $place = $existing->place;
            // Cập nhật category nếu chính xác hơn
            if ($place->category === 'other' && $category !== 'other') {
                $place->update(['category' => $category]);
            }
            $this->placeCache[$cacheKey] = $place->id;

            return $place;
        }

        $place = StayPlace::create([
            'category' => $category,
            'icon' => $icon,
            'project_id' => $projectId,
        ]);

        StayPlaceTranslation::create([
            'stay_place_id' => $place->id,
            'language_id' => $langId,
            'name' => $name,
            'slug' => Str::slug($name) ?: Str::slug(Str::ascii($name)) ?: null,
        ]);

        $this->placeCache[$cacheKey] = $place->id;

        return $place;
    }

    /**
     * Đồng bộ toàn bộ Tags Tiện ích & Địa danh cho 1 khách sạn (Service).
     *
     * @param  array<string, mixed>  $attrs  services.attrs đã normalize
     */
    public function syncServiceTaxonomies(
        Service $service,
        array $attrs,
        string $locale = 'vi',
    ): void {
        DB::transaction(function () use ($service, $attrs, $locale) {
            $this->syncAmenities($service, $attrs, $locale);
            $this->syncPlaces($service, $attrs, $locale);
        });
    }

    /**
     * Đồng bộ tiện ích từ attrs vào pivot stay_amenity_service.
     */
    private function syncAmenities(Service $service, array $attrs, string $locale): void
    {
        $amenityPivot = []; // [amenity_id => ['is_popular' => bool, 'sort' => int]]
        $sort = 0;

        // 1. Highlight badges (popular)
        $highlights = StayFacilities::stringList($attrs['highlight_badges'] ?? null);
        foreach ($highlights as $name) {
            $amenity = $this->findOrCreateAmenity($name, 'popular', true, $locale);
            $amenityPivot[$amenity->id] = ['is_popular' => true, 'sort' => $sort++];
        }

        // 2. Amenity groups (keyed by group name — linh động, bất kỳ key nào đều được)
        $amenityGroups = $attrs['amenity_groups'] ?? [];
        if (is_array($amenityGroups)) {
            foreach ($amenityGroups as $groupKey => $items) {
                $groupKey = (string) $groupKey;
                $list = StayFacilities::stringList($items);
                foreach ($list as $name) {
                    $amenity = $this->findOrCreateAmenity($name, $groupKey, false, $locale);
                    if (! isset($amenityPivot[$amenity->id])) {
                        $amenityPivot[$amenity->id] = ['is_popular' => false, 'sort' => $sort++];
                    }
                }
            }
        }

        // 3. Flat amenities list (catch-all)
        $flatAmenities = StayFacilities::stringList($attrs['amenities'] ?? null);
        foreach ($flatAmenities as $name) {
            $amenity = $this->findOrCreateAmenity($name, 'general', false, $locale);
            if (! isset($amenityPivot[$amenity->id])) {
                $amenityPivot[$amenity->id] = ['is_popular' => false, 'sort' => $sort++];
            }
        }

        // Sync pivot
        $service->stayAmenities()->sync($amenityPivot);
    }

    /**
     * Đồng bộ địa danh lân cận từ attrs vào pivot stay_place_service.
     */
    private function syncPlaces(Service $service, array $attrs, string $locale): void
    {
        $placePivot = []; // [place_id => ['distance_meters' => int, 'sort' => int]]
        $sort = 0;

        $projectId = $service->project_id;
        $nearbyGroups = $attrs['nearby_groups'] ?? [];

        if (is_array($nearbyGroups)) {
            foreach ($nearbyGroups as $category => $items) {
                $category = (string) $category;
                if (! is_array($items)) {
                    continue;
                }
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $name = trim((string) ($item['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $icon = $item['icon'] ?? null;
                    $distanceText = $item['distance'] ?? $item['dist'] ?? '0';
                    $meters = StayDistance::parseMeters($distanceText);

                    $place = $this->findOrCreatePlace($name, $category, $locale, $icon, $projectId);
                    if (! isset($placePivot[$place->id])) {
                        $placePivot[$place->id] = [
                            'distance_meters' => $meters,
                            'sort' => $sort++,
                        ];
                    }
                }
            }
        }

        // Sync pivot
        $service->stayPlaces()->sync($placePivot);
    }

    /**
     * Đồng bộ tiện ích phòng (hạng phòng / ServiceOption) từ attrs.
     *
     * @param  list<string>  $amenityNames
     */
    public function syncOptionAmenities(
        ServiceOption $option,
        array $amenityNames,
        string $locale = 'vi',
    ): void {
        $pivot = [];
        $sort = 0;
        foreach ($amenityNames as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $amenity = $this->findOrCreateAmenity($name, 'room', false, $locale);
            $pivot[$amenity->id] = ['sort' => $sort++];
        }
        $option->stayAmenities()->sync($pivot);
    }

    /**
     * Backfill: quét tất cả Stay services hiện có, trích xuất JSON attrs cũ sang relations mới.
     *
     * @return int  Số lượng service đã xử lý
     */
    public function backfillAll(string $locale = 'vi'): int
    {
        $count = 0;
        Service::withoutGlobalScope('project')
            ->where('cluster', Service::CLUSTER_STAY)
            ->with(['options.translations'])
            ->chunkById(50, function ($services) use ($locale, &$count) {
                foreach ($services as $service) {
                    $attrs = is_array($service->attrs) ? $service->attrs : [];
                    if ($attrs === []) {
                        continue;
                    }
                    $attrs = StayFacilities::normalizeStayAttrs($attrs);
                    $this->syncServiceTaxonomies($service, $attrs, $locale);

                    // Sync room amenities
                    foreach ($service->options as $option) {
                        $optAttrs = is_array($option->attrs) ? $option->attrs : [];
                        $roomAmenities = StayFacilities::stringList($optAttrs['amenities'] ?? null);
                        if ($roomAmenities !== []) {
                            $this->syncOptionAmenities($option, $roomAmenities, $locale);
                        }
                    }

                    $count++;
                }
            });

        return $count;
    }

    /**
     * Lấy dữ liệu public cho tab Tiện ích & Lân cận từ relations (thay vì JSON attrs).
     * Fallback sang attrs nếu relations trống.
     *
     * @return array{
     *   amenityGroups: list<array{key: string, label: string, items: list<string>}>,
     *   nearbyGroups: list<array{key: string, label: string, items: list<array<string, mixed>>}>,
     *   highlightBadges: list<string>,
     * }
     */
    public function resolvePublicData(Service $service, string $locale = 'vi'): array
    {
        $service->loadMissing(['stayAmenities.translations', 'stayPlaces.translations']);

        $amenities = $service->stayAmenities;
        $places = $service->stayPlaces;

        // Nếu chưa có relations (dữ liệu cũ chưa backfill) -> fallback JSON
        if ($amenities->isEmpty() && $places->isEmpty()) {
            return $this->fallbackFromAttrs($service, $locale);
        }

        // Build amenity groups
        $groupLabels = config('stay.amenity_groups', []);
        $nearbyLabels = config('stay.nearby_groups', []);
        $groupedAmenities = [];
        $highlightBadges = [];

        foreach ($amenities as $amenity) {
            $tr = $amenity->translation($locale);
            $name = $tr?->name ?? '';
            if ($name === '') {
                continue;
            }
            $groupKey = $amenity->group_key;
            $groupedAmenities[$groupKey][] = $name;

            if ($amenity->pivot->is_popular || $amenity->is_highlight) {
                $highlightBadges[] = $name;
            }
        }

        $amenityGroups = [];
        // Sorted known groups first, then unknown ones
        $allGroupKeys = array_unique(array_merge(array_keys($groupLabels), array_keys($groupedAmenities)));
        foreach ($allGroupKeys as $key) {
            $items = $groupedAmenities[$key] ?? [];
            if ($items === []) {
                continue;
            }
            $label = $groupLabels[$key]['label']
                ?? ucfirst(str_replace('_', ' ', $key));
            $amenityGroups[] = [
                'key' => $key,
                'label' => $label,
                'items' => array_values(array_unique($items)),
            ];
        }

        // Build nearby groups
        $groupedPlaces = [];
        foreach ($places as $place) {
            $tr = $place->translation($locale);
            $name = $tr?->name ?? '';
            if ($name === '') {
                continue;
            }
            $category = $place->category;
            $meters = (int) ($place->pivot->distance_meters ?? 0);

            $groupedPlaces[$category][] = [
                'name' => $name,
                'distance' => StayDistance::format($meters, $locale),
                'distance_meters' => $meters,
                'icon' => $place->icon ?: $this->defaultPlaceIcon($category),
                'category' => $category,
            ];
        }

        $nearbyGroups = [];
        $allPlaceKeys = array_unique(array_merge(array_keys($nearbyLabels), array_keys($groupedPlaces)));
        foreach ($allPlaceKeys as $key) {
            $items = $groupedPlaces[$key] ?? [];
            if ($items === []) {
                continue;
            }
            // Sort by distance within each group
            usort($items, fn ($a, $b) => ($a['distance_meters'] ?? 0) <=> ($b['distance_meters'] ?? 0));
            $nearbyGroups[] = [
                'key' => $key,
                'label' => $nearbyLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                'items' => $items,
            ];
        }

        return [
            'amenityGroups' => $amenityGroups,
            'nearbyGroups' => $nearbyGroups,
            'highlightBadges' => array_values(array_unique($highlightBadges)),
        ];
    }

    /**
     * Fallback: đọc từ JSON attrs nếu relations chưa có.
     */
    private function fallbackFromAttrs(Service $service, string $locale): array
    {
        $attrs = is_array($service->attrs) ? $service->attrs : [];
        $sections = StayFacilities::resolvePublicSections($attrs);

        return [
            'amenityGroups' => $sections['amenityGroups'] ?? [],
            'nearbyGroups' => $sections['nearbyGroups'] ?? [],
            'highlightBadges' => StayFacilities::stringList($attrs['highlight_badges'] ?? null),
        ];
    }

    private function defaultPlaceIcon(string $category): string
    {
        return match ($category) {
            'beach' => 'waves',
            'landmark' => 'compass',
            'nature' => 'tent',
            'dining' => 'utensils',
            'transport' => 'plane',
            default => 'map-pin',
        };
    }
}
