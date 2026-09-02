<?php

/**
 * ============================================================================
 * DỮ LIỆU Hi Mũi Né — profile `himuine` (project:seed / migrate --seed)
 * ============================================================================
 *
 * Trang thông tin du lịch + dịch vụ + kết nối TOÀN DIỆN Mũi Né (Phan Thiet, Binh Thuan).
 * Một điểm đến duy nhất — "zones" thay "countries". Cụm "train" = xe khách,
 * limousine Sài Gòn — Mũi Né (không có sân bay tại Mũi Né). Cụm "flight" = bay
 * Cam Ranh/Nha Trang (CXR) hoặc Tuy Hoa (TBB) + xe nối. Thuyền biển = câu mực đêm,
 * ngắm san hô, SUP/kayak — không phải du thuyền qua đêm.
 *
 * Schema: project/README.md | Loader: App\Support\ProjectSeed::useProfile('himuine')
 *
 * @return array<string, mixed>
 */

$__himuineSeed = array(
    'meta' => array(
        'schema' => 1,
        'brand' => 'Hi Mũi Né',
        'tagline' => 'Biển xanh, đồi cát và thiên đường kite-surf Nam Trung Bộ',
        'admin' => array(
            'email' => 'admin@himuine.dev',
            'name' => 'Admin Hi Mũi Né',
            'password' => '111111',
        ),
        'primary_domain' => 'himuine.dev',
        'domains' => array('himuine.dev', 'www.himuine.dev', 'himuine.com', 'www.himuine.com'),
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
            array('kind' => 'range', 'label' => 'Mùa gió kite-surf — cuối năm {year}', 'starts_on' => '{year}-11-01', 'ends_on' => '{year}-12-31', 'is_promo' => true, 'priority' => 10, 'amount_multiplier' => 1.1),
            array('kind' => 'range', 'label' => 'Mùa gió kite-surf — đầu năm {year}', 'starts_on' => '{year}-01-01', 'ends_on' => '{year}-03-31', 'is_promo' => true, 'priority' => 10, 'amount_multiplier' => 1.1),
            array('kind' => 'range', 'label' => 'Mùa hè biển {year} (May–Aug)', 'starts_on' => '{year}-05-01', 'ends_on' => '{year}-08-31', 'is_promo' => true, 'priority' => 8, 'amount_multiplier' => 1.05),
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
        'how-to-get-there' => array('vi' => 'Di chuyển tới Mũi Né thế nào?',



'en' => 'How to get to Mui Ne?'),
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
        'doi-cat' => array(
            'vi' => 'Tour đồi cát',




'en' => 'Sand dune tours',
        ),
        'kite-surf' => array(
            'vi' => 'Kite-surf & windsurf',




'en' => 'Kite-surf & windsurf',
        ),
        'lang-chai' => array(
            'vi' => 'Làng chài & hải sản',




'en' => 'Fishing village & seafood',
        ),
        'gia-dinh' => array(
            'vi' => 'Gia đình & resort',




'en' => 'Family & resort',
        ),
        'combo' => array(
            'vi' => 'Combo Sài Gòn',




'en' => 'Saigon combo',
        ),
        'honeymoon' => array(
            'vi' => 'Trang mat & cap doi',




'en' => 'Honeymoon & couples',
        ),
    ),

    'review_platforms' => array(
        array('code' => 'tripadvisor', 'name' => 'Tripadvisor', 'rating' => 4.9, 'review_count' => 318, 'sort' => 0,
            'quote' => 'Khách quốc tế khen jeep đồi cát sunrise và gói kite-surf mùa gió — đúng positioning thiên đường kite Việt Nam.',
            'link_label' => 'Đọc đánh giá trên Tripadvisor', 'url' => 'https://www.tripadvisor.com'),
        array('code' => 'google', 'name' => 'Google', 'rating' => 4.8, 'review_count' => 486, 'sort' => 1,
            'quote' => '4.8/5 trên Google Maps — khách Sài Gòn khen limousine cuối tuần và resort Ham Tien.',
            'link_label' => 'Xem đánh giá trên Google', 'url' => 'https://www.google.com/maps'),
        array('code' => 'trustpilot', 'name' => 'Trustpilot', 'rating' => 4.7, 'review_count' => 84, 'sort' => 2,
            'quote' => 'Điểm "Xuất sắc" — đặc biệt combo Đà Lạt + Mũi Né và tour ngày từ Sài Gòn.',
            'link_label' => 'Đọc đánh giá trên Trustpilot', 'url' => 'https://www.trustpilot.com'),
    ),

    'cruise_types' => array(
        array('slug' => 'thuyen-cau-muc-dem', 'name' => 'Thuyền câu mực đêm', 'count' => 2, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 10),
        array('slug' => 'thuyen-ngam-san-hoi', 'name' => 'Thuyền ngắm san hô', 'count' => 2, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 20),
        array('slug' => 'sup-kayak-bien', 'name' => 'SUP & kayak biển', 'count' => 2, 'image' => null, 'imageHero' => null, 'imageSrcset' => null, 'sort' => 30),
    ),

    'home_slides' => array(
        array(
            'sort' => 0, 'text_align' => 'center', 'link_url' => '/tours',
            'vi' => array('title' => 'Mũi Né', 'title_accent' => 'đồi cát, biển xanh & kite-surf',
                'description' => 'Dải resort Ham Tien — Nguyen Dinh Chieu, đồi cát đỏ bình minh, Suối Tiên và làng chài — thiên đường biển Nam Trung Bộ cách Sài Gòn 4–5 giờ.',
                'button_label' => 'Khám phá tour Mũi Né', 'image_alt' => 'Mũi Né — đồi cát đỏ và biển xanh'),




'en' => array('title' => 'Mui Ne', 'title_accent' => 'sand dunes, blue sea & kite-surf',
                'description' => 'Ham Tien dải resort, red dunes at sunrise, Fairy Stream and fishing village — South Central beach paradise, 4–5 hours from Saigon.',
                'button_label' => 'Explore Mui Ne tours', 'image_alt' => 'Mui Ne red dunes and blue sea'),
        ),
        array(
            'sort' => 1, 'text_align' => 'center', 'link_url' => '/diem-den/doi-cat-do',
            'vi' => array('title' => 'Đồi cát', 'title_accent' => 'đỏ bình minh & Bàu Trắng hoang sơ',
                'description' => 'Jeep sunrise đồi cát đỏ, trượt cát Bàu Trắng và Suối Tiên — combo kinh điển nửa ngày hoặc cả ngày.',
                'button_label' => 'Xem tour đồi cát', 'image_alt' => 'Đồi cát đỏ Mũi Né bình minh'),




'en' => array('title' => 'Sand dunes', 'title_accent' => 'red sunrise & Bau Trang wilderness',
                'description' => 'Jeep sunrise on red dunes, sand sliding at White Dunes and Fairy Stream — classic half or full-day combo.',
                'button_label' => 'View dune tours', 'image_alt' => 'Mui Ne red dunes sunrise'),
        ),
        array(
            'sort' => 2, 'text_align' => 'center', 'link_url' => '/cruises',
            'vi' => array('title' => 'Kite-surf & cuối tuần SGN', 'title_accent' => 'mùa gió Nov–Mar & limousine',
                'description' => 'Thiên đường kite-surf Việt Nam, khách Nga/EU mùa đông, gia đình Sài Gòn cuối tuần — limo 250–350k/chiều.',
                'button_label' => 'Xem thuyền biển & kite', 'image_alt' => 'Kite-surf Mũi Né mùa gió'),




'en' => array('title' => 'Kite-surf & cuối tuần Sài Gòns', 'title_accent' => 'wind season Nov–Mar & limousine',
                'description' => 'Vietnam\'s kite-surf capital, Russian/EU khách mùa đôngs, Saigon family weekends — limo VND 250–350k each way.',
                'button_label' => 'View boats & kite', 'image_alt' => 'Mui Ne kite-surf wind season'),
        ),
    ),

    'zones' => array(
        array('slug' => 'ham-tien-bai-bien', 'name' => 'Ham Tien — dải bãi biển', 'size' => 'large', 'tourCount' => 5, 'tagline' => 'Nguyễn Đình Chiểu — dải resort chính, kite school & bãi biển'),
        array('slug' => 'doi-cat-do', 'name' => 'Đồi cát đỏ', 'size' => 'large', 'tourCount' => 4, 'tagline' => 'Sunrise jeep — điểm chụp iconic Mũi Né'),
        array('slug' => 'doi-cat-trang-bau-trang', 'name' => 'Đồi cát trắng — Bàu Trắng', 'size' => 'large', 'tourCount' => 3, 'tagline' => 'Sa mạc mini ~30km — trượt cát, hồ Bàu Trắng'),
        array('slug' => 'suoi-tien', 'name' => 'Suối Tiên (Fairy Stream)', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Suối nước đỏ giữa vách đá — đi bộ barefoot'),
        array('slug' => 'lang-chai-mui-ne', 'name' => 'Làng chài Mũi Né', 'size' => 'normal', 'tourCount' => 3, 'tagline' => 'Thuyền đánh cá, nước mắm Phan Thiết & hải sản tươi'),
        array('slug' => 'phan-thiet-thanh-pho', 'name' => 'Phan Thiết — thành phố', 'size' => 'normal', 'tourCount' => 3, 'tagline' => 'Tháp Chăm Po Sah Inu, cảng & nước mắm'),
        array('slug' => 'ke-ga-ta-cu', 'name' => 'Ke Ga — Ta Cu', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Hải đăng Ke Ga, núi Ta Cu phía nam'),
        array('slug' => 'ket-hop-da-lat', 'name' => 'Kết hợp Đà Lạt', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Cao nguyên + biển — 3–4 giờ đường đèo'),
        array('slug' => 'ket-hop-nha-trang', 'name' => 'Kết hợp Nha Trang', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Cam Ranh/Nha Trang — ~2 giờ xe'),
        array('slug' => 'ket-hop-sai-gon', 'name' => 'Kết hợp Sài Gòn', 'size' => 'large', 'tourCount' => 4, 'tagline' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Sài Gòn — limo 4–5 giờ'),
    ),

    'zone_translations' => array(
        'ham-tien-bai-bien' => array('vi' => 'Ham Tien — dải bãi biển',



'en' => 'Ham Tien beach strip',
            'tagline' => array('vi' => 'Nguyễn Đình Chiểu — dải resort chính, trường kite và bãi biển',



'en' => 'Nguyen Dinh Chieu — main resort strip, kite schools & beach')),
        'doi-cat-do' => array('vi' => 'Đồi cát đỏ',



'en' => 'Red sand dunes',
            'tagline' => array('vi' => 'Jeep bình minh — điểm chụp biểu tượng Mũi Né',



'en' => 'Sunrise jeep — iconic Mui Ne photo spot')),
        'doi-cat-trang-bau-trang' => array('vi' => 'Đồi cát trắng — Bàu Trắng',



'en' => 'White dunes — Bau Trang',
            'tagline' => array('vi' => 'Sa mạc nhỏ ~30km — trượt cát, hồ Bàu Trắng',



'en' => 'Mini desert ~30km — sand sliding, Bau Trang lake')),
        'suoi-tien' => array('vi' => 'Suối Tiên (Fairy Stream)',



'en' => 'Fairy Stream (Suoi Tien)',
            'tagline' => array('vi' => 'Suối nước đỏ giữa vách đá — đi bộ barefoot',



'en' => 'Red stream between cliffs — barefoot walk')),
        'lang-chai-mui-ne' => array('vi' => 'Làng chài Mũi Né',



'en' => 'Mui Ne fishing village',
            'tagline' => array('vi' => 'Thuyền đánh cá, nước mắm Phan Thiết & hải sản tươi',



'en' => 'Fishing boats, Phan Thiet fish sauce & fresh seafood')),
        'phan-thiet-thanh-pho' => array('vi' => 'Phan Thiết — thành phố',



'en' => 'Phan Thiet city',
            'tagline' => array('vi' => 'Tháp Chăm Po Sah Inu, cảng & nước mắm',



'en' => 'Po Sah Inu Cham towers, harbour & fish sauce')),
        'ke-ga-ta-cu' => array('vi' => 'Ke Ga — Ta Cu',



'en' => 'Ke Ga — Ta Cu',
            'tagline' => array('vi' => 'Hải đăng Ke Ga, núi Ta Cu phía nam',



'en' => 'Ke Ga lighthouse, Ta Cu mountain south')),
        'ket-hop-da-lat' => array('vi' => 'Kết hợp Đà Lạt',



'en' => 'Combined with Dalat',
            'tagline' => array('vi' => 'Cao nguyên + biển — 3–4 giờ đường đèo',



'en' => 'Highlands + beach — 3–4 hour mountain road')),
        'ket-hop-nha-trang' => array('vi' => 'Kết hợp Nha Trang',



'en' => 'Combined with Nha Trang',
            'tagline' => array('vi' => 'Cam Ranh/Nha Trang — ~2 giờ xe',



'en' => 'Cam Ranh/Nha Trang — ~2 hour drive')),
        'ket-hop-sai-gon' => array('vi' => 'Kết hợp Sài Gòn',



'en' => 'Combined with Saigon',
            'tagline' => array('vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Sài Gòn — limo 4–5 giờ',



'en' => 'Day trip & 2N1D weekend from HCMC — 4–5h limo')),
    ),

    'tours' => array(
        array(
            'slug' => 'tour-ngay-doi-cat-suoi-tien',
            'title' => 'Tour ngày đồi cát đỏ — Suối Tiên & làng chài',
            'zoneSlug' => 'doi-cat-do',
            'zone' => 'Đồi cát đỏ',
            'tourCode' => 'MN1D-01',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 1240,
            'badge' => 'Bán chạy nhất',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Combo kinh điển — jeep đồi cát đỏ sáng sớm, Suối Tiên và làng chài buổi chiều.',
                'author' => 'Chị Minh Anh',
            ),
            'places' => array(
                'Đồi cát đỏ',
                'Suối Tiên',
                'Làng chài Mũi Né',
            ),
            'start' => 'Ham Tien / Mũi Né',
            'end' => 'Ham Tien / Mũi Né',
            'highlightsIntro' => 'Tour ngày phổ biến nhất Mũi Né: jeep sunrise đồi cát đỏ, đi bộ Suối Tiên và tham quan làng chài.',
            'highlights' => array(
                'Jeep đồi cát đỏ sunrise',
                'Suối Tiên — đi bộ barefoot',
                'Làng chài & nước mắm',
                'Trượt cát (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đồi cát — Suối Tiên — làng chài',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'jeep',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '05:00–05:30 jeep đồi cát đỏ, 09:00 Suối Tiên, trưa hải sản, chiều làng chài, về resort ~16:00.',
                ),
            ),
            'inclusions' => array(
                'Xe jeep + HDV',
                'Vé Suối Tiên',
                'Bữa trưa',
            ),
            'exclusions' => array(
                'Trượt cát',
                'Đồ uống',
            ),
            'notes' => array(
                'Nên đi sáng sớm đồi cát đỏ để tránh nóng.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Tour ngày có cần ở resort không?',
                    'a' => 'Nên ở Ham Tien đêm trước để tiện jeep 5h sáng — hoặc ghép tour ngày từ Sài Gòn (đêm trước tới bằng limo).',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'jeep-doi-cat-do-sunrise',
            'title' => 'Jeep đồi cát đỏ — Sunrise & chụp ảnh',
            'zoneSlug' => 'doi-cat-do',
            'zone' => 'Đồi cát đỏ',
            'tourCode' => 'MN-JEEP-R',
            'duration' => '3 giờ',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 892,
            'badge' => 'Sunrise',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Bình minh trên đồi cát đỏ — ảnh đẹp nhất chuyến Mũi Né.',
                'author' => 'Photographer Ken',
            ),
            'places' => array(
                'Đồi cát đỏ',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => 'Jeep riêng hoặc ghép nhóm nhỏ — đón resort 05:00, ngắm sunrise và trượt cát.',
            'highlights' => array(
                'Sunrise 05:30–07:00',
                'Jeep Mui Ne style',
                'Trượt cát',
                'Góc chụp HDV gợi ý',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Sunrise jeep',
                    'meals' => null,
                    'transport' => array(
                        'jeep',
                    ),
                    'overnight' => null,
                    'content' => '05:00 đón, 05:30–07:30 đồi cát đỏ, về resort ~08:00.',
                ),
            ),
            'inclusions' => array(
                'Jeep',
                'HDV',
                'Trượt cát cơ bản',
            ),
            'exclusions' => array(
                'Ăn sáng',
            ),
            'notes' => array(
                'Giá jeep 150–300k tham khảo — ghép nhóm rẻ hơn private.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 180000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'jeep-doi-cat-trang-bau-trang',
            'title' => 'Jeep đồi cát trắng — Bàu Trắng & hồ nước',
            'zoneSlug' => 'doi-cat-trang-bau-trang',
            'zone' => 'Đồi cát trắng — Bàu Trắng',
            'tourCode' => 'MN-JEEP-W',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 567,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                'day-trip',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Cát trắng khác hẳn đồi đỏ — hoang sơ hơn, đường jeep vui.',
                'author' => 'Anh Felix',
            ),
            'places' => array(
                'Đồi cát trắng',
                'Hồ Bàu Trắng',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => 'Cách Ham Tien ~25–30km — jeep qua đường cát, trượt cát đồi trắng, ngắm hồ Bàu Trắng.',
            'highlights' => array(
                'Đồi cát trắng hoang sơ',
                'Trượt cát',
                'Hồ Bàu Trắng',
                'Jeep off-road',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Bàu Trắng jeep',
                    'meals' => null,
                    'transport' => array(
                        'jeep',
                    ),
                    'overnight' => null,
                    'content' => '08:00–13:00 hoặc chiều 14:00–18:00 — jeep, trượt cát, hồ, về resort.',
                ),
            ),
            'inclusions' => array(
                'Jeep',
                'HDV',
                'Trượt cát',
            ),
            'exclusions' => array(
                'Ăn uống',
            ),
            'notes' => array(
                'Nên kết hợp đồi đỏ sáng + đồi trắng chiều trong tour ngày full.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'suoi-tien-nua-ngay',
            'title' => 'Suối Tiên (Fairy Stream) — Nửa ngày',
            'zoneSlug' => 'suoi-tien',
            'zone' => 'Suối Tiên',
            'tourCode' => 'MN-FT-HD',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 423,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Đi bộ giữa suối đỏ và vách đá — nhẹ nhàng, trẻ em cũng đi được.',
                'author' => 'Gia đình chị Thảo',
            ),
            'places' => array(
                'Suối Tiên',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => '2–3 giờ đi bộ barefoot trong suối — ghép tour ngày hoặc đặt lẻ buổi sáng/chiều.',
            'highlights' => array(
                'Đi bộ barefoot',
                'Vách đá đỏ',
                'HDV giải thích địa chất',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Fairy Stream walk',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => 'Xe đón resort, đi bộ suối 1.5–2h, về resort.',
                ),
            ),
            'inclusions' => array(
                'Xe đón',
                'Vé Suối Tiên',
                'HDV',
            ),
            'exclusions' => array(),
            'notes' => array(
                'Mang dép/sandals — không cần giày leo núi.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 280000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'lang-chai-mui-ne-sang',
            'title' => 'Làng chài Mũi Né buổi sáng — Thuyền & nước mắm',
            'zoneSlug' => 'lang-chai-mui-ne',
            'zone' => 'Làng chài Mũi Né',
            'tourCode' => 'MN-FV-AM',
            'duration' => '3 giờ',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 312,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                'day-trip',
                'lang-chai',
            ),
            'quote' => array(
                'text' => 'Sáng sớm thuyền về cập bến — hải sản tươi và nước mắm Phan Thiết.',
                'author' => 'Food blogger Ken',
            ),
            'places' => array(
                'Làng chài Mũi Né',
                'Lò nước mắm',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => '06:00–09:00 — thuyền đánh cá về, chợ hải sản, tham quan lò nước mắm (tuỳ lịch).',
            'highlights' => array(
                'Thuyền cập bến sáng',
                'Chợ hải sản',
                'Nước mắm Phan Thiết',
                'HDV địa phương',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Fishing village morning',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '06:00 đón, làng chài, chợ, nước mắm, về ~09:30.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'HDV',
            ),
            'exclusions' => array(
                'Mua hải sản',
            ),
            'notes' => array(
                'Ghép tour ngày hoặc đặt lẻ — không trùng jeep sunrise nếu lịch gấp.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 350000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'ham-tien-beach-kite-culture',
            'title' => 'Ham Tien — Bãi biển, kite school & phố resort',
            'zoneSlug' => 'ham-tien-bai-bien',
            'zone' => 'Ham Tien — dải bãi biển',
            'tourCode' => 'MN-HT-WK',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 198,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'kite-surf',
            ),
            'quote' => array(
                'text' => 'Hiểu rõ dải Nguyễn Đình Chiểu — resort, kite spot và quán ven biển.',
                'author' => 'Khách Úc',
            ),
            'places' => array(
                'Nguyễn Đình Chiểu',
                'Bãi Ham Tien',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => 'Walking tour dọc dải resort — kite school, beach club và mẹo chọn resort theo vị trí.',
            'highlights' => array(
                'Dạo phố resort',
                'Kite spot briefing',
                'Gợi ý ăn uống ven biển',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ham Tien walk',
                    'meals' => null,
                    'transport' => array(
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '3h dạo bãi và phố resort với HDV — phù hợp ngày nghỉ resort.',
                ),
            ),
            'inclusions' => array(
                'HDV am thực/local',
            ),
            'exclusions' => array(
                'Ăn uống',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 320000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'phan-thiet-po-sah-inu-city',
            'title' => 'Phan Thiết — Tháp Po Sah Inu & phố cũ',
            'zoneSlug' => 'phan-thiet-thanh-pho',
            'zone' => 'Phan Thiết — thành phố',
            'tourCode' => 'MN-PT-CT',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.6,
            'reviewCount' => 234,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Tháp Chăm trên đồi — góc nhìn khác hẳn resort biển.',
                'author' => 'Chị Diệu Linh',
            ),
            'places' => array(
                'Tháp Po Sah Inu',
                'Phan Thiết cảng',
                'Khu nước mắm',
            ),
            'start' => 'Ham Tien / Phan Thiết',
            'end' => 'Ham Tien / Phan Thiết',
            'highlightsIntro' => 'Nửa ngày khám phá Phan Thiết — di sản Chăm, cảng và văn hoá nước mắm.',
            'highlights' => array(
                'Tháp Po Sah Inu',
                'Cảng Phan Thiết',
                'Chợ & nước mắm',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Phan Thiet city',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '08:30–13:00 tháp Chăm, cảng, chợ, về resort.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'HDV',
                'Vé tháp',
            ),
            'exclusions' => array(
                'Ăn trưa',
            ),
            'notes' => array(
                'Ghép combo Ke Ga nếu muốn full ngày phía nam.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 420000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'ke-ga-ta-cu-ngay',
            'title' => 'Ke Ga hải đăng & Ta Cu — Tour ngày phía nam',
            'zoneSlug' => 'ke-ga-ta-cu',
            'zone' => 'Ke Ga — Ta Cu',
            'tourCode' => 'MN-KG-TC',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 156,
            'badge' => 'Phía nam',
            'featured' => true,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Hải đăng Ke Ga trên đảo đá — ít khách hơn đồi cát.',
                'author' => 'James Mitchell',
            ),
            'places' => array(
                'Hải đăng Ke Ga',
                'Núi Ta Cu',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => 'Full ngày phía nam Bình Thuận — thuyền ra Ke Ga, tuỳ chọn cáp treo Ta Cu.',
            'highlights' => array(
                'Thuyền Ke Ga lighthouse',
                'Đảo đá & sóng',
                'Ta Cu cable car (tuỳ chọn)',
                'View biển',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ke Ga & Ta Cu',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng Ke Ga thuyền, trưa hải sản địa phương, chiều Ta Cu hoặc về resort.',
                ),
            ),
            'inclusions' => array(
                'Xe',
                'Thuyền Ke Ga',
                'HDV',
                'Trưa',
            ),
            'exclusions' => array(
                'Vé cáp treo Ta Cu',
            ),
            'notes' => array(
                'Mùa mưa sóng lớn — có thể hủy thuyền Ke Ga.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 780000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'mui-ne-2n1d-resort-dunes',
            'title' => 'Mũi Né 2 ngày 1 đêm — Resort Ham Tien + đồi cát',
            'zoneSlug' => 'ham-tien-bai-bien',
            'zone' => 'Ham Tien — dải bãi biển',
            'tourCode' => 'MN2D-01',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 534,
            'badge' => 'Weekend',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'gia-dinh',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Resort 4 sao + jeep đồi cát — cuối tuần Sài Gòn chuẩn.',
                'author' => 'Chị Thùy Linh',
            ),
            'places' => array(
                'Ham Tien resort',
                'Đồi cát đỏ',
                'Suối Tiên',
            ),
            'start' => 'Mũi Né',
            'end' => 'Mũi Né',
            'highlightsIntro' => 'Gói resort 1 đêm + jeep sunrise + Suối Tiên — phù hợp khách tự túc hoặc ghép limo SGN.',
            'highlights' => array(
                'Resort 4* 1 đêm',
                'Jeep đồi cát đỏ',
                'Suối Tiên',
                'Bữa sáng resort',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Check-in — chiều biển',
                    'meals' => 'Tối (tuỳ chọn)',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Ham Tien resort',
                    'content' => 'Nhận phòng, tắm biển, tối tự do phố resort.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Jeep — Suối Tiên — trả phòng',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'jeep',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => '05:00 jeep đồi cát, Suối Tiên, trả phòng ~14:00.',
                ),
            ),
            'inclusions' => array(
                'Resort 1 đêm',
                'Jeep sunrise',
                'Suối Tiên',
                'Sáng sáng 2',
            ),
            'exclusions' => array(
                'Limousine Sài Gòn',
                'Bữa tối ngày 1',
            ),
            'notes' => array(
                'Giá resort theo hạng phòng — :brand tư vấn theo ngân sách.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 1850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'mui-ne-3n2d-kite-surf-package',
            'title' => 'Mũi Né 3 ngày 2 đêm — Kite-surf & biển (mùa gió)',
            'zoneSlug' => 'ham-tien-bai-bien',
            'zone' => 'Ham Tien — dải bãi biển',
            'tourCode' => 'MN3D-KS',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 4.9,
            'reviewCount' => 178,
            'badge' => 'Kite-surf',
            'featured' => true,
            'styles' => array(
                '3n2d',
                'kite-surf',
            ),
            'quote' => array(
                'text' => 'Nov–Mar gió ổn — 3 buổi học kite + resort, đúng thiên đường kite VN.',
                'author' => 'Anh Felix',
            ),
            'places' => array(
                'Ham Tien kite beach',
                'Kite school',
            ),
            'start' => 'Mũi Né',
            'end' => 'Mũi Né',
            'highlightsIntro' => 'Gói resort 2 đêm + 3 buổi kite lesson (hoặc windsurf) — mùa gió Nov–Mar.',
            'highlights' => array(
                '3 buổi kite lesson',
                'Resort 2 đêm',
                'Thiết bị & HLV',
                'Jeep đồi cát (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Check-in — kite intro',
                    'meals' => 'Tối tuỳ chọn',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Resort',
                    'content' => 'Nhận phòng, briefing kite, buổi 1 nếu gió phù hợp.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Kite lesson & biển',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'walking',
                    ),
                    'overnight' => 'Resort',
                    'content' => 'Buổi kite 2, tắm biển, tối câu mực (tuỳ chọn).',
                ),
                array(
                    'day' => 3,
                    'title' => 'Kite — trả phòng',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => 'Buổi kite 3, trả phòng ~12:00.',
                ),
            ),
            'inclusions' => array(
                'Resort 2 đêm',
                '3 buổi kite',
                'Thiết bị',
                'HLV',
                'Sáng mỗi ngày',
            ),
            'exclusions' => array(
                'Limousine',
                'Câu mực đêm',
            ),
            'notes' => array(
                'Mùa gió Nov–Mar — giá cao hơn 10% trong price_table_defaults.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Không biết bơi có học kite được không?',
                    'a' => 'Cần tự tin trong nước — HLV đánh giá buổi đầu; beginner lesson trên bãi cát trước khi vào nước.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 4200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'sai-gon-mui-ne-2n1d-weekend',
            'title' => 'Sài Gòn — Mũi Né 2 ngày 1 đêm (limousine + resort)',
            'zoneSlug' => 'ket-hop-sai-gon',
            'zone' => 'Kết hợp Sài Gòn',
            'tourCode' => 'SGMN2D',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 892,
            'badge' => 'Cuối tuần Sài Gòn',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'combo',
                'gia-dinh',
            ),
            'quote' => array(
                'text' => 'Thứ 7 sáng limo, tắm biển chiều, jeep sáng chủ nhật — về tối SGN.',
                'author' => 'Nhóm bạn Hà Nội',
            ),
            'places' => array(
                'Sài Gòn',
                'Ham Tien',
                'Đồi cát đỏ',
            ),
            'start' => 'Sài Gòn',
            'end' => 'Sài Gòn',
            'highlightsIntro' => 'Cuối tuần phổ biến khách Sài Gòn: limousine khứ hồi + resort + jeep bình minh.',
            'highlights' => array(
                'Limousine khứ hồi',
                'Resort 4* 1 đêm',
                'Jeep đồi cát đỏ',
                'Suối Tiên (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Sài Gòn — Mũi Né',
                    'meals' => 'Tối tuỳ chọn',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Ham Tien',
                    'content' => 'Sáng limo (~4–5h), nhận phòng, chiều biển.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Jeep — về Sài Gòn',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'jeep',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => '05:00 jeep, trả phòng, limo chiều về Sài Gòn.',
                ),
            ),
            'inclusions' => array(
                'Limousine 2 chiều',
                'Resort',
                'Jeep',
                'Sáng sáng 2',
            ),
            'exclusions' => array(
                'Bữa tối ngày 1',
                'Suối Tiên',
            ),
            'notes' => array(
                'Đặt trước T6–CN — limo full nhanh.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 2450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'sai-gon-mui-ne-tour-ngay',
            'title' => 'Sài Gòn — Mũi Né tour ngày (limousine + đồi cát)',
            'zoneSlug' => 'ket-hop-sai-gon',
            'zone' => 'Kết hợp Sài Gòn',
            'tourCode' => 'SGMN1D',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 445,
            'badge' => 'Tour ngày',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'combo',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Một ngày thấy đủ đồi cát — hơi mệt nhưng tiết kiệm nghỉ phép.',
                'author' => 'David Chen',
            ),
            'places' => array(
                'Sài Gòn',
                'Đồi cát đỏ',
                'Suối Tiên',
            ),
            'start' => 'Sài Gòn',
            'end' => 'Sài Gòn',
            'highlightsIntro' => 'Limousine sáng sớm + jeep đồi cát + Suối Tiên + limo tối về Sài Gòn — không nghỉ đêm.',
            'highlights' => array(
                'Limousine 2 chiều',
                'Jeep sunrise (hoặc chiều)',
                'Suối Tiên',
                'Trưa hải sản',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Sài Gòn — Mũi Né — Sài Gòn',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'jeep',
                    ),
                    'overnight' => null,
                    'content' => '04:30 limo đi, tới ~09:00, jeep/Suối Tiên, trưa, limo ~15:00 về Sài Gòn ~20:00.',
                ),
            ),
            'inclusions' => array(
                'Limousine',
                'Jeep',
                'Suối Tiên',
                'Trưa',
            ),
            'exclusions' => array(
                'Đồ uống',
            ),
            'notes' => array(
                'Lịch gấp — nên đi đêm trước nếu muốn sunrise đồi đỏ.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 1150000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'da-lat-mui-ne-3n2d',
            'title' => 'Đà Lạt — Mũi Né 3 ngày 2 đêm (cao nguyên + biển)',
            'zoneSlug' => 'ket-hop-da-lat',
            'zone' => 'Kết hợp Đà Lạt',
            'tourCode' => 'DLMN3D',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 4.9,
            'reviewCount' => 267,
            'badge' => 'Combo',
            'featured' => true,
            'styles' => array(
                '3n2d',
                'combo',
            ),
            'quote' => array(
                'text' => 'Đà Lạt mát, Mũi Né nóng — một chuyến hai vibe.',
                'author' => 'Khách Úc',
            ),
            'places' => array(
                'Đà Lạt',
                'Mũi Né',
                'Đồi cát',
            ),
            'start' => 'Đà Lạt / Sài Gòn',
            'end' => 'Mũi Né / Sài Gòn',
            'highlightsIntro' => '1 đêm Đà Lạt + 1 đêm Mũi Né — xe đèo 3–4h, jeep đồi cát.',
            'highlights' => array(
                'Đà Lạt city/homestay',
                'Xe đèo Prenn',
                'Resort Mũi Né',
                'Jeep đồi cát',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đà Lạt',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Đà Lạt',
                    'content' => 'Tham quan Đà Lạt, tối chợ đêm.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Đà Lạt — Mũi Né',
                    'meals' => 'Sáng; Tối tuỳ chọn',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Ham Tien',
                    'content' => 'Sáng xuống đèo, chiều tới Mũi Né, nhận phòng.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Jeep — kết thúc',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'jeep',
                    ),
                    'overnight' => null,
                    'content' => 'Jeep đồi cát, trả phòng, tiễn Sài Gòn hoặc Nha Trang.',
                ),
            ),
            'inclusions' => array(
                'Xe liên tuyến',
                'Lưu trú 2 đêm',
                'Jeep',
                'HDV',
                'Bữa chính',
            ),
            'exclusions' => array(
                'Tip',
                'Vé Đà Lạt riêng lẻ',
            ),
            'notes' => array(
                'Có thể đảo chiều Mũi Né — Đà Lạt.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 3850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'nha-trang-mui-ne-2n1d',
            'title' => 'Nha Trang — Mũi Né 2 ngày 1 đêm',
            'zoneSlug' => 'ket-hop-nha-trang',
            'zone' => 'Kết hợp Nha Trang',
            'tourCode' => 'NTMN2D',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 189,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                '2n1d',
            ),
            'quote' => array(
                'text' => 'Bay Cam Ranh, 2h ra Mũi Né — combo biển Nam Trung Bộ.',
                'author' => 'Yuna Park',
            ),
            'places' => array(
                'Nha Trang / Cam Ranh',
                'Mũi Né',
            ),
            'start' => 'Nha Trang',
            'end' => 'Nha Trang / Sài Gòn',
            'highlightsIntro' => 'Xe ~2h Nha Trang — Mũi Né + 1 đêm resort + jeep đồi cát.',
            'highlights' => array(
                'Transfer Nha Trang',
                'Resort 1 đêm',
                'Jeep sunrise',
                'Suối Tiên',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Nha Trang — Mũi Né',
                    'meals' => 'Tối tuỳ chọn',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Ham Tien',
                    'content' => 'Sáng chuyển ~2h, nhận phòng, chiều biển.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Jeep — về',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'jeep',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Jeep sáng sớm, trả phòng, về Nha Trang hoặc tiếp SGN.',
                ),
            ),
            'inclusions' => array(
                'Xe nối tuyến',
                'Resort',
                'Jeep',
                'Sáng',
            ),
            'exclusions' => array(
                'Vé bay',
            ),
            'notes' => array(
                'Phù hợp khách bay Cam Ranh.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 2650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'gia-dinh-mui-ne-2n1d',
            'title' => 'Gia đình Mũi Né 2 ngày 1 đêm — Resort, biển & jeep nhẹ',
            'zoneSlug' => 'ham-tien-bai-bien',
            'zone' => 'Ham Tien — dải bãi biển',
            'tourCode' => 'MN-FAM2D',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 312,
            'badge' => 'Gia đình',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'gia-dinh',
            ),
            'quote' => array(
                'text' => 'Con thích trượt cát — resort có hồ bơi, không cần tour gấp.',
                'author' => 'Bích Ngọc',
            ),
            'places' => array(
                'Ham Tien',
                'Đồi cát',
                'Suối Tiên',
            ),
            'start' => 'Mũi Né / Sài Gòn',
            'end' => 'Mũi Né / Sài Gòn',
            'highlightsIntro' => 'Resort family-friendly + jeep chiều (không 5h sáng) + Suối Tiên — lịch nhẹ cho trẻ em.',
            'highlights' => array(
                'Resort có hồ bơi',
                'Jeep chiều đồi cát',
                'Suối Tiên',
                'Trượt cát cho trẻ',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Resort & biển',
                    'meals' => 'Tối tuỳ chọn',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Resort',
                    'content' => 'Check-in, hồ bơi, tắm biển.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Jeep chiều — Suối Tiên',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'jeep',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng biển, 14:00 jeep (tránh nóng), Suối Tiên, trả phòng.',
                ),
            ),
            'inclusions' => array(
                'Resort 1 đêm',
                'Jeep chiều',
                'Suối Tiên',
                'Sáng',
            ),
            'exclusions' => array(
                'Limousine',
            ),
            'notes' => array(
                'Trẻ em giảm giá jeep — liên hệ tư vấn.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 2100000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'honeymoon-mui-ne-3n2d',
            'title' => 'Trăng mật Mũi Né 3 ngày 2 đêm — Resort boutique & jeep riêng',
            'zoneSlug' => 'ham-tien-bai-bien',
            'zone' => 'Ham Tien — dải bãi biển',
            'tourCode' => 'MN-HM3D',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 5,
            'reviewCount' => 98,
            'badge' => 'Honeymoon',
            'featured' => true,
            'styles' => array(
                '3n2d',
                'honeymoon',
            ),
            'quote' => array(
                'text' => 'Jeep private sunrise + dinner beach — kỷ niệm đẹp.',
                'author' => 'Cặp đôi Minh & Diệu',
            ),
            'places' => array(
                'Ham Tien boutique resort',
                'Đồi cát đỏ',
            ),
            'start' => 'Mũi Né',
            'end' => 'Mũi Né',
            'highlightsIntro' => 'Boutique resort 2 đêm, jeep private sunrise, câu mực đêm hoặc dinner biển.',
            'highlights' => array(
                'Boutique resort',
                'Jeep private',
                'Câu mực đêm hoặc dinner',
                'Trang trí phòng (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Welcome',
                    'meals' => 'Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Resort',
                    'content' => 'Check-in, welcome drink, dinner biển.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Jeep private — tự do',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'jeep',
                    ),
                    'overnight' => 'Resort',
                    'content' => 'Sunrise jeep private, chiều spa/biển, tối câu mực.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Trả phòng',
                    'meals' => 'Sáng',
                    'transport' => array(),
                    'overnight' => null,
                    'content' => 'Sáng biển, trả phòng ~12:00.',
                ),
            ),
            'inclusions' => array(
                'Resort 2 đêm',
                'Jeep private',
                '1 dinner',
                'Câu mực 1 đêm',
            ),
            'exclusions' => array(
                'Limousine',
                'Spa',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 5200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'food-tour-phan-thiet-dem',
            'title' => 'Tour ẩm thực Phan Thiết buổi tối — Hải sản & nước mắm',
            'zoneSlug' => 'phan-thiet-thanh-pho',
            'zone' => 'Phan Thiết — thành phố',
            'tourCode' => 'MN-FT-PT',
            'duration' => 'Buổi tối (3 giờ)',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 145,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'lang-chai',
            ),
            'quote' => array(
                'text' => 'Bánh căn, lẩu cá bớp, nước mắm — khác hẳn quán resort.',
                'author' => 'Food blogger Ken',
            ),
            'places' => array(
                'Phan Thiết',
                'Chợ đêm',
            ),
            'start' => 'Ham Tien / Phan Thiết',
            'end' => 'Ham Tien / Phan Thiết',
            'highlightsIntro' => '18:00–21:00 — HDV am thực dẫn quán địa phương Phan Thiết, không chỉ dải resort.',
            'highlights' => array(
                'HDV am thực',
                '5–6 món tasting',
                'Nước mắm & bánh đặc sản',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Food walk Phan Thiet',
                    'meals' => 'Tasting',
                    'transport' => array(
                        'car',
                        'walking',
                    ),
                    'overnight' => null,
                    'content' => 'Xe đón resort, 3 quán/chợ, về ~21:00.',
                ),
            ),
            'inclusions' => array(
                'HDV',
                'Tasting',
                'Xe',
            ),
            'exclusions' => array(
                'Bữa tối full course',
            ),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 480000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'tour-ngay-private-jeep-dunes',
            'title' => 'Xe riêng jeep — Đồi cát đỏ & trắng (tùy chọn)',
            'zoneSlug' => 'doi-cat-do',
            'zone' => 'Đồi cát đỏ',
            'tourCode' => 'MN-PVT-J',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 5,
            'reviewCount' => 67,
            'badge' => 'Private',
            'featured' => false,
            'styles' => array(
                'day-trip',
                'doi-cat',
            ),
            'quote' => array(
                'text' => 'Jeep riêng 4 người — lịch linh hoạt, chụp ảnh thoải mái.',
                'author' => 'Cặp đôi Bích Ngọc',
            ),
            'places' => array(
                'Đồi cát đỏ',
                'Đồi cát trắng',
                'Suối Tiên',
            ),
            'start' => 'Ham Tien',
            'end' => 'Ham Tien',
            'highlightsIntro' => 'Charter jeep 2–6 khách — chọn sunrise đỏ + trắng chiều hoặc full combo.',
            'highlights' => array(
                'Jeep riêng',
                'Giờ linh hoạt',
                'HDV riêng',
                'Trượt cát',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Xe riêng jeep charter',
                    'meals' => 'Trưa tuỳ chọn',
                    'transport' => array(
                        'jeep',
                    ),
                    'overnight' => null,
                    'content' => 'Theo lịch riêng — thường 05:00–14:00 hoặc 08:00–18:00.',
                ),
            ),
            'inclusions' => array(
                'Jeep riêng',
                'HDV',
                'Trượt cát',
            ),
            'exclusions' => array(
                'Suối Tiên vé',
                'Ăn uống',
            ),
            'notes' => array(
                'Giá theo nhóm 2 khách.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 2200000,
            'currency' => 'VND',
        ),
    ),

    'cruises' => array(
        array(
            'slug' => 'thuyen-cau-muc-dem-mui-ne',
            'title' => 'Thuyền câu mực đêm — Mũi Né',
            'typeSlug' => 'thuyen-cau-muc-dem', 'typeName' => 'Thuyền câu mực đêm',
            'tourCode' => 'BT-SQD', 'duration' => '3 giờ', 'days' => 1,
            'rating' => 4.9, 'reviewCount' => 412, 'badge' => 'Buổi tối',
            'styles' => array('fishing-village', 'seafood-culture', 'day-trip'),
            'quote' => array('text' => 'Câu mực đêm trên biển — trải nghiệm đặc trưng buổi tối Mũi Né.', 'author' => 'Chị Hải Yến'),
            'places' => array('Biển Mũi Né', 'Ham Tien'),
            'start' => 'Bến Ham Tien', 'end' => 'Bến Ham Tien',
            'departurePort' => 'Bến thuyền Ham Tien', 'boatClass' => 'Thuyền máy mui che', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '19:00–22:00 — thuyền ra biển, câu mực với ngư dân, nướng thử trên boong.',
            'highlights' => array('Câu mực đêm', 'Dụng cụ đầy đủ', 'Nướng trên thuyền', 'Đồ uống chào mừng'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Squid fishing night', 'meals' => 'Mực nướng thử', 'transport' => array('boat'), 'overnight' => null,
                    'content' => '19:00 xuất bến, câu mực, nướng, về bến ~22:00.'),
            ),
            'inclusions' => array('Thuyền', 'Dụng cụ câu', 'Áo phao', 'HDV'),
            'exclusions' => array('Đồ uống có cồn'),
            'notes' => array('Phụ thuộc thời tiết — có thể đổi ngày miễn phí.'),
            'faqs' => array(array('q' => 'Trẻ em có đi được không?', 'a' => 'Từ 5 tuổi với người lớn — mang áo phao, tránh say sóng.')),
            'galleryCount' => 4, 'priceFrom' => 450000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'thuyen-cau-muc-cooking-class',
            'title' => 'Câu mực đêm + cooking class hải sản',
            'typeSlug' => 'thuyen-cau-muc-dem', 'typeName' => 'Thuyền câu mực đêm',
            'tourCode' => 'BT-SQD-CK', 'duration' => '4 giờ', 'days' => 1,
            'rating' => 4.9, 'reviewCount' => 156, 'badge' => 'Cooking',
            'styles' => array('seafood-culture', 'family', 'day-trip'),
            'quote' => array('text' => 'Vừa câu vừa học nấu — team building hay cặp đôi đều vui.', 'author' => 'Nhóm công ty ABC'),
            'places' => array('Biển Mũi Né'),
            'start' => 'Ham Tien', 'end' => 'Ham Tien',
            'departurePort' => 'Bến Ham Tien', 'boatClass' => 'Thuyền nhóm nhỏ', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => 'Câu mực + lớp nấu món hải sản đơn giản trên boong — nhóm max 15.',
            'highlights' => array('Câu mực', 'Cooking class', 'Ăn thử trên tàu', 'Nhóm nhỏ'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Squid + cooking', 'meals' => 'Món vừa học', 'transport' => array('boat'), 'overnight' => null,
                    'content' => '19:00–23:00 câu mực, cooking, nếm thử.'),
            ),
            'inclusions' => array('Thuyền', 'Cooking class', 'Dụng cụ câu'),
            'exclusions' => array('Rượu'),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 650000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'thuyen-ngam-san-hoi-nua-ngay',
            'title' => 'Thuyền ngắm san hô & câu cá — Nửa ngày',
            'typeSlug' => 'thuyen-ngam-san-hoi', 'typeName' => 'Thuyền ngắm san hô',
            'tourCode' => 'BT-SNORK', 'duration' => '4 giờ', 'days' => 1,
            'rating' => 4.7, 'reviewCount' => 289, 'badge' => null,
            'styles' => array('adventure', 'family', 'day-trip'),
            'quote' => array('text' => 'Snorkel gần Mũi Né — đừng kỳ vọng Maldives nhưng vui cho gia đình.', 'author' => 'Gia đình chị Thảo'),
            'places' => array('Vùng biển Mũi Né'),
            'start' => 'Ham Tien', 'end' => 'Ham Tien',
            'departurePort' => 'Bến Ham Tien', 'boatClass' => 'Thuyền ngày', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '08:00–12:00 hoặc 13:00–17:00 — thuyền ra điểm snorkel, câu cá nhẹ, trái cây trên tàu.',
            'highlights' => array('Snorkel + kính ống', 'Câu cá giải trí', 'Trái cây', 'Áo phao'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Snorkel boat', 'meals' => 'Trái cây', 'transport' => array('boat'), 'overnight' => null,
                    'content' => 'Ra 2 điểm snorkel, câu cá, về bến.'),
            ),
            'inclusions' => array('Thuyền', 'Snorkel gear', 'Trái cây', 'HDV'),
            'exclusions' => array('Thuê wetsuit'),
            'notes' => array('Thủy triều ảnh hưởng điểm snorkel — HDV chọn theo ngày.'),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 550000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'thuyen-lang-chai-sang',
            'title' => 'Thuyền làng chài sáng sớm — Hải sản tươi',
            'typeSlug' => 'thuyen-ngam-san-hoi', 'typeName' => 'Thuyền ngắm san hô',
            'tourCode' => 'BT-FV-AM', 'duration' => '2 giờ', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 198, 'badge' => 'Sáng sớm',
            'styles' => array('fishing-village', 'seafood-culture', 'day-trip'),
            'quote' => array('text' => 'Thuyền về đầy ắp — mua hải sản tươi rẻ hơn resort.', 'author' => 'Chị Diệu Linh'),
            'places' => array('Làng chài Mũi Né'),
            'start' => 'Làng chài', 'end' => 'Làng chài',
            'departurePort' => 'Bến làng chài', 'boatClass' => 'Thuyền làng chài', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '06:00–08:00 — ngắm thuyền cập bến, mua hải sản (tuỳ chọn), không snorkel.',
            'highlights' => array('Thuyền cập bến', 'Chợ sáng', 'HDV làng chài'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Morning fishing boats', 'meals' => null, 'transport' => array('boat', 'walking'), 'overnight' => null,
                    'content' => 'Dạo bến làng chài khi thuyền về.'),
            ),
            'inclusions' => array('HDV', 'Xe đón resort'),
            'exclusions' => array('Mua hải sản'),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 3, 'priceFrom' => 320000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'sup-kayak-bien-ham-tien',
            'title' => 'SUP & kayak biển Ham Tien — Buổi sáng',
            'typeSlug' => 'sup-kayak-bien', 'typeName' => 'SUP & kayak biển',
            'tourCode' => 'BT-SUP', 'duration' => '2.5 giờ', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 134, 'badge' => null,
            'styles' => array('adventure', 'kite-surf', 'day-trip'),
            'quote' => array('text' => 'Kayak sáng sớm biển lặng — view dải resort từ mặt biển.', 'author' => 'Anh Felix'),
            'places' => array('Bãi Ham Tien'),
            'start' => 'Ham Tien beach', 'end' => 'Ham Tien beach',
            'departurePort' => 'Bãi biển resort', 'boatClass' => 'SUP / kayak đôi', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '07:00–09:30 — SUP hoặc kayak đôi, HLV briefing, phù hợp biết bơi cơ bản.',
            'highlights' => array('SUP hoặc kayak', 'HLV kèm', 'Áo phao', 'Nước & snack'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Chèo kayak buổi sáng', 'meals' => 'Đồ ăn nhẹ', 'transport' => array('kayak'), 'overnight' => null,
                    'content' => 'Briefing, chèo 1.5h dọc bãi Ham Tien.'),
            ),
            'inclusions' => array('Thiết bị', 'HLV', 'Snack'),
            'exclusions' => array(),
            'notes' => array('Hủy khi sóng lớn hoặc gió kite quá mạnh.'),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 380000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'sup-kayak-sunset',
            'title' => 'SUP sunset — Hoàng hôn Ham Tien',
            'typeSlug' => 'sup-kayak-bien', 'typeName' => 'SUP & kayak biển',
            'tourCode' => 'BT-SUP-SS', 'duration' => '2 giờ', 'days' => 1,
            'rating' => 4.9, 'reviewCount' => 89, 'badge' => 'Sunset',
            'styles' => array('photography', 'honeymoon', 'day-trip'),
            'quote' => array('text' => 'Hoàng hôn trên SUP — ảnh couple đẹp không cần Photoshop.', 'author' => 'Cặp đôi Minh & Diệu'),
            'places' => array('Ham Tien beach'),
            'start' => 'Ham Tien', 'end' => 'Ham Tien',
            'departurePort' => 'Bãi biển', 'boatClass' => 'SUP', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => '17:00–19:00 — SUP sunset session, nhóm nhỏ, HLV chụp ảnh gợi ý.',
            'highlights' => array('Sunset paddle', 'HLV + góc chụp', 'Nhóm max 8'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Sunset SUP', 'meals' => null, 'transport' => array('kayak'), 'overnight' => null,
                    'content' => '17:00 briefing, chèo hoàng hôn, về ~19:00.'),
            ),
            'inclusions' => array('SUP', 'HLV', 'Áo phao'),
            'exclusions' => array(),
            'notes' => array(),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 420000.0, 'currency' => 'VND',
        ),
    ),

    'blog_categories' => array(
        array('slug' => 'di-chuyen-cam-nang', 'name' => 'Di chuyển & cẩm nang', 'zoneSlug' => 'ket-hop-sai-gon', 'count' => 3),
        array('slug' => 'doi-cat-kite', 'name' => 'Đồi cát & kite-surf', 'zoneSlug' => 'doi-cat-do', 'count' => 3),
        array('slug' => 'an-uong-luu-tru', 'name' => 'Ăn uống & lưu trú', 'zoneSlug' => 'ham-tien-bai-bien', 'count' => 3),
        array('slug' => 'combo-diem-den', 'name' => 'Combo điểm đến', 'zoneSlug' => 'ket-hop-da-lat', 'count' => 2),
    ),

    'popular_keywords' => array(
        'Mũi Né từ Sài Gòn', 'Đồi cát đỏ Mũi Né', 'Kite-surf Mũi Né mùa gió', 'Suối Tiên Fairy Stream',
        'Limousine Sài Gòn Mũi Né giá', 'Mũi Né mùa nào đẹp', 'Resort Ham Tien', 'Câu mực đêm Mũi Né',
        'Đà Lạt Mũi Né combo', 'Đồi cát trắng Bàu Trắng', 'Bay Nha Trang đi Mũi Né', 'Phan Thiết Po Sah Inu',
    ),

    'articles' => array(
        array(
            'slug' => 'tu-sai-gon-di-mui-ne-the-nao',
            'title' => 'Từ Sài Gòn đi Mũi Né thế nào? Limousine, xe khách & mẹo cuối tuần',
            'zoneSlug' => 'ket-hop-sai-gon', 'zone' => 'Kết hợp Sài Gòn',
            'category' => 'Di chuyển & cẩm nang', 'categorySlug' => 'di-chuyen-cam-nang',
            'tags' => array('Di chuyển thế nào?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '05/06/2026', 'updatedAt' => '20/08/2026',
            'views' => 3120, 'rating' => 4.9, 'ratingCount' => 94,
            'excerpt' => 'Mũi Né không có sân bay — 99% khách Sài Gòn dùng limousine/xe khách 4–5 giờ. So sánh giá 250–350k/chiều và mẹo đi cuối tuần.',
            'content' => array(
                array('type' => 'p', 'text' => 'Mũi Né cách Sài Gòn khoảng 220–250km qua QL1A hoặc cao tốc — mất 4–5 giờ tùy giao thông. Không có ga tàu hoả hay sân bay tại Mũi Né.'),
                array('type' => 'h2', 'id' => 'limousine', 'text' => 'I. Limousine (phổ biến nhất)'),
                array('type' => 'p', 'text' => 'Xe 9–16 chỗ, đón quận trung tâm SGN, giá tham khảo 250.000–350.000đ/chiều. Đặt trước thứ 6–CN.'),
                array('type' => 'h2', 'id' => 'xe-khach', 'text' => 'II. Xe khách open bus'),
                array('type' => 'p', 'text' => 'Rẻ hơn limousine — tự taxi từ bến/xe đến resort Ham Tien.'),
            ),
            'faqs' => array(array('q' => 'Tour ngày từ Sài Gòn có kịp sunrise đồi cát không?', 'a' => 'Khó — nên đi đêm trước hoặc chọn jeep chiều. Tour ngày limo sáng thường tới ~09:00–10:00.')),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'mui-ne-mua-nao-dep-nhat',
            'title' => 'Mũi Né mùa nào đẹp nhất? Gió kite, biển và mưa',
            'zoneSlug' => 'ham-tien-bai-bien', 'zone' => 'Ham Tien — dải bãi biển',
            'category' => 'Ăn uống & lưu trú', 'categorySlug' => 'an-uong-luu-tru',
            'tags' => array('Mẹo du lịch', 'Chơi gì, xem gì?'),
            'author' => 'Lan Hương', 'publishedAt' => '12/06/2026', 'updatedAt' => '01/09/2026',
            'views' => 2450, 'rating' => 4.9, 'ratingCount' => 71,
            'excerpt' => 'Nov–Mar mùa gió kite-surf; May–Aug biển tắm; tránh mưa Sep–Nov nếu có thể.',
            'content' => array(
                array('type' => 'p', 'text' => 'Mũi Né khô quanh năm hơn miền Bắc. Mùa gió (Nov–Mar) là mùa kite-surf cao điểm và khách Nga/EU. Mùa hè (May–Aug) biển đẹp, khách Sài Gòn cuối tuần đông.'),
                array('type' => 'h3', 'id' => 'kite', 'text' => 'Mùa kite Nov–Mar'),
                array('type' => 'p', 'text' => 'Gió ổn định — giá resort và lesson tăng ~10%. Đặt trước 2–3 tuần.'),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'doi-cat-do-hay-doi-cat-trang',
            'title' => 'Đồi cát đỏ hay đồi cát trắng Bàu Trắng — nên chọn gì?',
            'zoneSlug' => 'doi-cat-do', 'zone' => 'Đồi cát đỏ',
            'category' => 'Đồi cát & kite-surf', 'categorySlug' => 'doi-cat-kite',
            'tags' => array('Chọn tour nào?', 'Mẹo du lịch'),
            'author' => 'Lan Hương', 'publishedAt' => '18/07/2026', 'updatedAt' => '15/08/2026',
            'views' => 1890, 'rating' => 4.9, 'ratingCount' => 58,
            'excerpt' => 'Đỏ gần, sunrise iconic; trắng xa ~30km, hoang sơ hơn — có thể gộp tour ngày.',
            'content' => array(
                array('type' => 'p', 'text' => 'Đồi cát đỏ cách Ham Tien ~15 phút — điểm sunrise không thể bỏ qua. Đồi cát trắng/Bàu Trắng xa hơn, cát trắng, ít người hơn buổi sáng.'),
                array('type' => 'h2', 'id' => 'ket-luan', 'text' => 'Kết luận'),
                array('type' => 'p', 'text' => 'Lần đầu: cả hai trong tour ngày. Chỉ có 1 buổi: chọn đỏ sunrise hoặc trắng chiều.'),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'kite-surf-mui-ne-huong-dan',
            'title' => 'Kite-surf Mũi Né — Hướng dẫn cho người mới (mùa gió)',
            'zoneSlug' => 'ham-tien-bai-bien', 'zone' => 'Ham Tien — dải bãi biển',
            'category' => 'Đồi cát & kite-surf', 'categorySlug' => 'doi-cat-kite',
            'tags' => array('Chơi gì, xem gì?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '01/07/2026', 'updatedAt' => '10/08/2026',
            'views' => 1560, 'rating' => 4.8, 'ratingCount' => 45,
            'excerpt' => 'Thiên đường kite Việt Nam — Nov–Mar, học 3–5 buổi, giá lesson và chọn school Ham Tien.',
            'content' => array(
                array('type' => 'p', 'text' => 'Mũi Né được kite-surfer quốc tế công nhận — gió cross-onshore ổn định Nov–Mar. Nhiều school dọc Nguyễn Đình Chiểu.'),
                array('type' => 'ul', 'items' => array(
                    'Beginner: 2–3 buổi trên bãi + nước',
                    'Cần tự tin bơi — HLV đánh giá buổi 1',
                    'Giá lesson ~800k–1.2M/buổi tham khảo',
                )),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'an-gi-o-mui-ne',
            'title' => 'Ăn gì ở Mũi Né? Hải sản, nước mắm Phan Thiết & quán resort',
            'zoneSlug' => 'lang-chai-mui-ne', 'zone' => 'Làng chài Mũi Né',
            'category' => 'Ăn uống & lưu trú', 'categorySlug' => 'an-uong-luu-tru',
            'tags' => array('Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '15/06/2026', 'updatedAt' => '05/07/2026',
            'views' => 1120, 'rating' => 4.7, 'ratingCount' => 38,
            'excerpt' => 'Lẩu cá bớp, bánh căn, nước mắm Phan Thiết — dải resort vs Phan Thiết city.',
            'content' => array(
                array('type' => 'p', 'text' => 'Resort strip tiện nhưng giá cao hơn — nên thử Phan Thiết city hoặc làng chài sáng sớm cho hải sản tươi.'),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'o-dau-ham-tien-resort',
            'title' => 'Ở đâu Mũi Né: Ham Tien dải resort hay Phan Thiết?',
            'zoneSlug' => 'ham-tien-bai-bien', 'zone' => 'Ham Tien — dải bãi biển',
            'category' => 'Ăn uống & lưu trú', 'categorySlug' => 'an-uong-luu-tru',
            'tags' => array('Ở đâu?', 'Mẹo du lịch'),
            'author' => 'Phạm Thị Liên', 'publishedAt' => '10/07/2026', 'updatedAt' => '12/08/2026',
            'views' => 980, 'rating' => 4.6, 'ratingCount' => 29,
            'excerpt' => 'Ham Tien — bãi biển, kite, quán ven đường. Phan Thiết — gần tháp Chăm, giá rẻ hơn, cách bãi ~15 phút.',
            'content' => array(
                array('type' => 'p', 'text' => 'Đa số khách chọn Ham Tien (Nguyễn Đình Chiểu) để đi bộ ra bãi và jeep đồi cát. Phan Thiết city phù hợp ghép tham quan tháp Po Sah Inu.'),
            ),
            'faqs' => array(),
            'galleryCount' => 3,
        ),
        array(
            'slug' => 'ket-hop-da-lat-mui-ne',
            'title' => 'Kết hợp Đà Lạt và Mũi Né — Lịch trình 3–4 ngày gợi ý',
            'zoneSlug' => 'ket-hop-da-lat', 'zone' => 'Kết hợp Đà Lạt',
            'category' => 'Combo điểm đến', 'categorySlug' => 'combo-diem-den',
            'tags' => array('Chọn tour nào?', 'Mẹo du lịch'),
            'author' => 'Lan Hương', 'publishedAt' => '02/08/2026', 'updatedAt' => '20/08/2026',
            'views' => 870, 'rating' => 4.8, 'ratingCount' => 32,
            'excerpt' => 'Cao nguyên mát + biển nóng — xe đèo 3–4h, gợi ý 1N Đà Lạt + 2N Mũi Né.',
            'content' => array(
                array('type' => 'p', 'text' => 'Combo phổ biến khách quốc tế và nội địa: bay Sài Gòn, Đà Lạt 1–2 đêm, xuống đèo Mũi Né 2 đêm. :brand có tour combo sẵn.'),
            ),
            'faqs' => array(),
            'galleryCount' => 3,
        ),
        array(
            'slug' => 'chi-phi-mui-ne-2n1d',
            'title' => 'Chi phí Mũi Né 2 ngày 1 đêm hết bao nhiêu? Bảng 2026',
            'zoneSlug' => 'ham-tien-bai-bien', 'zone' => 'Ham Tien — dải bãi biển',
            'category' => 'Ăn uống & lưu trú', 'categorySlug' => 'an-uong-luu-tru',
            'tags' => array('Mẹo du lịch', 'Chọn tour nào?'),
            'author' => 'Lan Hương', 'publishedAt' => '20/06/2026', 'updatedAt' => '01/09/2026',
            'views' => 1680, 'rating' => 4.9, 'ratingCount' => 62,
            'excerpt' => 'Budget 1.5M, tầm trung 2–3M, resort 4–6M+ — gồm limo SGN, jeep và ăn uống.',
            'content' => array(
                array('type' => 'ul', 'items' => array(
                    'Tiết kiệm: ~1.5–2M (xe khách + homestay + jeep ghép)',
                    'Tầm trung: ~2.5–3.5M (limo + resort 3–4* + tour ngày)',
                    'Resort 4–5*: từ 4M+ (limo + resort + private jeep)',
                )),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'suoi-tien-kinh-nghiem',
            'title' => 'Suối Tiên (Fairy Stream) — Kinh nghiệm đi bộ & mẹo chụp ảnh',
            'zoneSlug' => 'suoi-tien', 'zone' => 'Suối Tiên',
            'category' => 'Đồi cát & kite-surf', 'categorySlug' => 'doi-cat-kite',
            'tags' => array('Chơi gì, xem gì?', 'Mẹo du lịch'),
            'author' => 'Phạm Thị Liên', 'publishedAt' => '08/08/2026', 'updatedAt' => '25/08/2026',
            'views' => 720, 'rating' => 4.7, 'ratingCount' => 24,
            'excerpt' => 'Đi barefoot, mang dép — suối đỏ giữa vách đá, ghép tour đồi cát buổi sáng.',
            'content' => array(
                array('type' => 'p', 'text' => 'Suối Tiên gần đồi cát đỏ — thường ghép sau jeep sunrise. Vé ~15–30k, đi 1–1.5h.'),
            ),
            'faqs' => array(),
            'galleryCount' => 3,
        ),
        array(
            'slug' => 'phan-thiet-thanh-pho-di-gi',
            'title' => 'Phan Thiết thành phố — Tháp Po Sah Inu, cảng & nước mắm',
            'zoneSlug' => 'phan-thiet-thanh-pho', 'zone' => 'Phan Thiết — thành phố',
            'category' => 'Di chuyển & cẩm nang', 'categorySlug' => 'di-chuyen-cam-nang',
            'tags' => array('Chơi gì, xem gì?', 'Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '22/07/2026', 'updatedAt' => '28/08/2026',
            'views' => 650, 'rating' => 4.6, 'ratingCount' => 19,
            'excerpt' => 'Nửa ngày khám phá Phan Thiết — bổ sung resort beach, không thay đồi cát.',
            'content' => array(
                array('type' => 'p', 'text' => 'Tháp Chăm Po Sah Inu trên đồi nhìn Phan Thiết, cảng cá và làng nước mắm — tour nửa ngày từ Ham Tien.'),
            ),
            'faqs' => array(),
            'galleryCount' => 3,
        ),
        array(
            'slug' => 'bay-nha-trang-di-mui-ne',
            'title' => 'Bay Nha Trang/Cam Ranh đi Mũi Né — Transfer và lịch gợi ý',
            'zoneSlug' => 'ket-hop-nha-trang', 'zone' => 'Kết hợp Nha Trang',
            'category' => 'Di chuyển & cẩm nang', 'categorySlug' => 'di-chuyen-cam-nang',
            'tags' => array('Di chuyển thế nào?'),
            'author' => 'Minh Trí', 'publishedAt' => '01/08/2026', 'updatedAt' => '15/08/2026',
            'views' => 540, 'rating' => 4.7, 'ratingCount' => 16,
            'excerpt' => 'Sân bay Cam Ranh (CXR) — xe ~2h tới Mũi Né — alternative khi không bay Sài Gòn.',
            'content' => array(
                array('type' => 'p', 'text' => 'Khách bay quốc tế vào CXR có thể nghỉ Nha Trang 1 đêm hoặc chuyển thẳng Mũi Né ~2 giờ. :brand gộp transfer + resort.'),
            ),
            'faqs' => array(),
            'galleryCount' => 3,
        ),
    ),

    'testimonials' => array(
        array('name' => 'Nguyễn Minh Anh', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0, 'quote' => 'Cuối tuần Sài Gòn — limo đón đúng giờ, jeep đồi cát đỏ bình minh đẹp tuyệt.', 'photos' => 5, 'trip' => 'Sài Gòn — Mũi Né 2 ngày 1 đêm', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'James Mitchell', 'country' => 'Anh', 'flag' => '🇬🇧', 'rating' => 5.0, 'quote' => 'Kite lessons in November — exactly as windy as promised.', 'photos' => 8, 'trip' => 'Kite-surf 3 ngày 2 đêm package', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Hải Yến', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.9, 'quote' => 'Câu mực đêm vui hơn tưởng — con 8 tuổi thích mực nướng.', 'photos' => 4, 'trip' => 'Thuyền câu mực đêm', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Ivan Petrov', 'country' => 'Nga', 'flag' => '🇷🇺', 'rating' => 5.0, 'quote' => 'Winter in Mui Ne — kite every day, resort strip convenient.', 'photos' => 6, 'trip' => 'Ham Tien resort + kite', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'David Chen', 'country' => 'Úc', 'flag' => '🇦🇺', 'rating' => 4.8, 'quote' => 'Dalat + Mui Ne combo saved us planning — one quote, smooth transfers.', 'photos' => 6, 'trip' => 'Đà Lạt — Mũi Né 3 ngày 2 đêm', 'avatar' => null, 'photoUrls' => array()),
        array('name' => 'Bích Ngọc', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0, 'quote' => 'Gia đình jeep chiều — con trượt cát không sợ nóng 5h sáng.', 'photos' => 5, 'trip' => 'Gia đình 2 ngày 1 đêm', 'avatar' => null, 'photoUrls' => array()),
    ),

    'team' => array(
        array(
            'slug' => 'le-van-phuc', 'name' => 'Lê Văn Phúc', 'role' => 'Giám đốc điều hành',
            'bio' => 'Gốc Phan Thiết, hơn 14 năm vận hành dải resort Ham Tien và tour đồi cát...',
            'phone' => '+84 252 384 8888', 'email' => 'phuc.le@himuine.dev', 'area' => 'Mũi Né & Phan Thiết',
            'years_experience' => 14, 'languages' => array('Tiếng Việt', 'English', 'Русский'),
            'stat_clients' => 4800, 'stat_tours' => 920, 'stat_awards' => 4, 'is_verified' => true,
            'bio_html' => '<p>14 năm kết nối khách quốc tế và Sài Gòn cuối tuần với resort Ham Tien — minh bạch jeep và limo.</p>',
            'bio_html_en' => '<p>14 years connecting international guests and Saigon weekends with Ham Tien resorts — transparent jeep and limo pricing.</p>',
            'name_en' => 'Le Van Phuc', 'role_en' => 'Chief Executive Officer',
            'short_bio_en' => 'Phan Thiet native — 14 years Mui Ne operations.',
            'achievements' => array('50+ resort đối tác Ham Tien', 'Kite-surf school network'),
            'skills' => array(array('skill' => 'Vận hành resort strip', 'percent' => 97), array('skill' => 'Thị trường Nga/EU', 'percent' => 94)),
            'experiences' => array(array('title' => 'CEO', 'company' => 'Hi Mũi Né', 'items' => array('Chiến lược biển & kite'))),
            'degrees' => array(array('title' => 'Cử nhân QTDN', 'school' => 'ĐH Kinh tế TP.HCM', 'items' => array())),
        ),
        array(
            'slug' => 'tran-thi-mai', 'name' => 'Trần Thị Mai', 'role' => 'Trưởng phòng thiết kế tour',
            'bio' => 'Thiết kế tour đồi cát, combo Đà Lạt/Nha Trang và gói gia đình...',
            'phone' => '+84 252 384 8889', 'email' => 'mai.tran@himuine.dev', 'area' => 'Mũi Né & Nam Trung Bộ',
            'years_experience' => 10, 'languages' => array('Tiếng Việt', 'English', 'Français'),
            'stat_clients' => 3200, 'stat_tours' => 580, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Trần Thị Mai chuyên lịch trình đồi cát + Suối Tiên và combo đa điểm — tránh trùng lặp SEO tour và hướng dẫn.</p>',
            'bio_html_en' => '<p>Mai designs dune + Fairy Stream itineraries and multi-destination combos with clear SEO positioning.</p>',
            'name_en' => 'Tran Thi Mai', 'role_en' => 'Head of Tour Design',
            'short_bio_en' => 'Dune tours and South Central combos expert.',
            'achievements' => array('Combo Đà Lạt + Mũi Né 4.9/5'),
            'skills' => array(array('skill' => 'Thiết kế tour', 'percent' => 96), array('skill' => 'Combo đa điểm', 'percent' => 94)),
            'experiences' => array(array('title' => 'Head of Design', 'company' => 'Hi Mũi Né', 'items' => array('Tour & resort packages'))),
            'degrees' => array(array('title' => 'Cử nhân Địa lý DL', 'school' => 'ĐH KHTN', 'items' => array())),
        ),
        array(
            'slug' => 'nguyen-hoang-son', 'name' => 'Nguyễn Hoàng Sơn', 'role' => 'Trưởng đội jeep & làng chài',
            'bio' => 'Gia đình ngư dân Mũi Né — điều phối jeep sunrise và thuyền làng chài...',
            'phone' => '+84 252 384 8890', 'email' => 'son.nguyen@himuine.dev', 'area' => 'Đồi cát & làng chài',
            'years_experience' => 12, 'languages' => array('Tiếng Việt', 'English cơ bản'),
            'stat_clients' => 6100, 'stat_tours' => 1400, 'stat_awards' => 3, 'is_verified' => true,
            'bio_html' => '<p>Nguyễn Hoàng Sơn quản lý đội jeep và làng chài — biết thủy triều, gió kite và giờ thuyền về bến.</p>',
            'bio_html_en' => '<p>Hoang Son manages jeep fleet and fishing village ops — knows tides, kite wind and boat return times.</p>',
            'name_en' => 'Nguyen Hoang Son', 'role_en' => 'Head of Jeep & Fishing Village Ops',
            'short_bio_en' => 'Fishing family — jeep sunrise and village boats.',
            'achievements' => array('2000+ jeep sunrise tours'), 'skills' => array(array('skill' => 'Jeep & làng chài', 'percent' => 98)),
            'experiences' => array(array('title' => 'Fleet ops', 'company' => 'Hi Mũi Né', 'items' => array('Jeep & thuyền ngày'))),
            'degrees' => array(array('title' => 'Nghề lái jeep du lịch', 'school' => 'Đối tác địa phương', 'items' => array())),
        ),
        array(
            'slug' => 'pham-linh-chi', 'name' => 'Phạm Linh Chi', 'role' => 'Chuyên gia kite-surf & tư vấn cao cấp',
            'bio' => 'Tư vấn kite-surf mùa gió, resort boutique và khách honeymoon...',
            'phone' => '+84 252 384 8891', 'email' => 'chi.pham@himuine.dev', 'area' => 'Ham Tien & Sài Gòn',
            'years_experience' => 8, 'languages' => array('Tiếng Việt', 'English', 'Русский'),
            'stat_clients' => 2100, 'stat_tours' => 340, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Phạm Linh Chi là đầu mối kite school, windsurf và khách lẻ quốc tế mùa Nov–Mar.</p>',
            'bio_html_en' => '<p>Linh Chi leads kite school, windsurf and international FIT advice Nov–Mar.</p>',
            'name_en' => 'Pham Linh Chi', 'role_en' => 'Senior Kite-surf & Travel Consultant',
            'short_bio_en' => 'Wind season, boutique resorts and honeymoons.',
            'achievements' => array('300+ kite packages booked'), 'skills' => array(array('skill' => 'Kite-surf tư vấn', 'percent' => 96)),
            'experiences' => array(array('title' => 'Consultant', 'company' => 'Hi Mũi Né', 'items' => array('Kite & luxury FIT'))),
            'degrees' => array(array('title' => 'Cử nhân NN Anh', 'school' => 'ĐH HCM', 'items' => array())),
        ),
    ),

    'videos' => array(
        array('title' => 'Sunrise đồi cát đỏ Mũi Né', 'description' => 'Jeep timelapse bình minh trên cát đỏ.', 'date' => '18/08/2026', 'duration' => '05:20', 'tag' => 'Đồi cát',
            'image' => 'https://i.ytimg.com/vi/MN00000001/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/MN00000001?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'MN00000001'),
        array('title' => 'Kite-surf Ham Tien — mùa gió', 'description' => 'Nov–Mar trên bãi Nguyễn Đình Chiểu.', 'date' => '05/07/2026', 'duration' => '07:10', 'tag' => 'Kite-surf',
            'image' => 'https://i.ytimg.com/vi/MN00000002/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/MN00000002?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'MN00000002'),
        array('title' => 'Câu mực đêm Mũi Né', 'description' => 'Buổi tối trên biển với ngư dân.', 'date' => '22/06/2026', 'duration' => '04:40', 'tag' => 'Thuyền biển',
            'image' => 'https://i.ytimg.com/vi/MN00000003/hqdefault.jpg', 'imageSrcset' => null,
            'embedUrl' => 'https://www.youtube.com/embed/MN00000003?autoplay=1&rel=0', 'provider' => 'youtube', 'youtubeId' => 'MN00000003'),
    ),

    'gallery_albums' => array(
        array('title' => 'Đồi cát đỏ sunrise — đoàn SGN', 'photos' => 22, 'date' => '08/2026'),
        array('title' => 'Kite-surf mùa gió 2025', 'photos' => 18, 'date' => '02/2026'),
        array('title' => 'Gia đình Ham Tien resort', 'photos' => 15, 'date' => '07/2026'),
        array('title' => 'Suối Tiên & Bàu Trắng', 'photos' => 20, 'date' => '06/2026'),
    ),

    'usps' => array(
        array('icon' => 'compass', 'sort' => 0,
            'vi' => array('title' => 'chuyên gia Mũi Né & Ham Tien', 'description' => 'Đội ngũ gốc Phan Thiết — biết jeep sunrise, kite mùa gió và dải resort Nguyễn Đình Chiểu.'),




'en' => array('title' => 'Mui Ne & Ham Tien experts', 'description' => 'Phan Thiet-born team — jeep sunrise, wind season kite and Nguyen Dinh Chieu dải resort.')),
        array('icon' => 'refund', 'sort' => 1,
            'vi' => array('title' => 'giá minh bạch, đổi ngày khi biển động', 'description' => 'Báo giá trọn gói; đổi ngày miễn phí khi thời tiết không an toàn cho thuyền/jeep.'),




'en' => array('title' => 'clear pricing & weather rescheduling', 'description' => 'All-in quotes; free rebooking when weather makes boats or jeeps unsafe.')),
        array('icon' => 'boat', 'sort' => 2,
            'vi' => array('title' => 'thuyền biển thật — không nhầm du thuyền', 'description' => 'Câu mực đêm, snorkel, SUP — trải nghiệm ngày trên biển, không cabin qua đêm.'),




'en' => array('title' => 'real sea boats — not overnight cruises', 'description' => 'Squid fishing, snorkel, SUP — day sea experiences, no overnight cabins.')),
        array('icon' => 'support', 'sort' => 3,
            'vi' => array('title' => 'Limousine Sài Gòn & bay Cam Ranh một đầu mối', 'description' => 'Gộp xe Sài Gòn — Mũi Né 250–350k/chiều, transfer Cam Ranh ~2h và resort một báo giá.'),




'en' => array('title' => 'limo Sài Gòn & CXR flights in one place', 'description' => 'Bundle Saigon — Mui Ne limo VND 250–350k each way, Cam Ranh ~2h transfers and resorts in one quote.')),
    ),

    'offices' => array(
        array('city' => 'Mũi Né — Ham Tien', 'address' => 'Tầng 1, 120 Nguyễn Đình Chiểu, phường Hàm Tiến', 'phone' => '+84 252 384 8888'),
        array('city' => 'Phan Thiết', 'address' => 'Số 45 Trần Hưng Đạo, phường Phan Thiết', 'phone' => '+84 252 384 8889'),
        array('city' => 'Sài Gòn (đặt tour)', 'address' => 'Quận 1 — văn phòng đại diện', 'phone' => '+84 28 3999 8888'),
    ),

    'values' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi cuối tuần Sài Gòn được chăm như khách resort cao cấp'),



'en' => array('name' => 'Dedication', 'desc' => 'Every Saigon cuối tuần treated with VIP resort care')),
        array('vi' => array('name' => 'Am hiểu biển', 'desc' => 'Biết gió kite, thủy triều và giờ jeep đẹp'),



'en' => array('name' => 'Sea knowledge', 'desc' => 'We know kite wind, tides and best jeep times')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Không oversell resort — tư vấn đúng ngân sách'),



'en' => array('name' => 'Honesty', 'desc' => 'No overselling resorts — advice matched to budget')),
        array('vi' => array('name' => 'Trách nhiệm', 'desc' => 'Bảo vệ làng chài và môi trường đồi cát'),



'en' => array('name' => 'Responsibility', 'desc' => 'Protecting fishing villages and dune environment')),
    ),
    'value_definitions' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi cuối tuần Sài Gòn được chăm như khách resort cao cấp'),



'en' => array('name' => 'Dedication', 'desc' => 'Every Saigon cuối tuần treated with VIP resort care')),
        array('vi' => array('name' => 'Am hiểu biển', 'desc' => 'Biết gió kite, thủy triều và giờ jeep đẹp'),



'en' => array('name' => 'Sea knowledge', 'desc' => 'We know kite wind, tides and best jeep times')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Không oversell resort — tư vấn đúng ngân sách'),



'en' => array('name' => 'Honesty', 'desc' => 'No overselling resorts — advice matched to budget')),
        array('vi' => array('name' => 'Trách nhiệm', 'desc' => 'Bảo vệ làng chài và môi trường đồi cát'),



'en' => array('name' => 'Responsibility', 'desc' => 'Protecting fishing villages and dune environment')),
    ),

    'reasons' => array(
        array('vi' => array('title' => 'Đối tác resort Ham Tien trực tiếp', 'desc' => 'Giá phòng và gói jeep chính xác.'),



'en' => array('title' => 'Direct Ham Tien resort partners', 'desc' => 'Accurate room rates and jeep bundles.')),
        array('vi' => array('title' => 'Kite-surf mùa gió Nov–Mar', 'desc' => 'Gói lesson + resort — một đầu mối.'),



'en' => array('title' => 'Nov–Mar kite-surf packages', 'desc' => 'Lesson + resort bundles — single contact.')),
        array('vi' => array('title' => 'Limousine Sài Gòn & transfer Cam Ranh', 'desc' => 'Cuối tuần và khách bay Nha Trang.'),



'en' => array('title' => 'limo Sài Gòn & CXR transfers', 'desc' => 'Weekends and guests flying to Nha Trang.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7 trên biển', 'desc' => 'Hotline khi thời tiết đổi thuyền/jeep.'),



'en' => array('title' => '24/7 sea support', 'desc' => 'Hotline when weather changes boat/jeep plans.')),
        array('vi' => array('title' => 'Cẩm nang tách biệt trang tour', 'desc' => 'Hướng dẫn di chuyển, đồi cát, kite — SEO không trùng lặp.'),



'en' => array('title' => 'Separate guide articles', 'desc' => 'Transport, dunes, kite guides — SEO without cannibalizing tours.')),
    ),
    'reason_definitions' => array(
        array('vi' => array('title' => 'Đối tác resort Ham Tien trực tiếp', 'desc' => 'Giá phòng và gói jeep chính xác.'),



'en' => array('title' => 'Direct Ham Tien resort partners', 'desc' => 'Accurate room rates and jeep bundles.')),
        array('vi' => array('title' => 'Kite-surf mùa gió Nov–Mar', 'desc' => 'Gói lesson + resort — một đầu mối.'),



'en' => array('title' => 'Nov–Mar kite-surf packages', 'desc' => 'Lesson + resort bundles — single contact.')),
        array('vi' => array('title' => 'Limousine Sài Gòn & transfer Cam Ranh', 'desc' => 'Cuối tuần và khách bay Nha Trang.'),



'en' => array('title' => 'limo Sài Gòn & CXR transfers', 'desc' => 'Weekends and guests flying to Nha Trang.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7 trên biển', 'desc' => 'Hotline khi thời tiết đổi thuyền/jeep.'),



'en' => array('title' => '24/7 sea support', 'desc' => 'Hotline when weather changes boat/jeep plans.')),
        array('vi' => array('title' => 'Cẩm nang tách biệt trang tour', 'desc' => 'Hướng dẫn di chuyển, đồi cát, kite — SEO không trùng lặp.'),



'en' => array('title' => 'Separate guide articles', 'desc' => 'Transport, dunes, kite guides — SEO without cannibalizing tours.')),
    ),

    'reference_persons' => array(
        array('name' => 'Mr. Ivan Petrov', 'country' => 'Nga', 'email' => 'ivan@himuine.example', 'phone' => '+7 900 123 4567', 'skype' => 'ivan.petrov.ru', 'image' => null, 'imageSrcset' => null),
        array('name' => 'Ms. Emma Wilson', 'country' => 'Anh', 'email' => 'emma@himuine.example', 'phone' => '+44 7700 900456', 'skype' => 'emma.wilson.uk', 'image' => null, 'imageSrcset' => null),
    ),

    'about_page' => array(
        'vi' => array(
            'seo_title' => 'Về chúng tôi — Hi Mũi Né, chuyên gia biển Phan Thiết',
            'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ Hi Mũi Né — resort Ham Tien, đồi cát, kite-surf và limousine Sài Gòn.',
            'page_title' => 'Về chúng tôi',
            'page_subtitle' => 'Mũi Né chân thật — thiết kế bởi người am hiểu biển & cát',
            'banner' => array('src' => null, 'srcset' => null, 'alt' => 'Đội ngũ Hi Mũi Né'),
            'mission' => array('title' => 'Sứ mệnh', 'text' => 'Kết nối du khách với Mũi Né chân thật — đồi cát, làng chài, kite-surf và resort minh bạch.', 'image' => null, 'imageSrcset' => null),
            'vision' => array('title' => 'Tầm nhìn', 'text' => 'Cầu nối tin cậy nhất giữa du khách và Mũi Né — đúng resort, đúng mùa gió, đúng tour.', 'image' => null, 'imageSrcset' => null),
            'sales_policy' => array('title' => 'Chính sách minh bạch', 'content' => 'Báo giá liệt kê rõ resort, jeep, thuyền. Trẻ em giảm theo bảng. Đổi ngày miễn phí khi biển động không an toàn.', 'cta_label' => 'Hỏi thêm', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'values_section' => array('title' => 'Giá trị cốt lõi', 'hub_label' => 'Giá trị', 'eyebrow' => 'Điều chúng tôi tin', 'subtitle' => 'Bốn giá trị dẫn dắt mọi booking Mũi Né.'),
            'reasons_section' => array('title' => 'Vì sao chọn Hi Mũi Né?', 'eyebrow' => 'Lý do', 'subtitle' => 'Ham Tien, kite, limo SGN.', 'cta_label' => 'Bắt đầu', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'reference_section' => array('title' => 'Đại diện nước ngoài', 'eyebrow' => 'Toàn cầu', 'subtitle' => 'Liên hệ bằng ngôn ngữ của bạn.'),
        ),




'en' => array(
            'seo_title' => 'About Hi Mui Ne — Phan Thiet beach experts',
            'seo_description' => 'Our story, team and mission — Ham Tien resorts, sand dunes, kite-surf and Saigon limousine.',
            'page_title' => 'About us',
            'page_subtitle' => 'Authentic Mui Ne — designed by sea & sand insiders',
            'banner' => array('src' => null, 'srcset' => null, 'alt' => 'Hi Mui Ne team'),
            'mission' => array('title' => 'Mission', 'text' => 'Connect travellers with authentic Mui Ne — dunes, fishing village, kite-surf and transparent resorts.', 'image' => null, 'imageSrcset' => null),
            'vision' => array('title' => 'Vision', 'text' => 'The most trusted bridge to Mui Ne — right resort, right wind season, right tour.', 'image' => null, 'imageSrcset' => null),
            'sales_policy' => array('title' => 'Transparent policy', 'content' => 'Quotes list resort, jeep and boat clearly. Free rebooking when sea conditions are unsafe.', 'cta_label' => 'Ask us', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'values_section' => array('title' => 'Core values', 'hub_label' => 'Values', 'eyebrow' => 'What we believe', 'subtitle' => 'Four values guiding every Mui Ne booking.'),
            'reasons_section' => array('title' => 'Why Hi Mui Ne?', 'eyebrow' => 'Why us', 'subtitle' => 'Ham Tien, kite, limo Sài Gòn.', 'cta_label' => 'Start planning', 'cta_url' => null, 'image' => null, 'imageSrcset' => null),
            'reference_section' => array('title' => 'Representatives abroad', 'eyebrow' => 'Global', 'subtitle' => 'Contact us in your language.'),
        ),
    ),

    'hero_pills' => array(
        array('zone_slug' => 'doi-cat-do', 'vi' => array('label' => 'Đồi cát đỏ'),



'en' => array('label' => 'Red dunes'), 'url' => '/diem-den/doi-cat-do'),
        array('zone_slug' => 'ham-tien-bai-bien', 'vi' => array('label' => 'Kite-surf Ham Tien'),



'en' => array('label' => 'Ham Tien kite-surf'), 'url' => '/diem-den/ham-tien-bai-bien'),
    ),

    'home_sections' => array(
        'company_intro' => array(
            'vi' => array('key' => 'company_intro', 'eyebrow' => 'Chuyên gia Mũi Né', 'title' => 'Biển Phan Thiết — thiết kế bởi người am hiểu cát & gió', 'subtitle' => null,
                'body' => 'Hi Mũi Né kết nối du khách với <strong class="font-semibold text-ink">resort Ham Tiến</strong>, jeep đồi cát, kite-surf mùa gió, thuyền câu mực và limousine Sài Gòn — Mũi Né. Không có sân bay tại Mũi Né — bay Sài Gòn hoặc Cam Ranh + xe.',
                'metaLine' => 'Giấy phép kinh doanh dịch vụ lữ hành số 0055/2024/TCDL-GPLHQT', 'ctaLabel' => 'Về chúng tôi', 'ctaUrl' => '/ve-chung-toi', 'image' => null, 'imageAlt' => 'Hi Mũi Né'),




'en' => array('key' => 'company_intro', 'eyebrow' => 'Mui Ne experts', 'title' => 'Phan Thiet sea — designed by sand & wind insiders', 'subtitle' => null,
                'body' => 'Hi Mui Ne connects you with Ham Tien resorts, dune jeeps, wind-season kite-surf, squid boats and Saigon — Mui Ne limousines. No airport at Mui Ne — bay Sài Gòn or Cam Ranh + road.',
                'metaLine' => 'Travel licence No. 0055/2024/TCDL-GPLHQT', 'ctaLabel' => 'About us', 'ctaUrl' => '/ve-chung-toi', 'image' => null, 'imageAlt' => 'Hi Mui Ne'),
        ),
        'featured_tours' => array(
            'vi' => array('key' => 'featured_tours', 'eyebrow' => 'Tour & combo', 'title' => 'Tour được đặt nhiều nhất', 'subtitle' => 'Đồi cát, cuối tuần Sài Gòn, combo Đà Lạt — tách mục thuyền biển.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_tours', 'eyebrow' => 'Tours & combos', 'title' => 'Most booked tours', 'subtitle' => 'Dunes, cuối tuần Sài Gòns, Dalat combos — separate from boat hub.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'featured_cruises' => array(
            'vi' => array('key' => 'featured_cruises', 'eyebrow' => 'Thuyền biển', 'title' => 'Trải nghiệm trên biển', 'subtitle' => 'Câu mực đêm, snorkel, SUP — không cabin qua đêm.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_cruises', 'eyebrow' => 'Sea boats', 'title' => 'On-the-water experiences', 'subtitle' => 'Squid fishing, snorkel, SUP — no overnight cabins.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'featured_trains' => array(
            'vi' => array('key' => 'featured_trains', 'eyebrow' => 'Ra Mũi Né dễ dàng', 'title' => 'Limousine & xe Sài Gòn — Mũi Né', 'subtitle' => 'Xe 9–16 chỗ 4–5 giờ, đón tận nơi — không tàu hoả.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'featured_trains', 'eyebrow' => 'Easy Mui Ne access', 'title' => 'Limousine & bus Saigon — Mui Ne', 'subtitle' => '9–16 seat vans in 4–5 hours, hotel pickup — not a railway.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'support_services' => array(
            'vi' => array('key' => 'support_services', 'eyebrow' => 'Dịch vụ bổ trợ', 'title' => 'Kite, jeep & làng chài', 'subtitle' => 'Lesson kite, jeep đồi cát, tour ẩm thực — ghép lịch riêng.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'support_services', 'eyebrow' => 'Add-on services', 'title' => 'Kite, jeep & fishing village', 'subtitle' => 'Kite lessons, dune jeeps, tour ẩm thựcs — mix into your plan.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'destinations' => array(
            'vi' => array('key' => 'destinations', 'eyebrow' => 'Khắp Mũi Né', 'title' => 'Điểm đến được yêu thích', 'subtitle' => 'Ham Tien, đồi cát, Suối Tiên, combo Đà Lạt & SGN.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'destinations', 'eyebrow' => 'Across Mui Ne', 'title' => 'Popular destinations', 'subtitle' => 'Ham Tien, dunes, Fairy Stream, Dalat & SGN combos.', 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'testimonials' => array(
            'vi' => array('key' => 'testimonials', 'eyebrow' => 'Khách kể lại', 'title' => 'Trải nghiệm thật', 'subtitle' => 'Hơn 4.000 khách đã nghỉ dưỡng và tour cùng chúng tôi.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Xem cảm nhận', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'testimonials', 'eyebrow' => 'Guest stories', 'title' => 'Real experiences', 'subtitle' => 'Over 4,000 guests stayed and toured with us.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'All reviews', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => null, 'imageAlt' => null),
        ),
        'review_platforms' => array(
            'vi' => array('key' => 'review_platforms', 'eyebrow' => null, 'title' => 'Hi Mũi Né được đánh giá cao trên', 'subtitle' => null, 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'review_platforms', 'eyebrow' => null, 'title' => 'Hi Mui Ne is highly rated on', 'subtitle' => null, 'body' => null, 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
        'team' => array(
            'vi' => array('key' => 'team', 'eyebrow' => 'Con người Hi Mũi Né', 'title' => 'Đội ngũ biển & cát', 'subtitle' => 'Từ jeep sunrise đến kite school Ham Tien.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Gặp đội ngũ', 'ctaUrl' => '/doi-ngu', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'team', 'eyebrow' => 'Hi Mui Ne team', 'title' => 'Our sea & sand experts', 'subtitle' => 'From sunrise jeeps to Ham Tien kite schools.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Meet the team', 'ctaUrl' => '/doi-ngu', 'image' => null, 'imageAlt' => null),
        ),
        'videos' => array(
            'vi' => array('key' => 'videos', 'eyebrow' => 'Trải nghiệm thật', 'title' => 'Mũi Né qua ống kính', 'subtitle' => 'Video khách hàng và đội ngũ.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'Xem video', 'ctaUrl' => '/video-trai-nghiem', 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'videos', 'eyebrow' => 'Real footage', 'title' => 'Mui Ne in motion', 'subtitle' => 'Guest and team films.', 'body' => null, 'metaLine' => null, 'ctaLabel' => 'All videos', 'ctaUrl' => '/video-trai-nghiem', 'image' => null, 'imageAlt' => null),
        ),
        'quick_inquiry' => array(
            'vi' => array('key' => 'quick_inquiry', 'eyebrow' => 'Tư vấn miễn phí', 'title' => 'Gửi lời nhắn', 'subtitle' => null,
                'body' => 'Chưa chọn resort hay jeep sunrise, kite hay tour ngày từ Sài Gòn? Phản hồi trong <strong class="font-semibold text-ink">24 giờ</strong>, miễn phí.', 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),




'en' => array('key' => 'quick_inquiry', 'eyebrow' => 'Free advice', 'title' => 'Send a message', 'subtitle' => null,
                'body' => 'Unsure about resort vs jeep sunrise, kite or SGN day trip? Reply within <strong class="font-semibold text-ink">24 hours</strong>, free.', 'metaLine' => null, 'ctaLabel' => null, 'ctaUrl' => null, 'image' => null, 'imageAlt' => null),
        ),
    ),

    'footer_columns' => array(
        array('title' => 'Hi Mũi Né', 'links' => array(
            array('label' => 'Về chúng tôi', 'route' => array('about')),
            array('label' => 'Cảm nhận khách hàng', 'route' => array('reviews')),
            array('label' => 'Đội ngũ', 'route' => array('team')),
            array('label' => 'Thư viện ảnh', 'route' => array('gallery')),
            array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
        )),
        array('title' => 'Tour & thuyền', 'links' => array(
            array('label' => 'Tour ngày đồi cát', 'route' => array('tours.show', array('zone' => 'doi-cat-do', 'slug' => 'tour-ngay-doi-cat-suoi-tien'))),
            array('label' => 'SGN cuối tuần 2 ngày 1 đêm', 'route' => array('tours.show', array('zone' => 'ket-hop-sai-gon', 'slug' => 'sai-gon-mui-ne-2n1d-weekend'))),
            array('label' => 'Câu mực đêm', 'route' => array('cruises.show', array('type' => 'thuyen-cau-muc-dem', 'slug' => 'thuyen-cau-muc-dem-mui-ne'))),
            array('label' => 'Kite-surf 3 ngày 2 đêm', 'route' => array('tours.show', array('zone' => 'ham-tien-bai-bien', 'slug' => 'mui-ne-3n2d-kite-surf-package'))),
            array('label' => 'Đà Lạt + Mũi Né', 'route' => array('tours.show', array('zone' => 'ket-hop-da-lat', 'slug' => 'da-lat-mui-ne-3n2d'))),
        )),
        array('title' => 'Điểm đến', 'links' => array(
            array('label' => 'Ham Tien bãi biển', 'route' => array('guide.zone', array('zone' => 'ham-tien-bai-bien'))),
            array('label' => 'Đồi cát đỏ', 'route' => array('guide.zone', array('zone' => 'doi-cat-do'))),
            array('label' => 'Suối Tiên', 'route' => array('tours.show', array('zone' => 'suoi-tien', 'slug' => 'suoi-tien-nua-ngay'))),
            array('label' => 'Kết hợp Sài Gòn', 'route' => array('tours.show', array('zone' => 'ket-hop-sai-gon', 'slug' => 'sai-gon-mui-ne-tour-ngay'))),
        )),
        array('title' => 'Cẩm nang (guide)', 'links' => array(
            array('label' => 'Từ Sài Gòn đi Mũi Né thế nào?', 'route' => array('guide.show', array('zone' => 'ket-hop-sai-gon', 'slug' => 'tu-sai-gon-di-mui-ne-the-nao'))),
            array('label' => 'Đồi cát đỏ hay trắng?', 'route' => array('guide.show', array('zone' => 'doi-cat-do', 'slug' => 'doi-cat-do-hay-doi-cat-trang'))),
            array('label' => 'Kite-surf hướng dẫn', 'route' => array('guide.show', array('zone' => 'ham-tien-bai-bien', 'slug' => 'kite-surf-mui-ne-huong-dan'))),
            array('label' => 'Mũi Né mùa nào đẹp?', 'route' => array('guide.show', array('zone' => 'ham-tien-bai-bien', 'slug' => 'mui-ne-mua-nao-dep-nhat'))),
        )),
    ),

    'footer_seo_links' => array(
        array('label' => 'Cẩm nang di chuyển Mũi Né', 'route' => array('guide.zone', array('zone' => 'ket-hop-sai-gon'))),
        array('label' => 'Thuyền biển Mũi Né', 'route' => array('cruises.index', array('type' => 'thuyen-cau-muc-dem'))),
        array('label' => 'Jeep đồi cát đỏ', 'route' => array('tours.show', array('zone' => 'doi-cat-do', 'slug' => 'jeep-doi-cat-do-sunrise'))),
        array('label' => 'Limousine Sài Gòn Mũi Né', 'route' => array('services.hub', array('cluster' => 'train'))),
        array('label' => 'Bay Cam Ranh đi Mũi Né', 'route' => array('services.hub', array('cluster' => 'flight'))),
        array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
    ),

    'tour_categories' => array(
        array(
            'zoneSlug' => 'doi-cat-do',
            'slug' => '1-ngay',
            'type' => 'duration',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'tour-ngay-doi-cat-suoi-tien',
                'jeep-doi-cat-do-sunrise',
                'jeep-doi-cat-trang-bau-trang',
                'tour-ngay-private-jeep-dunes',
            ),
            'name' => array(
                'vi' => 'Tour 1 ngày đồi cát',




'en' => '1-day dune tours',
            ),
            'subtitle' => array(
                'vi' => 'Jeep đỏ, trắng, Suối Tiên — nửa ngày hoặc cả ngày.',




'en' => 'Red, white dunes, Fairy Stream — half or full day.',
            ),
            'seo_body' => array(
                'vi' => 'Tour ngày đồi cát Mũi Né: jeep 150–300k, tour trọn ngày 400–900k — khác gói resort qua đêm.',




'en' => 'Mui Ne dune day tours: jeep VND 150–300k, full tours 400–900k — separate from resort stays.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => '2-3-ngay',
            'type' => 'duration',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 3,
            'packageSlugs' => array(
                'mui-ne-2n1d-resort-dunes',
                'mui-ne-3n2d-kite-surf-package',
                'gia-dinh-mui-ne-2n1d',
                'honeymoon-mui-ne-3n2d',
            ),
            'name' => array(
                'vi' => 'Tour 2 – 3 ngày resort',




'en' => '2–3 day resort tours',
            ),
            'subtitle' => array(
                'vi' => 'Ham Tiến + jeep + kite (tùy chọn).',




'en' => 'Ham Tien + jeep + optional kite.',
            ),
            'seo_body' => array(
                'vi' => 'Gói 2–3 ngày dải resort — phân biệt tour ngày từ Sài Gòn.',




'en' => '2–3 day resort strip packages — distinct from one-day SGN trips.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ket-hop-sai-gon',
            'slug' => '1-ngay',
            'type' => 'duration',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'sai-gon-mui-ne-tour-ngay',
            ),
            'name' => array(
                'vi' => 'Tour ngày từ Sài Gòn',




'en' => 'Day trip from Saigon',
            ),
            'subtitle' => array(
                'vi' => 'Limo 2 chiều + đồi cát — về tối.',




'en' => 'Return limo + dunes — back evening.',
            ),
            'seo_body' => array(
                'vi' => 'Tour ngày từ Sài Gòn ~1,1 triệu+ — limo riêng 250–350k/chiều.',




'en' => 'SGN day tour from ~1.1M — limo separately VND 250–350k each way.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ket-hop-sai-gon',
            'slug' => '2-3-ngay',
            'type' => 'duration',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 3,
            'packageSlugs' => array(
                'sai-gon-mui-ne-2n1d-weekend',
            ),
            'name' => array(
                'vi' => 'Cuối tuần 2 ngày 1 đêm từ Sài Gòn',




'en' => '2N1D weekend from SGN',
            ),
            'subtitle' => array(
                'vi' => 'Limo + resort + jeep — chuẩn cuối tuần.',




'en' => 'Limo + resort + jeep — classic weekend.',
            ),
            'seo_body' => array(
                'vi' => 'Combo cửa ngõ Sài Gòn — T6 sáng đi, CN tối về.',




'en' => 'Saigon gateway combo — Fri/Sat out, Sun evening back.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ket-hop-nha-trang',
            'slug' => '2-3-ngay',
            'type' => 'duration',
            'sort' => 0,
            'minDays' => 2,
            'maxDays' => 3,
            'packageSlugs' => array(
                'nha-trang-mui-ne-2n1d',
            ),
            'name' => array(
                'vi' => 'Nha Trang + Mũi Né',




'en' => 'Nha Trang + Mui Ne',
            ),
            'subtitle' => array(
                'vi' => 'Bay CXR — xe ~2h.',




'en' => 'Fly CXR — ~2h drive.',
            ),
            'seo_body' => array(
                'vi' => 'Phương án bay thay Sài Gòn — sân bay Cam Ranh + Mũi Né.',




'en' => 'SGN alternative — Cam Ranh airport + Mui Ne.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'tour-trong-ngay',
            'type' => 'theme',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'tour-ngay-doi-cat-suoi-tien',
                'jeep-doi-cat-do-sunrise',
                'jeep-doi-cat-trang-bau-trang',
                'suoi-tien-nua-ngay',
                'lang-chai-mui-ne-sang',
                'ham-tien-beach-kite-culture',
                'phan-thiet-po-sah-inu-city',
                'ke-ga-ta-cu-ngay',
                'sai-gon-mui-ne-tour-ngay',
                'food-tour-phan-thiet-dem',
                'tour-ngay-private-jeep-dunes',
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
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'tour-2-ngay-1-dem',
            'type' => 'theme',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 2,
            'packageSlugs' => array(
                'mui-ne-2n1d-resort-dunes',
                'sai-gon-mui-ne-2n1d-weekend',
                'nha-trang-mui-ne-2n1d',
                'gia-dinh-mui-ne-2n1d',
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
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'tour-3-ngay-2-dem',
            'type' => 'theme',
            'sort' => 2,
            'minDays' => 3,
            'maxDays' => 3,
            'packageSlugs' => array(
                'mui-ne-3n2d-kite-surf-package',
                'da-lat-mui-ne-3n2d',
                'honeymoon-mui-ne-3n2d',
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
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'tour-4-ngay-3-dem',
            'type' => 'theme',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 4,
            'packageSlugs' => array(),
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
            'zoneSlug' => 'ham-tien-bai-bien',
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
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'doi-cat',
            'type' => 'theme',
            'sort' => 10,
            'packageSlugs' => array(
                'tour-ngay-doi-cat-suoi-tien',
                'jeep-doi-cat-do-sunrise',
                'jeep-doi-cat-trang-bau-trang',
                'tour-ngay-private-jeep-dunes',
            ),
            'name' => array(
                'vi' => 'Tour đồi cát',




'en' => 'Sand dune tours',
            ),
            'subtitle' => array(
                'vi' => 'Đồi đỏ bình minh, Bàu Trắng, jeep riêng.',




'en' => 'Red sunrise, White Bau Trang, private jeep.',
            ),
            'seo_body' => array(
                'vi' => 'Trang chủ đề đồi cát — không nhầm với danh mục resort Ham Tiến.',




'en' => 'SEO zone doi-cat-do — not confused with Ham Tien resort hub.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'kite-surf',
            'type' => 'theme',
            'sort' => 11,
            'packageSlugs' => array(
                'mui-ne-3n2d-kite-surf-package',
            ),
            'name' => array(
                'vi' => 'Kite-surf & windsurf',




'en' => 'Kite-surf & windsurf',
            ),
            'subtitle' => array(
                'vi' => 'Mùa gió Nov–Mar — lesson + resort.',




'en' => 'Wind season Nov–Mar — lessons + resort.',
            ),
            'seo_body' => array(
                'vi' => 'Thiên đường kite Việt Nam — gói 3 ngày 2 đêm từ 4,2 triệu tham khảo.',




'en' => 'Vietnam kite capital — 3N2D packages from ~VND 4.2M.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'lang-chai',
            'type' => 'theme',
            'sort' => 12,
            'packageSlugs' => array(
                'lang-chai-mui-ne-sang',
                'food-tour-phan-thiet-dem',
            ),
            'name' => array(
                'vi' => 'Làng chài & hải sản',




'en' => 'Fishing village & seafood',
            ),
            'subtitle' => array(
                'vi' => 'Sáng sớm thuyền về, tour ẩm thực tối.',




'en' => 'Morning boats, evening food tours.',
            ),
            'seo_body' => array(
                'vi' => 'Văn hoá biển Phan Thiết — nước mắm và làng chài.',




'en' => 'Phan Thiet sea culture — fish sauce and fishing village.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'gia-dinh',
            'type' => 'theme',
            'sort' => 13,
            'packageSlugs' => array(
                'gia-dinh-mui-ne-2n1d',
                'mui-ne-2n1d-resort-dunes',
            ),
            'name' => array(
                'vi' => 'Gia đình & resort',




'en' => 'Family & resort',
            ),
            'subtitle' => array(
                'vi' => 'Jeep chiều, hồ bơi — không 5h sáng.',




'en' => 'Afternoon jeep, pool — no 5am rush.',
            ),
            'seo_body' => array(
                'vi' => 'Phân khúc gia đình cuối tuần từ Sài Gòn — resort 4 sao + jeep nhẹ.',




'en' => 'SGN weekend family segment — 4* resort + light jeep.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'combo',
            'type' => 'theme',
            'sort' => 14,
            'packageSlugs' => array(
                'sai-gon-mui-ne-2n1d-weekend',
                'sai-gon-mui-ne-tour-ngay',
            ),
            'name' => array(
                'vi' => 'Combo Sài Gòn',




'en' => 'Saigon combo',
            ),
            'subtitle' => array(
                'vi' => 'Limo + tour/resort một báo giá.',




'en' => 'Limo + tour/resort one quote.',
            ),
            'seo_body' => array(
                'vi' => 'Combo từ Sài Gòn — tách biệt với danh mục resort Ham Tiến.',




'en' => 'Zone ket-hop-sai-gon — anti-cannibalize vs Ham Tien resort only.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ham-tien-bai-bien',
            'slug' => 'honeymoon-bien',
            'type' => 'theme',
            'sort' => 15,
            'packageSlugs' => array(
                'honeymoon-mui-ne-3n2d',
            ),
            'name' => array(
                'vi' => 'Trang mat & cap doi',




'en' => 'Honeymoon & couples',
            ),
            'subtitle' => array(
                'vi' => 'Resort 4-5*, sunset cruise.',




'en' => '4-5* resort, sunset cruise.',
            ),
            'seo_body' => array(
                'vi' => 'Mini honeymoon bien nong gan SGN.',




'en' => 'Hot beach mini honeymoon near Saigon.',
            ),
            'faqs' => array(),
        ),
    ),

    'listing_faqs' => array(
        array('q' => 'Mũi Né có sân bay không?', 'a' => 'Không — bay Sài Gòn + limo 4–5h, hoặc Cam Ranh/Nha Trang + xe ~2h. Tuy Hòa cũng có thể nối xe.'),
        array('q' => 'Tour trọn gói đã gồm limousine Sài Gòn chưa?', 'a' => 'Tuỳ tour — combo từ Sài Gòn 2 ngày 1 đêm/tour ngày thường gồm xe; resort riêng có thể đặt thêm limo 250–350k/chiều.'),
        array('q' => 'Jeep đồi cát đỏ mấy giờ?', 'a' => 'Thường đón 05:00–05:30 để sunrise ~05:30–07:00 — về resort ~08:00. Có jeep chiều cho gia đình.'),
        array('q' => 'Kite-surf mùa nào?', 'a' => 'Nov–Mar gió ổn định nhất — giá resort/lesson cao hơn ~10% trong price_table_defaults.'),
        array('q' => 'Thuyền biển có phải du thuyền qua đêm không?', 'a' => 'Không — câu mực đêm, snorkel, SUP là trải nghiệm buổi tối/nửa ngày, không cabin ngủ trên biển.'),
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
        'doi-cat' => 'Tour đồi cát',
        'kite-surf' => 'Kite-surf & windsurf',
        'lang-chai' => 'Làng chài & hải sản',
        'gia-dinh' => 'Gia đình & resort',
        'combo' => 'Combo Sài Gòn',
        'honeymoon' => 'Trang mat & cap doi',
    ),
);

$__servicesSeed = [
    'service_clusters' => [
        ['code' => 'train', 'nav_label' => 'Di chuyển', 'label' => 'Limousine & xe Sài Gòn — Mũi Né', 'icon' => 'train', 'hub_key' => 'trains_hub', 'sort' => 1],
        ['code' => 'flight', 'nav_label' => 'Máy bay & đưa đón', 'label' => 'Cam Ranh (CXR) & kết nối bay', 'icon' => 'plane', 'hub_key' => 'flights_hub', 'sort' => 2],
        ['code' => 'stay', 'nav_label' => 'Lưu trú', 'label' => 'Resort Ham Tien & Phan Thiết', 'icon' => 'building', 'hub_key' => 'stays_hub', 'sort' => 3],
        ['code' => 'experience', 'nav_label' => 'Vui chơi', 'label' => 'Jeep, kite & tour ngày', 'icon' => 'sparkles', 'hub_key' => 'experiences_hub', 'sort' => 4],
        ['code' => 'other', 'nav_label' => 'Dịch vụ', 'label' => 'Hỗ trợ & tiện ích Mũi Né', 'icon' => 'briefcase', 'hub_key' => 'extras_hub', 'sort' => 5],
    ],

    'service_categories' => [
        ['cluster' => 'train', 'slug' => 'limousine-sgn-mui-ne', 'name' => 'Limousine Sài Gòn ↔ Mũi Né', 'sort' => 1, 'intro' => 'Xe 9–16 chỗ, 4–5 giờ, đón tận nơi.', 'seo_body' => 'Cửa ngõ chính từ SGN — :brand giá 250–350k/chiều. Không có sân bay Mũi Né.'],
        ['cluster' => 'train', 'slug' => 'xe-khach-sgn-mui-ne', 'name' => 'Xe khách Sài Gòn ↔ Mũi Né', 'sort' => 2, 'intro' => 'Open bus — giá rẻ hơn limo.', 'seo_body' => 'Tự taxi từ điểm xuống xe tới resort Ham Tien.'],
        ['cluster' => 'train', 'slug' => 'xe-da-lat-mui-ne', 'name' => 'Xe Đà Lạt ↔ Mũi Né', 'sort' => 3, 'intro' => 'Đèo 3–4 giờ — combo cao nguyên.', 'seo_body' => 'Nền tảng combo Đà Lạt + biển.'],
        ['cluster' => 'train', 'slug' => 'xe-nha-trang-mui-ne', 'name' => 'Xe Nha Trang ↔ Mũi Né', 'sort' => 4, 'intro' => '~2 giờ — sau bay Cam Ranh.', 'seo_body' => 'Transfer sân bay Cam Ranh + Mũi Né.'],
        ['cluster' => 'train', 'slug' => 'xe-rieng-charter', 'name' => 'Xe riêng & charter', 'sort' => 5, 'intro' => '4–16 chỗ theo ngày.', 'seo_body' => 'Đoàn MICE, gia đình — lịch linh hoạt.'],
        ['cluster' => 'train', 'slug' => 'xe-don-resort', 'name' => 'Xe đón resort Ham Tien', 'sort' => 6, 'intro' => 'Bến xe / sân bay → resort.', 'seo_body' => 'Đón tận sảnh Nguyễn Đình Chiểu.'],
        ['cluster' => 'flight', 'slug' => 'bay-cxr-nha-trang', 'name' => 'Bay Cam Ranh (CXR) + xe Mũi Né', 'sort' => 1, 'intro' => 'Sài Gòn/Hà Nội/DAD — nối ~2h xe.', 'seo_body' => 'Alternative bay SGN — gộp transfer.'],
        ['cluster' => 'flight', 'slug' => 'bay-tbb-tuy-hoa', 'name' => 'Bay Tuy Hòa (TBB) + xe', 'sort' => 2, 'intro' => 'Một số chuyến quốc tế/nội địa.', 'seo_body' => 'TBB cách Mũi Né ~3–4h — tư vấn riêng.'],
        ['cluster' => 'flight', 'slug' => 'dua-don-san-bay', 'name' => 'Transfer sân bay ↔ Mũi Né', 'sort' => 3, 'intro' => 'CXR, SGN door-to-resort.', 'seo_body' => ':brand canh chuyến bay.'],
        ['cluster' => 'flight', 'slug' => 'combo-bay-xe-mui-ne', 'name' => 'Combo bay + xe + resort', 'sort' => 4, 'intro' => 'Một báo giá bay + transfer + nghỉ.', 'seo_body' => 'Tiện khách bay vào CXR.'],
        ['cluster' => 'flight', 'slug' => 'bay-sgn-ket-noi', 'name' => 'Vé bay Sài Gòn (nối limo)', 'sort' => 5, 'intro' => 'Tư vấn chuyến bay + xe.', 'seo_body' => 'Liên hệ :brand ghép limousine.'],
        ['cluster' => 'stay', 'slug' => 'resort-ham-tien', 'name' => 'Resort Ham Tien 4–5*', 'sort' => 1, 'intro' => 'Dải Nguyễn Đình Chiểu.', 'seo_body' => 'Signature lưu trú Mũi Né — sát bãi & kite spot.'],
        ['cluster' => 'stay', 'slug' => 'resort-mui-ne', 'name' => 'Resort Mũi Né & bungalow', 'sort' => 2, 'intro' => '3–5 sao và boutique.', 'seo_body' => 'Đặt theo ngân sách — gộp jeep.'],
        ['cluster' => 'stay', 'slug' => 'khach-san-phan-thiet', 'name' => 'Khách sạn Phan Thiết city', 'sort' => 3, 'intro' => 'Gần tháp Po Sah Inu.', 'seo_body' => 'Giá thấp hơn strip — cách bãi 15 phút.'],
        ['cluster' => 'stay', 'slug' => 'bungalow-bien', 'name' => 'Bungalow & villa biển', 'sort' => 4, 'intro' => 'Gia đình & nhóm bạn.', 'seo_body' => 'Phù hợp 4–8 người.'],
        ['cluster' => 'stay', 'slug' => 'kite-surf-camp', 'name' => 'Kite camp & lesson stay', 'sort' => 5, 'intro' => 'Gộp lesson mùa gió.', 'seo_body' => 'Nov–Mar packages.'],
        ['cluster' => 'experience', 'slug' => 'jeep-doi-cat', 'name' => 'Jeep đồi cát đỏ & trắng', 'sort' => 1, 'intro' => 'Sunrise & Bàu Trắng 150–300k.', 'seo_body' => 'Không phải tour resort — jeep riêng hub.'],
        ['cluster' => 'experience', 'slug' => 'kite-surf-lessons', 'name' => 'Kite-surf & windsurf lesson', 'sort' => 2, 'intro' => 'Nov–Mar — HLV quốc tế.', 'seo_body' => 'Ham Tien schools — đặt qua :brand.'],
        ['cluster' => 'experience', 'slug' => 'tour-ngay-dunes', 'name' => 'Tour ngày đồi cát full', 'sort' => 3, 'intro' => '400–900k tham khảo.', 'seo_body' => 'Đỏ + Suối Tiên + làng chài.'],
        ['cluster' => 'experience', 'slug' => 'food-tour-hai-san', 'name' => 'Tour ẩm thực hải sản', 'sort' => 4, 'intro' => 'Phan Thiết & làng chài.', 'seo_body' => 'Buổi tối — khác quán resort.'],
        ['cluster' => 'experience', 'slug' => 'thuyen-cau-muc', 'name' => 'Thuyền câu mực & snorkel', 'sort' => 5, 'intro' => 'Buổi tối & nửa ngày.', 'seo_body' => 'Xem mục du thuyền — không qua đêm trên tàu.'],
        ['cluster' => 'experience', 'slug' => 'sup-kayak', 'name' => 'SUP & kayak Ham Tien', 'sort' => 6, 'intro' => 'Sáng sớm hoặc sunset.', 'seo_body' => 'Biết bơi khuyến nghị.'],
        ['cluster' => 'other', 'slug' => 'huong-dan-vien', 'name' => 'HDV riêng tiếng Anh', 'sort' => 1, 'intro' => 'Theo ngày trên tour/jeep.'],
        ['cluster' => 'other', 'slug' => 'dat-ban-hai-san', 'name' => 'Đặt bàn hải sản', 'sort' => 2, 'intro' => 'Giữ bàn cuối tuần.'],
        ['cluster' => 'other', 'slug' => 'hotline-24-7', 'name' => 'Hotline 24/7 & đổi lịch', 'sort' => 3, 'intro' => 'Biển động, đổi jeep/thuyền.'],
    ],

    'services' => [
        ['code' => 'train-limo-sgn-mn-oneway', 'cluster' => 'train', 'category_slug' => 'limousine-sgn-mui-ne', 'zone_slug' => 'ket-hop-sai-gon', 'title' => 'Limousine Sài Gòn → Mũi Né (một chiều)', 'slug' => 'limousine-sai-gon-mui-ne-mot-chieu', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 1560, 'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Đón tận nơi', 'location_label' => 'Sài Gòn → Mũi Né', 'summary' => '4–5 giờ, đón quận trung tâm — không tàu hoả.', 'highlights' => ['250–350k tham khảo', '9–16 chỗ', 'Weekend phổ biến'], 'inclusions' => ['Limousine một chiều'], 'exclusions' => ['Đón xa trung tâm'], 'notes' => ['Đặt trước T6–CN.'], 'attrs' => ['from' => 'SGN', 'to' => 'Mũi Né', 'duration_hours' => 4.5, 'vehicle_type' => 'limousine']],
        ['code' => 'train-limo-sgn-mn-round', 'cluster' => 'train', 'category_slug' => 'limousine-sgn-mui-ne', 'zone_slug' => 'ket-hop-sai-gon', 'title' => 'Limousine khứ hồi Sài Gòn ↔ Mũi Né', 'slug' => 'limousine-khu-hoi-sai-gon-mui-ne', 'price_from' => 520000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 980, 'is_featured' => true, 'location_label' => 'SGN ↔ Mũi Né', 'summary' => 'Cuối tuần 2 ngày 1 đêm — tiết kiệm hơn 2 chiều lẻ.', 'highlights' => ['Khứ hồi', 'Giữ chỗ chiều về'], 'inclusions' => ['2 chiều limousine'], 'exclusions' => [], 'notes' => [], 'attrs' => ['from' => 'SGN', 'to' => 'Mũi Né', 'vehicle_type' => 'limousine']],
        ['code' => 'train-bus-sgn-mn', 'cluster' => 'train', 'category_slug' => 'xe-khach-sgn-mui-ne', 'zone_slug' => 'ket-hop-sai-gon', 'title' => 'Xe khách Sài Gòn → Mũi Né', 'slug' => 'xe-khach-sai-gon-mui-ne', 'price_from' => 150000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 678, 'is_featured' => false, 'location_label' => 'Sài Gòn → Mũi Né', 'summary' => 'Giá rẻ — tự taxi ra resort.', 'highlights' => ['Open bus'], 'inclusions' => ['Vé một chiều'], 'exclusions' => ['Taxi resort'], 'notes' => [], 'attrs' => ['from' => 'SGN', 'to' => 'Mũi Né', 'duration_hours' => 5, 'vehicle_type' => 'xe khách']],
        ['code' => 'train-dl-mn', 'cluster' => 'train', 'category_slug' => 'xe-da-lat-mui-ne', 'zone_slug' => 'ket-hop-da-lat', 'title' => 'Xe Đà Lạt → Mũi Né', 'slug' => 'xe-da-lat-mui-ne', 'price_from' => 350000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Đà Lạt → Mũi Né', 'summary' => 'Đèo 3–4 giờ — combo cao nguyên.', 'highlights' => ['Combo ĐL'], 'inclusions' => ['Vé hoặc xe riêng'], 'exclusions' => [], 'notes' => ['Đường đèo — nên ban ngày.'], 'attrs' => ['from' => 'Đà Lạt', 'to' => 'Mũi Né', 'duration_hours' => 3.5]],
        ['code' => 'train-nt-mn', 'cluster' => 'train', 'category_slug' => 'xe-nha-trang-mui-ne', 'zone_slug' => 'ket-hop-nha-trang', 'title' => 'Xe Nha Trang → Mũi Né', 'slug' => 'xe-nha-trang-mui-ne', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 312, 'is_featured' => true, 'location_label' => 'Nha Trang → Mũi Né', 'summary' => '~2 giờ — sau bay Cam Ranh.', 'highlights' => ['Transfer CXR'], 'inclusions' => ['Xe 4–7 chỗ'], 'exclusions' => [], 'notes' => [], 'attrs' => ['from' => 'Nha Trang', 'to' => 'Mũi Né', 'duration_hours' => 2]],
        ['code' => 'train-charter-private', 'cluster' => 'train', 'category_slug' => 'xe-rieng-charter', 'zone_slug' => 'ket-hop-sai-gon', 'title' => 'Xe riêng Sài Gòn — Mũi Né (4–16 chỗ)', 'slug' => 'xe-rieng-sai-gon-mui-ne', 'price_from' => 2800000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 89, 'is_featured' => true, 'location_label' => 'Sài Gòn → Mũi Né', 'summary' => 'Lịch linh hoạt — đoàn resort.', 'highlights' => ['Private', 'Dừng chân tuỳ ý'], 'inclusions' => ['Xe + tài xế'], 'exclusions' => ['Cầu đường'], 'notes' => [], 'attrs' => ['vehicle_type' => '4–16 chỗ']],
        ['code' => 'train-resort-pickup', 'cluster' => 'train', 'category_slug' => 'xe-don-resort', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Xe đón bến xe / sân bay → resort', 'slug' => 'xe-don-resort-ham-tien', 'price_from' => 180000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 445, 'is_featured' => true, 'location_label' => '→ Ham Tien', 'summary' => 'Đón tận sảnh resort.', 'highlights' => ['Bảng tên'], 'inclusions' => ['Xe một chiều'], 'exclusions' => [], 'notes' => [], 'attrs' => ['service_type' => 'resort_transfer']],
        ['code' => 'flight-cxr-mn', 'cluster' => 'flight', 'category_slug' => 'bay-cxr-nha-trang', 'zone_slug' => 'ket-hop-nha-trang', 'title' => 'Transfer Cam Ranh (CXR) → Mũi Né', 'slug' => 'transfer-cam-ranh-mui-ne', 'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 267, 'is_featured' => true, 'location_label' => 'CXR → Mũi Né', 'summary' => '~2h — theo dõi flight.', 'highlights' => ['Door-to-resort'], 'inclusions' => ['Xe 4–7 chỗ'], 'exclusions' => ['Vé bay'], 'notes' => ['Gửi số hiệu chuyến bay.'], 'attrs' => ['from' => 'CXR', 'to' => 'Mũi Né', 'duration_hours' => 2]],
        ['code' => 'flight-tbb-connect', 'cluster' => 'flight', 'category_slug' => 'bay-tbb-tuy-hoa', 'zone_slug' => 'ket-hop-nha-trang', 'title' => 'Tư vấn Tuy Hòa (TBB) + xe Mũi Né', 'slug' => 'tu-van-tuy-hoa-mui-ne', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 45, 'is_featured' => false, 'location_label' => 'TBB → Mũi Né', 'summary' => 'TBB ~3–4h xe — tư vấn miễn phí.', 'highlights' => ['Tư vấn'], 'inclusions' => ['Tư vấn'], 'exclusions' => ['Vé bay', 'Xe'], 'notes' => [], 'attrs' => ['price_label' => 'Liên hệ']],
        ['code' => 'flight-airport-transfer', 'cluster' => 'flight', 'category_slug' => 'dua-don-san-bay', 'zone_slug' => 'ket-hop-sai-gon', 'title' => 'Đưa đón SGN sân bay ↔ limo bến', 'slug' => 'dua-don-sgn-san-bay', 'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 156, 'is_featured' => true, 'location_label' => 'SGN airport', 'summary' => 'Xe riêng đón sảnh bay.', 'highlights' => ['Theo dõi flight'], 'inclusions' => ['Xe 4 chỗ'], 'exclusions' => ['Limo Mũi Né'], 'notes' => [], 'attrs' => ['from' => 'SGN', 'duration_hours' => 0.5]],
        ['code' => 'flight-combo-cxr', 'cluster' => 'flight', 'category_slug' => 'combo-bay-xe-mui-ne', 'zone_slug' => 'ket-hop-nha-trang', 'title' => 'Combo bay Cam Ranh + transfer + resort 2 ngày 1 đêm', 'slug' => 'combo-bay-cxr-mui-ne-resort', 'price_from' => 4200000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 78, 'is_featured' => true, 'is_hot_deal' => true, 'location_label' => 'CXR → Mũi Né', 'summary' => 'Một báo giá bay + xe + resort.', 'highlights' => ['Một đầu mối'], 'inclusions' => ['Vé bay', 'Transfer', 'Resort 1 đêm'], 'exclusions' => ['Jeep'], 'notes' => ['Giá theo ngày bay.'], 'attrs' => ['price_label' => 'Từ']],
        ['code' => 'flight-sgn-advice', 'cluster' => 'flight', 'category_slug' => 'bay-sgn-ket-noi', 'zone_slug' => 'ket-hop-sai-gon', 'title' => 'Tư vấn vé bay Sài Gòn + limo Mũi Né', 'slug' => 'tu-van-ve-bay-sgn-mui-ne', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 67, 'is_featured' => true, 'location_label' => 'Sài Gòn → Mũi Né', 'summary' => 'Ghép vé bay + limousine.', 'highlights' => ['Miễn phí tư vấn'], 'inclusions' => ['Tư vấn'], 'exclusions' => ['Vé', 'Xe'], 'notes' => [], 'attrs' => ['price_label' => 'Liên hệ']],
        ['code' => 'stay-ham-tien-4', 'cluster' => 'stay', 'category_slug' => 'resort-ham-tien', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Resort Ham Tien 4* (1 đêm)', 'slug' => 'resort-ham-tien-4-sao-1-dem', 'price_from' => 950000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 534, 'is_featured' => true, 'location_label' => 'Ham Tien', 'summary' => 'Nguyễn Đình Chiểu — sát bãi.', 'highlights' => ['View biển', 'Hồ bơi'], 'inclusions' => ['Phòng 1 đêm'], 'exclusions' => ['Ăn sáng tuỳ gói'], 'notes' => [], 'attrs' => ['property_type' => 'resort']],
        ['code' => 'stay-ham-tien-5', 'cluster' => 'stay', 'category_slug' => 'resort-ham-tien', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Resort Ham Tien 5* boutique (1 đêm)', 'slug' => 'resort-ham-tien-5-sao-1-dem', 'price_from' => 1800000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 198, 'is_featured' => true, 'location_label' => 'Ham Tien', 'summary' => 'Honeymoon & kite season.', 'highlights' => ['Boutique', 'Spa'], 'inclusions' => ['Phòng 1 đêm'], 'exclusions' => [], 'notes' => [], 'attrs' => ['property_type' => 'resort']],
        ['code' => 'stay-phan-thiet-hotel', 'cluster' => 'stay', 'category_slug' => 'khach-san-phan-thiet', 'zone_slug' => 'phan-thiet-thanh-pho', 'title' => 'Khách sạn Phan Thiết (1 đêm)', 'slug' => 'khach-san-phan-thiet-1-dem', 'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 123, 'is_featured' => false, 'location_label' => 'Phan Thiết', 'summary' => 'Gần tháp Chăm — giá tốt.', 'highlights' => ['City center'], 'inclusions' => ['Phòng 1 đêm'], 'exclusions' => [], 'notes' => [], 'attrs' => ['property_type' => 'hotel']],
        ['code' => 'stay-bungalow', 'cluster' => 'stay', 'category_slug' => 'bungalow-bien', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Bungalow biển (1 đêm)', 'slug' => 'bungalow-bien-1-dem', 'price_from' => 750000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 89, 'is_featured' => false, 'location_label' => 'Ham Tien', 'summary' => 'Nhóm 4–6 người.', 'highlights' => ['Riêng tư'], 'inclusions' => ['Bungalow 1 đêm'], 'exclusions' => [], 'notes' => [], 'attrs' => ['property_type' => 'bungalow']],
        ['code' => 'stay-kite-camp', 'cluster' => 'stay', 'category_slug' => 'kite-surf-camp', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Kite camp + 2 lesson (2 đêm)', 'slug' => 'kite-camp-2-dem-lesson', 'price_from' => 3200000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 67, 'is_featured' => true, 'location_label' => 'Ham Tien', 'summary' => 'Nov–Mar wind season.', 'highlights' => ['2 lesson', 'Camp stay'], 'inclusions' => ['2 đêm', '2 buổi kite'], 'exclusions' => ['Limo'], 'notes' => ['Mùa gió Nov–Mar.'], 'attrs' => ['property_type' => 'kite_camp']],
        ['code' => 'exp-jeep-red', 'cluster' => 'experience', 'category_slug' => 'jeep-doi-cat', 'zone_slug' => 'doi-cat-do', 'title' => 'Jeep đồi cát đỏ sunrise — vé lẻ', 'slug' => 'jeep-doi-cat-do-sunrise-ve-le', 'price_from' => 180000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 1240, 'is_featured' => true, 'location_label' => 'Đồi cát đỏ', 'summary' => '05:00 đón — ghép nhóm.', 'highlights' => ['Sunrise', 'Trượt cát'], 'inclusions' => ['Jeep', 'HDV'], 'exclusions' => ['Ăn sáng'], 'notes' => [], 'attrs' => ['duration_hours' => 3, 'activity' => 'jeep']],
        ['code' => 'exp-jeep-white', 'cluster' => 'experience', 'category_slug' => 'jeep-doi-cat', 'zone_slug' => 'doi-cat-trang-bau-trang', 'title' => 'Jeep đồi cát trắng Bàu Trắng', 'slug' => 'jeep-doi-cat-trang-ve-le', 'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 567, 'is_featured' => true, 'location_label' => 'Bàu Trắng', 'summary' => 'Nửa ngày — ~30km.', 'highlights' => ['Trượt cát', 'Hồ Bàu Trắng'], 'inclusions' => ['Jeep', 'HDV'], 'exclusions' => [], 'notes' => [], 'attrs' => ['duration_hours' => 5]],
        ['code' => 'exp-kite-lesson', 'cluster' => 'experience', 'category_slug' => 'kite-surf-lessons', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Kite-surf lesson (1 buổi)', 'slug' => 'kite-surf-lesson-1-buoi', 'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 234, 'is_featured' => true, 'location_label' => 'Ham Tien', 'summary' => '2–3h — HLV & thiết bị.', 'highlights' => ['HLV quốc tế', 'Thiết bị'], 'inclusions' => ['Lesson', 'Gear'], 'exclusions' => [], 'notes' => ['Nov–Mar best.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'kite']],
        ['code' => 'exp-tour-day-full', 'cluster' => 'experience', 'category_slug' => 'tour-ngay-dunes', 'zone_slug' => 'doi-cat-do', 'title' => 'Tour ngày đồi cát full — vé lẻ', 'slug' => 'tour-ngay-doi-cat-ve-le', 'price_from' => 650000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 890, 'is_featured' => true, 'location_label' => 'Mũi Né', 'summary' => 'Đỏ + Suối Tiên + làng chài.', 'highlights' => ['Full day', 'Trưa'], 'inclusions' => ['Jeep', 'Suối Tiên', 'Trưa'], 'exclusions' => ['Limo SGN'], 'notes' => [], 'attrs' => ['duration_hours' => 8]],
        ['code' => 'exp-food-tour', 'cluster' => 'experience', 'category_slug' => 'food-tour-hai-san', 'zone_slug' => 'phan-thiet-thanh-pho', 'title' => 'Tour ẩm thực Phan Thiết buổi tối', 'slug' => 'food-tour-phan-thiet-ve-le', 'price_from' => 480000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 145, 'is_featured' => false, 'location_label' => 'Phan Thiết', 'summary' => 'HDV am thực — 3h.', 'highlights' => ['5–6 món'], 'inclusions' => ['HDV', 'Tasting'], 'exclusions' => ['Rượu'], 'notes' => [], 'attrs' => ['duration_hours' => 3]],
        ['code' => 'exp-squid-boat', 'cluster' => 'experience', 'category_slug' => 'thuyen-cau-muc', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Thuyền câu mực đêm — vé lẻ', 'slug' => 'thuyen-cau-muc-dem-ve-le', 'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 312, 'is_featured' => true, 'location_label' => 'Biển Mũi Né', 'summary' => '19:00–22:00.', 'highlights' => ['Câu mực', 'Áo phao'], 'inclusions' => ['Thuyền', 'Dụng cụ'], 'exclusions' => [], 'notes' => ['Theo thời tiết.'], 'attrs' => ['duration_hours' => 3]],
        ['code' => 'exp-sup-morning', 'cluster' => 'experience', 'category_slug' => 'sup-kayak', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'SUP sáng Ham Tien', 'slug' => 'sup-sang-ham-tien', 'price_from' => 380000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 98, 'is_featured' => true, 'location_label' => 'Ham Tien', 'summary' => '07:00–09:30.', 'highlights' => ['HLV', 'Snack'], 'inclusions' => ['SUP', 'HLV'], 'exclusions' => [], 'notes' => [], 'attrs' => ['duration_hours' => 2.5]],
        ['code' => 'other-guide-en', 'cluster' => 'other', 'category_slug' => 'huong-dan-vien', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'HDV tiếng Anh riêng (8h)', 'slug' => 'hdv-tieng-anh-rieng-mui-ne', 'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 78, 'is_featured' => true, 'location_label' => 'Mũi Né', 'summary' => 'Theo ngày jeep/tour.', 'highlights' => ['FIT intl'], 'inclusions' => ['HDV 8h'], 'exclusions' => ['Vé'], 'notes' => [], 'attrs' => ['service_type' => 'guide']],
        ['code' => 'other-seafood-booking', 'cluster' => 'other', 'category_slug' => 'dat-ban-hai-san', 'zone_slug' => 'lang-chai-mui-ne', 'title' => 'Đặt bàn hải sản Ham Tien/Phan Thiết', 'slug' => 'dat-ban-hai-san-mui-ne', 'price_from' => 0, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 56, 'is_featured' => false, 'location_label' => 'Mũi Né', 'summary' => 'Miễn phí đặt bàn.', 'highlights' => ['Cuối tuần'], 'inclusions' => ['Đặt bàn'], 'exclusions' => ['Tiền ăn'], 'notes' => [], 'attrs' => ['price_label' => 'Miễn phí']],
        ['code' => 'other-hotline', 'cluster' => 'other', 'category_slug' => 'hotline-24-7', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Hotline 24/7 — đổi lịch jeep/thuyền', 'slug' => 'hotline-doi-lich-mui-ne', 'price_from' => 0, 'currency' => 'VND', 'rating' => 5.0, 'review_count' => 34, 'is_featured' => true, 'location_label' => 'Toàn Mũi Né', 'summary' => 'Miễn phí khách :brand.', 'highlights' => ['VI/EN/RU'], 'inclusions' => ['Hotline'], 'exclusions' => [], 'notes' => [], 'attrs' => ['price_label' => 'Liên hệ']],
        ['code' => 'stay-mui-ne-3star', 'cluster' => 'stay', 'category_slug' => 'resort-mui-ne', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Resort Mũi Né 3* (1 đêm)', 'slug' => 'resort-mui-ne-3-sao-1-dem', 'price_from' => 650000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 267, 'is_featured' => false, 'location_label' => 'Mũi Né', 'summary' => 'Budget beach — gần bãi.', 'highlights' => ['Giá tốt'], 'inclusions' => ['Phòng 1 đêm'], 'exclusions' => [], 'notes' => [], 'attrs' => ['property_type' => 'resort']],
        ['code' => 'exp-windsurf', 'cluster' => 'experience', 'category_slug' => 'kite-surf-lessons', 'zone_slug' => 'ham-tien-bai-bien', 'title' => 'Windsurf lesson (1 buổi)', 'slug' => 'windsurf-lesson-1-buoi', 'price_from' => 750000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 89, 'is_featured' => false, 'location_label' => 'Ham Tien', 'summary' => '2h — mùa gió.', 'highlights' => ['HLV', 'Board'], 'inclusions' => ['Lesson', 'Gear'], 'exclusions' => [], 'notes' => [], 'attrs' => ['activity' => 'windsurf']],
    ],

    'service_listing_faqs' => [
        ['q' => 'Limousine có phải tàu hoả không?', 'a' => 'Không — xe 9–16 chỗ đường bộ Sài Gòn — Mũi Né (~4–5 giờ). Mũi Né không có ga tàu hoả.'],
        ['q' => 'Gộp vé CXR + resort một đơn được không?', 'a' => 'Có — :brand báo giá combo bay Cam Ranh, transfer ~2h và resort một lần.'],
        ['q' => 'Jeep đồi cát trên dịch vụ có gì khác tour?', 'a' => 'Vé lẻ jeep — ghép nhóm. Tour ngày full gộp Suối Tiên, trưa và làng chài.'],
        ['q' => 'Mùa gió kite giá có tăng không?', 'a' => 'Nov–Mar multiplier ~1.1 trong price_table — lesson và resort cao hơn mùa thấp điểm.'],
        ['q' => 'Giá limo 250–350k có cố định không?', 'a' => 'Mức tham khảo — chốt theo nhà xe, ngày đi và điểm đón. T6–CN và lễ có thể cao hơn.'],
    ],
];

$__companySeed = [
    'name' => 'Hi Mũi Né',
    'legal_name' => 'Công ty TNHH Du lịch Hi Mũi Né',
    'tagline' => 'Biển xanh, đồi cát và thiên đường kite-surf Nam Trung Bộ',
    'slogan' => '"Khám phá Mũi Né như người am hiểu gió và cát"',
    'license_number' => '0055/2024/TCDL-GPLHQT',
    'contact' => [
        'email' => 'hello@himuine.dev',
        'phone' => '+84 252 384 8888',
        'whatsapp' => '+84 912 888 777',
        'zalo' => '+84 252 384 8888',
        'hotline_label' => 'Hotline',
    ],
    'address' => [
        'street' => 'Tầng 1, 120 Nguyễn Đình Chiểu, phường Hàm Tiến',
        'locality' => 'Phan Thiết, Bình Thuận',
        'region' => 'Bình Thuận',
        'postal' => '800000',
        'country' => 'VN',
        'geo' => [
            'latitude' => 10.9289,
            'longitude' => 108.1021,
        ],
    ],
    'social' => [
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/himuine'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'play', 'url' => 'https://www.youtube.com/@himuine'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'photo', 'url' => 'https://www.instagram.com/himuine'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'share', 'url' => 'https://www.tiktok.com/@himuine'],
    ],
    'schema' => [
        'available_language' => ['Vietnamese', 'English', 'Russian'],
        'contact_type' => 'customer service',
        'logo' => null,
        'geo' => [
            'latitude' => 10.9289,
            'longitude' => 108.1021,
        ],
    ],
    'footer' => [
        'copyright' => '© :year Hi Mũi Né. Giấy phép kinh doanh dịch vụ lữ hành số :license.',
        'show_dmca_badge' => true,
    ],
];

return array_merge(
    $__himuineSeed,
    $__servicesSeed,
    ['company' => $__companySeed],
    ['customize_form' => [
        'destinations_label' => [
            'vi' => 'Bạn muốn khám phá khu vực nào tại Mũi Né?',




'en' => 'Which areas of Mui Ne would you like to explore?',
        ],
        'accommodation_label' => [
            'vi' => 'Bạn muốn trải nghiệm lưu trú nào?',




'en' => 'What kind of stay do you prefer?',
        ],
        'budget_note' => [
            'vi' => 'Ngân sách dự kiến (chưa gồm limousine Sài Gòn / bay Cam Ranh)',




'en' => 'Estimated budget (excluding Saigon limousine / Cam Ranh flights)',
        ],
        'accommodation' => [
            'vi' => [
                'Resort Ham Tien 4–5 sao (Nguyễn Đình Chiểu)',
                'Resort 3 sao / bungalow gần bãi',
                'Khách sạn Phan Thiết city (gần tháp Chăm)',
                'Kite camp + lesson mùa gió',
                'Nhờ tư vấn giúp tôi',
            ],




'en' => [
                '4–5 star Ham Tien resort (Nguyen Dinh Chieu)',
                '3 star resort / beach bungalow',
                'Phan Thiet city hotel (near Cham towers)',
                'Kite camp + lessons in wind season',
                'Please advise me',
            ],
        ],
    ]],
    ['nav' => [
        'about_group' => ['vi' => 'Về Hi Mũi Né',



'en' => 'About Hi Mui Ne'],
        'tours' => ['label' => ['vi' => 'Tour',



'en' => 'Tours']],
        'cruise' => [
            'label' => ['vi' => 'Thuyền biển',



'en' => 'Sea boats'],
            'all_label' => ['vi' => 'Tất cả thuyền biển',



'en' => 'All sea boats'],
            'all_meta' => ['vi' => 'Câu mực đêm, snorkel & SUP/kayak',



'en' => 'Squid fishing, snorkel & SUP/kayak'],
            'search_hint' => ['vi' => 'Tour đồi cát, resort, kite, cẩm nang…',



'en' => 'Dune tours, resorts, kite, guides…'],
            'search_placeholder' => ['vi' => 'Tìm tour, thuyền, bài viết…',



'en' => 'Search tours, boats, articles…'],
            'hub_title' => ['vi' => 'Thuyền biển',



'en' => 'Sea boats'],
            'hub_subtitle' => ['vi' => 'Câu mực đêm, ngắm san hô, SUP & kayak — không du thuyền qua đêm.',



'en' => 'Squid fishing, snorkel, SUP & kayak — not overnight cruises.'],
        ],
    ]],
    ['listing_hubs' => [
        'tours_hub' => [
            'vi' => ['seo_body' => 'Tour ngày đồi cát, cuối tuần Sài Gòn, Đà Lạt/Nha Trang combo và gói gia đình — :brand thiết kế riêng, tách mục thuyền biển.'],




'en' => ['seo_body' => 'Dune day tours, SGN weekends, Dalat/Nha Trang combos and family packages — separate from the sea boats hub.'],
        ],
        'cruises_hub' => [
            'vi' => ['seo_body' => 'Thuyền biển Mũi Né — câu mực đêm, snorkel, SUP/kayak từ :brand. Trải nghiệm buổi tối/nửa ngày, không cabin ngủ trên biển.'],




'en' => ['seo_body' => 'Mui Ne sea boats — squid fishing, snorkel, SUP/kayak from :brand. Evening/half-day experiences, no overnight cabins.'],
        ],
        'trains_hub' => [
            'vi' => ['seo_body' => 'Limousine & xe Sài Gòn — Mũi Né qua :brand: 250–350k/chiều, đón tận nơi, cuối tuần 2 ngày 1 đêm.'],




'en' => ['seo_body' => 'Limousine & bus Saigon — Mui Ne via :brand: VND 250–350k each way, hotel pickup, 2N1D weekends.'],
        ],
        'flights_hub' => [
            'vi' => ['seo_body' => 'Bay Cam Ranh (CXR) ~2h và Tuy Hòa (TBB) — :brand gộp transfer + resort một báo giá. Mũi Né không có sân bay.'],




'en' => ['seo_body' => 'Cam Ranh (CXR) ~2h and Tuy Hoa (TBB) connections — :brand bundles transfer + resort. No airport at Mui Ne.'],
        ],
        'stays_hub' => [
            'vi' => ['seo_body' => 'Resort Ham Tien, bungalow và kite camp — :brand đặt hộ theo ngân sách và mùa gió.'],




'en' => ['seo_body' => 'Ham Tien resorts, bungalows and kite camps booked via :brand by budget and wind season.'],
        ],
        'experiences_hub' => [
            'vi' => ['seo_body' => 'Jeep đồi cát, kite lesson, tour ẩm thực, Suối Tiên — trải nghiệm lẻ hoặc gộp resort qua :brand.'],




'en' => ['seo_body' => 'Dune jeeps, kite lessons, food tours, Fairy Stream — à la carte or bundled via :brand.'],
        ],
        'extras_hub' => [
            'vi' => ['seo_body' => 'HDV riêng, đặt bàn hải sản, hotline đổi lịch jeep/thuyền — hỗ trợ :brand tại Mũi Né.'],




'en' => ['seo_body' => 'Private guides, seafood bookings, jeep/boat rebooking hotline — :brand support in Mui Ne.'],
        ],
    ]],
);

