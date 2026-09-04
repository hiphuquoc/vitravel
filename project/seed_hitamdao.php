<?php

/**
 * ============================================================================
 * DỮ LIỆU Hi Tam Đảo — profile `hitamdao` (project:seed / migrate --seed)
 * ============================================================================
 *
 * Trang thông tin du lịch + dịch vụ + kết nối TOÀN DIỆN Tam Đảo (Vĩnh Phúc).
 * Một điểm đến duy nhất — "zones" thay "countries". Cụm "train" = xe khách,
 * limousine Hà Nội — Tam Đảo (~80km, 2–2.5h). Cụm "flight" = bay Nội Bài (HAN)
 * + xe nối (không có sân bay tại Tam Đảo). Thuyền = suối cá, hồ picnic — không
 * phải du thuyền biển.
 *
 * Lưu trú: không seed catalogue trong file này (data từ tool cào riêng); giữ menu + stays_hub.
 *
 * ---------------------------------------------------------------------------
 * TAXONOMY TOUR (bắt buộc đồng bộ mọi hub — xem project/README.md §3):
 * - DANH MỤC = tour_categories type=region → khu vực / combo (zone hoặc ket-hop-*).
 *   Không chia theo số ngày; không đặt tên "Tour 1 ngày / 2–3 ngày…".
 * - CHỦ ĐỀ   = tour_categories type=theme  → gắn HUB zone:
 *   (A) thời lượng: tour-trong-ngay | 2N1D | 3N2D | 4N3D | từ 5 ngày
 *   (B) tính chất: gia đình, trăng mật, teambuilding, cuối tuần, insight địa phương
 *   — KHÔNG clone tên zone GEO.
 * - Package ↔ category/theme: nhiều–nhiều qua packageSlugs[] (+ minDays cho theme thời lượng).
 * - travel_styles: mã filter khớp chủ đề; không tạo trang SEO riêng.
 * - Cấm type=duration trên hub (trùng chủ đề thời lượng).
 * ---------------------------------------------------------------------------
 * ZONES / ĐIỂM ĐẾN (→ CMS countries; SEO type=country KHÔNG có parent; URL /{slug}):
 * Hub (đứng đầu — themes gắn đây):
 *   trung-tam-thi-tran — nhà thờ đá, chợ đêm, phố núi di sản Pháp
 * GEO (search/book riêng):
 *   thac-bac-thac-dai — thác giữa rừng thông, chụp sương
 *   cau-may-sky-walk — cầu treo mây, Sky Walk check-in
 *   vqg-rung-thong — VQG Tam Đảo, trekking, chim hoang dã
 *   khu-resort-dinh-cao — resort/villa trên đỉnh, MICE nhỏ
 *   suoi-ca-am-thuc — suối cá, gà đồi, đặc sản địa phương
 * Combo only (ket-hop-* — KHÔNG gắn stay vật lý):
 *   ket-hop-ha-noi — limo ~80km, tour ngày / cuối tuần từ HN
 *   ket-hop-ninh-binh — Tràng An + đồi mát
 * Services: stay/experience/other → zone_slug hub|GEO; train/flight → KHÔNG zone_slug.
 * ---------------------------------------------------------------------------
 *
 * Schema: project/README.md | Loader: App\Support\ProjectSeed::useProfile('hitamdao')
 *
 * @return array<string, mixed>
 */

