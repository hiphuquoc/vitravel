<?php

/**
 * ============================================================================
 * DỮ LIỆU Hi Sa Pa — profile `hisapa` (project:seed / migrate --seed)
 * ============================================================================
 *
 * Trang thông tin du lịch + dịch vụ + kết nối TOÀN DIỆN Sa Pa (Lào Cai).
 * Một điểm đến duy nhất — "zones" thay "countries". Cụm "train" = tàu đêm
 * HN—Lào Cai (SP/CH), limousine & xe ~320km. Cụm "flight" = bay Nội Bài (HAN)
 * + xe nối (không có sân bay tại Sa Pa). Cáp treo = Fansipan Sun World — không
 * phải du thuyền biển.
 *
 * Lưu trú: không seed catalogue trong file này (data từ tool cào riêng); giữ menu + stays_hub.
 *
 * Schema: project/README.md | Loader: App\Support\ProjectSeed::useProfile('hisapa')
 *
 * @return array<string, mixed>
 */

$__hisapaSeed = array(
    'meta' => array(
        'schema' => 1,
        'brand' => 'Hi Sa Pa',
        'tagline' => 'Ruộng bậc thang, Fansipan & thoát urban cuối tuần từ Hà Nội',
        'admin' => array(
            'email' => 'admin@hisapa.dev',
            'name' => 'Admin Hi Sa Pa',
            'password' => '111111',
        ),
        'primary_domain' => 'hisapa.dev',
        'domains' => array('hisapa.dev', 'www.hisapa.dev', 'hisapa.vn', 'www.hisapa.vn'),
        'exported_at' => '2026-09-02T00:00:00+00:00',
    ),

    'price_guest_types' => array(
        array('code' => 'adult', 'sort' => 10, 'age_min' => 12, 'age_max' => 59, 'name' => array('vi' => 'Người lớn',



'en' => 'Adult')),
        array('code' => 'child', 'sort' => 20, 'age_min' => 2, 'age_max' => 11, 'name' => array('vi' => 'Trẻ em',



'en' => 'Child')),
        array('code' => 'senior', 'sort' => 30, 'age_min' => 60, 'age_max' => null, 'name' => array('vi' => 'Cao tuổi (60+)',



'en' => 'Senior (60+)')),
    ),

    'price_table_defaults' => array(
        'unit' => 'per_person',
        'notes' => 'Giá tham khảo theo người. Trẻ em và cao tuổi giảm theo bảng. Liên hệ để chốt báo giá chính xác.',
        'guest_multipliers' => array('adult' => 1, 'child' => 0.7, 'senior' => 0.85),
        'cluster_units' => array('stay' => 'per_room'),
        'periods' => array(
            array('kind' => 'year', 'label' => 'Giá năm {year}', 'is_promo' => false, 'priority' => 0),
            array('kind' => 'range', 'label' => 'Mùa sương lạnh — cuối năm {year}', 'starts_on' => '{year}-11-01', 'ends_on' => '{year}-12-31', 'is_promo' => true, 'priority' => 10, 'amount_multiplier' => 1.1),
            array('kind' => 'range', 'label' => 'Mùa sương lạnh — đầu năm {year}', 'starts_on' => '{year}-01-01', 'ends_on' => '{year}-02-28', 'is_promo' => true, 'priority' => 10, 'amount_multiplier' => 1.1),
            array('kind' => 'range', 'label' => 'Mùa vàng bậc thang {year} (Sep–Oct)', 'starts_on' => '{year}-09-01', 'ends_on' => '{year}-10-31', 'is_promo' => true, 'priority' => 9, 'amount_multiplier' => 1.12),
            array('kind' => 'range', 'label' => 'Mùa xanh bậc thang {year} (May–Aug)', 'starts_on' => '{year}-05-01', 'ends_on' => '{year}-08-31', 'is_promo' => true, 'priority' => 8, 'amount_multiplier' => 1.05),
        ),
    ),

    'content_tag_map' => array(
        'Ăn gì, uống gì?' => 'where-to-eat',
        'Ở đâu?' => 'where-to-stay',
        'Chơi gì, xem gì?' => 'what-to-do',
        'Di chuyển thế nào?' => 'how-to-get-there',
        'Mẹo du lịch' => 'travel-tips',
        'Chuyến đi thế nào?' => 'trip-report',
        'Chọn tour nào?' => 'which-tour',
    ),

    'content_tags' => array(
        'where-to-eat' => array('vi' => 'Ăn uống ở đâu?',



'en' => 'Where to eat & drink?'),
        'where-to-stay' => array('vi' => 'Ở đâu?',



'en' => 'Where to stay?'),
        'what-to-do' => array('vi' => 'Làm gì & xem gì?',



'en' => 'What to do & see?'),
        'how-to-get-there' => array('vi' => 'Di chuyển tới Sa Pa thế nào?',



'en' => 'How to get to Sapa?'),
        'travel-tips' => array('vi' => 'Mẹo du lịch',



'en' => 'Travel tips'),
        'trip-report' => array('vi' => 'Cảm nhận chuyến đi',



'en' => 'How was the trip?'),
        'which-tour' => array('vi' => 'Chọn tour nào?',



'en' => 'Which tour to choose?'),
    ),

    'travel_styles' => array(
        'day-trip' => array(
            'vi' => 'Tour trong ngày',




'en' => 'Day trip',
        ),
        '2n1d' => array(
            'vi' => '2 ngày 1 đêm',




'en' => '2 days 1 night',
        ),
        '3n2d' => array(
            'vi' => '3 ngày 2 đêm',




'en' => '3 days 2 nights',
        ),
        '4n3d' => array(
            'vi' => '4 ngày 3 đêm',




'en' => '4 days 3 nights',
        ),
        '5-plus-days' => array(
            'vi' => 'Từ 5 ngày',




'en' => '5+ days',
        ),
        'cuoi-tuan-ha-noi' => array(
            'vi' => 'Cuối tuần từ Hà Nội',




'en' => 'Weekend from Hanoi',
        ),
        'trekking-homestay' => array(
            'vi' => 'Trekking & homestay',




'en' => 'Trekking & homestay',
        ),
        'fansipan' => array(
            'vi' => 'Tour Fansipan & cáp treo',




'en' => 'Fansipan & cable car tours',
        ),
        'am-thuc' => array(
            'vi' => 'Tour ẩm thực',




'en' => 'Food tours',
        ),
        'cap-doi' => array(
            'vi' => 'Tour cặp đôi & lãng mạn',




'en' => 'Couple & romantic',
        ),
        'gia-dinh' => array(
            'vi' => 'Tour gia đình',




'en' => 'Family tours',
        ),
    ),

    'review_platforms' => array(
        array('code' => 'tripadvisor', 'name' => 'Tripadvisor', 'rating' => 4.9, 'review_count' => 412, 'sort' => 0,
            'quote' => 'Khách quốc tế khen trek Muong Hoa homestay và cáp treo Fansipan — đúng vibe núi Tây Bắc gần Hà Nội.',
            'link_label' => 'Đọc đánh giá trên Tripadvisor', 'url' => 'https://www.tripadvisor.com'),
        array('code' => 'google', 'name' => 'Google', 'rating' => 4.8, 'review_count' => 586, 'sort' => 1,
            'quote' => '4.8/5 trên Google Maps — khách Hà Nội khen tàu đêm SP/CH và tư vấn homestay bản làng.',
            'link_label' => 'Xem đánh giá trên Google', 'url' => 'https://www.google.com/maps'),
        array('code' => 'trustpilot', 'name' => 'Trustpilot', 'rating' => 4.7, 'review_count' => 94, 'sort' => 2,
            'quote' => 'Điểm "Xuất sắc" — đặc biệt combo 2 ngày 1 đêm tàu đêm từ Hà Nội và hỗ trợ đổi lịch khi sương dày.',
            'link_label' => 'Đọc đánh giá trên Trustpilot', 'url' => 'https://www.trustpilot.com'),
    ),

    'cruise_types' => array(
        array('slug' => 'cap-treo-fansipan', 'name' => 'Cáp treo Fansipan', 'count' => 2, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 10),
        array('slug' => 'tau-mui-may-fansipan', 'name' => 'Tàu Mười Mây Fansipan', 'count' => 1, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 20),
    ),

    'home_slides' => array(
        array(
            'sort' => 0, 'text_align' => 'center', 'link_url' => '/tours',
            'vi' => array('title' => 'Sa Pa', 'title_accent' => 'ruộng bậc thang, Fansipan & văn hoá Tây Bắc',
                'description' => 'Thị trấn mây ~1600m, cách Hà Nội ~320km — tàu đêm SP/CH, cáp treo Fansipan, thung lũng Mường Hoa và ẩm thực thắng cố, cá hồi, rượu ngô.',
                'button_label' => 'Khám phá Sa Pa', 'image_alt' => 'Sa Pa — ruộng bậc thang và Fansipan Lào Cai'),




'en' => array('title' => 'Sapa', 'title_accent' => 'terraced fields, Fansipan & Northwest culture',
                'description' => 'Misty town ~1600m, ~320km from Hanoi — overnight SP/CH trains, Fansipan cable car, Muong Hoa valley and thang co, salmon hotpot, corn wine cuisine.',
                'button_label' => 'Discover Sapa', 'image_alt' => 'Sapa terraced fields and Fansipan Lao Cai'),
        ),
        array(
            'sort' => 1, 'text_align' => 'center', 'link_url' => '/diem-den/fansipan-sun-world',
            'vi' => array('title' => 'Fansipan Sun World', 'title_accent' => 'cáp treo & đỉnh nóc Đông Dương',
                'description' => 'Chinh phục 3143m bằng cáp treo ba đoạn và tàu Mười Mây — view Hoàng Liên Sơn trên mây.',
                'button_label' => 'Xem tour Fansipan', 'image_alt' => 'Cáp treo Fansipan Sun World'),




'en' => array('title' => 'Fansipan Sun World', 'title_accent' => 'cable car & Indochina rooftop',
                'description' => 'Reach 3143m via three cable-car sections and the Muoi May mountain train — Hoang Lien Son views above the clouds.',
                'button_label' => 'View Fansipan tours', 'image_alt' => 'Fansipan Sun World cable car'),
        ),
        array(
            'sort' => 2, 'text_align' => 'center', 'link_url' => '/diem-den/ket-hop-ha-noi',
            'vi' => array('title' => 'Cuối tuần từ Hà Nội', 'title_accent' => 'tàu đêm SP/CH & limo 5–6h',
                'description' => 'Thứ 6 tối lên tàu, sáng Thứ 7 Sa Pa — combo tàu + Fansipan + thị trấn phổ biến nhất với dân văn phòng Hà Nội.',
                'button_label' => 'Xem tour cuối tuần', 'image_alt' => 'Cuối tuần escape Hanoi to Sapa by train'),




'en' => array('title' => 'Cuối tuần from Hanoi', 'title_accent' => 'overnight SP/CH train & 5–6h limo',
                'description' => 'Friday night train, Saturday morning in Sapa — train + Fansipan + town is the classic Hanoi office-worker break.',
                'button_label' => 'View cuối tuần tours', 'image_alt' => 'Cuối tuần escape Hanoi to Sapa by train'),
        ),
    ),

    'zones' => array(
        array('slug' => 'thi-tran-sapa', 'name' => 'Thị trấn Sa Pa', 'size' => 'large', 'tourCount' => 6, 'tagline' => 'Nhà thờ đá, chợ đêm, Ham Rong biển mây & phố núi'),
        array('slug' => 'fansipan-sun-world', 'name' => 'Fansipan Sun World', 'size' => 'large', 'tourCount' => 4, 'tagline' => 'Cáp treo, tàu Mười Mây & đỉnh 3143m'),
        array('slug' => 'thung-lung-muong-hoa', 'name' => 'Thung lũng Mường Hoa', 'size' => 'large', 'tourCount' => 5, 'tagline' => 'Ruộng bậc thang UNESCO & tàu hoả leo núi'),
        array('slug' => 'ban-cat-cat-y-linh-ho', 'name' => 'Bản Cát Cát & Y Linh Hồ', 'size' => 'normal', 'tourCount' => 3, 'tagline' => 'Làng H\'Mông, thác Cát Cát & view thung lũng'),
        array('slug' => 'thac-bac-thac-tinh-yeu', 'name' => 'Thác Bạc & Thác Tình Yêu', 'size' => 'normal', 'tourCount' => 3, 'tagline' => 'Thác nước giữa rừng thông — điểm chụp sương'),
        array('slug' => 'homestay-trek-ban-ho', 'name' => 'Homestay trek Bản Hồ', 'size' => 'normal', 'tourCount' => 4, 'tagline' => 'Trek Lao Chai—Ta Van, ngủ nhà dân Tày & Giáy'),
        array('slug' => 'lao-cai-cua-ngo', 'name' => 'Lào Cai cửa ngõ', 'size' => 'large', 'tourCount' => 3, 'tagline' => 'Ga tàu, shuttle 38km lên Sa Pa & biên giới'),
        array('slug' => 'ket-hop-bac-ha', 'name' => 'Kết hợp Bắc Hà', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Chợ phiên Chủ nhật & bản làng Flower H\'Mong'),
        array('slug' => 'ket-hop-ha-noi', 'name' => 'Cửa ngõ Hà Nội', 'size' => 'large', 'tourCount' => 4, 'tagline' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — tàu/limo ~320km'),
        array('slug' => 'ket-hop-mu-cang-chai', 'name' => 'Kết hợp Mù Cang Chải', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Sa Pa + Mù Cang Chải — bậc thang vàng Tây Bắc'),
    ),

    'zone_translations' => array(
        'thi-tran-sapa' => array('vi' => 'Thị trấn Sa Pa',



'en' => 'Sapa town',
            'tagline' => array('vi' => 'Nhà thờ đá, chợ đêm, Ham Rong biển mây & phố núi',



'en' => 'Stone Church, night market, Ham Rong cloud sea & mountain town')),
        'fansipan-sun-world' => array('vi' => 'Fansipan Sun World',



'en' => 'Fansipan Sun World',
            'tagline' => array('vi' => 'Cáp treo, tàu Mười Mây & đỉnh 3143m',



'en' => 'Cable car, Muoi May train & 3143m summit')),
        'thung-lung-muong-hoa' => array('vi' => 'Thung lũng Mường Hoa',



'en' => 'Muong Hoa valley',
            'tagline' => array('vi' => 'Ruộng bậc thang UNESCO & tàu hoả leo núi',



'en' => 'UNESCO terraces & mountain railway')),
        'ban-cat-cat-y-linh-ho' => array('vi' => 'Bản Cát Cát & Y Linh Hồ',



'en' => 'Cat Cat & Y Linh Ho villages',
            'tagline' => array('vi' => 'Làng H\'Mông, thác Cát Cát & view thung lũng',



'en' => 'H\'Mong villages, Cat Cat falls & valley views')),
        'thac-bac-thac-tinh-yeu' => array('vi' => 'Thác Bạc & Thác Tình Yêu',



'en' => 'Silver & Love Falls',
            'tagline' => array('vi' => 'Thác nước giữa rừng thông — điểm chụp sương',



'en' => 'Waterfalls in pine forest — iconic mist photo spots')),
        'homestay-trek-ban-ho' => array('vi' => 'Homestay trek Bản Hồ',



'en' => 'Ban Ho homestay trek',
            'tagline' => array('vi' => 'Trek Lao Chai—Ta Van, ngủ nhà dân Tày & Giáy',



'en' => 'Lao Chai—Ta Van trek, Tay & Giay family stays')),
        'lao-cai-cua-ngo' => array('vi' => 'Lào Cai cửa ngõ',



'en' => 'Lao Cai gateway',
            'tagline' => array('vi' => 'Ga tàu, shuttle 38km lên Sa Pa & biên giới',



'en' => 'Train station, 38km shuttle to Sapa & border gateway')),
        'ket-hop-bac-ha' => array('vi' => 'Kết hợp Bắc Hà',



'en' => 'Combined with Bac Ha',
            'tagline' => array('vi' => 'Chợ phiên Chủ nhật & bản làng Flower H\'Mong',



'en' => 'Sunday market & Flower H\'Mong villages')),
        'ket-hop-ha-noi' => array('vi' => 'Cửa ngõ Hà Nội',



'en' => 'Hanoi gateway',
            'tagline' => array('vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — tàu/limo ~320km',



'en' => 'Day trip & 2N1D weekend from Hanoi — train/limo ~320km')),
        'ket-hop-mu-cang-chai' => array('vi' => 'Kết hợp Mù Cang Chải',



'en' => 'Combined with Mu Cang Chai',
            'tagline' => array('vi' => 'Sa Pa + Mù Cang Chải — bậc thang vàng Tây Bắc',



'en' => 'Sapa + Mu Cang Chai — golden Northwest terraces')),
    ),

    'tours' => array(
        array(
            'slug' => 'sapa-2n1d-cuoi-tuan-ha-noi',
            'title' => 'Sa Pa 2 ngày 1 đêm — Cuối tuần từ Hà Nội',
            'zoneSlug' => 'ket-hop-ha-noi',
            'zone' => 'Cửa ngõ Hà Nội',
            'tourCode' => 'SP2D-01',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 428,
            'badge' => 'Bán chạy nhất',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'cuoi-tuan-ha-noi',
                'fansipan',
            ),
            'quote' => array(
                'text' => 'Tối thứ 6 lên tàu SP, sáng thứ 7 Fansipan — đúng kiểu thoát Hà Nội cuối tuần.',
                'author' => 'Chị Minh Anh',
            ),
            'places' => array(
                'Tàu đêm HN',
                'Fansipan',
                'Nhà thờ đá',
                'Chợ đêm Sa Pa',
            ),
            'start' => 'Hà Nội',
            'end' => 'Hà Nội',
            'highlightsIntro' => 'Combo cửa ngõ Hà Nội phổ biến nhất: tàu đêm khứ hồi, cáp treo Fansipan, nhà thờ đá và tối ẩm thực cá hồi.',
            'highlights' => array(
                'Tàu đêm SP/CH khứ hồi',
                'Shuttle Lào Cai—Sa Pa',
                'Cáp treo Fansipan',
                'Nhà thờ đá & phố đi bộ',
                'Gợi ý homestay/khách sạn',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Tàu đêm HN — Lào Cai — Sa Pa — Fansipan',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'train',
                        'bus',
                        'cable_car',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => '20:00–21:00 lên tàu SP/CH. Sáng đến Lào Cai, shuttle 38km lên Sa Pa. Trưa check-in, chiều cáp treo Fansipan, tối lẩu cá hồi hoặc thắng cố.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Ham Rong — Tiễn tàu về HN',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                        'train',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Ham Rong biển mây (tuỳ thời tiết), nhà thờ đá. Trưa ăn, chiều xuống Lào Cai, tàu về Hà Nội sáng hôm sau.',
                ),
            ),
            'inclusions' => array(
                'Tàu đêm khứ hồi HN—Lào Cai',
                'Shuttle Lào Cai—Sa Pa',
                'Vé cáp treo Fansipan',
                'HDV địa phương',
            ),
            'exclusions' => array(
                'Lưu trú (đặt thêm)',
                'Ăn trưa/tối',
                'Vé Ham Rong',
            ),
            'notes' => array(
                'Có thể gộp đặt homestay qua :brand theo ngân sách.',
                'Mùa đông 0–8°C — mang áo ấm.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Tour có bao gồm khách sạn không?',
                    'a' => 'Giá tour chưa gồm phòng. :brand hỗ trợ gợi ý và đặt homestay/khách sạn — ghép vào cùng chuyến.',
                ),
                array(
                    'q' => 'Tàu SP hay CH?',
                    'a' => 'SP nhanh hơn (~8h), CH rẻ hơn (~9h) — tư vấn theo ngân sách và giờ đón.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 2450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'sapa-3n2d-tong-quan',
            'title' => 'Sa Pa 3 ngày 2 đêm — Tổng quan thị trấn mây',
            'zoneSlug' => 'thi-tran-sapa',
            'zone' => 'Thị trấn Sa Pa',
            'tourCode' => 'SP3D-01',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 4.9,
            'reviewCount' => 256,
            'badge' => 'Tổng quan',
            'featured' => true,
            'styles' => array(
                '3n2d',
                'fansipan',
                'gia-dinh',
            ),
            'quote' => array(
                'text' => 'Ba ngày đủ để cảm nhận Sa Pa — từ Fansipan, Mường Hoa đến Cát Cát và ẩm thực Tây Bắc.',
                'author' => 'Anh Quốc Bảo',
            ),
            'places' => array(
                'Fansipan',
                'Mường Hoa',
                'Cát Cát',
                'Ham Rong',
                'Nhà thờ đá',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Lịch trình cân bằng cho lần đầu: Fansipan, thung lũng, làng H\'Mông và half-day thị trấn.',
            'highlights' => array(
                'Cáp treo Fansipan',
                'Tàu hoả Mường Hoa',
                'Bản Cát Cát',
                'Ham Rong biển mây',
                'Tour ẩm thực đặc sản',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đón — Fansipan',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'cable_car',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Đón Lào Cai/HN, lên Sa Pa. Chiều cáp treo Fansipan, tối BBQ hoặc lẩu cá hồi.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Mường Hoa — Cát Cát',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'car',
                        'train',
                        'trekking',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Sáng tàu hoả Mường Hoa, Cát Cát trek nhẹ. Chiều tự do chợ, tối rượu ngô & thắng cố.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Ham Rong — Tiễn',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Ham Rong, nhà thờ đá. Trưa tiễn khách xuống Lào Cai/HN.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng 3 ngày',
                'Vé tham quan theo lịch',
                'HDV địa phương',
                'Bữa ăn ghi trong chương trình',
            ),
            'exclusions' => array(
                'Tàu đêm HN (đặt thêm)',
                'Lưu trú',
                'Đồ uống',
                'Tip',
            ),
            'notes' => array(
                'Mang áo khoát — tối 8–12°C mùa đông.',
            ),
            'faqs' => array(),
            'galleryCount' => 6,
            'priceFrom' => 3850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'sapa-2n1d-lang-man',
            'title' => 'Sa Pa 2 ngày 1 đêm — Lãng mạn & biển mây',
            'zoneSlug' => 'thi-tran-sapa',
            'zone' => 'Thị trấn Sa Pa',
            'tourCode' => 'SP2D-02',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 167,
            'badge' => 'Cặp đôi',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'cuoi-tuan-ha-noi',
                'cap-doi',
            ),
            'quote' => array(
                'text' => 'Sáng dậy trên Ham Rong trong sương, chiều Mường Hoa — mini trăng mật không cần bay xa.',
                'author' => 'Vợ chồng Tuấn — Hà',
            ),
            'places' => array(
                'Ham Rong',
                'Mường Hoa',
                'Resort view núi',
                'Nhà thờ đá',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Dành cho cặp đôi: resort view ruộng, photo walk Ham Rong, tối lẩu cá hồi lãng mạn.',
            'highlights' => array(
                'Resort/view ruộng bậc thang',
                'Ham Rong biển mây sớm',
                'Tàu hoả Mường Hoa',
                'Bữa tối couple gợi ý',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ham Rong & Mường Hoa',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'train',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Check-in resort, chiều tàu Mường Hoa, tối fine-dining hoặc lẩu cá hồi view núi.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Ham Rong sương sớm — Tiễn',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => '05:30–08:00 Ham Rong chụp sương couple, brunch, tiễn khách.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng 2 ngày',
                'Vé Ham Rong & Mường Hoa',
                'HDV chụp ảnh cơ bản',
            ),
            'exclusions' => array(
                'Lưu trú',
                'Bữa tối fine-dining',
            ),
            'notes' => array(
                'Có thể thêm setup hoa surprise qua mục Dịch vụ.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 2150000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'city-tour-sapa-1-ngay',
            'title' => 'Tour thành phố Sa Pa 1 ngày — Nhà thờ đá & chợ đêm',
            'zoneSlug' => 'thi-tran-sapa',
            'zone' => 'Thị trấn Sa Pa',
            'tourCode' => 'SP1D-01',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 312,
            'badge' => 'Phổ biến',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Một ngày gom nhà thờ đá, chợ và quán ven núi — không cần tự trek.',
                'author' => 'Gia đình chị Thảo',
            ),
            'places' => array(
                'Nhà thờ đá',
                'Chợ Sa Pa',
                'Phố đi bộ',
                'Quảng trường',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Tour ghép hoặc riêng — nhịp sống thị trấn mây và văn hoá Tây Bắc trong một ngày.',
            'highlights' => array(
                'Nhà thờ đá Sa Pa',
                'Chợ & quán ven núi',
                'Gợi ý rượu ngô',
                'Chợ đêm tối (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Tour thành phố trọn ngày',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '08:30–17:00 tham quan nhà thờ, phố núi, ăn trưa thắng cố, chiều tự do chợ & cafe view ruộng.',
                ),
            ),
            'inclusions' => array(
                'Xe 16 chỗ hoặc riêng',
                'HDV',
                'Bữa trưa',
            ),
            'exclusions' => array(
                'Vé Ham Rong',
                'Đồ uống',
            ),
            'notes' => array(
                'Ghép nhóm max 12 khách.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'fansipan-cap-treo-1-ngay',
            'title' => 'Fansipan cáp treo 1 ngày — Đỉnh nóc Đông Dương',
            'zoneSlug' => 'fansipan-sun-world',
            'zone' => 'Fansipan Sun World',
            'tourCode' => 'SP1D-02',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 534,
            'badge' => 'Signature',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'fansipan',
            ),
            'quote' => array(
                'text' => 'Cáp treo ba đoạn lên 3143m — view Hoàng Liên Sơn đáng từng đồng.',
                'author' => 'Anh Felix',
            ),
            'places' => array(
                'Cáp treo Fansipan',
                'Tàu Mười Mây',
                'Đỉnh Fansipan',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Hoạt động đặc trưng Sa Pa — cáp treo Sun World, tàu leo núi và chinh phục đỉnh.',
            'highlights' => array(
                'Vé cáp treo 3 đoạn',
                'Tàu Mười Mây',
                'Chùa trên đỉnh',
                'HDV & xe đón',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Fansipan full day',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'cable_car',
                        'train',
                    ),
                    'overnight' => null,
                    'content' => '07:30 đón, cáp treo lên đỉnh, tàu Mười Mây, trưa trên núi, về Sa Pa ~16:00.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Vé cáp treo combo',
                'HDV',
            ),
            'exclusions' => array(
                'Ăn tối',
                'Tip',
            ),
            'notes' => array(
                'Sương dày view phụ thuộc thời tiết; mang áo ấm trên đỉnh.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 980000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'muong-hoa-trek-homestay-2n1d',
            'title' => 'Mường Hoa trek homestay 2 ngày 1 đêm',
            'zoneSlug' => 'homestay-trek-ban-ho',
            'zone' => 'Homestay trek Bản Hồ',
            'tourCode' => 'SP2D-03',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 198,
            'badge' => 'Trekking',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'trekking-homestay',
            ),
            'quote' => array(
                'text' => 'Ngủ nhà dân Tày ở Ta Van, sáng thức giữa ruộng bậc thang — trải nghiệm đích thực.',
                'author' => 'Sarah Kim',
            ),
            'places' => array(
                'Lao Chai',
                'Ta Van',
                'Mường Hoa',
                'Bản Hồ',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Trek 2 ngày qua Lao Chai—Ta Van, homestay dân tộc và ruộng UNESCO.',
            'highlights' => array(
                'HLV trekking địa phương',
                'Homestay Tày/Giáy',
                'Ruộng bậc thang Mường Hoa',
                'Bữa tối gia đình',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Trek Lao Chai — Ta Van — Homestay',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'trekking',
                    ),
                    'overnight' => 'Homestay Ta Van',
                    'content' => 'Sáng trek từ Sa Pa xuống Lao Chai, Ta Van. Chiều homestay, tối ăn cùng gia đình, kể chuyện văn hoá.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Ta Van — Bản Hồ — Về Sa Pa',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'trekking',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng trek tiếp Bản Hồ, trưa về Sa Pa, tiễn khách.',
                ),
            ),
            'inclusions' => array(
                'HLV',
                'Homestay 1 đêm',
                'Bữa theo chương trình',
                'Porter nhẹ',
            ),
            'exclusions' => array(
                'Giày trek',
                'Tip homestay',
            ),
            'notes' => array(
                'Mang áo mưa; tôn trọng phong tục bản làng.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Homestay có điện nước không?',
                    'a' => 'Cơ bản có — tiêu chuẩn đơn giản, ấm cúng. :brand chọn nhà đã kiểm tra.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 1950000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cat-cat-y-linh-ho-nua-ngay',
            'title' => 'Cát Cát & Y Linh Hồ — Trek nửa ngày',
            'zoneSlug' => 'ban-cat-cat-y-linh-ho',
            'zone' => 'Bản Cát Cát & Y Linh Hồ',
            'tourCode' => 'SP0.5D-01',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 389,
            'badge' => 'Check-in',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Thác Cát Cát và làng H\'Mông — đủ cho buổi sáng trước khi lên Fansipan.',
                'author' => 'Chị Thùy Linh',
            ),
            'places' => array(
                'Bản Cát Cát',
                'Thác Cát Cát',
                'Y Linh Hồ',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => '4 giờ — làng H\'Mông, thác nước, view thung lũng — ghép tour dài hoặc đặt lẻ.',
            'highlights' => array(
                'Vé Cát Cát',
                'Trek nhẹ',
                'HDV góc chụp',
                'Xe đón khách sạn',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cat Cat circuit',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => '08:00 hoặc 13:30 đón, 2–3h tham quan làng & thác, về khách sạn.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Vé Cát Cát',
                'HDV',
            ),
            'exclusions' => array(
                'Ăn uống',
            ),
            'notes' => array(
                'Đường đá — mang giày chống trượt.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 480000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'thac-bac-thac-tinh-yeu-1-ngay',
            'title' => 'Thác Bạc & Thác Tình Yêu — Trekking 1 ngày',
            'zoneSlug' => 'thac-bac-thac-tinh-yeu',
            'zone' => 'Thác Bạc & Thác Tình Yêu',
            'tourCode' => 'SP1D-03',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 234,
            'badge' => 'Thiên nhiên',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Hai thác trong rừng thông — ít đông hơn Fansipan, ảnh sương đẹp.',
                'author' => 'Photographer Ken',
            ),
            'places' => array(
                'Thác Bạc',
                'Thác Tình Yêu',
                'Rừng thông',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Trek nhẹ qua rừng thông — Thác Bạc (~200m) và Thác Tình Yêu trong một ngày.',
            'highlights' => array(
                'Thác Bạc',
                'Thác Tình Yêu',
                'HDV kể chuyện địa phương',
                'Góc chụp sương',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Silver & Love Falls',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => '07:00–07:30 đón, Thác Bạc sáng sớm, Thác Tình Yêu, trưa quán địa phương, về ~15:00.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'HDV',
                'Trưa',
            ),
            'exclusions' => array(
                'Vé (nếu có)',
                'Tip',
            ),
            'notes' => array(
                'Mùa mưa đường trơn; mang áo mưa.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 780000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'food-tour-am-thuc-sapa',
            'title' => 'Tour ẩm thực Sa Pa — Buổi tối',
            'zoneSlug' => 'thi-tran-sapa',
            'zone' => 'Thị trấn Sa Pa',
            'tourCode' => 'SP0.5D-02',
            'duration' => 'Nửa đêm',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 267,
            'badge' => 'Ăn uống',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'am-thuc',
            ),
            'quote' => array(
                'text' => 'Thắng cố, cá hồi, BBQ và rượu ngô — ăn no mà vẫn nhớ Tây Bắc.',
                'author' => 'Food blogger Ken',
            ),
            'places' => array(
                'Chợ đêm',
                'Quán thắng cố',
                'Nhà hàng cá hồi',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => '18:00–22:00 — dẫn ăn 6–8 món đặc sản với HDV am thực.',
            'highlights' => array(
                'Thắng cố',
                'Lẩu cá hồi',
                'BBQ Tây Bắc',
                'Rượu ngô',
                'Chợ đêm ăn vặt',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Tour ẩm thực tối',
                    'meals' => 'Tối',
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => 'Đi 3–4 quán đặc trưng, thử món, kể chuyện ẩm thực vùng núi.',
                ),
            ),
            'inclusions' => array(
                'HDV am thực',
                '7–8 món tasting',
            ),
            'exclusions' => array(
                'Rượu thêm',
                'Mua mang về',
            ),
            'notes' => array(
                'Báo dị ứng trước.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 520000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'gia-dinh-sapa-2n1d',
            'title' => 'Sa Pa gia đình 2 ngày 1 đêm — Nhẹ nhàng',
            'zoneSlug' => 'thi-tran-sapa',
            'zone' => 'Thị trấn Sa Pa',
            'tourCode' => 'SP2D-04',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 178,
            'badge' => 'Gia đình',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'gia-dinh',
            ),
            'quote' => array(
                'text' => 'Bé thích tàu Mường Hoa, bố mẹ thích Fansipan — ai cũng vui.',
                'author' => 'Chị Ngọc Ánh',
            ),
            'places' => array(
                'Fansipan',
                'Mường Hoa',
                'Cát Cát',
                'Chợ đêm',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Lịch nhẹ cho trẻ: cáp treo, tàu hoả, Cát Cát — không trek nặng.',
            'highlights' => array(
                'Cáp treo Fansipan (có cabin)',
                'Tàu Mường Hoa',
                'Cát Cát trek nhẹ',
                'Chợ đêm ăn vặt',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Fansipan & Mường Hoa',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'cable_car',
                        'train',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Sáng Fansipan, chiều tàu Mường Hoa, tối chợ đêm.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Cát Cát — Tiễn',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Cát Cát nhẹ, trưa tiễn.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng',
                'Vé điểm tham quan',
                'HDV gia đình',
            ),
            'exclusions' => array(
                'Lưu trú',
                'Tàu đêm HN',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 2250000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'ha-noi-sapa-tour-ngay',
            'title' => 'Tour ngày Hà Nội — Sa Pa',
            'zoneSlug' => 'ket-hop-ha-noi',
            'zone' => 'Cửa ngõ Hà Nội',
            'tourCode' => 'SP1D-HN',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 356,
            'badge' => 'Tour ngày',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'cuoi-tuan-ha-noi',
            ),
            'quote' => array(
                'text' => 'Một ngày đủ Fansipan và nhà thờ — về HN tối, không cần nghỉ lại.',
                'author' => 'Anh Felix',
            ),
            'places' => array(
                'Limousine HN',
                'Fansipan',
                'Nhà thờ đá',
            ),
            'start' => 'Hà Nội',
            'end' => 'Hà Nội',
            'highlightsIntro' => '05:00 HN — 23:00 HN: limo + Fansipan + city tour — cho khách thiếu thời gian.',
            'highlights' => array(
                'Limousine 2 chiều',
                'Cáp treo Fansipan',
                'Nhà thờ đá',
                'Trưa thắng cố',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Day trip HN—Sa Pa',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'limousine',
                        'car',
                        'cable_car',
                    ),
                    'overnight' => null,
                    'content' => '05:00 đón HN, 10:30 Fansipan, trưa, chiều nhà thờ, 17:00 xuống núi, ~23:00 về HN.',
                ),
            ),
            'inclusions' => array(
                'Limousine',
                'Xe nội bộ',
                'Vé Fansipan',
                'HDV',
                'Trưa',
            ),
            'exclusions' => array(
                'Ham Rong',
                'Mua sắm',
            ),
            'notes' => array(
                'Mệt hơn 2 ngày 1 đêm — khuyên cuối tuần nghỉ lại.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 1280000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'sapa-4n3d-kham-pha-sau',
            'title' => 'Sa Pa 4 ngày 3 đêm — Khám phá sâu',
            'zoneSlug' => 'homestay-trek-ban-ho',
            'zone' => 'Homestay trek Bản Hồ',
            'tourCode' => 'SP4D-01',
            'duration' => '4 ngày 3 đêm',
            'days' => 4,
            'rating' => 4.8,
            'reviewCount' => 72,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                '4n3d',
                'trekking-homestay',
            ),
            'quote' => array(
                'text' => 'Bốn ngày mới đủ trek homestay, Fansipan và tour ẩm thực đầy đủ.',
                'author' => 'Nhóm bạn Hà Nội',
            ),
            'places' => array(
                'Fansipan',
                'Mường Hoa',
                'Lao Chai',
                'Ta Van',
                'Bản Hồ',
                'Cát Cát',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Hành trình đầy đủ: Fansipan, 2 đêm homestay trek, ẩm thực và thị trấn.',
            'highlights' => array(
                '2 đêm homestay trek',
                'Fansipan cáp treo',
                'Mường Hoa & Cát Cát',
                'Tour ẩm thực & thắng cố',
                'Ham Rong biển mây',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đón — Fansipan',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'cable_car',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Lên Sa Pa, chiều Fansipan, tối tour ẩm thực.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Trek Lao Chai — Ta Van',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'trekking',
                    ),
                    'overnight' => 'Homestay Ta Van',
                    'content' => 'Cả ngày trek & homestay.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Ta Van — Bản Hồ — Sa Pa',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'trekking',
                        'car',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Sáng trek Bản Hồ, chiều về thị trấn.',
                ),
                array(
                    'day' => 4,
                    'title' => 'Ham Rong — Cát Cát — Tiễn',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Ham Rong, Cát Cát, tiễn.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng',
                'Homestay & vé',
                'HLV',
                'Bữa theo chương trình',
            ),
            'exclusions' => array(
                'Lưu trú Sa Pa (1 đêm)',
                'Tàu đêm HN',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 6,
            'priceFrom' => 5200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'combo-sapa-bac-ha-3n2d',
            'title' => 'Sa Pa — Bắc Hà 3 ngày 2 đêm',
            'zoneSlug' => 'ket-hop-bac-ha',
            'zone' => 'Kết hợp Bắc Hà',
            'tourCode' => 'SPBH3D-01',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 4.7,
            'reviewCount' => 89,
            'badge' => 'Combo',
            'featured' => false,
            'styles' => array(
                '3n2d',
            ),
            'quote' => array(
                'text' => 'Chợ phiên Bắc Hà Chủ nhật + Sa Pa hai ngày — contrast hay cho khách quốc tế.',
                'author' => 'David Park',
            ),
            'places' => array(
                'Sa Pa',
                'Fansipan',
                'Chợ Bắc Hà',
                'Bản Flower H\'Mong',
            ),
            'start' => 'Lào Cai',
            'end' => 'Lào Cai',
            'highlightsIntro' => '2 đêm: 2N Sa Pa + 1 buổi chợ Bắc Hà — xe nối ~2h giữa hai vùng.',
            'highlights' => array(
                'Chợ phiên Chủ nhật',
                'Fansipan',
                'Bản làng Flower H\'Mong',
                'Shuttle Lào Cai',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Lào Cai — Sa Pa — Fansipan',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'cable_car',
                    ),
                    'overnight' => 'Sa Pa',
                    'content' => 'Sáng đón ga, lên Sa Pa, chiều Fansipan.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Sa Pa — Mường Hoa — Bắc Hà',
                    'meals' => 'Sáng; Tối',
                    'transport' => array(
                        'car',
                        'train',
                    ),
                    'overnight' => 'Bắc Hà',
                    'content' => 'Sáng Mường Hoa, chiều lên Bắc Hà, tối homestay.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Chợ Bắc Hà — Về Lào Cai',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng chợ (CN), trưa về Lào Cai, tiễn tàu/HN.',
                ),
            ),
            'inclusions' => array(
                'Xe 3 ngày',
                'Vé Fansipan',
                'HDV',
                'Homestay Bắc Hà 1 đêm',
            ),
            'exclusions' => array(
                'Lưu trú Sa Pa',
                'Tàu đêm',
            ),
            'notes' => array(
                'Chợ Bắc Hà chỉ Chủ nhật — lên lịch theo tuần.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 3650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'photo-tour-ruong-bac-thang',
            'title' => 'Tour chụp ảnh ruộng bậc thang — 1 ngày',
            'zoneSlug' => 'thung-lung-muong-hoa',
            'zone' => 'Thung lũng Mường Hoa',
            'tourCode' => 'SP1D-04',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 94,
            'badge' => 'Photography',
            'featured' => false,
            'styles' => array(
                'day-trip',
                'cap-doi',
            ),
            'quote' => array(
                'text' => 'Photographer biết góc Mường Hoa lúc 6h — ảnh bậc thang không cần filter.',
                'author' => 'Cặp đôi Huy — Mai',
            ),
            'places' => array(
                'Mường Hoa',
                'Y Linh Hồ',
                'Lao Chai',
                'Ta Van',
            ),
            'start' => 'Sa Pa',
            'end' => 'Sa Pa',
            'highlightsIntro' => 'Xe + photographer half-day sương sớm — Mường Hoa, Y Linh Hồ, Lao Chai.',
            'highlights' => array(
                'Photographer 4h',
                '3–4 địa điểm',
                'Gợi ý trang phục dân tộc',
                '50+ ảnh chỉnh màu',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Terrace photo tour',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => '05:00 Mường Hoa sương, 08:00 Y Linh Hồ, 10:00 Lao Chai (tuỳ mùa vàng/xanh).',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Photographer',
                '50+ ảnh',
            ),
            'exclusions' => array(
                'Makeup',
                'Trang phục thuê',
            ),
            'notes' => array(
                'Mùa vàng Sep–Oct đẹp nhất; mùa nước đổ May–Jun.',
            ),
            'faqs' => array(),
            'galleryCount' => 6,
            'priceFrom' => 2400000,
            'currency' => 'VND',
        ),
    ),

    'cruises' => array(
        array(
            'slug' => 'fansipan-cap-treo-tong-hop',
            'title' => 'Fansipan cáp treo tổng hợp — Ba đoạn lên đỉnh',
            'typeSlug' => 'cap-treo-fansipan', 'typeName' => 'Cáp treo Fansipan',
            'tourCode' => 'SP-CT-01', 'duration' => '4 giờ', 'days' => 1,
            'rating' => 4.9, 'reviewCount' => 892, 'badge' => 'Bán chạy',
            'styles' => array('fansipan', 'family', 'day-trip'),
            'quote' => array('text' => 'Cáp treo ba đoạn lên 3143m — trải nghiệm đặc trưng Sa Pa.', 'author' => 'Gia đình chị Hương'),
            'places' => array('Ga Mường Hoa', 'Ga Hoàng Liên', 'Đỉnh Fansipan'),
            'start' => 'Ga Mường Hoa', 'end' => 'Ga Mường Hoa',
            'departurePort' => 'Fansipan Sun World', 'boatClass' => 'Cáp treo 3 đoạn', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '07:30–11:30 — cáp treo Sun World, chùa trên đỉnh, view Hoàng Liên Sơn.',
            'highlights' => array('Vé cáp treo combo', 'Chùa trên đỉnh', 'Ảnh trên mây', 'HDV địa phương'),
            'itinerary' => array(array('day' => 1, 'title' => 'Cable car summit', 'meals' => 'Đồ ăn nhẹ', 'transport' => array('cable_car'), 'overnight' => null,
                'content' => 'Lên ga, cáp treo 3 đoạn, tham quan đỉnh, về trưa.')),
            'inclusions' => array('Vé cáp treo', 'HDV'), 'exclusions' => array('Tàu Mười Mây', 'Xe từ thị trấn'),
            'notes' => array('Sương dày có thể che view — đổi slot miễn phí.'), 'faqs' => array(),
            'galleryCount' => 5, 'priceFrom' => 750000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'fansipan-tau-mui-may-sang-som',
            'title' => 'Fansipan tàu Mười Mây — Sáng sớm trên đỉnh',
            'typeSlug' => 'tau-mui-may-fansipan', 'typeName' => 'Tàu Mười Mây Fansipan',
            'tourCode' => 'SP-TMM-02', 'duration' => '3 giờ', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 234, 'badge' => 'Sáng sớm',
            'styles' => array('fansipan', 'romantic', 'photography'),
            'quote' => array('text' => 'Tàu leo núi giữa mây — cảm giác chạm đỉnh Fansipan thật sự.', 'author' => 'Chị Diệu Linh'),
            'places' => array('Tàu Mười Mây', 'Đỉnh Fansipan', 'Chùa Vân'),
            'start' => 'Ga Hoàng Liên', 'end' => 'Ga Hoàng Liên',
            'departurePort' => 'Fansipan Sun World', 'boatClass' => 'Tàu leo núi', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '06:30–09:30 — tàu Mười Mây + cáp treo đoạn cuối, góc chụp sương trên đỉnh.',
            'highlights' => array('Tàu Mười Mây', 'Vé combo cáp treo', 'View biển mây', 'Nhóm max 12'),
            'itinerary' => array(array('day' => 1, 'title' => 'Muoi May morning', 'meals' => 'Đồ ăn nhẹ', 'transport' => array('cable_car', 'train'), 'overnight' => null,
                'content' => 'Sáng sớm lên tàu, dừng chùa, chụp ảnh đỉnh, về trước 10h.')),
            'inclusions' => array('Vé tàu + cáp treo', 'HDV'), 'exclusions' => array('Xe đón resort'),
            'notes' => array('Mùa sương Nov–Feb đẹp nhất — mang áo ấm.'), 'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 820000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'fansipan-sunset-cap-treo',
            'title' => 'Fansipan hoàng hôn — Cáp treo chiều tà',
            'typeSlug' => 'cap-treo-fansipan', 'typeName' => 'Cáp treo Fansipan',
            'tourCode' => 'SP-CT-03', 'duration' => '3.5 giờ', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 156, 'badge' => null,
            'styles' => array('fansipan', 'romantic', 'balanced'),
            'quote' => array('text' => 'Hoàng hôn trên đỉnh Fansipan — ít đông hơn buổi sáng.', 'author' => 'Anh Felix'),
            'places' => array('Đỉnh Fansipan', 'Hoàng Liên Sơn'),
            'start' => 'Ga Mường Hoa', 'end' => 'Ga Mường Hoa',
            'departurePort' => 'Fansipan Sun World', 'boatClass' => 'Cáp treo hoàng hôn', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '15:00–18:30 — slot chiều, hoàng hôn trên đỉnh, phù hợp cặp đôi.',
            'highlights' => array('Slot chiều', 'Hoàng hôn đỉnh', 'Ảnh couple', 'Snack trên đỉnh'),
            'itinerary' => array(array('day' => 1, 'title' => 'Sunset summit', 'meals' => 'Đồ ăn nhẹ', 'transport' => array('cable_car'), 'overnight' => null,
                'content' => 'Chiều lên cáp treo, ngắm hoàng hôn, về tối.')),
            'inclusions' => array('Vé cáp treo', 'Snack'), 'exclusions' => array('Xe đón'),
            'notes' => array('Thời tiết lạnh trên đỉnh — áo khoác bắt buộc.'), 'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 780000.0, 'currency' => 'VND',
        ),
    ),

    'blog_categories' => array(
        array('slug' => 'thi-tran-sapa', 'name' => 'Thị trấn Sa Pa', 'zoneSlug' => 'thi-tran-sapa', 'count' => 2),
        array('slug' => 'ruong-bac-thang', 'name' => 'Ruộng bậc thang', 'zoneSlug' => 'thung-lung-muong-hoa', 'count' => 2),
        array('slug' => 'di-chuyen-ha-noi', 'name' => 'Di chuyển từ Hà Nội', 'zoneSlug' => 'ket-hop-ha-noi', 'count' => 2),
        array('slug' => 'am-thuc-tay-bac', 'name' => 'Ẩm thực Tây Bắc', 'zoneSlug' => 'thi-tran-sapa', 'count' => 2),
    ),

    'popular_keywords' => array(
        'Kinh nghiệm du lịch Sa Pa', 'Sa Pa mùa nao đẹp', 'Từ Hà Nội đi Sa Pa', 'Tàu đêm SP CH Lào Cai',
        'Tour cuối tuần Sa Pa', 'Ăn gì ở Sa Pa', 'Cáp treo Fansipan giá', 'Sa Pa 2 ngày 1 đêm',
        'Trek homestay Mường Hoa', 'Homestay Sa Pa khu nào', 'Ruộng bậc thang mùa vàng', 'Bay Nội Bài Sa Pa',
    ),

    'articles' => array(
        array(
            'slug' => 'tu-ha-noi-di-sapa-the-nao',
            'title' => 'Từ Hà Nội đi Sa Pa thế nào? Tàu đêm, limousine hay bay Nội Bài',
            'zoneSlug' => 'ket-hop-ha-noi', 'zone' => 'Cửa ngõ Hà Nội',
            'category' => 'Di chuyển từ Hà Nội', 'categorySlug' => 'di-chuyen-ha-noi',
            'tags' => array('Di chuyển thế nào?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '05/06/2026', 'updatedAt' => '20/07/2026',
            'views' => 3120, 'rating' => 4.9, 'ratingCount' => 78,
            'excerpt' => 'Sa Pa cách Hà Nội ~320km — tàu đêm SP/CH phổ biến nhất; limousine 5–6h hoặc bay Nội Bài + xe nối.',
            'content' => array(
                array('type' => 'p', 'text' => 'Sa Pa không có sân bay. Phần lớn khách cuối tuần từ Hà Nội chọn tàu đêm SP (~8h) hoặc CH (~9h) đến Lào Cai + shuttle 38km. Limousine 5–6h hoặc bay Hà Nội + transfer.'),
                array('type' => 'h2', 'id' => 'tau-dem', 'text' => 'I. Tàu đêm Hà Nội — Lào Cai'),
                array('type' => 'p', 'text' => 'Tối thứ 6 lên tàu, sáng thứ 7 đến Lào Cai — tiết kiệm 1 đêm khách sạn HN. SP nhanh hơn, CH rẻ hơn.'),
                array('type' => 'h2', 'id' => 'limousine', 'text' => 'II. Limousine Hà Nội — Sa Pa'),
                array('type' => 'p', 'text' => '5–6 giờ thẳng lên Sa Pa — phù hợp không muốn ngủ tàu. T6–CN nên đặt trước.'),
                array('type' => 'h2', 'id' => 'bay-han', 'text' => 'III. Bay Nội Bài + transfer'),
                array('type' => 'p', 'text' => 'Khách tỉnh xa bay Hà Nội, xe nối Lào Cai/Sa Pa ~5–6h — tiện khi gộp tour nhiều ngày Tây Bắc.'),
            ),
            'faqs' => array(array('q' => 'Tàu SP hay CH?', 'a' => 'SP ~8h, giá cao hơn; CH ~9h, rẻ hơn — cả hai đều có cabin 4/6 người.')),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'sapa-mua-nao-dep-nhat',
            'title' => 'Sa Pa mùa nao đẹp nhất? Bậc thang vàng, sương lạnh & mùa xanh',
            'zoneSlug' => 'thung-lung-muong-hoa', 'zone' => 'Thung lũng Mường Hoa',
            'category' => 'Ruộng bậc thang', 'categorySlug' => 'ruong-bac-thang',
            'tags' => array('Mẹo du lịch', 'Chơi gì, xem gì?'),
            'author' => 'Lan Hương', 'publishedAt' => '12/06/2026', 'updatedAt' => '28/07/2026',
            'views' => 2680, 'rating' => 4.9, 'ratingCount' => 62,
            'excerpt' => 'Mát quanh năm ~1600m — mỗi mùa một vẻ: vàng Sep–Oct, xanh May–Aug, sương lạnh Nov–Feb.',
            'content' => array(
                array('type' => 'p', 'text' => 'Sa Pa ~1600m — nhiệt độ thường 15–22°C, mùa đông 0–8°C. Mùa vàng (Sep–Oct) đẹp chụp ảnh; May–Aug xanh mướt; Nov–Feb sương mù lãng mạn.'),
                array('type' => 'h2', 'id' => 'mua-vang', 'text' => 'Mùa vàng bậc thang (Sep–Oct)'),
                array('type' => 'p', 'text' => 'Mường Hoa và Y Linh Hồ vàng óng — đặt tour & homestay sớm.'),
            ),
            'faqs' => array(), 'galleryCount' => 5,
        ),
        array(
            'slug' => 'an-gi-o-sapa',
            'title' => 'Ăn gì ở Sa Pa? Thắng cố, cá hồi, BBQ & rượu ngô',
            'zoneSlug' => 'thi-tran-sapa', 'zone' => 'Thị trấn Sa Pa',
            'category' => 'Ẩm thực Tây Bắc', 'categorySlug' => 'am-thuc-tay-bac',
            'tags' => array('Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '18/06/2026', 'updatedAt' => '02/08/2026',
            'views' => 2180, 'rating' => 4.8, 'ratingCount' => 54,
            'excerpt' => 'Ẩm thực Sa Pa gắn núi rừng Tây Bắc — thắng cố, lẩu cá hồi, BBQ và rượu ngô.',
            'content' => array(
                array('type' => 'p', 'text' => 'Tour ẩm thực nửa đêm là cách nhanh nhất thử đặc sản — thắng cố, lẩu cá hồi, BBQ và mua rượu ngô làm quà.'),
                array('type' => 'ul', 'items' => array('Thắng cố', 'Lẩu cá hồi', 'BBQ Tây Bắc', 'Rượu ngô', 'Cơm lam')),
            ),
            'faqs' => array(), 'galleryCount' => 4,
        ),
        array(
            'slug' => 'fansipan-kinh-nghiem-cap-treo',
            'title' => 'Fansipan: kinh nghiệm cáp treo, giờ đi và mẹo chụp ảnh',
            'zoneSlug' => 'fansipan-sun-world', 'zone' => 'Fansipan Sun World',
            'category' => 'Thị trấn Sa Pa', 'categorySlug' => 'thi-tran-sapa',
            'tags' => array('Chơi gì, xem gì?', 'Chọn tour nào?'),
            'author' => 'Phạm Thị Liên', 'publishedAt' => '22/06/2026', 'updatedAt' => '10/07/2026',
            'views' => 1890, 'rating' => 4.9, 'ratingCount' => 48,
            'excerpt' => 'Đỉnh nóc Đông Dương 3143m — nên đi sáng sớm tránh đông, mang áo ấm trên đỉnh.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cáp treo 3 đoạn từ ga Mường Hoa — có thể thêm tàu Mười Mây. Tour 1 ngày gộp Fansipan + city tour — hoặc ghép vào chuyến 2 ngày 1 đêm cuối tuần.'),
                array('type' => 'p', 'text' => 'Mùa sương view đẹp nhưng lạnh; mùa hè đông khách. Đặt vé qua :brand để đổi slot khi thời tiết xấu.'),
            ),
            'faqs' => array(array('q' => 'Trẻ em đi cáp treo được không?', 'a' => 'Có — cabin an toàn, trẻ nhỏ cần giám sát trên đỉnh lạnh.')),
            'galleryCount' => 5,
        ),
    ),

    'testimonials' => array(
        array('name' => 'Nguyễn Minh Anh', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => '2 ngày 1 đêm cuối tuần tàu SP vừa đủ — Fansipan trong sương và lẩu cá hồi tối là nhớ nhất.', 'photos' => 6, 'trip' => 'Sa Pa 2 ngày 1 đêm cuối tuần', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Sarah Kim', 'country' => 'Hàn Quốc', 'flag' => '🇰🇷', 'rating' => 5.0,
            'quote' => 'Homestay Ta Van and terrace trek — authentic Northwest Vietnam experience.', 'photos' => 9, 'trip' => 'Mường Hoa trek homestay', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Trần Quốc Bảo', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.9,
            'quote' => 'Ham Rong cloud sea at dawn — chiều Mường Hoa train, perfect couple trip.', 'photos' => 5, 'trip' => 'Sa Pa 2 ngày 1 đêm lãng mạn', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Felix Müller', 'country' => 'Đức', 'flag' => '🇩🇪', 'rating' => 4.8,
            'quote' => 'Fansipan cable car was smooth — they even helped book a terrace-view homestay.', 'photos' => 7, 'trip' => 'Fansipan 1 ngày', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Diệu Linh', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => 'Photo tour bậc thang sáng sớm — ảnh Mường Hoa đẹp hơn mọi filter.', 'photos' => 4, 'trip' => 'Photo tour ruộng bậc thang', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'David Park', 'country' => 'Úc', 'flag' => '🇦🇺', 'rating' => 4.9,
            'quote' => 'Overnight train from Hanoi was an adventure — Bac Ha Sunday market combo was unique.', 'photos' => 8, 'trip' => 'Combo Sa Pa Bắc Hà', 'avatar' => null, 'photoUrls' => array()),
    ),

    'team' => array(
        array(
            'slug' => 'hoang-van-linh', 'name' => 'Hoàng Văn Linh', 'role' => 'Giám đốc điều hành',
            'bio' => 'Sinh ra tại Lào Cai, hơn 14 năm thiết kế tour Sa Pa và kết nối homestay dân tộc địa phương...',
            'phone' => '+84 214 388 8888', 'email' => 'linh.hoang@hisapa.dev', 'area' => 'Sa Pa, Lào Cai',
            'years_experience' => 14, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 4200, 'stat_tours' => 680, 'stat_awards' => 4, 'is_verified' => true,
            'bio_html' => '<p>Sinh ra tại Lào Cai, Hoàng Văn Linh có hơn 14 năm kinh nghiệm thiết kế tour Sa Pa và tư vấn homestay theo bản làng.</p>',
            'bio_html_en' => '<p>Born in Lao Cai, Van Linh has 14 years designing Sapa tours and advising stays by village.</p>',
            'name_en' => 'Hoang Van Linh', 'role_en' => 'Chief Executive Officer',
            'short_bio_en' => 'Born in Lao Cai — 14 years designing Sapa tours and homestay advice.',
            'achievements' => array('Xây dựng mạng 40+ homestay đối tác trực tiếp', 'Đối tác Fansipan Sun World & trek Mường Hoa'),
            'skills' => array(array('skill' => 'Thiết kế tour núi Tây Bắc', 'percent' => 97), array('skill' => 'Tư vấn homestay theo bản', 'percent' => 95)),
            'experiences' => array(array('title' => 'Giám đốc điều hành', 'company' => 'Hi Sa Pa', 'items' => array('Chiến lược sản phẩm tour & dịch vụ Sa Pa'))),
            'degrees' => array(array('title' => 'Cử nhân Quản trị Du lịch', 'school' => 'Học viện Ngoại thương', 'items' => array())),
        ),
        array(
            'slug' => 'nguyen-thi-may', 'name' => 'Nguyễn Thị Mai', 'role' => 'Trưởng phòng thiết kế tour',
            'bio' => 'Chuyên gia tour cuối tuần Hà Nội, trek homestay và lịch trình cặp đôi...',
            'phone' => '+84 214 388 8899', 'email' => 'mai.nguyen@hisapa.dev', 'area' => 'Sa Pa & Hà Nội',
            'years_experience' => 10, 'languages' => array('Tiếng Việt', 'English', '한국어'),
            'stat_clients' => 2800, 'stat_tours' => 520, 'stat_awards' => 3, 'is_verified' => true,
            'bio_html' => '<p>Nguyễn Thị Mai phụ trách sản phẩm cuối tuần, trek homestay và tour lãng mạn — biết từng mùa bậc thang vàng/xanh.</p>',
            'bio_html_en' => '<p>Thi Mai leads weekend, homestay trek and romantic itineraries — she knows every golden and green terrace season.</p>',
            'name_en' => 'Nguyen Thi Mai', 'role_en' => 'Head of Tour Design',
            'short_bio_en' => 'Weekend escapes, homestay treks and couple itineraries specialist.',
            'achievements' => array('Thiết kế tour 2 ngày 1 đêm tàu đêm được đánh giá 4.9/5'),
            'skills' => array(array('skill' => 'Tour trek & homestay', 'percent' => 96), array('skill' => 'Itinerary lãng mạn', 'percent' => 94)),
            'experiences' => array(array('title' => 'Trưởng phòng thiết kế tour', 'company' => 'Hi Sa Pa', 'items' => array('Sản phẩm cuối tuần, trek, couple'))),
            'degrees' => array(array('title' => 'Cử nhân Địa lý Du lịch', 'school' => 'Đại học Khoa học Tự nhiên', 'items' => array())),
        ),
        array(
            'slug' => 'giang-a-pao', 'name' => 'Giang A Páo', 'role' => 'Trưởng đội trekking & an toàn',
            'bio' => 'HLV trekking H\'Mông bản địa, phụ trách Mường Hoa, Lao Chai—Ta Van...',
            'phone' => '+84 214 388 8877', 'email' => 'pao.gianga@hisapa.dev', 'area' => 'Mường Hoa & Bản Hồ',
            'years_experience' => 12, 'languages' => array('Tiếng Việt', 'English', 'H\'Mông'),
            'stat_clients' => 3600, 'stat_tours' => 720, 'stat_awards' => 4, 'is_verified' => true,
            'bio_html' => '<p>Giang A Páo là HLV trekking H\'Mông và điều phối an toàn cho mọi hoạt động bản làng tại Sa Pa.</p>',
            'bio_html_en' => '<p>Giang A Pao is a H\'Mong trekking coach and safety lead for village activities in Sapa.</p>',
            'name_en' => 'Giang A Pao', 'role_en' => 'Head of Trekking & Safety',
            'short_bio_en' => 'Certified H\'Mong trekking guide — safety lead for Muong Hoa and homestay trails.',
            'achievements' => array('Hơn 500 chuyến trek homestay không sự cố nghiêm trọng'),
            'skills' => array(array('skill' => 'Trekking & rescue', 'percent' => 98), array('skill' => 'Văn hoá dân tộc', 'percent' => 96)),
            'experiences' => array(array('title' => 'Trưởng đội trekking', 'company' => 'Hi Sa Pa', 'items' => array('An toàn trek, homestay, porter'))),
            'degrees' => array(array('title' => 'Chứng chỉ HLV trekking', 'school' => 'UBND Sa Pa', 'items' => array())),
        ),
        array(
            'slug' => 'pham-minh-chau', 'name' => 'Phạm Minh Châu', 'role' => 'Chuyên gia tư vấn cao cấp',
            'bio' => 'Tư vấn khách Hà Nội cuối tuần, combo Bắc Hà/Mù Cang Chải và lưu trú homestay...',
            'phone' => '+84 214 388 8866', 'email' => 'chau.pham@hisapa.dev', 'area' => 'Sa Pa & Hà Nội',
            'years_experience' => 8, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 2100, 'stat_tours' => 410, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Phạm Minh Châu là đầu mối tư vấn cho khách Hà Nội — tàu đêm, bay Nội Bài và gợi ý homestay theo ngân sách.</p>',
            'bio_html_en' => '<p>Minh Chau advises Hanoi weekend guests — overnight trains, Noi Bai flights and homestay suggestions by budget.</p>',
            'name_en' => 'Pham Minh Chau', 'role_en' => 'Senior Travel Consultant',
            'short_bio_en' => 'Hanoi weekend escapes, combos and homestay advice.',
            'achievements' => array('Tư vấn 600+ kỳ nghỉ cuối tuần HN—Sa Pa (2022–2026)'),
            'skills' => array(array('skill' => 'Tư vấn khách Hà Nội', 'percent' => 97), array('skill' => 'Combo đa điểm', 'percent' => 93)),
            'experiences' => array(array('title' => 'Chuyên gia tư vấn', 'company' => 'Hi Sa Pa', 'items' => array('Khách HN & combo Bắc Hà'))),
            'degrees' => array(array('title' => 'Cử nhân Marketing Du lịch', 'school' => 'UEH', 'items' => array())),
        ),
    ),

    'videos' => array(
        array('title' => 'Sương sớm Mường Hoa Sa Pa', 'description' => 'Thung lũng Mường Hoa buổi sáng — ruộng bậc thang trong sương.', 'date' => '10/07/2026', 'duration' => '05:45', 'tag' => 'Mường Hoa',
            'image' => 'https://i.ytimg.com/vi/SP00000001/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/SP00000001?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'SP00000001'),
        array('title' => 'Cáp treo Fansipan trên mây', 'description' => 'Fansipan Sun World — góc quay từ khách.', 'date' => '22/06/2026', 'duration' => '04:30', 'tag' => 'Fansipan',
            'image' => 'https://i.ytimg.com/vi/SP00000002/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/SP00000002?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'SP00000002'),
    ),

    'gallery_albums' => array(
        array('title' => 'Ruộng bậc thang mùa vàng 2026', 'photos' => 28, 'date' => '10/2026'),
        array('title' => 'Tour ẩm thực thắng cố & cá hồi', 'photos' => 18, 'date' => '08/2026'),
        array('title' => 'Homestay trek Ta Van', 'photos' => 16, 'date' => '07/2026'),
    ),

    'usps' => array(
        array('icon' => 'compass', 'sort' => 0,
            'vi' => array('title' => 'am hiểu Sa Pa như người bản địa', 'description' => 'Đội ngũ Lào Cai biết mùa bậc thang, homestay phù hợp và lịch Fansipan an toàn.'),




'en' => array('title' => 'true Sapa locals', 'description' => 'Our Lao Cai team knows terrace seasons, the right homestays and safe Fansipan windows.')),
        array('icon' => 'refund', 'sort' => 1,
            'vi' => array('title' => 'báo giá minh bạch, không phí ẩn', 'description' => 'Giá tour và dịch vụ liệt kê rõ từng hạng mục; lưu trú chọn theo bản và ngân sách.'),




'en' => array('title' => 'transparent pricing, no hidden fees', 'description' => 'Clear line-item quotes for tours and services; stays matched to your village and budget.')),
        array('icon' => 'mountain', 'sort' => 2,
            'vi' => array('title' => 'từ Fansipan đến trek homestay & Bắc Hà', 'description' => 'Một đầu mối cho cáp treo, trek Mường Hoa, tour ẩm thực và combo Mù Cang Chải.'),




'en' => array('title' => 'Fansipan to homestay trek & Bac Ha', 'description' => 'One contact for cable car, Muong Hoa treks, tour ẩm thựcs and Mu Cang Chai combos.')),
        array('icon' => 'support', 'sort' => 3,
            'vi' => array('title' => 'hỗ trợ 24/7 trên đèo Sa Pa', 'description' => 'Hotline khi sương mù, đèo kẹt hoặc cần đổi tàu/limo về Hà Nội.'),




'en' => array('title' => '24/7 Sapa pass support', 'description' => 'Hotline for fog delays, pass traffic or changing your return train/limo to Hanoi.')),
    ),

    'offices' => array(
        array('city' => 'Sa Pa, Lào Cai', 'address' => 'Thị trấn Sa Pa, huyện Sa Pa, tỉnh Lào Cai', 'phone' => '+84 214 388 8888'),
        array('city' => 'Hà Nội (đặt tour)', 'address' => 'Quận Hoàn Kiếm — văn phòng đại diện', 'phone' => '+84 24 3999 7777'),
    ),

    'values' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm như khách mời tại nhà'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is hosted like a guest at home')),
        array('vi' => array('name' => 'Am hiểu Sa Pa', 'desc' => 'Gắn Lào Cai — hiểu từng mùa bậc thang và đèo'),



'en' => array('name' => 'Sapa expertise', 'desc' => 'Rooted in Lao Cai — we know every terrace season and pass')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn đúng nhu cầu — không ép mua thêm dịch vụ'),



'en' => array('name' => 'Sincerity', 'desc' => 'Advice that fits your trip — never upselling you')),
        array('vi' => array('name' => 'An toàn', 'desc' => 'Trekking có HLV bản địa và kế hoạch dự phòng khi sương dày'),



'en' => array('name' => 'Safety', 'desc' => 'Trekking with local guides and fog backup plans')),
    ),
    'value_definitions' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm như khách mời tại nhà'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is hosted like a guest at home')),
        array('vi' => array('name' => 'Am hiểu Sa Pa', 'desc' => 'Gắn Lào Cai — hiểu từng mùa bậc thang và đèo'),



'en' => array('name' => 'Sapa expertise', 'desc' => 'Rooted in Lao Cai — we know every terrace season and pass')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn đúng nhu cầu — không ép mua thêm dịch vụ'),



'en' => array('name' => 'Sincerity', 'desc' => 'Advice that fits your trip — never upselling you')),
        array('vi' => array('name' => 'An toàn', 'desc' => 'Trekking có HLV bản địa và kế hoạch dự phòng khi sương dày'),



'en' => array('name' => 'Safety', 'desc' => 'Trekking with local guides and fog backup plans')),
    ),

    'reasons' => array(
        array('vi' => array('title' => 'HDV bản địa Lào Cai', 'desc' => 'Người H\'Mông, Tày dẫn trek, Fansipan và tư vấn homestay.'),



'en' => array('title' => 'Local Lao Cai guides', 'desc' => 'H\'Mong and Tay locals lead treks, Fansipan and homestay advice.')),
        array('vi' => array('title' => 'Homestay đúng bản, đúng ngân sách', 'desc' => 'Ta Van, Lao Chai, Bản Hồ — view ruộng, gần trek.'),



'en' => array('title' => 'Stays matched to your trek', 'desc' => 'Ta Van, Lao Chai, Ban Ho — terrace views, trek access.')),
        array('vi' => array('title' => 'Một đầu mối tàu + bay + tour', 'desc' => 'Tàu đêm SP/CH, transfer Nội Bài và tour gộp một báo giá.'),



'en' => array('title' => 'One contact: train, flights, tours', 'desc' => 'Overnight SP/CH trains, Noi Bai transfers and tours in one quote.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7', 'desc' => 'Hotline khi sương mù đèo kẹt hoặc đổi lịch Fansipan.'),



'en' => array('title' => '24/7 support', 'desc' => 'Hotline when fog blocks the pass or Fansipan schedules shift.')),
    ),
    'reason_definitions' => array(
        array('vi' => array('title' => 'HDV bản địa Lào Cai', 'desc' => 'Người H\'Mông, Tày dẫn trek, Fansipan và tư vấn homestay.'),



'en' => array('title' => 'Local Lao Cai guides', 'desc' => 'H\'Mong and Tay locals lead treks, Fansipan and homestay advice.')),
        array('vi' => array('title' => 'Homestay đúng bản, đúng ngân sách', 'desc' => 'Ta Van, Lao Chai, Bản Hồ — view ruộng, gần trek.'),



'en' => array('title' => 'Stays matched to your trek', 'desc' => 'Ta Van, Lao Chai, Ban Ho — terrace views, trek access.')),
        array('vi' => array('title' => 'Một đầu mối tàu + bay + tour', 'desc' => 'Tàu đêm SP/CH, transfer Nội Bài và tour gộp một báo giá.'),



'en' => array('title' => 'One contact: train, flights, tours', 'desc' => 'Overnight SP/CH trains, Noi Bai transfers and tours in one quote.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7', 'desc' => 'Hotline khi sương mù đèo kẹt hoặc đổi lịch Fansipan.'),



'en' => array('title' => '24/7 support', 'desc' => 'Hotline when fog blocks the pass or Fansipan schedules shift.')),
    ),

    'reference_persons' => array(
        array('name' => 'Ms. Sarah Kim', 'country' => 'Hàn Quốc', 'email' => 'sarah@hisapa.example', 'phone' => '+82 10 9876 5432', 'skype' => 'sarah.kim.sapa', 'image' => null, 'imageSrcset' => null),
        array('name' => 'Mr. Felix Müller', 'country' => 'Đức', 'email' => 'felix@hisapa.example', 'phone' => '+49 170 1234567', 'skype' => 'felix.muller.travel', 'image' => null, 'imageSrcset' => null),
    ),

    'about_page' => array(
        'vi' => array(
            'seo_title' => 'Về chúng tôi — Hi Sa Pa, kết nối du khách với Lào Cai',
            'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ Hi Sa Pa — tour, dịch vụ và tư vấn homestay theo bản làng.',
            'page_title' => 'Về chúng tôi',
            'page_subtitle' => 'Ruộng bậc thang, Fansipan — thiết kế bởi người bản địa yêu Sa Pa',
            'banner' => array('src' => null, 'srcset' => null, 'alt' => 'Đội ngũ Hi Sa Pa'),
            'mission' => array('title' => 'Sứ mệnh', 'text' => 'Mang đến hành trình chân thật trên núi Sa Pa — từ Fansipan, Mường Hoa đến homestay dân tộc — và giúp mỗi khách chọn tour, dịch vụ lẫn chỗ ở phù hợp trong một hành trình thống nhất.', 'image' => null, 'imageSrcset' => null),
            'vision' => array('title' => 'Tầm nhìn', 'text' => 'Trở thành cầu nối tin cậy nhất giữa du khách Hà Nội và Sa Pa — nơi mỗi người cảm nhận được nhịp sống Tây Bắc cách phố 320km.', 'image' => null, 'imageSrcset' => null),
            'sales_policy' => array('title' => 'Chính sách minh bạch', 'content' => 'Báo giá tour và dịch vụ liệt kê rõ từng hạng mục. Hỗ trợ đặt homestay theo bản và ngân sách. Đổi ngày linh hoạt khi sương mù/trekking hủy vì thời tiết.', 'cta_label' => 'Hỏi thêm', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'values_section' => array('title' => 'Giá trị cốt lõi', 'hub_label' => 'Giá trị', 'eyebrow' => 'Điều chúng tôi tin', 'subtitle' => 'Bốn giá trị dẫn dắt mọi hành trình trên núi Sa Pa.'),
            'reasons_section' => array('title' => 'Vì sao chọn Hi Sa Pa?', 'eyebrow' => 'Lý do', 'subtitle' => 'Bản địa, minh bạch, an toàn trekking.', 'cta_label' => 'Bắt đầu hành trình', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'reference_section' => array('title' => 'Đại diện nước ngoài', 'eyebrow' => 'Mạng lưới', 'subtitle' => 'Trao đổi bằng ngôn ngữ của bạn với đại diện Hi Sa Pa.'),
        ),




'en' => array(
            'seo_title' => 'About us — Hi Sa Pa, connecting travellers with Lao Cai mountains',
            'seo_description' => 'Our story, mission and team — tours, services and homestay advice by village.',
            'page_title' => 'About us',
            'page_subtitle' => 'Terraced fields and Fansipan — designed by locals who love Sapa',
            'banner' => array('src' => null, 'srcset' => null, 'alt' => 'Hi Sa Pa team'),
            'mission' => array('title' => 'Mission', 'text' => 'Deliver authentic Sapa mountain journeys — from Fansipan and Muong Hoa to ethnic homestays — and help every guest book tours, services and stays in one coherent trip.', 'image' => null, 'imageSrcset' => null),
            'vision' => array('title' => 'Vision', 'text' => 'The most trusted bridge between Hanoi travellers and Sapa — where everyone feels the Northwest rhythm just 320km from the city.', 'image' => null, 'imageSrcset' => null),
            'sales_policy' => array('title' => 'Transparent policy', 'content' => 'Clear line-item quotes for tours and services. Homestay booking support by village and budget. Flexible rescheduling when fog or weather cancels trekking.', 'cta_label' => 'Ask us', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'values_section' => array('title' => 'Core values', 'hub_label' => 'Values', 'eyebrow' => 'What we believe', 'subtitle' => 'Four values guiding every Sapa itinerary.'),
            'reasons_section' => array('title' => 'Why Hi Sa Pa?', 'eyebrow' => 'Why us', 'subtitle' => 'Local expertise, clear pricing, safe trekking.', 'cta_label' => 'Start your journey', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'reference_section' => array('title' => 'Representatives abroad', 'eyebrow' => 'Network', 'subtitle' => 'Speak in your language with Hi Sa Pa representatives.'),
        ),
    ),

    'hero_pills' => array(
        array('zone_slug' => 'fansipan-sun-world', 'vi' => array('label' => 'Fansipan cáp treo'),



'en' => array('label' => 'Fansipan cable car'), 'url' => '/diem-den/fansipan-sun-world'),
        array('zone_slug' => 'thung-lung-muong-hoa', 'vi' => array('label' => 'Mường Hoa bậc thang'),



'en' => array('label' => 'Muong Hoa terraces'), 'url' => '/diem-den/thung-lung-muong-hoa'),
    ),

    'home_sections' => array(
        'company_intro' => array(
            'vi' => array('key' => 'company_intro', 'eyebrow' => 'Chuyên gia Sa Pa', 'title' => 'Thoát urban cuối tuần — cách Hà Nội 320km', 'subtitle' => null,
                'body' => 'Hi Sa Pa là nền tảng du lịch Sa Pa do người bản địa xây dựng — <strong class="font-semibold text-ink">một đầu mối</strong> cho tour, vé tham quan, tàu đêm HN và lưu trú từ homestay đến resort. Chúng tôi thiết kế hành trình quanh Fansipan, Mường Hoa, Cát Cát và trek bản làng để bạn cảm nhận đúng nhịp sống Tây Bắc.',
                'metaLine' => 'Giấy phép lữ hành số 0052/2024/TCDL-GPLHQT', 'ctaLabel' => 'Về chúng tôi', 'ctaUrl' => '/ve-chung-toi', 'image' => null, 'imageAlt' => 'Hi Sa Pa'),




'en' => array('key' => 'company_intro', 'eyebrow' => 'Sapa experts', 'title' => 'Cuối tuần escape — 320km from Hanoi', 'subtitle' => null,
                'body' => 'Hi Sa Pa is a locally built Sapa travel platform — <strong class="font-semibold text-ink">one place</strong> for tours, tickets, Hanoi overnight trains and stays from homestays to resorts. We craft itineraries around Fansipan, Muong Hoa, Cat Cat and village treks.',
                'metaLine' => 'Travel license No. 0052/2024/TCDL-GPLHQT', 'ctaLabel' => 'About us', 'ctaUrl' => '/ve-chung-toi', 'image' => null, 'imageAlt' => 'Hi Sa Pa'),
        ),
        'featured_tours' => array(
            'vi' => array('key' => 'featured_tours', 'eyebrow' => 'Yêu thích', 'title' => 'Tour được đặt nhiều nhất', 'subtitle' => 'Hành trình khách đánh giá cao trong 12 tháng qua.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_tours', 'eyebrow' => 'Popular', 'title' => 'Most booked tours', 'subtitle' => 'Top-rated itineraries over the past 12 months.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'featured_cruises' => array(
            'vi' => array('key' => 'featured_cruises', 'eyebrow' => 'Fansipan Sun World', 'title' => 'Cáp treo & tàu Mười Mây', 'subtitle' => 'Chinh phục đỉnh 3143m — trải nghiệm núi đặc trưng Sa Pa.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_cruises', 'eyebrow' => 'Fansipan Sun World', 'title' => 'Cable car & Muoi May train', 'subtitle' => 'Reach 3143m — Sapa\'s đặc trưng mountain experience.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'featured_trains' => array(
            'vi' => array('key' => 'featured_trains', 'eyebrow' => 'Di chuyển', 'title' => 'Tàu đêm & limo Hà Nội — Sa Pa', 'subtitle' => 'Từ Hà Nội ~320km — một đầu mối đặt vé và đưa đón.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_trains', 'eyebrow' => 'Getting there', 'title' => 'Overnight trains & limos Hanoi — Sapa', 'subtitle' => '~320km from Hanoi — one place to book transfers.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'support_services' => array(
            'vi' => array('key' => 'support_services', 'eyebrow' => 'Dịch vụ', 'title' => 'Vé, trải nghiệm & hỗ trợ', 'subtitle' => 'Fansipan, Ham Rong, trek homestay và tiện ích trên núi.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'support_services', 'eyebrow' => 'Services', 'title' => 'Tickets, experiences & support', 'subtitle' => 'Fansipan, Ham Rong, homestay treks and mountain extras.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'destinations' => array(
            'vi' => array('key' => 'destinations', 'eyebrow' => 'Khắp Sa Pa', 'title' => 'Khu vực được yêu thích', 'subtitle' => 'Thị trấn, Fansipan, Mường Hoa, Cát Cát và combo Bắc Hà.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'destinations', 'eyebrow' => 'Across Sapa', 'title' => 'Favourite areas', 'subtitle' => 'Town, Fansipan, Muong Hoa, Cat Cat and Bac Ha combos.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'testimonials' => array(
            'vi' => array('key' => 'testimonials', 'eyebrow' => 'Khách kể', 'title' => 'Trải nghiệm thật', 'subtitle' => 'Hơn 4.200 khách đã đồng hành cùng Hi Sa Pa.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Xem cảm nhận', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'testimonials', 'eyebrow' => 'Guest stories', 'title' => 'Real experiences', 'subtitle' => 'Over 4,200 guests have travelled with Hi Sa Pa.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'All reviews', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => null, 'imageAlt' => null),
        ),
        'review_platforms' => array(
            'vi' => array('key' => 'review_platforms', 'eyebrow' => null, 'title' => 'Hi Sa Pa được đánh giá cao trên', 'subtitle' => null, 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'review_platforms', 'eyebrow' => null, 'title' => 'Hi Sa Pa is highly rated on', 'subtitle' => null, 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'team' => array(
            'vi' => array('key' => 'team', 'eyebrow' => 'Con người Hi Sa Pa', 'title' => 'Đội ngũ bản địa', 'subtitle' => 'Cùng bạn từ ý tưởng đến khi rời đèo Sa Pa.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Gặp đội ngũ', 'ctaUrl' => '/doi-ngu', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'team', 'eyebrow' => 'The Hi Sa Pa team', 'title' => 'Local experts', 'subtitle' => 'With you from the first idea until you leave the Sapa pass.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Meet the team', 'ctaUrl' => '/doi-ngu', 'image' => null, 'imageAlt' => null),
        ),
        'videos' => array(
            'vi' => array('key' => 'videos', 'eyebrow' => 'Video', 'title' => 'Sa Pa qua ống kính', 'subtitle' => 'Bậc thang, Fansipan và homestay — clip từ khách và đội ngũ.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Xem video', 'ctaUrl' => '/video-trai-nghiem', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'videos', 'eyebrow' => 'Video', 'title' => 'Sapa on film', 'subtitle' => 'Terraces, Fansipan and homestays — from guests and our team.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'All videos', 'ctaUrl' => '/video-trai-nghiem', 'image' => null, 'imageAlt' => null),
        ),
        'quick_inquiry' => array(
            'vi' => array('key' => 'quick_inquiry', 'eyebrow' => 'Tư vấn miễn phí', 'title' => 'Gửi lời nhắn', 'subtitle' => null,
                'body' => 'Chưa chọn tour hay homestay? Phản hồi trong <strong class="font-semibold text-ink">24 giờ làm việc</strong>, miễn phí.', 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'quick_inquiry', 'eyebrow' => 'Free advice', 'title' => 'Send a message', 'subtitle' => null,
                'body' => 'Not sure which tour or homestay? Reply within <strong class="font-semibold text-ink">1 business day</strong>, free of charge.', 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
    ),

    'footer_columns' => array(
        array('title' => 'Hi Sa Pa', 'links' => array(
            array('label' => 'Về chúng tôi', 'route' => array('about')),
            array('label' => 'Cảm nhận khách hàng', 'route' => array('reviews')),
            array('label' => 'Đội ngũ', 'route' => array('team')),
            array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
        )),
        array('title' => 'Tour phổ biến', 'links' => array(
            array('label' => 'Sa Pa 2 ngày 1 đêm cuối tuần', 'route' => array('tours.show', array('zone' => 'ket-hop-ha-noi', 'slug' => 'sapa-2n1d-cuoi-tuan-ha-noi'))),
            array('label' => 'Fansipan cáp treo 1 ngày', 'route' => array('tours.show', array('zone' => 'fansipan-sun-world', 'slug' => 'fansipan-cap-treo-1-ngay'))),
            array('label' => 'Sa Pa 3 ngày 2 đêm tổng quan', 'route' => array('tours.show', array('zone' => 'thi-tran-sapa', 'slug' => 'sapa-3n2d-tong-quan'))),
            array('label' => 'Tour ẩm thực ẩm thực', 'route' => array('tours.show', array('zone' => 'thi-tran-sapa', 'slug' => 'food-tour-am-thuc-sapa'))),
        )),
        array('title' => 'Khu vực', 'links' => array(
            array('label' => 'Fansipan Sun World', 'route' => array('guide.zone', array('zone' => 'fansipan-sun-world'))),
            array('label' => 'Mường Hoa bậc thang', 'route' => array('guide.zone', array('zone' => 'thung-lung-muong-hoa'))),
            array('label' => 'Cáp treo Fansipan', 'route' => array('cruises.index', array('type' => 'cap-treo-fansipan'))),
        )),
        array('title' => 'Cẩm nang', 'links' => array(
            array('label' => 'Từ Hà Nội đi Sa Pa', 'route' => array('guide.show', array('zone' => 'ket-hop-ha-noi', 'slug' => 'tu-ha-noi-di-sapa-the-nao'))),
            array('label' => 'Mùa nào đẹp?', 'route' => array('guide.show', array('zone' => 'thung-lung-muong-hoa', 'slug' => 'sapa-mua-nao-dep-nhat'))),
            array('label' => 'Ăn gì ở Sa Pa', 'route' => array('guide.show', array('zone' => 'thi-tran-sapa', 'slug' => 'an-gi-o-sapa'))),
        )),
    ),

    'footer_seo_links' => array(
        array('label' => 'Cẩm nang Sa Pa', 'route' => array('guide.zone', array('zone' => 'thi-tran-sapa'))),
        array('label' => 'Tour Sa Pa trọn gói', 'route' => array('tours.index', array('zone' => 'thi-tran-sapa'))),
        array('label' => 'Cáp treo Fansipan', 'route' => array('cruises.index', array('type' => 'cap-treo-fansipan'))),
        array('label' => 'Tàu đêm Hà Nội Sa Pa', 'route' => array('services.hub', array('cluster' => 'train'))),
        array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
    ),

    'tour_categories' => array(
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => '1-ngay',
            'type' => 'duration',
            'sort' => 1,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'city-tour-sapa-1-ngay',
                'fansipan-cap-treo-1-ngay',
                'thac-bac-thac-tinh-yeu-1-ngay',
                'ha-noi-sapa-tour-ngay',
                'photo-tour-ruong-bac-thang',
            ),
            'name' => array(
                'vi' => 'Tour 1 ngày',




'en' => '1-day tours',
            ),
            'subtitle' => array(
                'vi' => 'City, Fansipan, thác, photo bậc thang.',




'en' => 'City, Fansipan, falls, terrace photo.',
            ),
            'seo_body' => array(
                'vi' => 'Tour theo thời lượng — khác trang Fansipan hay Mường Hoa. Phù hợp ghép chuyến cuối tuần 2 ngày 1 đêm.',




'en' => 'Duration theme — distinct from Fansipan or Muong Hoa zone URLs. Fits a 2N1D weekend add-on.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => '2-3-ngay',
            'type' => 'duration',
            'sort' => 2,
            'minDays' => 2,
            'maxDays' => 3,
            'packageSlugs' => array(
                'sapa-2n1d-cuoi-tuan-ha-noi',
                'sapa-2n1d-lang-man',
                'sapa-3n2d-tong-quan',
                'gia-dinh-sapa-2n1d',
                'muong-hoa-trek-homestay-2n1d',
                'combo-sapa-bac-ha-3n2d',
            ),
            'name' => array(
                'vi' => 'Tour 2 – 3 ngày',




'en' => '2–3 day tours',
            ),
            'subtitle' => array(
                'vi' => 'Tổng quan, lãng mạn, gia đình, trek homestay.',




'en' => 'Overview, romance, family, homestay trek.',
            ),
            'seo_body' => array(
                'vi' => 'Sweet spot thoát phố Hà Nội — homestay bản làng + Fansipan + ẩm thực.',




'en' => 'Sweet spot for escaping Hanoi — village homestay + Fansipan + cuisine.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'homestay-trek-ban-ho',
            'slug' => '4-ngay-tro-len',
            'type' => 'duration',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 10,
            'packageSlugs' => array(
                'sapa-4n3d-kham-pha-sau',
            ),
            'name' => array(
                'vi' => 'Tour 4 ngày trở lên',




'en' => '4+ day tours',
            ),
            'subtitle' => array(
                'vi' => 'Khám phá sâu trek & ẩm thực.',




'en' => 'Deep trek & cuisine exploration.',
            ),
            'seo_body' => array(
                'vi' => 'Trekking nhiều ngày — bổ sung danh mục homestay theo thời lượng.',




'en' => 'Multi-day trekking — duration intent complementing the homestay GEO zone page.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ban-cat-cat-y-linh-ho',
            'slug' => 'nua-ngay',
            'type' => 'duration',
            'sort' => 0,
            'minDays' => 0,
            'maxDays' => 1,
            'packageSlugs' => array(
                'cat-cat-y-linh-ho-nua-ngay',
                'food-tour-am-thuc-sapa',
            ),
            'name' => array(
                'vi' => 'Tour nửa ngày / tối',




'en' => 'Half-day / evening',
            ),
            'subtitle' => array(
                'vi' => 'Cát Cát, tour ẩm thực tối.',




'en' => 'Cat Cat, evening food tour.',
            ),
            'seo_body' => array(
                'vi' => 'Ghép vào chuyến 2 ngày 1 đêm cuối tuần — khung chiều/tối.',




'en' => 'Add to a 2N1D weekend — afternoon/evening slots.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'tour-trong-ngay',
            'type' => 'theme',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'city-tour-sapa-1-ngay',
                'fansipan-cap-treo-1-ngay',
                'cat-cat-y-linh-ho-nua-ngay',
                'thac-bac-thac-tinh-yeu-1-ngay',
                'food-tour-am-thuc-sapa',
                'ha-noi-sapa-tour-ngay',
                'photo-tour-ruong-bac-thang',
            ),
            'name' => array(
                'vi' => 'Tour trong ngày',




'en' => 'Day tours',
            ),
            'subtitle' => array(
                'vi' => 'Trọn vẹn trong ngày — không qua đêm.',




'en' => 'Full day — no overnight.',
            ),
            'seo_body' => array(
                'vi' => 'Lọc theo thời lượng — khác trang danh mục theo từng vùng.',




'en' => 'Duration filter — distinct from per-zone GEO category pages.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'tour-2-ngay-1-dem',
            'type' => 'theme',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 2,
            'packageSlugs' => array(
                'sapa-2n1d-cuoi-tuan-ha-noi',
                'sapa-2n1d-lang-man',
                'muong-hoa-trek-homestay-2n1d',
                'gia-dinh-sapa-2n1d',
            ),
            'name' => array(
                'vi' => 'Tour 2 ngày 1 đêm',




'en' => '2 days 1 night',
            ),
            'subtitle' => array(
                'vi' => 'Cuối tuần ngắn, homestay hoặc resort một đêm.',




'en' => 'Short weekend, one-night homestay or resort.',
            ),
            'seo_body' => array(
                'vi' => 'Lựa chọn phổ biến 2 ngày 1 đêm — lọc theo số ngày.',




'en' => '2N1D sweet spot — hub duration filter.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'tour-3-ngay-2-dem',
            'type' => 'theme',
            'sort' => 2,
            'minDays' => 3,
            'maxDays' => 3,
            'packageSlugs' => array(
                'sapa-3n2d-tong-quan',
                'combo-sapa-bac-ha-3n2d',
            ),
            'name' => array(
                'vi' => 'Tour 3 ngày 2 đêm',




'en' => '3 days 2 nights',
            ),
            'subtitle' => array(
                'vi' => 'Khám phá vừa đủ — hai đêm nghỉ.',




'en' => 'Enough depth — two overnights.',
            ),
            'seo_body' => array(
                'vi' => 'Gói 3 ngày 2 đêm phổ biến — không trùng danh mục theo vùng.',




'en' => 'Popular 3N2D packages — not a GEO zone duplicate.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'tour-4-ngay-3-dem',
            'type' => 'theme',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 4,
            'packageSlugs' => array(
                'sapa-4n3d-kham-pha-sau',
            ),
            'name' => array(
                'vi' => 'Tour 4 ngày 3 đêm',




'en' => '4 days 3 nights',
            ),
            'subtitle' => array(
                'vi' => 'Khám phá sâu, ba đêm trải nghiệm.',




'en' => 'Deeper exploration, three nights.',
            ),
            'seo_body' => array(
                'vi' => 'Lịch 4 ngày 3 đêm — lọc theo thời lượng.',




'en' => '4N3D itineraries — hub duration filter.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'tour-tu-5-ngay',
            'type' => 'theme',
            'sort' => 4,
            'minDays' => 5,
            'maxDays' => null,
            'packageSlugs' => array(),
            'name' => array(
                'vi' => 'Tour từ 5 ngày',




'en' => '5+ day tours',
            ),
            'subtitle' => array(
                'vi' => 'Combo dài ngày, nhiều điểm đến.',




'en' => 'Extended combos and multi-destination trips.',
            ),
            'seo_body' => array(
                'vi' => 'Tour dài và combo — lọc theo thời lượng, không trùng danh mục vùng.',




'en' => 'Long tours & combos — duration insight, not a GEO page.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'cuoi-tuan-ha-noi',
            'type' => 'theme',
            'sort' => 10,
            'packageSlugs' => array(
                'sapa-2n1d-cuoi-tuan-ha-noi',
                'ha-noi-sapa-tour-ngay',
            ),
            'name' => array(
                'vi' => 'Cuối tuần từ Hà Nội',




'en' => 'Weekend from Hanoi',
            ),
            'subtitle' => array(
                'vi' => 'Tàu đêm SP/CH, 2 ngày 1 đêm và tour trong ngày.',




'en' => 'Overnight SP/CH train, 2N1D & day trips.',
            ),
            'seo_body' => array(
                'vi' => 'Trang riêng cuối tuần từ Hà Nội — không trùng danh mục thị trấn. Combo tàu đêm + Fansipan phổ biến dân văn phòng (~320km).',




'en' => 'Separate intent URL — not the town GEO zone. Overnight train + Fansipan combo is the classic Hanoi office-worker break (~320km).',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'trekking-homestay',
            'type' => 'theme',
            'sort' => 11,
            'packageSlugs' => array(
                'muong-hoa-trek-homestay-2n1d',
                'sapa-4n3d-kham-pha-sau',
            ),
            'name' => array(
                'vi' => 'Trekking & homestay',




'en' => 'Trekking & homestay',
            ),
            'subtitle' => array(
                'vi' => 'Lao Chai, Ta Van, Bản Hồ — ngủ nhà dân.',




'en' => 'Lao Chai, Ta Van, Ban Ho — family stays.',
            ),
            'seo_body' => array(
                'vi' => 'Tour phiêu lưu — HLV H\'Mông/Tày bản địa, tôn trọng phong tục bản làng.',




'en' => 'Adventure intent — local H\'Mong/Tay guides, respect village customs.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'fansipan',
            'type' => 'theme',
            'sort' => 12,
            'packageSlugs' => array(
                'fansipan-cap-treo-1-ngay',
                'sapa-2n1d-cuoi-tuan-ha-noi',
                'sapa-3n2d-tong-quan',
            ),
            'name' => array(
                'vi' => 'Tour Fansipan & cáp treo',




'en' => 'Fansipan & cable car tours',
            ),
            'subtitle' => array(
                'vi' => 'Đỉnh 3143m, tàu Mười Mây.',




'en' => '3143m summit, Muoi May train.',
            ),
            'seo_body' => array(
                'vi' => 'Trang chủ đề Fansipan — khác danh mục khu Fansipan Sun World.',




'en' => 'Theme intent — separate from the Fansipan Sun World GEO zone URL.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'am-thuc',
            'type' => 'theme',
            'sort' => 13,
            'packageSlugs' => array(
                'food-tour-am-thuc-sapa',
            ),
            'name' => array(
                'vi' => 'Tour ẩm thực',




'en' => 'Food tours',
            ),
            'subtitle' => array(
                'vi' => 'Thắng cố, cá hồi, BBQ, rượu ngô.',




'en' => 'Thang co, salmon hotpot, BBQ, corn wine.',
            ),
            'seo_body' => array(
                'vi' => 'Trang chủ đề ẩm thực — khác danh mục thị trấn Sa Pa.',




'en' => 'Theme intent — separate from the town GEO zone URL.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'cap-doi',
            'type' => 'theme',
            'sort' => 14,
            'packageSlugs' => array(
                'sapa-2n1d-lang-man',
                'photo-tour-ruong-bac-thang',
            ),
            'name' => array(
                'vi' => 'Tour cặp đôi & lãng mạn',




'en' => 'Couple & romantic',
            ),
            'subtitle' => array(
                'vi' => 'Ham Rong biển mây, Mường Hoa, photo tour.',




'en' => 'Ham Rong cloud sea, Muong Hoa, photo tour.',
            ),
            'seo_body' => array(
                'vi' => 'Tour lãng mạn — kỳ nghỉ ngắn không cần bay xa từ Hà Nội.',




'en' => 'Romance intent — mini honeymoon without flying far from Hanoi.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thi-tran-sapa',
            'slug' => 'gia-dinh',
            'type' => 'theme',
            'sort' => 15,
            'packageSlugs' => array(
                'gia-dinh-sapa-2n1d',
                'sapa-3n2d-tong-quan',
            ),
            'name' => array(
                'vi' => 'Tour gia đình',




'en' => 'Family tours',
            ),
            'subtitle' => array(
                'vi' => 'Fansipan & Mường Hoa — lịch nhẹ trẻ em.',




'en' => 'Fansipan & Muong Hoa — kid-friendly pace.',
            ),
            'seo_body' => array(
                'vi' => 'Tour gia đình — không trek nặng, ưu tiên cáp treo và tàu leo núi.',




'en' => 'Family intent — no heavy trekking; cable car and mountain train.',
            ),
            'faqs' => array(),
        ),
    ),

    'listing_faqs' => array(
        array('q' => 'Tour có bao gồm khách sạn không?', 'a' => 'Hầu hết tour chưa gồm lưu trú trong giá. Bạn có thể đặt homestay hoặc khách sạn qua :brand — tư vấn viên gợi ý theo bản (Ta Van, Lao Chai) và ngân sách.'),
        array('q' => 'Sa Pa lạnh không, cần mang gì?', 'a' => 'Nhiệt độ thường 15–22°C, mùa đông 0–8°C — áo khoát ấm, giày chống trượt nếu trek/homestay.'),
        array('q' => 'Tàu đêm hay limousine?', 'a' => 'Tàu đêm SP/CH phổ biến cuối tuần — tiết kiệm 1 đêm HN. Limousine 5–6h thẳng lên Sa Pa — tiện không muốn ngủ tàu.'),
        array('q' => 'Sương mù có hủy tour không?', 'a' => 'Fansipan/trek có thể đổi slot — tàu/limo đèo kẹt :brand hỗ trợ đổi giờ miễn phí khi an toàn.'),
    ),

    'duration_buckets' => array(
        'half-day' => 'Nửa ngày',
        '1-day' => '1 ngày',
        '2n1d' => '2 ngày 1 đêm',
        '3n2d' => '3 ngày 2 đêm',
        '4n3d' => '4 ngày 3 đêm',
        '5-plus-days' => 'Từ 5 ngày',
    ),

    'travel_style_labels' => array(
        'day-trip' => 'Tour trong ngày',
        '2n1d' => '2 ngày 1 đêm',
        '3n2d' => '3 ngày 2 đêm',
        '4n3d' => '4 ngày 3 đêm',
        '5-plus-days' => 'Từ 5 ngày',
        'cuoi-tuan-ha-noi' => 'Cuối tuần từ Hà Nội',
        'trekking-homestay' => 'Trekking & homestay',
        'fansipan' => 'Tour Fansipan & cáp treo',
        'am-thuc' => 'Tour ẩm thực',
        'cap-doi' => 'Tour cặp đôi & lãng mạn',
        'gia-dinh' => 'Tour gia đình',
    ),
);

$__servicesSeed = [
    'service_clusters' => [
        ['code' => 'train', 'nav_label' => 'Di chuyển', 'label' => 'Tàu đêm & xe Hà Nội — Sa Pa', 'icon' => 'train', 'hub_key' => 'trains_hub', 'sort' => 1],
        ['code' => 'flight', 'nav_label' => 'Máy bay & đưa đón', 'label' => 'Nội Bài (Hà Nội) & transfer Sa Pa', 'icon' => 'plane', 'hub_key' => 'flights_hub', 'sort' => 2],
        ['code' => 'stay', 'nav_label' => 'Lưu trú', 'label' => 'Homestay & khách sạn Sa Pa', 'icon' => 'building', 'hub_key' => 'stays_hub', 'sort' => 3],
        ['code' => 'experience', 'nav_label' => 'Vui chơi', 'label' => 'Fansipan, trek & tour ngày', 'icon' => 'sparkles', 'hub_key' => 'experiences_hub', 'sort' => 4],
        ['code' => 'other', 'nav_label' => 'Dịch vụ', 'label' => 'Hỗ trợ & tiện ích Sa Pa', 'icon' => 'briefcase', 'hub_key' => 'extras_hub', 'sort' => 5],
    ],
    'service_categories' => [
        ['cluster' => 'train', 'slug' => 'tau-dem-ha-noi-lao-cai', 'name' => 'Tàu đêm Hà Nội ↔ Lào Cai (SP/CH)', 'sort' => 1, 'intro' => 'Tàu đêm ~8–9h, ga Lào Cai.', 'seo_body' => 'Cửa ngõ chính từ Hà Nội — :brand giá cabin 4/6 người. Shuttle 38km lên Sa Pa đặt kèm.'],
        ['cluster' => 'train', 'slug' => 'limousine-ha-noi-sapa', 'name' => 'Limousine Hà Nội ↔ Sa Pa', 'sort' => 2, 'intro' => 'Xe 9–16 chỗ, ~320km, 5–6 giờ.', 'seo_body' => 'Thẳng lên Sa Pa — không qua tàu. Không có sân bay tại Sa Pa.'],
        ['cluster' => 'train', 'slug' => 'xe-khach-lao-cai-sapa', 'name' => 'Xe khách & shuttle Lào Cai ↔ Sa Pa', 'sort' => 3, 'intro' => '38km đèo — sau tàu đêm hoặc tự đến ga.', 'seo_body' => 'Kết nối ga Lào Cai với thị trấn Sa Pa.'],
        ['cluster' => 'train', 'slug' => 'ket-noi-bac-ha', 'name' => 'Xe Sa Pa ↔ Bắc Hà', 'sort' => 4, 'intro' => 'Chợ phiên Chủ nhật — ~2h.', 'seo_body' => 'Combo chợ Bắc Hà + Sa Pa qua :brand.'],
        ['cluster' => 'train', 'slug' => 'xe-rieng-charter', 'name' => 'Xe riêng & charter', 'sort' => 5, 'intro' => '4–16 chỗ theo ngày.', 'seo_body' => 'Đoàn, gia đình — lịch linh hoạt trên đèo.'],
        ['cluster' => 'train', 'slug' => 'xe-don-ga-lao-cai', 'name' => 'Đón ga Lào Cai & nội bộ Sa Pa', 'sort' => 6, 'intro' => 'Đón tàu đêm → shuttle lên Sa Pa.', 'seo_body' => 'Tiện khi mang vali hoặc sương mù dày.'],
        ['cluster' => 'flight', 'slug' => 'noi-bai-transfer', 'name' => 'Transfer Nội Bài (Hà Nội) ↔ Sa Pa', 'sort' => 1, 'intro' => 'Bay HAN + xe ~5–6h lên Sa Pa.', 'seo_body' => 'Khách quốc tế/nội địa bay HN — gộp transfer qua :brand.'],
        ['cluster' => 'flight', 'slug' => 'dua-don-noi-bai', 'name' => 'Đưa đón sân bay Nội Bài đón tận nơi', 'sort' => 2, 'intro' => 'Xe riêng Hà Nội ↔ Hà Nội/Sa Pa.', 'seo_body' => ':brand canh chuyến bay.'],
        ['cluster' => 'flight', 'slug' => 'combo-bay-tau-sapa', 'name' => 'Combo vé bay Hà Nội + tàu/transfer Sa Pa', 'sort' => 3, 'intro' => 'Một báo giá bay + xe/tàu lên núi.', 'seo_body' => 'Tiện khách lần đầu — không tự ghép.'],
        ['cluster' => 'stay', 'slug' => 'homestay-ban-lang', 'name' => 'Homestay bản làng (Ta Van, Lao Chai)', 'sort' => 1, 'intro' => 'Ngủ nhà dân Tày, Giáy, H\'Mông.', 'seo_body' => 'Catalogue lưu trú có thể bổ sung từ tool cào — liên hệ :brand tư vấn theo bản.'],
        ['cluster' => 'stay', 'slug' => 'khach-san-thi-tran', 'name' => 'Khách sạn & resort thị trấn', 'sort' => 2, 'intro' => 'Gần nhà thờ đá & chợ đêm.', 'seo_body' => 'Phù hợp cuối tuần không trek — đi bộ phố núi.'],
        ['cluster' => 'stay', 'slug' => 'resort-view-ruong', 'name' => 'Resort view ruộng bậc thang', 'sort' => 3, 'intro' => 'View Mường Hoa — nghỉ lãng mạn.', 'seo_body' => 'Mùa vàng Sep–Oct — đặt sớm cuối tuần.'],
        ['cluster' => 'experience', 'slug' => 'fansipan-cap-treo', 'name' => 'Fansipan & cáp treo Sun World', 'sort' => 1, 'intro' => 'Vé cáp treo & tàu Mười Mây.', 'seo_body' => 'Signature Sa Pa — đặt sớm cuối tuần.'],
        ['cluster' => 'experience', 'slug' => 'ham-rong', 'name' => 'Ham Rong biển mây', 'sort' => 2, 'intro' => 'Vé & tour nửa ngày.', 'seo_body' => 'View thị trấn & ruộng — sương sớm đẹp nhất.'],
        ['cluster' => 'experience', 'slug' => 'cat-cat-muong-hoa', 'name' => 'Cát Cát & Mường Hoa', 'sort' => 3, 'intro' => 'Làng H\'Mông, tàu hoả leo núi.', 'seo_body' => 'Khác zone Fansipan — URL riêng chống cannibalization.'],
        ['cluster' => 'experience', 'slug' => 'trek-homestay', 'name' => 'Trek & homestay Mường Hoa', 'sort' => 4, 'intro' => 'HLV & homestay Ta Van.', 'seo_body' => 'Lao Chai—Ta Van—Bản Hồ — HLV bản địa.'],
        ['cluster' => 'experience', 'slug' => 'bac-ha-cho-phien', 'name' => 'Bắc Hà chợ phiên', 'sort' => 5, 'intro' => 'Tour Chủ nhật & xe nối.', 'seo_body' => 'Chỉ Chủ nhật — lên lịch theo tuần.'],
        ['cluster' => 'experience', 'slug' => 'thac-bac-tinh-yeu', 'name' => 'Thác Bạc & Thác Tình Yêu', 'sort' => 6, 'intro' => 'Trek nhẹ rừng thông.', 'seo_body' => 'Ít đông hơn Fansipan — ảnh sương đẹp.'],
        ['cluster' => 'experience', 'slug' => 'am-thuc-tay-bac', 'name' => 'Ẩm thực Tây Bắc', 'sort' => 7, 'intro' => 'Thắng cố, cá hồi, rượu ngô.', 'seo_body' => 'Food tour tối — khác tour city.'],
        ['cluster' => 'experience', 'slug' => 'van-hoa-dan-toc', 'name' => 'Văn hoá dân tộc & làng nghề', 'sort' => 8, 'intro' => 'H\'Mông, Dao đỏ, Tày, Giáy.', 'seo_body' => 'Trang phục, dệt thổ công — tôn trọng bản làng.'],
        ['cluster' => 'other', 'slug' => 'huong-dan-rieng', 'name' => 'HDV riêng & photo tour', 'sort' => 1, 'intro' => 'Guide tiếng Anh, bậc thang.', 'seo_body' => 'Pre-wedding & couple photo trên ruộng.'],
        ['cluster' => 'other', 'slug' => 'porter-trek', 'name' => 'Porter & hỗ trợ trek', 'sort' => 2, 'intro' => 'Vác hành lý homestay trek.', 'seo_body' => 'Giảm tải cho khách trek 2–3 ngày.'],
        ['cluster' => 'other', 'slug' => 'xe-tien-ich', 'name' => 'Xe có lái, thuê xe máy', 'sort' => 3, 'intro' => 'Xe máy, ô tô có tài trên đèo.', 'seo_body' => 'Cẩn thận sương mù — khuyên xe có lái.'],
        ['cluster' => 'other', 'slug' => 'ho-tro-dac-biet', 'name' => 'Hotline 24/7 & đổi lịch sương mù', 'sort' => 4, 'intro' => 'Đổi tàu/tour khi đèo kẹt.', 'seo_body' => 'Miễn phí khách đặt qua :brand.'],
    ],
    'services' => [
        ['code' => 'train-sp-hn-lc', 'cluster' => 'train', 'category_slug' => 'tau-dem-ha-noi-lao-cai', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Tàu đêm SP Hà Nội → Lào Cai (cabin 4 người)', 'slug' => 'tau-dem-sp-ha-noi-lao-cai', 'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 1560, 'is_featured' => true, 'is_hot_deal' => true, 'location_label' => 'Hà Nội → Lào Cai', 'summary' => '~8h, tối T6 phổ biến — cabin 4 người.', 'highlights' => ['Tàu SP nhanh', 'Cabin 4/6', 'Cuối tuần đông khách'], 'inclusions' => ['Vé tàu một chiều'], 'exclusions' => ['Shuttle Sa Pa'], 'notes' => ['Đặt trước T6–CN.'], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Lào Cai', 'duration_hours' => 8, 'vehicle_type' => 'tàu SP']],
        ['code' => 'train-ch-hn-lc', 'cluster' => 'train', 'category_slug' => 'tau-dem-ha-noi-lao-cai', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Tàu đêm CH Hà Nội → Lào Cai (giá rẻ)', 'slug' => 'tau-dem-ch-ha-noi-lao-cai', 'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 890, 'is_featured' => true, 'location_label' => 'Hà Nội → Lào Cai', 'summary' => '~9h — tiết kiệm hơn SP.', 'highlights' => ['Giá rẻ', 'Cabin 6'], 'inclusions' => ['Vé tàu một chiều'], 'exclusions' => ['Shuttle Sa Pa'], 'notes' => [], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Lào Cai', 'duration_hours' => 9, 'vehicle_type' => 'tàu CH']],
        ['code' => 'train-sp-round', 'cluster' => 'train', 'category_slug' => 'tau-dem-ha-noi-lao-cai', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Tàu đêm khứ hồi Hà Nội ↔ Lào Cai (SP)', 'slug' => 'tau-dem-khu-hoi-sp-ha-noi-lao-cai', 'price_from' => 1600000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 720, 'is_featured' => true, 'location_label' => 'Hà Nội ↔ Lào Cai', 'summary' => 'Cuối tuần 2 ngày 1 đêm — tiết kiệm hơn 2 chiều lẻ.', 'highlights' => ['Khứ hồi SP', 'Giữ chỗ CN về'], 'inclusions' => ['2 chiều tàu SP'], 'exclusions' => ['Shuttle'], 'notes' => [], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Lào Cai', 'vehicle_type' => 'tàu SP']],
        ['code' => 'train-limo-hn-sp', 'cluster' => 'train', 'category_slug' => 'limousine-ha-noi-sapa', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Limousine Hà Nội → Sa Pa (một chiều)', 'slug' => 'limousine-ha-noi-sapa-mot-chieu', 'price_from' => 320000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 1120, 'is_featured' => true, 'location_label' => 'Hà Nội → Sa Pa', 'summary' => '5–6 giờ thẳng lên Sa Pa.', 'highlights' => ['9–16 chỗ', 'Không qua tàu'], 'inclusions' => ['Limousine một chiều'], 'exclusions' => ['Đón xa trung tâm'], 'notes' => [], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Sa Pa', 'duration_hours' => 5.5, 'vehicle_type' => 'limousine']],
        ['code' => 'train-bus-lc-sp', 'cluster' => 'train', 'category_slug' => 'xe-khach-lao-cai-sapa', 'zone_slug' => 'lao-cai-cua-ngo', 'title' => 'Shuttle Lào Cai → Sa Pa (sau tàu đêm)', 'slug' => 'shuttle-lao-cai-sapa', 'price_from' => 80000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 2340, 'is_featured' => true, 'location_label' => 'Lào Cai → Sa Pa', 'summary' => '38km đèo — đón ga 6h–7h sáng.', 'highlights' => ['Ghép sau tàu', 'Bảng tên'], 'inclusions' => ['Xe một chiều'], 'exclusions' => [], 'notes' => ['Sương mù có thể trễ 30–60 phút.'], 'attrs' => ['from' => 'Lào Cai', 'to' => 'Sa Pa', 'duration_hours' => 1, 'vehicle_type' => 'shuttle']],
        ['code' => 'train-bac-ha', 'cluster' => 'train', 'category_slug' => 'ket-noi-bac-ha', 'zone_slug' => 'ket-hop-bac-ha', 'title' => 'Xe Sa Pa ↔ Bắc Hà (chợ Chủ nhật)', 'slug' => 'xe-sapa-bac-ha-cho-phien', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 156, 'is_featured' => true, 'location_label' => 'Sa Pa ↔ Bắc Hà', 'summary' => '~2h — combo chợ phiên.', 'highlights' => ['Chủ nhật', 'Combo tour'], 'inclusions' => ['Xe khứ hồi'], 'exclusions' => ['HDV'], 'notes' => ['Chỉ Chủ nhật.'], 'attrs' => ['from' => 'Sa Pa', 'to' => 'Bắc Hà', 'duration_hours' => 2]],
        ['code' => 'train-charter-hn-sp', 'cluster' => 'train', 'category_slug' => 'xe-rieng-charter', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Xe riêng Hà Nội — Sa Pa (4–16 chỗ)', 'slug' => 'xe-rieng-ha-noi-sapa', 'price_from' => 3500000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 89, 'is_featured' => true, 'location_label' => 'Hà Nội → Sa Pa', 'summary' => 'Lịch linh hoạt — dừng chân đèo tự do.', 'highlights' => ['Xe riêng', 'Gia đình'], 'inclusions' => ['Xe + tài xế'], 'exclusions' => ['Cầu đường'], 'notes' => [], 'attrs' => ['vehicle_type' => '4–16 chỗ']],
        ['code' => 'train-pickup-ga', 'cluster' => 'train', 'category_slug' => 'xe-don-ga-lao-cai', 'zone_slug' => 'lao-cai-cua-ngo', 'title' => 'Đón ga Lào Cai + shuttle Sa Pa', 'slug' => 'don-ga-lao-cai-shuttle-sapa', 'price_from' => 150000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 890, 'is_featured' => true, 'location_label' => 'Ga → Sa Pa', 'summary' => 'Combo đón tàu + lên thị trấn.', 'highlights' => ['Bảng tên', 'Ghép tàu SP/CH'], 'inclusions' => ['Đón ga', 'Shuttle'], 'exclusions' => [], 'notes' => [], 'attrs' => ['service_type' => 'station_pickup']],
        ['code' => 'flight-han-sp-transfer', 'cluster' => 'flight', 'category_slug' => 'noi-bai-transfer', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Transfer Nội Bài (Hà Nội) → Sa Pa', 'slug' => 'transfer-noi-bai-sapa', 'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 312, 'is_featured' => true, 'location_label' => 'Hà Nội → Sa Pa', 'summary' => 'Bay Hà Nội + xe ~5–6h lên Sa Pa — theo dõi flight.', 'highlights' => ['Door-to-hotel'], 'inclusions' => ['Xe 4–7 chỗ'], 'exclusions' => ['Vé bay'], 'notes' => ['Gửi số hiệu chuyến bay.'], 'attrs' => ['from' => 'HAN', 'to' => 'Sa Pa', 'duration_hours' => 6]],
        ['code' => 'flight-han-door', 'cluster' => 'flight', 'category_slug' => 'dua-don-noi-bai', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Đưa đón Nội Bài ↔ Hà Nội nội thành', 'slug' => 'dua-don-noi-bai-ha-noi', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Hà Nội ↔ Hà Nội', 'summary' => 'Xe riêng sảnh bay — ghép tàu/limo Sa Pa.', 'highlights' => ['Theo dõi flight'], 'inclusions' => ['Xe 4 chỗ'], 'exclusions' => ['Lên Sa Pa'], 'notes' => [], 'attrs' => ['from' => 'HAN', 'duration_hours' => 0.75]],
        ['code' => 'flight-combo-han-sp', 'cluster' => 'flight', 'category_slug' => 'combo-bay-tau-sapa', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Combo vé bay Hà Nội + transfer Sa Pa', 'slug' => 'combo-ve-bay-han-transfer-sapa', 'price_from' => 1450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 98, 'is_featured' => true, 'is_hot_deal' => true, 'location_label' => 'Hà Nội → Sa Pa', 'summary' => 'Một báo giá bay + xe lên núi.', 'highlights' => ['Một đầu mối'], 'inclusions' => ['Vé bay', 'Transfer'], 'exclusions' => ['Homestay', 'Tour'], 'notes' => ['Giá theo ngày bay.'], 'attrs' => ['from' => 'HAN', 'to' => 'Sa Pa']],
        ['code' => 'flight-han-advice', 'cluster' => 'flight', 'category_slug' => 'combo-bay-tau-sapa', 'zone_slug' => 'ket-hop-ha-noi', 'title' => 'Tư vấn bay quốc tế HAN + Sa Pa', 'slug' => 'tu-van-bay-quoc-te-han-sapa', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 56, 'is_featured' => false, 'location_label' => 'Intl → Sa Pa', 'summary' => 'Khách bay Hà Nội — ghép transfer & tour.', 'highlights' => ['Miễn phí tư vấn'], 'inclusions' => ['Tư vấn'], 'exclusions' => ['Vé', 'Xe'], 'notes' => [], 'attrs' => ['price_label' => 'Liên hệ']],
        ['code' => 'exp-fansipan', 'cluster' => 'experience', 'category_slug' => 'fansipan-cap-treo', 'zone_slug' => 'fansipan-sun-world', 'title' => 'Vé cáp treo Fansipan Sun World', 'slug' => 've-cap-treo-fansipan-sun-world', 'price_from' => 750000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 1240, 'is_featured' => true, 'location_label' => 'Fansipan', 'summary' => 'E-ticket 3 đoạn — đỉnh 3143m.', 'highlights' => ['Cáp treo combo', 'E-ticket'], 'inclusions' => ['Vé cáp treo'], 'exclusions' => ['Tàu Mười Mây', 'Xe đón'], 'notes' => ['Đóng khi bão — đổi slot miễn phí.'], 'attrs' => ['activity' => 'cable_car']],
        ['code' => 'exp-ham-rong', 'cluster' => 'experience', 'category_slug' => 'ham-rong', 'zone_slug' => 'thi-tran-sapa', 'title' => 'Ham Rong — vé & tour biển mây sáng sớm', 'slug' => 'ham-rong-ve-tour-bien-may', 'price_from' => 120000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 678, 'is_featured' => true, 'location_label' => 'Ham Rong', 'summary' => '06:00 — sương & view thị trấn.', 'highlights' => ['Vé Ham Rong', 'Xe ghép'], 'inclusions' => ['Vé'], 'exclusions' => ['HDV riêng'], 'notes' => [], 'attrs' => ['duration_hours' => 2.5, 'activity' => 'ham_rong']],
        ['code' => 'exp-cat-cat', 'cluster' => 'experience', 'category_slug' => 'cat-cat-muong-hoa', 'zone_slug' => 'ban-cat-cat-y-linh-ho', 'title' => 'Bản Cát Cát — vé & HDV nửa ngày', 'slug' => 'ban-cat-cat-ve-hdv-nua-ngay', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 456, 'is_featured' => true, 'location_label' => 'Cát Cát', 'summary' => 'Làng H\'Mông & thác Cát Cát.', 'highlights' => ['Vé', 'HDV', 'Trek nhẹ'], 'inclusions' => ['Vé', 'HDV'], 'exclusions' => ['Trưa'], 'notes' => [], 'attrs' => ['duration_hours' => 3]],
        ['code' => 'exp-muong-hoa-trek', 'cluster' => 'experience', 'category_slug' => 'trek-homestay', 'zone_slug' => 'thung-lung-muong-hoa', 'title' => 'Trek Mường Hoa — HLV & homestay Ta Van', 'slug' => 'trek-muong-hoa-hlv-homestay', 'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Mường Hoa', 'summary' => '1 đêm homestay — Lao Chai, Ta Van.', 'highlights' => ['HLV H\'Mông', 'Homestay', 'Bữa tối gia đình'], 'inclusions' => ['HLV', 'Homestay', 'Bữa tối'], 'exclusions' => ['Porter'], 'notes' => [], 'attrs' => ['duration_hours' => 24, 'activity' => 'trekking']],
        ['code' => 'exp-bac-ha', 'cluster' => 'experience', 'category_slug' => 'bac-ha-cho-phien', 'zone_slug' => 'ket-hop-bac-ha', 'title' => 'Chợ phiên Bắc Hà — tour Chủ nhật', 'slug' => 'cho-phien-bac-ha-tour-chu-nhat', 'price_from' => 650000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 178, 'is_featured' => true, 'location_label' => 'Bắc Hà', 'summary' => 'Flower H\'Mong — chợ độc đáo nhất Tây Bắc.', 'highlights' => ['Chủ nhật', 'HDV', 'Xe'], 'inclusions' => ['Xe', 'HDV'], 'exclusions' => ['Ăn'], 'notes' => ['Chỉ Chủ nhật.'], 'attrs' => ['duration_hours' => 8]],
        ['code' => 'exp-thac-bac', 'cluster' => 'experience', 'category_slug' => 'thac-bac-tinh-yeu', 'zone_slug' => 'thac-bac-thac-tinh-yeu', 'title' => 'Thác Bạc & Thác Tình Yêu — tour nửa ngày', 'slug' => 'thac-bac-thac-tinh-yeu-tour-nua-ngay', 'price_from' => 380000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 198, 'is_featured' => true, 'location_label' => 'Thác Bạc', 'summary' => 'Trek rừng thông — 2 thác trong sương.', 'highlights' => ['HDV', 'Trek nhẹ'], 'inclusions' => ['Xe', 'HDV'], 'exclusions' => ['Trưa'], 'notes' => [], 'attrs' => ['duration_hours' => 4]],
        ['code' => 'exp-food-tour', 'cluster' => 'experience', 'category_slug' => 'am-thuc-tay-bac', 'zone_slug' => 'thi-tran-sapa', 'title' => 'Tour ẩm thực Sa Pa — vé lẻ', 'slug' => 'food-tour-sapa-ve-le', 'price_from' => 520000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 267, 'is_featured' => true, 'location_label' => 'Sa Pa', 'summary' => '18h–22h — thắng cố, cá hồi, rượu ngô.', 'highlights' => ['7–8 món'], 'inclusions' => ['HDV', 'Tasting'], 'exclusions' => ['Rượu thêm'], 'notes' => [], 'attrs' => ['duration_hours' => 3, 'activity' => 'food_tour']],
        ['code' => 'exp-ethnic-culture', 'cluster' => 'experience', 'category_slug' => 'van-hoa-dan-toc', 'zone_slug' => 'ban-cat-cat-y-linh-ho', 'title' => 'Tour văn hoá dân tộc — trang phục & dệt', 'slug' => 'tour-van-hoa-dan-toc-trang-phuc', 'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 134, 'is_featured' => true, 'location_label' => 'Bản làng', 'summary' => 'H\'Mông, Dao đỏ — học dệt & múa.', 'highlights' => ['HDV dân tộc', 'Workshop'], 'inclusions' => ['HDV', 'Workshop'], 'exclusions' => ['Trang phục mua'], 'notes' => [], 'attrs' => ['duration_hours' => 3, 'activity' => 'culture']],
        ['code' => 'other-guide-en', 'cluster' => 'other', 'category_slug' => 'huong-dan-rieng', 'zone_slug' => 'thi-tran-sapa', 'title' => 'HDV tiếng Anh riêng (8h)', 'slug' => 'hdv-tieng-anh-rieng-sapa', 'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 112, 'is_featured' => true, 'location_label' => 'Sa Pa', 'summary' => 'khách lẻ quốc tế — trek/Fansipan/city.', 'highlights' => ['Bản địa Lào Cai'], 'inclusions' => ['HDV 8h'], 'exclusions' => ['Vé', 'Xe'], 'notes' => [], 'attrs' => ['service_type' => 'guide', 'languages' => ['en']]],
        ['code' => 'other-porter', 'cluster' => 'other', 'category_slug' => 'porter-trek', 'zone_slug' => 'homestay-trek-ban-ho', 'title' => 'Porter trek homestay (1 ngày)', 'slug' => 'porter-trek-homestay-1-ngay', 'price_from' => 350000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 89, 'is_featured' => true, 'location_label' => 'Mường Hoa', 'summary' => 'Vác tối đa 15kg — trek 2–3 ngày.', 'highlights' => ['Địa phương'], 'inclusions' => ['Porter 8h'], 'exclusions' => ['Tip'], 'notes' => [], 'attrs' => ['service_type' => 'porter']],
        ['code' => 'other-motorbike', 'cluster' => 'other', 'category_slug' => 'xe-tien-ich', 'zone_slug' => 'thi-tran-sapa', 'title' => 'Thuê xe máy Sa Pa', 'slug' => 'thue-xe-may-sapa', 'price_from' => 120000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 312, 'is_featured' => true, 'location_label' => 'Thị trấn', 'summary' => 'Tự khám phá — cẩn thận sương đèo.', 'highlights' => ['Giao khách sạn'], 'inclusions' => ['Xe/ngày', 'Mũ'], 'exclusions' => ['Xăng', 'CCCD cọc'], 'notes' => ['Không khuyến khích người mới lái đèo.'], 'attrs' => ['service_type' => 'vehicle_rental']],
        ['code' => 'other-emergency', 'cluster' => 'other', 'category_slug' => 'ho-tro-dac-biet', 'zone_slug' => 'thi-tran-sapa', 'title' => 'Hotline 24/7 — sương mù & đổi lịch', 'slug' => 'hotline-ho-tro-khan-cap-sapa', 'price_from' => 0, 'currency' => 'VND', 'rating' => 5.0, 'review_count' => 56, 'is_featured' => true, 'location_label' => '24/7', 'summary' => 'Miễn phí khách :brand — đèo kẹt, đổi tàu.', 'highlights' => ['VI/EN'], 'inclusions' => ['Hotline'], 'exclusions' => [], 'notes' => ['Nguy hiểm — gọi 115.'], 'attrs' => ['service_type' => 'medical_assistance', 'price_label' => 'Liên hệ']],
        ['code' => 'other-costume-photo', 'cluster' => 'other', 'category_slug' => 'huong-dan-rieng', 'zone_slug' => 'thung-lung-muong-hoa', 'title' => 'Thuê trang phục dân tộc & chụp ảnh', 'slug' => 'thue-trang-phuc-dan-toc-chup-anh', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 198, 'is_featured' => true, 'location_label' => 'Mường Hoa', 'summary' => 'H\'Mông, Dao đỏ — góc ruộng bậc thang.', 'highlights' => ['Trang phục', 'Gợi ý góc'], 'inclusions' => ['Trang phục 2h'], 'exclusions' => ['Photographer'], 'notes' => ['Tôn trọng văn hoá — không mặc vào nơi linh thiêng.'], 'attrs' => ['service_type' => 'costume_rental']],
        ['code' => 'other-team-building', 'cluster' => 'other', 'category_slug' => 'ho-tro-dac-biet', 'zone_slug' => 'thi-tran-sapa', 'title' => 'Team-building Sa Pa — facilitator + game kit', 'slug' => 'team-building-sapa-facilitator', 'price_from' => 9500000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 28, 'is_featured' => true, 'location_label' => 'Sa Pa', 'summary' => '15–40 pax — MC, game ruộng, gala cơ bản.', 'highlights' => ['MICE nhỏ'], 'inclusions' => ['Facilitator 4h', 'Game kit'], 'exclusions' => ['Homestay', 'Tàu HN'], 'notes' => ['Báo trước 7 ngày.'], 'attrs' => ['service_type' => 'team_building']],
        ['code' => 'exp-muong-hoa-train', 'cluster' => 'experience', 'category_slug' => 'cat-cat-muong-hoa', 'zone_slug' => 'thung-lung-muong-hoa', 'title' => 'Tàu hoả leo núi Mường Hoa — vé', 'slug' => 'tau-hoa-muong-hoa-ve', 'price_from' => 200000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 534, 'is_featured' => true, 'location_label' => 'Mường Hoa', 'summary' => 'E-ticket — view ruộng bậc thang.', 'highlights' => ['Tàu leo núi', 'E-ticket'], 'inclusions' => ['Vé tàu'], 'exclusions' => ['Xe đón'], 'notes' => [], 'attrs' => ['activity' => 'mountain_train']],
    ],
    'service_listing_faqs' => [
        ['q' => 'Sa Pa có sân bay không?', 'a' => 'Không — bay Nội Bài (Hà Nội) + transfer ~5–6h, hoặc tàu đêm SP/CH + shuttle từ Lào Cai (~320km từ Hà Nội).'],
        ['q' => 'Giá tàu đêm có cố định không?', 'a' => 'Mức tham khảo 550k–850k/chiều cabin — chốt theo toa, ngày đi và loại tàu SP/CH. T6–CN và mùa vàng cao hơn.'],
        ['q' => 'Đặt homestay qua :brand thế nào?', 'a' => 'Xem mục Lưu trú (catalogue có thể bổ sung từ tool cào) hoặc liên hệ tư vấn homestay theo bản — Ta Van, Lao Chai, Bản Hồ.'],
        ['q' => 'Gộp tàu HN + tour + Fansipan một đơn?', 'a' => 'Có — một báo giá minh bạch, một đầu mối chăm sóc.'],
        ['q' => 'Sương mù có hủy tour không?', 'a' => 'Fansipan/trek có thể đổi slot — tàu đèo kẹt :brand hỗ trợ đổi giờ miễn phí khi an toàn.'],
    ],
];
$__companySeed = [
    'name' => 'Hi Sa Pa',
    'legal_name' => 'Công ty TNHH Du lịch Hi Sa Pa',
    'tagline' => 'Ruộng bậc thang, Fansipan & thoát urban cuối tuần',
    'slogan' => '"Khám phá Sa Pa như người bản địa"',
    'license_number' => '0052/2024/TCDL-GPLHQT',
    'contact' => [
        'email' => 'hello@hisapa.dev',
        'phone' => '+84 214 388 8888',
        'whatsapp' => '+84 912 999 888',
        'zalo' => '+84 214 388 8888',
        'hotline_label' => 'Hotline',
    ],
    'address' => [
        'street' => 'Thị trấn Sa Pa, huyện Sa Pa',
        'locality' => 'Sa Pa, Lào Cai',
        'region' => 'Lào Cai',
        'postal' => '330000',
        'country' => 'VN',
    ],
    'social' => [
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/hisapa'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'play', 'url' => 'https://www.youtube.com/@hisapa'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'photo', 'url' => 'https://www.instagram.com/hisapa'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'share', 'url' => 'https://www.tiktok.com/@hisapa'],
    ],
    'schema' => [
        'available_language' => ['Vietnamese', 'English', 'Korean'],
        'contact_type' => 'customer service',
        'logo' => null,
    ],
    'footer' => [
        'copyright' => '© :year Hi Sa Pa. Giấy phép kinh doanh dịch vụ lữ hành số :license.',
        'show_dmca_badge' => true,
    ],
];

return array_merge(
    $__hisapaSeed,
    $__servicesSeed,
    ['company' => $__companySeed],
    ['customize_form' => [
        'destinations_label' => [
            'vi' => 'Bạn muốn khám phá khu vực nào ở Sa Pa?',




'en' => 'Which areas of Sapa would you like to explore?',
        ],
        'accommodation_label' => [
            'vi' => 'Bạn thích loại lưu trú nào?',




'en' => 'What kind of stay do you prefer?',
        ],
        'budget_note' => [
            'vi' => 'Ngân sách dự kiến (chưa gồm vé tàu/xe Hà Nội—Sa Pa)',




'en' => 'Estimated budget (excluding Hanoi—Sapa train/coach tickets)',
        ],
        'accommodation' => [
            'vi' => [
                'Thị trấn Sa Pa / Nhà thờ đá',
                'Homestay bản làng (Ta Van, Lao Chai)',
                'Resort view ruộng bậc thang',
                'Khách sạn trung tâm chợ đêm',
                'Nhờ tư vấn giúp tôi',
            ],




'en' => [
                'Sapa town / Stone Church',
                'Village homestay (Ta Van, Lao Chai)',
                'Terrace-view resort',
                'Town centre near night market',
                'Please advise me',
            ],
        ],
    ]],
    ['nav' => [
        'about_group' => ['vi' => 'Về Hi Sa Pa',



'en' => 'About Hi Sa Pa'],
        'tours' => ['label' => ['vi' => 'Tour',



'en' => 'Tours']],
        'cruise' => [
            'label' => ['vi' => 'Fansipan & cáp treo',



'en' => 'Fansipan & cable car'],
            'all_label' => ['vi' => 'Tất cả Fansipan & cáp treo',



'en' => 'All Fansipan & cable car'],
            'all_meta' => ['vi' => 'Cáp treo Fansipan & tàu Mười Mây Sun World',



'en' => 'Fansipan cable car & Muoi May mountain train'],
            'search_hint' => ['vi' => 'Tour, Fansipan, trek, cẩm nang…',



'en' => 'Tours, Fansipan, treks, guides…'],
            'search_placeholder' => ['vi' => 'Tìm tour, dịch vụ, bài viết…',



'en' => 'Search tours, services, articles…'],
            'hub_title' => ['vi' => 'Fansipan & cáp treo',



'en' => 'Fansipan & cable car'],
            'hub_subtitle' => ['vi' => 'Cáp treo ba đoạn, tàu Mười Mây và chinh phục đỉnh 3143m.',



'en' => 'Three-section cable car, Muoi May train and the 3143m summit.'],
        ],
    ]],
    ['listing_hubs' => [
        'tours_hub' => [
            'vi' => ['seo_body' => 'Tour :brand gom cuối tuần HN, Fansipan, trek homestay và combo Bắc Hà — thiết kế bởi chuyên gia bản địa Lào Cai.'],




'en' => ['seo_body' => ':brand tours cover Hanoi weekends, Fansipan, homestay treks and Bac Ha combos — designed by local Lao Cai experts.'],
        ],
        'cruises_hub' => [
            'vi' => ['seo_body' => 'Cáp treo Fansipan & tàu Mười Mây Sun World — trải nghiệm núi đặc trưng từ :brand.'],




'en' => ['seo_body' => 'Fansipan cable car and Muoi May train at Sun World — signature mountain experiences from :brand.'],
        ],
        'trains_hub' => [
            'vi' => ['seo_body' => 'Tàu đêm SP/CH Hà Nội — Lào Cai, limousine và shuttle Sa Pa qua :brand — e-ticket, đổi ngày linh hoạt.'],




'en' => ['seo_body' => 'Hanoi—Lao Cai overnight SP/CH trains, limousines and Sapa shuttles via :brand — e-tickets and flexible changes.'],
        ],
        'flights_hub' => [
            'vi' => ['seo_body' => 'Vé bay Nội Bài (Hà Nội) và đưa đón Sa Pa kết nối tour :brand.'],




'en' => ['seo_body' => 'Noi Bai (HAN) flights and Sapa transfers aligned with your :brand itinerary.'],
        ],
        'stays_hub' => [
            'vi' => ['seo_body' => ':brand tổng hợp lưu trú Sa Pa theo khu vực — homestay bản làng, khách sạn thị trấn và resort view ruộng bậc thang. Catalogue có thể bổ sung từ tool cào; lọc theo ngân sách, phong cách và gần các điểm trong hành trình của bạn.'],




'en' => ['seo_body' => ':brand brings together Sapa stays by area — village homestays, town hotels and terrace-view resorts. Catalogue may be enriched via crawl tool; filter by budget, style and proximity to your itinerary.'],
        ],
        'experiences_hub' => [
            'vi' => ['seo_body' => 'Trải nghiệm Sa Pa: Fansipan, Ham Rong, Cát Cát, trek homestay, Bắc Hà, tour ẩm thực — đặt lẻ hoặc gộp tour qua :brand.'],




'en' => ['seo_body' => 'Sapa experiences: Fansipan, Ham Rong, Cat Cat, homestay treks, Bac Ha, food tours — à la carte or bundled via :brand.'],
        ],
        'extras_hub' => [
            'vi' => ['seo_body' => 'HDV riêng, porter trek, trang phục dân tộc và hỗ trợ 24/7 trên đèo Sa Pa cùng :brand.'],




'en' => ['seo_body' => 'Private guides, trek porters, ethnic costumes and 24/7 Sapa pass support with :brand.'],
        ],
    ]],
);
