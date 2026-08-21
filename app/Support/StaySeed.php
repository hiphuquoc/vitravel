<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Bổ sung field lưu trú còn thiếu khi seed — không ghi đè dữ liệu seed đã khai.
 */
final class StaySeed
{
    /** @var array<string, string> */
    private const TYPE_ALIASES = [
        'resort/hotel' => 'resort',
        'boutique hotel' => 'boutique',
        'boutique resort' => 'boutique',
        'luxury resort' => 'resort',
        'luxury villa resort' => 'villa',
        'villa resort' => 'villa',
        'bungalow/resort' => 'bungalow',
        'floating bungalow' => 'floating',
        'cruise cabin' => 'cabin',
        'camping' => 'camping',
        'glamping' => 'glamping',
        'homestay' => 'homestay',
        'hotel' => 'hotel',
        'resort' => 'resort',
        'villa' => 'villa',
        'boutique' => 'boutique',
        'bungalow' => 'bungalow',
        'cabin' => 'cabin',
        'floating' => 'floating',
        'apartment' => 'apartment',
        'hostel' => 'hostel',
    ];

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function complete(array $row): array
    {
        if (($row['cluster'] ?? '') !== 'stay') {
            return $row;
        }

        $attrs = is_array($row['attrs'] ?? null) ? $row['attrs'] : [];
        $type = self::normalizeType((string) ($attrs['property_type'] ?? 'hotel'));
        $attrs['property_type'] = $type;
        $attrs['check_in'] = (string) ($attrs['check_in'] ?? '14:00');
        $attrs['check_out'] = (string) ($attrs['check_out'] ?? '12:00');
        $attrs['amenities'] = is_array($attrs['amenities'] ?? null) ? array_values($attrs['amenities']) : [];

        $location = (string) ($row['location_label'] ?? '');
        $title = (string) ($row['title'] ?? 'chỗ nghỉ');
        $fromCrawl = filled(data_get($attrs, 'crawl.source'));

        if (! $fromCrawl) {
            $attrs['cancellation_policy'] = trim((string) ($attrs['cancellation_policy'] ?? '')) !== ''
                ? $attrs['cancellation_policy']
                : 'Huỷ/đổi ngày theo chính sách chỗ nghỉ và gói rate — báo giá chi tiết khi đặt qua :brand. Không cam kết hoàn 100% trừ khi chỗ nghỉ xác nhận bằng văn bản.';
            $attrs['child_policy'] = trim((string) ($attrs['child_policy'] ?? '')) !== ''
                ? $attrs['child_policy']
                : 'Trẻ em ngủ chung tuỳ loại phòng và sức chứa. Extra bed / cũi báo giá riêng — không tự ý thêm khách ngoài sức chứa đã xác nhận.';
            $attrs['pet_policy'] = trim((string) ($attrs['pet_policy'] ?? '')) !== ''
                ? $attrs['pet_policy']
                : 'Thú cưng: theo chính sách từng chỗ nghỉ (thường không nhận, trừ hỗ trợ đặc biệt đã xác nhận trước).';
            $attrs['payment_policy'] = trim((string) ($attrs['payment_policy'] ?? '')) !== ''
                ? $attrs['payment_policy']
                : 'Đặt cọc / thanh toán theo xác nhận booking. Giá “từ” là tham khảo / đêm — không phải giá chốt cho mọi ngày.';
            $attrs['id_required_policy'] = trim((string) ($attrs['id_required_policy'] ?? '')) !== ''
                ? $attrs['id_required_policy']
                : 'CCCD/hộ chiếu bản gốc khi check-in — tên khách khớp booking. Khách nước ngoài mang hộ chiếu.';

            if (empty($attrs['nearby_groups']) && $location !== '') {
                $attrs['nearby_groups'] = [
                    'other' => [
                        ['name' => $location, 'distance' => 'Khu vực chỗ nghỉ', 'icon' => 'map-pin'],
                    ],
                ];
            }
            unset($attrs['nearby']);
        }

        $row['attrs'] = $attrs;

        if (trim((string) ($row['content'] ?? '')) === '') {
            $row['content'] = self::defaultContent($row, $type);
        }

        $row['faqs'] = self::mergeFaqs(
            is_array($row['faqs'] ?? null) ? $row['faqs'] : [],
            self::defaultFaqs($row, $attrs),
        );

        $options = is_array($row['options'] ?? null) ? $row['options'] : [];
        if ($options === [] && ! $fromCrawl) {
            $price = $row['price_from'] ?? null;
            $options = [[
                'code' => Str::slug((string) ($row['code'] ?? 'stay')).'-std',
                'name' => 'Phòng tiêu chuẩn',
                'description' => 'Hạng phòng tiêu chuẩn — sức chứa và giá xác nhận khi báo giá.',
                'price_from' => $price,
                'capacity' => 2,
                'amenities' => ['Wi-Fi', 'Điều hoà'],
            ]];
        }
        $row['options'] = array_map(fn ($opt) => self::normalizeOption(is_array($opt) ? $opt : []), $options);

        if (empty($row['en']['content'] ?? null) && is_array($row['en'] ?? null) && isset($row['en']['summary'])) {
            $row['en']['content'] = '<p>'.e((string) $row['en']['summary']).'</p>';
        }

        unset($title);

        return $row;
    }

