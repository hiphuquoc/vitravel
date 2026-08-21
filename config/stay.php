<?php

declare(strict_types=1);

/**
 * Cấu hình cụm lưu trú (cluster stay) — loại hình, tiện ích, nhóm hiển thị public.
 * Schema crawler: docs/16-accommodation-stays.md
 */
return [
    'property_types' => [
        'hotel' => 'Khách sạn',
        'resort' => 'Resort',
        'villa' => 'Villa / biệt thự',
        'homestay' => 'Homestay',
        'apartment' => 'Căn hộ / condotel',
        'boutique' => 'Boutique hotel',
        'hostel' => 'Hostel',
        'bungalow' => 'Bungalow',
        'glamping' => 'Glamping',
        'camping' => 'Cắm trại',
        'floating' => 'Bungalow nổi',
        'cabin' => 'Cabin / du thuyền',
    ],

    /** Privacy / unit type trên overlay hạng phòng. */
    'unit_types' => [
        'hotel_room' => 'Phòng khách sạn',
        'entire_place' => 'Nguyên căn',
        'entire_apartment' => '1 căn hộ nguyên căn',
        'entire_villa' => '1 villa nguyên căn',
        'private_room' => 'Phòng riêng',
        'shared_room' => 'Phòng tập thể',
        'bungalow' => 'Bungalow',
        'tent' => 'Lều',
        'cabin' => 'Cabin',
    ],

    /**
     * Nhãn ưu đãi (deal_key) — crawler mặc định `seasonal`.
     * Admin chọn lại trên form hạng phòng.
     */
    'deal_labels' => [
        'seasonal' => 'Ưu Đãi Mùa Du Lịch',
        'loyalty' => 'Ưu đãi khách hàng thân thiết',
        'brand_exclusive' => 'Chỉ có trên website của chúng tôi',
        'early_bird' => 'Ưu đãi đặt sớm',
        'last_minute' => 'Ưu đãi phút chót',
        'long_stay' => 'Ưu đãi lưu trú dài ngày',
        'online' => 'Ưu đãi đặt trực tuyến',
        'flash' => 'Flash sale hôm nay',
        'member' => 'Giá dành cho thành viên',
        'bundle' => 'Combo tiết kiệm',
    ],

    /** Template scarcity public — {n} random 1–5 mỗi lần tải (JS, an toàn HTML cache). */
    'scarcity_template' => 'Chúng tôi còn {n} phòng',
    'scarcity_min' => 1,
    'scarcity_max' => 5,

    /** Map tiện ích (label) → tên icon trong x-icon. */
    'amenity_icons' => [
        'hồ bơi' => 'waves',
        'pool' => 'waves',
        'spa' => 'sparkles',
        'gym' => 'bolt',
        'fitness' => 'bolt',
        'bãi biển' => 'umbrella',
        'beach' => 'umbrella',
        'wifi' => 'wifi',
        'wi-fi' => 'wifi',
        'nhà hàng' => 'utensils',
        'bếp' => 'utensils',
        'kitchen' => 'utensils',
        'restaurant' => 'utensils',
        'bar' => 'utensils',
        'kids' => 'users',
        'trẻ em' => 'users',
        'club' => 'star',
        'lounge' => 'building',
        'butler' => 'expert',
        'đưa đón' => 'car',
        'sân bay' => 'plane',
        'parking' => 'car',
        'đậu xe' => 'car',
        'kayak' => 'kayak',
        'view' => 'eye',
        'nhìn ra' => 'eye',
        'vịnh' => 'waves',
        'camping' => 'tent',
        'lều' => 'tent',
        'máy lạnh' => 'snowflake',
        'điều hoà' => 'snowflake',
        'điều hòa' => 'snowflake',
        'ban công' => 'building',
        'sân hiên' => 'building',
        'sân trong' => 'building',
        'phòng tắm' => 'sparkles',
        'm²' => 'maximize',
        'm2' => 'maximize',
        'giường' => 'bed',
        'tv' => 'photo',
        'thang máy' => 'building',
        'an toàn' => 'shield',
        'xe lăn' => 'users',
    ],

    /**
     * Nhóm tiện ích Booking-style (property + room).
     * Crawler ghi attrs.amenity_groups[key] = string[]; không có thì auto-match từ amenities[].
     */
    'amenity_groups' => [
        'popular' => ['label' => 'Tiện ích nổi bật', 'match' => []],
        'bathroom' => ['label' => 'Phòng tắm', 'match' => ['phòng tắm', 'nhà vệ sinh', 'bồn tắm', 'vòi sen', 'máy sấy', 'khăn tắm', 'bidet', 'dép lê', 'áo choàng', 'toiletries', 'giấy vệ sinh']],
        'bedroom' => ['label' => 'Phòng ngủ', 'match' => ['giường', 'ga trải', 'tủ quần', 'tủ hoặc phòng', 'báo thức', 'ga gối', 'linens', 'wardrobe']],
        'view' => ['label' => 'Hướng tầm nhìn', 'match' => ['nhìn ra', 'view', 'hướng biển', 'hướng vịnh', 'hướng núi', 'hướng thành phố']],
        'kitchen' => ['label' => 'Nhà bếp', 'match' => ['bếp', 'kitchen', 'tủ lạnh', 'ấm đun', 'máy giặt', 'mặt bếp', 'lò', 'bát đĩa', 'sản phẩm làm sạch']],
        'living' => ['label' => 'Khu sinh hoạt', 'match' => ['sofa', 'ghế ngồi', 'bàn làm việc', 'phòng khách', 'seating', 'desk']],
        'media' => ['label' => 'Truyền thông & công nghệ', 'match' => ['tv', 'netflix', 'streaming', 'cáp', 'wifi', 'wi-fi']],
        'outdoor' => ['label' => 'Ngoài trời', 'match' => ['ban công', 'sân hiên', 'hồ bơi riêng', 'bãi biển', 'ăn uống ngoài trời', 'terrace', 'balcony']],
        'wellness' => ['label' => 'Spa & thể thao', 'match' => ['spa', 'gym', 'fitness', 'yoga', 'massage', 'bồn tắm nóng']],
        'pool_beach' => ['label' => 'Hồ bơi & biển', 'match' => ['hồ bơi', 'pool', 'bãi biển', 'beach', 'private beach']],
        'dining' => ['label' => 'Ẩm thực', 'match' => ['nhà hàng', 'restaurant', 'bar', 'buffet', 'room service']],
        'family' => ['label' => 'Gia đình & trẻ em', 'match' => ['kids', 'trẻ em', 'club', 'playground', 'babysit', 'nắp đậy ổ cắm']],
        'accessibility' => ['label' => 'Tiếp cận', 'match' => ['xe lăn', 'thang máy', 'wheelchair', 'accessible']],
        'safety' => ['label' => 'An toàn', 'match' => ['báo cháy', 'bình chữa', 'key card', 'cách âm', 'smoke', 'fire', 'an toàn']],
        'parking' => ['label' => 'Đậu xe & đưa đón', 'match' => ['parking', 'đậu xe', 'đưa đón', 'sân bay', 'shuttle']],
        'general' => ['label' => 'Tiện nghi chỗ nghỉ', 'match' => ['điều hoà', 'điều hòa', 'máy lạnh', 'lối vào riêng', 'ủi', 'sàn', 'wifi', 'lễ tân', '24']],
        'business' => ['label' => 'Công việc', 'match' => ['meeting', 'hội nghị', 'business', 'conference']],
        'other' => ['label' => 'Khác', 'match' => []],
    ],

    'nearby_groups' => [
        'landmark' => 'Địa danh',
        'beach' => 'Bãi biển / thiên nhiên',
        'nature' => 'Thiên nhiên',
        'transport' => 'Giao thông',
        'dining' => 'Ăn uống',
        'shop' => 'Mua sắm',
        'other' => 'Lân cận',
    ],

    'review_score_tags' => [
        'staff' => 'Nhân viên',
        'facilities' => 'Cơ sở vật chất',
        'cleanliness' => 'Sạch sẽ',
        'comfort' => 'Thoải mái',
        'value' => 'Đáng giá tiền',
        'location' => 'Vị trí',
        'wifi' => 'WiFi miễn phí',
    ],

    /** @deprecated Dùng review_score_tags — giữ alias cho code cũ. */
    'review_score_labels' => [
        'staff' => 'Nhân viên',
        'facilities' => 'Cơ sở vật chất',
        'cleanliness' => 'Sạch sẽ',
        'comfort' => 'Thoải mái',
        'value' => 'Đáng giá tiền',
        'location' => 'Vị trí',
        'wifi' => 'WiFi miễn phí',
    ],

    'policy_labels' => [
        'check_in' => 'Nhận phòng',
        'check_out' => 'Trả phòng',
        'cancellation' => 'Huỷ / đổi ngày',
        'child' => 'Trẻ em',
        'extra_bed' => 'Giường phụ / cũi',
        'age_restriction' => 'Độ tuổi tối thiểu',
        'pet' => 'Thú cưng',
        'smoking' => 'Hút thuốc',
        'payment' => 'Thanh toán',
        'payment_cards' => 'Thẻ được nhận',
        'id_required' => 'Giấy tờ check-in',
    ],

    /**
     * Crawler Booking.com — lưu URL nguồn, lọc khung HTML thủ công, draft service stay.
     * Docs: docs/16-accommodation-stays.md
     */
    'crawl' => [
        'user_agent' => env('STAY_CRAWL_UA', 'ViTravelStayBot/1.0 (+https://vitravel.dev/bot; research import)'),
        'timeout' => (int) env('STAY_CRAWL_TIMEOUT', 35),
        /** Nghỉ giữa các request HTTP fallback / lịch sự với Booking (ms). */
        'delay_ms' => (int) env('STAY_CRAWL_DELAY_MS', 450),
        'max_html_bytes' => (int) env('STAY_CRAWL_MAX_HTML', 6_000_000),
        'max_extract_chars' => (int) env('STAY_CRAWL_MAX_EXTRACT', 90_000),
        'max_images' => 120,
        'max_rooms' => 16,
        'extractor_version' => 2,
        'hosts' => ['booking.com', 'www.booking.com'],
        'accept_language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
        'browser_user_agent' => env(
            'STAY_CRAWL_BROWSER_UA',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
        ),
        /** browser = Chrome/Puppeteer (mặc định). http = Guzzle. auto = browser rồi fallback HTTP. */
        'driver' => env('STAY_CRAWL_DRIVER', 'browser'),
        'browser_timeout' => (int) env('STAY_CRAWL_BROWSER_TIMEOUT', 240),
        'node_bin' => env('STAY_CRAWL_NODE', ''),
        /** false = mở cửa sổ Chrome trên màn hình (xem thao tác). VPS để true. */
        'headless' => (static function () {
            $raw = env('STAY_CRAWL_HEADLESS', true);
            // phpdotenv ép `false` thành bool; (string) false === '' nên không được so sánh chuỗi.
            if (is_bool($raw)) {
                return $raw;
            }

            return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
        })(),
        'slow_mo' => (int) env('STAY_CRAWL_SLOW_MO', 0),
        /** Listing category: số trang offset tối đa (mỗi trang ~list_page_size URL). */
        'list_max_pages' => (int) env('STAY_CRAWL_LIST_MAX_PAGES', 80),
        'list_page_size' => (int) env('STAY_CRAWL_LIST_PAGE_SIZE', 25),
        /** Cộng thêm giây Process timeout khi crawl listing (scroll + «Tải thêm kết quả»). */
        'list_browser_extra_sec' => (int) env('STAY_CRAWL_LIST_BROWSER_EXTRA_SEC', 240),
        /** Worker stay-crawl:work — nghỉ giữa các bước processNext (ms). */
        'worker_sleep_ms' => (int) env('STAY_CRAWL_WORKER_SLEEP_MS', 400),
        /** Heartbeat worker quá hạn → coi chết (phải > thời gian 1 bước Chrome gallery/phòng). */
        'worker_heartbeat_stale_sec' => (int) env('STAY_CRAWL_WORKER_STALE_SEC', 900),
        'proxy' => [
            'enabled' => (bool) env('STAY_CRAWL_PROXY', false),
            'host' => env('STAY_CRAWL_PROXY_HOST', env('PROXY_HOST', '')),
            'port' => env('STAY_CRAWL_PROXY_PORT', env('PROXY_PORT', '')),
            'username' => env('STAY_CRAWL_PROXY_USER', env('PROXY_USERNAME', '')),
            'password' => env('STAY_CRAWL_PROXY_PASS', env('PROXY_PASSWORD', '')),
        ],
    ],
];
