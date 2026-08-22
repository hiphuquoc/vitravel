<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Support\StayBookingUrl;
use App\Support\StayFacilities;
use App\Support\StaySeed;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

/**
 * Tách schema chỗ nghỉ từ HTML Booking.com (không AI).
 * Selector dựa trên dump trang chi tiết (PropertyHeaderName, facilities, rooms_table…).
 */
final class StayHtmlMapper
{
    public const VERSION = 8;

    /**
     * @param  array<string, mixed>  $extracted  kết quả StayHtmlExtractor
     * @param  array<string, mixed>  $pack  overlay từ Chrome (không phụ thuộc HTML cắt)
     * @return array<string, mixed>
     */
    public function map(string $html, string $sourceUrl, array $extracted = [], array $pack = []): array
    {
        $dom = $this->loadDom($html);
        $xpath = $dom ? new DOMXPath($dom) : null;

        $pack = $this->mergeStayPacks(
            $pack,
            $this->stayPackFromHtmlString($html),
            $this->stayPack($xpath),
        );
        $title = $this->title($xpath, $extracted);
        $address = $this->address($xpath);
        $coords = $this->coords($xpath);
        $description = $this->description($xpath);
        $stars = $this->starRating($xpath);
        $reviews = $this->reviews($xpath);
        $popular = $this->popularFacilities($xpath);
        $highlights = $this->highlights($xpath);
        $rooms = $this->rooms($xpath, $pack);
        $images = $this->images($xpath, $extracted, $sourceUrl, $pack, $html);
        $policies = $this->policies($xpath, $html, $pack);
        $nearbyPack = $this->nearby($xpath);
        $nearbyGroups = $nearbyPack['groups'];
        $amenityGroups = $this->amenityGroups($xpath, $popular, $pack);
        $propertyType = $this->propertyType($title, $xpath);

        $amenities = $popular;
        foreach ($amenityGroups as $items) {
            foreach ($items as $item) {
                $amenities[] = $item;
            }
        }
        $amenities = StayFacilities::stringList($amenities);

        $slug = StayBookingUrl::hotelSlug($extracted['canonical_url'] ?? $sourceUrl)
            ?: StayBookingUrl::hotelSlug($sourceUrl)
            ?: Str::slug($title);

        $summary = $description !== '' ? $description : $title;
        $location = $this->shortLocation($address, $title);

        $attrs = array_filter([
            'property_type' => $propertyType,
            'address' => $address,
            'lat' => $coords['lat'] ?? null,
            'lng' => $coords['lng'] ?? null,
            'amenities' => $amenities,
            'amenity_groups' => $amenityGroups !== [] ? $amenityGroups : ['popular' => $popular],
            'highlight_badges' => array_values(array_unique(array_merge(
                array_slice($popular, 0, 8),
                $highlights,
            ))),
            'nearby_groups' => $nearbyGroups !== [] ? $nearbyGroups : null,
            'review_scores' => StayFacilities::normalizeReviewScores($reviews['scores']),
            'photos' => $images,
            'check_in' => $policies['check_in'] ?? null,
            'check_out' => $policies['check_out'] ?? null,
            'cancellation_policy' => $policies['cancellation'] ?? null,
            'child_policy' => $policies['child'] ?? null,
            'extra_bed_policy' => $policies['extra_bed'] ?? null,
            'pet_policy' => $policies['pet'] ?? null,
            'smoking_policy' => $policies['smoking'] ?? null,
            'payment_policy' => $policies['payment'] ?? null,
            'id_required_policy' => $policies['id'] ?? null,
            'age_restriction' => $policies['age'] ?? null,
            'policy_sections' => is_array($policies['_sections'] ?? null) ? $policies['_sections'] : [],
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);

        return [
            'title' => $title,
            'location_label' => $location,
            'content' => $description !== '' ? '<p>'.e($description).'</p>' : null,
            'star_rating' => $stars,
            'rating' => $reviews['rating'],
            'review_count' => $reviews['count'],
            'seo_slug' => $slug,
            'seo_title' => $title,
            'seo_description' => Str::limit(strip_tags($summary), 160, ''),
            'attrs' => $attrs,
            'options' => $rooms,
            'faqs' => [],
            'mapper_version' => self::VERSION,
        ];
    }

    /**
     * @param  list<mixed>  $rooms
     * @return list<array<string, mixed>>
     */
    public function mapRoomsFromPack(array $rooms): array
    {
        return $this->roomsFromPack($rooms);
    }

    /**
     * Parse bảng #hprt-table (có ngày check-in/out) → hạng phòng + rate_options[].
     *
     * @param  array<string, mixed>|null  $crawlDates
     * @return list<array<string, mixed>>
     */
    public function mapRoomsFromHprtHtml(string $html, ?array $crawlDates = null): array
    {
        $rooms = $this->roomsFromHprtTable($this->xpathFromHtml($html));
        if (is_array($crawlDates) && $crawlDates !== []) {
            $rooms = $this->attachCrawlDates($rooms, $crawlDates);
        }

        return $rooms;
    }

    private function title(?DOMXPath $xpath, array $extracted): string
    {
        $candidates = [
            $extracted['title'] ?? null,
            $this->firstText($xpath, '//h2[contains(@class,"pp-header__title")]'),
            $this->firstText($xpath, '//*[@id="hp_hotel_name_reviews"]'),
            $this->firstText($xpath, '//*[@id="hp_hotel_name"]'),
            $extracted['open_graph']['og:title'] ?? null,
        ];
        foreach ($candidates as $raw) {
            $t = $this->clean((string) $raw);
            $t = preg_replace('/\s*[|,].*(Booking\.com|Phú Quốc|\(cập nhật|cập nhật giá).*$/iu', '', $t) ?? $t;
            if ($t !== '' && ! str_contains(mb_strtolower($t), 'booking.com')) {
                return $t;
            }
        }

        return $this->clean((string) ($extracted['title'] ?? ''));
    }

    private function address(?DOMXPath $xpath): ?string
    {
        $text = $this->firstText($xpath, '//*[@data-testid="PropertyHeaderAddressDesktop-wrapper"]//button');
        if (! is_string($text) || $text === '') {
            $text = $this->firstText($xpath, '//*[@data-testid="PropertyHeaderAddressDesktop-wrapper"]');
        }
        $text = preg_replace('/(Vị trí|Sau khi đặt phòng|Được khách đánh giá|–?\s*Vị trí tốt).*$/iu', '', $text) ?? $text;
        $text = preg_replace('/–?\s*Vị trí tốt.*$/u', '', $text) ?? $text;
        $text = $this->clean($text);

        return $text !== '' ? $text : null;
    }

    /** @return array{lat?: float, lng?: float} */
    private function coords(?DOMXPath $xpath): array
    {
        if (! $xpath) {
            return [];
        }
        foreach ($xpath->query('//*[@data-atlas-latlng]') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $raw = $node->getAttribute('data-atlas-latlng');
            if (preg_match('/(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/', $raw, $m)) {
                return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
            }
        }

        return [];
    }

    private function description(?DOMXPath $xpath): string
    {
        $text = $this->firstText($xpath, '//*[@data-testid="property-description"]');
        if ($text === '') {
            $text = $this->firstText($xpath, '//*[@id="property_description_content"]');
        }

        return $this->clean($text);
    }

    private function starRating(?DOMXPath $xpath): ?int
    {
        $label = $this->firstAttr($xpath, '//*[@data-testid="quality-rating"]/@aria-label')
            ?: $this->firstAttr($xpath, '//*[@data-testid="rating-stars"]/@aria-label');
        if (preg_match('/(\d+)\s*(?:trên|of|\/)\s*5/i', $label, $m)) {
            $n = (int) $m[1];

            return $n >= 1 && $n <= 5 ? $n : null;
        }

        return null;
    }

    /**
     * @return array{rating: float, count: int, scores: array<string, float>}
     */
    private function reviews(?DOMXPath $xpath): array
    {
        $rating = 0.0;
        $count = 0;
        $scores = [];
        if (! $xpath) {
            return compact('rating', 'count', 'scores');
        }

        $scoreRaw = $this->firstAttr($xpath, '//*[@data-review-score]/@data-review-score');
        if ($scoreRaw === '') {
            $scoreRaw = $this->firstText($xpath, '//*[@data-testid="review-score-right-component"]//*[@aria-hidden="true"][contains(@class,"dff2e52086")]');
        }
        $rating = $this->parseScore($scoreRaw);

        $countText = $this->firstText($xpath, '//*[@data-testid="review-score-right-component"]');
        if (preg_match('/(\d[\d\.]*)\s*đánh giá/iu', $countText, $m)) {
            $count = (int) str_replace('.', '', $m[1]);
        }

        $map = [
            'hotel_staff' => 'staff',
            'staff' => 'staff',
            'hotel_services' => 'facilities',
            'hotel_clean' => 'cleanliness',
            'hotel_comfort' => 'comfort',
            'hotel_value' => 'value',
            'hotel_location' => 'location',
            'hotel_wifi' => 'wifi',
            'wifi' => 'wifi',
        ];
        foreach ($xpath->query('//*[@id="review_list_score_breakdown"]/li[@data-question]') ?: [] as $li) {
            if (! $li instanceof DOMElement) {
                continue;
            }
            $q = $li->getAttribute('data-question');
            if ($q === 'total' || ! isset($map[$q])) {
                continue;
            }
            $val = $this->parseScore($this->nodeText($this->childByClass($li, 'review_score_value') ?? $li));
            if ($val > 0) {
                $scores[$map[$q]] = $val;
            }
        }

        return ['rating' => $rating, 'count' => $count, 'scores' => $scores];
    }

    /** @return list<string> */
    private function popularFacilities(?DOMXPath $xpath): array
    {
        return $this->listTexts($xpath, '//*[@data-testid="property-most-popular-facilities-wrapper"]//li');
    }

    /** @return list<string> */
    private function highlights(?DOMXPath $xpath): array
    {
        $items = $this->listTexts($xpath, '//*[contains(@class,"property-highlights")]//*[contains(@class,"ph-item-copy")]');
        if ($items === []) {
            $items = $this->listTexts($xpath, '//*[contains(@class,"property-highlights")]//li');
        }

        return array_values(array_filter($items, fn ($t) => ! preg_match('/lưu chỗ nghỉ|wishlist/i', $t)));
    }

    /**
     * @return array<string, list<string>>
     */
    private function amenityGroups(?DOMXPath $xpath, array $popular, array $pack = []): array
    {
        $groups = [];
        if ($popular !== []) {
            $groups['popular'] = $popular;
        }
        $groups = $this->mergeAmenityBlocks($xpath, $groups);
        $facilitiesHtml = (string) ($pack['facilities_html'] ?? '');
        if ($facilitiesHtml !== '') {
            $extra = $this->xpathFromHtml($facilitiesHtml);
            $groups = $this->mergeAmenityBlocks($extra, $groups);
        }

        return $groups;
    }

    /**
     * @param  array<string, list<string>>  $groups
     * @return array<string, list<string>>
     */
    private function mergeAmenityBlocks(?DOMXPath $xpath, array $groups): array
    {
        if (! $xpath) {
            return $groups;
        }

        $blocks = $xpath->query(
            '//*[@data-testid="facility-group-container"] | //*[@data-testid="facility-group"] | //*[@data-testid="property-facilities-group"] | //*[@id="hp_facilities_box"]//*[contains(@class,"facilitiesChecklist")]'
        ) ?: [];

        foreach ($blocks as $block) {
            if (! $block instanceof DOMElement) {
                continue;
            }
            $heading = $this->facilityGroupHeading($xpath, $block);
            if ($heading === '' || preg_match('/xem phòng trống/i', $heading)) {
                continue;
            }
            $items = $this->facilityGroupItems($xpath, $block, $heading);
            if ($items === []) {
                continue;
            }
            $key = $this->groupKeyFromHeading($heading);
            $groups[$key] = array_values(array_unique(array_merge($groups[$key] ?? [], $items)));
        }

        return $groups;
    }

    private function facilityGroupHeading(DOMXPath $xpath, DOMElement $block): string
    {
        $icon = $xpath->query('.//*[@data-testid="facility-group-icon"]', $block)?->item(0);
        if ($icon instanceof DOMElement && $icon->parentNode instanceof DOMElement) {
            $heading = $this->clean($this->nodeText($icon->parentNode));
            $heading = preg_replace('/\s*(Miễn phí!?|Phụ phí)\s*$/iu', '', $heading) ?? $heading;

            return $this->clean($heading);
        }

        $heading = $this->clean($this->firstTextIn($xpath, $block, './/*[@data-testid="facility-group-name"] | .//h3 | .//h4'));
        $heading = preg_replace('/\s*(Miễn phí!?|Phụ phí)\s*$/iu', '', $heading) ?? $heading;

        return $this->clean($heading);
    }

    /** @return list<string> */
    private function facilityGroupItems(DOMXPath $xpath, DOMElement $block, string $heading): array
    {
        $items = [];
        foreach ($xpath->query('.//ul//li', $block) ?: [] as $li) {
            $t = $this->cleanFacilityLabel($this->nodeText($li));
            if ($t === '' || mb_strlen($t) > 140 || preg_match('/xem phòng trống/i', $t)) {
                continue;
            }
            $items[] = $t;
        }
        if ($items !== []) {
            return array_values(array_unique($items));
        }

        $h3 = $xpath->query('.//h3', $block)?->item(0);
        if (! $h3 instanceof DOMElement) {
            return [];
        }
        $note = $this->cleanFacilityLabel(str_replace($heading, '', $this->nodeText($h3)));

        return $note !== '' && mb_strlen($note) < 200 ? [$note] : [];
    }

    private function cleanFacilityLabel(string $text): string
    {
        $text = $this->clean($text);
        $text = preg_replace('/\s*(Phụ phí|Miễn phí!?|Đã bao gồm)\s*/iu', ' ', $text) ?? $text;

        return $this->clean($text);
    }

    /** @return list<array<string, mixed>> */
    private function rooms(?DOMXPath $xpath, array $pack = []): array
    {
        $fromHprtPack = [];
        if (! empty($pack['hprt_html']) && is_string($pack['hprt_html'])) {
            $fromHprtPack = $this->roomsFromHprtTable($this->xpathFromHtml($pack['hprt_html']));
        }
        $fromPack = $this->roomsFromPack(is_array($pack['rooms'] ?? null) ? $pack['rooms'] : []);
        $fromTable = $this->roomsFromTable($xpath);
        $merged = $this->mergeRoomLists($fromTable, $fromHprtPack);
        $merged = $this->mergeRoomLists($merged, $fromPack);
        if (is_array($pack['crawl_dates'] ?? null)) {
            $merged = $this->attachCrawlDates($merged, $pack['crawl_dates']);
        }

        return array_slice($merged, 0, (int) config('stay.crawl.max_rooms', 16));
    }

    /**
     * @param  list<array<string, mixed>>  $rooms
     * @param  array<string, mixed>  $dates
     * @return list<array<string, mixed>>
     */
    private function attachCrawlDates(array $rooms, array $dates): array
    {
        $slice = array_filter([
            'checkin' => $dates['checkin'] ?? null,
            'checkout' => $dates['checkout'] ?? null,
            'nights' => isset($dates['nights']) ? (int) $dates['nights'] : null,
        ]);
        if ($slice === []) {
            return $rooms;
        }
        foreach ($rooms as &$room) {
            $attrs = is_array($room['attrs'] ?? null) ? $room['attrs'] : [];
            $attrs['crawl_dates'] = $slice;
            $rates = is_array($attrs['rate_options'] ?? null) ? $attrs['rate_options'] : [];
            if ($rates !== []) {
                $attrs['rate_options'] = \App\Support\StayRateCopy::enrichRateOptions(
                    $rates,
                    $slice,
                    (string) ($attrs['deal_key'] ?? \App\Support\StayRateCopy::DEFAULT_DEAL_KEY),
                );
            }
            $room['attrs'] = $attrs;
        }
        unset($room);

        return $rooms;
    }

    /** @return list<array<string, mixed>> */
    private function roomsFromTable(?DOMXPath $xpath): array
    {
        if (! $xpath) {
            return [];
        }
        $hprt = $this->roomsFromHprtTable($xpath);
        if ($hprt !== []) {
            return $hprt;
        }

        $out = [];
        $seen = [];
        $nodes = $xpath->query(
            '//*[@data-testid="rt-name-link" or @data-testid="rt-name-no-room-page"'
            .' or contains(@class,"hprt-roomtype-link") or starts-with(@id,"room_type_id_")]'
        ) ?: [];
        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $name = $this->clean($this->nodeText($node));
            if ($name === '' || isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $row = $this->closest($node, 'tr') ?? $this->closest($node, 'th') ?? $node;
            $rowText = $this->clean($this->nodeText($row));
            $beds = [];
            if (preg_match_all('/\d+\s*giường[^.,\n+]{0,40}/iu', $rowText, $bedHits)) {
                $beds = array_values(array_unique(array_filter(array_map(function (string $t) {
                    $t = $this->clean($t);
                    $t = preg_replace('/\+?\s*Hiển thị giá.*$/iu', '', $t) ?? $t;

                    return $this->clean($t);
                }, $bedHits[0]))));
            }
            $capLabel = '';
            if ($row instanceof DOMElement) {
                foreach ($xpath->query('.//*[@aria-label]', $row) ?: [] as $el) {
                    if ($el instanceof DOMElement && str_contains($el->getAttribute('aria-label'), 'người')) {
                        $capLabel = $el->getAttribute('aria-label');
                        break;
                    }
                }
            }
            $capacity = $this->capacityFromLabel($capLabel !== '' ? $capLabel : $rowText);
            $roomId = $node->getAttribute('data-room-id');
            $id = $node->getAttribute('id');
            if ($roomId === '' && preg_match('/room_type_id_(\d+)/', $id, $m)) {
                $roomId = $m[1];
            }
            $code = $roomId !== '' ? $roomId : Str::slug($name);
            $out[] = [
                'code' => 'bk-'.$code,
                'name' => $name,
                'capacity' => $capacity,
                'amenities' => $beds,
                'attrs' => array_filter([
                    'room_id' => $roomId !== '' ? $roomId : null,
                    'hash' => $roomId !== '' ? '#RD'.$roomId : null,
                    'bed' => $beds !== [] ? implode(', ', $beds) : null,
                    'beds' => $beds !== [] ? [['name' => 'Phòng', 'items' => array_map(
                        fn ($b) => ['type' => $b, 'count' => 1, 'label' => $b],
                        $beds,
                    )]] : null,
                    'unit_type' => $this->unitType($name),
                    'view' => $this->viewFromName($name),
                ]),
            ];
        }

        return array_slice($out, 0, (int) config('stay.crawl.max_rooms', 16));
    }

    /**
     * Một loại phòng = th roomtype (rowspan); mỗi tr[data-block-id] = một rate option.
     *
     * @return list<array<string, mixed>>
     */
    private function roomsFromHprtTable(?DOMXPath $xpath): array
    {
        if (! $xpath) {
            return [];
        }
        $table = $xpath->query('//*[@id="hprt-table"] | //table[contains(@class,"hprt-table")]')->item(0);
        if (! $table instanceof DOMElement) {
            return [];
        }

        $rows = $xpath->query('.//tbody/tr[@data-block-id]', $table) ?: [];
        $rooms = [];
        $current = null;

        foreach ($rows as $tr) {
            if (! $tr instanceof DOMElement) {
                continue;
            }
            $blockId = trim($tr->getAttribute('data-block-id'));
            if ($blockId === '') {
                continue;
            }

            $roomTh = $xpath->query('.//th[contains(@class,"hprt-table-cell-roomtype")]', $tr)->item(0);
            if ($roomTh instanceof DOMElement) {
                if ($current !== null) {
                    $rooms[] = $this->finalizeHprtRoom($current);
                }
                $current = $this->parseHprtRoomType($xpath, $roomTh);
            }
            if ($current === null) {
                continue;
            }

            $rate = $this->parseHprtRateRow($xpath, $tr, $blockId);
            if ($rate !== null) {
                $current['attrs']['rate_options'][] = $rate;
            }
        }
        if ($current !== null) {
            $rooms[] = $this->finalizeHprtRoom($current);
        }

        return array_slice($rooms, 0, (int) config('stay.crawl.max_rooms', 16));
    }

    /**
     * @return array<string, mixed>
     */
    private function parseHprtRoomType(DOMXPath $xpath, DOMElement $roomTh): array
    {
        $link = $xpath->query('.//a[contains(@class,"hprt-roomtype-link") or starts-with(@id,"room_type_id_")]', $roomTh)->item(0);
        $name = '';
        $roomId = '';
        if ($link instanceof DOMElement) {
            $roomId = trim($link->getAttribute('data-room-id'));
            $id = $link->getAttribute('id');
            if ($roomId === '' && preg_match('/room_type_id_(\d+)/', $id, $m)) {
                $roomId = $m[1];
            }
            $nameNode = $xpath->query('.//*[contains(@class,"hprt-roomtype-icon-link")]', $link)->item(0);
            $name = $this->clean($this->nodeText($nameNode instanceof DOMElement ? $nameNode : $link));
        }
        if ($name === '') {
            $name = $this->clean($this->nodeText($roomTh));
            $name = preg_replace('/Sức chứa:.*$/iu', '', $name) ?? $name;
            $name = $this->clean($name);
        }

        $occ = $this->firstTextIn($xpath, $roomTh, './/*[contains(@class,"hprt-roomtype-occupancy-text")]');
        $capacity = $this->capacityFromLabel($occ !== '' ? $occ : $this->nodeText($roomTh));

        $beds = [];
        foreach ($xpath->query('.//*[contains(@class,"rt-bed-type")]', $roomTh) ?: [] as $bedEl) {
            $t = $this->clean($this->nodeText($bedEl));
            $t = preg_replace('/\s+và\s*$/iu', '', $t) ?? $t;
            $t = $this->clean($t);
            if ($t !== '' && mb_strlen($t) < 80) {
                $beds[] = $t;
            }
        }
        $beds = array_values(array_unique($beds));

        $size = null;
        $facilities = [];
        foreach ($xpath->query('.//*[contains(@class,"hprt-facilities-facility")]', $roomTh) ?: [] as $fac) {
            if (! $fac instanceof DOMElement) {
                continue;
            }
            $label = $this->clean($this->nodeText($fac));
            if ($label === '') {
                continue;
            }
            if (preg_match('/(\d+)\s*m\s*²/u', $label, $m) || preg_match('/(\d+)\s*m2\b/iu', $label, $m)) {
                $size = (int) $m[1];
            }
            if (mb_strlen($label) < 80) {
                $facilities[] = $label;
            }
        }
        $facilities = array_values(array_unique($facilities));

        $scarcity = $this->clean($this->firstTextIn($xpath, $roomTh, './/*[contains(@class,"only_x_left")]'));
        $availabilityLeft = null;
        if (preg_match('/(\d+)/', $scarcity, $m)) {
            $availabilityLeft = (int) $m[1];
        }

        $amenities = array_values(array_unique(array_merge($beds, $facilities)));

        return [
            'code' => 'bk-'.($roomId !== '' ? $roomId : Str::slug($name)),
            'name' => $name,
            'capacity' => $capacity,
            'amenities' => $amenities,
            'attrs' => array_filter([
                'room_id' => $roomId !== '' ? $roomId : null,
                'hash' => $roomId !== '' ? '#RD'.$roomId : null,
                'bed' => $beds !== [] ? implode(', ', $beds) : null,
                'beds' => $beds !== [] ? [['name' => 'Phòng', 'items' => array_map(
                    fn ($b) => ['type' => $b, 'count' => 1, 'label' => $b],
                    $beds,
                )]] : null,
                'size_sqm' => $size,
                'scarcity' => $scarcity !== '' ? $scarcity : null,
                'availability_left' => $availabilityLeft,
                'highlights' => $facilities !== [] ? array_slice($facilities, 0, 12) : null,
                'unit_type' => $this->unitType($name),
                'view' => $this->viewFromName($name) ?? $this->viewFromFacilityList($facilities),
                'rate_options' => [],
            ], fn ($v) => $v !== null && $v !== '' && $v !== []),
        ];
    }

    /**
     * @param  array<string, mixed>  $room
     * @return array<string, mixed>
     */
    private function finalizeHprtRoom(array $room): array
    {
        $attrs = is_array($room['attrs'] ?? null) ? $room['attrs'] : [];
        $rates = is_array($attrs['rate_options'] ?? null) ? $attrs['rate_options'] : [];
        $crawlDates = is_array($attrs['crawl_dates'] ?? null) ? $attrs['crawl_dates'] : null;
        $rates = \App\Support\StayRateCopy::enrichRateOptions($rates, $crawlDates, \App\Support\StayRateCopy::DEFAULT_DEAL_KEY);

        $perNight = [];
        $hasDeal = false;
        foreach ($rates as $rate) {
            if (! is_array($rate)) {
                continue;
            }
            $p = $rate['price_per_night'] ?? null;
            if (is_numeric($p) && (float) $p > 0) {
                $perNight[] = (float) $p;
            }
            if (! empty($rate['deal_key']) || ! empty($rate['save_percent'])) {
                $hasDeal = true;
            }
        }
        if ($perNight !== []) {
            $room['price_from'] = min($perNight);
        }
        $attrs['rate_options'] = $rates;

        $rawScarcity = trim((string) ($attrs['scarcity'] ?? ''));
        $left = isset($attrs['availability_left']) ? (int) $attrs['availability_left'] : null;
        $attrs['scarcity_active'] = $rawScarcity !== '' || ($left !== null && $left > 0);
        // Không giữ câu Booking tuyệt đối — public random hoá.
        unset($attrs['scarcity']);
        if ($hasDeal && empty($attrs['deal_key'])) {
            $attrs['deal_key'] = \App\Support\StayRateCopy::DEFAULT_DEAL_KEY;
        }
        $room['attrs'] = $attrs;

        return $room;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseHprtRateRow(DOMXPath $xpath, DOMElement $tr, string $blockId): ?array
    {
        $price = null;
        $rawPrice = $tr->getAttribute('data-hotel-rounded-price');
        if ($rawPrice !== '' && is_numeric($rawPrice)) {
            $price = (float) $rawPrice;
        }
        $strikethrough = null;
        $strikeEl = $xpath->query('.//*[@data-strikethrough-value]', $tr)->item(0);
        if ($strikeEl instanceof DOMElement) {
            $sv = $strikeEl->getAttribute('data-strikethrough-value');
            if ($sv !== '' && is_numeric($sv)) {
                $strikethrough = (float) $sv;
            }
        }

        $nights = 2;
        $priceCell = $xpath->query('.//*[contains(@class,"hprt-table-cell-price")]', $tr)->item(0);
        $priceText = $priceCell instanceof DOMElement ? $this->clean($this->nodeText($priceCell)) : '';
        if (preg_match('/×\s*(\d+)\s*đêm/iu', $priceText, $m)
            || preg_match('/(\d+)\s*đêm/iu', $priceText, $m)) {
            $nights = max(1, (int) $m[1]);
        }
        $pricePerNight = $price !== null ? round($price / $nights, 2) : null;

        $taxesIncluded = str_contains(mb_strtolower($priceText), 'đã bao gồm thuế')
            || (bool) $xpath->query('.//*[contains(@class,"prd-taxes-and-fees-under-price")]', $tr)->length;

        $fltrsRaw = $tr->getAttribute('data-fltrs');
        $fltrs = [];
        if ($fltrsRaw !== '') {
            $decoded = json_decode(html_entity_decode($fltrsRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            if (is_array($decoded)) {
                $fltrs = $decoded;
            }
        }

        $breakfastIncluded = ! empty($fltrs['breakfast_included'])
            || (bool) $xpath->query('.//*[@data-testid="rt-rate-breakfast-included"]', $tr)->length;
        $breakfastLabel = '';
        $breakfastExtra = null;
        foreach ($xpath->query('.//*[contains(@class,"hprt-conditions-bui")]//*[contains(@class,"bui-list__description")]', $tr) ?: [] as $desc) {
            $t = $this->clean($this->nodeText($desc));
            if ($t === '' || ! preg_match('/bữa\s*sáng/iu', $t)) {
                continue;
            }
            $breakfastLabel = $t;
            if (preg_match('/VND\s*([\d.]+)/u', $t, $pm)) {
                $breakfastExtra = (float) str_replace('.', '', $pm[1]);
            }
            break;
        }
        if ($breakfastIncluded && $breakfastLabel === '') {
            $breakfastLabel = 'Bao gồm bữa sáng';
        }

        $cancelTitle = $this->clean($this->firstTextIn(
            $xpath,
            $tr,
            './/*[@data-testid="cancellation-subtitle"]//*[@data-testid="policy-title"]',
        ));
        $prepayTitle = $this->clean($this->firstTextIn(
            $xpath,
            $tr,
            './/*[@data-testid="prepayment-subtitle"]//*[@data-testid="policy-title"]',
        ));

        $modal = $xpath->query('//*[@id="policyModal_'.addcslashes($blockId, '"\\').'"]')->item(0);
        $mealsDetail = [];
        $cancelDesc = '';
        $prepayDesc = '';
        if ($modal instanceof DOMElement) {
            $parsed = $this->parseHprtPolicyModal($xpath, $modal);
            $mealsDetail = $parsed['meals'];
            $cancelTitle = $parsed['cancellation_title'] ?: $cancelTitle;
            $cancelDesc = $parsed['cancellation_description'];
            $prepayTitle = $parsed['prepayment_title'] ?: $prepayTitle;
            $prepayDesc = $parsed['prepayment_description'];
        }

        $deals = [];
        foreach ($xpath->query('.//*[contains(@class,"c-deals-container__badge-box_title")]', $tr) ?: [] as $dealTitle) {
            $title = $this->clean($this->nodeText($dealTitle));
            if ($title === '') {
                continue;
            }
            $box = $dealTitle->parentNode?->parentNode;
            $desc = '';
            if ($box instanceof DOMElement) {
                $descEl = $xpath->query('.//*[contains(@class,"c-deals-container__badge-box_description")]', $box)->item(0);
                $desc = $descEl instanceof DOMElement ? $this->clean($this->nodeText($descEl)) : '';
            }
            $deals[] = array_filter(['title' => $title, 'description' => $desc !== '' ? $desc : null]);
        }
        if ($deals === []) {
            foreach ($xpath->query('.//*[contains(@class,"bui-badge__text")]', $tr) ?: [] as $badge) {
                $t = $this->clean($this->nodeText($badge));
                if ($t !== '' && mb_strlen($t) < 80) {
                    $deals[] = ['title' => $t];
                }
            }
        }

        $maxRooms = 0;
        $select = $xpath->query('.//select[contains(@class,"hprt-nos-select")]', $tr)->item(0);
        if ($select instanceof DOMElement) {
            foreach ($xpath->query('./option', $select) ?: [] as $opt) {
                if (! $opt instanceof DOMElement) {
                    continue;
                }
                $v = (int) $opt->getAttribute('value');
                if ($v > $maxRooms) {
                    $maxRooms = $v;
                }
            }
        }

        $refundable = $cancelTitle !== '' && ! preg_match('/không\s*hoàn\s*tiền|non[\s-]?refundable/iu', $cancelTitle);

        return array_filter([
            'block_id' => $blockId,
            'price' => $price,
            'price_strikethrough' => $strikethrough,
            'currency' => 'VND',
            'nights' => $nights,
            'price_per_night' => $pricePerNight,
            'taxes_included' => $taxesIncluded,
            'breakfast' => array_filter([
                'included' => $breakfastIncluded,
                'label' => $breakfastLabel !== '' ? $breakfastLabel : null,
                'extra_price' => $breakfastExtra,
            ], fn ($v) => $v !== null && $v !== ''),
            'cancellation' => array_filter([
                'refundable' => $refundable,
                'title' => $cancelTitle !== '' ? $cancelTitle : null,
                'description' => $cancelDesc !== '' ? $cancelDesc : null,
            ], fn ($v) => $v !== null && $v !== ''),
            'prepayment' => array_filter([
                'title' => $prepayTitle !== '' ? $prepayTitle : null,
                'description' => $prepayDesc !== '' ? $prepayDesc : null,
            ], fn ($v) => $v !== null && $v !== ''),
            'meals_detail' => $mealsDetail !== [] ? $mealsDetail : null,
            'deals' => $deals !== [] ? array_values($deals) : null,
            'max_rooms' => $maxRooms > 0 ? $maxRooms : null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /**
     * @return array{
     *     meals: list<string>,
     *     cancellation_title: string,
     *     cancellation_description: string,
     *     prepayment_title: string,
     *     prepayment_description: string
     * }
     */
    private function parseHprtPolicyModal(DOMXPath $xpath, DOMElement $modal): array
    {
        $meals = [];
        $cancelTitle = '';
        $cancelDesc = '';
        $prepayTitle = '';
        $prepayDesc = '';

        foreach ($xpath->query('.//h3', $modal) ?: [] as $h3) {
            if (! $h3 instanceof DOMElement) {
                continue;
            }
            $heading = mb_strtolower($this->clean($this->nodeText($h3)));
            $group = $h3->parentNode;
            if (! $group instanceof DOMElement) {
                continue;
            }
            $bodyTexts = [];
            foreach ($xpath->query('.//*[contains(@class,"bui-text--variant-body_2")]', $group) ?: [] as $body) {
                $t = $this->clean($this->nodeText($body));
                if ($t !== '') {
                    $bodyTexts[] = $t;
                }
            }
            if (str_contains($heading, 'bữa ăn') || str_contains($heading, 'meal')) {
                $meals = $bodyTexts;
            } elseif (str_contains($heading, 'hủy') || str_contains($heading, 'cancel')) {
                $cancelTitle = $this->clean($this->firstTextIn($xpath, $group, './/*[@data-testid="policy-title"]'));
                $cancelDesc = $this->clean($this->firstTextIn($xpath, $group, './/*[@data-testid="policy-description"]'));
                if ($cancelDesc === '' && $bodyTexts !== []) {
                    $cancelDesc = implode(' ', $bodyTexts);
                }
            } elseif (str_contains($heading, 'trả trước') || str_contains($heading, 'prepay') || str_contains($heading, 'prepayment')) {
                $prepayTitle = $this->clean($this->firstTextIn($xpath, $group, './/*[@data-testid="policy-title"]'));
                $prepayDesc = $this->clean($this->firstTextIn($xpath, $group, './/*[@data-testid="policy-description"]'));
                if ($prepayDesc === '' && $bodyTexts !== []) {
                    $prepayDesc = implode(' ', $bodyTexts);
                }
            }
        }

        return [
            'meals' => $meals,
            'cancellation_title' => $cancelTitle,
            'cancellation_description' => $cancelDesc,
            'prepayment_title' => $prepayTitle,
            'prepayment_description' => $prepayDesc,
        ];
    }

    /** @param  list<string>  $facilities */
    private function viewFromFacilityList(array $facilities): ?string
    {
        foreach ($facilities as $f) {
            if (preg_match('/nhìn\s*ra|view/iu', $f)) {
                return $f;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $base
     * @param  list<array<string, mixed>>  $extra
     * @return list<array<string, mixed>>
     */
    private function mergeRoomLists(array $base, array $extra): array
    {
        if ($extra === []) {
            return $base;
        }
        if ($base === []) {
            return $extra;
        }
        $byKey = [];
        foreach ($base as $row) {
            $byKey[$this->roomMergeKey($row)] = $row;
        }
        foreach ($extra as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '' && empty($row['attrs']['room_id'])) {
                continue;
            }
            $key = $this->roomMergeKey($row);
            if (! isset($byKey[$key])) {
                // Thử khớp theo tên nếu chưa có room_id ở một phía
                $matched = null;
                foreach ($byKey as $k => $existing) {
                    if ((string) ($existing['name'] ?? '') === $name && $name !== '') {
                        $matched = $k;
                        break;
                    }
                }
                if ($matched !== null) {
                    $key = $matched;
                } else {
                    $byKey[$key] = $row;
                    continue;
                }
            }
            $old = $byKey[$key];
            $oldAttrs = is_array($old['attrs'] ?? null) ? $old['attrs'] : [];
            $newAttrs = is_array($row['attrs'] ?? null) ? $row['attrs'] : [];
            $mergedAttrs = array_merge($oldAttrs, array_filter($newAttrs, fn ($v) => ! $this->isEmptyMergeValue($v)));
            $mergedAttrs['rate_options'] = $this->mergeRateOptions(
                is_array($oldAttrs['rate_options'] ?? null) ? $oldAttrs['rate_options'] : [],
                is_array($newAttrs['rate_options'] ?? null) ? $newAttrs['rate_options'] : [],
            );
            if ($mergedAttrs['rate_options'] === []) {
                unset($mergedAttrs['rate_options']);
            }
            $byKey[$key] = [
                'code' => $old['code'] ?? $row['code'],
                'name' => $name !== '' ? $name : (string) ($old['name'] ?? ''),
                'capacity' => $row['capacity'] ?? $old['capacity'] ?? null,
                'description' => $row['description'] ?? $old['description'] ?? null,
                'price_from' => $row['price_from'] ?? $old['price_from'] ?? null,
                'amenities' => array_values(array_unique(array_merge(
                    is_array($old['amenities'] ?? null) ? $old['amenities'] : [],
                    is_array($row['amenities'] ?? null) ? $row['amenities'] : [],
                ))),
                'photos' => ! empty($row['photos']) ? $row['photos'] : ($old['photos'] ?? []),
                'attrs' => array_filter($mergedAttrs, fn ($v) => ! $this->isEmptyMergeValue($v)),
            ];
            if (isset($mergedAttrs['rate_options'])) {
                $byKey[$key]['attrs']['rate_options'] = $mergedAttrs['rate_options'];
                $final = $this->finalizeHprtRoom($byKey[$key]);
                $byKey[$key] = $final;
            }
        }

        return array_values(array_filter($byKey, fn ($k) => $k !== '', ARRAY_FILTER_USE_KEY));
    }

    /** @param  array<string, mixed>  $row */
    private function roomMergeKey(array $row): string
    {
        $roomId = (string) ($row['attrs']['room_id'] ?? '');
        if ($roomId !== '') {
            return 'id:'.$roomId;
        }
        $code = (string) ($row['code'] ?? '');
        if ($code !== '' && preg_match('/^bk-\d+$/', $code)) {
            return 'code:'.$code;
        }

        return 'name:'.(string) ($row['name'] ?? '');
    }

    /**
     * @param  list<mixed>  $a
     * @param  list<mixed>  $b
     * @return list<array<string, mixed>>
     */
    private function mergeRateOptions(array $a, array $b): array
    {
        $by = [];
        foreach (array_merge($a, $b) as $rate) {
            if (! is_array($rate)) {
                continue;
            }
            $id = (string) ($rate['block_id'] ?? '');
            if ($id === '') {
                continue;
            }
            if (! isset($by[$id])) {
                $by[$id] = $rate;
                continue;
            }
            $by[$id] = array_merge($by[$id], array_filter($rate, fn ($v) => ! $this->isEmptyMergeValue($v)));
        }

        return array_values($by);
    }

    private function isEmptyMergeValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    /**
     * @param  array<string, mixed>  $extracted
     * @return list<array{url: string, alt: string}>
     */
    private function images(?DOMXPath $xpath, array $extracted, string $baseUrl = '', array $pack = [], string $html = ''): array
    {
        $out = [];
        $seen = [];
        $push = function (string $url, string $alt = '') use (&$out, &$seen): void {
            $url = $this->normalizeHotelPhotoUrl($url);
            if (! $this->isHotelPhotoUrl($url)) {
                return;
            }
            $id = $this->hotelPhotoId($url);
            $key = $id ?? $url;
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $out[] = ['url' => $url, 'alt' => $this->clean($alt)];
        };

        foreach (is_array($pack['photos'] ?? null) ? $pack['photos'] : [] as $img) {
            if (is_array($img) && ! empty($img['url'])) {
                $push((string) $img['url'], (string) ($img['alt'] ?? ''));
            } elseif (is_string($img)) {
                $push($img);
            }
        }
        if ($html !== '' && preg_match_all('#/xdata/images/hotel/(?:max\d+|square\d+)/(\d+)\.jpe?g#i', $html, $idHits)) {
            foreach ($idHits[1] as $photoId) {
                $push('https://cf.bstatic.com/xdata/images/hotel/max1024x768/'.$photoId.'.jpg');
            }
        }
        foreach (is_array($pack['rooms'] ?? null) ? $pack['rooms'] : [] as $room) {
            foreach (is_array($room['photos'] ?? null) ? $room['photos'] : [] as $img) {
                if (is_array($img) && ! empty($img['url'])) {
                    $push((string) $img['url'], (string) ($img['alt'] ?? ''));
                }
            }
        }

        if ($xpath) {
            foreach ($xpath->query('//*[@data-testid="gallery-modal-grid"]//img | //*[@data-testid="GalleryUnifiedDesktop-wrapper"]//img | //*[@id="photo_wrapper"]//img') ?: [] as $img) {
                if (! $img instanceof DOMElement) {
                    continue;
                }
                $src = $img->getAttribute('src') ?: $this->firstSrcsetUrl($img->getAttribute('srcset'));
                $push($src, $img->getAttribute('alt'));
            }
            foreach ($xpath->query('//*[starts-with(@data-testid,"gallery-grid-photo-action-") or starts-with(@data-testid,"gallery-photo-thumb-")]') ?: [] as $btn) {
                if (! $btn instanceof DOMElement) {
                    continue;
                }
                $tid = $btn->getAttribute('data-testid');
                if (preg_match('/(\d{5,})$/', $tid, $m)) {
                    $push('https://cf.bstatic.com/xdata/images/hotel/max1024x768/'.$m[1].'.jpg', $btn->getAttribute('aria-label'));
                }
            }
        }

        foreach (is_array($extracted['json_ld'] ?? null) ? $extracted['json_ld'] : [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            $images = $item['image'] ?? [];
            if (is_string($images)) {
                $images = [$images];
            }
            foreach (is_array($images) ? $images : [] as $img) {
                if (is_string($img)) {
                    $push($img);
                } elseif (is_array($img) && is_string($img['url'] ?? $img['contentUrl'] ?? null)) {
                    $push((string) ($img['url'] ?? $img['contentUrl']), (string) ($img['caption'] ?? ''));
                }
            }
        }

        $og = is_array($extracted['open_graph'] ?? null) ? $extracted['open_graph'] : [];
        if (! empty($og['og:image'])) {
            $push((string) $og['og:image']);
        }

        foreach (is_array($extracted['images'] ?? null) ? $extracted['images'] : [] as $img) {
            if (is_array($img) && ! empty($img['url'])) {
                $push((string) $img['url'], (string) ($img['alt'] ?? ''));
            } elseif (is_string($img)) {
                $push($img);
            }
        }

        if ($xpath && count(is_array($pack['photos'] ?? null) ? $pack['photos'] : []) < 10) {
            foreach ($xpath->query('//img[@src or @srcset]') ?: [] as $img) {
                if (! $img instanceof DOMElement) {
                    continue;
                }
                $src = $img->getAttribute('src') ?: $this->firstSrcsetUrl($img->getAttribute('srcset'));
                $push($src, $img->getAttribute('alt'));
            }
        }

        return array_slice($out, 0, (int) config('stay.crawl.max_images', 40));
    }

    private function firstSrcsetUrl(string $srcset): string
    {
        $srcset = trim($srcset);
        if ($srcset === '') {
            return '';
        }

        return trim(explode(' ', explode(',', $srcset)[0])[0]);
    }

    private function isHotelPhotoUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, 'data:')) {
            return false;
        }
        if (! preg_match('#^https?://#i', $url)) {
            return false;
        }
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if (str_contains($path, '/static/img/') || str_contains($path, 'transparent')) {
            return false;
        }

        return str_contains($path, '/xdata/images/hotel/')
            || str_contains($path, '/xdata/images/max')
            || (str_contains(strtolower($url), 'bstatic.com') && preg_match('/\.(jpe?g|webp)(\?|$)/i', $path) === 1);
    }

    /**
     * @return array<string, string>
     */
    private function policies(?DOMXPath $xpath, string $html, array $pack = []): array
    {
        unset($html);
        $out = [];
        $policyHtml = trim((string) ($pack['policies_html'] ?? ''));
        if ($policyHtml !== '') {
            $extra = $this->xpathFromHtml($policyHtml);
            $out = array_merge($out, $this->policiesFromXpath($extra));
        }
        $pagePolicies = $this->policiesFromXpath($xpath);
        $out = array_merge($pagePolicies, $out);
        foreach ($out as $key => $value) {
            if ($key === '_sections') {
                if (is_array($value)) {
                    $out['_sections'] = array_values(array_filter(
                        $value,
                        fn ($sec) => is_array($sec) && isset($sec['body']) && ! $this->isJunkPolicyText((string) $sec['body'])
                    ));
                    if (empty($out['_sections'])) {
                        unset($out['_sections']);
                    }
                }
                continue;
            }
            if (! is_string($value) || $this->isJunkPolicyText($value)) {
                unset($out[$key]);
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private function policiesFromXpath(?DOMXPath $xpath): array
    {
        $out = [];
        if (! $xpath) {
            return [];
        }

        $roots = $xpath->query(
            '//*[@id="vt-policies"] | //*[@id="policies"] | //*[@id="hotelPoliciesInc"] | //*[@data-testid="house-rules"] | //*[@data-testid="house-rules-section"] | //*[@data-testid="PropertyImportantInfo-wrapper"] | //*[contains(@class,"hp-policies")]'
        ) ?: [];

        $blobParts = [];
        foreach ($roots as $root) {
            if (! $root instanceof DOMElement) {
                continue;
            }
            $blobParts[] = $this->clean($this->readableBlockText($root));
            $this->policiesFromBookingRows($xpath, $root, $out);
            foreach ($xpath->query('.//*[self::h3 or self::h4 or self::h5 or contains(@class,"headline") or contains(@class,"description__headline")]', $root) ?: [] as $heading) {
                if (! $heading instanceof DOMElement) {
                    continue;
                }
                $title = $this->clean($this->nodeText($heading));
                $body = '';
                $next = $heading->nextSibling;
                while ($next) {
                    if ($next instanceof DOMElement) {
                        $tag = strtolower($next->tagName);
                        if (in_array($tag, ['h3', 'h4', 'h5'], true)) {
                            break;
                        }
                        $body .= ' '.$this->readableBlockText($next);
                    } elseif ($next instanceof \DOMText) {
                        $body .= ' '.$next->textContent;
                    }
                    $next = $next->nextSibling;
                }
                if ($body === '' && $heading->parentNode instanceof DOMElement) {
                    $body = str_replace($title, '', $this->readableBlockText($heading->parentNode));
                }
                $this->assignPolicy($out, $title, $this->clean($body));
            }
            foreach ($xpath->query('.//*[@data-vt-policy] | .//*[@data-testid="PolicyBlock"] | .//*[@data-testid="house-rule"] | .//*[@data-testid="HouseRulesBlock"]', $root) ?: [] as $block) {
                if (! $block instanceof DOMElement) {
                    continue;
                }
                if ($block->hasAttribute('data-vt-policy')) {
                    $title = $this->clean($this->firstTextIn($xpath, $block, './/h3'));
                    $bodyNode = null;
                    foreach ($block->childNodes as $child) {
                        if ($child instanceof DOMElement && strtolower($child->tagName) === 'div') {
                            $bodyNode = $child;
                            break;
                        }
                    }
                    $body = $bodyNode
                        ? $this->clean($this->readableBlockText($bodyNode))
                        : $this->clean($this->firstTextIn($xpath, $block, './/div'));
                    if ($title !== '') {
                        $this->assignPolicy($out, $title, $body);
                    }

                    continue;
                }
                $title = $this->clean($this->firstTextIn($xpath, $block, './/h3 | .//h4 | .//*[@data-testid="PolicyExceptionTitle"]'));
                $body = $this->clean($this->readableBlockText($block));
                if ($title !== '') {
                    $body = $this->clean(str_replace($title, '', $body));
                }
                if ($title !== '') {
                    $this->assignPolicy($out, $title, $body);
                }
            }
        }

        if ($blobParts === []) {
            $fallback = $xpath->query('//*[@id="vt-pack"]')?->item(0);
            if ($fallback) {
                $blobParts[] = $this->clean($this->readableBlockText($fallback));
            }
        }

        $blob = $this->clean(implode(' ', $blobParts));
        if ($this->isJunkPolicyText($blob)) {
            $blob = '';
        }

        if ($blob !== '') {
            if (empty($out['check_in']) && preg_match('/Nhận phòng[^0-9]{0,40}(?:Từ\s+)?(\d{1,2}:\d{2})/iu', $blob, $m)) {
                $out['check_in'] = $m[1];
            }
            if (empty($out['check_out']) && preg_match('/Trả phòng[^0-9]{0,80}(?:Từ\s+|Trước\s+|Đến\s+)?(\d{1,2}:\d{2})/iu', $blob, $m)) {
                $out['check_out'] = $m[1];
            }
            foreach ([
                'cancellation' => '/(?:Huỷ|Hủy)\s*(?:\/\s*đổi ngày)?[:\s]+(.{12,280})/iu',
                'child' => '/Trẻ em[:\s]+(.{8,280})/iu',
                'extra_bed' => '/(?:Giường phụ|cũi)[:\s]+(.{8,280})/iu',
                'pet' => '/(?:Thú cưng|Vật nuôi)[:\s]+(.{8,180})/iu',
                'smoking' => '/Hút thuốc[:\s]+(.{8,180})/iu',
                'payment' => '/Thanh toán[:\s]+(.{8,280})/iu',
                'id' => '/(?:Giấy tờ|CCCD|hộ chiếu)[:\s]+(.{8,280})/iu',
                'age' => '/(?:Độ tuổi|tuổi tối thiểu)[:\s]+(.{4,160})/iu',
            ] as $key => $re) {
                if (! empty($out[$key])) {
                    continue;
                }
                if (preg_match($re, $blob, $m)) {
                    $this->assignPolicy($out, $key, $this->clean($m[1]));
                }
            }
        }

        foreach ($out as $key => $value) {
            if ($this->isJunkPolicyText($value)) {
                unset($out[$key]);
            }
        }

        return $out;
    }

    /**
     * Booking house-rules redesign: rows = [icon+title col | body col], separated by <hr>.
     *
     * @param  array<string, mixed>  $out
     */
    private function policiesFromBookingRows(DOMXPath $xpath, DOMElement $root, array &$out): void
    {
        $contentList = $xpath->query(
            './/*[@data-testid="property-section--content"] | .//*[@data-testid="PropertyImportantInfo-wrapper"]',
            $root
        );
        $start = $contentList && $contentList->item(0) instanceof DOMElement
            ? $contentList->item(0)
            : $root;

        foreach ($this->collectBookingPolicyRows($start) as $row) {
            $this->assignPolicy($out, $row['title'], $row['body']);
        }
    }

    /**
     * @return list<array{title: string, body: string}>
     */
    private function collectBookingPolicyRows(DOMElement $container): array
    {
        $blocks = [];
        foreach ($container->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            if (strcasecmp($child->tagName, 'hr') === 0) {
                continue;
            }
            if (strcasecmp($child->tagName, 'div') !== 0) {
                continue;
            }
            if ($this->isBookingPolicyRow($child)) {
                $cols = $this->directDivChildrenWithText($child);
                if (count($cols) < 2) {
                    continue;
                }
                $title = $this->policyTitleFromColumn($cols[0]);
                $body = $this->clean($this->readableBlockText($cols[1]));
                if ($title !== '' && $body !== '') {
                    $blocks[] = ['title' => $title, 'body' => $body];
                }
            } else {
                foreach ($this->collectBookingPolicyRows($child) as $nested) {
                    $blocks[] = $nested;
                }
            }
        }

        return $blocks;
    }

    private function isBookingPolicyRow(DOMElement $el): bool
    {
        $cols = $this->directDivChildrenWithText($el);
        if (count($cols) !== 2) {
            return false;
        }
        $title = $this->policyTitleFromColumn($cols[0]);
        if ($title === '' || mb_strlen($title) > 120) {
            return false;
        }
        $body = $this->clean($this->readableBlockText($cols[1]));
        if ($body === '') {
            return false;
        }
        $hasSvg = $cols[0]->getElementsByTagName('svg')->length > 0;
        $titleLines = preg_split('/\n+/u', trim($this->readableBlockText($cols[0]))) ?: [];
        $titleLines = array_values(array_filter(array_map('trim', $titleLines)));

        return $hasSvg || count($titleLines) <= 3;
    }

    /**
     * @return list<DOMElement>
     */
    private function directDivChildrenWithText(DOMElement $el): array
    {
        $cols = [];
        foreach ($el->childNodes as $child) {
            if (! $child instanceof DOMElement || strcasecmp($child->tagName, 'div') !== 0) {
                continue;
            }
            if ($this->clean($this->readableBlockText($child)) === '') {
                continue;
            }
            $cols[] = $child;
        }

        return $cols;
    }

    private function policyTitleFromColumn(DOMElement $col): string
    {
        $raw = trim($this->readableBlockText($col));
        $lines = preg_split('/\n+/u', $raw) ?: [];
        $lines = array_values(array_filter(array_map(
            fn ($line) => $this->clean((string) $line),
            $lines
        )));
        if ($lines === []) {
            return '';
        }
        foreach ($lines as $line) {
            if (preg_match('/^(Nhận phòng|Trả phòng|Hủy|Huỷ|Trẻ em|Không giới hạn|Vật nuôi|Thú cưng|Các phương thức|Thanh toán|Hút thuốc|Giấy tờ)/iu', $line) === 1) {
                return $line;
            }
        }
        if (count($lines) <= 2 && mb_strlen($lines[0]) <= 100) {
            return $this->clean(implode(' ', $lines));
        }

        return $lines[0];
    }

    private function assignPolicy(array &$out, string $titleOrKey, string $body): void
    {
        $body = $this->cleanPolicyValue($body);
        if ($body === '') {
            return;
        }
        $key = match (true) {
            $titleOrKey === 'check_in' || preg_match('/^nhận phòng/iu', $titleOrKey) === 1 => 'check_in',
            $titleOrKey === 'check_out' || preg_match('/^trả phòng/iu', $titleOrKey) === 1 => 'check_out',
            $titleOrKey === 'cancellation' || preg_match('/huỷ|hủy|đổi ngày|trả trước|cancel/iu', $titleOrKey) === 1 => 'cancellation',
            $titleOrKey === 'child' || preg_match('/trẻ em|trẻ nhỏ/iu', $titleOrKey) === 1 => 'child',
            $titleOrKey === 'extra_bed' || preg_match('/giường phụ|cũi|extra bed/iu', $titleOrKey) === 1 => 'extra_bed',
            $titleOrKey === 'pet' || preg_match('/thú cưng|vật nuôi/iu', $titleOrKey) === 1 => 'pet',
            $titleOrKey === 'smoking' || preg_match('/hút thuốc/iu', $titleOrKey) === 1 => 'smoking',
            $titleOrKey === 'payment' || preg_match('/thanh toán|phương thức thanh toán|thẻ/iu', $titleOrKey) === 1 => 'payment',
            $titleOrKey === 'id' || preg_match('/giấy tờ|cccd|hộ chiếu/iu', $titleOrKey) === 1 => 'id',
            $titleOrKey === 'age' || preg_match('/độ tuổi|tuổi tối thiểu|không giới hạn độ tuổi/iu', $titleOrKey) === 1 => 'age',
            default => null,
        };
        if ($key === null) {
            $title = $this->clean($titleOrKey);
            if ($title === '' || $this->isJunkPolicyText($body)) {
                return;
            }
            $out['_sections'] ??= [];
            foreach ($out['_sections'] as $section) {
                if (is_array($section) && ($section['title'] ?? '') === $title) {
                    return;
                }
            }
            $out['_sections'][] = ['title' => $title, 'body' => $body];

            return;
        }

        // Guard: mis-paired rows (wrapper treated as one block) put checkout into check-in.
        if ($key === 'check_in' && preg_match('/^trả phòng\b/iu', $body) === 1) {
            $this->assignPolicy($out, 'check_out', preg_replace('/^trả phòng\s*/iu', '', $body) ?? $body);

            return;
        }
        if ($key === 'check_out' && preg_match('/^nhận phòng\b/iu', $body) === 1) {
            return;
        }

        if (in_array($key, ['check_in', 'check_out'], true)) {
            $time = $this->extractPolicyTimeWindow($body);
            $notes = $time !== ''
                ? $this->clean(preg_replace('/'.preg_quote($time, '/').'/u', '', $body, 1) ?? $body)
                : '';
            $value = $time !== '' ? $time : $body;
            if (! isset($out[$key]) || mb_strlen($value) >= mb_strlen((string) $out[$key])) {
                $out[$key] = $value;
            }
            if ($key === 'check_in' && $notes !== '' && preg_match('/giấy tờ|thẻ tín dụng|xuất trình|giờ bạn sẽ đến/iu', $notes) === 1) {
                if (empty($out['id']) || mb_strlen($notes) > mb_strlen((string) $out['id'])) {
                    $out['id'] = $notes;
                }
            }

            return;
        }
        if (! isset($out[$key]) || mb_strlen($body) > mb_strlen((string) $out[$key])) {
            $out[$key] = $body;
        }
    }

    private function extractPolicyTimeWindow(string $body): string
    {
        if (preg_match('/Từ\s+\d{1,2}:\d{2}\s*[-–]\s*\d{1,2}:\d{2}/u', $body, $m) === 1) {
            return $this->clean($m[0]);
        }
        if (preg_match('/(?:Từ|Trước|Đến)\s+\d{1,2}:\d{2}/u', $body, $m) === 1) {
            return $this->clean($m[0]);
        }
        if (preg_match('/\b\d{1,2}:\d{2}\b/u', $body, $m) === 1) {
            return $m[0];
        }

        return '';
    }

    private function cleanPolicyValue(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);
        // Adjacent block nodes often arrive glued: "trở lênCó giường"
        $text = preg_replace('/([\p{Ll}\p{N}])(\p{Lu})/u', '$1 $2', $text) ?? $text;
        $text = preg_replace('/([.!?…])([^\s\d])/u', '$1 $2', $text) ?? $text;
        $text = preg_replace('/["\'][a-z0-9_.]+["]\s*:\s*/iu', ' ', $text) ?? $text;
        $text = $this->clean($text);
        if ($this->isJunkPolicyText($text)) {
            return '';
        }
        if (mb_strlen($text) > 1200) {
            $text = Str::limit($text, 1200, '');
        }

        return rtrim($text, " \t.;");
    }

    private function isJunkPolicyText(mixed $text): bool
    {
        if (! is_string($text) || $text === '') {
            return true;
        }
        if (str_contains($text, '{') || str_contains($text, '}') || str_contains($text, '\\u')) {
            return true;
        }
        if (preg_match('/amount_with_currency|language_exception|famex_|bhqc_|paycom_|sr_last_room/i', $text)) {
            return true;
        }
        if (preg_match('/"[a-z0-9_]{8,}"\s*:/', $text)) {
            return true;
        }
        $underscores = preg_match_all('/[a-z]{3,}_[a-z]{3,}/i', $text);

        return $underscores >= 2;
    }

    /**
     * @return array{items: list<array{name: string, distance: string, category?: string}>, groups: array<string, list<array<string, mixed>>>}
     */
    private function nearby(?DOMXPath $xpath): array
    {
        $items = [];
        $groups = [];
        if (! $xpath) {
            return ['items' => $items, 'groups' => $groups];
        }

        $rootList = $xpath->query('//*[@id="surroundings_block"] | //*[@data-testid="location-block-container"]');
        $root = $rootList ? $rootList->item(0) : null;
        $poiBlocks = $root instanceof DOMElement
            ? ($xpath->query('.//*[@data-testid="poi-block"]', $root) ?: [])
            : ($xpath->query('//*[@data-testid="poi-block"]') ?: []);

        foreach ($poiBlocks as $block) {
            if (! $block instanceof DOMElement) {
                continue;
            }
            $heading = $this->clean($this->firstTextIn($xpath, $block, './/h3'));
            if ($heading === '' || preg_match('/xem phòng trống|hiển thị bản đồ/i', $heading)) {
                continue;
            }
            $category = $this->nearbyCategoryFromHeading($heading);
            foreach ($xpath->query('.//*[@data-testid="poi-block-list"]//li', $block) ?: [] as $li) {
                if (! $li instanceof DOMElement) {
                    continue;
                }
                $place = $this->poiPlace($xpath, $li, $category);
                if ($place === null) {
                    continue;
                }
                $items[] = $place;
                $groups[$category][] = $place;
            }
        }

        if ($items === []) {
            foreach ($xpath->query('//*[@data-testid="NearbyPlaces"]//li | //*[@data-testid="nearby-place"] | //*[@id="surroundings"]//li') ?: [] as $li) {
                if (! $li instanceof DOMElement) {
                    continue;
                }
                $place = $this->poiPlace($xpath, $li, 'other');
                if ($place === null) {
                    continue;
                }
                $items[] = $place;
                $groups['other'][] = $place;
            }
        }

        return ['items' => $items, 'groups' => $groups];
    }

    /**
     * @return array{name: string, distance: string, category: string}|null
     */
    private function poiPlace(DOMXPath $xpath, DOMElement $li, string $category): ?array
    {
        $name = '';
        $distance = '';
        $cells = $xpath->query('.//*[@role="listitem"]/div/div', $li);
        if ($cells && $cells->length >= 2) {
            $nameEl = $cells->item(0);
            $distEl = $cells->item($cells->length - 1);
            if ($nameEl instanceof DOMElement) {
                $name = $this->poiNameFromCell($nameEl);
            }
            if ($distEl instanceof DOMElement) {
                $distance = $this->clean($this->nodeText($distEl));
            }
        }
        if ($name === '') {
            $blob = $this->clean($this->nodeText($li));
            if (preg_match('/^(.*?)\s+(\d+[.,]?\d*\s*(?:km|m|phút))\s*$/iu', $blob, $m)) {
                $name = $this->clean($m[1]);
                $distance = $this->clean($m[2]);
            } else {
                $name = $blob;
            }
        }
        if ($name === '' || mb_strlen($name) > 140 || preg_match('/khoảng cách đi bộ|hiển thị bản đồ|xem phòng trống/i', $name)) {
            return null;
        }

        return array_filter([
            'name' => $name,
            'distance' => $distance,
            'category' => $category,
        ]);
    }

    private function poiNameFromCell(DOMElement $cell): string
    {
        $parts = [];
        foreach ($cell->childNodes as $child) {
            if ($child instanceof DOMElement && strcasecmp($child->tagName, 'span') === 0) {
                $parts[] = $this->clean($this->nodeText($child));
            } elseif ($child instanceof \DOMText) {
                $parts[] = $this->clean($child->textContent);
            } elseif ($child instanceof DOMElement) {
                $parts[] = $this->clean($this->nodeText($child));
            }
        }
        $name = $this->clean(implode(' ', array_filter($parts)));

        return $name !== '' ? $name : $this->clean($this->nodeText($cell));
    }

    private function nearbyCategoryFromHeading(string $heading): string
    {
        $h = mb_strtolower($heading);
        $map = [
            'bãi biển' => 'beach',
            'beach' => 'beach',
            'thiên nhiên' => 'nature',
            'thác' => 'nature',
            'nhà hàng' => 'dining',
            'cà phê' => 'dining',
            'cafe' => 'dining',
            'sân bay' => 'transport',
            'giao thông' => 'transport',
            'tham quan' => 'landmark',
            'địa danh' => 'landmark',
            'mua sắm' => 'shop',
        ];
        foreach ($map as $needle => $key) {
            if (str_contains($h, $needle)) {
                return $key;
            }
        }

        return 'other';
    }

    private function propertyType(string $title, ?DOMXPath $xpath): string
    {
        $hay = mb_strtolower($title.' '.$this->firstText($xpath, '//*[@data-testid="PropertyBadges-wrapper"]'));
        foreach ([
            'resort' => 'resort',
            'villa' => 'villa',
            'homestay' => 'homestay',
            'apartment' => 'apartment',
            'căn hộ' => 'apartment',
            'hostel' => 'hostel',
            'bungalow' => 'bungalow',
            'glamping' => 'glamping',
            'boutique' => 'boutique',
            'hotel' => 'hotel',
            'khách sạn' => 'hotel',
        ] as $needle => $type) {
            if (str_contains($hay, $needle)) {
                return StaySeed::normalizeType($type);
            }
        }

        return 'hotel';
    }

    private function shortLocation(?string $address, string $title): ?string
    {
        if ($address) {
            $parts = array_map('trim', explode(',', $address));
            $parts = array_values(array_filter($parts, fn ($p) => $p !== '' && ! preg_match('/việt nam|vietnam/i', $p)));

            return $parts !== [] ? implode(', ', array_slice($parts, -3)) : $address;
        }

        return str_contains(mb_strtolower($title), 'phú quốc') ? 'Phú Quốc' : null;
    }

    private function unitType(string $name): string
    {
        $h = mb_strtolower($name);
        if (str_contains($h, 'villa') || str_contains($h, 'biệt thự')) {
            return 'entire_villa';
        }
        if (str_contains($h, 'căn hộ') || str_contains($h, 'apartment')) {
            return 'entire_apartment';
        }

        return 'hotel_room';
    }

    private function viewFromName(string $name): ?string
    {
        if (preg_match('/nhìn ra\s+(.+)$/iu', $name, $m)) {
            return $this->clean($m[0]);
        }

        return str_contains(mb_strtolower($name), 'ban công') ? 'Ban công' : null;
    }

    private function groupKeyFromHeading(string $heading): string
    {
        $h = mb_strtolower($heading);
        $map = [
            'cực kỳ phù hợp' => 'popular',
            'phòng tắm' => 'bathroom',
            'bathroom' => 'bathroom',
            'phòng ngủ' => 'bedroom',
            'nhìn' => 'view',
            'view' => 'view',
            'bếp' => 'kitchen',
            'kitchen' => 'kitchen',
            'hồ bơi' => 'pool_beach',
            'chăm sóc sức khỏe' => 'wellness',
            'spa' => 'wellness',
            'ngoài trời' => 'outdoor',
            'hoạt động' => 'outdoor',
            'đồ ăn' => 'dining',
            'thức uống' => 'dining',
            'nhà hàng' => 'dining',
            'internet' => 'media',
            'wifi' => 'media',
            'đậu xe' => 'parking',
            'đỗ xe' => 'parking',
            'an ninh' => 'safety',
            'an toàn' => 'safety',
            'gia đình' => 'family',
            'giải trí' => 'family',
            'doanh nhân' => 'business',
            'lau dọn' => 'general',
            'lễ tân' => 'general',
            'tổng quát' => 'general',
            'tiện nghi' => 'general',
            'tầm nhìn' => 'view',
            'ngôn ngữ' => 'other',
        ];
        foreach ($map as $needle => $key) {
            if (str_contains($h, $needle)) {
                return $key;
            }
        }

        return Str::slug($heading) ?: 'other';
    }

    private function capacityFromLabel(string $label): ?int
    {
        $adults = 0;
        $kids = 0;
        if (preg_match('/(\d+)\s*người lớn/iu', $label, $m)) {
            $adults = (int) $m[1];
        }
        if (preg_match('/(\d+)\s*trẻ em/iu', $label, $m)) {
            $kids = (int) $m[1];
        }
        $n = $adults + $kids;
        if ($n > 0) {
            return $n;
        }
        if (preg_match('/(\d+)\s*(?:khách|người)(?!\s*lớn)/iu', $label, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function parseScore(string $raw): float
    {
        $raw = str_replace(',', '.', trim($raw));
        if (! preg_match('/\d+(\.\d+)?/', $raw, $m)) {
            return 0.0;
        }

        return round((float) $m[0], 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function stayPack(?DOMXPath $xpath): array
    {
        if (! $xpath) {
            return [];
        }
        $node = $xpath->query('//*[@id="vt-stay-pack"]')?->item(0);
        if (! $node) {
            return [];
        }
        $decoded = json_decode(trim($node->textContent ?? ''), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function stayPackFromHtmlString(string $html): array
    {
        if (! preg_match('/id="vt-stay-pack"[^>]*>(.*?)<\/script>/si', $html, $m)) {
            $tail = strlen($html) > 400000 ? substr($html, -400000) : $html;
            if (! preg_match('/id="vt-stay-pack"[^>]*>(.*?)<\/script>/si', $tail, $m)) {
                return [];
            }
        }
        $raw = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  ...$packs
     * @return array<string, mixed>
     */
    private function mergeStayPacks(array ...$packs): array
    {
        $out = [
            'photos' => [],
            'rooms' => [],
            'facilities_html' => '',
            'policies_html' => '',
            'hprt_html' => '',
            'crawl_dates' => null,
        ];
        foreach ($packs as $pack) {
            if ($pack === []) {
                continue;
            }
            $photos = is_array($pack['photos'] ?? null) ? $pack['photos'] : [];
            $rooms = is_array($pack['rooms'] ?? null) ? $pack['rooms'] : [];
            if (count($photos) > count($out['photos'])) {
                $out['photos'] = $photos;
            }
            if (count($rooms) > count($out['rooms'])) {
                $out['rooms'] = $rooms;
            }
            foreach (['facilities_html', 'policies_html', 'hprt_html'] as $key) {
                $val = trim((string) ($pack[$key] ?? ''));
                if ($val !== '' && strlen($val) > strlen((string) $out[$key])) {
                    $out[$key] = $val;
                }
            }
            if (is_array($pack['crawl_dates'] ?? null) && ($out['crawl_dates'] === null || $out['crawl_dates'] === [])) {
                $out['crawl_dates'] = $pack['crawl_dates'];
            }
            if (! empty($pack['debug'])) {
                $out['debug'] = $pack['debug'];
            }
            if (! empty($pack['error'])) {
                $out['error'] = $pack['error'];
            }
        }

        return $out;
    }

    private function xpathFromHtml(string $html): ?DOMXPath
    {
        $dom = $this->loadDom('<div id="vt-pack">'.$html.'</div>');

        return $dom ? new DOMXPath($dom) : null;
    }

    /**
     * @param  list<mixed>  $rows
     * @return list<array<string, mixed>>
     */
    private function roomsFromPack(array $rows): array
    {
        $out = [];
        $seen = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $fromHtml = $this->roomFieldsFromRpHtml((string) ($row['html'] ?? ''));
            $rowAttrs = is_array($row['attrs'] ?? null) ? $row['attrs'] : [];
            $name = $this->clean((string) ($row['name'] ?? $fromHtml['name'] ?? ''));
            $roomId = $this->clean((string) ($row['room_id'] ?? $rowAttrs['room_id'] ?? ''));
            $hash = $this->clean((string) ($row['hash'] ?? $rowAttrs['hash'] ?? ''));
            if ($roomId === '' && preg_match('/#?RD(\d+)/i', $hash, $hm)) {
                $roomId = $hm[1];
            }
            if ($hash === '' && $roomId !== '') {
                $hash = '#RD'.$roomId;
            }
            $dedupeKey = $roomId !== '' ? 'id:'.$roomId : ($name !== '' ? 'name:'.$name : '');
            if ($dedupeKey === '' || isset($seen[$dedupeKey])) {
                continue;
            }
            $seen[$dedupeKey] = true;
            $text = $this->clean((string) ($row['text'] ?? ''));
            $description = $this->clean((string) ($row['description'] ?? $fromHtml['description'] ?? ''));
            if ($description === '' && $text !== '') {
                $description = Str::limit($text, 900, '');
            }
            $bed = $this->clean((string) ($row['bed'] ?? $fromHtml['bed'] ?? ''));
            $beds = [];
            if ($bed !== '') {
                $beds = [$bed];
            } elseif (preg_match_all('/\d+\s*giường[^.,]{0,48}/iu', $text, $bedHits)) {
                $beds = array_values(array_unique(array_map(fn ($t) => $this->clean($t), $bedHits[0])));
            }
            $capacity = $this->capacityFromLabel($text);
            $size = isset($row['size_sqm']) && is_numeric($row['size_sqm'])
                ? (int) $row['size_sqm']
                : ($fromHtml['size_sqm'] ?? null);
            if ($size === null && (preg_match('/(\d+)\s*m\s*²/u', $text, $m) || preg_match('/(\d+)\s*m2\b/iu', $text, $m))) {
                $size = (int) $m[1];
            }
            $amenities = [];
            foreach (is_array($row['amenities'] ?? null) ? $row['amenities'] : ($fromHtml['amenities'] ?? []) as $item) {
                $t = $this->cleanFacilityLabel((string) $item);
                if ($t === '' || mb_strlen($t) >= 120 || preg_match('/xem phòng|đóng|close|đặt ngay|chọn phòng/i', $t)) {
                    continue;
                }
                $amenities[] = $t;
            }
            $groups = [];
            $rawGroups = is_array($row['amenity_groups'] ?? null) ? $row['amenity_groups'] : ($fromHtml['amenity_groups'] ?? []);
            foreach ($rawGroups as $heading => $items) {
                $key = $this->groupKeyFromHeading((string) $heading);
                foreach (is_array($items) ? $items : [] as $item) {
                    $t = $this->cleanFacilityLabel((string) $item);
                    if ($t === '') {
                        continue;
                    }
                    $groups[$key][] = $t;
                    $amenities[] = $t;
                }
            }
            foreach ($groups as $key => $items) {
                $groups[$key] = array_values(array_unique($items));
            }
            $highlights = [];
            foreach (is_array($row['highlights'] ?? null) ? $row['highlights'] : ($fromHtml['highlights'] ?? []) as $item) {
                $t = $this->cleanFacilityLabel((string) $item);
                if ($t !== '' && mb_strlen($t) < 80) {
                    $highlights[] = $t;
                }
            }
            $photos = $this->collectPhotoList($row['photos'] ?? []);
            $photos = $this->mergePhotoLists($photos, $fromHtml['photos'] ?? []);
            $smoking = $this->clean((string) ($row['smoking'] ?? $fromHtml['smoking'] ?? ''));
            $view = $this->viewFromName($name);
            if ($view === null && ! empty($groups['view'][0])) {
                $view = $groups['view'][0];
            }
            $amenities = array_values(array_unique(array_merge($beds, $amenities, $highlights)));
            $rateOptions = is_array($row['rate_options'] ?? null)
                ? $row['rate_options']
                : (is_array($rowAttrs['rate_options'] ?? null) ? $rowAttrs['rate_options'] : []);
            $crawlDates = is_array($row['crawl_dates'] ?? null)
                ? $row['crawl_dates']
                : (is_array($rowAttrs['crawl_dates'] ?? null) ? $rowAttrs['crawl_dates'] : null);
            $scarcity = $this->clean((string) ($row['scarcity'] ?? $rowAttrs['scarcity'] ?? ''));
            $codeStem = $roomId !== '' ? $roomId : Str::slug($name);
            if ($codeStem === '') {
                continue;
            }
            $out[] = [
                'code' => 'bk-'.$codeStem,
                'name' => $name !== '' ? $name : ('Phòng '.$roomId),
                'capacity' => $capacity,
                'description' => $description !== '' ? $description : null,
                'price_from' => isset($row['price_from']) && is_numeric($row['price_from'])
                    ? (float) $row['price_from']
                    : null,
                'amenities' => $amenities,
                'photos' => $photos,
                'attrs' => array_filter([
                    'room_id' => $roomId !== '' ? $roomId : null,
                    'hash' => $hash !== '' ? $hash : null,
                    'bed' => $beds !== [] ? implode(', ', $beds) : null,
                    'size_sqm' => $size,
                    'photos' => $photos !== [] ? $photos : null,
                    'highlights' => $highlights !== [] ? array_values(array_unique($highlights)) : null,
                    'amenity_groups' => $groups !== [] ? $groups : null,
                    'smoking' => $smoking !== '' ? $smoking : null,
                    'unit_type' => $this->unitType($name),
                    'view' => $view,
                    'scarcity' => $scarcity !== '' ? $scarcity : null,
                    'rate_options' => $rateOptions !== [] ? $rateOptions : null,
                    'crawl_dates' => $crawlDates,
                ], fn ($v) => $v !== null && $v !== '' && $v !== []),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function roomFieldsFromRpHtml(string $html): array
    {
        if (trim($html) === '' || ! str_contains($html, 'rp-')) {
            return [];
        }
        $xpath = $this->xpathFromHtml($html);
        if (! $xpath) {
            return [];
        }
        $name = $this->firstText($xpath, '//*[@data-testid="rp-room-title"]');
        $description = $this->firstText($xpath, '//*[@data-testid="rp-description"]');
        $sizeText = $this->firstText($xpath, '//*[@data-testid="rp-room-size"] | //*[@data-testid="room-size-icon"]');
        $size = null;
        if (preg_match('/(\d+)\s*m/iu', $sizeText, $m)) {
            $size = (int) $m[1];
        }
        $bed = '';
        foreach ($xpath->query('//*[starts-with(@data-testid,"bed-icon")]') ?: [] as $icon) {
            $cur = $icon->parentNode;
            while ($cur instanceof DOMElement) {
                $t = $this->clean($this->nodeText($cur));
                if (preg_match('/giường/iu', $t) && mb_strlen($t) < 90) {
                    $bed = $t;
                    break 2;
                }
                $cur = $cur->parentNode;
            }
        }
        $smoking = $this->firstText($xpath, '//*[@data-testid="rp-smoking-policy"]/..');
        $highlights = $this->listTexts($xpath, '//*[@data-testid="rp-highlights-test"]//*[@data-testid="property-unit-facility-badge-icon"]');
        $groups = [];
        $amenities = [];
        foreach ($xpath->query('//*[@data-testid="rp-facilities"]') ?: [] as $ul) {
            if (! $ul instanceof DOMElement) {
                continue;
            }
            $heading = '';
            $section = $this->closest($ul, 'section');
            if ($section) {
                $heading = $this->firstTextIn($xpath, $section, './/h2');
            }
            $items = $this->listTextsIn($xpath, $ul, './/li');
            $key = $this->groupKeyFromHeading($heading !== '' ? $heading : 'Tiện nghi');
            $groups[$key] = array_values(array_unique(array_merge($groups[$key] ?? [], $items)));
            $amenities = array_merge($amenities, $items);
        }
        $photos = [];
        foreach ($xpath->query('//*[@data-testid="roomPagePhotos"]//*[@style]') ?: [] as $el) {
            if (! $el instanceof DOMElement) {
                continue;
            }
            if (preg_match('/url\((["\']?)([^"\')]+)\1\)/i', $el->getAttribute('style'), $m)) {
                $photos[] = ['url' => $this->normalizeHotelPhotoUrl($m[2]), 'alt' => $name];
            }
        }
        foreach ($xpath->query('//*[@data-testid="roomPagePhotos"]//img | //img[contains(@alt,"Hình của")]') ?: [] as $img) {
            if (! $img instanceof DOMElement) {
                continue;
            }
            $src = $img->getAttribute('src') ?: $this->firstSrcsetUrl($img->getAttribute('srcset'));
            $photos[] = ['url' => $this->normalizeHotelPhotoUrl($src), 'alt' => $img->getAttribute('alt')];
        }

        return array_filter([
            'name' => $name,
            'description' => $description,
            'size_sqm' => $size,
            'bed' => $bed,
            'smoking' => $smoking,
            'highlights' => $highlights,
            'amenity_groups' => $groups,
            'amenities' => array_values(array_unique($amenities)),
            'photos' => $this->collectPhotoList($photos),
        ]);
    }

    /**
     * @param  list<mixed>  $photos
     * @return list<array{url: string, alt: string}>
     */
    private function collectPhotoList(mixed $photos): array
    {
        $out = [];
        $seen = [];
        foreach (is_array($photos) ? $photos : [] as $photo) {
            $url = is_array($photo) ? (string) ($photo['url'] ?? '') : (string) $photo;
            $local = is_array($photo) ? (string) ($photo['local_path'] ?? $photo['path'] ?? '') : '';
            $url = $this->normalizeHotelPhotoUrl($url);
            if (! $this->isHotelPhotoUrl($url) && $local === '') {
                continue;
            }
            $id = $this->hotelPhotoId($url);
            $key = $id ?? ($url !== '' ? $url : $local);
            $hasSig = str_contains($url, '?k=') || str_contains($url, '&k=');
            if (isset($seen[$key])) {
                $idx = $seen[$key];
                if ($local !== '' && empty($out[$idx]['local_path'])) {
                    $out[$idx]['local_path'] = $local;
                }
                if ($hasSig && ! str_contains((string) ($out[$idx]['url'] ?? ''), 'k=')) {
                    $out[$idx]['url'] = $url;
                }

                continue;
            }
            $seen[$key] = count($out);
            $row = [
                'url' => $url,
                'alt' => is_array($photo) ? $this->clean((string) ($photo['alt'] ?? '')) : '',
            ];
            if ($local !== '') {
                $row['local_path'] = $local;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  list<array{url: string, alt: string}>  $a
     * @param  list<array{url: string, alt: string}>  $b
     * @return list<array{url: string, alt: string}>
     */
    private function mergePhotoLists(array $a, array $b): array
    {
        return $this->collectPhotoList(array_merge($a, $b));
    }

    private function normalizeHotelPhotoUrl(string $url): string
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = preg_replace('#/hotel/(max\d+|square\d+)/#i', '/hotel/max1024x768/', $url) ?? $url;

        return $url;
    }

    private function hotelPhotoId(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (preg_match('#/(\d+)\.jpe?g$#i', $path, $m)) {
            return $m[1];
        }

        return null;
    }

    private function loadDom(string $html): ?DOMDocument
    {
        $dom = new DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $ok = $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $ok ? $dom : null;
    }

    private function firstText(?DOMXPath $xpath, string $query): string
    {
        if (! $xpath) {
            return '';
        }
        $node = $xpath->query($query)?->item(0);

        return $node ? $this->clean($this->nodeText($node)) : '';
    }

    private function firstTextIn(DOMXPath $xpath, DOMElement $ctx, string $query): string
    {
        $node = $xpath->query($query, $ctx)?->item(0);

        return $node ? $this->clean($this->nodeText($node)) : '';
    }

    private function firstAttr(?DOMXPath $xpath, string $query): string
    {
        if (! $xpath) {
            return '';
        }
        $node = $xpath->query($query)?->item(0);

        return $node ? trim((string) $node->nodeValue) : '';
    }

    /** @return list<string> */
    private function listTexts(?DOMXPath $xpath, string $query): array
    {
        if (! $xpath) {
            return [];
        }
        $out = [];
        foreach ($xpath->query($query) ?: [] as $node) {
            $t = $this->clean($this->nodeText($node));
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return array_values(array_unique($out));
    }

    /** @return list<string> */
    private function listTextsIn(DOMXPath $xpath, \DOMNode $ctx, string $query): array
    {
        $out = [];
        foreach ($xpath->query($query, $ctx) ?: [] as $node) {
            $t = $this->clean($this->nodeText($node));
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return array_values(array_unique($out));
    }

    private function nodeText(\DOMNode $node): string
    {
        return html_entity_decode($node->textContent ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Prefer spaced text between block elements (DOM textContent glues sibling divs).
     */
    private function readableBlockText(\DOMNode $node): string
    {
        if ($node instanceof \DOMText) {
            return $node->textContent ?? '';
        }
        if (! $node instanceof DOMElement) {
            return '';
        }
        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style', 'noscript', 'svg'], true)) {
            return '';
        }
        if ($tag === 'br') {
            return "\n";
        }
        $imgAlt = '';
        if ($tag === 'img') {
            $imgAlt = trim((string) $node->getAttribute('alt'));
        }
        $parts = [];
        if ($imgAlt !== '') {
            $parts[] = $imgAlt;
        }
        $blockish = in_array($tag, ['p', 'div', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'tr', 'section', 'article'], true);
        foreach ($node->childNodes as $child) {
            $chunk = $this->readableBlockText($child);
            if ($chunk === '') {
                continue;
            }
            $parts[] = $chunk;
        }
        $joined = implode($blockish ? "\n" : ' ', $parts);
        if ($blockish) {
            return "\n".$joined."\n";
        }

        return $joined;
    }

    private function childByClass(DOMElement $parent, string $class): ?DOMElement
    {
        foreach ($parent->getElementsByTagName('*') as $el) {
            if ($el instanceof DOMElement && str_contains($el->getAttribute('class'), $class)) {
                return $el;
            }
        }

        return null;
    }

    private function closest(DOMElement $node, string $tag): ?DOMElement
    {
        $cur = $node;
        while ($cur->parentNode instanceof DOMElement) {
            $cur = $cur->parentNode;
            if (strcasecmp($cur->tagName, $tag) === 0) {
                return $cur;
            }
        }

        return null;
    }

    private function clean(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