    public static function normalizeType(string $raw): string
    {
        $key = mb_strtolower(trim($raw));
        if ($key === '') {
            return 'hotel';
        }
        if (isset(self::TYPE_ALIASES[$key])) {
            return self::TYPE_ALIASES[$key];
        }
        $allowed = array_keys(config('stay.property_types', []));
        if (in_array($key, $allowed, true)) {
            return $key;
        }
        foreach (self::TYPE_ALIASES as $alias => $canon) {
            if (str_contains($key, $alias)) {
                return $canon;
            }
        }

        return 'hotel';
    }

    /**
     * @param  array<string, mixed>  $opt
     * @return array<string, mixed>
     */
    public static function normalizeOption(array $opt): array
    {
        $attrs = is_array($opt['attrs'] ?? null) ? $opt['attrs'] : [];
        $amenities = $opt['amenities'] ?? [];
        $amenityList = is_array($amenities) ? $amenities : [];

        if (empty($attrs['bed']) && ! empty($opt['bed_label'])) {
            $attrs['bed'] = $opt['bed_label'];
        }
        if (empty($attrs['size_sqm']) && ! empty($opt['size_sqm'])) {
            $attrs['size_sqm'] = (int) $opt['size_sqm'];
        }
        if (empty($attrs['view']) && ! empty($opt['view'])) {
            $attrs['view'] = $opt['view'];
        }
        if (empty($attrs['photos'])) {
            $fromOpt = $opt['photos'] ?? null;
            if (is_array($fromOpt) && $fromOpt !== []) {
                $attrs['photos'] = $fromOpt;
            }
        }

        foreach ($amenityList as $item) {
            $s = (string) $item;
            if (empty($attrs['size_sqm']) && preg_match('/(\d+)\s*m²/u', $s, $m)) {
                $attrs['size_sqm'] = (int) $m[1];
            }
            if (empty($attrs['bed']) && preg_match('/giường|king|queen|twin|giuong/iu', $s)) {
                $attrs['bed'] = $s;
            }
            if (empty($attrs['view']) && preg_match('/view|hướng|bien|biển|vịnh|núi/iu', $s)) {
                $attrs['view'] = $s;
            }
        }

        if (empty($attrs['highlights']) && $amenityList !== []) {
            $attrs['highlights'] = array_values(array_slice(array_map('strval', $amenityList), 0, 8));
        }
        if (empty($attrs['beds']) && empty($attrs['bedrooms']) && ! empty($attrs['bed'])) {
            $attrs['beds'] = StayFacilities::normalizeBeds($attrs);
        }
        if (empty($attrs['amenity_groups']) && $amenityList !== []) {
            $grouped = [];
            foreach (StayFacilities::displayGroups(array_map('strval', $amenityList)) as $group) {
                $grouped[$group['key']] = $group['items'];
            }
            if ($grouped !== []) {
                $attrs['amenity_groups'] = $grouped;
            }
        }

        if (! empty($attrs)) {
            $opt['attrs'] = $attrs;
        }

        return $opt;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function defaultContent(array $row, string $type): string
    {
        $title = e((string) ($row['title'] ?? 'Chỗ nghỉ'));
        $summary = e((string) ($row['summary'] ?? ''));
        $location = e((string) ($row['location_label'] ?? ''));
        $typeLabel = e((string) (config("stay.property_types.{$type}") ?? 'Chỗ nghỉ'));
        $highlights = is_array($row['highlights'] ?? null) ? $row['highlights'] : [];
        $lis = '';
        foreach (array_slice($highlights, 0, 6) as $h) {
            $lis .= '<li>'.e((string) $h).'</li>';
        }

        $html = "<p>{$title} là {$typeLabel}";
        if ($location !== '') {
            $html .= " tại {$location}";
        }
        $html .= '. '.($summary !== '' ? $summary : 'Đặt qua chuyên gia bản địa để nhận báo giá đúng ngày check-in.').'</p>';
        $html .= '<h2>Không gian &amp; tiện ích</h2>';
        if ($lis !== '') {
            $html .= "<ul>{$lis}</ul>";
        } else {
            $html .= '<p>Tiện ích và hạng phòng chi tiết được xác nhận trên báo giá — không tự ý thêm dịch vụ ngoài danh sách đã công bố.</p>';
        }
        $html .= '<h2>Đặt phòng</h2><p>Giá từ là mức tham khảo theo đêm. Báo giá chính xác theo ngày nhận phòng, hạng phòng và số khách — kiểm tra rồi mới thanh toán.</p>';

        return $html;
    }

    /**
     * FAQ seed (nếu có) đứng trước; FAQ mặc định chỉ thêm khi chưa có cùng câu hỏi.
     *
     * @param  list<array<string, mixed>>  $seedFaqs
     * @param  list<array{q: string, a: string}>  $defaults
     * @return list<array{q: string, a: string}>
     */
    private static function mergeFaqs(array $seedFaqs, array $defaults): array
    {
        $out = [];
        $seen = [];

        foreach (array_merge($seedFaqs, $defaults) as $faq) {
            if (! is_array($faq)) {
                continue;
            }
            $q = trim((string) ($faq['q'] ?? $faq['question'] ?? ''));
            $a = trim((string) ($faq['a'] ?? $faq['answer'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            $key = mb_strtolower($q);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = ['q' => $q, 'a' => $a];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $attrs
     * @return list<array{q: string, a: string}>
     */
    private static function defaultFaqs(array $row, array $attrs): array
    {
        $checkIn = (string) ($attrs['check_in'] ?? '14:00');
        $checkOut = (string) ($attrs['check_out'] ?? '12:00');

        return [
            [
                'q' => 'Giờ nhận phòng và trả phòng?',
                'a' => "Nhận phòng {$checkIn}, trả phòng {$checkOut} (theo chỗ nghỉ). Nhận sớm / trả muộn tuỳ tình trạng phòng — xác nhận khi đặt, không tự ý hứa trước.",
            ],
            [
                'q' => 'Giá “từ” đã gồm ăn sáng chưa?',
                'a' => 'Giá từ là tham khảo / đêm theo hạng tiêu chuẩn. Ăn sáng, đưa đón và phụ phí ghi rõ trên báo giá từng đợt — không mặc định gồm mọi dịch vụ.',
            ],
            [
                'q' => 'Trẻ em ngủ chung được không?',
                'a' => (string) ($attrs['child_policy'] ?? ''),
            ],
            [
                'q' => 'Chính sách huỷ / đổi ngày?',
                'a' => (string) ($attrs['cancellation_policy'] ?? ''),
            ],
            [
                'q' => 'Cần giấy tờ gì khi check-in?',
                'a' => (string) ($attrs['id_required_policy'] ?? ''),
            ],
            [
                'q' => 'Có thể gộp phòng với tour / vé tàu không?',
                'a' => 'Có — đặt qua :brand để gộp lưu trú với vận chuyển và trải nghiệm trong một báo giá, một đầu mối chăm sóc.',
            ],
        ];
    }
}