$__hitamdaoSeed = array(
    'meta' => array(
        'schema' => 1,
        'brand' => 'Hi Tam Đảo',
        'tagline' => 'Sương mù, rừng thông & thoát urban cuối tuần từ Hà Nội',
        'admin' => array(
            'email' => 'admin@hitamdao.dev',
            'name' => 'Admin Hi Tam Đảo',
            'password' => '111111',
        ),
        'primary_domain' => 'hitamdao.dev',
        'domains' => array('hitamdao.dev', 'www.hitamdao.dev', 'hitamdao.com', 'www.hitamdao.com'),
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
            array('kind' => 'range', 'label' => 'Mùa sương mù — cuối năm {year}', 'starts_on' => '{year}-11-01', 'ends_on' => '{year}-12-31', 'is_promo' => true, 'priority' => 10, 'amount_multiplier' => 1.08),
            array('kind' => 'range', 'label' => 'Mùa sương mù — đầu năm {year}', 'starts_on' => '{year}-01-01', 'ends_on' => '{year}-02-28', 'is_promo' => true, 'priority' => 10, 'amount_multiplier' => 1.08),
            array('kind' => 'range', 'label' => 'Mùa hoa anh đào {year} (Mar–Apr)', 'starts_on' => '{year}-03-01', 'ends_on' => '{year}-04-30', 'is_promo' => true, 'priority' => 9, 'amount_multiplier' => 1.05),
            array('kind' => 'range', 'label' => 'Mùa hè Tam Đảo {year} (Jun–Aug)', 'starts_on' => '{year}-06-01', 'ends_on' => '{year}-08-31', 'is_promo' => true, 'priority' => 8, 'amount_multiplier' => 1.1),
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
        'how-to-get-there' => array('vi' => 'Di chuyển tới Tam Đảo thế nào?',



'en' => 'How to get to Tam Dao?'),
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
        'suong-mu-lang-man' => array(
            'vi' => 'Sương mù & lãng mạn',




'en' => 'Mist & romance',
        ),
        'trekking-vqg' => array(
            'vi' => 'Trekking & VQG',




'en' => 'Trekking & national park',
        ),
        'am-thuc-doi-ga' => array(
            'vi' => 'Ẩm thực gà đồi & suối cá',




'en' => 'Hill chicken & fish stream',
        ),
        'team-building-mice' => array(
            'vi' => 'Team-building & MICE',




'en' => 'Team building & MICE',
        ),
        'gia-dinh' => array(
            'vi' => 'Gia đình có trẻ em',




'en' => 'Family with kids',
        ),
    ),

    'review_platforms' => array(
        array('code' => 'tripadvisor', 'name' => 'Tripadvisor', 'rating' => 4.8, 'review_count' => 286, 'sort' => 0,
            'quote' => 'Khách quốc tế khen Thác Bạc trong sương và tour ẩm thực gà đồi — đúng không khí đồi mát gần Hà Nội.',
            'link_label' => 'Đọc đánh giá trên Tripadvisor', 'url' => 'https://www.tripadvisor.com'),
        array('code' => 'google', 'name' => 'Google', 'rating' => 4.7, 'review_count' => 412, 'sort' => 1,
            'quote' => '4.7/5 trên Google Maps — khách Hà Nội khen limousine cuối tuần và tư vấn resort trên đồi.',
            'link_label' => 'Xem đánh giá trên Google', 'url' => 'https://www.google.com/maps'),
        array('code' => 'trustpilot', 'name' => 'Trustpilot', 'rating' => 4.6, 'review_count' => 72, 'sort' => 2,
            'quote' => 'Điểm "Xuất sắc" — đặc biệt combo 2 ngày 1 đêm từ Hà Nội và hỗ trợ đổi lịch khi sương mù dày.',
            'link_label' => 'Đọc đánh giá trên Trustpilot', 'url' => 'https://www.trustpilot.com'),
    ),

    'cruise_types' => array(
        array('slug' => 'thuyen-suoi-ca', 'name' => 'Thuyền suối cá', 'count' => 2, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 10),
        array('slug' => 'thuyen-ho-picnic', 'name' => 'Thuyền hồ & picnic', 'count' => 2, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 20),
        array('slug' => 'sup-kayak-ho-nui', 'name' => 'SUP & kayak hồ núi', 'count' => 1, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 30),
    ),

    'home_slides' => array(
        array(
            'sort' => 0, 'text_align' => 'center', 'link_url' => '/tours',
            'vi' => array('title' => 'Tam Đảo', 'title_accent' => 'sương mù, rừng thông & di sản Pháp trên đồi',
                'description' => 'Khu nghỉ dưỡng mát ~900m, cách Hà Nội ~80km (2–2.5h) — Thác Bạc, Nhà thờ đá, Cầu Mây và ẩm thực gà đồi, suối cá, rượu mật ong.',
                'button_label' => 'Khám phá Tam Đảo', 'image_alt' => 'Tam Đảo — sương mù và rừng thông Vĩnh Phúc'),




'en' => array('title' => 'Tam Dao', 'title_accent' => 'mist, pine forest & French hill heritage',
                'description' => 'Cool retreat ~900m, ~80km from Hanoi (2–2.5h) — Silver Falls, Stone Church, Cloud Bridge and hill chicken, fish stream cuisine, honey wine.',
                'button_label' => 'Discover Tam Dao', 'image_alt' => 'Tam Dao mist and pine forest Vinh Phuc'),
        ),
        array(
            'sort' => 1, 'text_align' => 'center', 'link_url' => '/diem-den/thac-bac-thac-dai',
            'vi' => array('title' => 'Thác Bạc & Thác Dải', 'title_accent' => 'nước đổ giữa rừng thông',
                'description' => 'Hai thác nổi tiếng nhất Tam Đảo — đi bộ rừng, chụp ảnh sương và nghe kể chuyện khu nghỉ Pháp xưa.',
                'button_label' => 'Xem tour thác', 'image_alt' => 'Thác Bạc Tam Đảo trong sương mù'),




'en' => array('title' => 'Silver & Dai Falls', 'title_accent' => 'waterfalls in the pine forest',
                'description' => 'Tam Dao\'s most famous falls — forest walks, mist photography and stories of the old French hill station.',
                'button_label' => 'View waterfall tours', 'image_alt' => 'Silver Falls Tam Dao in the mist'),
        ),
        array(
            'sort' => 2, 'text_align' => 'center', 'link_url' => '/diem-den/ket-hop-ha-noi',
            'vi' => array('title' => 'Cuối tuần từ Hà Nội', 'title_accent' => 'limousine 2–2.5h & thoát urban',
                'description' => 'Thứ 6 chiều ra đồi, Chủ nhật về — combo limo + resort + Thác Bạc phổ biến nhất với dân văn phòng Hà Nội.',
                'button_label' => 'Xem tour cuối tuần', 'image_alt' => 'Cuối tuần escape Hanoi to Tam Dao'),




'en' => array('title' => 'Cuối tuần from Hanoi', 'title_accent' => '2–2.5h limo & urban escape',
                'description' => 'Friday evening to the hills, back Sunday — limo + resort + Silver Falls is the classic Hanoi office-worker break.',
                'button_label' => 'View cuối tuần tours', 'image_alt' => 'Cuối tuần escape Hanoi to Tam Dao'),
        ),
    ),

    // Zones → countries. Hub đầu; GEO = thác/cầu mây/VQG/resort/ẩm thực; ket-hop-* = combo only.
    // SEO country: parent_id=null; public URL /{slug}. Themes gắn hub trung-tam-thi-tran.
    'zones' => array(
        array('slug' => 'trung-tam-thi-tran', 'name' => 'Trung tâm thị trấn', 'size' => 'large', 'tourCount' => 6, 'tagline' => 'Nhà thờ đá, chợ đêm, quán ven đồi & di sản Pháp'),
        array('slug' => 'thac-bac-thac-dai', 'name' => 'Thác Bạc & Thác Dải', 'size' => 'large', 'tourCount' => 4, 'tagline' => 'Thác nước giữa rừng thông — điểm chụp sương iconic'),
        array('slug' => 'cau-may-sky-walk', 'name' => 'Cầu Mây & Sky Walk', 'size' => 'large', 'tourCount' => 3, 'tagline' => 'Cầu treo mây, Sky Walk view rừng — check-in hot'),
        array('slug' => 'vqg-rung-thong', 'name' => 'VQG Tam Đảo & rừng thông', 'size' => 'normal', 'tourCount' => 4, 'tagline' => 'Trekking, chim hoang dã & không khí trong lành'),
        array('slug' => 'khu-resort-dinh-cao', 'name' => 'Khu resort trên đỉnh', 'size' => 'normal', 'tourCount' => 3, 'tagline' => 'Resort, villa view sương — nghỉ dưỡng trên đồi'),
        array('slug' => 'suoi-ca-am-thuc', 'name' => 'Suối cá & ẩm thực', 'size' => 'normal', 'tourCount' => 3, 'tagline' => 'Suối cá, gà đồi, dê núi, chay tỏi & rượu mật ong'),
        array('slug' => 'ket-hop-ha-noi', 'name' => 'Cửa ngõ Hà Nội', 'size' => 'large', 'tourCount' => 4, 'tagline' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — limo ~80km'),
        array('slug' => 'ket-hop-ninh-binh', 'name' => 'Kết hợp Ninh Bình', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Tràng An + Tam Đảo — núi mát sau di sản'),
    ),

    'zone_translations' => array(
        'trung-tam-thi-tran' => array('vi' => 'Trung tâm thị trấn',



'en' => 'Town centre',
            'tagline' => array('vi' => 'Nhà thờ đá, chợ đêm, quán ven đồi & di sản Pháp',



'en' => 'Stone Church, night market, hillside eateries & French heritage')),
        'thac-bac-thac-dai' => array('vi' => 'Thác Bạc & Thác Dải',



'en' => 'Silver & Dai Falls',
            'tagline' => array('vi' => 'Thác nước giữa rừng thông — điểm chụp sương iconic',



'en' => 'Waterfalls in pine forest — iconic mist photo spots')),
        'cau-may-sky-walk' => array('vi' => 'Cầu Mây & Sky Walk',



'en' => 'Cloud Bridge & Sky Walk',
            'tagline' => array('vi' => 'Cầu treo mây, Sky Walk view rừng — check-in hot',



'en' => 'Cloud rope bridge, Sky Walk forest views — hot check-in')),
        'vqg-rung-thong' => array('vi' => 'VQG Tam Đảo & rừng thông',



'en' => 'Tam Dao NP & pine forest',
            'tagline' => array('vi' => 'Trekking, chim hoang dã & không khí trong lành',



'en' => 'Trekking, wild birds & pristine air')),
        'khu-resort-dinh-cao' => array('vi' => 'Khu resort trên đỉnh',



'en' => 'Hilltop resort area',
            'tagline' => array('vi' => 'Resort, villa view sương — nghỉ dưỡng trên đồi',



'en' => 'Resorts, mist-view villas — hilltop relaxation')),
        'suoi-ca-am-thuc' => array('vi' => 'Suối cá & ẩm thực',



'en' => 'Fish stream & cuisine',
            'tagline' => array('vi' => 'Suối cá, gà đồi, dê núi, chay tỏi & rượu mật ong',



'en' => 'Fish stream, hill chicken, goat, garlic veg & honey wine')),
        'ket-hop-ha-noi' => array('vi' => 'Cửa ngõ Hà Nội',



'en' => 'Hanoi gateway',
            'tagline' => array('vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — limo ~80km',



'en' => 'Day trip & 2N1D weekend from Hanoi — ~80km limo')),
        'ket-hop-ninh-binh' => array('vi' => 'Kết hợp Ninh Bình',



'en' => 'Combined with Ninh Binh',
            'tagline' => array('vi' => 'Tràng An + Tam Đảo — núi mát sau di sản',



'en' => 'Trang An + Tam Dao — cool hills after heritage sites')),
    ),

    'tours' => array(
        array(
            'slug' => 'tam-dao-2n1d-cuoi-tuan-ha-noi',
            'title' => 'Tam Đảo 2 ngày 1 đêm — Cuối tuần từ Hà Nội',
            'zoneSlug' => 'ket-hop-ha-noi',
            'zone' => 'Cửa ngõ Hà Nội',
            'tourCode' => 'TD2D-01',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 312,
            'badge' => 'Bán chạy nhất',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'cuoi-tuan-ha-noi',
            ),
            'quote' => array(
                'text' => 'Thứ 6 chiều limo ra đồi, sáng Thác Bạc trong sương — đúng kiểu thoát Hà Nội cuối tuần.',
                'author' => 'Chị Minh Anh',
            ),
            'places' => array(
                'Limousine HN',
                'Thác Bạc',
                'Nhà thờ đá',
                'Chợ đêm Tam Đảo',
            ),
            'start' => 'Hà Nội',
            'end' => 'Hà Nội',
            'highlightsIntro' => 'Combo cửa ngõ Hà Nội phổ biến nhất: limo 2 chiều, Thác Bạc, nhà thờ đá và tối ẩm thực gà đồi.',
            'highlights' => array(
                'Limousine Hà Nội ↔ Tam Đảo',
                'Thác Bạc buổi sáng',
                'Nhà thờ đá & phố đi bộ',
                'Gợi ý resort trên đồi',
                'Food tour tối (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Limousine HN — Tam Đảo — Nhà thờ đá',
                    'meals' => 'Tối',
                    'transport' => array(
                        'limousine',
                        'car',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => '16:00–17:00 đón Hà Nội, limo ~2–2.5h lên đồi. Check-in resort/homestay, dạo Nhà thờ đá, tối quán gà đồi hoặc suối cá.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Thác Bạc — Tiễn Hà Nội',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                        'limousine',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng sớm Thác Bạc (sương đẹp nhất), Cầu Mây/Sky Walk tuỳ thời tiết. Trưa ăn, chiều limo về Hà Nội ~18:00–19:00.',
                ),
            ),
            'inclusions' => array(
                'Limousine khứ hồi HN—Tam Đảo',
                'Xe nội bộ 2 ngày',
                'Vé Thác Bạc',
                'HDV địa phương',
            ),
            'exclusions' => array(
                'Lưu trú (đặt thêm)',
                'Ăn trưa/tối',
                'Vé Cầu Mây/Sky Walk',
            ),
            'notes' => array(
                'Có thể gộp đặt resort qua :brand theo ngân sách.',
                'Lịch đổi khi sương mù dày — ưu tiên an toàn đường đèo.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Tour có bao gồm khách sạn không?',
                    'a' => 'Giá tour chưa gồm phòng. :brand hỗ trợ gợi ý và đặt resort/homestay trên đồi — ghép vào cùng chuyến.',
                ),
                array(
                    'q' => 'Limousine đón khu vực nào ở Hà Nội?',
                    'a' => 'Thường đón Cầu Giấy, Ba Đình, Hoàn Kiếm, Hai Bà Trưng — phụ phí đón xa trung tâm.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 1650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'tam-dao-3n2d-tong-quan',
            'title' => 'Tam Đảo 3 ngày 2 đêm — Tổng quan thị trấn mây',
            'zoneSlug' => 'trung-tam-thi-tran',
            'zone' => 'Trung tâm thị trấn',
            'tourCode' => 'TD3D-01',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 4.9,
            'reviewCount' => 198,
            'badge' => 'Tổng quan',
            'featured' => true,
            'styles' => array(
                '3n2d',
                'gia-dinh',
            ),
            'quote' => array(
                'text' => 'Ba ngày đủ để cảm nhận Tam Đảo — từ nhà thờ đá, Thác Bạc đến VQG và ẩm thực suối cá.',
                'author' => 'Anh Quốc Bảo',
            ),
            'places' => array(
                'Nhà thờ đá',
                'Thác Bạc',
                'Thác Dải',
                'Cầu Mây',
                'VQG Tam Đảo',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Lịch trình cân bằng cho lần đầu: di sản Pháp, thác nước, cầu mây và half-day VQG.',
            'highlights' => array(
                'City tour nhà thờ đá & phố núi',
                'Thác Bạc + Thác Dải',
                'Cầu Mây / Sky Walk',
                'Trekking VQG nhẹ',
                'Tour ẩm thực đặc sản',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đón — Nhà thờ đá & phố núi',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Đón HN/Vĩnh Yên, lên đồi. Chiều Nhà thờ đá, dạo phố, tối gà đồi hoặc chay tỏi.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Thác Bạc — Thác Dải — Cầu Mây',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Sáng Thác Bạc trong sương, Thác Dải. Chiều Cầu Mây, Sky Walk. Tối rượu mật ong & dê núi.',
                ),
                array(
                    'day' => 3,
                    'title' => 'VQG — Suối cá — Tiễn',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng trekking VQG nhẹ (2–3h). Trưa suối cá, tiễn khách xuống HN/Vĩnh Phúc.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng 3 ngày',
                'Vé tham quan theo lịch',
                'HDV địa phương',
                'Bữa ăn ghi trong chương trình',
            ),
            'exclusions' => array(
                'Limousine HN (đặt thêm)',
                'Lưu trú',
                'Đồ uống',
                'Tip',
            ),
            'notes' => array(
                'Mang áo khoát — tối 15–18°C mùa sương.',
            ),
            'faqs' => array(),
            'galleryCount' => 6,
            'priceFrom' => 2850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'tam-dao-2n1d-lang-man',
            'title' => 'Tam Đảo 2 ngày 1 đêm — Lãng mạn & sương mù',
            'zoneSlug' => 'khu-resort-dinh-cao',
            'zone' => 'Khu resort trên đỉnh',
            'tourCode' => 'TD2D-02',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 124,
            'badge' => 'Cặp đôi',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'cuoi-tuan-ha-noi',
                'suong-mu-lang-man',
            ),
            'quote' => array(
                'text' => 'Sáng dậy trong sương trên đồi, chiều Cầu Mây — mini trăng mật không cần bay xa.',
                'author' => 'Vợ chồng Tuấn — Hà',
            ),
            'places' => array(
                'Resort trên đồi',
                'Cầu Mây',
                'Thác Bạc',
                'Sky Walk',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Dành cho cặp đôi: resort view sương, photo walk Cầu Mây, tối rượu mật ong.',
            'highlights' => array(
                'Resort/villa view đồi',
                'Photo walk sương sớm',
                'Cầu Mây & Sky Walk',
                'Bữa tối lãng mạn gợi ý',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Resort & Cầu Mây',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Resort Tam Đảo',
                    'content' => 'Check-in resort trên đỉnh, chiều Cầu Mây/Sky Walk, tối fine-dining hoặc quán view sương.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Thác Bạc sương sớm — Tiễn',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => '05:30–08:00 Thác Bạc chụp sương couple, brunch, tiễn khách.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng 2 ngày',
                'Vé Cầu Mây/Sky Walk',
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
            'priceFrom' => 1850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'city-tour-tam-dao-1-ngay',
            'title' => 'Tour thành phố Tam Đảo 1 ngày — Nhà thờ đá & phố núi',
            'zoneSlug' => 'trung-tam-thi-tran',
            'zone' => 'Trung tâm thị trấn',
            'tourCode' => 'TD1D-01',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 267,
            'badge' => 'Phổ biến',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Một ngày gom Nhà thờ đá, chợ và quán ven đồi — không cần tự lái đèo.',
                'author' => 'Gia đình chị Thảo',
            ),
            'places' => array(
                'Nhà thờ đá',
                'Chợ Tam Đảo',
                'Phố đi bộ',
                'Nhà cổ Pháp',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Tour ghép hoặc riêng — di sản Pháp và nhịp sống thị trấn mây trong một ngày.',
            'highlights' => array(
                'Nhà thờ đá Tam Đảo',
                'Khu nhà cổ Pháp',
                'Chợ & quán ven đồi',
                'Gợi ý rượu mật ong',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Tour thành phố trọn ngày',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => '08:30–17:00 tham quan nhà thờ, phố núi, ăn trưa gà đồi, chiều tự do chợ & cafe view sương.',
                ),
            ),
            'inclusions' => array(
                'Xe 16 chỗ hoặc riêng',
                'HDV',
                'Bữa trưa',
            ),
            'exclusions' => array(
                'Vé tham quan riêng lẻ',
                'Đồ uống',
            ),
            'notes' => array(
                'Ghép nhóm max 12 khách.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 580000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'thac-bac-thac-dai-1-ngay',
            'title' => 'Thác Bạc & Thác Dải — Trekking rừng thông 1 ngày',
            'zoneSlug' => 'thac-bac-thac-dai',
            'zone' => 'Thác Bạc & Thác Dải',
            'tourCode' => 'TD1D-02',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 342,
            'badge' => 'Signature',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Thác Bạc trong sương sáng sớm — ảnh đẹp nhất chuyến Tam Đảo.',
                'author' => 'Photographer Ken',
            ),
            'places' => array(
                'Thác Bạc',
                'Thác Dải',
                'Rừng thông',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Hoạt động đặc trưng Tam Đảo — đi bộ rừng thông, nghe thác đổ giữa sương.',
            'highlights' => array(
                'Thác Bạc buổi sáng',
                'Thác Dải',
                'HDV kể chuyện khu nghỉ Pháp',
                'Góc chụp sương',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Thác Bạc & Thác Dải',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => '06:00–06:30 đón, Thác Bạc sương sớm, Thác Dải, trưa quán địa phương, về ~15:00.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Vé thác',
                'HDV',
                'Trưa',
            ),
            'exclusions' => array(
                'Áo mưa',
                'Tip',
            ),
            'notes' => array(
                'Mang giày chống trượt; sương dày view phụ thuộc thời tiết.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 720000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cau-may-sky-walk-nua-ngay',
            'title' => 'Cầu Mây & Sky Walk — Nửa ngày check-in',
            'zoneSlug' => 'cau-may-sky-walk',
            'zone' => 'Cầu Mây & Sky Walk',
            'tourCode' => 'TD0.5D-01',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 289,
            'badge' => 'Check-in',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Cầu Mây giữa rừng — cảm giác đi bộ trên mây thật sự.',
                'author' => 'Chị Thùy Linh',
            ),
            'places' => array(
                'Cầu Mây',
                'Sky Walk',
                'Rừng thông',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => '4 giờ — cầu treo, Sky Walk view rừng thông, ghép tour dài hoặc đặt lẻ.',
            'highlights' => array(
                'Vé Cầu Mây',
                'Sky Walk',
                'HDV góc chụp',
                'Xe đón resort',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cầu Mây circuit',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '08:00 hoặc 13:30 đón, 2–3h tham quan cầu & sky walk, về resort.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Vé Cầu Mây/Sky Walk',
                'HDV',
            ),
            'exclusions' => array(
                'Ăn uống',
            ),
            'notes' => array(
                'Đóng khi mưa bão — đổi slot miễn phí.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'trekking-vqg-tam-dao-1-ngay',
            'title' => 'Trekking VQG Tam Đảo — Rừng thông & chim 1 ngày',
            'zoneSlug' => 'vqg-rung-thong',
            'zone' => 'VQG Tam Đảo & rừng thông',
            'tourCode' => 'TD1D-03',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 156,
            'badge' => 'Trekking',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'trekking-vqg',
            ),
            'quote' => array(
                'text' => 'Không khí trong lành, nghe chim — khác hẳn Hà Nội chỉ 80km.',
                'author' => 'Anh Đức Thắng',
            ),
            'places' => array(
                'VQG Tam Đảo',
                'Rừng thông',
                'Suối núi',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Trek 4–5h cung nhẹ-trung bình trong VQG — HLV địa phương, picnic rừng.',
            'highlights' => array(
                'HLV trekking',
                'Cung rừng thông',
                'Quan sát chim (tuỳ mùa)',
                'Picnic nhẹ',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'VQG trek',
                    'meals' => 'Picnic',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => '07:30 briefing, trek 4h qua rừng thông, picnic, về trước 16h.',
                ),
            ),
            'inclusions' => array(
                'HLV',
                'Vé VQG',
                'Picnic',
                'Xe',
            ),
            'exclusions' => array(
                'Giày trek chuyên dụng',
            ),
            'notes' => array(
                'Mang áo mưa mùa hè; không xả rác trong rừng.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'food-tour-am-thuc-tam-dao',
            'title' => 'Tour ẩm thực Tam Đảo — Buổi tối',
            'zoneSlug' => 'suoi-ca-am-thuc',
            'zone' => 'Suối cá & ẩm thực',
            'tourCode' => 'TD0.5D-02',
            'duration' => 'Nửa đêm',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 198,
            'badge' => 'Ăn uống',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'am-thuc-doi-ga',
            ),
            'quote' => array(
                'text' => 'Gà đồi, suối cá, chay tỏi và rượu mật ong — ăn no mà vẫn nhớ.',
                'author' => 'Food blogger Ken',
            ),
            'places' => array(
                'Suối cá',
                'Quán gà đồi',
                'Chay tỏi',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => '18:00–22:00 — dẫn ăn 6–8 món đặc sản với HDV am thực.',
            'highlights' => array(
                'Gà đồi Tam Đảo',
                'Suối cá tươi',
                'Chay tỏi',
                'Dê núi',
                'Rượu mật ong',
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
            'priceFrom' => 420000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'gia-dinh-tam-dao-2n1d',
            'title' => 'Tam Đảo gia đình 2 ngày 1 đêm — Nhẹ nhàng',
            'zoneSlug' => 'trung-tam-thi-tran',
            'zone' => 'Trung tâm thị trấn',
            'tourCode' => 'TD2D-03',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 145,
            'badge' => 'Gia đình',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'gia-dinh',
            ),
            'quote' => array(
                'text' => 'Bé thích suối cá, bố mẹ thích Thác Bạc sương sớm — ai cũng vui.',
                'author' => 'Chị Ngọc Ánh',
            ),
            'places' => array(
                'Thác Bạc',
                'Suối cá',
                'Nhà thờ đá',
                'Chợ đêm',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Lịch nhẹ cho trẻ: thác gần, suối cá, phố đi bộ — không trek nặng.',
            'highlights' => array(
                'Thác Bạc chiều (tránh sương sớm)',
                'Suối cá trưa',
                'Nhà thờ đá',
                'Chợ đêm ăn vặt',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Lên đồi — Nhà thờ — Chợ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Trưa lên đồi, chiều nhà thờ & phố, tối chợ đêm.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Thác Bạc — Suối cá — Tiễn',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Thác Bạc, trưa suối cá, chiều tiễn.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng',
                'Vé điểm tham quan',
                'HDV gia đình',
            ),
            'exclusions' => array(
                'Lưu trú',
                'Limousine HN',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 1750000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'team-building-tam-dao-2n1d',
            'title' => 'Team-building Tam Đảo 2 ngày 1 đêm',
            'zoneSlug' => 'khu-resort-dinh-cao',
            'zone' => 'Khu resort trên đỉnh',
            'tourCode' => 'TD2D-04',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 67,
            'badge' => 'Doanh nghiệp',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'team-building-mice',
                'trekking-vqg',
            ),
            'quote' => array(
                'text' => 'Treking nhẹ + gala tối trên đồi — team Hà Nội recharge hiệu quả.',
                'author' => 'HR Manager Linh',
            ),
            'places' => array(
                'Resort',
                'VQG',
                'Sân team-building',
            ),
            'start' => 'Hà Nội',
            'end' => 'Hà Nội',
            'highlightsIntro' => 'Gói MICE nhỏ 15–40 người: limo, phòng họp, game rừng, gala tối.',
            'highlights' => array(
                'Limousine đoàn',
                'Phòng họp resort',
                'Game rừng thông',
                'Gala BBQ tối',
                'Trek nhóm VQG',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'HN — Resort — Team games',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'limousine',
                        'car',
                    ),
                    'overnight' => 'Resort Tam Đảo',
                    'content' => 'Sáng limo HN, trưa check-in, chiều game team-building, tối gala BBQ.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Trek nhóm — Tiễn HN',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'trekking',
                        'limousine',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng trek VQG nhẹ cả đoàn, trưa, chiều limo về HN.',
                ),
            ),
            'inclusions' => array(
                'Limousine khứ hồi',
                'Phòng họp 4h',
                'MC/facilitator',
                'Game kit',
                'Gala cơ bản',
            ),
            'exclusions' => array(
                'Lưu trú phòng ( báo riêng)',
                'Rượu gala',
                'Teambuilding premium',
            ),
            'notes' => array(
                'Báo số người trước 7 ngày.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 3200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'ha-noi-tam-dao-tour-ngay',
            'title' => 'Tour ngày Hà Nội — Tam Đảo',
            'zoneSlug' => 'ket-hop-ha-noi',
            'zone' => 'Cửa ngõ Hà Nội',
            'tourCode' => 'TD1D-HN',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 423,
            'badge' => 'Tour ngày',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'cuoi-tuan-ha-noi',
            ),
            'quote' => array(
                'text' => 'Một ngày đủ Thác Bạc và nhà thờ — về HN tối, không cần nghỉ lại.',
                'author' => 'Anh Felix',
            ),
            'places' => array(
                'Limousine HN',
                'Thác Bạc',
                'Nhà thờ đá',
            ),
            'start' => 'Hà Nội',
            'end' => 'Hà Nội',
            'highlightsIntro' => '07:00 HN — 21:00 HN: limo + Thác Bạc + city tour — cho khách thiếu thời gian.',
            'highlights' => array(
                'Limousine 2 chiều',
                'Thác Bạc',
                'Nhà thờ đá',
                'Trưa gà đồi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Day trip HN—Tam Đảo',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'limousine',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => '07:00 đón HN, 10:00 Thác Bạc, trưa, chiều nhà thờ, 17:00 xuống đèo, ~21:00 về HN.',
                ),
            ),
            'inclusions' => array(
                'Limousine',
                'Xe nội bộ',
                'Vé Thác Bạc',
                'HDV',
                'Trưa',
            ),
            'exclusions' => array(
                'Cầu Mây',
                'Mua sắm',
            ),
            'notes' => array(
                'Mệt hơn 2 ngày 1 đêm — khuyên cuối tuần nghỉ lại.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 980000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'tam-dao-4n3d-kham-pha-sau',
            'title' => 'Tam Đảo 4 ngày 3 đêm — Khám phá sâu',
            'zoneSlug' => 'vqg-rung-thong',
            'zone' => 'VQG Tam Đảo & rừng thông',
            'tourCode' => 'TD4D-01',
            'duration' => '4 ngày 3 đêm',
            'days' => 4,
            'rating' => 4.8,
            'reviewCount' => 54,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                '4n3d',
                'trekking-vqg',
            ),
            'quote' => array(
                'text' => 'Bốn ngày mới đủ trek VQG, cả hai thác và tour ẩm thực đầy đủ.',
                'author' => 'Nhóm bạn Hà Nội',
            ),
            'places' => array(
                'Thác Bạc',
                'Thác Dải',
                'VQG',
                'Cầu Mây',
                'Suối cá',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Hành trình đầy đủ: thác, cầu mây, 2 buổi VQG, ẩm thực và nghỉ resort.',
            'highlights' => array(
                '2 ngày VQG/trek',
                'Thác Bạc + Dải',
                'Cầu Mây Sky Walk',
                'Tour ẩm thực & suối cá',
                'Resort trên đồi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đón — City & nhà thờ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Lên đồi, city tour, tối tour ẩm thực.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Thác Bạc — Thác Dải',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Cả ngày thác & rừng thông.',
                ),
                array(
                    'day' => 3,
                    'title' => 'VQG trekking',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'trekking',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Trek cung dài VQG, picnic, tối nghỉ sớm.',
                ),
                array(
                    'day' => 4,
                    'title' => 'Cầu Mây — Suối cá — Tiễn',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Cầu Mây, trưa suối cá, tiễn.',
                ),
            ),
            'inclusions' => array(
                'Xe riêng',
                'Vé & trek',
                'HDV',
                'Bữa theo chương trình',
            ),
            'exclusions' => array(
                'Lưu trú',
                'Limousine HN',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 6,
            'priceFrom' => 4200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'photo-tour-suong-mu-tam-dao',
            'title' => 'Tour chụp ảnh sương mù Tam Đảo — 1 ngày',
            'zoneSlug' => 'thac-bac-thac-dai',
            'zone' => 'Thác Bạc & Thác Dải',
            'tourCode' => 'TD1D-04',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 78,
            'badge' => 'Photography',
            'featured' => false,
            'styles' => array(
                'day-trip',
                'suong-mu-lang-man',
            ),
            'quote' => array(
                'text' => 'Photographer biết góc Thác Bạc lúc 6h — ảnh không cần filter.',
                'author' => 'Cặp đôi Huy — Mai',
            ),
            'places' => array(
                'Thác Bạc',
                'Rừng thông',
                'Cầu Mây',
            ),
            'start' => 'Tam Đảo',
            'end' => 'Tam Đảo',
            'highlightsIntro' => 'Xe + photographer half-day sương sớm — Thác Bạc, rừng thông, Cầu Mây.',
            'highlights' => array(
                'Photographer 4h',
                '3–4 địa điểm',
                'Gợi ý trang phục',
                '50+ ảnh chỉnh màu',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Mist photo tour',
                    'meals' => null,
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => '05:00 Thác Bạc sương, 08:00 rừng thông, 10:00 Cầu Mây (tuỳ thời tiết).',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Photographer',
                '50+ ảnh',
            ),
            'exclusions' => array(
                'Makeup',
            ),
            'notes' => array(
                'Mùa sương Nov–Feb đẹp nhất.',
            ),
            'faqs' => array(),
            'galleryCount' => 6,
            'priceFrom' => 2200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'combo-tam-dao-ninh-binh-3n2d',
            'title' => 'Tam Đảo — Ninh Bình 3 ngày 2 đêm',
            'zoneSlug' => 'ket-hop-ninh-binh',
            'zone' => 'Kết hợp Ninh Bình',
            'tourCode' => 'TDNB3D-01',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 4.7,
            'reviewCount' => 48,
            'badge' => 'Combo',
            'featured' => false,
            'styles' => array(
                '3n2d',
            ),
            'quote' => array(
                'text' => 'Tràng An một ngày, Tam Đảo hai ngày — contrast hay cho khách quốc tế.',
                'author' => 'Sarah Kim',
            ),
            'places' => array(
                'Tràng An',
                'Tam Cốc',
                'Tam Đảo',
                'Thác Bạc',
            ),
            'start' => 'Hà Nội',
            'end' => 'Hà Nội',
            'highlightsIntro' => '2 đêm: 1N Ninh Bình + 1N Tam Đảo — xe nối ~2.5h giữa hai vùng.',
            'highlights' => array(
                'Tràng An boat',
                'Tam Cốc (tuỳ chọn)',
                'Tam Đảo Thác Bạc',
                'Limousine HN',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'HN — Ninh Bình — Tràng An',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Ninh Bình',
                    'content' => 'Sáng HN, Tràng An chiều, tối Ninh Bình.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Ninh Bình — Tam Đảo',
                    'meals' => 'Sáng; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Tam Đảo',
                    'content' => 'Sáng Tam Cốc ngắn, chiều lên Tam Đảo, tối gà đồi.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Thác Bạc — Về HN',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Thác Bạc, trưa xuống đèo, về HN chiều.',
                ),
            ),
            'inclusions' => array(
                'Xe 3 ngày',
                'Vé Tràng An',
                'Vé Thác Bạc',
                'HDV',
            ),
            'exclusions' => array(
                'Lưu trú',
                'Ăn tối',
            ),
            'notes' => array(
                'Có thể đảo chiều bắt đầu Tam Đảo trước.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 3100000,
            'currency' => 'VND',
        ),
    ),
    'cruises' => array(
        array(
            'slug' => 'thuyen-suoi-ca-an-tuoi',
            'title' => 'Thuyền suối cá & ăn tươi — Nửa ngày',
            'typeSlug' => 'thuyen-suoi-ca', 'typeName' => 'Thuyền suối cá',
            'tourCode' => 'TD-SC-01', 'duration' => '3 giờ', 'days' => 1,
            'rating' => 4.9, 'reviewCount' => 186, 'badge' => 'Đặc sản',
            'styles' => array('food-culture', 'family', 'day-trip'),
            'quote' => array('text' => 'Cá suối ăn ngay trên thuyền — trải nghiệm đặc trưng Tam Đảo.', 'author' => 'Gia đình chị Hương'),
            'places' => array('Suối cá Tam Đảo', 'Rừng thông ven suối'),
            'start' => 'Bến suối cá', 'end' => 'Bến suối cá',
            'departurePort' => 'Suối cá Tam Đảo', 'boatClass' => 'Thuyền gỗ mái che', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '10:00–13:00 — thuyền dọc suối, cho cá ăn, bắt thử và nướng tại chỗ.',
            'highlights' => array('Thuyền suối cá', 'Cho cá ăn & bắt thử', 'Nướng trên thuyền', 'HDV địa phương'),
            'itinerary' => array(array('day' => 1, 'title' => 'Thuyền suối cá', 'meals' => 'Cá suối nướng', 'transport' => array('boat'), 'overnight' => null,
                'content' => 'Lên thuyền, dọc suối trong rừng thông, cho cá ăn, nướng thử, về trưa.')),
            'inclusions' => array('Thuyền', 'HDV', 'Cá nướng thử'), 'exclusions' => array('Đồ uống có cồn', 'Xe từ trung tâm'),
            'notes' => array('Phụ thuộc mực nước suối — mùa khô ổn định hơn.'), 'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 380000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'thuyen-ho-picnic-sang',
            'title' => 'Thuyền hồ picnic sáng sớm',
            'typeSlug' => 'thuyen-ho-picnic', 'typeName' => 'Thuyền hồ & picnic',
            'tourCode' => 'TD-HP-02', 'duration' => '2.5 giờ', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 94, 'badge' => 'Sáng sớm',
            'styles' => array('romantic', 'fog-mist', 'photography'),
            'quote' => array('text' => 'Sương mỏng trên mặt hồ — picnic trà nóng giữa rừng thông.', 'author' => 'Chị Diệu Linh'),
            'places' => array('Hồ núi Tam Đảo', 'Rừng thông bao quanh'),
            'start' => 'Bến hồ', 'end' => 'Bến hồ',
            'departurePort' => 'Bến hồ Tam Đảo', 'boatClass' => 'Thuyền picnic', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '06:30–09:00 — thuyền nhỏ trên hồ núi, picnic trà & bánh, góc chụp sương.',
            'highlights' => array('Thuyền riêng/nhóm nhỏ', 'Picnic trà nóng', 'View rừng thông', 'Nhóm max 8'),
            'itinerary' => array(array('day' => 1, 'title' => 'Picnic sáng bên hồ', 'meals' => 'Picnic nhẹ', 'transport' => array('boat'), 'overnight' => null,
                'content' => 'Sáng sớm lên thuyền, dạo hồ, dừng picnic giữa rừng, về trước 9h.')),
            'inclusions' => array('Thuyền', 'Picnic', 'HDV'), 'exclusions' => array('Xe đón resort'),
            'notes' => array('Mùa sương Nov–Feb đẹp nhất — mang áo ấm.'), 'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 420000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'sup-kayak-ho-nui-tam-dao',
            'title' => 'SUP & kayak hồ núi Tam Đảo',
            'typeSlug' => 'sup-kayak-ho-nui', 'typeName' => 'SUP & kayak hồ núi',
            'tourCode' => 'TD-SUP-03', 'duration' => '2 giờ', 'days' => 1,
            'rating' => 4.7, 'reviewCount' => 58, 'badge' => null,
            'styles' => array('adventure', 'day-trip', 'balanced'),
            'quote' => array('text' => 'Nước hồ lặng sáng sớm — SUP dễ hơn tưởng, view núi đẹp.', 'author' => 'Anh Felix'),
            'places' => array('Hồ núi Tam Đảo'),
            'start' => 'Bến hồ', 'end' => 'Bến hồ',
            'departurePort' => 'Bến hồ Tam Đảo', 'boatClass' => 'SUP / kayak đôi', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '07:00–09:00 — SUP hoặc kayak đôi, briefing, phù hợp người mới.',
            'highlights' => array('SUP hoặc kayak', 'Áo phao & briefing', 'HLV kèm', 'Nước & snack'),
            'itinerary' => array(array('day' => 1, 'title' => 'Chèo kayak trên hồ', 'meals' => 'Đồ ăn nhẹ', 'transport' => array('kayak'), 'overnight' => null,
                'content' => 'Briefing, chèo 1h quanh bờ rừng thông, về bến.')),
            'inclusions' => array('Thiết bị', 'HLV', 'Snack'), 'exclusions' => array('Xe đón'),
            'notes' => array('Biết bơi cơ bản khuyến nghị. Hủy khi mưa lớn.'), 'faqs' => array(),
            'galleryCount' => 3, 'priceFrom' => 320000.0, 'currency' => 'VND',
        ),
    ),

    'blog_categories' => array(
        array('slug' => 'trung-tam-thi-tran', 'name' => 'Trung tâm thị trấn', 'zoneSlug' => 'trung-tam-thi-tran', 'count' => 2),
        array('slug' => 'thac-thien-nhien', 'name' => 'Thác & thiên nhiên', 'zoneSlug' => 'thac-bac-thac-dai', 'count' => 2),
        array('slug' => 'di-chuyen-ha-noi', 'name' => 'Di chuyển từ Hà Nội', 'zoneSlug' => 'ket-hop-ha-noi', 'count' => 2),
        array('slug' => 'am-thuc-suoi-ca', 'name' => 'Ẩm thực & suối cá', 'zoneSlug' => 'suoi-ca-am-thuc', 'count' => 2),
    ),

    'popular_keywords' => array(
        'Kinh nghiệm du lịch Tam Đảo', 'Tam Đảo mùa nao đẹp', 'Từ Hà Nội đi Tam Đảo', 'Thác Bạc Thác Dải',
        'Tour cuối tuần Tam Đảo', 'Ăn gì ở Tam Đảo', 'Limousine Hà Nội Tam Đảo', 'Tam Đảo 2 ngày 1 đêm',
        'Cầu Mây Sky Walk', 'Homestay Tam Đảo khu nào', 'Sương mù Tam Đảo', 'Bay Nội Bài Tam Đảo',
    ),

    'articles' => array(
        array(
            'slug' => 'tu-ha-noi-di-tam-dao-the-nao',
            'title' => 'Từ Hà Nội đi Tam Đảo thế nào? Limousine, xe khách hay bay Nội Bài',
            'zoneSlug' => 'ket-hop-ha-noi', 'zone' => 'Kết hợp Hà Nội',
            'category' => 'Di chuyển từ Hà Nội', 'categorySlug' => 'di-chuyen-ha-noi',
            'tags' => array('Di chuyển thế nào?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '05/06/2026', 'updatedAt' => '20/07/2026',
            'views' => 2340, 'rating' => 4.9, 'ratingCount' => 62,
            'excerpt' => 'Tam Đảo cách Hà Nội ~80km — limousine 2–2.5h là lựa chọn phổ biến nhất; bay Nội Bài + xe nối cho khách tỉnh xa.',
            'content' => array(
                array('type' => 'p', 'text' => 'Tam Đảo không có sân bay. Phần lớn khách cuối tuần từ Hà Nội chọn limousine 9–16 chỗ (~180–280k/chiều) hoặc xe khách rẻ hơn. Khách bay vào Nội Bài (Hà Nội) cộng transfer ~2.5–3h.'),
                array('type' => 'h2', 'id' => 'limousine', 'text' => 'I. Limousine Hà Nội — Tam Đảo'),
                array('type' => 'p', 'text' => 'Đón quận nội thành, mất 2–2.5 giờ qua đèo Tam Đảo — phù hợp cặp đôi và gia đình. T6–CN và mùa sương nên đặt trước.'),
                array('type' => 'h2', 'id' => 'xe-khach', 'text' => 'II. Xe khách / open bus'),
                array('type' => 'p', 'text' => 'Giá thấp hơn limo — thường về bến chân đồi, cần taxi/xe ôm lên thị trấn (~15 phút).'),
                array('type' => 'h2', 'id' => 'bay-han', 'text' => 'III. Bay Nội Bài + transfer'),
                array('type' => 'p', 'text' => 'Khách tỉnh xa bay Hà Nội, xe nối thẳng Tam Đảo ~2.5–3h — tiện khi gộp tour nhiều ngày Bắc Bộ.'),
            ),
            'faqs' => array(array('q' => 'Cuối tuần có kẹt đèo không?', 'a' => 'T6 chiều và CN chiều thường đông — nên đi sáng sớm thứ 7 hoặc tránh 16h–19h về.')),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'tam-dao-mua-nao-dep-nhat',
            'title' => 'Tam Đảo mùa nao đẹp nhất? Sương mù, hoa anh đào & mùa hè mát',
            'zoneSlug' => 'trung-tam-thi-tran', 'zone' => 'Trung tâm thị trấn',
            'category' => 'Trung tâm thị trấn', 'categorySlug' => 'trung-tam-thi-tran',
            'tags' => array('Mẹo du lịch', 'Chơi gì, xem gì?'),
            'author' => 'Lan Hương', 'publishedAt' => '12/06/2026', 'updatedAt' => '28/07/2026',
            'views' => 1980, 'rating' => 4.9, 'ratingCount' => 48,
            'excerpt' => 'Mát quanh năm ~900m — mỗi mùa một vẻ: sương dày Nov–Feb, hoa anh đào Mar–Apr, hè thoát nóng HN.',
            'content' => array(
                array('type' => 'p', 'text' => 'Tam Đảo ~900m — nhiệt độ thường 18–26°C, tối có thể 12–15°C. Mùa sương (Nov–Feb) đẹp cho chụp ảnh; Mar–Apr hoa anh đào; Jun–Aug đông khách thoát nóng.'),
                array('type' => 'h2', 'id' => 'suong-mu', 'text' => 'Mùa sương mù (Nov–Feb)'),
                array('type' => 'p', 'text' => 'Thác Bạc và Cầu Mây trong sương — mang áo khoác, dự phòng limo trễ vì đèo kẹt.'),
            ),
            'faqs' => array(), 'galleryCount' => 5,
        ),
        array(
            'slug' => 'an-gi-o-tam-dao',
            'title' => 'Ăn gì ở Tam Đảo? Gà đồi, suối cá, chay tỏi & rượu mật ong',
            'zoneSlug' => 'suoi-ca-am-thuc', 'zone' => 'Suối cá & ẩm thực',
            'category' => 'Ẩm thực & suối cá', 'categorySlug' => 'am-thuc-suoi-ca',
            'tags' => array('Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '18/06/2026', 'updatedAt' => '02/08/2026',
            'views' => 1720, 'rating' => 4.8, 'ratingCount' => 41,
            'excerpt' => 'Ẩm thực Tam Đảo gắn núi rừng — gà đồi, cá suối, rau chay tỏi và rượu mật ong.',
            'content' => array(
                array('type' => 'p', 'text' => 'Tour ẩm thực nửa ngày là cách nhanh nhất thử đặc sản — gà đồi nướng, suối cá ăn tươi, quán chay tỏi và mua rượu mật ong làm quà.'),
                array('type' => 'ul', 'items' => array('Gà đồi Tam Đảo', 'Cá suối nướng', 'Rau chay tỏi', 'Rượu mật ong', 'Dê núi (mùa lạnh)')),
            ),
            'faqs' => array(), 'galleryCount' => 4,
        ),
        array(
            'slug' => 'thac-bac-thac-dai-kinh-nghiem',
            'title' => 'Thác Bạc & Thác Dải: kinh nghiệm, giờ đi và mẹo chụp ảnh',
            'zoneSlug' => 'thac-bac-thac-dai', 'zone' => 'Thác Bạc & Thác Dải',
            'category' => 'Thác & thiên nhiên', 'categorySlug' => 'thac-thien-nhien',
            'tags' => array('Chơi gì, xem gì?', 'Chọn tour nào?'),
            'author' => 'Phạm Thị Liên', 'publishedAt' => '22/06/2026', 'updatedAt' => '10/07/2026',
            'views' => 1450, 'rating' => 4.9, 'ratingCount' => 35,
            'excerpt' => 'Hai thác nổi tiếng nhất Tam Đảo — nên đi sáng sớm tránh đông, mang giày chống trượt.',
            'content' => array(
                array('type' => 'p', 'text' => 'Thác Bạc (~50m) gần thị trấn; Thác Dải dài hơn, ít đông hơn. Tour 1 ngày gộp cả hai + Nhà thờ đá — hoặc ghép vào chuyến 2 ngày 1 đêm cuối tuần.'),
                array('type' => 'p', 'text' => 'Mùa mưa thác mạnh nhưng đường trơn; mùa khô dễ đi hơn. Sương mù buổi sáng cho ảnh đẹp.'),
            ),
            'faqs' => array(array('q' => 'Trẻ em đi được không?', 'a' => 'Thác Bạc phù hợp gia đình với lối đi bộ; Thác Dải cần giám sát kỹ ở bậc đá.')),
            'galleryCount' => 5,
        ),
    ),

    'testimonials' => array(
        array('name' => 'Nguyễn Minh Anh', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => '2 ngày 1 đêm cuối tuần từ Hà Nội vừa đủ — Thác Bạc trong sương và gà đồi tối là nhớ nhất.', 'photos' => 5, 'trip' => 'Tam Đảo 2 ngày 1 đêm cuối tuần', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Sarah Kim', 'country' => 'Hàn Quốc', 'flag' => '🇰🇷', 'rating' => 5.0,
            'quote' => 'French hill town vibe near Hanoi — Stone Church and mist photos were perfect.', 'photos' => 8, 'trip' => 'Photo tour sương mù', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Trần Quốc Bảo', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.9,
            'quote' => 'Resort trên đồi đúng gu — sáng dậy trong sương, chiều Cầu Mây.', 'photos' => 4, 'trip' => 'Tam Đảo 3 ngày 2 đêm tổng quan', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Felix Müller', 'country' => 'Đức', 'flag' => '🇩🇪', 'rating' => 4.8,
            'quote' => 'Fish stream boat lunch — unique and fun for the whole family.', 'photos' => 6, 'trip' => 'Thuyền suối cá', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Diệu Linh', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => 'Thuyền hồ picnic sáng sớm — lãng mạn hơn mọi nhà hàng view.', 'photos' => 3, 'trip' => 'Thuyền hồ picnic', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'David Park', 'country' => 'Úc', 'flag' => '🇦🇺', 'rating' => 4.9,
            'quote' => 'Limousine from Hanoi was smooth — they even helped pick a hilltop resort area.', 'photos' => 7, 'trip' => 'Cuối tuần escape Hanoi', 'avatar' => null, 'photoUrls' => array()),
    ),

    'team' => array(
        array(
            'slug' => 'tran-van-phuc', 'name' => 'Trần Văn Phúc', 'role' => 'Giám đốc điều hành',
            'bio' => 'Sinh ra tại Vĩnh Phúc, hơn 12 năm thiết kế tour Tam Đảo và kết nối resort/homestay địa phương...',
            'phone' => '+84 211 388 8888', 'email' => 'phuc.tran@hitamdao.dev', 'area' => 'Tam Đảo, Vĩnh Phúc',
            'years_experience' => 12, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 2800, 'stat_tours' => 520, 'stat_awards' => 3, 'is_verified' => true,
            'bio_html' => '<p>Sinh ra tại Vĩnh Phúc, Trần Văn Phúc có hơn 12 năm kinh nghiệm thiết kế tour Tam Đảo và tư vấn lưu trú theo khu vực.</p>',
            'bio_html_en' => '<p>Born in Vinh Phuc, Van Phuc has 12 years designing Tam Dao tours and advising stays by area.</p>',
            'name_en' => 'Tran Van Phuc', 'role_en' => 'Chief Executive Officer',
            'short_bio_en' => 'Born in Vinh Phuc — 12 years designing Tam Dao tours and stay advice.',
            'achievements' => array('Xây dựng mạng 25+ resort/homestay đối tác trực tiếp', 'Đối tác tour Thác Bạc & VQG'),
            'skills' => array(array('skill' => 'Thiết kế tour núi mát', 'percent' => 96), array('skill' => 'Tư vấn lưu trú theo khu', 'percent' => 94)),
            'experiences' => array(array('title' => 'Giám đốc điều hành', 'company' => 'Hi Tam Đảo', 'items' => array('Chiến lược sản phẩm tour & dịch vụ Tam Đảo'))),
            'degrees' => array(array('title' => 'Cử nhân Quản trị Du lịch', 'school' => 'Học viện Ngoại thương', 'items' => array())),
        ),
        array(
            'slug' => 'nguyen-thi-hoa', 'name' => 'Nguyễn Thị Hoa', 'role' => 'Trưởng phòng thiết kế tour',
            'bio' => 'Chuyên gia tour cuối tuần Hà Nội, tour ẩm thực và lịch trình cặp đôi/trăng mật...',
            'phone' => '+84 211 388 8899', 'email' => 'hoa.nguyen@hitamdao.dev', 'area' => 'Tam Đảo & Hà Nội',
            'years_experience' => 9, 'languages' => array('Tiếng Việt', 'English', '한국어'),
            'stat_clients' => 1900, 'stat_tours' => 380, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Nguyễn Thị Hoa phụ trách sản phẩm cuối tuần, ẩm thực và tour lãng mạn — biết từng mùa sương và hoa anh đào.</p>',
            'bio_html_en' => '<p>Thi Hoa leads weekend, food and romantic itineraries — she knows every fog and cherry blossom season.</p>',
            'name_en' => 'Nguyen Thi Hoa', 'role_en' => 'Head of Tour Design',
            'short_bio_en' => 'Weekend escapes, food tours and honeymoon itineraries specialist.',
            'achievements' => array('Thiết kế tour 2 ngày 1 đêm từ Hà Nội được đánh giá 4.9/5'),
            'skills' => array(array('skill' => 'Tour ẩm thực & food', 'percent' => 95), array('skill' => 'Itinerary lãng mạn', 'percent' => 93)),
            'experiences' => array(array('title' => 'Trưởng phòng thiết kế tour', 'company' => 'Hi Tam Đảo', 'items' => array('Sản phẩm cuối tuần, food, couple'))),
            'degrees' => array(array('title' => 'Cử nhân Địa lý Du lịch', 'school' => 'Đại học Khoa học Tự nhiên', 'items' => array())),
        ),
        array(
            'slug' => 'le-quang-huy', 'name' => 'Lê Quang Huy', 'role' => 'Trưởng đội trekking & an toàn',
            'bio' => 'HLV trekking VQG Tam Đảo, phụ trách Thác Bạc/Dải và đối tác rừng...',
            'phone' => '+84 211 388 8877', 'email' => 'huy.le@hitamdao.dev', 'area' => 'VQG Tam Đảo & Thác Bạc',
            'years_experience' => 10, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 2400, 'stat_tours' => 460, 'stat_awards' => 3, 'is_verified' => true,
            'bio_html' => '<p>Lê Quang Huy là HLV trekking và điều phối an toàn cho mọi hoạt động rừng tại Tam Đảo.</p>',
            'bio_html_en' => '<p>Quang Huy is a trekking coach and safety lead for forest activities in Tam Dao.</p>',
            'name_en' => 'Le Quang Huy', 'role_en' => 'Head of Trekking & Safety',
            'short_bio_en' => 'Certified trekking guide — safety lead for national park and falls trails.',
            'achievements' => array('Hơn 300 chuyến VQG không sự cố nghiêm trọng'),
            'skills' => array(array('skill' => 'Trekking & rescue', 'percent' => 97), array('skill' => 'Điều phối nhóm lớn', 'percent' => 92)),
            'experiences' => array(array('title' => 'Trưởng đội trekking', 'company' => 'Hi Tam Đảo', 'items' => array('An toàn VQG, thác, team-building'))),
            'degrees' => array(array('title' => 'Chứng chỉ HLV trekking', 'school' => 'VQG Tam Đảo', 'items' => array())),
        ),
        array(
            'slug' => 'pham-minh-chau', 'name' => 'Phạm Minh Châu', 'role' => 'Chuyên gia tư vấn cao cấp',
            'bio' => 'Tư vấn khách Hà Nội cuối tuần, combo Ninh Bình và lưu trú resort/homestay...',
            'phone' => '+84 211 388 8866', 'email' => 'chau.pham@hitamdao.dev', 'area' => 'Tam Đảo & Hà Nội',
            'years_experience' => 7, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 1600, 'stat_tours' => 310, 'stat_awards' => 1, 'is_verified' => true,
            'bio_html' => '<p>Phạm Minh Châu là đầu mối tư vấn cho khách Hà Nội — limousine, bay Nội Bài và gợi ý resort theo ngân sách.</p>',
            'bio_html_en' => '<p>Minh Chau advises Hanoi weekend guests — limousines, Noi Bai flights and resort suggestions by budget.</p>',
            'name_en' => 'Pham Minh Chau', 'role_en' => 'Senior Travel Consultant',
            'short_bio_en' => 'Hanoi weekend escapes, combos and resort/homestay advice.',
            'achievements' => array('Tư vấn 400+ kỳ nghỉ cuối tuần HN—Tam Đảo (2022–2026)'),
            'skills' => array(array('skill' => 'Tư vấn khách Hà Nội', 'percent' => 96), array('skill' => 'Combo đa điểm', 'percent' => 91)),
            'experiences' => array(array('title' => 'Chuyên gia tư vấn', 'company' => 'Hi Tam Đảo', 'items' => array('Khách HN & combo Ninh Bình'))),
            'degrees' => array(array('title' => 'Cử nhân Marketing Du lịch', 'school' => 'UEH', 'items' => array())),
        ),
    ),

    'videos' => array(
        array('title' => 'Sương sớm Nhà thờ đá Tam Đảo', 'description' => 'Tam Đảo buổi sáng — nhiệt độ 14°C trong sương.', 'date' => '10/07/2026', 'duration' => '05:20', 'tag' => 'Trung tâm',
            'image' => 'https://i.ytimg.com/vi/TD00000001/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/TD00000001?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'TD00000001'),
        array('title' => 'Thác Bạc trong sương mù', 'description' => 'Thác Bạc buổi sáng — góc chụp từ khách.', 'date' => '22/06/2026', 'duration' => '04:45', 'tag' => 'Thác Bạc',
            'image' => 'https://i.ytimg.com/vi/TD00000002/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/TD00000002?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'TD00000002'),
    ),

    'gallery_albums' => array(
        array('title' => 'Sương mù & hoa anh đào 2026', 'photos' => 22, 'date' => '03/2026'),
        array('title' => 'Tour ẩm thực gà đồi & suối cá', 'photos' => 16, 'date' => '08/2026'),
        array('title' => 'Cặp đôi Cầu Mây Sky Walk', 'photos' => 14, 'date' => '07/2026'),
    ),

    'usps' => array(
        array('icon' => 'compass', 'sort' => 0,
            'vi' => array('title' => 'am hiểu Tam Đảo như người bản địa', 'description' => 'Đội ngũ Vĩnh Phúc biết mùa sương, khu resort phù hợp và lịch thác an toàn.'),




'en' => array('title' => 'true Tam Dao locals', 'description' => 'Our Vinh Phuc team knows fog seasons, the right resort areas and safe falls windows.')),
        array('icon' => 'refund', 'sort' => 1,
            'vi' => array('title' => 'báo giá minh bạch, không phí ẩn', 'description' => 'Giá tour và dịch vụ liệt kê rõ từng hạng mục; lưu trú chọn theo khu và ngân sách.'),




'en' => array('title' => 'transparent pricing, no hidden fees', 'description' => 'Clear line-item quotes for tours and services; stays matched to your area and budget.')),
        array('icon' => 'mountain', 'sort' => 2,
            'vi' => array('title' => 'từ thác đến suối cá & Cầu Mây', 'description' => 'Một đầu mối cho Thác Bạc, tour ẩm thực, thuyền suối cá và combo Ninh Bình.'),




'en' => array('title' => 'falls to fish stream & Cloud Bridge', 'description' => 'One contact for Silver Falls, tour ẩm thựcs, fish-stream boats and Ninh Binh combos.')),
        array('icon' => 'support', 'sort' => 3,
            'vi' => array('title' => 'hỗ trợ 24/7 trên đèo Tam Đảo', 'description' => 'Hotline khi sương mù, đèo kẹt hoặc cần đổi limousine về Hà Nội.'),




'en' => array('title' => '24/7 Tam Dao pass support', 'description' => 'Hotline for fog delays, pass traffic or changing your return limousine to Hanoi.')),
    ),

    'offices' => array(
        array('city' => 'Tam Đảo, Vĩnh Phúc', 'address' => 'Khu du lịch Tam Đảo, huyện Tam Đảo', 'phone' => '+84 211 388 8888'),
        array('city' => 'Hà Nội (đặt tour)', 'address' => 'Quận Ba Đình — văn phòng đại diện', 'phone' => '+84 24 3999 6666'),
    ),

    'values' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm như khách mời tại nhà'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is hosted like a guest at home')),
        array('vi' => array('name' => 'Am hiểu Tam Đảo', 'desc' => 'Gắn Vĩnh Phúc — hiểu từng mùa sương và đèo'),



'en' => array('name' => 'Tam Dao expertise', 'desc' => 'Rooted in Vinh Phuc — we know every mist season and pass')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn đúng nhu cầu — không ép mua thêm dịch vụ'),



'en' => array('name' => 'Sincerity', 'desc' => 'Advice that fits your trip — never upselling you')),
        array('vi' => array('name' => 'An toàn', 'desc' => 'Trekking có HLV và kế hoạch dự phòng khi sương dày'),



'en' => array('name' => 'Safety', 'desc' => 'Trekking with certified guides and fog backup plans')),
    ),
    'value_definitions' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm như khách mời tại nhà'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is hosted like a guest at home')),
        array('vi' => array('name' => 'Am hiểu Tam Đảo', 'desc' => 'Gắn Vĩnh Phúc — hiểu từng mùa sương và đèo'),



'en' => array('name' => 'Tam Dao expertise', 'desc' => 'Rooted in Vinh Phuc — we know every mist season and pass')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn đúng nhu cầu — không ép mua thêm dịch vụ'),



'en' => array('name' => 'Sincerity', 'desc' => 'Advice that fits your trip — never upselling you')),
        array('vi' => array('name' => 'An toàn', 'desc' => 'Trekking có HLV và kế hoạch dự phòng khi sương dày'),



'en' => array('name' => 'Safety', 'desc' => 'Trekking with certified guides and fog backup plans')),
    ),

    'reasons' => array(
        array('vi' => array('title' => 'HDV bản địa Vĩnh Phúc', 'desc' => 'Người địa phương dẫn tour thác, VQG và tư vấn khu resort.'),



'en' => array('title' => 'Local Vinh Phuc guides', 'desc' => 'Locals lead falls, NP tours and resort-area advice.')),
        array('vi' => array('title' => 'Lưu trú đúng khu, đúng ngân sách', 'desc' => 'Resort, homestay — trung tâm, đỉnh đồi, view rừng thông.'),



'en' => array('title' => 'Stays matched to your trip', 'desc' => 'Resorts and homestays — town centre, hilltop, pine forest views.')),
        array('vi' => array('title' => 'Một đầu mối limo + bay + tour', 'desc' => 'Limousine HN, transfer Nội Bài và tour gộp một báo giá.'),



'en' => array('title' => 'One contact: limo, flights, tours', 'desc' => 'HN limousines, Noi Bai transfers and tours in one quote.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7', 'desc' => 'Hotline khi sương mù đèo kẹt hoặc đổi lịch thác.'),



'en' => array('title' => '24/7 support', 'desc' => 'Hotline when fog blocks the pass or falls schedules shift.')),
    ),
    'reason_definitions' => array(
        array('vi' => array('title' => 'HDV bản địa Vĩnh Phúc', 'desc' => 'Người địa phương dẫn tour thác, VQG và tư vấn khu resort.'),



'en' => array('title' => 'Local Vinh Phuc guides', 'desc' => 'Locals lead falls, NP tours and resort-area advice.')),
        array('vi' => array('title' => 'Lưu trú đúng khu, đúng ngân sách', 'desc' => 'Resort, homestay — trung tâm, đỉnh đồi, view rừng thông.'),



'en' => array('title' => 'Stays matched to your trip', 'desc' => 'Resorts and homestays — town centre, hilltop, pine forest views.')),
        array('vi' => array('title' => 'Một đầu mối limo + bay + tour', 'desc' => 'Limousine HN, transfer Nội Bài và tour gộp một báo giá.'),



'en' => array('title' => 'One contact: limo, flights, tours', 'desc' => 'HN limousines, Noi Bai transfers and tours in one quote.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7', 'desc' => 'Hotline khi sương mù đèo kẹt hoặc đổi lịch thác.'),



'en' => array('title' => '24/7 support', 'desc' => 'Hotline when fog blocks the pass or falls schedules shift.')),
    ),

    'reference_persons' => array(
        array('name' => 'Ms. Sarah Kim', 'country' => 'Hàn Quốc', 'email' => 'sarah@hitamdao.example', 'phone' => '+82 10 9876 5432', 'skype' => 'sarah.kim.tamdao', 'image' => null, 'imageSrcset' => null),
        array('name' => 'Mr. Felix Müller', 'country' => 'Đức', 'email' => 'felix@hitamdao.example', 'phone' => '+49 170 1234567', 'skype' => 'felix.muller.travel', 'image' => null, 'imageSrcset' => null),
    ),

    'about_page' => array(
        'vi' => array(
            'seo_title' => 'Về chúng tôi — Hi Tam Đảo, kết nối du khách với Vĩnh Phúc',
            'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ Hi Tam Đảo — tour, dịch vụ và tư vấn lưu trú theo khu vực.',
            'page_title' => 'Về chúng tôi',
            'page_subtitle' => 'Sương mù, rừng thông — thiết kế bởi người bản địa yêu Tam Đảo',
            'banner' => array('src' => null, 'srcset' => null, 'alt' => 'Đội ngũ Hi Tam Đảo'),
            'mission' => array('title' => 'Sứ mệnh', 'text' => 'Mang đến hành trình chân thật trên đồi Tam Đảo — từ thác, suối cá đến Cầu Mây — và giúp mỗi khách chọn tour, dịch vụ lẫn chỗ ở phù hợp trong một hành trình thống nhất.', 'image' => null, 'imageSrcset' => null),
            'vision' => array('title' => 'Tầm nhìn', 'text' => 'Trở thành cầu nối tin cậy nhất giữa du khách Hà Nội và Tam Đảo — nơi mỗi người cảm nhận được nhịp sống núi mát cách phố 80km.', 'image' => null, 'imageSrcset' => null),
            'sales_policy' => array('title' => 'Chính sách minh bạch', 'content' => 'Báo giá tour và dịch vụ liệt kê rõ từng hạng mục. Hỗ trợ đặt lưu trú theo khu và ngân sách. Đổi ngày linh hoạt khi sương mù/trekking hủy vì thời tiết.', 'cta_label' => 'Hỏi thêm', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'values_section' => array('title' => 'Giá trị cốt lõi', 'hub_label' => 'Giá trị', 'eyebrow' => 'Điều chúng tôi tin', 'subtitle' => 'Bốn giá trị dẫn dắt mọi hành trình trên đồi Tam Đảo.'),
            'reasons_section' => array('title' => 'Vì sao chọn Hi Tam Đảo?', 'eyebrow' => 'Lý do', 'subtitle' => 'Bản địa, minh bạch, an toàn trekking.', 'cta_label' => 'Bắt đầu hành trình', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'reference_section' => array('title' => 'Đại diện nước ngoài', 'eyebrow' => 'Mạng lưới', 'subtitle' => 'Trao đổi bằng ngôn ngữ của bạn với đại diện Hi Tam Đảo.'),
        ),




'en' => array(
            'seo_title' => 'About us — Hi Tam Dao, connecting travellers with Vinh Phuc hills',
            'seo_description' => 'Our story, mission and team — tours, services and stay advice by area.',
            'page_title' => 'About us',
            'page_subtitle' => 'Mist and pine forest — designed by locals who love Tam Dao',
            'banner' => array('src' => null, 'srcset' => null, 'alt' => 'Hi Tam Dao team'),
            'mission' => array('title' => 'Mission', 'text' => 'Deliver authentic Tam Dao hill journeys — from falls and fish streams to Cloud Bridge — and help every guest book tours, services and stays in one coherent trip.', 'image' => null, 'imageSrcset' => null),
            'vision' => array('title' => 'Vision', 'text' => 'The most trusted bridge between Hanoi travellers and Tam Dao — where everyone feels the cool mountain rhythm just 80km from the city.', 'image' => null, 'imageSrcset' => null),
            'sales_policy' => array('title' => 'Transparent policy', 'content' => 'Clear line-item quotes for tours and services. Stay booking support by area and budget. Flexible rescheduling when fog or weather cancels trekking.', 'cta_label' => 'Ask us', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'values_section' => array('title' => 'Core values', 'hub_label' => 'Values', 'eyebrow' => 'What we believe', 'subtitle' => 'Four values guiding every Tam Dao itinerary.'),
            'reasons_section' => array('title' => 'Why Hi Tam Dao?', 'eyebrow' => 'Why us', 'subtitle' => 'Local expertise, clear pricing, safe trekking.', 'cta_label' => 'Start your journey', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'reference_section' => array('title' => 'Representatives abroad', 'eyebrow' => 'Network', 'subtitle' => 'Speak in your language with Hi Tam Dao representatives.'),
        ),
    ),

    'hero_pills' => array(
        array('zone_slug' => 'thac-bac-thac-dai', 'vi' => array('label' => 'Thác Bạc & Dải'),



'en' => array('label' => 'Silver Falls'), 'url' => '/diem-den/thac-bac-thac-dai'),
        array('zone_slug' => 'cau-may-sky-walk', 'vi' => array('label' => 'Cầu Mây Sky Walk'),



'en' => array('label' => 'Cloud Bridge'), 'url' => '/diem-den/cau-may-sky-walk'),
    ),

    'home_sections' => array(
        'company_intro' => array(
            'vi' => array('key' => 'company_intro', 'eyebrow' => 'Chuyên gia Tam Đảo', 'title' => 'Thoát urban cuối tuần — cách Hà Nội 80km', 'subtitle' => null,
                'body' => 'Hi Tam Đảo là nền tảng du lịch Tam Đảo do người bản địa xây dựng — <strong class="font-semibold text-ink">một đầu mối</strong> cho tour, vé tham quan, limousine HN và lưu trú từ homestay đến resort. Chúng tôi thiết kế hành trình quanh Thác Bạc, Nhà thờ đá, Cầu Mây và VQG để bạn cảm nhận đúng nhịp sống núi mát.',
                'metaLine' => 'Giấy phép lữ hành số 0046/2024/TCDL-GPLHQT', 'ctaLabel' => 'Về chúng tôi', 'ctaUrl' => '/ve-chung-toi', 'image' => null, 'imageAlt' => 'Hi Tam Đảo'),




'en' => array('key' => 'company_intro', 'eyebrow' => 'Tam Dao experts', 'title' => 'Cuối tuần escape — 80km from Hanoi', 'subtitle' => null,
                'body' => 'Hi Tam Dao is a locally built Tam Dao travel platform — <strong class="font-semibold text-ink">one place</strong> for tours, tickets, Hanoi limousines and stays from homestays to resorts. We craft itineraries around Silver Falls, Stone Church, Cloud Bridge and the national park.',
                'metaLine' => 'Travel license No. 0046/2024/TCDL-GPLHQT', 'ctaLabel' => 'About us', 'ctaUrl' => '/ve-chung-toi', 'image' => null, 'imageAlt' => 'Hi Tam Dao'),
        ),
        'featured_tours' => array(
            'vi' => array('key' => 'featured_tours', 'eyebrow' => 'Yêu thích', 'title' => 'Tour được đặt nhiều nhất', 'subtitle' => 'Hành trình khách đánh giá cao trong 12 tháng qua.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_tours', 'eyebrow' => 'Popular', 'title' => 'Most booked tours', 'subtitle' => 'Top-rated itineraries over the past 12 months.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'featured_cruises' => array(
            'vi' => array('key' => 'featured_cruises', 'eyebrow' => 'Suối cá & hồ núi', 'title' => 'Thuyền suối cá & picnic hồ', 'subtitle' => 'Suối cá ăn tươi, picnic sáng sớm — trải nghiệm nước đặc trưng Tam Đảo.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_cruises', 'eyebrow' => 'Fish stream & lake', 'title' => 'Fish-stream boats & lake picnics', 'subtitle' => 'Fresh stream fish and dawn picnics — Tam Dao\'s đặc trưng water experiences.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'featured_trains' => array(
            'vi' => array('key' => 'featured_trains', 'eyebrow' => 'Di chuyển', 'title' => 'Limousine & xe Hà Nội — Tam Đảo', 'subtitle' => 'Từ Hà Nội ~80km — một đầu mối đặt vé và đưa đón.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_trains', 'eyebrow' => 'Getting there', 'title' => 'Limousines & coaches Hanoi — Tam Dao', 'subtitle' => '~80km from Hanoi — one place to book transfers.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'support_services' => array(
            'vi' => array('key' => 'support_services', 'eyebrow' => 'Dịch vụ', 'title' => 'Vé, trải nghiệm & hỗ trợ', 'subtitle' => 'Thác Bạc, Cầu Mây, tour ẩm thực và tiện ích trên đồi.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'support_services', 'eyebrow' => 'Services', 'title' => 'Tickets, experiences & support', 'subtitle' => 'Silver Falls, Cloud Bridge, tour ẩm thựcs and hill extras.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'destinations' => array(
            'vi' => array('key' => 'destinations', 'eyebrow' => 'Khắp Tam Đảo', 'title' => 'Khu vực được yêu thích', 'subtitle' => 'Trung tâm, Thác Bạc, Cầu Mây, VQG và combo Ninh Bình.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'destinations', 'eyebrow' => 'Across Tam Dao', 'title' => 'Favourite areas', 'subtitle' => 'Town centre, Silver Falls, Cloud Bridge, NP and Ninh Binh combos.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'testimonials' => array(
            'vi' => array('key' => 'testimonials', 'eyebrow' => 'Khách kể', 'title' => 'Trải nghiệm thật', 'subtitle' => 'Hơn 2.800 khách đã đồng hành cùng Hi Tam Đảo.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Xem cảm nhận', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'testimonials', 'eyebrow' => 'Guest stories', 'title' => 'Real experiences', 'subtitle' => 'Over 2,800 guests have travelled with Hi Tam Dao.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'All reviews', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => null, 'imageAlt' => null),
        ),
        'review_platforms' => array(
            'vi' => array('key' => 'review_platforms', 'eyebrow' => null, 'title' => 'Hi Tam Đảo được đánh giá cao trên', 'subtitle' => null, 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'review_platforms', 'eyebrow' => null, 'title' => 'Hi Tam Dao is highly rated on', 'subtitle' => null, 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'team' => array(
            'vi' => array('key' => 'team', 'eyebrow' => 'Con người Hi Tam Đảo', 'title' => 'Đội ngũ bản địa', 'subtitle' => 'Cùng bạn từ ý tưởng đến khi rời đèo Tam Đảo.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Gặp đội ngũ', 'ctaUrl' => '/doi-ngu', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'team', 'eyebrow' => 'The Hi Tam Dao team', 'title' => 'Local experts', 'subtitle' => 'With you from the first idea until you leave the Tam Dao pass.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Meet the team', 'ctaUrl' => '/doi-ngu', 'image' => null, 'imageAlt' => null),
        ),
        'videos' => array(
            'vi' => array('key' => 'videos', 'eyebrow' => 'Video', 'title' => 'Tam Đảo qua ống kính', 'subtitle' => 'Sương, thác và rừng thông — clip từ khách và đội ngũ.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Xem video', 'ctaUrl' => '/video-trai-nghiem', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'videos', 'eyebrow' => 'Video', 'title' => 'Tam Dao on film', 'subtitle' => 'Mist, falls and pine forest — from guests and our team.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'All videos', 'ctaUrl' => '/video-trai-nghiem', 'image' => null, 'imageAlt' => null),
        ),
        'quick_inquiry' => array(
            'vi' => array('key' => 'quick_inquiry', 'eyebrow' => 'Tư vấn miễn phí', 'title' => 'Gửi lời nhắn', 'subtitle' => null,
                'body' => 'Chưa chọn tour hay khu resort? Phản hồi trong <strong class="font-semibold text-ink">24 giờ làm việc</strong>, miễn phí.', 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'quick_inquiry', 'eyebrow' => 'Free advice', 'title' => 'Send a message', 'subtitle' => null,
                'body' => 'Not sure which tour or resort area? Reply within <strong class="font-semibold text-ink">1 business day</strong>, free of charge.', 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
    ),

    'footer_columns' => array(
        array('title' => 'Hi Tam Đảo', 'links' => array(
            array('label' => 'Về chúng tôi', 'route' => array('about')),
            array('label' => 'Cảm nhận khách hàng', 'route' => array('reviews')),
            array('label' => 'Đội ngũ', 'route' => array('team')),
            array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
        )),
        array('title' => 'Tour phổ biến', 'links' => array(
            array('label' => 'Tam Đảo 2 ngày 1 đêm cuối tuần', 'route' => array('tours.show', array('zone' => 'ket-hop-ha-noi', 'slug' => 'tam-dao-2n1d-cuoi-tuan-ha-noi'))),
            array('label' => 'Thác Bạc 1 ngày', 'route' => array('tours.show', array('zone' => 'thac-bac-thac-dai', 'slug' => 'thac-bac-thac-dai-1-ngay'))),
            array('label' => 'Tam Đảo 3 ngày 2 đêm tổng quan', 'route' => array('tours.show', array('zone' => 'trung-tam-thi-tran', 'slug' => 'tam-dao-3n2d-tong-quan'))),
            array('label' => 'Tour ẩm thực ẩm thực', 'route' => array('tours.show', array('zone' => 'suoi-ca-am-thuc', 'slug' => 'food-tour-am-thuc-tam-dao'))),
        )),
        array('title' => 'Khu vực', 'links' => array(
            array('label' => 'Thác Bạc & Dải', 'route' => array('guide.zone', array('zone' => 'thac-bac-thac-dai'))),
            array('label' => 'Cầu Mây Sky Walk', 'route' => array('guide.zone', array('zone' => 'cau-may-sky-walk'))),
            array('label' => 'Suối cá', 'route' => array('cruises.index', array('type' => 'thuyen-suoi-ca'))),
        )),
        array('title' => 'Cẩm nang', 'links' => array(
            array('label' => 'Từ Hà Nội đi Tam Đảo', 'route' => array('guide.show', array('zone' => 'ket-hop-ha-noi', 'slug' => 'tu-ha-noi-di-tam-dao-the-nao'))),
            array('label' => 'Mùa nào đẹp?', 'route' => array('guide.show', array('zone' => 'trung-tam-thi-tran', 'slug' => 'tam-dao-mua-nao-dep-nhat'))),
            array('label' => 'Ăn gì ở Tam Đảo', 'route' => array('guide.show', array('zone' => 'suoi-ca-am-thuc', 'slug' => 'an-gi-o-tam-dao'))),
        )),
    ),

    'footer_seo_links' => array(
        array('label' => 'Cẩm nang Tam Đảo', 'route' => array('guide.zone', array('zone' => 'trung-tam-thi-tran'))),
        array('label' => 'Tour Tam Đảo trọn gói', 'route' => array('tours.index', array('zone' => 'trung-tam-thi-tran'))),
        array('label' => 'Thuyền suối cá', 'route' => array('cruises.index', array('type' => 'thuyen-suoi-ca'))),
        array('label' => 'Limousine Hà Nội Tam Đảo', 'route' => array('services.hub', array('cluster' => 'train'))),
        array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
    ),




    'tour_categories' => array(
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'tour-trung-tam-thi-tran',
            'type' => 'region',
            'sort' => 0,
            'packageSlugs' => array(
                'tam-dao-3n2d-tong-quan',
                'gia-dinh-tam-dao-2n1d',
                'city-tour-tam-dao-1-ngay',
            ),
            'name' => array(
                'vi' => 'Tour Trung tâm thị trấn',




'en' => 'Town centre tours',
            ),
            'subtitle' => array(
                'vi' => 'Nhà thờ đá, chợ đêm, quán ven đồi & di sản Pháp.',




'en' => 'Stone church, night market, hillside cafes & French heritage.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục khu vực trung tâm Tam Đảo — nơi lưu trú và khởi hành của mọi tuyến.',




'en' => 'Tam Dao town GEO category — where guests stay and every route starts.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'thac-bac-thac-dai',
            'slug' => 'tour-thac-bac-thac-dai',
            'type' => 'region',
            'sort' => 1,
            'packageSlugs' => array(
                'thac-bac-thac-dai-1-ngay',
                'photo-tour-suong-mu-tam-dao',
            ),
            'name' => array(
                'vi' => 'Tour Thác Bạc & Thác Dải',




'en' => 'Silver Falls & Dai Falls tours',
            ),
            'subtitle' => array(
                'vi' => 'Thác nước giữa rừng thông — điểm chụp sương iconic.',




'en' => 'Waterfalls in the pine forest — the iconic misty photo stop.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục khu vực thác — 200 bậc xuống Thác Bạc, đi được quanh năm.',




'en' => 'Waterfall GEO category — 200 steps down to Silver Falls, walkable year-round.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'cau-may-sky-walk',
            'slug' => 'tour-cau-may-sky-walk',
            'type' => 'region',
            'sort' => 2,
            'packageSlugs' => array(
                'cau-may-sky-walk-nua-ngay',
            ),
            'name' => array(
                'vi' => 'Tour Cầu Mây & Sky Walk',




'en' => 'Cloud Bridge & Sky Walk tours',
            ),
            'subtitle' => array(
                'vi' => 'Cầu treo mây, Sky Walk view rừng — check-in hot.',




'en' => 'Cloud suspension bridge and forest-view Sky Walk — the hot check-in.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục khu vực Cầu Mây — cụm check-in nửa ngày ngay cạnh thị trấn.',




'en' => 'Cloud Bridge GEO category — the half-day check-in cluster next to town.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'vqg-rung-thong',
            'slug' => 'tour-vqg-rung-thong',
            'type' => 'region',
            'sort' => 3,
            'packageSlugs' => array(
                'trekking-vqg-tam-dao-1-ngay',
                'tam-dao-4n3d-kham-pha-sau',
            ),
            'name' => array(
                'vi' => 'Tour VQG Tam Đảo & rừng thông',




'en' => 'Tam Dao National Park & pine forest tours',
            ),
            'subtitle' => array(
                'vi' => 'Trekking, chim hoang dã & không khí trong lành.',




'en' => 'Trekking, wild birds & clean mountain air.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục khu vực vườn quốc gia — tuyến trek có hướng dẫn viên bản địa.',




'en' => 'National park GEO category — guided trekking routes with local rangers.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'khu-resort-dinh-cao',
            'slug' => 'tour-khu-resort-dinh-cao',
            'type' => 'region',
            'sort' => 4,
            'packageSlugs' => array(
                'tam-dao-2n1d-lang-man',
                'team-building-tam-dao-2n1d',
            ),
            'name' => array(
                'vi' => 'Tour Khu resort trên đỉnh',




'en' => 'Hilltop resort tours',
            ),
            'subtitle' => array(
                'vi' => 'Resort, villa view sương — nghỉ dưỡng trên đồi.',




'en' => 'Resorts and mist-view villas — hilltop retreats.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục khu vực resort đỉnh — phòng họp, gala và villa cho đoàn.',




'en' => 'Hilltop resort GEO category — meeting rooms, gala space and group villas.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'suoi-ca-am-thuc',
            'slug' => 'tour-suoi-ca-am-thuc',
            'type' => 'region',
            'sort' => 5,
            'packageSlugs' => array(
                'food-tour-am-thuc-tam-dao',
            ),
            'name' => array(
                'vi' => 'Tour Suối cá & ẩm thực',




'en' => 'Fish stream & food tours',
            ),
            'subtitle' => array(
                'vi' => 'Suối cá, gà đồi, dê núi, chay tỏi & rượu mật ong.',




'en' => 'Fish stream, hill chicken, mountain goat, garlic greens & honey wine.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục khu vực suối cá — cụm quán đặc sản dọc suối phía dưới thị trấn.',




'en' => 'Fish stream GEO category — the specialty eateries along the stream below town.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ket-hop-ha-noi',
            'slug' => 'combo-ha-noi',
            'type' => 'region',
            'sort' => 6,
            'packageSlugs' => array(
                'tam-dao-2n1d-cuoi-tuan-ha-noi',
                'ha-noi-tam-dao-tour-ngay',
            ),
            'name' => array(
                'vi' => 'Combo Hà Nội',




'en' => 'Hanoi combo',
            ),
            'subtitle' => array(
                'vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — limo ~80km.',




'en' => 'Day trips and 2N1D weekends from Hanoi — limo ~80km.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục combo cửa ngõ — trọn gói limousine hai chiều kèm tour Tam Đảo.',




'en' => 'Gateway combo category — return limousine bundled with Tam Dao tours.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ket-hop-ninh-binh',
            'slug' => 'combo-ninh-binh',
            'type' => 'region',
            'sort' => 7,
            'packageSlugs' => array(
                'combo-tam-dao-ninh-binh-3n2d',
            ),
            'name' => array(
                'vi' => 'Combo Ninh Bình',




'en' => 'Ninh Binh combo',
            ),
            'subtitle' => array(
                'vi' => 'Tràng An + Tam Đảo — núi mát sau di sản.',




'en' => 'Trang An plus Tam Dao — cool hills after the heritage sites.',
            ),
            'seo_body' => array(
                'vi' => 'Danh mục combo liên vùng — ghép di sản Ninh Bình với hai đêm nghỉ đồi.',




'en' => 'Multi-region combo category — pair Ninh Binh heritage with two nights in the hills.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'tour-trong-ngay',
            'type' => 'theme',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'city-tour-tam-dao-1-ngay',
                'thac-bac-thac-dai-1-ngay',
                'cau-may-sky-walk-nua-ngay',
                'trekking-vqg-tam-dao-1-ngay',
                'food-tour-am-thuc-tam-dao',
                'ha-noi-tam-dao-tour-ngay',
                'photo-tour-suong-mu-tam-dao',
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
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'tour-2-ngay-1-dem',
            'type' => 'theme',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 2,
            'packageSlugs' => array(
                'tam-dao-2n1d-cuoi-tuan-ha-noi',
                'tam-dao-2n1d-lang-man',
                'gia-dinh-tam-dao-2n1d',
                'team-building-tam-dao-2n1d',
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
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'tour-3-ngay-2-dem',
            'type' => 'theme',
            'sort' => 2,
            'minDays' => 3,
            'maxDays' => 3,
            'packageSlugs' => array(
                'tam-dao-3n2d-tong-quan',
                'combo-tam-dao-ninh-binh-3n2d',
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
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'tour-4-ngay-3-dem',
            'type' => 'theme',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 4,
            'packageSlugs' => array(
                'tam-dao-4n3d-kham-pha-sau',
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
            'zoneSlug' => 'trung-tam-thi-tran',
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
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'cuoi-tuan-ha-noi',
            'type' => 'theme',
            'sort' => 10,
            'packageSlugs' => array(
                'tam-dao-2n1d-cuoi-tuan-ha-noi',
                'ha-noi-tam-dao-tour-ngay',
                'tam-dao-2n1d-lang-man',
            ),
            'name' => array(
                'vi' => 'Cuối tuần từ Hà Nội',




'en' => 'Weekend from Hanoi',
            ),
            'subtitle' => array(
                'vi' => 'Limousine 2–2,5 giờ, 2 ngày 1 đêm và tour trong ngày.',




'en' => '2–2.5h limo, 2N1D & day trips.',
            ),
            'seo_body' => array(
                'vi' => 'Trang riêng cuối tuần từ Hà Nội — không trùng danh mục thị trấn. Combo limo + Thác Bạc phổ biến dân văn phòng (~80km).',




'en' => 'Separate intent URL — not the town GEO zone. Limo + Silver Falls combo is the classic Hanoi office-worker break (~80km).',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'am-thuc-doi-ga',
            'type' => 'theme',
            'sort' => 11,
            'packageSlugs' => array(
                'food-tour-am-thuc-tam-dao',
            ),
            'name' => array(
                'vi' => 'Ẩm thực gà đồi & suối cá',




'en' => 'Hill chicken & fish stream',
            ),
            'subtitle' => array(
                'vi' => 'Gà đồi, suối cá, chay tỏi, rượu mật ong.',




'en' => 'Hill chicken, fish stream, garlic veg, honey wine.',
            ),
            'seo_body' => array(
                'vi' => 'Foodie intent Tam Đảo — không trùng danh mục khu vực suối cá.',




'en' => 'Tam Dao foodie intent — not the fish-stream GEO category.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'trekking-vqg',
            'type' => 'theme',
            'sort' => 12,
            'packageSlugs' => array(
                'trekking-vqg-tam-dao-1-ngay',
                'tam-dao-4n3d-kham-pha-sau',
                'team-building-tam-dao-2n1d',
            ),
            'name' => array(
                'vi' => 'Trekking & VQG',




'en' => 'Trekking & national park',
            ),
            'subtitle' => array(
                'vi' => 'Rừng thông, chim, picnic.',




'en' => 'Pine forest, birds, picnic.',
            ),
            'seo_body' => array(
                'vi' => 'Tour phiêu lưu — HLV địa phương, không xả rác trong rừng.',




'en' => 'Adventure intent — local guides, leave no trace in the forest.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'suong-mu-lang-man',
            'type' => 'theme',
            'sort' => 13,
            'packageSlugs' => array(
                'tam-dao-2n1d-lang-man',
                'photo-tour-suong-mu-tam-dao',
            ),
            'name' => array(
                'vi' => 'Sương mù & lãng mạn',




'en' => 'Mist & romance',
            ),
            'subtitle' => array(
                'vi' => 'Resort sương, Cầu Mây, photo tour.',




'en' => 'Mist resort, Cloud Bridge, photo tour.',
            ),
            'seo_body' => array(
                'vi' => 'Tour lãng mạn — kỳ nghỉ ngắn không cần bay xa từ Hà Nội.',




'en' => 'Romance intent — mini honeymoon without flying far from Hanoi.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'gia-dinh-tre-em',
            'type' => 'theme',
            'sort' => 14,
            'packageSlugs' => array(
                'gia-dinh-tam-dao-2n1d',
                'tam-dao-3n2d-tong-quan',
            ),
            'name' => array(
                'vi' => 'Gia đình có trẻ em',




'en' => 'Family with kids',
            ),
            'subtitle' => array(
                'vi' => 'Thác & suối cá — lịch nhẹ trẻ em.',




'en' => 'Falls & fish stream — kid-friendly pace.',
            ),
            'seo_body' => array(
                'vi' => 'Tour gia đình — không trek nặng, ưu tiên suối cá và phố đi bộ.',




'en' => 'Family intent — no heavy trekking; fish stream and town walks.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-thi-tran',
            'slug' => 'team-building-mice',
            'type' => 'theme',
            'sort' => 15,
            'packageSlugs' => array(
                'team-building-tam-dao-2n1d',
            ),
            'name' => array(
                'vi' => 'Team-building & MICE',




'en' => 'Team building & MICE',
            ),
            'subtitle' => array(
                'vi' => 'Resort + game rừng + gala.',




'en' => 'Resort + forest games + gala.',
            ),
            'seo_body' => array(
                'vi' => 'Doanh nghiệp Hà Nội — limo đoàn, phòng họp, trek nhóm nhẹ.',




'en' => 'Hanoi corporates — group limo, meeting room, light group trek.',
            ),
            'faqs' => array(),
        ),
    ),
    'listing_faqs' => array(
        array('q' => 'Tour có bao gồm khách sạn không?', 'a' => 'Hầu hết tour chưa gồm lưu trú trong giá. Bạn có thể đặt resort hoặc homestay qua :brand — tư vấn viên gợi ý theo khu (trung tâm, đỉnh đồi, view rừng) và ngân sách.'),
        array('q' => 'Tam Đảo lạnh không, cần mang gì?', 'a' => 'Nhiệt độ thường 18–26°C, tối có thể 12–15°C — áo khoác mỏng, giày chống trượt nếu đi Thác Bạc/trekking VQG.'),
        array('q' => 'Limousine HN hay bay Nội Bài?', 'a' => 'Limousine ~80km, 2–2.5h — phổ biến nhất cho khách HN cuối tuần. Bay Nội Bài + transfer ~2.5–3h — tiện cho khách tỉnh xa.'),
        array('q' => 'Sương mù có hủy tour không?', 'a' => 'Thác/Cầu Mây có thể đổi slot — limo đèo kẹt :brand hỗ trợ đổi giờ miễn phí khi an toàn.'),
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
        'suong-mu-lang-man' => 'Sương mù & lãng mạn',
        'trekking-vqg' => 'Trekking & VQG',
        'am-thuc-doi-ga' => 'Ẩm thực gà đồi & suối cá',
        'team-building-mice' => 'Team-building & MICE',
        'gia-dinh' => 'Gia đình có trẻ em',
    ),
);

$__servicesSeed = [
    'service_clusters' => [
        ['code' => 'train', 'nav_label' => 'Di chuyển', 'label' => 'Limousine & xe Hà Nội — Tam Đảo', 'icon' => 'train', 'hub_key' => 'trains_hub', 'sort' => 1],
        ['code' => 'flight', 'nav_label' => 'Máy bay & đưa đón', 'label' => 'Nội Bài (Hà Nội) & transfer Tam Đảo', 'icon' => 'plane', 'hub_key' => 'flights_hub', 'sort' => 2],
        ['code' => 'stay', 'nav_label' => 'Lưu trú', 'label' => 'Resort & homestay Tam Đảo', 'icon' => 'building', 'hub_key' => 'stays_hub', 'sort' => 3],
        ['code' => 'experience', 'nav_label' => 'Vui chơi', 'label' => 'Thác, Cầu Mây & tour ngày', 'icon' => 'sparkles', 'hub_key' => 'experiences_hub', 'sort' => 4],
        ['code' => 'other', 'nav_label' => 'Dịch vụ', 'label' => 'Hỗ trợ & tiện ích Tam Đảo', 'icon' => 'briefcase', 'hub_key' => 'extras_hub', 'sort' => 5],
    ],
    'service_categories' => [
        ['cluster' => 'train', 'slug' => 'limousine-ha-noi-tam-dao', 'name' => 'Limousine Hà Nội ↔ Tam Đảo', 'sort' => 1, 'intro' => 'Xe 9–16 chỗ, ~80km, 2–2.5 giờ.', 'seo_body' => 'Cửa ngõ chính từ HN — :brand giá 180–280k/chiều. Không có sân bay tại Tam Đảo.'],
        ['cluster' => 'train', 'slug' => 'xe-khach-ha-noi-tam-dao', 'name' => 'Xe khách Hà Nội ↔ Tam Đảo', 'sort' => 2, 'intro' => 'Open bus / xe tuyến — giá rẻ hơn limo.', 'seo_body' => 'My Đình, Giáp Bát — tự taxi lên đồi.'],
        ['cluster' => 'train', 'slug' => 'xe-vinh-phuc-ninh-binh', 'name' => 'Xe Vĩnh Phúc & Ninh Bình ↔ Tam Đảo', 'sort' => 3, 'intro' => 'Kết nối combo & địa phương.', 'seo_body' => 'Nền tảng combo Tràng An + Tam Đảo.'],
        ['cluster' => 'train', 'slug' => 'xe-rieng-charter', 'name' => 'Xe riêng & charter', 'sort' => 4, 'intro' => '4–16 chỗ theo ngày.', 'seo_body' => 'Đoàn MICE, gia đình — lịch linh hoạt trên đèo.'],
        ['cluster' => 'train', 'slug' => 'xe-don-tam-dao', 'name' => 'Xe đón bến & nội bộ Tam Đảo', 'sort' => 5, 'intro' => 'Đón chân đèo → resort trên đồi.', 'seo_body' => 'Tiện khi mang vali hoặc sương mù dày.'],
        ['cluster' => 'flight', 'slug' => 'noi-bai-transfer', 'name' => 'Transfer Nội Bài (Hà Nội) ↔ Tam Đảo', 'sort' => 1, 'intro' => 'Bay HAN + xe ~2.5–3h lên đồi.', 'seo_body' => 'Khách quốc tế/nội địa bay HN — gộp transfer qua :brand.'],
        ['cluster' => 'flight', 'slug' => 'dua-don-noi-bai', 'name' => 'Đưa đón sân bay Nội Bài đón tận nơi', 'sort' => 2, 'intro' => 'Xe riêng Hà Nội ↔ Hà Nội/Tam Đảo.', 'seo_body' => ':brand canh chuyến bay.'],
        ['cluster' => 'flight', 'slug' => 'combo-bay-transfer-tam-dao', 'name' => 'Combo vé bay Hà Nội + transfer Tam Đảo', 'sort' => 3, 'intro' => 'Một báo giá bay + xe lên đồi.', 'seo_body' => 'Tiện khách lần đầu — không tự ghép.'],
        ['cluster' => 'stay', 'slug' => 'resort-tren-dinh', 'name' => 'Resort & villa trên đỉnh', 'sort' => 1, 'intro' => 'View sương, nghỉ cuối tuần.', 'seo_body' => 'Catalogue lưu trú có thể bổ sung từ tool cào — liên hệ :brand tư vấn theo khu.'],
        ['cluster' => 'stay', 'slug' => 'homestay-thi-tran', 'name' => 'Homestay & khách sạn thị trấn', 'sort' => 2, 'intro' => 'Gần nhà thờ đá & chợ đêm.', 'seo_body' => 'Phù hợp budget & đi bộ phố núi.'],
        ['cluster' => 'stay', 'slug' => 'resort-view-rung', 'name' => 'Resort view rừng thông', 'sort' => 3, 'intro' => 'Khu resort cao — lạnh hơn trung tâm.', 'seo_body' => 'Mùa sương Nov–Feb — đặt sớm cuối tuần.'],
        ['cluster' => 'experience', 'slug' => 'thac-bac-dai', 'name' => 'Thác Bạc & Thác Dải', 'sort' => 1, 'intro' => 'Vé & tour nửa/ngày.', 'seo_body' => 'Signature Tam Đảo — sương sớm đẹp nhất.'],
        ['cluster' => 'experience', 'slug' => 'cau-may-sky-walk', 'name' => 'Cầu Mây & Sky Walk', 'sort' => 2, 'intro' => 'Vé check-in rừng thông.', 'seo_body' => 'Khác zone thác — URL riêng chống cannibalization.'],
        ['cluster' => 'experience', 'slug' => 'trekking-vqg', 'name' => 'Trekking VQG Tam Đảo', 'sort' => 3, 'intro' => 'HLV & vé công viên.', 'seo_body' => 'Rừng thông, chim hoang dã — biết bơi không bắt buộc.'],
        ['cluster' => 'experience', 'slug' => 'am-thuc-dac-san', 'name' => 'Ẩm thực & suối cá', 'sort' => 4, 'intro' => 'Gà đồi, dê, chay tỏi, rượu mật ong.', 'seo_body' => 'Food tour tối — khác tour city.'],
        ['cluster' => 'experience', 'slug' => 'city-tour-nha-tho', 'name' => 'City tour & nhà thờ đá', 'sort' => 5, 'intro' => 'Di sản Pháp & phố núi.', 'seo_body' => 'Must-do lần đầu lên đồi.'],
        ['cluster' => 'experience', 'slug' => 'thuyen-suoi-ho', 'name' => 'Thuyền suối cá & hồ picnic', 'sort' => 6, 'intro' => 'Trải nghiệm nước nhỏ — không biển.', 'seo_body' => 'Xem mục du thuyền — suối cá, picnic hồ.'],
        ['cluster' => 'other', 'slug' => 'huong-dan-rieng', 'name' => 'HDV riêng & photo tour', 'sort' => 1, 'intro' => 'Guide tiếng Anh, sương mù.', 'seo_body' => 'Pre-wedding & couple photo trên đồi.'],
        ['cluster' => 'other', 'slug' => 'team-building-mice', 'name' => 'Team-building & MICE', 'sort' => 2, 'intro' => 'Resort + game rừng + gala.', 'seo_body' => 'Gói 15–40 người từ Hà Nội.'],
        ['cluster' => 'other', 'slug' => 'xe-tien-ich', 'name' => 'Xe có lái, thuê xe máy', 'sort' => 3, 'intro' => 'Xe máy, ô tô có tài trên đèo.', 'seo_body' => 'Cẩn thận sương mù — khuyên xe có lái.'],
        ['cluster' => 'other', 'slug' => 'ho-tro-dac-biet', 'name' => 'Hotline 24/7 & đổi lịch sương mù', 'sort' => 4, 'intro' => 'Đổi limo/tour khi đèo kẹt.', 'seo_body' => 'Miễn phí khách đặt qua :brand.'],
    ],
    'services' => [
        ['code' => 'train-limo-hn-td-oneway', 'cluster' => 'train', 'category_slug' => 'limousine-ha-noi-tam-dao', 'title' => 'Limousine Hà Nội → Tam Đảo (một chiều)', 'slug' => 'limousine-ha-noi-tam-dao-mot-chieu', 'price_from' => 220000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 1240, 'is_featured' => true, 'is_hot_deal' => true, 'location_label' => 'Hà Nội → Tam Đảo', 'summary' => '2–2.5 giờ, đón Cầu Giấy/Ba Đình/Hoàn Kiếm.', 'highlights' => ['180–280k tham khảo', '9–16 chỗ', 'Cuối tuần phổ biến'], 'inclusions' => ['Limousine một chiều'], 'exclusions' => ['Đón xa trung tâm'], 'notes' => ['Đặt trước T6–CN.'], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Tam Đảo', 'duration_hours' => 2.25, 'vehicle_type' => 'limousine']],
        ['code' => 'train-limo-hn-td-round', 'cluster' => 'train', 'category_slug' => 'limousine-ha-noi-tam-dao', 'title' => 'Limousine khứ hồi Hà Nội ↔ Tam Đảo', 'slug' => 'limousine-khu-hoi-ha-noi-tam-dao', 'price_from' => 400000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 890, 'is_featured' => true, 'location_label' => 'Hà Nội ↔ Tam Đảo', 'summary' => 'Cuối tuần 2 ngày 1 đêm — tiết kiệm hơn 2 chiều lẻ.', 'highlights' => ['Khứ hồi', 'Giữ chỗ chiều CN'], 'inclusions' => ['2 chiều limousine'], 'exclusions' => [], 'notes' => [], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Tam Đảo', 'vehicle_type' => 'limousine']],
        ['code' => 'train-bus-hn-td', 'cluster' => 'train', 'category_slug' => 'xe-khach-ha-noi-tam-dao', 'title' => 'Xe khách Hà Nội → Tam Đảo', 'slug' => 'xe-khach-ha-noi-tam-dao', 'price_from' => 120000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 456, 'is_featured' => false, 'location_label' => 'Hà Nội → Tam Đảo', 'summary' => 'Giá rẻ — tự taxi từ chân đèo lên thị trấn.', 'highlights' => ['Open bus'], 'inclusions' => ['Vé một chiều'], 'exclusions' => ['Xe lên đồi'], 'notes' => [], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Tam Đảo', 'duration_hours' => 2.5, 'vehicle_type' => 'xe khách']],
        ['code' => 'train-nb-td', 'cluster' => 'train', 'category_slug' => 'xe-vinh-phuc-ninh-binh', 'title' => 'Xe Ninh Bình → Tam Đảo', 'slug' => 'xe-ninh-binh-tam-dao', 'price_from' => 350000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 89, 'is_featured' => true, 'location_label' => 'Ninh Bình → Tam Đảo', 'summary' => '~2.5h — combo Tràng An + đồi mát.', 'highlights' => ['Combo ĐBSH'], 'inclusions' => ['Xe 4–7 chỗ'], 'exclusions' => [], 'notes' => [], 'attrs' => ['from' => 'Ninh Bình', 'to' => 'Tam Đảo', 'duration_hours' => 2.5]],
        ['code' => 'train-charter-hn-td', 'cluster' => 'train', 'category_slug' => 'xe-rieng-charter', 'title' => 'Xe riêng Hà Nội — Tam Đảo (4–16 chỗ)', 'slug' => 'xe-rieng-ha-noi-tam-dao', 'price_from' => 2200000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 67, 'is_featured' => true, 'location_label' => 'Hà Nội → Tam Đảo', 'summary' => 'Lịch linh hoạt — dừng chân đèo tự do.', 'highlights' => ['Private', 'MICE'], 'inclusions' => ['Xe + tài xế'], 'exclusions' => ['Cầu đường'], 'notes' => [], 'attrs' => ['vehicle_type' => '4–16 chỗ']],
        ['code' => 'train-pickup-hill', 'cluster' => 'train', 'category_slug' => 'xe-don-tam-dao', 'title' => 'Xe đón chân đèo → resort Tam Đảo', 'slug' => 'xe-don-chan-deo-tam-dao', 'price_from' => 150000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 312, 'is_featured' => true, 'location_label' => 'Chân đèo → đồi', 'summary' => 'Đón điểm xe khách/limo xuống — lên thị trấn.', 'highlights' => ['Bảng tên'], 'inclusions' => ['Xe một chiều'], 'exclusions' => [], 'notes' => [], 'attrs' => ['service_type' => 'hill_transfer']],
        ['code' => 'train-vp-city', 'cluster' => 'train', 'category_slug' => 'xe-vinh-phuc-ninh-binh', 'title' => 'Xe Vĩnh Yên / Vĩnh Phúc → Tam Đảo', 'slug' => 'xe-vinh-yen-tam-dao', 'price_from' => 180000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 98, 'is_featured' => false, 'location_label' => 'Vĩnh Phúc → Tam Đảo', 'summary' => '~1h — gần hơn Hà Nội.', 'highlights' => ['Địa phương'], 'inclusions' => ['Xe một chiều'], 'exclusions' => [], 'notes' => [], 'attrs' => ['from' => 'Vĩnh Yên', 'to' => 'Tam Đảo', 'duration_hours' => 1]],
        ['code' => 'flight-han-td-transfer', 'cluster' => 'flight', 'category_slug' => 'noi-bai-transfer', 'title' => 'Transfer Nội Bài (Hà Nội) → Tam Đảo', 'slug' => 'transfer-noi-bai-tam-dao', 'price_from' => 650000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Hà Nội → Tam Đảo', 'summary' => 'Bay Hà Nội + xe ~2.5–3h lên đồi — theo dõi flight.', 'highlights' => ['Door-to-resort'], 'inclusions' => ['Xe 4–7 chỗ'], 'exclusions' => ['Vé bay'], 'notes' => ['Gửi số hiệu chuyến bay.'], 'attrs' => ['from' => 'HAN', 'to' => 'Tam Đảo', 'duration_hours' => 3]],
        ['code' => 'flight-han-door', 'cluster' => 'flight', 'category_slug' => 'dua-don-noi-bai', 'title' => 'Đưa đón Nội Bài ↔ Hà Nội nội thành', 'slug' => 'dua-don-noi-bai-ha-noi', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 189, 'is_featured' => true, 'location_label' => 'Hà Nội ↔ Hà Nội', 'summary' => 'Xe riêng sảnh bay — ghép limo Tam Đảo.', 'highlights' => ['Theo dõi flight'], 'inclusions' => ['Xe 4 chỗ'], 'exclusions' => ['Lên Tam Đảo'], 'notes' => [], 'attrs' => ['from' => 'HAN', 'duration_hours' => 0.75]],
        ['code' => 'flight-combo-han-td', 'cluster' => 'flight', 'category_slug' => 'combo-bay-transfer-tam-dao', 'title' => 'Combo vé bay Hà Nội + transfer Tam Đảo', 'slug' => 'combo-ve-bay-han-transfer-tam-dao', 'price_from' => 1350000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 78, 'is_featured' => true, 'is_hot_deal' => true, 'location_label' => 'Hà Nội → Tam Đảo', 'summary' => 'Một báo giá bay + xe lên đồi.', 'highlights' => ['Một đầu mối'], 'inclusions' => ['Vé bay', 'Transfer'], 'exclusions' => ['Resort', 'Tour'], 'notes' => ['Giá theo ngày bay.'], 'attrs' => ['from' => 'HAN', 'to' => 'Tam Đảo']],
        ['code' => 'flight-han-advice', 'cluster' => 'flight', 'category_slug' => 'combo-bay-transfer-tam-dao', 'title' => 'Tư vấn bay quốc tế HAN + Tam Đảo', 'slug' => 'tu-van-bay-quoc-te-han-tam-dao', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 45, 'is_featured' => false, 'location_label' => 'Quốc tế → Tam Đảo', 'summary' => 'Khách bay Hà Nội — ghép transfer & tour.', 'highlights' => ['Miễn phí tư vấn'], 'inclusions' => ['Tư vấn'], 'exclusions' => ['Vé', 'Xe'], 'notes' => [], 'attrs' => ['price_label' => 'Liên hệ']],
        ['code' => 'exp-thac-bac', 'cluster' => 'experience', 'category_slug' => 'thac-bac-dai', 'zone_slug' => 'thac-bac-thac-dai', 'title' => 'Thác Bạc — vé & xe đón sáng sớm', 'slug' => 'thac-bac-ve-xe-don-sang-som', 'price_from' => 180000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 678, 'is_featured' => true, 'location_label' => 'Thác Bạc', 'summary' => '06:00 đón — sương đẹp nhất.', 'highlights' => ['Vé thác', 'Xe ghép'], 'inclusions' => ['Vé', 'Xe'], 'exclusions' => ['HDV riêng'], 'notes' => [], 'attrs' => ['duration_hours' => 3, 'activity' => 'waterfall']],
        ['code' => 'exp-thac-dai', 'cluster' => 'experience', 'category_slug' => 'thac-bac-dai', 'zone_slug' => 'thac-bac-thac-dai', 'title' => 'Thác Dải — tour nửa ngày', 'slug' => 'thac-dai-tour-nua-ngay', 'price_from' => 320000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Thác Dải', 'summary' => 'Ghép Thác Bạc hoặc riêng.', 'highlights' => ['Trek nhẹ', 'HDV'], 'inclusions' => ['Xe', 'Vé', 'HDV'], 'exclusions' => ['Trưa'], 'notes' => [], 'attrs' => ['duration_hours' => 4]],
        ['code' => 'exp-cau-may', 'cluster' => 'experience', 'category_slug' => 'cau-may-sky-walk', 'zone_slug' => 'cau-may-sky-walk', 'title' => 'Vé Cầu Mây & Sky Walk', 'slug' => 've-cau-may-sky-walk', 'price_from' => 150000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 512, 'is_featured' => true, 'location_label' => 'Cầu Mây', 'summary' => 'E-ticket — check-in hot.', 'highlights' => ['Cầu treo', 'Sky Walk'], 'inclusions' => ['Vé combo'], 'exclusions' => ['Xe đón'], 'notes' => ['Đóng khi mưa bão.'], 'attrs' => ['activity' => 'cloud_bridge']],
        ['code' => 'exp-vqg-trek', 'cluster' => 'experience', 'category_slug' => 'trekking-vqg', 'zone_slug' => 'vqg-rung-thong', 'title' => 'Trekking VQG Tam Đảo — HLV & vé', 'slug' => 'trekking-vqg-hlv-ve', 'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 167, 'is_featured' => true, 'location_label' => 'VQG Tam Đảo', 'summary' => '4–5h cung nhẹ — picnic rừng.', 'highlights' => ['HLV', 'Vé VQG'], 'inclusions' => ['HLV', 'Vé', 'Picnic'], 'exclusions' => ['Giày trek'], 'notes' => [], 'attrs' => ['duration_hours' => 5, 'activity' => 'trekking']],
        ['code' => 'exp-food-tour', 'cluster' => 'experience', 'category_slug' => 'am-thuc-dac-san', 'zone_slug' => 'suoi-ca-am-thuc', 'title' => 'Tour ẩm thực Tam Đảo — vé lẻ', 'slug' => 'food-tour-tam-dao-ve-le', 'price_from' => 420000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 198, 'is_featured' => true, 'location_label' => 'Tam Đảo', 'summary' => '18h–22h — gà đồi, suối cá, rượu mật ong.', 'highlights' => ['7–8 món'], 'inclusions' => ['HDV', 'Tasting'], 'exclusions' => ['Rượu thêm'], 'notes' => [], 'attrs' => ['duration_hours' => 3, 'activity' => 'food_tour']],
        ['code' => 'exp-suoi-ca', 'cluster' => 'experience', 'category_slug' => 'am-thuc-dac-san', 'zone_slug' => 'suoi-ca-am-thuc', 'title' => 'Suối cá tươi — đặt bàn & xe đón', 'slug' => 'suoi-ca-dat-ban-xe-don', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 145, 'is_featured' => true, 'location_label' => 'Suối cá Tam Đảo', 'summary' => 'Miễn phí đặt bàn cuối tuần.', 'highlights' => ['Cá tươi', 'View suối'], 'inclusions' => ['Đặt bàn'], 'exclusions' => ['Tiền ăn'], 'notes' => [], 'attrs' => ['price_label' => 'Miễn phí']],
        ['code' => 'exp-city-tour', 'cluster' => 'experience', 'category_slug' => 'city-tour-nha-tho', 'zone_slug' => 'trung-tam-thi-tran', 'title' => 'City tour nhà thờ đá — nửa ngày', 'slug' => 'city-tour-nha-tho-da-nua-ngay', 'price_from' => 380000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 267, 'is_featured' => true, 'location_label' => 'Thị trấn Tam Đảo', 'summary' => 'Nhà thờ đá, phố núi, di sản Pháp.', 'highlights' => ['HDV', 'Ghép nhóm'], 'inclusions' => ['Xe', 'HDV'], 'exclusions' => ['Vé riêng'], 'notes' => [], 'attrs' => ['duration_hours' => 4, 'activity' => 'city_tour']],
        ['code' => 'exp-nha-tho', 'cluster' => 'experience', 'category_slug' => 'city-tour-nha-tho', 'zone_slug' => 'trung-tam-thi-tran', 'title' => 'Nhà thờ đá Tam Đảo — tham quan', 'slug' => 'nha-tho-da-tam-dao-tham-quan', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 890, 'is_featured' => false, 'location_label' => 'Nhà thờ đá', 'summary' => 'Miễn phí vào cửa — HDV city tour gợi ý.', 'highlights' => ['Di sản Pháp'], 'inclusions' => ['Tự do tham quan'], 'exclusions' => ['HDV'], 'notes' => [], 'attrs' => ['activity' => 'church']],
        ['code' => 'exp-photo-mist', 'cluster' => 'experience', 'category_slug' => 'thac-bac-dai', 'zone_slug' => 'thac-bac-thac-dai', 'title' => 'Tour chụp ảnh sương mù — photographer 4 giờ', 'slug' => 'photo-tour-suong-mu-photographer', 'price_from' => 1800000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 56, 'is_featured' => false, 'location_label' => 'Tam Đảo', 'summary' => 'Thác Bạc sáng sớm + rừng thông.', 'highlights' => ['50+ ảnh'], 'inclusions' => ['Photographer', 'Xe'], 'exclusions' => ['Makeup'], 'notes' => ['Tháng 11–2 đẹp nhất.'], 'attrs' => ['duration_hours' => 4]],
        ['code' => 'other-guide-en', 'cluster' => 'other', 'category_slug' => 'huong-dan-rieng', 'zone_slug' => 'trung-tam-thi-tran', 'title' => 'HDV tiếng Anh riêng (8h)', 'slug' => 'hdv-tieng-anh-rieng-tam-dao', 'price_from' => 750000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 89, 'is_featured' => true, 'location_label' => 'Tam Đảo', 'summary' => 'khách lẻ quốc tế — tour/thác/city.', 'highlights' => ['Bản địa Vĩnh Phúc'], 'inclusions' => ['HDV 8h'], 'exclusions' => ['Vé', 'Xe'], 'notes' => [], 'attrs' => ['service_type' => 'guide', 'languages' => ['en']]],
        ['code' => 'other-team-building', 'cluster' => 'other', 'category_slug' => 'team-building-mice', 'zone_slug' => 'khu-resort-dinh-cao', 'title' => 'Team-building Tam Đảo — facilitator + game kit', 'slug' => 'team-building-tam-dao-facilitator', 'price_from' => 8500000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 34, 'is_featured' => true, 'location_label' => 'Resort Tam Đảo', 'summary' => '15–40 pax — MC, game rừng, gala cơ bản.', 'highlights' => ['MICE nhỏ'], 'inclusions' => ['Facilitator 4h', 'Game kit'], 'exclusions' => ['Resort', 'Limo HN'], 'notes' => ['Báo trước 7 ngày.'], 'attrs' => ['service_type' => 'team_building']],
        ['code' => 'other-car-driver', 'cluster' => 'other', 'category_slug' => 'xe-tien-ich', 'zone_slug' => 'trung-tam-thi-tran', 'title' => 'Xe ô tô có tài xế Tam Đảo (theo ngày)', 'slug' => 'xe-o-to-co-tai-xe-tam-dao', 'price_from' => 1100000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 156, 'is_featured' => true, 'location_label' => 'Tam Đảo', 'summary' => '4–7 chỗ + lái ~8h — an toàn đèo sương.', 'highlights' => ['Lái đèo quen'], 'inclusions' => ['Xe + lái 8h'], 'exclusions' => ['Vé'], 'notes' => [], 'attrs' => ['service_type' => 'car_with_driver']],
        ['code' => 'other-motorbike', 'cluster' => 'other', 'category_slug' => 'xe-tien-ich', 'zone_slug' => 'trung-tam-thi-tran', 'title' => 'Thuê xe máy Tam Đảo', 'slug' => 'thue-xe-may-tam-dao', 'price_from' => 100000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Thị trấn', 'summary' => 'Tự khám phá — cẩn thận sương đèo.', 'highlights' => ['Giao resort'], 'inclusions' => ['Xe/ngày', 'Mũ'], 'exclusions' => ['Xăng', 'CCCD cọc'], 'notes' => ['Không khuyến khích người mới lái đèo.'], 'attrs' => ['service_type' => 'vehicle_rental']],
        ['code' => 'other-emergency', 'cluster' => 'other', 'category_slug' => 'ho-tro-dac-biet', 'zone_slug' => 'trung-tam-thi-tran', 'title' => 'Hotline 24/7 — sương mù & đổi lịch', 'slug' => 'hotline-ho-tro-khan-cap-tam-dao', 'price_from' => 0, 'currency' => 'VND', 'rating' => 5.0, 'review_count' => 42, 'is_featured' => true, 'location_label' => '24/7', 'summary' => 'Miễn phí khách :brand — đèo kẹt, đổi limo.', 'highlights' => ['VI/EN'], 'inclusions' => ['Hotline'], 'exclusions' => [], 'notes' => ['Nguy hiểm — gọi 115.'], 'attrs' => ['service_type' => 'medical_assistance', 'price_label' => 'Liên hệ']],
        ['code' => 'other-flower-surprise', 'cluster' => 'other', 'category_slug' => 'ho-tro-dac-biet', 'zone_slug' => 'khu-resort-dinh-cao', 'title' => 'Setup hoa surprise trên đồi', 'slug' => 'setup-hoa-surprise-tam-dao', 'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 38, 'is_featured' => false, 'location_label' => 'Resort Tam Đảo', 'summary' => 'Couple / sinh nhật view sương.', 'highlights' => ['Bí mật'], 'inclusions' => ['Hoa', 'Setup'], 'exclusions' => [], 'notes' => ['Đặt trước 48h.'], 'attrs' => ['service_type' => 'flower_surprise']],
        ['code' => 'train-limo-friday', 'cluster' => 'train', 'category_slug' => 'limousine-ha-noi-tam-dao', 'title' => 'Limousine thứ 6 chiều Hà Nội → Tam Đảo', 'slug' => 'limousine-thu-6-chieu-ha-noi-tam-dao', 'price_from' => 240000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 567, 'is_featured' => true, 'location_label' => 'Hà Nội → Tam Đảo', 'summary' => 'Khung 16h–18h — combo cuối tuần phổ biến.', 'highlights' => ['Khung cuối tuần'], 'inclusions' => ['Limousine'], 'exclusions' => [], 'notes' => ['Đặt trước thứ 5.'], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Tam Đảo']],
        ['code' => 'exp-sup-lake', 'cluster' => 'experience', 'category_slug' => 'thuyen-suoi-ho', 'zone_slug' => 'vqg-rung-thong', 'title' => 'SUP & kayak hồ núi Tam Đảo', 'slug' => 'sup-kayak-ho-nui-tam-dao', 'price_from' => 320000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 67, 'is_featured' => true, 'location_label' => 'Hồ núi', 'summary' => 'Sáng sớm — sương mỏng trên mặt nước.', 'highlights' => ['HLV', 'Áo phao'], 'inclusions' => ['Thiết bị', 'HLV'], 'exclusions' => ['Xe đón'], 'notes' => ['Biết bơi khuyến nghị.'], 'attrs' => ['duration_hours' => 2.5, 'activity' => 'sup_kayak']],
    ],
    'service_listing_faqs' => [
        ['q' => 'Tam Đảo có sân bay không?', 'a' => 'Không — bay Nội Bài (Hà Nội) + transfer ~2.5–3h, hoặc limousine/xe từ Hà Nội ~80km (2–2.5h).'],
        ['q' => 'Giá limo có cố định không?', 'a' => 'Mức tham khảo 180–280k/chiều — chốt theo nhà xe, ngày đi và điểm đón. T6–CN và mùa sương cao hơn.'],
        ['q' => 'Đặt lưu trú qua :brand thế nào?', 'a' => 'Xem mục Lưu trú (catalogue có thể bổ sung từ tool cào) hoặc liên hệ tư vấn resort/homestay theo khu — trung tâm, đỉnh đồi, view rừng.'],
        ['q' => 'Gộp limo HN + tour + Thác Bạc một đơn?', 'a' => 'Có — một báo giá minh bạch, một đầu mối chăm sóc.'],
        ['q' => 'Sương mù có hủy tour không?', 'a' => 'Thác Bạc/Cầu Mây có thể đổi slot — limo đèo kẹt :brand hỗ trợ đổi giờ miễn phí khi an toàn.'],
    ],
];
$__companySeed = [
    'name' => 'Hi Tam Đảo',
    'legal_name' => 'Công ty TNHH Du lịch Hi Tam Đảo',
    'tagline' => 'Sương mù, rừng thông & thoát urban cuối tuần',
    'slogan' => '"Khám phá Tam Đảo như người bản địa"',
    'license_number' => '0046/2024/TCDL-GPLHQT',
    'contact' => [
        'email' => 'hello@hitamdao.dev',
        'phone' => '+84 211 388 8888',
        'whatsapp' => '+84 912 888 777',
        'zalo' => '+84 211 388 8888',
        'hotline_label' => 'Hotline',
    ],
    'address' => [
        'street' => 'Khu du lịch Tam Đảo, huyện Tam Đảo',
        'locality' => 'Tam Đảo, Vĩnh Phúc',
        'region' => 'Vĩnh Phúc',
        'postal' => '280000',
        'country' => 'VN',
    ],
    'social' => [
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/hitamdao'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'play', 'url' => 'https://www.youtube.com/@hitamdao'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'photo', 'url' => 'https://www.instagram.com/hitamdao'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'share', 'url' => 'https://www.tiktok.com/@hitamdao'],
    ],
    'schema' => [
        'available_language' => ['Vietnamese', 'English', 'Korean'],
        'contact_type' => 'customer service',
        'logo' => null,
    ],
    'footer' => [
        'copyright' => '© :year Hi Tam Đảo. Giấy phép kinh doanh dịch vụ lữ hành số :license.',
        'show_dmca_badge' => true,
    ],
];

return array_merge(
    $__hitamdaoSeed,
    $__servicesSeed,
    ['company' => $__companySeed],
    ['customize_form' => [
        'destinations_label' => [
            'vi' => 'Bạn muốn khám phá khu vực nào ở Tam Đảo?',




'en' => 'Which areas of Tam Dao would you like to explore?',
        ],
        'accommodation_label' => [
            'vi' => 'Bạn thích loại lưu trú nào?',




'en' => 'What kind of stay do you prefer?',
        ],
        'budget_note' => [
            'vi' => 'Ngân sách dự kiến (chưa gồm vé limo/xe Hà Nội—Tam Đảo)',




'en' => 'Estimated budget (excluding Hanoi—Tam Dao limo/coach tickets)',
        ],
        'accommodation' => [
            'vi' => [
                'Trung tâm thị trấn / Nhà thờ đá',
                'Resort trên đồi / view sương',
                'Homestay rừng thông',
                'Villa / resort đỉnh cao',
                'Nhờ tư vấn giúp tôi',
            ],




'en' => [
                'Town centre / Stone Church',
                'Hilltop resort / mist views',
                'Pine forest homestay',
                'Villa / peak resort',
                'Please advise me',
            ],
        ],
    ]],
    ['nav' => [
        'about_group' => ['vi' => 'Về Hi Tam Đảo',



'en' => 'About Hi Tam Dao'],
        'tours' => ['label' => ['vi' => 'Tour',



'en' => 'Tours']],
        'cruise' => [
            'label' => ['vi' => 'Thuyền suối & hồ',



'en' => 'Stream & lake boats'],
            'all_label' => ['vi' => 'Tất cả thuyền suối & hồ',



'en' => 'All stream & lake boats'],
            'all_meta' => ['vi' => 'Thuyền suối cá & picnic hồ núi',



'en' => 'Fish-stream boats & mountain lake picnics'],
            'search_hint' => ['vi' => 'Tour, thác, ẩm thực, cẩm nang…',



'en' => 'Tours, falls, food, guides…'],
            'search_placeholder' => ['vi' => 'Tìm tour, dịch vụ, bài viết…',



'en' => 'Search tours, services, articles…'],
            'hub_title' => ['vi' => 'Thuyền suối & hồ',



'en' => 'Stream & lake boats'],
            'hub_subtitle' => ['vi' => 'Suối cá ăn tươi, picnic hồ sáng sớm và SUP trên hồ núi.',



'en' => 'Fresh fish-stream boats, dawn lake picnics and lake SUP.'],
        ],
    ]],
    ['listing_hubs' => [
        'tours_hub' => [
            'vi' => ['seo_body' => 'Tour :brand gom cuối tuần HN, Thác Bạc, Cầu Mây và combo Ninh Bình — thiết kế bởi chuyên gia bản địa Vĩnh Phúc.'],




'en' => ['seo_body' => ':brand tours cover Hanoi weekends, Silver Falls, Cloud Bridge and Ninh Binh combos — designed by local Vinh Phuc experts.'],
        ],
        'cruises_hub' => [
            'vi' => ['seo_body' => 'Thuyền suối cá & picnic hồ núi Tam Đảo — trải nghiệm nước nhỏ đặc trưng từ :brand.'],




'en' => ['seo_body' => 'Tam Dao fish-stream boats and mountain lake picnics — signature small water experiences from :brand.'],
        ],
        'trains_hub' => [
            'vi' => ['seo_body' => 'Limousine Hà Nội — Tam Đảo, xe khách và shuttle Vĩnh Phúc qua :brand — e-ticket, đổi ngày linh hoạt.'],




'en' => ['seo_body' => 'Hanoi—Tam Dao limousines, coaches and Vinh Phuc shuttles via :brand — e-tickets and flexible changes.'],
        ],
        'flights_hub' => [
            'vi' => ['seo_body' => 'Vé bay Nội Bài (Hà Nội) và đưa đón Tam Đảo kết nối tour :brand.'],




'en' => ['seo_body' => 'Noi Bai (HAN) flights and Tam Dao transfers aligned with your :brand itinerary.'],
        ],
        'stays_hub' => [
            'vi' => ['seo_body' => ':brand tổng hợp lưu trú Tam Đảo theo khu vực — homestay, villa và resort ở trung tâm thị trấn, đỉnh đồi và view rừng thông. Catalogue có thể bổ sung từ tool cào; lọc theo ngân sách, phong cách và gần các điểm trong hành trình của bạn.'],




'en' => ['seo_body' => ':brand brings together Tam Dao stays by area — homestays, villas and resorts in the town centre, hilltops and pine-forest views. Catalogue may be enriched via crawl tool; filter by budget, style and proximity to your itinerary.'],
        ],
        'experiences_hub' => [
            'vi' => ['seo_body' => 'Trải nghiệm Tam Đảo: Thác Bạc, Cầu Mây, tour ẩm thực, trekking VQG, Nhà thờ đá — đặt lẻ hoặc gộp tour qua :brand.'],




'en' => ['seo_body' => 'Tam Dao experiences: Silver Falls, Cloud Bridge, food tours, NP trekking, Stone Church — à la carte or bundled via :brand.'],
        ],
        'extras_hub' => [
            'vi' => ['seo_body' => 'HDV riêng, xe có lái, hoa surprise và hỗ trợ 24/7 trên đèo Tam Đảo cùng :brand.'],




'en' => ['seo_body' => 'Private guides, cars with drivers, flower surprises and 24/7 Tam Dao pass support with :brand.'],
        ],
    ]],
);
