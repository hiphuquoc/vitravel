<?php

namespace App\Support;

use App\Models\HomeSection;
use Throwable;

class HomePageDefaults
{
    /**
     * USP mặc định — ưu tiên `usps` trong project/seed_{profile}.php khi có ProjectContext / useProfile.
     *
     * @return list<array{icon: string, sort: int, vi: array{title: string, description: string}, en: array{title: string, description: string}}>
     */
    public static function usps(): array
    {
        $seed = self::seedGet('usps', []);
        if (is_array($seed) && $seed !== []) {
            $out = [];
            foreach (array_values($seed) as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $out[] = [
                    'icon' => (string) ($row['icon'] ?? 'support'),
                    'sort' => (int) ($row['sort'] ?? $i),
                    'vi' => [
                        'title' => (string) ($row['vi']['title'] ?? ''),
                        'description' => (string) ($row['vi']['description'] ?? ''),
                    ],
                    'en' => [
                        'title' => (string) ($row['en']['title'] ?? $row['vi']['title'] ?? ''),
                        'description' => (string) ($row['en']['description'] ?? $row['vi']['description'] ?? ''),
                    ],
                ];
            }
            if ($out !== []) {
                return apply_site_brand_deep($out);
            }
        }

        return apply_site_brand_deep(self::fallbackUsps());
    }

    /**
     * Section copy trang chủ — ưu tiên `home_sections` trong seed dự án (kèm `quick_inquiry`).
     *
     * @return list<array{key: string, sort: int, vi: array<string, mixed>, en: array<string, mixed>}>
     */
    public static function sections(): array
    {
        $byKey = [];
        foreach (self::fallbackSections() as $row) {
            $byKey[$row['key']] = $row;
        }

        $seed = self::seedGet('home_sections', []);
        if (is_array($seed) && $seed !== []) {
            foreach ($seed as $key => $byLocale) {
                if (! is_string($key) || ! is_array($byLocale)) {
                    continue;
                }
                $base = $byKey[$key] ?? [
                    'key' => $key,
                    'sort' => count($byKey),
                    'vi' => [],
                    'en' => [],
                ];
                foreach (['vi', 'en'] as $locale) {
                    if (! empty($byLocale[$locale]) && is_array($byLocale[$locale])) {
                        $base[$locale] = array_merge(
                            $base[$locale] ?? [],
                            self::normalizeSectionTranslation($byLocale[$locale]),
                        );
                    }
                }
                $byKey[$key] = $base;
            }
        }

        $ordered = [];
        foreach (HomeSection::predefinedKeys() as $i => $key) {
            if (! isset($byKey[$key])) {
                continue;
            }
            $row = $byKey[$key];
            $row['sort'] = $i;
            $ordered[] = $row;
        }

        return apply_site_brand_deep($ordered !== [] ? $ordered : array_values($byKey));
    }

