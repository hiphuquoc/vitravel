<?php

declare(strict_types=1);

namespace App\Support\StayCatalog;

/**
 * Nhóm filter Booking: tạo trang SEO / list-crawl / bỏ.
 *
 * @phpstan-type Item array{
 *   group: string,
 *   nflt: string,
 *   name: string,
 *   label: string,
 *   count: int,
 *   filter_url: string,
 *   expanded?: bool,
 *   policy: string,
 *   create_page_default: bool,
 *   crawl_default: bool
 * }
 */
final class StayFilterPolicy
{
    public const SEO_AND_CRAWL = 'seo_and_crawl';

    public const CRAWL_ONLY = 'crawl_only';

    public const IGNORE = 'ignore';

    public const UNKNOWN = 'unknown';

    /** @var list<string> */
    private const SEO_GROUPS = ['ht_id', 'di', 'stay_type', 'ht_beach', 'popular_nearby_landmarks'];

    /** @var list<string> */
    private const CRAWL_GROUPS = ['ht_id', 'di', 'stay_type', 'ht_beach', 'popular_nearby_landmarks', 'hotelfacility', 'class', 'popular_activities'];

    /** @var list<string> */
    private const IGNORE_GROUPS = [
        'price', 'distance', 'used_filters', 'popular', 'mealplan', 'roomfacility',
        'tdb', 'unit_config_grouped', 'review_score', 'rated_high', 'chaincode',
        'accessible_facilities', 'accessible_room_facilities', 'SustainablePropertyLevelFilter', 'fc',
    ];

    public static function forGroup(string $group): string
    {
        $group = trim($group);
        if ($group === '') {
            return self::UNKNOWN;
        }
        if (in_array($group, self::IGNORE_GROUPS, true)) {
            return self::IGNORE;
        }
        if (in_array($group, self::SEO_GROUPS, true)) {
            return self::SEO_AND_CRAWL;
        }
        if (in_array($group, self::CRAWL_GROUPS, true)) {
            return self::CRAWL_ONLY;
        }

        return self::UNKNOWN;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return Item
     */
    public static function decorate(array $item, int $landmarkMinCount = 5): array
    {
        $group = (string) ($item['group'] ?? '');
        $policy = self::forGroup($group);
        $count = (int) ($item['count'] ?? 0);
        $create = $policy === self::SEO_AND_CRAWL;
        if ($group === 'popular_nearby_landmarks' && $count < $landmarkMinCount) {
            $create = false;
        }
        if ($group === 'hotelfacility') {
            $create = self::facilityCreatesSeoPage((string) ($item['label'] ?? ''));
        }
        if ($group === 'class') {
            $create = false;
        }
        $crawl = in_array($policy, [self::SEO_AND_CRAWL, self::CRAWL_ONLY], true);

        $nflt = (string) ($item['nflt'] ?? $item['name'] ?? '');

        return [
            'group' => $group,
            'nflt' => $nflt,
            'name' => (string) ($item['name'] ?? $nflt),
            'label' => (string) ($item['label'] ?? $nflt),
            'count' => $count,
            'filter_url' => (string) ($item['filter_url'] ?? ''),
            'expanded' => (bool) ($item['expanded'] ?? false),
            'policy' => $policy,
            'create_page_default' => $create,
            'crawl_default' => $crawl,
        ];
    }

    /**
     * Allowlist trang SEO tiện nghi — map theo nhãn đã fold, không hardcode id Booking.
     */
    public static function facilityCreatesSeoPage(string $label): bool
    {
        $fold = StayText::fold($label);
        if ($fold === '') {
            return false;
        }
        $needles = config('stay.catalog.facility_seo_labels', [
            'beachfront', 'bai bien', 'ho boi', 'pool', 'spa',
            'gia dinh', 'family', 'tre em', 'sea view', 'nhin ra bien', 'view bien',
        ]);
        foreach (is_array($needles) ? $needles : [] as $needle) {
            $n = StayText::fold((string) $needle);
            if ($n !== '' && str_contains($fold, $n)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<Item>  $items
     * @return list<Item>
     */
    public static function dedupeStayTypePages(array $items): array
    {
        $htLabels = [];
        foreach ($items as $item) {
            if (($item['group'] ?? '') === 'ht_id') {
                $htLabels[StayText::fold((string) ($item['label'] ?? ''))] = true;
            }
        }
        foreach ($items as $i => $item) {
            if (($item['group'] ?? '') !== 'stay_type') {
                continue;
            }
            $fold = StayText::fold((string) ($item['label'] ?? ''));
            if ($fold !== '' && isset($htLabels[$fold])) {
                $items[$i]['create_page_default'] = false;
            }
        }

        return $items;
    }
}
