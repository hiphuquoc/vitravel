<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Language;
use App\Models\NavigationItem;
use App\Models\NavigationItemTranslation;
use App\Support\LocaleContent;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class NavigationMenuService
{
    /** @var array<string, array<int, array<string, mixed>>>|null */
    private ?array $resolvedCache = null;

    public function isCustomized(): bool
    {
        if (! Schema::hasTable('navigation_items')) {
            return false;
        }

        return NavigationItem::query()->exists();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function itemsForZone(string $zone, ?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $all = $this->resolvedItems($locale);

        return array_values(array_filter(
            $all,
            static fn (array $row): bool => ($row['zone'] ?? '') === $zone && ($row['is_active'] ?? true),
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mainBarItems(?string $locale = null): array
    {
        return array_values(array_filter(
            $this->itemsForZone(NavigationItem::ZONE_MAIN, $locale),
            static function (array $row): bool {
                if (($row['kind'] ?? '') !== NavigationItem::KIND_SERVICE_CLUSTER) {
                    return true;
                }

                return ($row['show_in_main_bar'] ?? true) !== false;
            },
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serviceClusters(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $out = [];

        foreach ($this->itemsForZone(NavigationItem::ZONE_MAIN, $locale) as $row) {
            if (($row['kind'] ?? '') !== NavigationItem::KIND_SERVICE_CLUSTER) {
                continue;
            }

            $code = (string) ($row['reference'] ?? '');
            if ($code === '') {
                continue;
            }

            $catalog = config('services_catalog.clusters.'.$code, []);
            $out[] = [
                'code' => $code,
                'nav_label' => (string) ($row['label'] ?? $code),
                'label' => (string) ($row['meta'] ?? ($catalog['label'] ?? $code)),
                'lead_label' => (string) ($row['lead_label'] ?? ''),
                'icon' => (string) ($catalog['icon'] ?? 'sparkles'),
                'hub_key' => (string) ($catalog['hub_key'] ?? ''),
                'sort' => (int) ($row['sort'] ?? 0),
                'show_in_main_bar' => ($row['show_in_main_bar'] ?? true) !== false,
            ];
        }

        if ($out === []) {
            return $this->legacyServiceClusters($locale);
        }

        usort($out, static fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $out;
    }

    /**
     * @return array{
     *   brand: string,
     *   tagline: string,
     *   about_group: string,
     *   tours: array{label: string, lead_label: string, meta: string},
     *   cruise: array{label: string, all_label: string, all_meta: string, search_hint: string, search_placeholder: string, hub_title: string, hub_subtitle: string}
     * }
     */
    public function siteNavBundle(string $brand, string $tagline, ?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $seed = is_array(ProjectSeed::get('nav', [])) ? ProjectSeed::get('nav', []) : [];
        $cruiseSeed = is_array($seed['cruise'] ?? null) ? $seed['cruise'] : [];

        $pickSeed = static function (mixed $val, string $fallback) use ($locale): string {
            if (is_string($val) && $val !== '') {
                return $val;
            }
            if (is_array($val)) {
                $picked = LocaleContent::pick($val, $locale, null);
                if (is_string($picked) && $picked !== '') {
                    return $picked;
                }
            }

            return $fallback;
        };

        $toursItem = $this->findItem(NavigationItem::ZONE_MAIN, NavigationItem::KIND_TOURS_MENU, 'tours', $locale);
        $cruiseItem = $this->findItem(NavigationItem::ZONE_MAIN, NavigationItem::KIND_CRUISE_MENU, 'cruise', $locale);
        $aboutHeading = $this->findItem(NavigationItem::ZONE_MORE, NavigationItem::KIND_HEADING, 'about_group', $locale);

        return [
            'brand' => $brand,
            'tagline' => $tagline,
            'about_group' => (string) ($aboutHeading['label'] ?? $pickSeed($seed['about_group'] ?? null, 'Về '.$brand)),
            'tours' => [
                'label' => (string) ($toursItem['label'] ?? $pickSeed($seed['tours']['label'] ?? null, 'Tour trọn gói')),
                'lead_label' => (string) ($toursItem['lead_label'] ?? 'Tất cả tour'),
                'meta' => (string) ($toursItem['meta'] ?? 'Xem toàn bộ hành trình'),
            ],
            'cruise' => [
                'label' => (string) ($cruiseItem['label'] ?? $pickSeed($cruiseSeed['label'] ?? null, 'Du thuyền')),
                'all_label' => (string) ($cruiseItem['lead_label'] ?? $pickSeed($cruiseSeed['all_label'] ?? null, 'Tất cả du thuyền')),
                'all_meta' => (string) ($cruiseItem['meta'] ?? $pickSeed($cruiseSeed['all_meta'] ?? null, 'Xem toàn bộ lịch trình du thuyền')),
                'search_hint' => $pickSeed($cruiseSeed['search_hint'] ?? null, 'Tour, điểm đến, du thuyền, cẩm nang…'),
                'search_placeholder' => $pickSeed(
                    $cruiseSeed['search_placeholder'] ?? null,
                    'Tìm tour, điểm đến, du thuyền, bài viết…'
                ),
                'hub_title' => $pickSeed($cruiseSeed['hub_title'] ?? $cruiseSeed['label'] ?? null, 'Du thuyền'),
                'hub_subtitle' => $pickSeed($cruiseSeed['hub_subtitle'] ?? null, 'Chọn lịch trình trên mặt nước phù hợp với bạn'),
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function ctaItem(?string $locale = null): ?array
    {
        $items = $this->itemsForZone(NavigationItem::ZONE_CTA, $locale);

        return $items[0] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function moreMenuItems(?string $locale = null): array
    {
        return $this->itemsForZone(NavigationItem::ZONE_MORE, $locale);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function adminPayload(?string $locale = null): array
    {
        $locale = $locale ?: 'vi';
        $defaults = $this->defaultMenuShape();
        $customized = $this->isCustomized();

        $dbItems = $customized
            ? NavigationItem::query()->with('translations')->orderBy('zone')->orderBy('sort')->get()
            : collect();

        $zones = [NavigationItem::ZONE_MAIN, NavigationItem::ZONE_MORE, NavigationItem::ZONE_CTA];
        $out = [];

        foreach ($zones as $zone) {
            if ($customized) {
                $rows = $dbItems->where('zone', $zone)->values();
                foreach ($rows as $item) {
                    $out[] = $this->mapAdminItem($item, $locale);
                }
            } else {
                foreach ($shape[$zone] ?? [] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $public = $this->mapPublicDefaultRow($zone, $row, $locale);
                    $out[] = array_merge($public, [
                        'id' => null,
                        'kind_label' => NavigationItem::kindLabels()[$public['kind']] ?? $public['kind'],
                        'config' => ['show_in_main_bar' => $public['show_in_main_bar']],
                    ]);
                }
            }
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function saveAdminPayload(array $items, string $locale): void
    {
        if (! Schema::hasTable('navigation_items')) {
            return;
        }

        $projectId = ProjectContext::id();
        $languageId = Language::idByCode($locale);
        if (! $projectId || ! $languageId) {
            return;
        }

        DB::transaction(function () use ($items, $projectId, $languageId, $locale): void {
            $existing = NavigationItem::query()->get()->keyBy(
                static fn (NavigationItem $row): string => $row->zone.'|'.$row->item_key,
            );

            $seen = [];
            foreach ($items as $index => $payload) {
                $zone = (string) ($payload['zone'] ?? '');
                $key = (string) ($payload['item_key'] ?? '');
                if ($zone === '' || $key === '') {
                    continue;
                }

                $mapKey = $zone.'|'.$key;
                $seen[$mapKey] = true;

                /** @var NavigationItem $item */
                $item = $existing->get($mapKey) ?? new NavigationItem([
                    'project_id' => $projectId,
                    'zone' => $zone,
                    'item_key' => $key,
                ]);

                $config = is_array($payload['config'] ?? null) ? $payload['config'] : [];
                if (array_key_exists('show_in_main_bar', $payload)) {
                    $config['show_in_main_bar'] = (bool) $payload['show_in_main_bar'];
                }

                $item->fill([
                    'project_id' => $projectId,
                    'zone' => $zone,
                    'kind' => (string) ($payload['kind'] ?? NavigationItem::KIND_ROUTE_LINK),
                    'item_key' => $key,
                    'reference' => filled($payload['reference'] ?? null) ? (string) $payload['reference'] : null,
                    'config' => $config,
                    'sort' => (int) ($payload['sort'] ?? ($index + 1)),
                    'is_active' => (bool) ($payload['is_active'] ?? true),
                ]);
                $item->save();

                NavigationItemTranslation::query()->updateOrCreate(
                    [
                        'navigation_item_id' => $item->id,
                        'language_id' => $languageId,
                    ],
                    [
                        'project_id' => $projectId,
                        'label' => (string) ($payload['label'] ?? ''),
                        'lead_label' => filled($payload['lead_label'] ?? null) ? (string) $payload['lead_label'] : null,
                        'meta' => filled($payload['meta'] ?? null) ? (string) $payload['meta'] : null,
                    ],
                );
            }

            foreach ($existing as $mapKey => $item) {
                if (! isset($seen[$mapKey])) {
                    $item->delete();
                }
            }

            $this->resolvedCache = null;
        });
    }

    public function resetToDefaults(): void
    {
        if (! Schema::hasTable('navigation_items')) {
            return;
        }

        NavigationItem::query()->delete();
        $this->resolvedCache = null;
    }

    /**
     * @return array{main: list<array<string, mixed>>, more: list<array<string, mixed>>, cta: list<array<string, mixed>>}
     */
    public function defaultMenuShape(): array
    {
        $fromSeed = ProjectSeed::get('nav_menu');
        if (is_array($fromSeed) && $fromSeed !== []) {
            return [
                'main' => array_values(is_array($fromSeed['main'] ?? null) ? $fromSeed['main'] : []),
                'more' => array_values(is_array($fromSeed['more'] ?? null) ? $fromSeed['more'] : []),
                'cta' => array_values(is_array($fromSeed['cta'] ?? null) ? $fromSeed['cta'] : []),
            ];
        }

        $helper = base_path('project/includes/nav_menu.php');
        if (is_file($helper)) {
            require_once $helper;

            return vitravel_build_nav_menu(ProjectSeed::all());
        }

        return ['main' => [], 'more' => [], 'cta' => []];
    }

    /** @return list<array<string, mixed>> */
    private function resolvedItems(string $locale): array
    {
        if ($this->resolvedCache !== null && isset($this->resolvedCache[$locale])) {
            return $this->resolvedCache[$locale];
        }

        if ($this->isCustomized()) {
            $items = NavigationItem::query()
                ->with('translations')
                ->orderBy('sort')
                ->get()
                ->map(fn (NavigationItem $item) => $this->mapPublicItem($item, $locale))
                ->values()
                ->all();
        } else {
            $items = $this->defaultResolvedItems($locale);
        }

        $this->resolvedCache ??= [];
        $this->resolvedCache[$locale] = $items;

        return $items;
    }

    /** @return list<array<string, mixed>> */
    private function defaultResolvedItems(string $locale): array
    {
        $shape = $this->defaultMenuShape();
        $out = [];

        foreach ([NavigationItem::ZONE_MAIN, NavigationItem::ZONE_MORE, NavigationItem::ZONE_CTA] as $zone) {
            foreach ($shape[$zone] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $out[] = $this->mapPublicDefaultRow($zone, $row, $locale);
            }
        }

        usort($out, static fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $out;
    }

    /** @param  array<string, mixed>  $row */
    private function mapPublicDefaultRow(string $zone, array $row, string $locale): array
    {
        $pick = static function (?array $val, string $fallback = '') use ($locale): string {
            if ($val === null) {
                return $fallback;
            }
            $picked = LocaleContent::pick($val, $locale, null);

            return is_string($picked) && $picked !== '' ? $picked : $fallback;
        };

        return [
            'zone' => $zone,
            'kind' => (string) ($row['kind'] ?? ''),
            'item_key' => (string) ($row['key'] ?? ''),
            'reference' => filled($row['reference'] ?? null) ? (string) $row['reference'] : null,
            'sort' => (int) ($row['sort'] ?? 0),
            'is_active' => ($row['is_active'] ?? true) !== false,
            'show_in_main_bar' => ($row['show_in_main_bar'] ?? true) !== false,
            'label' => $pick(is_array($row['label'] ?? null) ? $row['label'] : ['vi' => (string) ($row['label'] ?? '')], (string) ($row['label'] ?? '')),
            'lead_label' => $pick(is_array($row['lead_label'] ?? null) ? $row['lead_label'] : null, ''),
            'meta' => $pick(is_array($row['meta'] ?? null) ? $row['meta'] : null, ''),
        ];
    }

    private function mapPublicItem(NavigationItem $item, string $locale): array
    {
        $t = $item->translation($locale);

        return [
            'zone' => $item->zone,
            'kind' => $item->kind,
            'item_key' => $item->item_key,
            'reference' => $item->reference,
            'sort' => $item->sort,
            'is_active' => $item->is_active,
            'show_in_main_bar' => $item->showInMainBar(),
            'label' => (string) ($t?->label ?? ''),
            'lead_label' => (string) ($t?->lead_label ?? ''),
            'meta' => (string) ($t?->meta ?? ''),
        ];
    }

    /** @return array<string, mixed>|null */
    private function findItem(string $zone, string $kind, string $key, string $locale): ?array
    {
        foreach ($this->resolvedItems($locale) as $row) {
            if (($row['zone'] ?? '') === $zone
                && ($row['kind'] ?? '') === $kind
                && ($row['item_key'] ?? '') === $key) {
                return $row;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    private function legacyServiceClusters(string $locale): array
    {
        $seed = ProjectSeed::get('service_clusters', []);
        if (! is_array($seed) || $seed === []) {
            $out = [];
            foreach (config('services_catalog.clusters', []) as $code => $cfg) {
                $out[] = [
                    'code' => $code,
                    'nav_label' => $cfg['nav_label'] ?? $code,
                    'label' => $cfg['label'] ?? $code,
                    'lead_label' => '',
                    'icon' => $cfg['icon'] ?? 'sparkles',
                    'hub_key' => $cfg['hub_key'] ?? null,
                    'sort' => $cfg['sort'] ?? 0,
                    'show_in_main_bar' => ! in_array($code, ['train', 'flight'], true),
                ];
            }
            usort($out, static fn ($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

            return $out;
        }

        return array_values(array_map(static function (array $row) use ($locale): array {
            $navLabel = $row['nav_label'] ?? '';
            if (is_array($navLabel)) {
                $picked = LocaleContent::pick($navLabel, $locale, null);
                $navLabel = is_string($picked) ? $picked : '';
            }

            return [
                'code' => (string) ($row['code'] ?? ''),
                'nav_label' => (string) $navLabel,
                'label' => (string) ($row['label'] ?? ''),
                'lead_label' => '',
                'icon' => (string) ($row['icon'] ?? 'sparkles'),
                'hub_key' => (string) ($row['hub_key'] ?? ''),
                'sort' => (int) ($row['sort'] ?? 0),
                'show_in_main_bar' => ! in_array((string) ($row['code'] ?? ''), ['train', 'flight'], true),
            ];
        }, $seed));
    }

    private function mapAdminItem(NavigationItem $item, string $locale): array
    {
        $t = $item->translation($locale);

        return [
            'id' => $item->id,
            'zone' => $item->zone,
            'kind' => $item->kind,
            'kind_label' => $item->kindLabel(),
            'item_key' => $item->item_key,
            'reference' => $item->reference,
            'sort' => $item->sort,
            'is_active' => $item->is_active,
            'show_in_main_bar' => $item->showInMainBar(),
            'label' => $t?->label,
            'lead_label' => $t?->lead_label,
            'meta' => $t?->meta,
            'config' => $item->config ?? [],
        ];
    }
}