    /** @return mixed */
    private static function seedGet(string $key, mixed $default = null): mixed
    {
        try {
            return ProjectSeed::get($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    /**
     * @param  array<string, mixed>  $src
     * @return array<string, mixed>
     */
    private static function normalizeSectionTranslation(array $src): array
    {
        $map = [
            'eyebrow' => 'eyebrow',
            'title' => 'title',
            'subtitle' => 'subtitle',
            'body' => 'body',
            'meta_line' => 'meta_line',
            'metaLine' => 'meta_line',
            'cta_label' => 'cta_label',
            'ctaLabel' => 'cta_label',
            'cta_url' => 'cta_url',
            'ctaUrl' => 'cta_url',
            'image_alt' => 'image_alt',
            'imageAlt' => 'image_alt',
        ];

        $out = [];
        foreach ($map as $from => $to) {
            if (array_key_exists($from, $src)) {
                $out[$to] = $src[$from];
            }
        }

        return $out;
    }

    /** @return list<array{icon: string, sort: int, vi: array{title: string, description: string}, en: array{title: string, description: string}}> */
    private static function fallbackUsps(): array
    {
        return [
            [
                'icon' => 'expert',
                'sort' => 0,
                'vi' => [
                    'title' => 'trải nghiệm bản địa',
                    'description' => 'Hành trình do chuyên gia địa phương thiết kế, chọn đúng mùa đẹp, tuyến điểm hay và trải nghiệm văn hoá chân thực.',
                ],
                'en' => [
                    'title' => 'local-led experiences',
                    'description' => 'Curated by local experts who pick the best seasons, routes, and truly authentic cultural moments.',
                ],
            ],
            [
                'icon' => 'refund',
                'sort' => 1,
                'vi' => [
                    'title' => 'giá rõ ràng, minh bạch',
                    'description' => 'Báo giá trọn gói, không chi phí ẩn. Chính sách huỷ linh hoạt và hoàn tiền rõ ràng ngay từ đầu.',
                ],
                'en' => [
                    'title' => 'clear, transparent pricing',
                    'description' => 'All-in pricing with no hidden fees. Flexible cancellation and clear refund terms from the start.',
                ],
            ],
            [
                'icon' => 'value',
                'sort' => 2,
                'vi' => [
                    'title' => 'giá trị xứng đáng',
                    'description' => 'Dịch vụ chọn lọc chất lượng cao, tối ưu chi phí để bạn nhận được trải nghiệm tốt nhất trong ngân sách.',
                ],
                'en' => [
                    'title' => 'best value for your trip',
                    'description' => 'Carefully selected quality services to give you the best travel experience within your budget.',
                ],
            ],
            [
                'icon' => 'support',
                'sort' => 3,
                'vi' => [
                    'title' => 'hỗ trợ xuyên suốt',
                    'description' => 'Đội ngũ đồng hành trước, trong và sau chuyến đi, hỗ trợ nhanh chóng để hành trình luôn suôn sẻ.',
                ],
                'en' => [
                    'title' => 'seamless travel support',
                    'description' => 'Dedicated support before, during, and after your trip, ensuring everything runs smoothly.',
                ],
            ],
        ];
    }

    /** @return list<array{key: string, sort: int, vi: array<string, mixed>, en: array<string, mixed>}> */
    private static function fallbackSections(): array
    {
        return [
            [
                'key' => HomeSection::KEY_COMPANY_INTRO,
                'sort' => 0,
                'vi' => [
                    'eyebrow' => 'Chuyên gia du lịch Việt',
                    'title' => 'Hành trình chân thật, thiết kế bởi người bản địa',
                    'body' => ':brand là đại lý lữ hành đặt trụ sở tại Việt Nam, kết nối du khách quốc tế với Việt Nam và Đông Nam Á. Chúng tôi không bán những tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của đội ngũ chuyên gia bản địa tại từng điểm đến.',
                    'meta_line' => 'Giấy phép lữ hành quốc tế số 01-2234/TCDL-GP-LHQT',
                    'cta_label' => 'Tìm hiểu về chúng tôi',
                    'cta_url' => '/ve-chung-toi',
                    'image_alt' => 'Ảnh đội ngũ :brand tại văn phòng',
                ],
                'en' => [
                    'eyebrow' => 'Vietnam travel experts',
                    'title' => 'Authentic journeys, designed by locals',
                    'body' => ':brand is a Vietnam-based travel agency connecting international guests with Vietnam and Southeast Asia. We do not sell off-the-shelf packages — every itinerary is tailored from real on-the-ground experience by local experts in each destination.',
                    'meta_line' => 'International travel license No. 01-2234/TCDL-GP-LHQT',
                    'cta_label' => 'Learn about us',
                    'cta_url' => '/ve-chung-toi',
                    'image_alt' => ':brand team at our office',
                ],
            ],
            [
                'key' => HomeSection::KEY_FEATURED_TOURS,
                'sort' => 1,
                'vi' => [
                    'eyebrow' => 'Được yêu thích nhất',
                    'title' => 'Những tour được yêu cầu nhiều nhất',
                    'subtitle' => 'Ba hành trình khách hàng đặt và đánh giá cao nhất trong 12 tháng qua.',
                ],
                'en' => [
                    'eyebrow' => 'Most popular',
                    'title' => 'Our most requested tours',
                    'subtitle' => 'Three itineraries our guests book and rate highest over the past 12 months.',
                ],
            ],
            [
                'key' => HomeSection::KEY_FEATURED_CRUISES,
                'sort' => 2,
                'vi' => [
                    'eyebrow' => 'Hành trình biển',
                    'title' => 'Trải nghiệm trên mặt nước đáng nhớ',
                    'subtitle' => 'Những hải trình được yêu thích nhất — nơi việc di chuyển trở thành một phần của trải nghiệm, mở ra góc nhìn trọn vẹn về biển đảo.',
                ],
                'en' => [
                    'eyebrow' => 'Sea journeys',
                    'title' => 'Memorable experiences on the water',
                    'subtitle' => 'Our most loved voyages — where getting there becomes part of the experience, opening a fuller view of islands and sea.',
                ],
            ],
            [
                'key' => HomeSection::KEY_FEATURED_TRAINS,
                'sort' => 3,
                'vi' => [
                    'eyebrow' => 'Vé tàu hỏa',
                    'title' => 'Di chuyển nhanh, đặt vé linh hoạt',
                    'subtitle' => 'Chọn hạng ghế hoặc giường nằm theo từng tuyến. Chủ động thời gian, dễ dàng tích hợp vào lịch trình riêng của bạn.',
                ],
                'en' => [
                    'eyebrow' => 'Train tickets',
                    'title' => 'Travel fast, book with flexibility',
                    'subtitle' => 'Choose seat or sleeper by route. Stay in control of your schedule and fit tickets into your own itinerary.',
                ],
            ],
            [
                'key' => HomeSection::KEY_SUPPORT_SERVICES,
                'sort' => 4,
                'vi' => [
                    'eyebrow' => 'Dịch vụ bổ trợ',
                    'title' => 'Chỉ chọn những gì bạn cần',
                    'subtitle' => 'Lưu trú, vui chơi và các dịch vụ hỗ trợ — linh hoạt đặt riêng theo kế hoạch, tối ưu trải nghiệm du lịch trọn vẹn hơn.',
                ],
                'en' => [
                    'eyebrow' => 'Add-on services',
                    'title' => 'Choose only what you need',
                    'subtitle' => 'Stays, activities, and support services — book à la carte around your plan for a more complete trip.',
                ],
            ],
            [
                'key' => HomeSection::KEY_DESTINATIONS,
                'sort' => 5,
                'vi' => [
                    'eyebrow' => 'Điểm đến Đông Nam Á',
                    'title' => 'Những điểm đến được yêu thích nhất',
                    'subtitle' => 'Từ Việt Nam tới Campuchia, Thái Lan, Lào và Bali — chọn nơi bạn muốn lắng nghe câu chuyện bản địa.',
                ],
                'en' => [
                    'eyebrow' => 'Southeast Asia destinations',
                    'title' => 'Our most loved destinations',
                    'subtitle' => 'From Vietnam to Cambodia, Thailand, Laos and Bali — choose where you want to hear local stories.',
                ],
            ],
            [
                'key' => HomeSection::KEY_TESTIMONIALS,
                'sort' => 6,
                'vi' => [
                    'eyebrow' => 'Khách hàng kể lại',
                    'title' => 'Trải nghiệm chân thật từ khách hàng',
                    'subtitle' => 'Hàng nghìn du khách quốc tế đã khám phá Việt Nam & Đông Nam Á cùng chúng tôi — đây là những gì họ gửi lại.',
                    'cta_label' => 'Xem tất cả cảm nhận',
                    'cta_url' => '/cam-nhan-khach-hang',
                ],
                'en' => [
                    'eyebrow' => 'Guest stories',
                    'title' => 'Real experiences from our travellers',
                    'subtitle' => 'Thousands of international guests have explored Vietnam & Southeast Asia with us — here is what they say.',
                    'cta_label' => 'Read all reviews',
                    'cta_url' => '/cam-nhan-khach-hang',
                ],
            ],
            [
                'key' => HomeSection::KEY_REVIEW_PLATFORMS,
                'sort' => 7,
                'vi' => [
                    'eyebrow' => 'Được tin tưởng',
                    'title' => ':brand được đánh giá cao trên',
                    'subtitle' => 'Xếp hạng xuất sắc từ cộng đồng du khách quốc tế trên các nền tảng uy tín.',
                ],
                'en' => [
                    'eyebrow' => 'Trusted widely',
                    'title' => ':brand is highly rated on',
                    'subtitle' => 'Excellent scores from international travellers on the platforms that matter.',
                ],
            ],
            [
                'key' => HomeSection::KEY_TEAM,
                'sort' => 8,
                'vi' => [
                    'eyebrow' => 'Con người :brand',
                    'title' => 'Đội ngũ tận tâm của chúng tôi',
                    'subtitle' => 'Chuyên gia bản địa am hiểu từng điểm đến — đồng hành từ lúc lên ý tưởng tới khi về nhà.',
                    'cta_label' => 'Gặp gỡ cả đội ngũ',
                    'cta_url' => '/doi-ngu',
                ],
                'en' => [
                    'eyebrow' => 'The :brand team',
                    'title' => 'Our dedicated local experts',
                    'subtitle' => 'People who know each destination better than anyone — with you from the first idea until you are home.',
                    'cta_label' => 'Meet the full team',
                    'cta_url' => '/doi-ngu',
                ],
            ],
            [
                'key' => HomeSection::KEY_VIDEOS,
                'sort' => 9,
                'vi' => [
                    'eyebrow' => 'Trải nghiệm thật',
                    'title' => 'Hành trình qua từng thước phim đẹp',
                    'subtitle' => 'Video chân thật do khách hàng và đội ngũ :brand ghi lại — chọn một khoảnh khắc để xem toàn màn hình.',
                    'cta_label' => 'Xem tất cả video',
                    'cta_url' => '/video-trai-nghiem',
                ],
                'en' => [
                    'eyebrow' => 'Real experiences',
                    'title' => 'Journeys in unforgettable frames',
                    'subtitle' => 'Authentic films from guests and our local team — tap any moment to watch full screen.',
                    'cta_label' => 'View all videos',
                    'cta_url' => '/video-trai-nghiem',
                ],
            ],
            [
                'key' => HomeSection::KEY_QUICK_INQUIRY,
                'sort' => 10,
                'vi' => [
                    'eyebrow' => 'Tư vấn miễn phí',
                    'title' => 'Gửi lời nhắn cho chúng tôi',
                    'body' => 'Bạn muốn khám phá Việt Nam, kết hợp Campuchia hay Thái Lan — mùa nào, ngân sách bao nhiêu? Để lại lời nhắn — chuyên gia bản địa sẽ phản hồi trong vòng <strong>24 giờ làm việc</strong>, hoàn toàn miễn phí.',
                ],
                'en' => [
                    'eyebrow' => 'Free advice',
                    'title' => 'Send us a message',
                    'body' => 'Exploring Vietnam, combining Cambodia or Thailand — which season, what budget? Leave a note — our local experts will reply within <strong>1 business day</strong>, free of charge.',
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function heroPills(): array
    {
        return [
            [
                'sort' => 0,
                'category_slug' => 'viet-nam-3-tuan',
                'vi' => ['label' => 'Việt Nam 3 tuần'],
                'en' => ['label' => 'Vietnam 3 Weeks'],
            ],
            [
                'sort' => 1,
                'country_slug' => 'tour-ket-hop',
                'vi' => ['label' => 'Tour kết hợp'],
                'en' => ['label' => 'Combined Tours'],
            ],
        ];
    }
}
