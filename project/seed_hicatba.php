<?php

/**
 * ============================================================================
 * DỮ LIỆU Hi Cát Bà — profile `hicatba` (project:seed / migrate --seed)
 * ============================================================================
 *
 * Một file seed / một dự án: chứa đủ tours, cruises, company, catalogue dịch vụ.
 * Đây là trang thông tin du lịch + dịch vụ + kết nối TOÀN DIỆN của đảo Cát Bà
 * (Hải Phòng) — khác với ViTravel (đa quốc gia), Cát Bà là MỘT điểm đến duy nhất,
 * nên khái niệm "countries" trong template ViTravel được thay bằng "zones"
 * (các khu vực/điểm trong đảo: trung tâm, vịnh Lan Hạ, vườn quốc gia, Đảo Khỉ,
 * làng Việt Hải, làng chài Cái Bèo, và tuyến kết hợp Hạ Long).
 * Tương tự, cụm dịch vụ "train" (tàu hoả liên tỉnh) được đổi thành "ferry"
 * (tàu cao tốc / phà / xe khách ra đảo) — vì Cát Bà không có đường sắt.
 *
 * Schema: project/README.md | Loader: App\Support\ProjectSeed::useProfile('hicatba')
 *
 * @return array<string, mixed>
 */

$__hicatbaSeed = array(
    'meta' => array(
        'schema' => 1,
        'brand' => 'Hi Cát Bà',
        'tagline' => 'Đảo ngọc giữa vịnh di sản',
        'admin' => array(
            'email' => 'admin@hicatba.dev',
            'name' => 'Admin Hi Cát Bà',
            'password' => '111111',
        ),
        'primary_domain' => 'hicatba.dev',
        'domains' => array('hicatba.dev', 'www.hicatba.dev', 'hicatba.com', 'www.hicatba.com'),
        'exported_at' => '2026-08-28T00:00:00+00:00',
    ),

    'price_guest_types' => array(
        array(
            'code' => 'adult',
            'sort' => 10,
            'age_min' => 12,
            'age_max' => 59,
            'name' => array('vi' => 'Người lớn',



'en' => 'Adult'),
        ),
        array(
            'code' => 'child',
            'sort' => 20,
            'age_min' => 2,
            'age_max' => 11,
            'name' => array('vi' => 'Trẻ em',



'en' => 'Child'),
        ),
        array(
            'code' => 'senior',
            'sort' => 30,
            'age_min' => 60,
            'age_max' => null,
            'name' => array('vi' => 'Cao tuổi (60+)',



'en' => 'Senior (60+)'),
        ),
    ),

    'price_table_defaults' => array(
        'unit' => 'per_person',
        'notes' => 'Giá tham khảo theo người. Trẻ em và cao tuổi giảm theo bảng. Liên hệ để chốt báo giá chính xác.',
        'guest_multipliers' => array(
            'adult' => 1,
            'child' => 0.7,
            'senior' => 0.85,
        ),
        'cluster_units' => array(
            'stay' => 'per_room',
        ),
        'periods' => array(
            array(
                'kind' => 'year',
                'label' => 'Giá năm {year}',
                'is_promo' => false,
                'priority' => 0,
            ),
            array(
                'kind' => 'range',
                'label' => 'Ưu đãi hè {year}',
                'starts_on' => '{year}-06-01',
                'ends_on' => '{year}-08-31',
                'is_promo' => true,
                'priority' => 10,
                'amount_multiplier' => 0.9,
            ),
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
        'how-to-get-there' => array('vi' => 'Di chuyển ra đảo thế nào?',



'en' => 'How to get there?'),
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
        'vinh-lan-ha' => array(
            'vi' => 'Tour vịnh Lan Hạ',




'en' => 'Lan Ha Bay tours',
        ),
        'trekking-vqg' => array(
            'vi' => 'Tour trekking vườn quốc gia',




'en' => 'National park trekking tours',
        ),
        'dao-khi-tam-bien' => array(
            'vi' => 'Tour Đảo Khỉ & tắm biển',




'en' => 'Monkey Island & beach tours',
        ),
        'lang-viet-hai' => array(
            'vi' => 'Tour làng Việt Hải',




'en' => 'Viet Hai village tours',
        ),
        'lang-chai-cai-beo' => array(
            'vi' => 'Tour làng chài Cái Bèo',




'en' => 'Cai Beo fishing village tours',
        ),
    ),

    'review_platforms' => array(
        array('code' => 'tripadvisor', 'name' => 'Tripadvisor', 'rating' => 4.8, 'review_count' => 260, 'sort' => 0,
            'quote' => 'Xếp hạng 5/5 từ hơn 700 đánh giá — Giải thưởng Travelers\' Choice 2 năm liên tiếp.',
            'link_label' => 'Đọc đánh giá trên Tripadvisor', 'url' => 'https://www.tripadvisor.com'),
        array('code' => 'google', 'name' => 'Google', 'rating' => 4.7, 'review_count' => 340, 'sort' => 1,
            'quote' => '4.8/5 trên Google Maps với hơn 500 nhận xét về các tour vịnh Lan Hạ và trekking Việt Hải.',
            'link_label' => 'Xem đánh giá trên Google', 'url' => 'https://www.google.com/maps'),
        array('code' => 'trustpilot', 'name' => 'Trustpilot', 'rating' => 4.6, 'review_count' => 74, 'sort' => 2,
            'quote' => 'Điểm "Xuất sắc" trên Trustpilot — khách quốc tế đánh giá cao đội ngũ hướng dẫn địa phương.',
            'link_label' => 'Đọc đánh giá trên Trustpilot', 'url' => 'https://www.trustpilot.com'),
    ),

    'cruise_types' => array(
        array('slug' => 'du-thuyen-lan-ha', 'name' => 'Du thuyền Lan Hạ', 'count' => 2, 'image' => NULL, 'imageHero' => NULL, 'imageSrcset' => NULL, 'sort' => 10),
        array('slug' => 'tour-ngay-vinh-cat-ba', 'name' => 'Tour ngày vịnh Cát Bà', 'count' => 1, 'image' => NULL, 'imageHero' => NULL, 'imageSrcset' => NULL, 'sort' => 20),
        array('slug' => 'thuyen-viet-hai-ao-ech', 'name' => 'Thuyền Việt Hải — Ao Ếch', 'count' => 1, 'image' => NULL, 'imageHero' => NULL, 'imageSrcset' => NULL, 'sort' => 30),
    ),

    'home_slides' => array(
        array(
            'sort' => 0, 'text_align' => 'center', 'link_url' => '/tours',
            'vi' => array('title' => 'Cát Bà', 'title_accent' => 'khu dự trữ sinh quyển thế giới',
                'description' => 'Quần đảo hội tụ rừng nhiệt đới, vịnh đá vôi và biển xanh trong một không gian — vẻ đẹp có một không hai, nơi núi rừng chạm sóng vịnh.',
                'button_label' => 'Khám phá Cát Bà', 'image_alt' => 'Quần đảo Cát Bà — khu dự trữ sinh quyển'),




'en' => array('title' => 'Cat Ba', 'title_accent' => 'a UNESCO biosphere reserve',
                'description' => 'An archipelago where tropical forest, limestone karst and open sea meet — a one-of-a-kind landscape where jungle hills roll straight into the bay.',
                'button_label' => 'Discover Cat Ba', 'image_alt' => 'Cat Ba Archipelago biosphere reserve'),
        ),
        array(
            'sort' => 1, 'text_align' => 'center', 'link_url' => '/diem-den/vinh-lan-ha',
            'vi' => array('title' => 'Vịnh Lan Hạ', 'title_accent' => 'kayak, tàu nhỏ & đêm trên vịnh',
                'description' => 'Hơn 400 đảo đá vôi, hang Luồn và bãi tắm hoang sơ — tour ngày từ bến Bèo hoặc ngủ đêm trên du thuyền giữa vịnh.',
                'button_label' => 'Khám phá vịnh Lan Hạ', 'image_alt' => 'Kayak và du thuyền trên vịnh Lan Hạ'),




'en' => array('title' => 'Lan Ha Bay', 'title_accent' => 'kayak, small boats & nights on the water',
                'description' => 'Over 400 limestone islands, Luon Cave and hidden beaches — day trips from Ben Beo pier or an overnight cruise in the bay.',
                'button_label' => 'Explore Lan Ha Bay', 'image_alt' => 'Kayak and cruise on Lan Ha Bay'),
        ),
        array(
            'sort' => 2, 'text_align' => 'center', 'link_url' => '/diem-den/vuon-quoc-gia',
            'vi' => array('title' => 'Rừng & làng Việt Hải', 'title_accent' => 'trekking & homestay giữa vườn quốc gia',
                'description' => 'Đi bộ xuyên rừng nguyên sinh, ghé Ao Ếch và ăn cùng người dân — làng không xe máy, yên tĩnh sau giờ tàu về.',
                'button_label' => 'Xem tour trekking', 'image_alt' => 'Trekking vườn quốc gia Cát Bà tới làng Việt Hải'),




'en' => array('title' => 'Forest & Viet Hai village', 'title_accent' => 'trekking & homestay in the national park',
                'description' => 'Hike through primary forest, visit Ao Ech lake and share meals with locals — a motorbike-free village, quiet after the day boats leave.',
                'button_label' => 'View trekking tours', 'image_alt' => 'Trekking Cat Ba National Park to Viet Hai village'),
        ),
    ),

    'zones' => array(
        array('slug' => 'trung-tam-cat-ba', 'name' => 'Trung tâm đảo Cát Bà', 'size' => 'large', 'tourCount' => 5, 'tagline' => 'Bến Bèo, bãi Cát Cò và nhịp sống phố đảo'),
        array('slug' => 'vinh-lan-ha', 'name' => 'Vịnh Lan Hạ', 'size' => 'large', 'tourCount' => 5, 'tagline' => 'Hơn 400 đảo đá vôi, vịnh xanh vắng dấu chân người'),
        array('slug' => 'vuon-quoc-gia', 'name' => 'Vườn quốc gia Cát Bà', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Rừng nguyên sinh, hang động và voọc Cát Bà quý hiếm'),
        array('slug' => 'dao-khi', 'name' => 'Đảo Khỉ (Cát Dứa)', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Bãi cát trắng và đàn khỉ hoang dã'),
        array('slug' => 'lang-viet-hai', 'name' => 'Làng Việt Hải', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Bản làng giữa vườn quốc gia, không tiếng xe máy'),
        array('slug' => 'cai-beo', 'name' => 'Làng chài Cái Bèo', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Làng chài cổ trên vịnh, gắn với di chỉ khảo cổ hơn 6.000 năm'),
        array('slug' => 'ket-hop-ha-long', 'name' => 'Kết hợp Hạ Long', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Nối liền hai vịnh di sản trong một hành trình'),
    ),

    'zone_translations' => array(
        'trung-tam-cat-ba' => array('vi' => 'Trung tâm đảo Cát Bà',



'en' => 'Cat Ba Town Centre',
            'tagline' => array('vi' => 'Bến Bèo, bãi Cát Cò và nhịp sống phố đảo',



'en' => 'Ben Beo pier, Cat Co beaches and island town life')),
        'vinh-lan-ha' => array('vi' => 'Vịnh Lan Hạ',



'en' => 'Lan Ha Bay',
            'tagline' => array('vi' => 'Hơn 400 đảo đá vôi, vịnh xanh vắng dấu chân người',



'en' => 'Over 400 limestone islands, an untouched blue bay')),
        'vuon-quoc-gia' => array('vi' => 'Vườn quốc gia Cát Bà',



'en' => 'Cat Ba National Park',
            'tagline' => array('vi' => 'Rừng nguyên sinh, hang động và voọc Cát Bà quý hiếm',



'en' => 'Primary forest, caves and the rare Cat Ba langur')),
        'dao-khi' => array('vi' => 'Đảo Khỉ (Cát Dứa)',



'en' => 'Monkey Island (Cat Dua)',
            'tagline' => array('vi' => 'Bãi cát trắng và đàn khỉ hoang dã',



'en' => 'White sand beach and wild monkeys')),
        'lang-viet-hai' => array('vi' => 'Làng Việt Hải',



'en' => 'Viet Hai Village',
            'tagline' => array('vi' => 'Bản làng giữa vườn quốc gia, không tiếng xe máy',



'en' => 'A village inside the national park, free of motorbikes')),
        'cai-beo' => array('vi' => 'Làng chài Cái Bèo',



'en' => 'Cai Beo Fishing Village',
            'tagline' => array('vi' => 'Làng chài cổ trên vịnh, gắn với di chỉ khảo cổ hơn 6.000 năm',



'en' => 'An ancient floating village linked to a 6,000-year-old archaeological site')),
        'ket-hop-ha-long' => array('vi' => 'Kết hợp Hạ Long',



'en' => 'Combined with Halong Bay',
            'tagline' => array('vi' => 'Nối liền hai vịnh di sản trong một hành trình',



'en' => 'Two heritage bays in a single journey')),
    ),

    'tours' => array(
        array(
            'slug' => 'cat-ba-3-ngay-tong-quan-dao',
            'title' => 'Cát Bà 3 ngày 2 đêm — Tổng quan đảo ngọc',
            'zoneSlug' => 'trung-tam-cat-ba',
            'zone' => 'Trung tâm đảo Cát Bà',
            'tourCode' => 'CB3D-01',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 5,
            'reviewCount' => 142,
            'badge' => 'Bán chạy nhất',
            'featured' => true,
            'styles' => array(
                '3n2d',
            ),
            'quote' => array(
                'text' => 'Ba ngày vừa đủ để thấy hết vẻ đẹp của Cát Bà — từ vịnh, rừng đến bãi biển.',
                'author' => 'Anh Trọng Nghĩa',
            ),
            'places' => array(
                'Bến Bèo',
                'Vịnh Lan Hạ',
                'Đảo Khỉ',
                'Vườn quốc gia Cát Bà',
                'Bãi Cát Cò',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Lịch trình cân bằng cho lần đầu đến Cát Bà: một đêm ngủ vịnh, một ngày trekking rừng, một buổi tắm biển thư thái.',
            'highlights' => array(
                'Ngủ đêm trên du thuyền giữa vịnh Lan Hạ',
                'Chèo kayak qua hang và bãi tắm hoang sơ',
                'Trekking nửa ngày trong vườn quốc gia, thăm hang Trung Trang',
                'Tắm biển và lặn ngắm san hô tại Đảo Khỉ',
                'Thưởng thức hải sản tại chợ Cát Bà về đêm',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cát Bà — Lên du thuyền vịnh Lan Hạ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'cruise',
                        'kayak',
                    ),
                    'overnight' => 'Trên du thuyền',
                    'content' => 'Đón tại bến tàu/khách sạn, di chuyển ra vịnh Lan Hạ. Ăn trưa hải sản trên du thuyền, chiều chèo kayak qua hang Luồn và tắm tại bãi Ba Trái Đào. Tối tiệc BBQ trên boong, câu mực đêm.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Bình minh vịnh — Trekking vườn quốc gia',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'cruise',
                        'car',
                        'trekking',
                    ),
                    'overnight' => 'Trung tâm Cát Bà',
                    'content' => 'Đón bình minh trên vịnh, trả tàu về bến Bèo. Chiều trekking 2 giờ trong vườn quốc gia, ghé hang Trung Trang. Tối tự do khám phá chợ đêm Cát Bà.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Đảo Khỉ — Tạm biệt Cát Bà',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu ra Đảo Khỉ (Cát Dứa), tắm biển và lặn ngắm san hô buổi sáng. Ăn trưa hải sản, chiều xe/tàu đưa về Hải Phòng hoặc Hà Nội theo lịch đã đặt.',
                ),
            ),
            'inclusions' => array(
                'Cabin du thuyền 1 đêm + khách sạn 3* trung tâm 1 đêm',
                'Toàn bộ bữa ăn theo chương trình (B/L/D)',
                'Vé tàu ra Đảo Khỉ, vé vào vườn quốc gia',
                'Kayak, hướng dẫn viên trekking địa phương',
                'Xe đưa đón trong đảo',
            ),
            'exclusions' => array(
                'Vé phà/xe từ Hà Nội hoặc Hải Phòng ra Cát Bà (đặt thêm)',
                'Đồ uống ngoài chương trình',
                'Tiền tip',
                'Bảo hiểm du lịch',
            ),
            'notes' => array(
                'Lịch trình có thể đổi thứ tự tuỳ thời tiết và giờ tàu.',
                'Tour ghép nhóm nhỏ tối đa 15 khách; có phương án tour riêng.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Cát Bà cách Hà Nội bao xa và đi bằng gì?',
                    'a' => 'Khoảng 150km, đi limousine gộp vé phà mất 2.5–3 giờ, hoặc kết hợp cao tốc Hải Phòng và tàu cao tốc Bến Bính — Cát Bà.',
                ),
                array(
                    'q' => 'Tour 3 ngày có phù hợp gia đình có trẻ nhỏ?',
                    'a' => 'Có. Ngày trekking có phương án đi ngắn hơn cho trẻ em, và du thuyền, Đảo Khỉ đều an toàn cho trẻ từ 5 tuổi.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 2450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'trekking-viet-hai-vqg-1-ngay',
            'title' => 'Trekking Việt Hải xuyên Vườn quốc gia Cát Bà — 1 ngày',
            'zoneSlug' => 'vuon-quoc-gia',
            'zone' => 'Vườn quốc gia Cát Bà',
            'tourCode' => 'CB1D-02',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 76,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                'day-trip',
                'trekking-vqg',
                'lang-viet-hai',
            ),
            'quote' => array(
                'text' => 'Đường rừng mát, làng Việt Hải yên bình đến lạ — cảm giác như tách hẳn khỏi thị trấn.',
                'author' => 'Chị Hải Yến',
            ),
            'places' => array(
                'Trung tâm VQG',
                'Ao Ếch',
                'Làng Việt Hải',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Một ngày trekking 10km xuyên rừng nguyên sinh tới làng Việt Hải — nơi không có xe máy và điện lưới chỉ mới về gần đây.',
            'highlights' => array(
                'Rừng nguyên sinh và khả năng gặp voọc Cát Bà',
                'Ao Ếch — hồ nước ngọt tự nhiên giữa rừng',
                'Ăn trưa tại nhà dân làng Việt Hải',
                'Thuyền về qua vịnh, ngắm núi đá vôi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Trekking Việt Hải — Ao Ếch',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'car',
                        'trekking',
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Xe đưa tới cửa rừng, trekking 10km qua Ao Ếch tới làng Việt Hải, ăn trưa tại nhà dân. Chiều đi thuyền từ Việt Hải về lại bến Bèo, ngắm vịnh trên đường về.',
                ),
            ),
            'inclusions' => array(
                'Hướng dẫn viên trekking địa phương',
                'Vé vào vườn quốc gia',
                'Bữa trưa tại làng',
                'Thuyền về Việt Hải — bến Bèo',
            ),
            'exclusions' => array(
                'Xe đón tại khách sạn (có thể gộp thêm)',
                'Đồ uống',
                'Giày trekking (nên tự mang)',
            ),
            'notes' => array(
                'Độ khó trung bình, cần giày chống trượt.',
                'Không phù hợp mùa mưa lớn tháng 7–8 do đường trơn.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Có thể rút ngắn lộ trình không?',
                    'a' => 'Có — có thể đi thuyền một chiều và trekking một chiều để giảm còn khoảng 6km cho người mới.',
                ),
            ),
            'galleryCount' => 4,
            'priceFrom' => 850000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'vinh-lan-ha-kayak-dao-khi-1-ngay',
            'title' => 'Vịnh Lan Hạ 1 ngày — Kayak, tắm biển & Đảo Khỉ',
            'zoneSlug' => 'vinh-lan-ha',
            'zone' => 'Vịnh Lan Hạ',
            'tourCode' => 'CB1D-03',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 210,
            'badge' => 'Được yêu thích',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'vinh-lan-ha',
                'dao-khi-tam-bien',
            ),
            'quote' => array(
                'text' => 'Nước trong đến mức nhìn thấy đáy — đúng như quảng cáo, không hề bị thổi phồng.',
                'author' => 'Gia đình chị Thu Hằng',
            ),
            'places' => array(
                'Vịnh Lan Hạ',
                'Hang Luồn',
                'Đảo Khỉ',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Tour ngày kinh điển nhất của Cát Bà: tàu gỗ, kayak, tắm biển và ghé Đảo Khỉ — không cần ngủ đêm.',
            'highlights' => array(
                'Chèo kayak qua hang Luồn',
                'Tắm và lặn ngắm san hô tại vịnh',
                'Ghé Đảo Khỉ chơi với khỉ hoang dã',
                'Ăn trưa hải sản trên tàu',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Vịnh Lan Hạ — Đảo Khỉ',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'boat',
                        'kayak',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu đón tại bến Bèo, ra vịnh Lan Hạ chèo kayak, tắm biển tại các bãi hoang sơ. Ăn trưa trên tàu, chiều ghé Đảo Khỉ trước khi về lại bến.',
                ),
            ),
            'inclusions' => array(
                'Tàu tham quan cả ngày',
                'Kayak',
                'Bữa trưa hải sản',
                'Vé vào Đảo Khỉ',
            ),
            'exclusions' => array(
                'Đồ uống',
                'Xe đón tại khách sạn (đặt thêm phí nhỏ)',
            ),
            'notes' => array(
                'Khởi hành 08:30, về khoảng 16:00 — không cần ngủ đêm.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cat-ba-ha-long-ket-hop-2n1d',
            'title' => 'Cát Bà — Hạ Long kết hợp 2 ngày 1 đêm',
            'zoneSlug' => 'ket-hop-ha-long',
            'zone' => 'Kết hợp Hạ Long',
            'tourCode' => 'CBHL2D-04',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 58,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                '2n1d',
            ),
            'quote' => array(
                'text' => 'Đi một lần thấy cả hai vịnh — không còn phải chọn Cát Bà hay Hạ Long nữa.',
                'author' => 'Anh David Chen',
            ),
            'places' => array(
                'Cát Bà',
                'Vịnh Lan Hạ',
                'Tuần Châu',
                'Vịnh Hạ Long',
            ),
            'start' => 'Cát Bà',
            'end' => 'Hạ Long',
            'highlightsIntro' => 'Kết hợp trọn vẹn hai vịnh di sản chỉ trong 2 ngày, nối tuyến bằng tàu cao tốc Tuần Châu — Cát Bà.',
            'highlights' => array(
                'Ngủ đêm trên du thuyền vịnh Lan Hạ',
                'Chèo kayak, tắm biển hoang sơ',
                'Tàu cao tốc xuyên vịnh sang Hạ Long',
                'Tham quan hang Sửng Sốt trước khi kết thúc',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cát Bà — Vịnh Lan Hạ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'boat',
                        'kayak',
                        'cruise',
                    ),
                    'overnight' => 'Trên du thuyền',
                    'content' => 'Đón tại Cát Bà, ra vịnh Lan Hạ, kayak và tắm biển. Ngủ đêm trên du thuyền, tiệc tối trên boong.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Sang Hạ Long — Hang Sửng Sốt',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'cruise',
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng dậy sớm ngắm bình minh, tàu cao tốc đưa sang Tuần Châu (Hạ Long), thăm hang Sửng Sốt trước khi kết thúc hành trình tại Hạ Long hoặc quay lại Hà Nội.',
                ),
            ),
            'inclusions' => array(
                'Cabin du thuyền 1 đêm',
                'Bữa ăn theo chương trình',
                'Tàu cao tốc Cát Bà — Tuần Châu',
                'Vé tham quan hang Sửng Sốt',
            ),
            'exclusions' => array(
                'Xe từ Hà Nội đến Cát Bà và từ Hạ Long về Hà Nội (đặt thêm)',
                'Đồ uống',
                'Tip',
            ),
            'notes' => array(
                'Có thể đổi chiều: khởi hành từ Hạ Long, kết thúc tại Cát Bà.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Tàu cao tốc Cát Bà — Tuần Châu mất bao lâu?',
                    'a' => 'Khoảng 45–60 phút, chạy trực tiếp qua vịnh, không cần quay lại đất liền.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 3200000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cat-ba-5-ngay-nghi-duong-kham-pha',
            'title' => 'Cát Bà 5 ngày 4 đêm — Nghỉ dưỡng, lặn biển & trekking trọn vẹn',
            'zoneSlug' => 'trung-tam-cat-ba',
            'zone' => 'Trung tâm đảo Cát Bà',
            'tourCode' => 'CB5D-05',
            'duration' => '5 ngày 4 đêm',
            'days' => 5,
            'rating' => 5,
            'reviewCount' => 34,
            'badge' => 'Trải nghiệm sâu',
            'featured' => true,
            'styles' => array(
                '5-plus-days',
            ),
            'quote' => array(
                'text' => 'Năm ngày đủ để không vội — có cả ngày chỉ nằm biển, có ngày leo núi mệt nhoài, rất đáng.',
                'author' => 'Cặp đôi Minh & Diệu',
            ),
            'places' => array(
                'Bãi Cát Cò',
                'Vịnh Lan Hạ',
                'Vườn quốc gia',
                'Làng Việt Hải',
                'Đảo Khỉ',
                'Cái Bèo',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Lịch trình sâu nhất của chúng tôi: đủ thời gian cho biển, rừng, làng chài và cả những buổi chiều không làm gì.',
            'highlights' => array(
                '1 đêm du thuyền vịnh Lan Hạ + 3 đêm resort Cát Cò',
                'Trekking Việt Hải cả ngày',
                'Lặn ngắm san hô trên vịnh',
                'Một ngày tự do tại resort bãi Cát Cò',
                'Buổi sáng Đảo Khỉ trước khi về',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đến Cát Bà — Nhận phòng bãi Cát Cò',
                    'meals' => 'Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Bãi Cát Cò',
                    'content' => 'Đón tại Hải Phòng/Hà Nội, ra đảo, nhận phòng resort. Tối dạo chợ Cát Bà, ăn hải sản.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Vịnh Lan Hạ — Lên du thuyền',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'boat',
                        'kayak',
                        'cruise',
                    ),
                    'overnight' => 'Trên du thuyền',
                    'content' => 'Ra vịnh, kayak và lặn ngắm san hô, ngủ đêm trên du thuyền.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Trekking Việt Hải xuyên vườn quốc gia',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'trekking',
                        'boat',
                        'car',
                    ),
                    'overnight' => 'Bãi Cát Cò',
                    'content' => 'Trả tàu về bến Bèo, trekking tới làng Việt Hải và Ao Ếch, thuyền về. Tối nghỉ lại resort.',
                ),
                array(
                    'day' => 4,
                    'title' => 'Ngày tự do nghỉ dưỡng bãi Cát Cò',
                    'meals' => 'Sáng',
                    'transport' => array(),
                    'overnight' => 'Bãi Cát Cò',
                    'content' => 'Buổi sáng tắm biển, spa tùy chọn. Chiều có thể thuê xe máy dạo thị trấn hoặc ghé làng chài Cái Bèo.',
                ),
                array(
                    'day' => 5,
                    'title' => 'Đảo Khỉ — Tạm biệt đảo',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu ra Đảo Khỉ buổi sáng, ăn trưa hải sản, chiều xe/tàu về Hải Phòng hoặc Hà Nội.',
                ),
            ),
            'inclusions' => array(
                'Resort 4* bãi Cát Cò 3 đêm + du thuyền 1 đêm',
                'Toàn bộ bữa ăn theo chương trình',
                'Vé vườn quốc gia, Đảo Khỉ, lặn ngắm san hô',
                'Hướng dẫn viên suốt tuyến',
            ),
            'exclusions' => array(
                'Vé phà/xe khởi hành và kết thúc',
                'Chi phí cá nhân',
                'Bảo hiểm du lịch',
            ),
            'notes' => array(
                'Có thể đổi ngày tự do thành tour leo núi đá vôi hoặc lặn chuyên sâu theo yêu cầu.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Tour 5 ngày có phù hợp cặp đôi trăng mật?',
                    'a' => 'Có — nhiều cặp chọn cabin ban công trên du thuyền và ngày tự do tại resort cho riêng tư.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 5900000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'lang-chai-cai-beo-hang-trung-trang-nua-ngay',
            'title' => 'Làng chài Cái Bèo & hang Trung Trang — nửa ngày',
            'zoneSlug' => 'cai-beo',
            'zone' => 'Làng chài Cái Bèo',
            'tourCode' => 'CBHD-06',
            'duration' => 'Nửa ngày (4 giờ)',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 41,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'lang-chai-cai-beo',
            ),
            'quote' => array(
                'text' => 'Tour ngắn nhưng đúng chất địa phương — được lên nhà bè, xem người dân nuôi cá.',
                'author' => 'Bạn Anh Khoa',
            ),
            'places' => array(
                'Làng chài Cái Bèo',
                'Hang Trung Trang',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Lựa chọn lý tưởng cho buổi chiều còn trống trước hoặc sau chuyến đi chính — ghé làng chài cổ và một hang động ngắn.',
            'highlights' => array(
                'Thuyền nan tham quan làng chài nổi Cái Bèo',
                'Nghe kể về di chỉ khảo cổ hơn 6.000 năm',
                'Hang Trung Trang mát mẻ, dễ đi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cái Bèo — Trung Trang',
                    'meals' => null,
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Thuyền nan dạo quanh làng chài Cái Bèo, sau đó xe đưa tới hang Trung Trang tham quan khoảng 30 phút rồi về lại trung tâm.',
                ),
            ),
            'inclusions' => array(
                'Thuyền nan làng chài',
                'Vé hang Trung Trang',
                'Xe di chuyển',
            ),
            'exclusions' => array(
                'Ăn uống',
                'HDV tiếng Anh (có phụ phí nhỏ)',
            ),
            'notes' => array(
                'Phù hợp ghép cùng buổi sáng hoặc buổi tối tự do trong ngày.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 350000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cat-ba-2-ngay-gia-dinh-bien-dao',
            'title' => 'Cát Bà 2 ngày 1 đêm — Gia đình: biển, vịnh & Đảo Khỉ',
            'zoneSlug' => 'trung-tam-cat-ba',
            'zone' => 'Trung tâm đảo Cát Bà',
            'tourCode' => 'CB2D-07',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 88,
            'badge' => 'Gia đình',
            'featured' => true,
            'styles' => array(
                '2n1d',
            ),
            'quote' => array(
                'text' => 'Con tôi 6 tuổi cũng đi kayak được — lịch trình vừa sức, không vội.',
                'author' => 'Chị Mai Anh',
            ),
            'places' => array(
                'Bãi Cát Cò',
                'Vịnh Lan Hạ',
                'Đảo Khỉ',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Cuối tuần gọn cho gia đình: một ngày vịnh + Đảo Khỉ, một buổi sáng tắm biển Cát Cò trước khi về.',
            'highlights' => array(
                'Tour vịnh Lan Hạ & Đảo Khỉ cả ngày',
                'Ngủ khách sạn trung tâm gần biển',
                'Buổi sáng tự do tắm Cát Cò 1–3',
                'Nhịp độ chậm, phù hợp trẻ từ 5 tuổi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Vịnh Lan Hạ — Đảo Khỉ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'boat',
                        'kayak',
                    ),
                    'overnight' => 'Trung tâm Cát Bà',
                    'content' => 'Tàu ra vịnh, kayak nhẹ, tắm biển, ghé Đảo Khỉ. Tối ăn hải sản chợ Cát Bà.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Bãi Cát Cò — Về',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng tắm biển Cát Cò, nhận phòng muộn / gửi đồ, chiều về Hải Phòng hoặc Hà Nội.',
                ),
            ),
            'inclusions' => array(
                'Khách sạn 3* 1 đêm',
                'Tour vịnh + Đảo Khỉ',
                'Bữa trưa ngày 1',
                'Xe đưa đón trong đảo',
            ),
            'exclusions' => array(
                'Vé phà/xe ra đảo',
                'Bữa tối, đồ uống',
                'Tip',
            ),
            'notes' => array(
                'Có ghế trẻ em trên tàu theo yêu cầu trước 24 giờ.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 1650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'dap-xe-xuyen-dao-cat-ba-nua-ngay',
            'title' => 'Đạp xe xuyên đảo Cát Bà — làng chài & đồi chè',
            'zoneSlug' => 'trung-tam-cat-ba',
            'zone' => 'Trung tâm đảo Cát Bà',
            'tourCode' => 'CBHD-08',
            'duration' => 'Nửa ngày (3–4 giờ)',
            'days' => 1,
            'rating' => 4.6,
            'reviewCount' => 29,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Gió biển, đường ít xe — cách xem đảo khác hẳn ngồi ô tô.',
                'author' => 'Anh Phong',
            ),
            'places' => array(
                'Thị trấn Cát Bà',
                'Đồi chè',
                'Làng ven biển',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Tour nửa ngày trên xe đạp địa hình nhẹ: ven biển, đồi chè và làng nhỏ quanh thị trấn.',
            'highlights' => array(
                'Xe đạp + mũ bảo hiểm',
                'Dừng ảnh đồi chè và view vịnh',
                'HDV bản địa dẫn đường',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đạp xe quanh đảo',
                    'meals' => null,
                    'transport' => array(
                        'bike',
                    ),
                    'overnight' => null,
                    'content' => 'Nhận xe tại trung tâm, đạp ~15–20km vòng đồi chè và làng ven biển, nghỉ nước dừa, về lại điểm xuất phát.',
                ),
            ),
            'inclusions' => array(
                'Thuê xe đạp',
                'Mũ bảo hiểm',
                'Nước uống',
                'HDV',
            ),
            'exclusions' => array(
                'Ăn uống',
                'Bảo hiểm cá nhân',
            ),
            'notes' => array(
                'Độ khó dễ; có xe điện hỗ trợ người lớn tuổi.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 290000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'bai-cat-co-tam-bien-spa-nua-ngay',
            'title' => 'Bãi Cát Cò — Tắm biển & thư giãn nửa ngày',
            'zoneSlug' => 'trung-tam-cat-ba',
            'zone' => 'Trung tâm đảo Cát Bà',
            'tourCode' => 'CBHD-09',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.5,
            'reviewCount' => 22,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
            ),
            'quote' => array(
                'text' => 'Chỉ cần một buổi chiều Cát Cò là đã “reset” cả tuần làm việc.',
                'author' => 'Chị Hà My',
            ),
            'places' => array(
                'Bãi Cát Cò 1',
                'Bãi Cát Cò 2',
                'Bãi Cát Cò 3',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Buổi chiều nhàn: ghế nằm, nước trong, có thể thêm massage chân ven biển.',
            'highlights' => array(
                'Ghế nằm + ô tại Cát Cò',
                'Nước dừa / nước trái cây',
                'Tuỳ chọn spa 30 phút',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Thư giãn Cát Cò',
                    'meals' => null,
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Xe đưa tới bãi, nhận ghế nằm, tự do tắm biển 2–3 giờ, tuỳ chọn spa rồi về khách sạn.',
                ),
            ),
            'inclusions' => array(
                'Xe đưa đón',
                'Ghế nằm',
                '1 nước giải khát',
            ),
            'exclusions' => array(
                'Spa (phụ phí)',
                'Đồ ăn',
            ),
            'notes' => array(
                'Không phải tour “chạy điểm” — đúng nghĩa nghỉ.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 250000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'vinh-lan-ha-hoang-hon-nua-ngay',
            'title' => 'Hoàng hôn vịnh Lan Hạ — nửa ngày trên tàu',
            'zoneSlug' => 'vinh-lan-ha',
            'zone' => 'Vịnh Lan Hạ',
            'tourCode' => 'CBHD-10',
            'duration' => 'Nửa ngày (15:00–19:30)',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 67,
            'badge' => 'Lãng mạn',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'vinh-lan-ha',
            ),
            'quote' => array(
                'text' => 'Ánh nắng vàng trên đá vôi — khoảnh khắc đẹp nhất chuyến đi của chúng tôi.',
                'author' => 'Cặp đôi Nam & Linh',
            ),
            'places' => array(
                'Vịnh Lan Hạ',
                'Bãi hoàng hôn',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Tour chiều–tối: tàu nhỏ, đồ uống nhẹ, ngắm hoàng hôn và trở về khi đèn làng chài lấp lánh.',
            'highlights' => array(
                'Tàu nhỏ nhóm tối đa 12 khách',
                'Đồ uống chào mừng',
                'Góc chụp hoàng hôn đẹp nhất vịnh',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Hoàng hôn Lan Hạ',
                    'meals' => 'Đồ uống nhẹ',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Lên tàu 15:00 tại bến Bèo, dạo vịnh, dừng bãi ngắm hoàng hôn, về bến khoảng 19:30.',
                ),
            ),
            'inclusions' => array(
                'Tàu',
                'Đồ uống nhẹ',
                'HDV',
            ),
            'exclusions' => array(
                'Bữa tối',
                'Xe khách sạn (có thể thêm)',
            ),
            'notes' => array(
                'Phụ thuộc thời tiết nắng chiều; có lịch thay thế nếu mưa lớn.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 480000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'vinh-lan-ha-lan-ngam-san-ho-1-ngay',
            'title' => 'Lặn ngắm san hô vịnh Lan Hạ — 1 ngày',
            'zoneSlug' => 'vinh-lan-ha',
            'zone' => 'Vịnh Lan Hạ',
            'tourCode' => 'CB1D-11',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 54,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'vinh-lan-ha',
            ),
            'quote' => array(
                'text' => 'San hô còn khá nguyên — hướng dẫn viên chỉ đúng điểm nước trong.',
                'author' => 'Anh Khoa Diving',
            ),
            'places' => array(
                'Vịnh Lan Hạ',
                'Vạn Bội',
                'Ba Trái Đào',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Ngày tập trung dưới nước: 2–3 điểm snorkel, thiết bị đầy đủ, HDV chuyên lặn.',
            'highlights' => array(
                '2–3 điểm lặn ngắm',
                'Mặt nạ, ống thở, áo phao',
                'Ăn trưa trên tàu',
                'Ảnh dưới nước (tuỳ chọn)',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Snorkel Lan Hạ',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu ra các điểm san hô sáng–chiều, nghỉ trưa trên tàu, về bến khoảng 16:00.',
                ),
            ),
            'inclusions' => array(
                'Tàu',
                'Thiết bị snorkel',
                'Bữa trưa',
                'HDV lặn',
            ),
            'exclusions' => array(
                'Ảnh/video dưới nước',
                'Đồ uống có cồn',
            ),
            'notes' => array(
                'Biết bơi cơ bản; trẻ từ 8 tuổi đi cùng người lớn.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 890000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'vinh-lan-ha-private-kayak-1-ngay',
            'title' => 'Kayak riêng vịnh Lan Hạ — tour private 1 ngày',
            'zoneSlug' => 'vinh-lan-ha',
            'zone' => 'Vịnh Lan Hạ',
            'tourCode' => 'CB1D-12',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 5,
            'reviewCount' => 19,
            'badge' => 'Private',
            'featured' => false,
            'styles' => array(
                'day-trip',
                'vinh-lan-ha',
            ),
            'quote' => array(
                'text' => 'Không đông đúc như tour ghép — được chèo vào những ngách không tàu lớn vào được.',
                'author' => 'Chị Quỳnh',
            ),
            'places' => array(
                'Hang Luồn',
                'Bãi kín',
                'Vịnh Lan Hạ',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Tàu riêng + kayak riêng cho 2–6 khách: lịch trình linh hoạt theo sức và sở thích.',
            'highlights' => array(
                'Tàu & HDV riêng',
                'Kayak vào hang hẹp',
                'Bữa trưa picnic trên bãi',
                'Giờ khởi hành linh hoạt',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Kayak private',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'boat',
                        'kayak',
                    ),
                    'overnight' => null,
                    'content' => 'Theo lịch riêng: chèo hang, tắm bãi kín, picnic, về theo giờ thỏa thuận.',
                ),
            ),
            'inclusions' => array(
                'Tàu riêng',
                'Kayak',
                'Picnic trưa',
                'HDV',
            ),
            'exclusions' => array(
                'Đồ uống có cồn',
                'Tip',
            ),
            'notes' => array(
                'Giá theo nhóm 2 khách; thêm khách tính phụ phí.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 2100000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'trekking-vqg-nua-ngay-de',
            'title' => 'Trekking vườn quốc gia nửa ngày — dễ, phù hợp mọi lứa tuổi',
            'zoneSlug' => 'vuon-quoc-gia',
            'zone' => 'Vườn quốc gia Cát Bà',
            'tourCode' => 'CBHD-13',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 45,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'trekking-vqg',
            ),
            'quote' => array(
                'text' => 'Không phải 10km — chỉ vài km rừng mát, vẫn thấy được “chất” vườn quốc gia.',
                'author' => 'Gia đình Hùng',
            ),
            'places' => array(
                'Cửa rừng VQG',
                'Hang Trung Trang',
                'Điểm view',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Phiên bản rút gọn của trekking Việt Hải: 3–4km, hang Trung Trang, không đòi hỏi thể lực cao.',
            'highlights' => array(
                'Độ khó dễ',
                'Hang Trung Trang',
                'HDV bản địa',
                'Xe đưa đón',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Rừng nhẹ — hang Trung Trang',
                    'meals' => null,
                    'transport' => array(
                        'car',
                        'trekking',
                    ),
                    'overnight' => null,
                    'content' => 'Xe tới cửa rừng, đi bộ nhẹ, thăm hang, nghỉ nước, về trung tâm trước trưa hoặc chiều tùy khung giờ chọn.',
                ),
            ),
            'inclusions' => array(
                'Vé VQG',
                'HDV',
                'Xe đưa đón',
                'Nước',
            ),
            'exclusions' => array(
                'Ăn uống',
            ),
            'notes' => array(
                'Phù hợp người lớn tuổi và trẻ em từ 6 tuổi.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 420000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'camping-viet-hai-2n1d',
            'title' => 'Camping / Homestay làng Việt Hải — 2 ngày 1 đêm',
            'zoneSlug' => 'lang-viet-hai',
            'zone' => 'Làng Việt Hải',
            'tourCode' => 'CB2D-14',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 37,
            'badge' => 'Bản địa',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'trekking-vqg',
                'lang-viet-hai',
            ),
            'quote' => array(
                'text' => 'Đêm không sóng điện thoại — chỉ tiếng côn trùng và đèn dầu nhà dân.',
                'author' => 'Bạn Minh Quân',
            ),
            'places' => array(
                'Vườn quốc gia',
                'Ao Ếch',
                'Làng Việt Hải',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Trekking vào làng, ngủ homestay hoặc lều, ăn cùng người dân, thuyền về ngày hôm sau.',
            'highlights' => array(
                'Ngủ homestay / camping tại làng',
                'Ăn tối cùng gia đình địa phương',
                'Bình minh Ao Ếch',
                'Thuyền về qua vịnh',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Trekking vào Việt Hải',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'trekking',
                    ),
                    'overnight' => 'Làng Việt Hải',
                    'content' => 'Trekking xuyên rừng tới làng, nhận chỗ nghỉ, tắm giếng/ao, ăn tối nhà dân.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Ao Ếch — Thuyền về',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'trekking',
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng đi Ao Ếch, ăn trưa nhẹ, thuyền từ Việt Hải về bến Bèo.',
                ),
            ),
            'inclusions' => array(
                'Homestay/lều 1 đêm',
                'Bữa ăn theo chương trình',
                'Vé VQG',
                'HDV + thuyền về',
            ),
            'exclusions' => array(
                'Túi ngủ (có thể thuê)',
                'Đồ uống',
            ),
            'notes' => array(
                'Sóng điện thoại yếu; nên báo gia đình trước.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Có toilet không?',
                    'a' => 'Homestay có nhà vệ sinh đơn giản; camping dùng khu vệ sinh chung của làng.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 1450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'lang-viet-hai-thuyen-nua-ngay',
            'title' => 'Làng Việt Hải bằng thuyền — nửa ngày không trekking',
            'zoneSlug' => 'lang-viet-hai',
            'zone' => 'Làng Việt Hải',
            'tourCode' => 'CBHD-15',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.6,
            'reviewCount' => 31,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'lang-viet-hai',
            ),
            'quote' => array(
                'text' => 'Muốn thấy làng mà không đi bộ 10km — thuyền là lựa chọn đúng.',
                'author' => 'Chị Lan',
            ),
            'places' => array(
                'Làng Việt Hải',
                'Vịnh gần làng',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Đi thuyền thẳng tới Việt Hải, dạo làng, uống nước chè nhà dân, không cần trekking.',
            'highlights' => array(
                'Không trekking',
                'Thăm nhà dân',
                'Phù hợp trẻ nhỏ / người lớn tuổi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Thuyền tới Việt Hải',
                    'meals' => null,
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu từ bến Bèo vào Việt Hải, dạo làng 60–90 phút, về lại trung tâm.',
                ),
            ),
            'inclusions' => array(
                'Tàu khứ hồi',
                'HDV',
                'Trà nước nhà dân',
            ),
            'exclusions' => array(
                'Ăn trưa',
                'Quà lưu niệm',
            ),
            'notes' => array(
                'Phụ thuộc thuỷ triều — giờ xuất phát có thể dịch ±30 phút.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 520000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'dao-khi-1-ngay-tam-bien-lan',
            'title' => 'Đảo Khỉ (Cát Dứa) 1 ngày — Tắm biển & lặn ngắm',
            'zoneSlug' => 'dao-khi',
            'zone' => 'Đảo Khỉ (Cát Dứa)',
            'tourCode' => 'CB1D-16',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 102,
            'badge' => 'Được yêu thích',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'dao-khi-tam-bien',
            ),
            'quote' => array(
                'text' => 'Bãi cát trắng, khỉ hoang dã, nước trong — đúng “must-do” của Cát Bà.',
                'author' => 'Gia đình Đức',
            ),
            'places' => array(
                'Đảo Khỉ',
                'Bãi tắm Cát Dứa',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Tour tập trung Đảo Khỉ cả ngày: tắm, snorkel, chơi với khỉ (có hướng dẫn an toàn), không chạy nhiều điểm.',
            'highlights' => array(
                'Cả ngày tại Đảo Khỉ',
                'Snorkel gần bãi',
                'Ăn trưa hải sản',
                'HDV hướng dẫn tiếp xúc khỉ an toàn',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cả ngày Đảo Khỉ',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu ra đảo sáng, tắm–lặn–nghỉ trưa, chiều tự do trước khi về bến.',
                ),
            ),
            'inclusions' => array(
                'Tàu',
                'Vé đảo',
                'Bữa trưa',
                'Thiết bị snorkel cơ bản',
            ),
            'exclusions' => array(
                'Đồ uống',
                'Thức ăn cho khỉ (không khuyến khích)',
            ),
            'notes' => array(
                'Không cho khỉ ăn đồ ngọt; giữ túi xách cẩn thận.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 550000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'dao-khi-nua-ngay',
            'title' => 'Đảo Khỉ nửa ngày — gọn, dễ ghép lịch',
            'zoneSlug' => 'dao-khi',
            'zone' => 'Đảo Khỉ (Cát Dứa)',
            'tourCode' => 'CBHD-17',
            'duration' => 'Nửa ngày',
            'days' => 1,
            'rating' => 4.6,
            'reviewCount' => 58,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'dao-khi-tam-bien',
            ),
            'quote' => array(
                'text' => 'Chỉ nửa ngày nhưng đã đủ cảm giác “đã đến Đảo Khỉ”.',
                'author' => 'Bạn Hạnh',
            ),
            'places' => array(
                'Đảo Khỉ',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Buổi sáng hoặc chiều: tàu ra đảo, tắm biển ngắn, về kịp giờ ăn hoặc chuyến phà.',
            'highlights' => array(
                '3–4 giờ trên đảo',
                'Tắm biển',
                'Linh hoạt khung giờ sáng/chiều',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đảo Khỉ nửa ngày',
                    'meals' => null,
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu ra đảo, tắm–chụp ảnh–nghỉ nước, về lại bến theo khung giờ đã chọn.',
                ),
            ),
            'inclusions' => array(
                'Tàu khứ hồi',
                'Vé đảo',
            ),
            'exclusions' => array(
                'Ăn uống',
                'Snorkel (có thể thuê thêm)',
            ),
            'notes' => array(
                'Lý tưởng ghép sau trekking hoặc trước giờ về đất liền.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 320000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cau-muc-dem-lang-chai-cai-beo',
            'title' => 'Câu mực đêm làng chài Cái Bèo',
            'zoneSlug' => 'cai-beo',
            'zone' => 'Làng chài Cái Bèo',
            'tourCode' => 'CBNT-18',
            'duration' => 'Buổi tối (2.5–3 giờ)',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 49,
            'badge' => 'Đêm',
            'featured' => false,
            'styles' => array(
                'day-trip',
                'lang-chai-cai-beo',
            ),
            'quote' => array(
                'text' => 'Đèn mực trên vịnh lung linh — trẻ con thích điên lên khi câu được con đầu tiên.',
                'author' => 'Anh Tuấn',
            ),
            'places' => array(
                'Làng chài Cái Bèo',
                'Vịnh đêm',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Ra vịnh ban đêm cùng ngư dân, câu mực, ăn hải sản nướng nhẹ trên thuyền.',
            'highlights' => array(
                'Thuyền câu mực đêm',
                'Dụng cụ câu đầy đủ',
                'Đồ nướng nhẹ trên thuyền',
                'HDV/ngư dân hướng dẫn',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Câu mực đêm',
                    'meals' => 'Đồ nướng nhẹ',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tối lên thuyền tại Cái Bèo, ra điểm câu, trải nghiệm 2 giờ, về bến khoảng 21:30–22:00.',
                ),
            ),
            'inclusions' => array(
                'Thuyền',
                'Cần câu',
                'Đồ nướng nhẹ',
                'Áo phao',
            ),
            'exclusions' => array(
                'Đồ uống có cồn',
                'Mực mang về chế biến thêm (có thể mua)',
            ),
            'notes' => array(
                'Không đi khi sóng lớn / cấm tàu đêm.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 450000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cat-ba-ha-long-ket-hop-3n2d',
            'title' => 'Cát Bà — Hạ Long 3 ngày 2 đêm — Hai vịnh trọn vẹn',
            'zoneSlug' => 'ket-hop-ha-long',
            'zone' => 'Kết hợp Hạ Long',
            'tourCode' => 'CBHL3D-19',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 5,
            'reviewCount' => 27,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                '3n2d',
            ),
            'quote' => array(
                'text' => 'Một đêm Lan Hạ, một đêm Hạ Long — cảm nhận rõ sự khác biệt của hai vịnh.',
                'author' => 'Anh Việt',
            ),
            'places' => array(
                'Cát Bà',
                'Vịnh Lan Hạ',
                'Tuần Châu',
                'Vịnh Hạ Long',
                'Hang Sửng Sốt',
            ),
            'start' => 'Cát Bà',
            'end' => 'Hạ Long',
            'highlightsIntro' => 'Phiên bản sâu hơn tour 2 ngày 1 đêm: ngủ 1 đêm Lan Hạ + 1 đêm du thuyền Hạ Long, tàu cao tốc nối tuyến.',
            'highlights' => array(
                '1 đêm du thuyền Lan Hạ',
                '1 đêm du thuyền Hạ Long',
                'Tàu cao tốc xuyên vịnh',
                'Hang Sửng Sốt & kayak',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cát Bà — Lan Hạ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'cruise',
                        'kayak',
                    ),
                    'overnight' => 'Du thuyền Lan Hạ',
                    'content' => 'Lên tàu Lan Hạ, kayak, BBQ tối.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Sang Hạ Long',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'boat',
                        'cruise',
                    ),
                    'overnight' => 'Du thuyền Hạ Long',
                    'content' => 'Tàu cao tốc sang Tuần Châu, chuyển du thuyền Hạ Long, thăm hang, kayak.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Kết thúc tại Hạ Long',
                    'meals' => 'Sáng; Trưa nhẹ',
                    'transport' => array(
                        'cruise',
                    ),
                    'overnight' => null,
                    'content' => 'Bình minh trên vịnh, brunch, trả khách tại Hạ Long / xe về Hà Nội (đặt thêm).',
                ),
            ),
            'inclusions' => array(
                '2 đêm cabin du thuyền',
                'Bữa ăn theo chương trình',
                'Tàu cao tốc nối tuyến',
                'Vé thắng cảnh',
            ),
            'exclusions' => array(
                'Xe Hà Nội hai đầu',
                'Tip',
                'Đồ uống',
            ),
            'notes' => array(
                'Có thể đảo chiều khởi hành từ Hạ Long.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 5600000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'leo-nui-da-voi-lan-ha-1-ngay',
            'title' => 'Leo núi đá vôi / deep water solo vịnh Lan Hạ — 1 ngày',
            'zoneSlug' => 'vinh-lan-ha',
            'zone' => 'Vịnh Lan Hạ',
            'tourCode' => 'CB1D-20',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 16,
            'badge' => 'Mạo hiểm',
            'featured' => false,
            'styles' => array(
                'day-trip',
                'vinh-lan-ha',
            ),
            'quote' => array(
                'text' => 'Nhảy xuống nước sau mỗi đường leo — adrenaline đúng nghĩa.',
                'author' => 'Bạn Duy Climb',
            ),
            'places' => array(
                'Vách đá Lan Hạ',
                'Điểm deep water solo',
            ),
            'start' => 'Cát Bà',
            'end' => 'Cát Bà',
            'highlightsIntro' => 'Tour mạo hiểm có hướng dẫn viên chuyên leo: deep water solo hoặc top-rope trên vách đá vịnh.',
            'highlights' => array(
                'HDV leo núi chuyên nghiệp',
                'Thiết bị an toàn',
                'Tàu riêng nhóm nhỏ',
                'Cứu hộ trên nước',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Leo núi trên vịnh',
                    'meals' => 'Trưa nhẹ',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tàu ra vách, khởi động, leo 2–4 đường tuỳ sức, nhảy nước an toàn, về chiều.',
                ),
            ),
            'inclusions' => array(
                'Tàu',
                'Dây/yếm/mũ',
                'HDV',
                'Trưa nhẹ',
            ),
            'exclusions' => array(
                'Bảo hiểm thể thao chuyên biệt (khuyến nghị tự mua)',
                'Ảnh GoPro',
            ),
            'notes' => array(
                'Yêu cầu biết bơi; từ 14 tuổi; khai báo sức khỏe.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 1800000,
            'currency' => 'VND',
        ),
    ),

    'cruises' => array(
        array(
            'slug' => 'du-thuyen-lan-ha-2-ngay',
            'title' => 'Du thuyền Lan Hạ 5* — 2 ngày 1 đêm giữa vịnh hoang sơ',
            'typeSlug' => 'du-thuyen-lan-ha', 'typeName' => 'Du thuyền Lan Hạ',
            'tourCode' => 'CR2D-01', 'duration' => '2 ngày 1 đêm', 'days' => 2,
            'rating' => 5.0, 'reviewCount' => 96, 'badge' => 'Ưu đãi đặc biệt',
            'styles' => array('balanced', 'honeymoon'),
            'quote' => array('text' => 'Cabin ban công nhìn thẳng ra vịnh, nước trong vắt — chưa từng thấy vịnh nào như vậy.', 'author' => 'Chị Bích Ngọc'),
            'places' => array('Bến Bèo', 'Vịnh Lan Hạ', 'Hang Luồn', 'Bãi Ba Trái Đào'),
            'start' => 'Cảng Bến Bèo, Cát Bà', 'end' => 'Cảng Bến Bèo, Cát Bà',
            'departurePort' => 'Cảng Bến Bèo', 'boatClass' => 'Luxury 5*', 'nightsOnBoard' => 1,
            'cabinTypes' => array(
                array('name' => 'Deluxe Balcony', 'capacity' => 2, 'note' => 'Ban công riêng, 26m²'),
                array('name' => 'Family Suite', 'capacity' => 4, 'note' => 'Cửa sổ toàn cảnh, 38m²'),
            ),
            'highlightsIntro' => 'Một đêm trọn vẹn giữa vịnh hoang sơ nhất miền Bắc: kayak, lặn ngắm san hô và tiệc hoàng hôn trên boong.',
            'highlights' => array('Cabin ban công riêng hướng vịnh', 'Kayak hang Luồn & bãi Ba Trái Đào', 'Lặn ngắm san hô buổi sáng', 'Tiệc BBQ hoàng hôn trên boong'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Lên tàu — khám phá vịnh', 'meals' => 'Trưa; Tối',
                    'transport' => array('cruise', 'kayak'), 'overnight' => 'Trên du thuyền',
                    'content' => 'Đón khách tại bến Bèo, ăn trưa hải sản, chiều kayak hang Luồn, tối tiệc BBQ và câu mực đêm.'),
                array('day' => 2, 'title' => 'Bình minh vịnh — trả khách', 'meals' => 'Sáng; Trưa nhẹ',
                    'transport' => array('cruise'), 'overnight' => NULL,
                    'content' => 'Thái cực quyền đón bình minh, lặn ngắm san hô, brunch và về lại bến Bèo.'),
            ),
            'inclusions' => array('Cabin theo hạng chọn', 'Toàn bộ bữa ăn trên tàu', 'Kayak, thiết bị lặn ngắm san hô cơ bản', 'Vé thắng cảnh vịnh'),
            'exclusions' => array('Xe đưa đón Hà Nội/Hải Phòng (đặt thêm)', 'Đồ uống', 'Spa trên tàu'),
            'notes' => array('Nhận phòng 12:00, trả phòng 10:30 hôm sau.'),
            'faqs' => array(array('q' => 'Có xe đưa đón từ Hà Nội không?', 'a' => 'Có, limousine khứ hồi Hà Nội — Cát Bà (gộp vé phà) với phụ phí nhỏ, đặt cùng lúc với vé du thuyền.')),
            'galleryCount' => 5, 'priceFrom' => 4200000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'du-thuyen-lan-ha-3-ngay',
            'title' => 'Du thuyền Lan Hạ 3 ngày — Vịnh xanh vắng dấu chân người',
            'typeSlug' => 'du-thuyen-lan-ha', 'typeName' => 'Du thuyền Lan Hạ',
            'tourCode' => 'CR3D-02', 'duration' => '3 ngày 2 đêm', 'days' => 3,
            'rating' => 4.9, 'reviewCount' => 52, 'badge' => NULL,
            'styles' => array('nature-park', 'small-group'),
            'quote' => array('text' => 'Yên tĩnh, ít tàu qua lại — đúng nơi để trốn khỏi đám đông.', 'author' => 'Anh Đức Huy'),
            'places' => array('Bến Bèo', 'Vịnh Lan Hạ', 'Đảo Cát Bà', 'Hang Sáng Tối'),
            'start' => 'Cảng Bến Bèo, Cát Bà', 'end' => 'Cảng Bến Bèo, Cát Bà',
            'departurePort' => 'Cảng Bến Bèo', 'boatClass' => 'Boutique 4*', 'nightsOnBoard' => 2,
            'cabinTypes' => array(
                array('name' => 'Ocean View', 'capacity' => 2, 'note' => 'Cửa sổ lớn, 22m²'),
                array('name' => 'Balcony Suite', 'capacity' => 3, 'note' => 'Ban công gỗ, 32m²'),
            ),
            'highlightsIntro' => 'Ba ngày len lỏi giữa các đảo đá vôi ít người biết, kết hợp trekking nhẹ trên đảo và chèo kayak hang Sáng Tối.',
            'highlights' => array('Bãi tắm hoang sơ ít khách', 'Trekking nhẹ trên đảo nhỏ trong vịnh', 'Chèo kayak hang Sáng Tối', 'Ngủ 2 đêm trên tàu — không đổi cabin'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Lên tàu tại Cát Bà', 'meals' => 'Trưa; Tối',
                    'transport' => array('cruise'), 'overnight' => 'Trên du thuyền',
                    'content' => 'Hải trình qua vịnh Lan Hạ, tắm biển tại bãi hoang sơ đầu tiên.'),
                array('day' => 2, 'title' => 'Trekking đảo nhỏ — Hang Sáng Tối', 'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array('trekking', 'kayak'), 'overnight' => 'Trên du thuyền',
                    'content' => 'Trekking nhẹ trên một đảo nhỏ trong vịnh, chiều kayak hang Sáng Tối.'),
                array('day' => 3, 'title' => 'Bình minh — trả khách', 'meals' => 'Sáng',
                    'transport' => array('cruise'), 'overnight' => NULL,
                    'content' => 'Ngắm bình minh, brunch, về lại bến Bèo.'),
            ),
            'inclusions' => array('Cabin 2 đêm', 'Bữa ăn trên tàu', 'Kayak, thiết bị trekking cơ bản', 'Vé vịnh'),
            'exclusions' => array('Đồ uống', 'Tip'),
            'notes' => array('Phù hợp khách đã từng đi tour ngày, muốn trải nghiệm chậm hơn.'),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 6100000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'tour-ngay-vinh-cat-ba-cai-beo-dao-khi',
            'title' => 'Tour ngày vịnh Cát Bà — Cái Bèo — Đảo Khỉ',
            'typeSlug' => 'tour-ngay-vinh-cat-ba', 'typeName' => 'Tour ngày vịnh Cát Bà',
            'tourCode' => 'CR1D-03', 'duration' => '1 ngày', 'days' => 1,
            'rating' => 4.7, 'reviewCount' => 88, 'badge' => NULL,
            'styles' => array('day-trip', 'floating-village-culture', 'beach'),
            'quote' => array('text' => 'Không cần ngủ đêm mà vẫn thấy được cả làng chài và Đảo Khỉ trong một ngày.', 'author' => 'Nhóm bạn Hà Nội'),
            'places' => array('Bến Bèo', 'Làng chài Cái Bèo', 'Đảo Khỉ'),
            'start' => 'Cảng Bến Bèo, Cát Bà', 'end' => 'Cảng Bến Bèo, Cát Bà',
            'departurePort' => 'Cảng Bến Bèo', 'boatClass' => 'Tàu gỗ mui che', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => 'Tour ngày gọn nhẹ, phù hợp khách có thời gian ngắn nhưng vẫn muốn thấy trọn vịnh Cát Bà.',
            'highlights' => array('Tham quan làng chài Cái Bèo', 'Tắm biển và chơi với khỉ hoang tại Đảo Khỉ', 'Ăn trưa hải sản trên tàu'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Cái Bèo — Đảo Khỉ trong ngày', 'meals' => 'Trưa',
                    'transport' => array('boat'), 'overnight' => NULL,
                    'content' => 'Khởi hành 08:00 từ bến Bèo, tham quan làng chài Cái Bèo, ăn trưa trên tàu, chiều ghé Đảo Khỉ trước khi về bến lúc 15:30.'),
            ),
            'inclusions' => array('Tàu tham quan cả ngày', 'Bữa trưa', 'Vé vào Đảo Khỉ'),
            'exclusions' => array('Đồ uống', 'Xe đón tại khách sạn'),
            'notes' => array('Không có cabin nghỉ — đây là tour ngày, không ngủ đêm trên tàu.'),
            'faqs' => array(),
            'galleryCount' => 3, 'priceFrom' => 480000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'thuyen-viet-hai-ao-ech-kayak',
            'title' => 'Thuyền Việt Hải — Ao Ếch kết hợp kayak',
            'typeSlug' => 'thuyen-viet-hai-ao-ech', 'typeName' => 'Thuyền Việt Hải — Ao Ếch',
            'tourCode' => 'CR1D-04', 'duration' => '1 ngày', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 29, 'badge' => NULL,
            'styles' => array('nature-park', 'kayak-adventure', 'small-group'),
            'quote' => array('text' => 'Thuyền chèo tay qua rừng ngập nước, im lặng đến mức nghe được cả tiếng chim.', 'author' => 'Chị Lan Chi'),
            'places' => array('Bến Bèo', 'Việt Hải', 'Ao Ếch'),
            'start' => 'Cảng Bến Bèo, Cát Bà', 'end' => 'Cảng Bến Bèo, Cát Bà',
            'departurePort' => 'Cảng Bến Bèo', 'boatClass' => 'Thuyền nan chèo tay + tàu gỗ', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => 'Kết hợp tàu và kayak/thuyền chèo tay để tiếp cận làng Việt Hải và Ao Ếch từ hướng biển — khác hẳn cung trekking đường bộ.',
            'highlights' => array('Thuyền chèo tay qua vùng nước lặng', 'Ao Ếch — hồ nước ngọt giữa rừng', 'Ghé làng Việt Hải ăn trưa'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Việt Hải — Ao Ếch bằng đường thuỷ', 'meals' => 'Trưa',
                    'transport' => array('boat', 'kayak', 'trekking'), 'overnight' => NULL,
                    'content' => 'Tàu từ bến Bèo vào gần Việt Hải, chuyển sang kayak/thuyền nan tới Ao Ếch, đi bộ ngắn tới hồ. Ăn trưa tại làng rồi về lại bến Bèo bằng tàu.'),
            ),
            'inclusions' => array('Tàu + kayak/thuyền nan', 'Bữa trưa tại làng', 'Hướng dẫn viên'),
            'exclusions' => array('Đồ uống', 'Xe đón tại khách sạn'),
            'notes' => array('Phụ thuộc thuỷ triều — giờ khởi hành có thể thay đổi.'),
            'faqs' => array(),
            'galleryCount' => 3, 'priceFrom' => 590000.0, 'currency' => 'VND',
        ),
    ),

    'blog_categories' => array(
        array('slug' => 'trung-tam-cat-ba', 'name' => 'Trung tâm Cát Bà', 'zoneSlug' => 'trung-tam-cat-ba', 'count' => 7),
        array('slug' => 'vinh-lan-ha', 'name' => 'Vịnh Lan Hạ', 'zoneSlug' => 'vinh-lan-ha', 'count' => 6),
        array('slug' => 'vuon-quoc-gia', 'name' => 'Vườn quốc gia', 'zoneSlug' => 'vuon-quoc-gia', 'count' => 2),
        array('slug' => 'dao-khi', 'name' => 'Đảo Khỉ', 'zoneSlug' => 'dao-khi', 'count' => 2),
        array('slug' => 'lang-viet-hai', 'name' => 'Làng Việt Hải', 'zoneSlug' => 'lang-viet-hai', 'count' => 2),
        array('slug' => 'hai-phong', 'name' => 'Hải Phòng (cửa ngõ)', 'zoneSlug' => 'trung-tam-cat-ba', 'count' => 4),
        array('slug' => 'ket-hop-ha-long', 'name' => 'Kết hợp Hạ Long', 'zoneSlug' => 'ket-hop-ha-long', 'count' => 2),
    ),

    'popular_keywords' => array(
        'Kinh nghiệm du lịch Cát Bà', 'Cát Bà mùa nào đẹp nhất', 'Từ Hà Nội đi Cát Bà thế nào',
        'Vịnh Lan Hạ giá tour', 'Trekking Việt Hải Cát Bà', 'Đảo Khỉ Cát Bà đi thế nào',
        'Chi phí du lịch Cát Bà 3 ngày', 'Limousine Hà Nội Cát Bà', 'Lan Hạ hay Hạ Long',
        'Làng chài Cái Bèo', 'Tàu cao tốc Tuần Châu Cát Bà', 'Voọc Cát Bà trekking',
    ),

    'articles' => array(
        array(
            'slug' => 'cat-ba-mua-nao-dep-nhat',
            'title' => 'Cát Bà mùa nào đẹp nhất? Cẩm nang chọn thời điểm cho từng kiểu du khách',
            'zoneSlug' => 'trung-tam-cat-ba', 'zone' => 'Trung tâm đảo Cát Bà',
            'category' => 'Trung tâm Cát Bà', 'categorySlug' => 'trung-tam-cat-ba',
            'tags' => array('Mẹo du lịch', 'Chơi gì, xem gì?'),
            'author' => 'Lan Hương', 'publishedAt' => '10/06/2026', 'updatedAt' => '22/07/2026',
            'views' => 1042, 'rating' => 4.9, 'ratingCount' => 33,
            'excerpt' => 'Mùa hè tắm biển, mùa thu trekking khô ráo, mùa đông vắng khách nhưng lạnh — mỗi mùa Cát Bà mang một trải nghiệm khác nhau.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cát Bà có khí hậu nhiệt đới gió mùa, chia rõ hai mùa: mùa hè nóng ẩm thích hợp tắm biển, mùa đông khô lạnh phù hợp trekking và ngắm cảnh. Chọn đúng mùa quyết định phần lớn trải nghiệm của bạn trên đảo.'),
                array('type' => 'h2', 'id' => 'khi-hau-tong-quan', 'text' => 'I. Khí hậu Cát Bà theo mùa'),
                array('type' => 'p', 'text' => 'Từ tháng 5 đến tháng 8 là cao điểm biển — nước ấm, nắng nhiều nhưng cũng là mùa mưa bão, cần theo dõi dự báo trước khi đi tàu ra vịnh. Từ tháng 9 đến tháng 11 là thời điểm lý tưởng nhất: biển vẫn đẹp, trời khô, ít mưa. Tháng 12 đến tháng 2 lạnh và vắng khách, phù hợp ai muốn tận hưởng đảo yên tĩnh.'),
                array('type' => 'image', 'caption' => 'Vịnh Lan Hạ vào tháng 10 — biển lặng, trời trong.'),
                array('type' => 'h3', 'id' => 'mua-cao-diem-bien', 'text' => '1. Mùa cao điểm biển (tháng 5 – 8)'),
                array('type' => 'p', 'text' => 'Nước biển ấm, thích hợp bơi và các hoạt động dưới nước. Lưu ý mùa này thường có bão nhiệt đới — nên đặt tour có chính sách đổi ngày linh hoạt.'),
                array('type' => 'h3', 'id' => 'mua-vang', 'text' => '2. Mùa vàng (tháng 9 – 11)'),
                array('type' => 'p', 'text' => 'Thời điểm được dân đi biển sành sỏi chọn nhất: ít mưa, biển vẫn ấm, trekking vườn quốc gia dễ chịu vì không quá nóng.'),
                array('type' => 'links', 'title' => 'Xem thêm:', 'links' => array(
                    array('label' => 'Trekking Việt Hải xuyên vườn quốc gia', 'route' => array('tours.show', array('zone' => 'vuon-quoc-gia', 'slug' => 'trekking-viet-hai-vqg-1-ngay'))),
                    array('label' => 'Du thuyền Lan Hạ 2 ngày 1 đêm', 'route' => array('cruises.show', array('type' => 'du-thuyen-lan-ha', 'slug' => 'du-thuyen-lan-ha-2-ngay'))),
                )),
                array('type' => 'h2', 'id' => 'ket-luan', 'text' => 'II. Kết luận'),
                array('type' => 'p', 'text' => 'Không có mùa "xấu" ở Cát Bà — chỉ có mùa phù hợp với mục đích chuyến đi của bạn. Nếu còn phân vân, chuyên gia bản địa của chúng tôi sẵn sàng tư vấn miễn phí.'),
            ),
            'faqs' => array(
                array('q' => 'Cát Bà có bão không, mùa nào cần tránh?', 'a' => 'Mùa bão thường rơi vào tháng 7–9, nên theo dõi dự báo thời tiết trước khi đặt tour vịnh và ưu tiên đơn vị có chính sách đổi ngày miễn phí khi có bão.'),
                array('q' => 'Đi Cát Bà 2 ngày cuối tuần có kịp không?', 'a' => 'Kịp — limousine gộp phà từ Hà Nội chỉ 2.5–3 giờ. Tuy nhiên 3 ngày sẽ thoải mái hơn nếu muốn thêm cả trekking và du thuyền.'),
            ),
            'galleryCount' => 5,
        ),
        array(
            'slug' => 'tu-ha-noi-di-cat-ba-the-nao',
            'title' => 'Từ Hà Nội đi Cát Bà thế nào? Cẩm nang phương tiện chi tiết',
            'zoneSlug' => 'trung-tam-cat-ba', 'zone' => 'Trung tâm đảo Cát Bà',
            'category' => 'Trung tâm Cát Bà', 'categorySlug' => 'trung-tam-cat-ba',
            'tags' => array('Di chuyển thế nào?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '02/07/2026', 'updatedAt' => '18/07/2026',
            'views' => 1560, 'rating' => 4.8, 'ratingCount' => 47,
            'excerpt' => 'Limousine gộp phà, xe khách tự túc hay tàu cao tốc từ Hạ Long — so sánh chi tiết ba cách phổ biến nhất để ra đảo.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cát Bà không có đường sắt hay sân bay riêng, nên phương tiện chính là đường bộ kết hợp phà/tàu cao tốc. Có ba cách phổ biến để đến đảo từ Hà Nội.'),
                array('type' => 'h2', 'id' => 'limousine-gop-pha', 'text' => 'I. Limousine gộp vé phà (phổ biến nhất)'),
                array('type' => 'p', 'text' => 'Xe limousine 9 chỗ đón tận khách sạn ở Hà Nội, chạy cao tốc Hà Nội — Hải Phòng, qua phà Đình Vũ hoặc phà Đồng Bài — Cái Viềng để sang đảo. Tổng thời gian khoảng 2.5–3 giờ, giá vé đã gồm phí phà.'),
                array('type' => 'h2', 'id' => 'xe-khach-tu-tuc', 'text' => 'II. Xe khách tự túc'),
                array('type' => 'p', 'text' => 'Xe khách thường từ bến xe Hà Nội tới bến xe Cát Bà, giá rẻ hơn nhưng không đón tận nơi và cần tự nối chuyến phà.'),
                array('type' => 'h2', 'id' => 'tau-cao-toc-ha-long', 'text' => 'III. Tàu cao tốc từ Tuần Châu (Hạ Long)'),
                array('type' => 'p', 'text' => 'Nếu đang ở Hạ Long, tàu cao tốc từ Tuần Châu sang Cát Bà chỉ mất 45–60 phút, phù hợp ai muốn kết hợp cả hai vịnh.'),
            ),
            'faqs' => array(
                array('q' => 'Có cần đặt vé phà trước không?', 'a' => 'Với limousine gộp vé, đơn vị vận hành đã đặt sẵn suất phà; nếu tự lái xe riêng vào mùa cao điểm nên đến sớm vì có thể phải chờ chuyến phà tiếp theo.'),
            ),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'an-gi-o-cat-ba',
            'title' => 'Ăn gì ở Cát Bà? Bản đồ hải sản từ chợ đến nhà hàng',
            'zoneSlug' => 'trung-tam-cat-ba', 'zone' => 'Trung tâm đảo Cát Bà',
            'category' => 'Trung tâm Cát Bà', 'categorySlug' => 'trung-tam-cat-ba',
            'tags' => array('Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '15/06/2026', 'updatedAt' => '20/07/2026',
            'views' => 780, 'rating' => 4.7, 'ratingCount' => 21,
            'excerpt' => 'Tu hài, sam biển, cá song hấp — mua tận chợ hải sản đêm hay ăn tại nhà hàng ven bến, đâu cũng có cái hay riêng.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cát Bà là thiên đường hải sản tươi với giá hợp lý hơn nhiều so với các điểm du lịch biển lớn. Dưới đây là những món và địa điểm không nên bỏ qua.'),
                array('type' => 'h2', 'id' => 'cho-hai-san-dem', 'text' => 'I. Chợ hải sản đêm bến Bèo'),
                array('type' => 'p', 'text' => 'Từ 18h, khu chợ hải sản gần bến Bèo bày bán tu hài, sam biển, ghẹ, cá song còn sống — có thể mua rồi mang sang nhà hàng bên cạnh chế biến với phí nấu nhỏ.'),
                array('type' => 'h2', 'id' => 'mon-dac-trung', 'text' => 'II. Món đặc trưng nên thử'),
                array('type' => 'ul', 'items' => array('Tu hài nướng mỡ hành', 'Sam biển xào xả ớt', 'Bún cá cay Hải Phòng biến thể đảo', 'Ốc bể hấp sả')),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'trekking-viet-hai-kinh-nghiem',
            'title' => 'Trekking Vườn quốc gia Cát Bà — Việt Hải: kinh nghiệm chi tiết',
            'zoneSlug' => 'vuon-quoc-gia', 'zone' => 'Vườn quốc gia Cát Bà',
            'category' => 'Vườn quốc gia', 'categorySlug' => 'vuon-quoc-gia',
            'tags' => array('Chơi gì, xem gì?', 'Mẹo du lịch'),
            'author' => 'Lan Hương', 'publishedAt' => '20/06/2026', 'updatedAt' => '05/07/2026',
            'views' => 934, 'rating' => 4.9, 'ratingCount' => 29,
            'excerpt' => 'Đường rừng 10km, hồ Ao Ếch giữa đường và một bữa trưa ở làng không xe máy — trải nghiệm trekking đáng nhớ nhất Cát Bà.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cung trekking Việt Hải là hoạt động được đánh giá cao nhất trên các nền tảng review về Cát Bà — không quá khó nhưng đủ để cảm nhận rừng nguyên sinh thật sự.'),
                array('type' => 'h2', 'id' => 'chuan-bi', 'text' => 'I. Chuẩn bị gì trước khi đi'),
                array('type' => 'ul', 'items' => array('Giày trekking chống trượt', 'Nước uống tối thiểu 1.5 lít', 'Áo mưa mỏng vào mùa hè', 'Kem chống muỗi cho đoạn qua Ao Ếch')),
                array('type' => 'h2', 'id' => 'gap-gi-tren-duong', 'text' => 'II. Có thể gặp gì trên đường'),
                array('type' => 'p', 'text' => 'Cơ hội (không đảm bảo) gặp voọc Cát Bà — loài đặc hữu chỉ còn vài chục cá thể trong tự nhiên, sinh sống trên các vách núi đá vôi quanh vườn quốc gia.'),
            ),
            'faqs' => array(
                array('q' => 'Trẻ em có đi được cung trekking này không?', 'a' => 'Trẻ trên 8 tuổi có thể lực tốt có thể tham gia; có phương án đi thuyền một chiều để giảm quãng đường đi bộ.'),
            ),
            'galleryCount' => 5,
        ),
        array(
            'slug' => 'dao-khi-cat-ba-di-the-nao',
            'title' => 'Đảo Khỉ Cát Bà: đi thế nào, chơi gì?',
            'zoneSlug' => 'dao-khi', 'zone' => 'Đảo Khỉ (Cát Dứa)',
            'category' => 'Đảo Khỉ', 'categorySlug' => 'dao-khi',
            'tags' => array('Chơi gì, xem gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '25/06/2026', 'updatedAt' => '12/07/2026',
            'views' => 1120, 'rating' => 4.8, 'ratingCount' => 38,
            'excerpt' => 'Chỉ 20 phút tàu từ bến Bèo — bãi cát trắng, nước trong và đàn khỉ hoang dã táo bạo hơn bạn nghĩ.',
            'content' => array(
                array('type' => 'p', 'text' => 'Đảo Khỉ (tên chính thức Cát Dứa) là điểm ghé gần như bắt buộc trong mọi tour ngày ở Cát Bà — nhờ vị trí gần bến và bãi biển đẹp.'),
                array('type' => 'h2', 'id' => 'di-the-nao', 'text' => 'I. Đi Đảo Khỉ thế nào'),
                array('type' => 'p', 'text' => 'Từ bến Bèo, tàu ra đảo chỉ mất khoảng 20 phút. Hầu hết tour ngày vịnh Cát Bà đều có ghé điểm này, hoặc có thể đặt tàu riêng.'),
                array('type' => 'h2', 'id' => 'luu-y-voi-khi', 'text' => 'II. Lưu ý khi gặp khỉ hoang dã'),
                array('type' => 'ul', 'items' => array('Không cầm đồ ăn hở trên tay — khỉ có thể giật', 'Không cho khỉ ăn đồ ngọt hoặc đồ ăn của người', 'Giữ khoảng cách, không chạm vào khỉ con')),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'chi-phi-du-lich-cat-ba-3-ngay',
            'title' => 'Chi phí du lịch Cát Bà 3 ngày 2 đêm hết bao nhiêu? Bảng dự trù 2026',
            'zoneSlug' => 'trung-tam-cat-ba', 'zone' => 'Trung tâm đảo Cát Bà',
            'category' => 'Trung tâm Cát Bà', 'categorySlug' => 'trung-tam-cat-ba',
            'tags' => array('Mẹo du lịch', 'Chọn tour nào?'),
            'author' => 'Lan Hương', 'publishedAt' => '01/06/2026', 'updatedAt' => '15/06/2026',
            'views' => 1780, 'rating' => 4.9, 'ratingCount' => 55,
            'excerpt' => 'So sánh ba mức ngân sách — tiết kiệm, tầm trung, cao cấp — cho hành trình 3 ngày phổ biến nhất tại Cát Bà.',
            'content' => array(
                array('type' => 'p', 'text' => 'Câu hỏi phổ biến nhất chúng tôi nhận được: "3 ngày ở Cát Bà tốn bao nhiêu?". Bảng dự trù dưới đây cho con số sát thực tế theo ba phong cách đi.'),
                array('type' => 'h2', 'id' => 'ba-muc-ngan-sach', 'text' => 'I. Ba mức ngân sách phổ biến'),
                array('type' => 'ul', 'items' => array(
                    'Tiết kiệm: 1.2 – 1.8 triệu/người (homestay, xe khách tự túc, ăn quán bình dân)',
                    'Tầm trung: 2.5 – 3.5 triệu/người (khách sạn 3-4*, du thuyền 1 đêm, limousine gộp phà)',
                    'Cao cấp: từ 5 triệu/người (resort 5*, du thuyền hạng sang, xe riêng, HDV riêng)',
                )),
            ),
            'faqs' => array(
                array('q' => 'Đặt tour trọn gói có rẻ hơn tự túc?', 'a' => 'Với nhóm 2 khách trở lên, tour trọn gói thường tối ưu hơn 10–15% nhờ giá hợp tác trực tiếp với chủ tàu và khách sạn.'),
            ),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'vinh-lan-ha-kham-pha-the-nao',
            'title' => 'Vịnh Lan Hạ: chơi gì, tour nào phù hợp từng kiểu khách?',
            'zoneSlug' => 'vinh-lan-ha', 'zone' => 'Vịnh Lan Hạ',
            'category' => 'Vịnh Lan Hạ', 'categorySlug' => 'vinh-lan-ha',
            'tags' => array('Chơi gì, xem gì?', 'Chọn tour nào?'),
            'author' => 'Phạm Thị Liên', 'publishedAt' => '08/07/2026', 'updatedAt' => '25/07/2026',
            'views' => 892, 'rating' => 4.9, 'ratingCount' => 31,
            'excerpt' => 'Tour ngày, kayak private, ngủ đêm trên du thuyền hay hoàng hôn trên tàu — bản đồ lựa chọn vịnh Lan Hạ theo thời gian và nhịp đi.',
            'content' => array(
                array('type' => 'p', 'text' => 'Lan Hạ là lý do chính khiến nhiều khách chọn Cát Bà thay vì chỉ ghé Hạ Long: ít tàu hơn, nước trong và vẫn giữ được cảm giác hoang sơ. Dưới đây là cách chọn hoạt động theo profile du khách.'),
                array('type' => 'h2', 'id' => 'khach-ngan-ngay', 'text' => 'I. Chỉ có 1 ngày trên đảo'),
                array('type' => 'p', 'text' => 'Tour ngày tàu gỗ + kayak + Đảo Khỉ là combo phổ biến nhất. Nếu muốn yên hơn, chọn tour hoàng hôn chiều–tối hoặc kayak private cho nhóm 2–4 người.'),
                array('type' => 'h2', 'id' => 'khach-ngu-dem', 'text' => 'II. Muốn ngủ trên vịnh'),
                array('type' => 'p', 'text' => 'Du thuyền 2 ngày 1 đêm hoặc cabin đặt riêng là trải nghiệm đặc trưng. Bungalow nổi phù hợp cặp đôi muốn riêng tư tuyệt đối — số lượng hạn chế, nên đặt trước.'),
                array('type' => 'links', 'title' => 'Gợi ý tour:', 'links' => array(
                    array('label' => 'Vịnh Lan Hạ 1 ngày — Kayak & Đảo Khỉ', 'route' => array('tours.show', array('zone' => 'vinh-lan-ha', 'slug' => 'vinh-lan-ha-kayak-dao-khi-1-ngay'))),
                    array('label' => 'Du thuyền Lan Hạ 2 ngày 1 đêm', 'route' => array('cruises.show', array('type' => 'du-thuyen-lan-ha', 'slug' => 'du-thuyen-lan-ha-2-ngay'))),
                )),
            ),
            'faqs' => array(array('q' => 'Lan Hạ có đông như Hạ Long không?', 'a' => 'Thường ít đông hơn đáng kể, đặc biệt ngoài mùa hè. Tuy nhiên tour ngày vẫn có giờ cao điểm — đi sớm hoặc chọn tàu nhỏ/private sẽ thoải mái hơn.')),
            'galleryCount' => 5,
        ),
        array(
            'slug' => 'lang-chai-cai-beo-kinh-nghiem',
            'title' => 'Làng chài Cái Bèo: thuyền nan, hang Trung Trang và câu mực đêm',
            'zoneSlug' => 'cai-beo', 'zone' => 'Làng chài Cái Bèo',
            'category' => 'Trung tâm Cát Bà', 'categorySlug' => 'trung-tam-cat-ba',
            'tags' => array('Chơi gì, xem gì?', 'Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '12/07/2026', 'updatedAt' => '28/07/2026',
            'views' => 645, 'rating' => 4.8, 'ratingCount' => 19,
            'excerpt' => 'Làng chài cổ gần trung tâm nhất — ghép nửa ngày với tour vịnh hoặc tối câu mực cho trải nghiệm địa phương thật.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cái Bèo là làng chài nổi lâu đời nhất vịnh Bắc Bộ, gắn với di chỉ khảo cổ Cái Bèo hơn 6.000 năm. Khác với tour vịnh “chạy điểm”, đây là trải nghiệm chậm và gần đời sống ngư dân.'),
                array('type' => 'h2', 'id' => 'tour-nua-ngay', 'text' => 'I. Tour nửa ngày: thuyền nan + hang Trung Trang'),
                array('type' => 'p', 'text' => 'Buổi chiều 3–4 giờ, phù hợp ghép sau khi về từ trekking hoặc trước giờ ăn tối chợ hải sản.'),
                array('type' => 'h2', 'id' => 'cau-muc-dem', 'text' => 'II. Buổi tối: câu mực trên vịnh'),
                array('type' => 'p', 'text' => 'Ra vịnh khoảng 19:00–22:00 với ngư dân địa phương — phụ thuộc thời tiết và quy định cấm tàu đêm.'),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'lan-ha-hay-ha-long',
            'title' => 'Lan Hạ hay Hạ Long — nên chọn vịnh nào, có thể đi cả hai?',
            'zoneSlug' => 'ket-hop-ha-long', 'zone' => 'Kết hợp Hạ Long',
            'category' => 'Kết hợp Hạ Long', 'categorySlug' => 'ket-hop-ha-long',
            'tags' => array('Chọn tour nào?', 'Mẹo du lịch'),
            'author' => 'Lan Hương', 'publishedAt' => '18/07/2026', 'updatedAt' => '02/08/2026',
            'views' => 1320, 'rating' => 4.9, 'ratingCount' => 44,
            'excerpt' => 'So sánh thực tế: độ đông, cảnh quan, chi phí — và tuyến tàu cao tốc Tuần Châu — Cát Bà để đi cả hai vịnh trong một chuyến.',
            'content' => array(
                array('type' => 'p', 'text' => 'Hai vịnh cùng hệ thống đá vôi nhưng trải nghiệm khác nhau: Hạ Long nổi tiếng và tiện hạ tầng; Lan Hạ yên hơn, gắn với đảo Cát Bà và trekking.'),
                array('type' => 'h2', 'id' => 'so-sanh', 'text' => 'I. Bảng so sánh nhanh'),
                array('type' => 'ul', 'items' => array(
                    'Lan Hạ: ít khách hơn, phù hợp kayak/snorkel/ngủ đêm, gần vườn quốc gia Cát Bà',
                    'Hạ Long: nhiều lựa chọn du thuyền, dễ kết hợp Hà Nội — Hạ Long classic, hang Sửng Sốt nổi tiếng',
                    'Kết hợp: tàu cao tốc xuyên vịnh ~45–60 phút, tour 2 ngày 1 đêm hoặc 3N2Đ của Hi Cát Bà',
                )),
                array('type' => 'h2', 'id' => 'ket-luan', 'text' => 'II. Kết luận'),
                array('type' => 'p', 'text' => 'Lần đầu đến miền Bắc và muốn “check-in Hạ Long” — cân nhắc combo. Nếu ưu tiên thiên nhiên và trekking — ở lại Cát Bà, dành thời gian Lan Hạ sâu hơn.'),
            ),
            'faqs' => array(array('q' => 'Tàu Tuần Châu — Cát Bà chạy quanh năm không?', 'a' => 'Phụ thuộc biển động và quy định hàng hải — thường ổn định mùa khô; mùa bão cần theo dõi và chọn đơn vị có chính sách đổi ngày.')),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'hai-phong-cua-ngo-cat-ba',
            'title' => 'Hải Phòng — cửa ngõ ra Cát Bà: sân bay, bến tàu và mẹo nối chuyến',
            'zoneSlug' => 'trung-tam-cat-ba', 'zone' => 'Trung tâm đảo Cát Bà',
            'category' => 'Hải Phòng (cửa ngõ)', 'categorySlug' => 'hai-phong',
            'tags' => array('Di chuyển thế nào?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '22/07/2026', 'updatedAt' => '05/08/2026',
            'views' => 720, 'rating' => 4.7, 'ratingCount' => 22,
            'excerpt' => 'Bay vào Cát Bi, tàu cao tốc Bến Bính hay phà Đồng Bài — hướng dẫn nối chuyến gọn cho khách tự túc và khách đặt qua Hi Cát Bà.',
            'content' => array(
                array('type' => 'p', 'text' => 'Hải Phòng là cửa ngõ đất liền gần nhất với Cát Bà. Hiểu rõ ba điểm then chốt — sân bay Cát Bi, bến Bến Bính và phà Đồng Bài — sẽ giúp bạn không lỡ chuyến tàu.'),
                array('type' => 'h2', 'id' => 'cat-bi', 'text' => 'I. Sân bay Cát Bi → ra đảo'),
                array('type' => 'p', 'text' => 'Từ sân bay ~30 phút tới Bến Bính (tàu cao tốc) hoặc ~45 phút tới Đồng Bài (nếu tự lái qua cầu Tân Vũ — Lạch Huyện). Nên đặt xe + vé tàu gộp để canh giờ.'),
                array('type' => 'h2', 'id' => 'ben-binh', 'text' => 'II. Tàu cao tốc Bến Bính — Bến Bèo'),
                array('type' => 'p', 'text' => 'Lựa chọn nhanh nhất cho khách không mang xe — ~45 phút, nhiều chuyến trong ngày.'),
            ),
            'faqs' => array(),
            'galleryCount' => 3,
        ),
    ),

    'testimonials' => array(
        array('name' => 'Nguyễn Trọng Nghĩa', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => 'Ba ngày ở Cát Bà mà cảm giác như đi được ba nơi khác nhau — vịnh, rừng và biển.', 'photos' => 6, 'trip' => 'Cát Bà 3 ngày 2 đêm', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Sarah Mitchell', 'country' => 'Úc', 'flag' => '🇦🇺', 'rating' => 5.0,
            'quote' => 'Lan Ha Bay is wilder and quieter than Halong — exactly what we wanted.', 'photos' => 9, 'trip' => 'Du thuyền Lan Hạ 2 ngày', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Hải Yến', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.9,
            'quote' => 'Trekking Việt Hải là trải nghiệm đáng nhớ nhất năm nay — rừng mát, làng yên bình.', 'photos' => 5, 'trip' => 'Trekking Việt Hải 1 ngày', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'David Chen', 'country' => 'Úc', 'flag' => '🇦🇺', 'rating' => 5.0,
            'quote' => 'Kết hợp Cát Bà và Hạ Long trong 2 ngày là quyết định đúng đắn nhất của chuyến đi.', 'photos' => 7, 'trip' => 'Cát Bà — Hạ Long kết hợp', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Bích Ngọc', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => 'Cabin ban công nhìn thẳng ra vịnh, nước trong đến mức thấy đáy.', 'photos' => 4, 'trip' => 'Du thuyền Lan Hạ 2 ngày', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Yuna Park', 'country' => 'Hàn Quốc', 'flag' => '🇰🇷', 'rating' => 4.8,
            'quote' => '가이드가 정말 친절했고 원숭이 섬과 카약 투어 모두 완벽했어요.', 'photos' => 8, 'trip' => 'Vịnh Lan Hạ 1 ngày', 'avatar' => NULL, 'photoUrls' => array()),
    ),

    'team' => array(
        array(
            'slug' => 'nguyen-van-hai', 'name' => 'Nguyễn Văn Hải', 'role' => 'Giám đốc điều hành',
            'bio' => 'Sinh ra tại Cát Bà, hơn 12 năm dẫn đoàn khắp vịnh Lan Hạ và vườn quốc gia, Hải tin rằng du lịch bền vững là cách duy nhất để đảo còn đẹp cho thế hệ sau...',
            'phone' => '+84 225 388 7777', 'email' => 'hai.nguyen@hicatba.dev', 'area' => 'Cát Bà, Hải Phòng',
            'years_experience' => 12, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 2400, 'stat_tours' => 520, 'stat_awards' => 3, 'is_verified' => true,
            'bio_html' => '<p>Sinh ra và lớn lên tại Cát Bà, Nguyễn Văn Hải có hơn 12 năm kinh nghiệm dẫn đoàn khắp vịnh Lan Hạ và vườn quốc gia.</p><p>Anh xây dựng Hi Cát Bà với triết lý du lịch bền vững — ưu tiên tàu nhỏ, hướng dẫn viên địa phương và bảo vệ hệ sinh thái vịnh.</p>',
            'bio_html_en' => '<p>Born and raised in Cat Ba, Van Hai has 12 years guiding groups across Lan Ha Bay and the national park, building the agency around sustainable, small-group travel.</p>',
            'name_en' => 'Nguyen Van Hai', 'role_en' => 'Chief Executive Officer',
            'short_bio_en' => 'Born on Cat Ba island — over 12 years guiding Lan Ha Bay and the national park.',
            'achievements' => array('Xây dựng mạng lưới hơn 20 chủ tàu địa phương hợp tác trực tiếp', 'Tham gia dự án bảo tồn voọc Cát Bà cùng vườn quốc gia'),
            'skills' => array(
                array('skill' => 'Vận hành tour vịnh & trekking', 'percent' => 96),
                array('skill' => 'Quan hệ đối tác tàu/thuyền địa phương', 'percent' => 93),
                array('skill' => 'Du lịch bền vững', 'percent' => 90),
            ),
            'experiences' => array(array('title' => 'Giám đốc điều hành', 'company' => 'Hi Cát Bà', 'items' => array('Điều hành chiến lược sản phẩm tour đảo & du thuyền vịnh Lan Hạ'))),
            'degrees' => array(array('title' => 'Cử nhân Quản trị Du lịch', 'school' => 'Đại học Hải Phòng', 'items' => array())),
        ),
        array(
            'slug' => 'pham-thi-lien', 'name' => 'Phạm Thị Liên', 'role' => 'Trưởng phòng thiết kế tour',
            'bio' => 'Liên đã đi bộ qua từng lối mòn của vườn quốc gia và biết chính xác đoạn nào nên trekking, đoạn nào nên đi thuyền...',
            'phone' => '+84 225 388 7788', 'email' => 'lien.pham@hicatba.dev', 'area' => 'Cát Bà & vườn quốc gia',
            'years_experience' => 9, 'languages' => array('Tiếng Việt', 'English', 'Français'),
            'stat_clients' => 1600, 'stat_tours' => 340, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Phạm Thị Liên thuộc từng lối mòn của vườn quốc gia Cát Bà và luôn biết cách cân bằng độ khó trekking với thời gian nghỉ ngơi trên vịnh.</p>',
            'bio_html_en' => '<p>Thi Lien knows every trail in Cat Ba National Park and balances trekking difficulty with rest time on the bay.</p>',
            'name_en' => 'Pham Thi Lien', 'role_en' => 'Head of Tour Design',
            'short_bio_en' => 'Knows every trail in the national park — designs itineraries that balance trekking and bay time.',
            'achievements' => array('Thiết kế cung trekking Việt Hải — Ao Ếch được đánh giá 4.9/5 trên Tripadvisor'),
            'skills' => array(array('skill' => 'Thiết kế lịch trình trekking', 'percent' => 95), array('skill' => 'Khảo sát thực địa vườn quốc gia', 'percent' => 92)),
            'experiences' => array(array('title' => 'Trưởng phòng thiết kế tour', 'company' => 'Hi Cát Bà', 'items' => array('Phụ trách toàn bộ sản phẩm trekking và tour ngày vịnh'))),
            'degrees' => array(array('title' => 'Cử nhân Địa lý Du lịch', 'school' => 'Đại học Khoa học Tự nhiên', 'items' => array())),
        ),
        array(
            'slug' => 'tran-quoc-vuong', 'name' => 'Trần Quốc Vượng', 'role' => 'Trưởng đội vận hành tàu & vịnh',
            'bio' => 'Xuất thân từ gia đình làm nghề chài lưới ở Cái Bèo, Vượng hiểu vịnh Lan Hạ như hiểu sân nhà mình...',
            'phone' => '+84 225 388 7799', 'email' => 'vuong.tran@hicatba.dev', 'area' => 'Vịnh Lan Hạ & bến Bèo',
            'years_experience' => 15, 'languages' => array('Tiếng Việt', 'English cơ bản'),
            'stat_clients' => 3100, 'stat_tours' => 680, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Xuất thân từ gia đình chài lưới ở Cái Bèo, Trần Quốc Vượng hiểu từng luồng nước và bãi tắm an toàn của vịnh Lan Hạ.</p>',
            'bio_html_en' => '<p>From a fishing family in Cai Beo, Quoc Vuong knows every current and safe swimming spot in Lan Ha Bay.</p>',
            'name_en' => 'Tran Quoc Vuong', 'role_en' => 'Head of Boat & Bay Operations',
            'short_bio_en' => 'From a Cai Beo fishing family — knows Lan Ha Bay like his own backyard.',
            'achievements' => array('Hơn 600 chuyến du thuyền/kayak vận hành an toàn không sự cố lớn'),
            'skills' => array(array('skill' => 'An toàn hàng hải & vịnh', 'percent' => 97), array('skill' => 'Điều hành đội tàu địa phương', 'percent' => 94)),
            'experiences' => array(array('title' => 'Trưởng đội vận hành tàu & vịnh', 'company' => 'Hi Cát Bà', 'items' => array('Phụ trách toàn bộ đội tàu, kayak và an toàn trên vịnh'))),
            'degrees' => array(array('title' => 'Chứng chỉ thuyền trưởng nội thuỷ', 'school' => 'Cục Đường thuỷ nội địa', 'items' => array())),
        ),
        array(
            'slug' => 'le-thu-huong', 'name' => 'Lê Thu Hương', 'role' => 'Chuyên gia tư vấn cao cấp',
            'bio' => 'Thành thạo ba ngoại ngữ, Hương là người bạn đồng hành tin cậy của khách quốc tế lần đầu đến Cát Bà...',
            'phone' => '+84 225 388 7711', 'email' => 'huong.le@hicatba.dev', 'area' => 'Cát Bà & Hải Phòng',
            'years_experience' => 8, 'languages' => array('Tiếng Việt', 'English', '한국어'),
            'stat_clients' => 1300, 'stat_tours' => 280, 'stat_awards' => 1, 'is_verified' => true,
            'bio_html' => '<p>Thành thạo tiếng Anh và tiếng Hàn, Lê Thu Hương là người bạn đồng hành tin cậy của khách quốc tế lần đầu đến Cát Bà.</p>',
            'bio_html_en' => '<p>Fluent in English and Korean, Thu Huong is a trusted companion for international guests visiting Cat Ba for the first time.</p>',
            'name_en' => 'Le Thu Huong', 'role_en' => 'Senior Travel Consultant',
            'short_bio_en' => 'Fluent in English and Korean — a trusted companion for first-time international visitors.',
            'achievements' => array('Đồng hành hơn 300 đoàn khách Hàn Quốc và phương Tây (2020–2026)'),
            'skills' => array(array('skill' => 'Tư vấn khách quốc tế', 'percent' => 95), array('skill' => 'Giao tiếp đa ngôn ngữ', 'percent' => 93)),
            'experiences' => array(array('title' => 'Chuyên gia tư vấn cao cấp', 'company' => 'Hi Cát Bà', 'items' => array('Phụ trách thị trường Hàn Quốc và khách nói tiếng Anh'))),
            'degrees' => array(array('title' => 'Cử nhân Ngôn ngữ Anh', 'school' => 'Đại học Hải Phòng', 'items' => array())),
        ),
    ),

    'videos' => array(
        array('title' => 'Một đêm trên du thuyền vịnh Lan Hạ', 'description' => 'Bình minh trên vịnh — góc nhìn từ boong tàu.', 'date' => '18/07/2026', 'duration' => '09:20', 'tag' => 'Vịnh Lan Hạ',
            'image' => 'https://i.ytimg.com/vi/CB0000000A1/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CB0000000A1?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CB0000000A1'),
        array('title' => 'Trekking Việt Hải xuyên vườn quốc gia — nhật ký bằng hình', 'description' => 'Rừng nguyên sinh, Ao Ếch và một bữa trưa ở làng.', 'date' => '05/07/2026', 'duration' => '11:05', 'tag' => 'Vườn quốc gia',
            'image' => 'https://i.ytimg.com/vi/CB0000000A2/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CB0000000A2?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CB0000000A2'),
        array('title' => 'Một ngày ở Đảo Khỉ Cát Bà', 'description' => 'Bãi cát trắng và đàn khỉ hoang dã táo bạo.', 'date' => '22/06/2026', 'duration' => '07:40', 'tag' => 'Đảo Khỉ',
            'image' => 'https://i.ytimg.com/vi/CB0000000A3/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CB0000000A3?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CB0000000A3'),
        array('title' => 'Cát Bà — Hạ Long kết hợp qua ống kính khách hàng', 'description' => 'Hai vịnh di sản trong một chuyến đi.', 'date' => '30/05/2026', 'duration' => '08:55', 'tag' => 'Kết hợp Hạ Long',
            'image' => 'https://i.ytimg.com/vi/CB0000000A4/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CB0000000A4?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CB0000000A4'),
    ),

    'gallery_albums' => array(
        array('title' => 'Gia đình Mitchell — Du thuyền Lan Hạ 2 ngày', 'photos' => 22, 'date' => '07/2026'),
        array('title' => 'Nhóm trekking Việt Hải — Ao Ếch', 'photos' => 27, 'date' => '06/2026'),
        array('title' => 'Đoàn khách Hàn Quốc — Vịnh Lan Hạ & Đảo Khỉ', 'photos' => 31, 'date' => '06/2026'),
        array('title' => 'Cát Bà — Hạ Long kết hợp, đoàn Úc', 'photos' => 19, 'date' => '05/2026'),
        array('title' => 'Bãi Cát Cò — kỷ niệm mùa hè', 'photos' => 15, 'date' => '05/2026'),
        array('title' => 'Làng chài Cái Bèo — chụp bằng drone', 'photos' => 12, 'date' => '04/2026'),
    ),

    'usps' => array(
        array('icon' => 'compass', 'sort' => 0,
            'vi' => array('title' => 'am hiểu đảo như dân bản địa', 'description' => 'Đội ngũ sinh ra và lớn lên tại Cát Bà, biết chính xác luồng nước an toàn, mùa trekking đẹp và bãi tắm ít người.'),




'en' => array('title' => 'local-island expertise', 'description' => 'Our team was born and raised on Cat Ba — they know the safe currents, best trekking seasons and quiet beaches.')),
        array('icon' => 'refund', 'sort' => 1,
            'vi' => array('title' => 'giá rõ ràng, minh bạch', 'description' => 'Báo giá trọn gói, không phí ẩn. Chính sách đổi ngày linh hoạt khi thời tiết xấu ảnh hưởng tàu/vịnh.'),




'en' => array('title' => 'clear, transparent pricing', 'description' => 'All-in pricing with no hidden fees. Flexible rescheduling when weather affects boats.')),
        array('icon' => 'boat', 'sort' => 2,
            'vi' => array('title' => 'tàu nhỏ, nhóm nhỏ', 'description' => 'Ưu tiên tàu và nhóm nhỏ để giảm tác động lên vịnh và mang lại trải nghiệm riêng tư hơn.'),




'en' => array('title' => 'small boats, small groups', 'description' => 'We favour small boats and small groups to protect the bay and give a more private experience.')),
        array('icon' => 'support', 'sort' => 3,
            'vi' => array('title' => 'hỗ trợ xuyên suốt', 'description' => 'Đội ngũ đồng hành trước, trong và sau chuyến đi — kể cả khi cần đổi lịch vì tàu tạm ngưng do thời tiết.'),




'en' => array('title' => 'seamless island support', 'description' => 'Dedicated support before, during and after your trip — including weather-related rescheduling.')),
    ),

    'offices' => array(
        array('city' => 'Cát Bà, Hải Phòng', 'address' => 'Số 12 đường 1/4, thị trấn Cát Bà', 'phone' => '+84 225 388 7777'),
        array('city' => 'Hải Phòng (cửa ngõ đất liền)', 'address' => '45 Điện Biên Phủ, Hồng Bàng', 'phone' => '+84 225 388 6666'),
        array('city' => 'Hà Nội (văn phòng đặt tour)', 'address' => 'Tầng 3, 20 Hàng Bài, Hoàn Kiếm', 'phone' => '+84 24 3999 5555'),
    ),

    'values' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is crafted with the care we give our own family')),
        array('vi' => array('name' => 'Am hiểu đảo', 'desc' => 'Sinh ra và lớn lên tại Cát Bà — hiểu từng bãi tắm, con đường'),



'en' => array('name' => 'Island expertise', 'desc' => 'Born and raised on Cat Ba — we know every beach and trail')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn trung thực, giá cả minh bạch'),



'en' => array('name' => 'Sincerity', 'desc' => 'Honest advice and transparent pricing')),
        array('vi' => array('name' => 'Trách nhiệm', 'desc' => 'Bảo vệ vịnh, rừng và cộng đồng làng chài địa phương'),



'en' => array('name' => 'Responsibility', 'desc' => 'Protecting the bay, the forest and local fishing communities')),
    ),
    'value_definitions' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is crafted with the care we give our own family')),
        array('vi' => array('name' => 'Am hiểu đảo', 'desc' => 'Sinh ra và lớn lên tại Cát Bà — hiểu từng bãi tắm, con đường'),



'en' => array('name' => 'Island expertise', 'desc' => 'Born and raised on Cat Ba — we know every beach and trail')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn trung thực, giá cả minh bạch'),



'en' => array('name' => 'Sincerity', 'desc' => 'Honest advice and transparent pricing')),
        array('vi' => array('name' => 'Trách nhiệm', 'desc' => 'Bảo vệ vịnh, rừng và cộng đồng làng chài địa phương'),



'en' => array('name' => 'Responsibility', 'desc' => 'Protecting the bay, the forest and local fishing communities')),
    ),

    'reasons' => array(
        array('vi' => array('title' => 'Hướng dẫn viên và thuyền trưởng bản địa', 'desc' => 'Đội ngũ sinh ra tại Cát Bà, hiểu từng luồng nước và lối mòn rừng.'),



'en' => array('title' => 'Local guides and boat captains', 'desc' => 'Our team grew up on Cat Ba — they know every current and forest trail.')),
        array('vi' => array('title' => 'Cam kết đổi lịch minh bạch khi thời tiết xấu', 'desc' => 'Chính sách đổi ngày/hoàn tiền rõ ràng khi tàu tạm ngưng do bão.'),



'en' => array('title' => 'Clear weather rescheduling policy', 'desc' => 'Transparent rebooking and refund terms when boats are suspended due to storms.')),
        array('vi' => array('title' => 'Làm việc trực tiếp với chủ tàu và homestay', 'desc' => 'Không qua trung gian — giá tốt hơn, hỗ trợ nhanh hơn.'),



'en' => array('title' => 'Direct partnerships with boat owners and homestays', 'desc' => 'No middlemen — better prices and faster support.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7 trong suốt hành trình', 'desc' => 'Hotline và Zalo trực người thật, phản hồi trong vòng 15 phút.'),



'en' => array('title' => '24/7 support throughout your trip', 'desc' => 'Real people on hotline and Zalo, responding within 15 minutes.')),
        array('vi' => array('title' => 'Du lịch có trách nhiệm với vịnh và rừng', 'desc' => 'Ưu tiên tàu nhỏ, hạn chế nhựa dùng một lần, đóng góp bảo tồn voọc Cát Bà.'),



'en' => array('title' => 'Responsible travel for the bay and forest', 'desc' => 'We favour small boats, minimise single-use plastic and support Cat Ba langur conservation.')),
    ),
    'reason_definitions' => array(
        array('vi' => array('title' => 'Hướng dẫn viên và thuyền trưởng bản địa', 'desc' => 'Đội ngũ sinh ra tại Cát Bà, hiểu từng luồng nước và lối mòn rừng.'),



'en' => array('title' => 'Local guides and boat captains', 'desc' => 'Our team grew up on Cat Ba — they know every current and forest trail.')),
        array('vi' => array('title' => 'Cam kết đổi lịch minh bạch khi thời tiết xấu', 'desc' => 'Chính sách đổi ngày/hoàn tiền rõ ràng khi tàu tạm ngưng do bão.'),



'en' => array('title' => 'Clear weather rescheduling policy', 'desc' => 'Transparent rebooking and refund terms when boats are suspended due to storms.')),
        array('vi' => array('title' => 'Làm việc trực tiếp với chủ tàu và homestay', 'desc' => 'Không qua trung gian — giá tốt hơn, hỗ trợ nhanh hơn.'),



'en' => array('title' => 'Direct partnerships with boat owners and homestays', 'desc' => 'No middlemen — better prices and faster support.')),
        array('vi' => array('title' => 'Hỗ trợ 24/7 trong suốt hành trình', 'desc' => 'Hotline và Zalo trực người thật, phản hồi trong vòng 15 phút.'),



'en' => array('title' => '24/7 support throughout your trip', 'desc' => 'Real people on hotline and Zalo, responding within 15 minutes.')),
        array('vi' => array('title' => 'Du lịch có trách nhiệm với vịnh và rừng', 'desc' => 'Ưu tiên tàu nhỏ, hạn chế nhựa dùng một lần, đóng góp bảo tồn voọc Cát Bà.'),



'en' => array('title' => 'Responsible travel for the bay and forest', 'desc' => 'We favour small boats, minimise single-use plastic and support Cat Ba langur conservation.')),
    ),

    'reference_persons' => array(
        array('name' => 'Ms. Yuna Park', 'country' => 'Hàn Quốc', 'email' => 'yuna@hicatba.example', 'phone' => '+82 10 1234 5678', 'skype' => 'yuna.park.travel', 'image' => NULL, 'imageSrcset' => NULL),
        array('name' => 'Mr. David Chen', 'country' => 'Úc', 'email' => 'david@hicatba.example', 'phone' => '+61 4 1234 5678', 'skype' => 'david.chen.au', 'image' => NULL, 'imageSrcset' => NULL),
        array('name' => 'Ms. Claire Dubois', 'country' => 'Pháp', 'email' => 'claire@hicatba.example', 'phone' => '+33 6 98 76 54 32', 'skype' => 'claire.dubois.travel', 'image' => NULL, 'imageSrcset' => NULL),
    ),

    'about_page' => array(
        'vi' => array(
            'seo_title' => 'Về chúng tôi — Hi Cát Bà, kết nối du khách với đảo Cát Bà toàn diện',
            'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ Hi Cát Bà — thiết kế hành trình và kết nối dịch vụ trên đảo Cát Bà cho du khách trong và ngoài nước.',
            'page_title' => 'Về chúng tôi',
            'page_subtitle' => 'Hành trình chân thật trên đảo Cát Bà — thiết kế bởi người bản địa yêu nghề',
            'banner' => array('src' => NULL, 'srcset' => NULL, 'alt' => 'Ảnh banner: đội ngũ Hi Cát Bà'),
            'mission' => array('title' => 'Sứ mệnh của chúng tôi',
                'text' => 'Mang đến những hành trình chân thật giúp du khách chạm vào vịnh, rừng và làng chài của Cát Bà — đồng thời góp phần bảo tồn hệ sinh thái và sinh kế bền vững cho cộng đồng địa phương.',
                'image' => NULL, 'imageSrcset' => NULL),
            'vision' => array('title' => 'Tầm nhìn của chúng tôi',
                'text' => 'Trở thành cầu nối tin cậy nhất giữa du khách và đảo Cát Bà — nơi mỗi người rời đi với cảm giác đã hiểu và yêu đảo hơn khi mới đến.',
                'image' => NULL, 'imageSrcset' => NULL),
            'sales_policy' => array('title' => 'Chính sách bán hàng minh bạch',
                'content' => 'Mọi báo giá của Hi Cát Bà đều liệt kê rõ từng hạng mục — không phụ phí ẩn. Trẻ em dưới 5 tuổi được miễn phí hầu hết dịch vụ mặt đất và tàu; trẻ 5–10 tuổi giảm 25% giá tour. Chính sách đổi ngày khi thời tiết xấu (bão, gió cấp cao) được ghi rõ trong hợp đồng: đổi ngày miễn phí hoặc hoàn 100% nếu tàu bị cấm ra vịnh.',
                'cta_label' => 'Hỏi thêm về chính sách', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'values_section' => array('title' => 'Cam kết với giá trị cốt lõi', 'hub_label' => 'Giá trị cốt lõi', 'eyebrow' => 'Điều chúng tôi tin',
                'subtitle' => 'Bốn giá trị dẫn dắt mọi lịch trình chúng tôi thiết kế trên đảo Cát Bà.'),
            'reasons_section' => array('title' => 'Vì sao chọn Hi Cát Bà?', 'eyebrow' => 'Lý do đồng hành',
                'subtitle' => 'Am hiểu đảo, minh bạch và luôn có người bản địa bên bạn.', 'cta_label' => 'Bắt đầu hành trình của bạn', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'reference_section' => array('title' => 'Người đại diện của chúng tôi tại nước ngoài', 'eyebrow' => 'Mạng lưới toàn cầu',
                'subtitle' => 'Bạn có thể trao đổi trực tiếp bằng ngôn ngữ của mình với đại diện Hi Cát Bà tại châu Á, châu Âu và châu Úc.'),
        ),




'en' => array(
            'seo_title' => 'About us — Hi Cat Ba, connecting travellers with Cat Ba Island',
            'seo_description' => 'Our story, mission and team at Hi Cat Ba — designing journeys and connecting services across Cat Ba Island.',
            'page_title' => 'About us',
            'page_subtitle' => 'Authentic journeys on Cat Ba Island — designed by locals who love their craft',
            'banner' => array('src' => NULL, 'srcset' => NULL, 'alt' => 'Hi Cat Ba team banner'),
            'mission' => array('title' => 'Our mission',
                'text' => 'To deliver authentic journeys that let travellers touch the bay, forest and fishing villages of Cat Ba — while supporting the island\'s ecosystem and local livelihoods.',
                'image' => NULL, 'imageSrcset' => NULL),
            'vision' => array('title' => 'Our vision',
                'text' => 'To become the most trusted bridge between travellers and Cat Ba Island — where every guest leaves loving the island more than when they arrived.',
                'image' => NULL, 'imageSrcset' => NULL),
            'sales_policy' => array('title' => 'Transparent sales policy',
                'content' => 'Every Hi Cat Ba quote lists each line item clearly — no hidden fees. Children under 5 travel free on most ground and boat services; ages 5–10 get 25% off. Weather rescheduling terms (storms, high winds) are written into the contract: free rebooking or full refund if boats are officially suspended.',
                'cta_label' => 'Ask about our policy', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'values_section' => array('title' => 'Commitment to our core values', 'hub_label' => 'Core values', 'eyebrow' => 'What we believe',
                'subtitle' => 'Four values that guide every itinerary we design on Cat Ba Island.'),
            'reasons_section' => array('title' => 'Why choose Hi Cat Ba?', 'eyebrow' => 'Why travel with us',
                'subtitle' => 'Deep island knowledge, clear pricing, and local people by your side.', 'cta_label' => 'Start your journey', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'reference_section' => array('title' => 'Our representatives abroad', 'eyebrow' => 'A global network',
                'subtitle' => 'Speak directly in your own language with Hi Cat Ba representatives across Asia, Europe and Australia.'),
        ),
    ),

    'hero_pills' => array(
        array('zone_slug' => 'vinh-lan-ha', 'vi' => array('label' => 'Vịnh Lan Hạ'),



'en' => array('label' => 'Lan Ha Bay'), 'url' => '/diem-den/vinh-lan-ha'),
        array('zone_slug' => 'ket-hop-ha-long', 'vi' => array('label' => 'Kết hợp Hạ Long'),



'en' => array('label' => 'Combined with Halong'), 'url' => '/diem-den/ket-hop-ha-long'),
    ),

    'home_sections' => array(
        'company_intro' => array(
            'vi' => array('key' => 'company_intro', 'eyebrow' => 'Chuyên gia đảo Cát Bà', 'title' => 'Hành trình chân thật, thiết kế bởi người bản địa', 'subtitle' => NULL,
                'body' => 'Hi Cát Bà là đơn vị lữ hành đặt trụ sở ngay trên đảo, kết nối du khách với vịnh Lan Hạ, vườn quốc gia và các làng chài Cát Bà. Chúng tôi không bán tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của đội ngũ sinh ra và lớn lên trên đảo.',
                'metaLine' => 'Giấy phép kinh doanh dịch vụ lữ hành số 0034/2019/TCDL-GPLHQT', 'ctaLabel' => 'Tìm hiểu về chúng tôi', 'ctaUrl' => '/ve-chung-toi', 'image' => NULL, 'imageAlt' => 'Ảnh đội ngũ Hi Cát Bà'),




'en' => array('key' => 'company_intro', 'eyebrow' => 'Cat Ba island experts', 'title' => 'Authentic journeys, designed by locals', 'subtitle' => NULL,
                'body' => 'Hi Cat Ba is an island-based travel agency connecting guests with Lan Ha Bay, the national park and Cat Ba\'s fishing villages. We do not sell off-the-shelf packages — every itinerary is tailored from real, on-the-ground experience.',
                'metaLine' => 'Travel service license No. 0034/2019/TCDL-GPLHQT', 'ctaLabel' => 'Learn about us', 'ctaUrl' => '/ve-chung-toi', 'image' => NULL, 'imageAlt' => 'Hi Cat Ba team'),
        ),
        'featured_tours' => array(
            'vi' => array('key' => 'featured_tours', 'eyebrow' => 'Được yêu thích nhất', 'title' => 'Những tour được yêu cầu nhiều nhất', 'subtitle' => 'Những hành trình khách hàng đặt và đánh giá cao nhất trong 12 tháng qua.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'featured_tours', 'eyebrow' => 'Most popular', 'title' => 'Our most requested tours', 'subtitle' => 'Itineraries our guests book and rate highest over the past 12 months.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'featured_cruises' => array(
            'vi' => array('key' => 'featured_cruises', 'eyebrow' => 'Hành trình vịnh', 'title' => 'Trải nghiệm trên mặt nước đáng nhớ', 'subtitle' => 'Những hải trình vịnh Lan Hạ được yêu thích nhất — nơi việc di chuyển trở thành một phần của trải nghiệm.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'featured_cruises', 'eyebrow' => 'Bay journeys', 'title' => 'Memorable experiences on the water', 'subtitle' => 'Our most loved Lan Ha Bay voyages — where getting there becomes part of the experience.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'featured_trains' => array(
            'vi' => array('key' => 'featured_trains', 'eyebrow' => 'Ra đảo dễ dàng', 'title' => 'Vé tàu cao tốc & xe khách ra Cát Bà', 'subtitle' => 'Chọn limousine gộp phà, xe khách hay tàu cao tốc từ Hạ Long. Chủ động thời gian, dễ dàng tích hợp vào lịch trình riêng của bạn.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'featured_trains', 'eyebrow' => 'Easy island access', 'title' => 'Ferry, speedboat & bus tickets to Cat Ba', 'subtitle' => 'Choose a ferry-inclusive limousine, a public bus, or a speedboat from Halong. Stay in control of your schedule.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'support_services' => array(
            'vi' => array('key' => 'support_services', 'eyebrow' => 'Dịch vụ bổ trợ', 'title' => 'Ra đảo, vui chơi & hỗ trợ trên đảo', 'subtitle' => 'Vé tàu/phà, trải nghiệm vịnh — rừng — làng chài và các tiện ích thực tế khi bạn tự ghép lịch trình.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'support_services', 'eyebrow' => 'Add-on services', 'title' => 'Island access, activities & on-island support', 'subtitle' => 'Ferry tickets, bay — forest — village experiences and practical extras for your own itinerary.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'destinations' => array(
            'vi' => array('key' => 'destinations', 'eyebrow' => 'Khắp đảo Cát Bà', 'title' => 'Những điểm đến được yêu thích nhất', 'subtitle' => 'Từ trung tâm thị trấn tới vịnh Lan Hạ, vườn quốc gia và Đảo Khỉ — chọn nơi bạn muốn khám phá.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'destinations', 'eyebrow' => 'Across Cat Ba Island', 'title' => 'Our most loved destinations', 'subtitle' => 'From the town centre to Lan Ha Bay, the national park and Monkey Island — choose where to explore.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'testimonials' => array(
            'vi' => array('key' => 'testimonials', 'eyebrow' => 'Khách hàng kể lại', 'title' => 'Trải nghiệm chân thật từ khách hàng', 'subtitle' => 'Hơn 3.000 du khách đã đồng hành cùng chúng tôi trên đảo — đây là những gì họ kể lại.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Xem tất cả cảm nhận', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'testimonials', 'eyebrow' => 'Guest stories', 'title' => 'Real experiences from our travellers', 'subtitle' => 'Over 3,000 guests have travelled with us on the island — here is what they say.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Read all reviews', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => NULL, 'imageAlt' => NULL),
        ),
        'review_platforms' => array(
            'vi' => array('key' => 'review_platforms', 'eyebrow' => NULL, 'title' => 'Hi Cát Bà được đánh giá cao trên', 'subtitle' => NULL, 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'review_platforms', 'eyebrow' => NULL, 'title' => 'Hi Cat Ba is highly rated on', 'subtitle' => NULL, 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'team' => array(
            'vi' => array('key' => 'team', 'eyebrow' => 'Con người Hi Cát Bà', 'title' => 'Đội ngũ tận tâm của chúng tôi', 'subtitle' => 'Những con người bản địa hiểu đảo hơn bất kỳ ai — đồng hành cùng bạn từ lúc lên ý tưởng tới khi rời đảo.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Gặp gỡ cả đội ngũ', 'ctaUrl' => '/doi-ngu', 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'team', 'eyebrow' => 'The Hi Cat Ba team', 'title' => 'Our dedicated local experts', 'subtitle' => 'People who know the island better than anyone — with you from the first idea until you leave.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Meet the full team', 'ctaUrl' => '/doi-ngu', 'image' => NULL, 'imageAlt' => NULL),
        ),
        'videos' => array(
            'vi' => array('key' => 'videos', 'eyebrow' => 'Trải nghiệm thật', 'title' => 'Đảo Cát Bà qua từng thước phim đẹp', 'subtitle' => 'Video chân thật do khách hàng và đội ngũ Hi Cát Bà ghi lại.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Xem tất cả video', 'ctaUrl' => '/video-trai-nghiem', 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'videos', 'eyebrow' => 'Real experiences', 'title' => 'Cat Ba Island in unforgettable frames', 'subtitle' => 'Authentic films from guests and our local team.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'View all videos', 'ctaUrl' => '/video-trai-nghiem', 'image' => NULL, 'imageAlt' => NULL),
        ),
        'quick_inquiry' => array(
            'vi' => array('key' => 'quick_inquiry', 'eyebrow' => 'Tư vấn miễn phí', 'title' => 'Gửi lời nhắn cho chúng tôi', 'subtitle' => NULL,
                'body' => 'Bạn chưa chắc nên đi vịnh hay trekking, mùa nào đẹp, ngân sách bao nhiêu? Để lại lời nhắn — chuyên gia bản địa của chúng tôi sẽ phản hồi trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>, hoàn toàn miễn phí.', 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'quick_inquiry', 'eyebrow' => 'Free advice', 'title' => 'Send us a message', 'subtitle' => NULL,
                'body' => 'Not sure between the bay or trekking, which season, or your budget? Leave a note — our local experts will reply within <strong class="font-semibold text-ink">1 business day</strong>, free of charge.', 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
    ),

    'footer_columns' => array(
        array('title' => 'Hi Cát Bà', 'links' => array(
            array('label' => 'Về chúng tôi', 'route' => array('about')),
            array('label' => 'Cảm nhận khách hàng', 'route' => array('reviews')),
            array('label' => 'Đội ngũ của chúng tôi', 'route' => array('team')),
            array('label' => 'Thư viện khoảnh khắc', 'route' => array('gallery')),
            array('label' => 'Nhận báo giá miễn phí', 'route' => array('customize')),
        )),
        array('title' => 'Tour được yêu thích', 'links' => array(
            array('label' => 'Cát Bà 3 ngày 2 đêm', 'route' => array('tours.show', array('zone' => 'trung-tam-cat-ba', 'slug' => 'cat-ba-3-ngay-tong-quan-dao'))),
            array('label' => 'Tour gia đình 2 ngày 1 đêm', 'route' => array('tours.show', array('zone' => 'trung-tam-cat-ba', 'slug' => 'cat-ba-2-ngay-gia-dinh-bien-dao'))),
            array('label' => 'Đảo Khỉ 1 ngày', 'route' => array('tours.show', array('zone' => 'dao-khi', 'slug' => 'dao-khi-1-ngay-tam-bien-lan'))),
            array('label' => 'Camping Việt Hải', 'route' => array('tours.show', array('zone' => 'lang-viet-hai', 'slug' => 'camping-viet-hai-2n1d'))),
            array('label' => 'Hoàng hôn Lan Hạ', 'route' => array('tours.show', array('zone' => 'vinh-lan-ha', 'slug' => 'vinh-lan-ha-hoang-hon-nua-ngay'))),
            array('label' => 'Cát Bà — Hạ Long kết hợp', 'route' => array('tours.show', array('zone' => 'ket-hop-ha-long', 'slug' => 'cat-ba-ha-long-ket-hop-2n1d'))),
        )),
        array('title' => 'Điểm đến nổi bật', 'links' => array(
            array('label' => 'Vịnh Lan Hạ', 'route' => array('cruises.index', array('type' => 'du-thuyen-lan-ha'))),
            array('label' => 'Vườn quốc gia Cát Bà', 'route' => array('guide.zone', array('zone' => 'vuon-quoc-gia'))),
            array('label' => 'Đảo Khỉ', 'route' => array('guide.show', array('zone' => 'dao-khi', 'slug' => 'dao-khi-cat-ba-di-the-nao'))),
            array('label' => 'Làng chài Cái Bèo', 'route' => array('tours.show', array('zone' => 'cai-beo', 'slug' => 'lang-chai-cai-beo-hang-trung-trang-nua-ngay'))),
        )),
        array('title' => 'Cẩm nang du lịch', 'links' => array(
            array('label' => 'Từ Hà Nội đi Cát Bà thế nào?', 'route' => array('guide.show', array('zone' => 'trung-tam-cat-ba', 'slug' => 'tu-ha-noi-di-cat-ba-the-nao'))),
            array('label' => 'Cát Bà mùa nào đẹp nhất?', 'route' => array('guide.show', array('zone' => 'trung-tam-cat-ba', 'slug' => 'cat-ba-mua-nao-dep-nhat'))),
            array('label' => 'Trekking Việt Hải: kinh nghiệm chi tiết', 'route' => array('guide.show', array('zone' => 'vuon-quoc-gia', 'slug' => 'trekking-viet-hai-kinh-nghiem'))),
            array('label' => 'Chi phí du lịch Cát Bà 3 ngày', 'route' => array('guide.show', array('zone' => 'trung-tam-cat-ba', 'slug' => 'chi-phi-du-lich-cat-ba-3-ngay'))),
        )),
    ),

    'footer_seo_links' => array(
        array('label' => 'Cẩm nang du lịch Cát Bà', 'route' => array('guide.zone', array('zone' => 'trung-tam-cat-ba'))),
        array('label' => 'Cẩm nang vườn quốc gia', 'route' => array('guide.zone', array('zone' => 'vuon-quoc-gia'))),
        array('label' => 'Tour Cát Bà trọn gói', 'route' => array('tours.index', array('zone' => 'trung-tam-cat-ba'))),
        array('label' => 'Du thuyền vịnh Lan Hạ', 'route' => array('cruises.index', array('type' => 'du-thuyen-lan-ha'))),
        array('label' => 'Tour ngày vịnh Cát Bà', 'route' => array('cruises.index', array('type' => 'tour-ngay-vinh-cat-ba'))),
        array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
        array('label' => 'Video trải nghiệm', 'route' => array('videos')),
    ),

    'tour_categories' => array(
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'nua-ngay',
            'type' => 'duration',
            'sort' => 0,
            'minDays' => 0,
            'maxDays' => 1,
            'packageSlugs' => array(
                'dap-xe-xuyen-dao-cat-ba-nua-ngay',
                'bai-cat-co-tam-bien-spa-nua-ngay',
                'lang-chai-cai-beo-hang-trung-trang-nua-ngay',
                'vinh-lan-ha-hoang-hon-nua-ngay',
                'trekking-vqg-nua-ngay-de',
                'lang-viet-hai-thuyen-nua-ngay',
                'dao-khi-nua-ngay',
                'cau-muc-dem-lang-chai-cai-beo',
            ),
            'name' => array(
                'vi' => 'Tour nửa ngày',




'en' => 'Half-day tours',
            ),
            'subtitle' => array(
                'vi' => 'Lựa chọn gọn cho buổi sáng hoặc chiều còn trống.',




'en' => 'A compact option for a free morning or afternoon.',
            ),
            'seo_body' => array(
                'vi' => 'Các tour nửa ngày ở Cát Bà phù hợp ghép thêm trước hoặc sau chuyến đi chính.',




'en' => 'Half-day Cat Ba tours are easy to add before or after your main trip.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => '1-ngay',
            'type' => 'duration',
            'sort' => 1,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'trekking-viet-hai-vqg-1-ngay',
                'vinh-lan-ha-kayak-dao-khi-1-ngay',
                'vinh-lan-ha-lan-ngam-san-ho-1-ngay',
                'vinh-lan-ha-private-kayak-1-ngay',
                'dao-khi-1-ngay-tam-bien-lan',
                'leo-nui-da-voi-lan-ha-1-ngay',
            ),
            'name' => array(
                'vi' => 'Tour 1 ngày',




'en' => '1-day tours',
            ),
            'subtitle' => array(
                'vi' => 'Vịnh Lan Hạ, Đảo Khỉ hoặc trekking Việt Hải — không cần ngủ đêm.',




'en' => 'Lan Ha Bay, Monkey Island or Viet Hai trekking — no overnight needed.',
            ),
            'seo_body' => array(
                'vi' => 'Tour 1 ngày là lựa chọn phổ biến nhất cho khách chỉ ghé Cát Bà ngắn ngày.',




'en' => '1-day tours are the most popular pick for short visits to Cat Ba.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => '2-3-ngay',
            'type' => 'duration',
            'sort' => 2,
            'minDays' => 2,
            'maxDays' => 3,
            'packageSlugs' => array(
                'cat-ba-3-ngay-tong-quan-dao',
                'cat-ba-2-ngay-gia-dinh-bien-dao',
                'camping-viet-hai-2n1d',
                'cat-ba-ha-long-ket-hop-2n1d',
                'cat-ba-ha-long-ket-hop-3n2d',
            ),
            'name' => array(
                'vi' => 'Tour 2 – 3 ngày',




'en' => '2–3 day tours',
            ),
            'subtitle' => array(
                'vi' => 'Kết hợp du thuyền qua đêm với trekking hoặc nghỉ dưỡng biển.',




'en' => 'Combine an overnight cruise with trekking or a beach day.',
            ),
            'seo_body' => array(
                'vi' => 'Tour 2–3 ngày cho phép cảm nhận trọn vẹn cả vịnh và rừng của Cát Bà.',




'en' => 'A 2–3 day tour lets you experience both the bay and the forest of Cat Ba.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => '4-ngay-tro-len',
            'type' => 'duration',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 10,
            'packageSlugs' => array(
                'cat-ba-5-ngay-nghi-duong-kham-pha',
            ),
            'name' => array(
                'vi' => 'Tour 4 ngày trở lên',




'en' => '4+ day tours',
            ),
            'subtitle' => array(
                'vi' => 'Lịch trình sâu nhất: nghỉ dưỡng, lặn biển, trekking và làng chài.',




'en' => 'Our deepest itineraries: beach, diving, trekking and fishing villages.',
            ),
            'seo_body' => array(
                'vi' => 'Tour dài ngày dành cho ai muốn trải nghiệm Cát Bà không vội vàng.',




'en' => 'Longer tours are for travellers who want an unhurried Cat Ba experience.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'tour-trong-ngay',
            'type' => 'theme',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'trekking-viet-hai-vqg-1-ngay',
                'vinh-lan-ha-kayak-dao-khi-1-ngay',
                'lang-chai-cai-beo-hang-trung-trang-nua-ngay',
                'dap-xe-xuyen-dao-cat-ba-nua-ngay',
                'bai-cat-co-tam-bien-spa-nua-ngay',
                'vinh-lan-ha-hoang-hon-nua-ngay',
                'vinh-lan-ha-lan-ngam-san-ho-1-ngay',
                'vinh-lan-ha-private-kayak-1-ngay',
                'trekking-vqg-nua-ngay-de',
                'lang-viet-hai-thuyen-nua-ngay',
                'dao-khi-1-ngay-tam-bien-lan',
                'dao-khi-nua-ngay',
                'cau-muc-dem-lang-chai-cai-beo',
                'leo-nui-da-voi-lan-ha-1-ngay',
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
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'tour-2-ngay-1-dem',
            'type' => 'theme',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 2,
            'packageSlugs' => array(
                'cat-ba-ha-long-ket-hop-2n1d',
                'cat-ba-2-ngay-gia-dinh-bien-dao',
                'camping-viet-hai-2n1d',
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
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'tour-3-ngay-2-dem',
            'type' => 'theme',
            'sort' => 2,
            'minDays' => 3,
            'maxDays' => 3,
            'packageSlugs' => array(
                'cat-ba-3-ngay-tong-quan-dao',
                'cat-ba-ha-long-ket-hop-3n2d',
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
            'zoneSlug' => 'trung-tam-cat-ba',
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
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'tour-tu-5-ngay',
            'type' => 'theme',
            'sort' => 4,
            'minDays' => 5,
            'maxDays' => null,
            'packageSlugs' => array(
                'cat-ba-5-ngay-nghi-duong-kham-pha',
            ),
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
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'vinh-lan-ha',
            'type' => 'theme',
            'sort' => 10,
            'packageSlugs' => array(
                'vinh-lan-ha-kayak-dao-khi-1-ngay',
                'vinh-lan-ha-hoang-hon-nua-ngay',
                'vinh-lan-ha-lan-ngam-san-ho-1-ngay',
                'vinh-lan-ha-private-kayak-1-ngay',
                'leo-nui-da-voi-lan-ha-1-ngay',
                'du-thuyen-lan-ha-2-ngay',
                'du-thuyen-lan-ha-3-ngay',
            ),
            'name' => array(
                'vi' => 'Tour vịnh Lan Hạ',




'en' => 'Lan Ha Bay tours',
            ),
            'subtitle' => array(
                'vi' => 'Kayak, tắm biển, hoàng hôn và du thuyền giữa hơn 400 đảo đá vôi.',




'en' => 'Kayak, swim, sunset and cruise among 400+ limestone islands.',
            ),
            'seo_body' => array(
                'vi' => 'Vịnh Lan Hạ được xem là phiên bản hoang sơ và ít khách hơn của Hạ Long.',




'en' => 'Lan Ha Bay is considered the wilder, quieter version of Halong Bay.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'trekking-vqg',
            'type' => 'theme',
            'sort' => 11,
            'packageSlugs' => array(
                'trekking-viet-hai-vqg-1-ngay',
                'trekking-vqg-nua-ngay-de',
                'camping-viet-hai-2n1d',
            ),
            'name' => array(
                'vi' => 'Tour trekking vườn quốc gia',




'en' => 'National park trekking tours',
            ),
            'subtitle' => array(
                'vi' => 'Rừng nguyên sinh, Ao Ếch và làng Việt Hải — từ nửa ngày tới qua đêm.',




'en' => 'Primary forest, Ao Ech and Viet Hai — from half-day to overnight.',
            ),
            'seo_body' => array(
                'vi' => 'Trekking vườn quốc gia là hoạt động được đánh giá cao nhất tại Cát Bà.',




'en' => 'National park trekking is the highest-rated activity on Cat Ba.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'dao-khi-tam-bien',
            'type' => 'theme',
            'sort' => 12,
            'packageSlugs' => array(
                'dao-khi-1-ngay-tam-bien-lan',
                'dao-khi-nua-ngay',
                'vinh-lan-ha-kayak-dao-khi-1-ngay',
            ),
            'name' => array(
                'vi' => 'Tour Đảo Khỉ & tắm biển',




'en' => 'Monkey Island & beach tours',
            ),
            'subtitle' => array(
                'vi' => 'Bãi cát trắng Cát Dứa, snorkel và trải nghiệm gần khỉ hoang dã.',




'en' => 'Cat Dua white sand, snorkel and safe monkey encounters.',
            ),
            'seo_body' => array(
                'vi' => 'Đảo Khỉ là điểm “must-do” gần như bắt buộc trong mọi chuyến Cát Bà ngắn ngày.',




'en' => 'Monkey Island is a near must-do on any short Cat Ba trip.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'lang-viet-hai',
            'type' => 'theme',
            'sort' => 13,
            'packageSlugs' => array(
                'camping-viet-hai-2n1d',
                'lang-viet-hai-thuyen-nua-ngay',
                'trekking-viet-hai-vqg-1-ngay',
            ),
            'name' => array(
                'vi' => 'Tour làng Việt Hải',




'en' => 'Viet Hai village tours',
            ),
            'subtitle' => array(
                'vi' => 'Homestay/camping hoặc thuyền vào làng giữa vườn quốc gia.',




'en' => 'Homestay/camping or boat access to the village inside the park.',
            ),
            'seo_body' => array(
                'vi' => 'Việt Hải là trải nghiệm “off-grid” đặc trưng nhất của đảo Cát Bà.',




'en' => 'Viet Hai is Cat Ba’s most distinctive off-grid village experience.',
            ),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'trung-tam-cat-ba',
            'slug' => 'lang-chai-cai-beo',
            'type' => 'theme',
            'sort' => 14,
            'packageSlugs' => array(
                'lang-chai-cai-beo-hang-trung-trang-nua-ngay',
                'cau-muc-dem-lang-chai-cai-beo',
            ),
            'name' => array(
                'vi' => 'Tour làng chài Cái Bèo',




'en' => 'Cai Beo fishing village tours',
            ),
            'subtitle' => array(
                'vi' => 'Thuyền nan, di chỉ khảo cổ và câu mực đêm trên vịnh.',




'en' => 'Sampan rides, archaeology and night squid fishing on the bay.',
            ),
            'seo_body' => array(
                'vi' => 'Cái Bèo gắn với di chỉ khảo cổ hơn 6.000 năm — điểm văn hoá gần trung tâm nhất.',




'en' => 'Cai Beo links to 6,000+ year archaeology — the closest cultural stop to town.',
            ),
            'faqs' => array(),
        ),
    ),

    'listing_faqs' => array(
        array('q' => 'Thời tiết Cát Bà tháng nào đẹp nhất để đi tour trọn gói?', 'a' => 'Tháng 9 – 11 là thời điểm dễ chịu nhất: ít mưa, biển vẫn ấm, trekking khô ráo. Tháng 5 – 8 nóng và có thể có bão nên cần theo dõi dự báo trước khi đặt tàu.'),
        array('q' => 'Tour trọn gói đã bao gồm vé phà/xe ra đảo chưa?', 'a' => 'Tuỳ tour — một số gói (như tour kết hợp Hạ Long) đã gồm tàu cao tốc xuyên vịnh; các tour trong đảo thường chưa gồm vé phà/xe từ Hà Nội hoặc Hải Phòng, có thể đặt thêm.'),
        array('q' => 'Tôi có thể đặt tour riêng (private) cho gia đình không?', 'a' => 'Được. Mọi tour trên website đều có phương án private với tàu riêng và hướng dẫn viên riêng — dùng form "Thiết kế tour riêng" để nhận báo giá trong 24 giờ.'),
        array('q' => 'Chính sách khi tàu tạm ngưng do thời tiết xấu như thế nào?', 'a' => 'Được đổi ngày miễn phí hoặc hoàn 100% nếu cơ quan chức năng cấm tàu ra vịnh do bão/gió mạnh — chi tiết ghi rõ trong hợp đồng.'),
        array('q' => 'Khách quốc tế có cần visa để đến Cát Bà không?', 'a' => 'Cát Bà thuộc Việt Nam nên áp dụng chính sách visa nhập cảnh Việt Nam thông thường — nhiều quốc tịch được miễn visa ngắn hạn hoặc dùng e-visa, vui lòng kiểm tra quy định mới nhất trước ngày bay.'),
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
        'vinh-lan-ha' => 'Tour vịnh Lan Hạ',
        'trekking-vqg' => 'Tour trekking vườn quốc gia',
        'dao-khi-tam-bien' => 'Tour Đảo Khỉ & tắm biển',
        'lang-viet-hai' => 'Tour làng Việt Hải',
        'lang-chai-cai-beo' => 'Tour làng chài Cái Bèo',
    ),
);

/* ── company + catalogue dịch vụ (gom trong seed dự án — 1 file / 1 PROJECT_SEED) ── */
$__servicesSeed = [
    'service_clusters' => [
        ['code' => 'ferry', 'nav_label' => 'Ra đảo', 'label' => 'Tàu cao tốc, phà & xe kết nối', 'icon' => 'ship', 'hub_key' => 'ferries_hub', 'sort' => 1],
        ['code' => 'flight', 'nav_label' => 'Máy bay & đưa đón', 'label' => 'Vé máy bay & đưa đón cửa ngõ', 'icon' => 'plane', 'hub_key' => 'flights_hub', 'sort' => 2],
        ['code' => 'stay', 'nav_label' => 'Lưu trú', 'label' => 'Du thuyền, bungalow nổi & homestay', 'icon' => 'building', 'hub_key' => 'stays_hub', 'sort' => 3],
        ['code' => 'experience', 'nav_label' => 'Vui chơi', 'label' => 'Vé vui chơi & trải nghiệm trên đảo', 'icon' => 'sparkles', 'hub_key' => 'experiences_hub', 'sort' => 4],
        ['code' => 'other', 'nav_label' => 'Dịch vụ', 'label' => 'Hỗ trợ & tiện ích trên đảo', 'icon' => 'briefcase', 'hub_key' => 'extras_hub', 'sort' => 5],
    ],

    'service_categories' => [
        // FERRY — cửa ngõ biển & đường bộ (insight: 90% khách phải nối Hà Nội/Hải Phòng/Hạ Long → đảo)
        ['cluster' => 'ferry', 'slug' => 'tau-cao-toc-hai-phong-cat-ba', 'name' => 'Tàu cao tốc Hải Phòng ↔ Cát Bà', 'sort' => 1,
            'intro' => 'Tuyến nhanh nhất từ trung tâm Hải Phòng (Bến Bính) ra bến Bèo — ~45 phút, không cần chờ phà ô tô.',
            'seo_body' => 'Tàu cao tốc Bến Bính — Bến Bèo là lựa chọn phổ biến nhất cho khách bay tới Cát Bi hoặc đã ở Hải Phòng. :brand hỗ trợ e-ticket và đổi ngày khi biển động.'],
        ['cluster' => 'ferry', 'slug' => 'pha-oto-xe-may-dong-bai', 'name' => 'Phà ô tô / xe máy Đồng Bài — Cái Viềng', 'sort' => 2,
            'intro' => 'Phà ngắn cho khách tự lái qua đảo Cát Hải — Cát Bà, sau khi đi cầu Tân Vũ — Lạch Huyện.',
            'seo_body' => 'Phù hợp khách thuê ô tô hoặc xe máy từ Hà Nội/Hải Phòng. Mùa cao điểm có thể chờ chuyến — nên đến sớm hoặc đặt limousine gộp phà.'],
        ['cluster' => 'ferry', 'slug' => 'limousine-xe-ha-noi-cat-ba', 'name' => 'Limousine & xe Hà Nội ↔ Cát Bà', 'sort' => 3,
            'intro' => 'Xe 9–16 chỗ đón tận nơi, gộp vé phà/tàu — không phải tự nối chuyến tại bến.',
            'seo_body' => 'Thị trường Hà Nội chiếm phần lớn khách Cát Bà cuối tuần. Limousine gộp phà mất 2.5–3 giờ, là phương án tiện nhất cho nhóm nhỏ và gia đình.'],
        ['cluster' => 'ferry', 'slug' => 'tau-tuan-chau-cat-ba', 'name' => 'Tàu cao tốc Tuần Châu ↔ Cát Bà', 'sort' => 4,
            'intro' => 'Xuyên vịnh trực tiếp giữa Hạ Long và Cát Bà — nền tảng mọi tour kết hợp hai vịnh di sản.',
            'seo_body' => 'Tuyến ~45–60 phút, không quay lại đất liền. Khách đã ở Hạ Long hoặc tour combo Lan Hạ — Hạ Long đều dùng tuyến này.'],
        ['cluster' => 'ferry', 'slug' => 'xe-hai-phong-ben-tau', 'name' => 'Xe Hải Phòng ↔ bến tàu / phà', 'sort' => 5,
            'intro' => 'Taxi hoặc xe riêng từ sân bay Cát Bi, bến xe hoặc trung tâm Hải Phòng tới Bến Bính hoặc Đồng Bài.',
            'seo_body' => 'Chặng nối quan trọng sau khi bay tới Hải Phòng — :brand canh giờ kịp chuyến tàu/phà đã đặt.'],
        ['cluster' => 'ferry', 'slug' => 'xe-noi-dao-don-ben', 'name' => 'Xe đón bến & di chuyển trong đảo', 'sort' => 6,
            'intro' => 'Đón tại bến Bèo, Cái Viềng hoặc khách sạn — đưa tới điểm trekking, bãi Cát Cò, cửa VQG.',
            'seo_body' => 'Đảo Cát Bà dài và đồi — nhiều khách đặt xe đón bến ngay khi xuống tàu, đặc biệt khi mang vali hoặc đi cùng trẻ nhỏ.'],

        // FLIGHT — cửa ngõ hàng không là Cát Bi (HPH), không phải sân bay trên đảo
        ['cluster' => 'flight', 'slug' => 'noi-dia-toi-hai-phong', 'name' => 'Vé nội địa tới Hải Phòng (Cát Bi)', 'sort' => 1,
            'intro' => 'Bay tới sân bay Cát Bi — gần Cát Bà hơn Nội Bài nếu bạn đi thẳng ra đảo.',
            'seo_body' => 'Chặng HAN/SGN — HPH phổ biến với khách miền Bắc và miền Nam muốn rút ngắn thời gian di chuyển mặt đất.'],
        ['cluster' => 'flight', 'slug' => 'dua-don-san-bay-cat-bi', 'name' => 'Đưa đón sân bay Cát Bi → Cát Bà', 'sort' => 2,
            'intro' => 'Xe riêng gộp vé tàu cao tốc/phà — từ sảnh đến tận bến Bèo hoặc khách sạn trên đảo.',
            'seo_body' => 'Giải pháp “door to pier” cho khách bay tới Hải Phòng — không phải tự bắt taxi và mua vé tàu lẻ.'],
        ['cluster' => 'flight', 'slug' => 'dua-don-noi-bai-cat-ba', 'name' => 'Đưa đón Nội Bài → Cát Bà (limousine)', 'sort' => 3,
            'intro' => 'Limousine hoặc xe riêng từ Hà Nội gộp phà — dành cho khách bay quốc tế vào Nội Bài.',
            'seo_body' => 'Khách quốc tế và đoàn MICE thường bay Nội Bài — :brand sắp limousine canh giờ phà/tàu phù hợp.'],
        ['cluster' => 'flight', 'slug' => 'combo-bay-ra-dao', 'name' => 'Combo vé bay + đưa đón + ra đảo', 'sort' => 4,
            'intro' => 'Một báo giá cho bay tới Hải Phòng/Hà Nội, xe đón và tàu/phà ra Cát Bà.',
            'seo_body' => 'Một đầu mối thay vì tự ghép vé máy bay, taxi và tàu — phù hợp khách lần đầu hoặc đặt cho đoàn.'],

        // STAY — chỉ chỗ ngủ đặc trưng (không catalogue khách sạn/resort)
        ['cluster' => 'stay', 'slug' => 'ngu-tren-vinh-du-thuyen', 'name' => 'Ngủ trên vịnh Lan Hạ', 'sort' => 1,
            'intro' => 'Cabin du thuyền qua đêm và bungalow nổi — trải nghiệm ngủ giữa vịnh, không phải lưu trú đất liền.',
            'seo_body' => 'Đây là dạng lưu trú “signature” của Cát Bà — khác hẳn khách sạn thị trấn. :brand đặt cabin theo hạng và chính sách đổi ngày khi cấm tàu.'],
        ['cluster' => 'stay', 'slug' => 'homestay-viet-hai', 'name' => 'Homestay làng Việt Hải', 'sort' => 2,
            'intro' => 'Ngủ cùng người dân trong làng không xe máy, giữa vườn quốc gia — thường ghép trekking.',
            'seo_body' => 'Homestay Việt Hải là trải nghiệm văn hoá đảo đặc trưng — tiện nghi đơn giản, điện máy phát, yên tĩnh tuyệt đối.'],

        // EXPERIENCE — phân theo “lý do đến Cát Bà” của từng nhóm khách
        ['cluster' => 'experience', 'slug' => 'tour-ngay-vinh-lan-ha', 'name' => 'Tour ngày vịnh Lan Hạ', 'sort' => 1,
            'intro' => 'Tàu gỗ, kayak, tắm biển hoang sơ — hoạt động số 1 của khách ghé ngắn ngày.',
            'seo_body' => 'Lan Hạ được khách quốc tế chọn vì ít đông hơn Hạ Long. Tour ngày phù hợp ghép Đảo Khỉ hoặc chỉ tập trung vịnh.'],
        ['cluster' => 'experience', 'slug' => 'kayak-snorkel-vinh', 'name' => 'Kayak & snorkel vịnh', 'sort' => 2,
            'intro' => 'Chèo hang Luồn, lặn ống thở tại Vạn Bội / Ba Trái Đào — mùa 4–8 nước trong nhất.',
            'seo_body' => 'Có thể đặt lẻ thiết bị + HDV hoặc gộp vào tour ngày. Khách Hàn/Âu thường ưu tiên snorkel; nhóm trẻ thích kayak private.'],
        ['cluster' => 'experience', 'slug' => 'trekking-vuon-quoc-gia', 'name' => 'Trekking vườn quốc gia', 'sort' => 3,
            'intro' => 'Việt Hải — Ao Ếch, Ngự Lâm viewpoint, cung nửa ngày dễ — từ dễ tới trung bình.',
            'seo_body' => 'Voọc Cát Bà đặc hữu là điểm thu hút khách sinh thái. :brand có cung rút gọn cho gia đình và cung 10km cho người có thể lực.'],
        ['cluster' => 'experience', 'slug' => 'lang-chai-van-hoa', 'name' => 'Làng chài & văn hoá địa phương', 'sort' => 4,
            'intro' => 'Thuyền nan Cái Bèo, hang Trung Trang, câu mực đêm — gắn với di sản khảo cổ hơn 6.000 năm.',
            'seo_body' => 'Phân khúc khách thích “authentic” và gia đình — tour ngắn, dễ ghép buổi chiều hoặc tối sau tour vịnh.'],
        ['cluster' => 'experience', 'slug' => 'dao-khi-tam-bien', 'name' => 'Đảo Khỉ & tắm biển', 'sort' => 5,
            'intro' => 'Bãi Cát Dứa, snorkel gần bờ — “must-do” của khách lần đầu, đặc biệt gia đình có trẻ.',
            'seo_body' => 'Có vé tàu lẻ hoặc ghép tour ngày vịnh. Lưu ý an toàn khi tiếp xúc khỉ hoang dã — HDV hướng dẫn trên chỗ.'],
        ['cluster' => 'experience', 'slug' => 'leo-nui-phieu-luu', 'name' => 'Leo núi & phiêu lưu trên vịnh', 'sort' => 6,
            'intro' => 'Deep water solo và leo núi có dây trên vách đá Lan Hạ — phân khúc khách trẻ, khách lẻ quốc tế.',
            'seo_body' => 'Cát Bà là một trong ít điểm DWS ở Việt Nam. Yêu cầu biết bơi; :brand chỉ đặt với HLV chứng nhận.'],
        ['cluster' => 'experience', 'slug' => 'kinh-nghiem-dem', 'name' => 'Trải nghiệm buổi tối trên vịnh', 'sort' => 7,
            'intro' => 'Hoàng hôn trên tàu, câu mực đêm, BBQ trên boong du thuyền.',
            'seo_body' => 'Cặp đôi và nhóm bạn hay chọn tour hoàng hôn hoặc câu mực — khác biệt so với tour ngày đông khách.'],
        ['cluster' => 'experience', 'slug' => 'kham-pha-dao-bang-xe', 'name' => 'Khám phá đảo bằng xe đạp / xe máy', 'sort' => 8,
            'intro' => 'Đạp xe đồi chè, làng ven biển; hoặc thuê xe máy tự do (đặt qua mục Dịch vụ).',
            'seo_body' => 'Góc nhìn “đảo thật” ngoài vịnh — ít tour đại trà, phù hợp khách ở 2–3 ngày muốn buổi tự do.'],

        // OTHER — tiện ích thực tế trên đảo
        ['cluster' => 'other', 'slug' => 'thue-xe-noi-dao', 'name' => 'Thuê xe máy, xe điện & tàu riêng', 'sort' => 1,
            'intro' => 'Tự khám phá đảo hoặc thuê nguyên tàu gỗ charter cho nhóm riêng.'],
        ['cluster' => 'other', 'slug' => 'huong-dan-vien-porter', 'name' => 'Hướng dẫn viên & porter địa phương', 'sort' => 2,
            'intro' => 'HDV trekking, porter mang đồ, phiên dịch tiếng Anh cơ bản — theo giờ hoặc theo ngày.'],
        ['cluster' => 'other', 'slug' => 'tien-ich-du-lich', 'name' => 'Gửi hành lý & hỗ trợ khẩn cấp', 'sort' => 3,
            'intro' => 'Ký gửi vali khi đi trekking/tour ngày; hotline y tế 24/7 cho khách đặt qua :brand.'],
        ['cluster' => 'other', 'slug' => 'an-uong-dat-cho', 'name' => 'Đặt bàn hải sản & chợ đêm', 'sort' => 4,
            'intro' => 'Đặt trước bàn chợ hải sản bến Bèo, gợi ý mùa và món theo ngân sách.'],
        ['cluster' => 'other', 'slug' => 'spa-thu-gian', 'name' => 'Spa & massage thư giãn', 'sort' => 5,
            'intro' => 'Massage chân/body sau ngày trekking hoặc lặn — đối tác trên đảo được kiểm duyệt.'],
    ],

    'services' => [
        // ── FERRY (9) ────────────────────────────────────────────────────────
        [
            'code' => 'ferry-benbinh-catba-oneway', 'cluster' => 'ferry', 'category_slug' => 'tau-cao-toc-hai-phong-cat-ba', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Tàu cao tốc Bến Bính → Cát Bà (một chiều)', 'slug' => 'tau-cao-toc-ben-binh-cat-ba',
            'price_from' => 90000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 214,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Bến Bính, Hải Phòng → Bến Bèo, Cát Bà',
            'summary' => 'Tuyến nhanh nhất từ trung tâm Hải Phòng ra đảo — ~45 phút, nhiều chuyến/ngày, không cần chờ phà ô tô.',
            'highlights' => ['~45 phút ra đảo', 'Nhiều chuyến/ngày kể cả cuối tuần', 'Không cần chờ phà như tuyến Đồng Bài', 'Phù hợp khách đi bộ, không mang xe', 'E-ticket qua Hi Cát Bà'],
            'inclusions' => ['Vé tàu cao tốc một chiều', 'Phí dịch vụ đặt vé'], 'exclusions' => ['Xe tới Bến Bính (đặt thêm)', 'Hành lý quá khổ'],
            'notes' => ['Giá tham khảo — chốt theo ngày đi và nhà xe.'], 'attrs' => ['from' => 'Hải Phòng (Bến Bính)', 'to' => 'Cát Bà (Bến Bèo)', 'duration_hours' => 1, 'operator' => 'Đội tàu cao tốc Cát Bà', 'vehicle_type' => 'tàu cao tốc'],
        ],
        [
            'code' => 'ferry-benbinh-roundtrip', 'cluster' => 'ferry', 'category_slug' => 'tau-cao-toc-hai-phong-cat-ba', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Vé tàu cao tốc khứ hồi Bến Bính ↔ Cát Bà', 'slug' => 've-tau-cao-toc-khu-hoi-ben-binh',
            'price_from' => 170000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 156,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Khứ hồi', 'location_label' => 'Bến Bính ↔ Bến Bèo',
            'summary' => 'Gói khứ hồi tiết kiệm hơn mua lẻ — giữ chỗ chiều về trong ngày hoặc ngày hôm sau tùy gói.',
            'highlights' => ['Rẻ hơn mua 2 chiều lẻ', 'Giữ chỗ chiều về linh hoạt', 'E-ticket một lần', 'Đổi ngày khi biển động'],
            'inclusions' => ['2 lượt tàu cao tốc', 'Phí dịch vụ'], 'exclusions' => ['Xe hai đầu', 'Phụ phí đổi giờ ngoài chính sách'],
            'notes' => ['Chiều về thường 14:00–17:00 — xác nhận khung giờ khi đặt.'], 'attrs' => ['from' => 'Bến Bính', 'to' => 'Bến Bèo', 'duration_hours' => 1, 'vehicle_type' => 'tàu cao tốc'],
        ],
        [
            'code' => 'ferry-dongbai-caivieng', 'cluster' => 'ferry', 'category_slug' => 'pha-oto-xe-may-dong-bai', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Phà Đồng Bài — Cái Viềng (ô tô / xe máy)', 'slug' => 'pha-dong-bai-cai-vieng',
            'price_from' => 25000, 'currency' => 'VND', 'rating' => 4.4, 'review_count' => 132,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Đồng Bài, Cát Hải → Cái Viềng, Cát Bà',
            'summary' => 'Phà ngắn ~15–20 phút cho khách tự lái qua cầu Tân Vũ — Lạch Huyện sang đảo Cát Bà.',
            'highlights' => ['Phù hợp tự lái ô tô/xe máy', 'Chạy liên tục theo giờ', 'Giá theo loại xe', 'Không cần đặt trước (trừ mùa lễ)'],
            'inclusions' => ['Vé phà một lượt theo loại xe'], 'exclusions' => ['Xăng dầu', 'Phí cầu đường'],
            'notes' => ['Mùa cao điểm có thể chờ chuyến — đến sớm hoặc chọn limousine gộp phà.'],
            'attrs' => ['from' => 'Đồng Bài', 'to' => 'Cái Viềng', 'duration_hours' => 0.5, 'vehicle_type' => 'phà ô tô'],
            'options' => [['code' => 'pha-xe-may', 'name' => 'Xe máy', 'price_from' => 25000], ['code' => 'pha-oto', 'name' => 'Ô tô 4–7 chỗ', 'price_from' => 60000]],
        ],
        [
            'code' => 'ferry-limousine-hanoi-oneway', 'cluster' => 'ferry', 'category_slug' => 'limousine-xe-ha-noi-cat-ba', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Limousine Hà Nội → Cát Bà (gộp phà/tàu)', 'slug' => 'limousine-ha-noi-cat-ba-gop-pha',
            'price_from' => 300000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 356,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Đón tận nơi', 'location_label' => 'Hà Nội → Cát Bà',
            'summary' => 'Xe 9 chỗ đón tận khách sạn Hà Nội, gộp vé qua đảo — tổng ~2.5–3 giờ, không tự nối chuyến.',
            'highlights' => ['Đón phố cổ & khu trung tâm', 'Đã gộp phà/tàu', 'Wifi & điều hoà', 'Nhiều chuyến/ngày'],
            'inclusions' => ['Limousine một chiều', 'Vé qua đảo'], 'exclusions' => ['Đón ngoài khu vực (phụ phí)', 'Ăn trên xe'],
            'notes' => ['Đặt trước 12 giờ, đặc biệt thứ 6–CN.'], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Cát Bà', 'duration_hours' => 3, 'vehicle_type' => 'limousine 9 chỗ'],
        ],
        [
            'code' => 'ferry-limousine-hanoi-roundtrip', 'cluster' => 'ferry', 'category_slug' => 'limousine-xe-ha-noi-cat-ba', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Limousine khứ hồi Hà Nội ↔ Cát Bà', 'slug' => 'limousine-khu-hoi-ha-noi-cat-ba',
            'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 198,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Hà Nội ↔ Cát Bà',
            'summary' => 'Gói cuối tuần phổ biến — đi và về gộp một giá, chọn ngày về linh hoạt trong tuần.',
            'highlights' => ['Tiết kiệm hơn 2 chiều lẻ', 'Giữ chỗ chiều về', 'Đón tận nơi hai đầu', 'Phù hợp gia đình 2–4 người'],
            'inclusions' => ['2 lượt limousine', 'Vé qua đảo mỗi chiều'], 'exclusions' => ['Phụ phí đón xa trung tâm'],
            'notes' => ['Chiều về thường 14:00–16:00 từ Cát Bà.'], 'attrs' => ['from' => 'Hà Nội', 'to' => 'Cát Bà', 'duration_hours' => 3, 'vehicle_type' => 'limousine'],
        ],
        [
            'code' => 'ferry-speedboat-tuanchau-oneway', 'cluster' => 'ferry', 'category_slug' => 'tau-tuan-chau-cat-ba', 'zone_slug' => 'ket-hop-ha-long',
            'title' => 'Tàu cao tốc Tuần Châu → Cát Bà', 'slug' => 'tau-cao-toc-tuan-chau-cat-ba',
            'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 97,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Tuần Châu, Hạ Long → Bến Bèo',
            'summary' => 'Xuyên vịnh trực tiếp ~45–60 phút — nền tảng tour kết hợp Lan Hạ & Hạ Long.',
            'highlights' => ['Không quay lại đất liền', 'Cảnh hai vịnh trên đường', 'Phù hợp combo 2 vịnh', 'E-ticket'],
            'inclusions' => ['Vé tàu một chiều'], 'exclusions' => ['Xe tới Tuần Châu', 'Tour mặt đất'],
            'notes' => ['Có thể tạm ngưng khi biển động.'], 'attrs' => ['from' => 'Tuần Châu', 'to' => 'Cát Bà', 'duration_hours' => 1, 'vehicle_type' => 'tàu cao tốc'],
        ],
        [
            'code' => 'ferry-speedboat-tuanchau-roundtrip', 'cluster' => 'ferry', 'category_slug' => 'tau-tuan-chau-cat-ba', 'zone_slug' => 'ket-hop-ha-long',
            'title' => 'Tàu cao tốc khứ hồi Tuần Châu ↔ Cát Bà', 'slug' => 'tau-cao-toc-khu-hoi-tuan-chau-cat-ba',
            'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 54,
            'is_featured' => false, 'is_hot_deal' => true, 'discount_badge' => 'Combo vịnh', 'location_label' => 'Tuần Châu ↔ Cát Bà',
            'summary' => 'Dành cho khách ở Hạ Long muốn sang Cát Bà trong ngày hoặc ngược lại — giữ chỗ hai chiều.',
            'highlights' => ['Gộp 2 chiều', 'Linh hoạt ngày về', 'Tiết kiệm so với mua lẻ'],
            'inclusions' => ['2 lượt tàu cao tốc'], 'exclusions' => ['Xe đất liền', 'Tour vịnh'],
            'notes' => ['Kiểm tra lịch chạy theo mùa trước khi thanh toán.'], 'attrs' => ['from' => 'Tuần Châu', 'to' => 'Cát Bà', 'vehicle_type' => 'tàu cao tốc'],
        ],
        [
            'code' => 'ferry-transfer-catbi-benbinh', 'cluster' => 'ferry', 'category_slug' => 'xe-hai-phong-ben-tau', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Xe sân bay Cát Bi → Bến Bính (nối tàu cao tốc)', 'slug' => 'xe-cat-bi-ben-binh',
            'price_from' => 180000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 89,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Sân bay Cát Bi → Bến Bính',
            'summary' => 'Xe riêng ~30 phút — canh giờ kịp chuyến tàu ra đảo sau khi bay tới Hải Phòng.',
            'highlights' => ['Theo dõi chuyến bay', 'Hỗ trợ hành lý', 'Có thể gộp vé tàu'],
            'inclusions' => ['Xe 4 chỗ một chiều'], 'exclusions' => ['Vé tàu cao tốc', 'Phụ phí đêm khuya'],
            'notes' => ['Gửi số hiệu chuyến bay trước 24 giờ.'], 'attrs' => ['from' => 'Sân bay Cát Bi', 'to' => 'Bến Bính', 'duration_hours' => 0.5, 'vehicle_type' => 'xe 4 chỗ'],
        ],
        [
            'code' => 'ferry-island-pickup', 'cluster' => 'ferry', 'category_slug' => 'xe-noi-dao-don-ben', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Xe đón bến Bèo / Cái Viềng → khách sạn / điểm tour', 'slug' => 'xe-don-ben-cat-ba',
            'price_from' => 120000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 167,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Bến tàu/phà → trong đảo',
            'summary' => 'Đón ngay khi xuống tàu — đưa tới khách sạn, bãi Cát Cò, cửa VQG hoặc điểm hẹn tour.',
            'highlights' => ['Đón tại bến có bảng tên', 'Xe 4–16 chỗ theo nhóm', 'Gộp nhiều điểm trong đảo', 'Phù hợp mang vali'],
            'inclusions' => ['Xe một chiều trong đảo'], 'exclusions' => ['Vé tàu/phà', 'Chờ quá 60 phút (phụ phí)'],
            'notes' => ['Gửi tên tàu và giờ cập bến khi đặt.'], 'attrs' => ['service_type' => 'island_transfer', 'vehicle_type' => 'xe 4–16 chỗ'],
        ],

        // ── FLIGHT (5) ───────────────────────────────────────────────────────
        [
            'code' => 'flight-han-hph', 'cluster' => 'flight', 'category_slug' => 'noi-dia-toi-hai-phong', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Vé máy bay Hà Nội — Hải Phòng (Cát Bi)', 'slug' => 've-may-bay-ha-noi-hai-phong',
            'price_from' => 890000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 312,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Hà Nội → HPH',
            'summary' => 'Bay ~1 giờ — gần Cát Bà hơn ở lại Hà Nội nếu bạn đi thẳng ra đảo trong ngày.',
            'highlights' => ['Nhiều chuyến/ngày', 'Gộp đưa đón Cát Bi — Cát Bà', 'E-ticket nhanh'],
            'inclusions' => ['Vé economy một chiều', 'Thuế phí sân bay'], 'exclusions' => ['Hành lý ký gửi', 'Ra đảo'],
            'notes' => ['Giá theo ngày bay — báo lại khi chốt.'], 'attrs' => ['from' => 'HAN', 'to' => 'HPH', 'flight_time' => '1h00m'],
        ],
        [
            'code' => 'flight-sgn-hph', 'cluster' => 'flight', 'category_slug' => 'noi-dia-toi-hai-phong', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Vé máy bay Sài Gòn — Hải Phòng (Cát Bi)', 'slug' => 've-may-bay-sai-gon-hai-phong',
            'price_from' => 1450000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 421,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Nối đảo', 'location_label' => 'Sài Gòn → HPH',
            'summary' => 'Bay thẳng ~2 giờ tới Cát Bi — sau đó tàu/phà ra Cát Bà khoảng 1 giờ.',
            'highlights' => ['Bay thẳng', 'Cát Bi gần bến tàu', 'Gộp transfer + tàu'],
            'inclusions' => ['Vé economy', 'Thuế phí'], 'exclusions' => ['Ra đảo', 'Hành lý thêm'],
            'notes' => ['Chọn chuyến sáng nếu muốn kịp tàu chiều cùng ngày.'], 'attrs' => ['from' => 'SGN', 'to' => 'HPH', 'flight_time' => '2h05m'],
        ],
        [
            'code' => 'flight-transfer-catbi-catba', 'cluster' => 'flight', 'category_slug' => 'dua-don-san-bay-cat-bi', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Đưa đón sân bay Cát Bi → Cát Bà (xe + tàu/phà)', 'slug' => 'dua-don-san-bay-cat-bi-cat-ba',
            'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 65,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Cát Bi → đảo Cát Bà',
            'summary' => 'Door-to-island: đón sảnh đến, gộp vé qua đảo, trả tận bến Bèo hoặc khách sạn.',
            'highlights' => ['Một lần đặt', 'Theo dõi chuyến bay', 'Gộp vé tàu/phà', 'Hỗ trợ vali'],
            'inclusions' => ['Xe riêng', 'Vé qua đảo'], 'exclusions' => ['Chiều về', 'Phụ phí đêm'],
            'notes' => ['Gửi số hiệu chuyến bay trước 24 giờ.'], 'attrs' => ['from' => 'Sân bay Cát Bi', 'to' => 'Cát Bà', 'duration_hours' => 2],
        ],
        [
            'code' => 'flight-transfer-noibai-catba', 'cluster' => 'flight', 'category_slug' => 'dua-don-noi-bai-cat-ba', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Limousine Nội Bài → Cát Bà (gộp phà)', 'slug' => 'limousine-noi-bai-cat-ba',
            'price_from' => 420000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 143,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Nội Bài → Cát Bà',
            'summary' => 'Dành cho khách bay quốc tế vào Nội Bài — limousine gộp phà, ~3 giờ ra đảo.',
            'highlights' => ['Đón sảnh Nội Bài', 'Đã gộp phà', 'Wifi trên xe', 'Phù hợp đoàn'],
            'inclusions' => ['Limousine một chiều', 'Vé qua đảo'], 'exclusions' => ['Bay vào Nội Bài', 'Phụ phí đón đêm'],
            'notes' => ['Chuyến đêm có thể nghỉ Hà Nội 1 đêm rồi ra đảo sáng hôm sau.'], 'attrs' => ['from' => 'Nội Bài', 'to' => 'Cát Bà', 'duration_hours' => 3],
        ],
        [
            'code' => 'flight-combo-fly-island', 'cluster' => 'flight', 'category_slug' => 'combo-bay-ra-dao', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Combo vé bay + đưa đón + ra đảo Cát Bà', 'slug' => 'combo-ve-bay-dua-don-ra-dao-cat-ba',
            'price_from' => 2350000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 58,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Một đầu mối', 'location_label' => 'Hà Nội/Sài Gòn → Cát Bà',
            'summary' => 'Một báo giá: bay tới Hải Phòng, xe đón và tàu/phà ra đảo — không tự ghép từng khâu.',
            'highlights' => ['Một đầu mối', 'Tính giờ nối chuyến', 'Đổi ngày khi biển động', 'Phù hợp lần đầu'],
            'inclusions' => ['Vé bay economy một chiều', 'Transfer sân bay', 'Vé qua đảo'], 'exclusions' => ['Lưu trú', 'Tour vịnh'],
            'notes' => ['Giá thay đổi theo ngày bay — chốt sau khi xác nhận lịch.'], 'attrs' => ['from' => 'HAN / SGN', 'to' => 'Cát Bà'],
        ],

        // ── STAY — chỉ ngủ đặc trưng, không catalogue khách sạn (3) ─────────
        [
            'code' => 'stay-overnight-cruise-cabin', 'cluster' => 'stay', 'category_slug' => 'ngu-tren-vinh-du-thuyen', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Cabin du thuyền qua đêm vịnh Lan Hạ (đặt riêng)', 'slug' => 'cabin-du-thuyen-qua-dem-dat-rieng',
            'price_from' => 1800000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 54,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => 'Chỉ cabin + bữa cơ bản trên tàu — tự ghép hoạt động ban ngày hoặc đi cùng tour riêng.',
            'highlights' => ['Không bắt buộc tour ghép', 'Bữa sáng & tối cơ bản', 'Linh hoạt ghép kayak/lặn', 'Đổi ngày khi cấm tàu'],
            'inclusions' => ['Cabin 1 đêm', 'Bữa sáng & tối cơ bản'], 'exclusions' => ['Kayak, snorkel', 'Đồ uống', 'Xe bến tàu'],
            'notes' => ['Không phải khách sạn đất liền — xuất phát từ bến Bèo theo lịch tàu.'],
            'attrs' => ['property_type' => 'cabin', 'check_in' => '12:00', 'check_out' => '10:30'],
            'options' => [
                ['code' => 'cabin-twin', 'name' => 'Cabin twin', 'price_from' => 1800000, 'capacity' => 2],
                ['code' => 'cabin-dbl', 'name' => 'Cabin giường đôi', 'price_from' => 1950000, 'capacity' => 2],
            ],
        ],
        [
            'code' => 'stay-floating-bungalow-lanha', 'cluster' => 'stay', 'category_slug' => 'ngu-tren-vinh-du-thuyen', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Bungalow nổi vịnh Lan Hạ (1 đêm)', 'slug' => 'bungalow-noi-vinh-lan-ha',
            'price_from' => 3500000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 88,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => 'Ngủ trên mặt nước giữa núi đá vôi — kayak tại chỗ, số lượng hạn chế mỗi ngày.',
            'highlights' => ['View vịnh 360°', 'Kayak miễn phí', 'Tàu đưa đón từ Bến Bèo', 'Tối & sáng theo gói'],
            'inclusions' => ['Bungalow 1 đêm', 'Tàu đưa đón', 'Kayak', 'Bữa tối & sáng'], 'exclusions' => ['Đồ uống có cồn', 'Lặn chuyên sâu'],
            'notes' => ['Biển động có thể hoãn — không hứa giữ lịch khi cấm tàu.'],
            'attrs' => ['property_type' => 'floating', 'check_in' => '13:00', 'check_out' => '11:00'],
            'options' => [['code' => 'float-dbl', 'name' => 'Bungalow 2 khách', 'price_from' => 3500000, 'capacity' => 2]],
        ],
        [
            'code' => 'stay-viethai-homestay', 'cluster' => 'stay', 'category_slug' => 'homestay-viet-hai', 'zone_slug' => 'lang-viet-hai',
            'title' => 'Homestay làng Việt Hải (1 đêm)', 'slug' => 'homestay-viet-hai-vuon-quoc-gia',
            'price_from' => 400000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 73,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Làng Việt Hải, VQG Cát Bà',
            'summary' => 'Ngủ cùng người dân — không xe máy, ăn tối gia đình, thường ghép trekking hoặc thuyền vào làng.',
            'highlights' => ['Yên tĩnh tuyệt đối', 'Bữa tối & sáng nhà dân', 'Dễ ghép trekking', 'Trải nghiệm off-grid'],
            'inclusions' => ['Chỗ ngủ 1 đêm', 'Tối & sáng với chủ nhà'], 'exclusions' => ['Vào làng (thuyền/trekking)', 'Đồ uống'],
            'notes' => ['Điện máy phát — có thể cắt giờ đêm.'],
            'attrs' => ['property_type' => 'homestay', 'check_in' => 'Linh hoạt', 'check_out' => 'Linh hoạt'],
            'options' => [['code' => 'vh-room', 'name' => 'Phòng homestay 2 khách', 'price_from' => 400000, 'capacity' => 2]],
        ],

        // ── EXPERIENCE (12) ──────────────────────────────────────────────────
        [
            'code' => 'exp-lanha-day-boat', 'cluster' => 'experience', 'category_slug' => 'tour-ngay-vinh-lan-ha', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Tour ngày vịnh Lan Hạ — tàu gỗ & bãi tắm', 'slug' => 'tour-ngay-vinh-lan-ha-tau-go',
            'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 512,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => 'Tour ngày kinh điển: tàu gỗ, tắm bãi hoang sơ, ăn trưa hải sản — không ngủ đêm.',
            'highlights' => ['08:30–16:00', 'Ăn trưa trên tàu', 'Ghép nhóm nhỏ', 'Có private'],
            'inclusions' => ['Tàu cả ngày', 'Bữa trưa'], 'exclusions' => ['Kayak (đặt thêm)', 'Đồ uống'],
            'notes' => ['Có thể thêm Đảo Khỉ hoặc kayak.'], 'attrs' => ['duration_hours' => 8, 'activity' => 'bay_cruise'],
        ],
        [
            'code' => 'exp-kayak-lanha', 'cluster' => 'experience', 'category_slug' => 'kayak-snorkel-vinh', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Kayak hang Luồn & bãi kín vịnh Lan Hạ', 'slug' => 'kayak-vinh-lan-ha',
            'price_from' => 250000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 456,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => 'Chèo kayak đôi qua hang và bãi chỉ tiếp cận được bằng đường thuỷ.',
            'highlights' => ['Kayak 2 người + áo phao', 'HDV đi kèm nhóm 4+', 'Ghép tour ngày'],
            'inclusions' => ['Kayak & thiết bị', 'HDV (nhóm đủ người)'], 'exclusions' => ['Tàu ra vịnh'],
            'notes' => ['Không cần kinh nghiệm.'], 'attrs' => ['duration_hours' => 2, 'activity' => 'kayak'],
        ],
        [
            'code' => 'exp-snorkel-lanha', 'cluster' => 'experience', 'category_slug' => 'kayak-snorkel-vinh', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Snorkel Vạn Bội / Ba Trái Đào', 'slug' => 'lan-ngam-san-ho-cat-ba',
            'price_from' => 350000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 178,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => 'Lặn ống thở tại rạn san hô còn khá nguyên — tháng 4–8 nước trong nhất.',
            'highlights' => ['Ống thở đầy đủ', 'HDV bơi kèm', '2–3 điểm/snorkel'],
            'inclusions' => ['Thiết bị snorkel', 'HDV'], 'exclusions' => ['Tàu ra điểm'],
            'notes' => ['Biết bơi cơ bản.'], 'attrs' => ['duration_hours' => 2, 'activity' => 'snorkeling'],
        ],
        [
            'code' => 'exp-trek-viethai-full', 'cluster' => 'experience', 'category_slug' => 'trekking-vuon-quoc-gia', 'zone_slug' => 'vuon-quoc-gia',
            'title' => 'Trekking Việt Hải — Ao Ếch (cả ngày)', 'slug' => 'trekking-vqg-viet-hai-ca-ngay',
            'price_from' => 550000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 231,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'VQG Cát Bà — Việt Hải',
            'summary' => 'Cung 10km được đánh giá cao nhất — rừng nguyên sinh, Ao Ếch, ăn trưa làng.',
            'highlights' => ['Voọc Cát Bà (may mắn)', 'Ăn nhà dân', 'Có thuỷ một chiều'],
            'inclusions' => ['HDV', 'Vé VQG', 'Bữa trưa'], 'exclusions' => ['Xe đón', 'Thuyền về'],
            'notes' => ['Giày trekking bắt buộc.'], 'attrs' => ['duration_hours' => 7, 'activity' => 'trekking'],
        ],
        [
            'code' => 'exp-trek-ngulam', 'cluster' => 'experience', 'category_slug' => 'trekking-vuon-quoc-gia', 'zone_slug' => 'vuon-quoc-gia',
            'title' => 'Trekking đỉnh Ngự Lâm — view toàn đảo', 'slug' => 'trekking-dinh-ngu-lam',
            'price_from' => 300000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 94,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Gần trung tâm Cát Bà',
            'summary' => '3–4 giờ, dễ hơn Việt Hải — view thị trấn và vịnh từ trên cao.',
            'highlights' => ['Độ khó dễ', 'Buổi sáng/chiều', 'Phù hợp người mới'],
            'inclusions' => ['HDV', 'Nước'], 'exclusions' => ['Xe đón'],
            'notes' => ['Tránh nắng gắt giữa trưa.'], 'attrs' => ['duration_hours' => 4, 'activity' => 'trekking'],
        ],
        [
            'code' => 'exp-cai-beo-sampan', 'cluster' => 'experience', 'category_slug' => 'lang-chai-van-hoa', 'zone_slug' => 'cai-beo',
            'title' => 'Thuyền nan làng chài Cái Bèo', 'slug' => 'thuyen-nan-lang-chai-cai-beo',
            'price_from' => 200000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 124,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Làng chài Cái Bèo',
            'summary' => 'Dạo làng chài nổi, nghe kể di chỉ khảo cổ 6.000 năm — tour ngắn gần trung tâm.',
            'highlights' => ['Thuyền nan địa phương', '2–3 giờ', 'Ghép buổi chiều', 'Văn hoá làng chài'],
            'inclusions' => ['Thuyền nan', 'HDV'], 'exclusions' => ['Ăn uống'],
            'notes' => ['Có thể ghép hang Trung Trang.'], 'attrs' => ['duration_hours' => 2.5, 'activity' => 'culture'],
        ],
        [
            'code' => 'exp-squid-fishing-night', 'cluster' => 'experience', 'category_slug' => 'kinh-nghiem-dem', 'zone_slug' => 'cai-beo',
            'title' => 'Câu mực đêm vịnh Cái Bèo', 'slug' => 'cau-muc-dem-cai-beo',
            'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 49,
            'is_featured' => false, 'is_hot_deal' => true, 'discount_badge' => 'Ban đêm', 'location_label' => 'Làng chài Cái Bèo',
            'summary' => 'Ra vịnh ban đêm với ngư dân — đèn mực, dụng cụ đầy đủ, đồ nướng nhẹ trên thuyền.',
            'highlights' => ['Thuyền chài thật', '2.5–3 giờ', 'Đồ nướng nhẹ', 'Trải nghiệm địa phương'],
            'inclusions' => ['Thuyền', 'Cần câu', 'Đồ nướng nhẹ', 'Áo phao'], 'exclusions' => ['Đồ uống có cồn'],
            'notes' => ['Không đi khi cấm tàu đêm / sóng lớn.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'night_fishing'],
        ],
        [
            'code' => 'exp-monkey-island', 'cluster' => 'experience', 'category_slug' => 'dao-khi-tam-bien', 'zone_slug' => 'dao-khi',
            'title' => 'Đảo Khỉ — vé tàu khứ hồi & bãi tắm', 'slug' => 'dao-khi-cat-dua-ve-tau',
            'price_from' => 150000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 389,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Phổ biến', 'location_label' => 'Đảo Khỉ (Cát Dứa)',
            'summary' => 'Tàu ~20 phút từ Bến Bèo — tự do tắm biển, snorkel gần bờ, gặp khỉ hoang dã.',
            'highlights' => ['Khứ hồi trong ngày', 'Bãi cát trắng', 'Ghép snorkel'],
            'inclusions' => ['Tàu khứ hồi', 'Vé vào bãi'], 'exclusions' => ['Ăn uống', 'Ghế nằm'],
            'notes' => ['Không cho khỉ ăn đồ ngọt.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'beach'],
        ],
        [
            'code' => 'exp-sunset-lanha', 'cluster' => 'experience', 'category_slug' => 'kinh-nghiem-dem', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Hoàng hôn vịnh Lan Hạ — tàu nhỏ chiều tối', 'slug' => 'hoang-hon-vinh-lan-ha',
            'price_from' => 480000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 67,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => '15:00–19:30 — tàu nhóm tối đa 12 khách, đồ uống nhẹ, góc chụp hoàng hôn.',
            'highlights' => ['Nhóm nhỏ', 'Đồ uống chào mừng', 'Lãng mạn', 'Không cần ngủ đêm'],
            'inclusions' => ['Tàu', 'Đồ uống nhẹ', 'HDV'], 'exclusions' => ['Bữa tối'],
            'notes' => ['Phụ thuộc thời tiết nắng chiều.'], 'attrs' => ['duration_hours' => 4.5, 'activity' => 'sunset_cruise'],
        ],
        [
            'code' => 'exp-climbing-dws', 'cluster' => 'experience', 'category_slug' => 'leo-nui-phieu-luu', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Leo núi đá vôi & Deep Water Solo Lan Hạ', 'slug' => 'leo-nui-da-voi-deep-water-solo-lan-ha',
            'price_from' => 900000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 67,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Vách đá vịnh Lan Hạ',
            'summary' => 'HLV chứng nhận, thiết bị đầy đủ — leo có dây hoặc DWS nhảy xuống biển.',
            'highlights' => ['HLV quốc tế', 'Tàu riêng nhóm nhỏ', 'Cứu hộ trên nước'],
            'inclusions' => ['Thiết bị', 'HLV', 'Tàu', 'Trưa nhẹ'], 'exclusions' => ['Bảo hiểm thể thao'],
            'notes' => ['Biết bơi, từ 14 tuổi.'], 'attrs' => ['duration_hours' => 4, 'activity' => 'rock_climbing'],
        ],
        [
            'code' => 'exp-cycling-island', 'cluster' => 'experience', 'category_slug' => 'kham-pha-dao-bang-xe', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Đạp xe đồi chè & làng ven biển', 'slug' => 'dap-xe-xuyen-dao-cat-ba',
            'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 58,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Trung tâm Cát Bà',
            'summary' => '15–20km nhẹ qua làng ít khách — góc nhìn đảo khác hẳn tour vịnh.',
            'highlights' => ['Xe đạp + mũ', 'HDV bản địa', 'Buổi sáng mát'],
            'inclusions' => ['Xe đạp', 'Mũ', 'Nước', 'HDV'], 'exclusions' => ['Ăn uống'],
            'notes' => ['Có xe điện hỗ trợ người lớn tuổi.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'cycling'],
        ],
        [
            'code' => 'exp-langur-eco', 'cluster' => 'experience', 'category_slug' => 'trekking-vuon-quoc-gia', 'zone_slug' => 'vuon-quoc-gia',
            'title' => 'Tour săn voọc Cát Bả (eco, sáng sớm)', 'slug' => 'tour-san-vooc-cat-ba',
            'price_from' => 650000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 41,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Vườn quốc gia Cát Bà',
            'summary' => 'Xuất phát sáng sớm với HDV sinh thái — không đảm bảo gặp voọc nhưng tối ưu cơ hội.',
            'highlights' => ['HDV bảo tồn', 'Nhóm tối đa 8', 'Giải thích sinh thái', 'Không làm phiền động vật'],
            'inclusions' => ['HDV', 'Vé VQG', 'Nước'], 'exclusions' => ['Ăn sáng'],
            'notes' => ['Không dùng đèn pin chiếu thẳng voọc.'], 'attrs' => ['duration_hours' => 4, 'activity' => 'wildlife'],
        ],

        // ── OTHER (7) ────────────────────────────────────────────────────────
        [
            'code' => 'other-motorbike-rental', 'cluster' => 'other', 'category_slug' => 'thue-xe-noi-dao', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Thuê xe máy & xe điện Cát Bà', 'slug' => 'thue-xe-may-xe-dien-cat-ba',
            'price_from' => 150000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 502,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Giao tại trung tâm Cát Bà',
            'summary' => 'Thuê theo ngày — cách phổ biến nhất tự khám phá bãi Cát Cò, VQG, làng.',
            'highlights' => ['Giao tận khách sạn', 'Bản đồ cung đường', 'Xe điện không cần bằng A1'],
            'inclusions' => ['Xe/ngày', 'Mũ'], 'exclusions' => ['Xăng', 'Hư hỏng do người thuê'],
            'notes' => ['Cần CCCD giữ khi thuê xe máy.'],
            'attrs' => ['service_type' => 'vehicle_rental'],
            'options' => [
                ['code' => 'rent-xe-so', 'name' => 'Xe máy số/ngày', 'price_from' => 150000],
                ['code' => 'rent-xe-dien', 'name' => 'Xe điện/ngày', 'price_from' => 250000],
            ],
        ],
        [
            'code' => 'other-private-boat-charter', 'cluster' => 'other', 'category_slug' => 'thue-xe-noi-dao', 'zone_slug' => 'vinh-lan-ha',
            'title' => 'Thuê nguyên tàu gỗ vịnh Lan Hạ (charter)', 'slug' => 'thue-nguyen-tau-go-lan-ha',
            'price_from' => 4500000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 38,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Vịnh Lan Hạ',
            'summary' => 'Tàu riêng 10–20 khách — chủ động lịch trình, điểm dừng và giờ về.',
            'highlights' => ['Lịch linh hoạt', 'Phù hợp gia đình/đoàn', 'Có thể thêm kayak/snorkel'],
            'inclusions' => ['Nguyên tàu ngày', 'Thuyền trưởng'], 'exclusions' => ['Ăn uống', 'Vé thắng cảnh'],
            'notes' => ['Giá theo tàu 12 chỗ — báo lại theo ngày.'], 'attrs' => ['service_type' => 'boat_charter', 'duration_hours' => 8],
        ],
        [
            'code' => 'other-local-guide', 'cluster' => 'other', 'category_slug' => 'huong-dan-vien-porter', 'zone_slug' => 'vuon-quoc-gia',
            'title' => 'Hướng dẫn viên / porter địa phương', 'slug' => 'huong-dan-vien-porter-dia-phuong',
            'price_from' => 700000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 121,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Toàn đảo Cát Bà',
            'summary' => 'HDV trekking hoặc porter mang đồ — 8 giờ/ngày, tiếng Anh cơ bản.',
            'highlights' => ['Người bản địa', 'Porter khi cần', 'Tiếng Anh', 'Gia hạn theo giờ'],
            'inclusions' => ['HDV 8 giờ'], 'exclusions' => ['Vé tham quan', 'Ăn HDV'],
            'notes' => ['Lễ Tết phụ phí 20–30%.'], 'attrs' => ['service_type' => 'local_guide', 'languages' => ['en']],
        ],
        [
            'code' => 'other-luggage-storage', 'cluster' => 'other', 'category_slug' => 'tien-ich-du-lich', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Gửi hành lý bến tàu / khách sạn', 'slug' => 'gui-hanh-ly-ben-tau-khach-san',
            'price_from' => 100000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 88,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Bến Bèo, trung tâm',
            'summary' => 'Gửi vali khi đi trekking hoặc tour ngày — nhận lại cuối ngày.',
            'highlights' => ['Biên nhận', 'Chuyển khách sạn trong ngày', '≤20kg/vali'],
            'inclusions' => ['Ký gửi 1 vali/ngày'], 'exclusions' => ['Quá cân', 'Đồ dễ vỡ'],
            'notes' => ['Đặt trước 3 giờ.'], 'attrs' => ['service_type' => 'luggage_storage'],
        ],
        [
            'code' => 'other-medical-emergency', 'cluster' => 'other', 'category_slug' => 'tien-ich-du-lich', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Hotline y tế & hỗ trợ khẩn cấp 24/7', 'slug' => 'ho-tro-y-te-cap-cuu-cat-ba',
            'price_from' => 0, 'currency' => 'VND', 'rating' => 5.0, 'review_count' => 42,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Toàn đảo — 24/7',
            'summary' => 'Miễn phí cho khách đặt qua Hi Cát Bà — phối hợp trạm y tế đảo & bệnh viện Hải Phòng.',
            'highlights' => ['Hotline VI/EN', 'Mất giấy tờ', 'Tai nạn trekking/lặn', 'Không thay 115'],
            'inclusions' => ['Hotline', 'Điều phối cơ bản'], 'exclusions' => ['Viện phí', 'Vận chuyển y tế dài'],
            'notes' => ['Nguy hiểm tính mạng — gọi 115.'], 'attrs' => ['service_type' => 'medical_assistance', 'price_label' => 'Liên hệ'],
        ],
        [
            'code' => 'other-seafood-booking', 'cluster' => 'other', 'category_slug' => 'an-uong-dat-cho', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Đặt bàn chợ hải sản đêm bến Bèo', 'slug' => 'dat-ban-cho-hai-san-dem-ben-beo',
            'price_from' => 0, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 76,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Chợ hải sản bến Bèo',
            'summary' => 'Đặt trước bàn, gợi ý món theo mùa và ngân sách — tránh chờ lâu cuối tuần.',
            'highlights' => ['Giữ bàn giờ cao điểm', 'Gợi ý món', 'Hỗ trợ mua + chế biến', 'Không markup ẩn'],
            'inclusions' => ['Đặt bàn', 'Tư vấn món'], 'exclusions' => ['Tiền ăn tại chỗ'],
            'notes' => ['Dịch vụ đặt chỗ — thanh toán tại quán.'], 'attrs' => ['service_type' => 'restaurant_booking', 'price_label' => 'Miễn phí đặt bàn'],
        ],
        [
            'code' => 'other-spa-post-trek', 'cluster' => 'other', 'category_slug' => 'spa-thu-gian', 'zone_slug' => 'trung-tam-cat-ba',
            'title' => 'Spa & massage sau trekking / lặn', 'slug' => 'spa-massage-thu-gian-sau-trekking',
            'price_from' => 300000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 133,
            'is_featured' => false, 'is_hot_deal' => true, 'discount_badge' => 'Thư giãn', 'location_label' => 'Trung tâm Cát Bà',
            'summary' => 'Massage chân/body 60–90 phút — đối tác spa trên đảo được kiểm duyệt.',
            'highlights' => ['60 hoặc 90 phút', '9h–21h', 'Không ép mua thêm'],
            'inclusions' => ['Massage', 'Khăn, trà'], 'exclusions' => ['Tip'],
            'notes' => ['Đặt trước 2–3 giờ buổi tối.'], 'attrs' => ['service_type' => 'spa'],
            'options' => [['code' => 'spa-60', 'name' => '60 phút', 'price_from' => 300000], ['code' => 'spa-90', 'name' => '90 phút', 'price_from' => 420000]],
        ],
    ],

    'service_listing_faqs' => [
        ['q' => 'Giá "từ" trên trang dịch vụ có phải giá cố định không?', 'a' => 'Không — giá "từ" là mức tham khảo theo mùa thấp điểm hoặc loại tiêu chuẩn. Hi Cát Bà báo giá chính xác sau khi nhận ngày sử dụng, số lượng khách và yêu cầu cụ thể, trước khi bạn thanh toán.'],
        ['q' => 'Tôi có thể gộp vé ra đảo + tour vịnh + trekking trong một đơn không?', 'a' => 'Có. Chúng tôi thiết kế lịch trình tuỳ chỉnh — gộp tàu/phà, limousine, vé vui chơi, homestay Việt Hải hoặc cabin vịnh vào một báo giá minh bạch, một đầu mối chăm sóc. Catalogue không liệt kê khách sạn — nếu cần lưu trú đất liền, tư vấn viên gợi ý riêng theo ngân sách.'],
        ['q' => 'Chính sách hoàn/hủy khi tàu tạm ngưng do thời tiết xấu như thế nào?', 'a' => 'Vé tàu cao tốc/phà và tour vịnh thường được đổi ngày miễn phí hoặc hoàn 100% nếu cơ quan chức năng cấm tàu ra biển. Cabin du thuyền, bungalow nổi và homestay áp dụng điều kiện hoàn riêng, ghi rõ trên voucher.'],
        ['q' => 'Hi Cát Bà có bán vé tàu và vé máy bay trực tiếp trên website không?', 'a' => 'Website hiển thị tham khảo và form yêu cầu báo giá. Sau khi bạn gửi yêu cầu, tư vấn viên xác nhận chỗ, giá và gửi link thanh toán hoặc hóa đơn — đảm bảo không phí ẩn.'],
        ['q' => 'Dịch vụ hỗ trợ y tế/khẩn cấp 24/7 áp dụng khi nào?', 'a' => 'Miễn phí cho khách đã đặt tour hoặc dịch vụ qua Hi Cát Bà. Hotline hỗ trợ tai nạn khi trekking/lặn biển, mất giấy tờ và phối hợp bệnh viện tại Hải Phòng — số điện thoại ghi trên voucher xác nhận.'],
        ['q' => 'Tại sao không thấy danh mục khách sạn trên website?', 'a' => 'Hi Cát Bà tập trung tour, vịnh, trekking và kết nối ra đảo — lưu trú đất liền thay đổi theo mùa và sở thích cá nhân. Chúng tôi chỉ catalogue ngủ trên vịnh (cabin/bungalow nổi) và homestay Việt Hải; khách sạn/resort được tư vấn riêng khi bạn yêu cầu.'],
    ],
];

$__companySeed = [
    'name' => 'Hi Cát Bà',
    'legal_name' => 'Công ty TNHH Du lịch Hi Cát Bà',
    'tagline' => 'Đảo ngọc giữa vịnh di sản',
    'slogan' => '"Khám phá Cát Bà như người bản địa"',
    'license_number' => '0034/2019/TCDL-GPLHQT',
    'contact' => [
        'email' => 'hello@hicatba.com',
        'phone' => '+84 225 388 7777',
        'whatsapp' => '+84 912 000 777',
        'zalo' => '+84 225 388 7777',
        'hotline_label' => 'Hotline',
    ],
    'address' => [
        'street' => 'Số 12 đường 1/4, thị trấn Cát Bà',
        'locality' => 'Cát Hải, Hải Phòng',
        'region' => 'Hải Phòng',
        'postal' => '180000',
        'country' => 'VN',
    ],
    'social' => [
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/hicatba'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'play', 'url' => 'https://www.youtube.com/@hicatba'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'photo', 'url' => 'https://www.instagram.com/hicatba'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'share', 'url' => 'https://www.tiktok.com/@hicatba'],
    ],
    'schema' => [
        'available_language' => ['Vietnamese', 'English', 'Korean'],
        'contact_type' => 'customer service',
        'logo' => null,
    ],
    'footer' => [
        'copyright' => '© :year Hi Cát Bà. Giấy phép kinh doanh dịch vụ lữ hành số :license.',
        'show_dmca_badge' => true,
    ],
];

return array_merge(
    $__hicatbaSeed,
    $__servicesSeed,
    ['company' => $__companySeed],
    ['customize_form' => [
        'destinations_label' => [
            'vi' => 'Bạn muốn khám phá khu vực nào trên đảo?',




'en' => 'Which parts of the island would you like to explore?',
        ],
        'accommodation_label' => [
            'vi' => 'Bạn thích loại lưu trú nào?',




'en' => 'What kind of stay do you prefer?',
        ],
        'budget_note' => [
            'vi' => 'Ngân sách dự kiến (chưa gồm vé tàu/phà / đưa đón cửa ngõ)',




'en' => 'Estimated budget (excluding ferry tickets / gateway transfers)',
        ],
        'accommodation' => [
            'vi' => [
                'Nghỉ trên du thuyền / bungalow vịnh Lan Hạ',
                'Homestay làng Việt Hải',
                'Trung tâm đảo / bãi Cát Cò (tư vấn riêng, không đặt qua web)',
                'Nhờ tư vấn giúp tôi',
            ],




'en' => [
                'Overnight cruise / Lan Ha floating bungalow',
                'Viet Hai village homestay',
                'Town centre / Cat Co (advised separately, not booked on site)',
                'Please advise me',
            ],
        ],
    ]],
    ['nav' => [
        'about_group' => [
            'vi' => 'Về Hi Cát Bà',




'en' => 'About Hi Cat Ba',
        ],
        'tours' => [
            'label' => ['vi' => 'Tour',



'en' => 'Tours'],
        ],
        'cruise' => [
            'label' => ['vi' => 'Du thuyền',



'en' => 'Cruises'],
            'all_label' => ['vi' => 'Tất cả du thuyền',



'en' => 'All cruises'],
            'all_meta' => ['vi' => 'Xem toàn bộ lịch trình du thuyền & vịnh',



'en' => 'Browse all bay cruises & day trips'],
            'search_hint' => ['vi' => 'Tour, điểm đến, du thuyền, cẩm nang…',



'en' => 'Tours, places, cruises, guides…'],
            'search_placeholder' => ['vi' => 'Tìm tour, điểm đến, du thuyền, bài viết…',



'en' => 'Search tours, places, cruises, articles…'],
            'hub_title' => ['vi' => 'Du thuyền',



'en' => 'Cruises'],
            'hub_subtitle' => ['vi' => 'Du thuyền Lan Hạ, tour ngày vịnh và thuyền Việt Hải — chọn lịch trình phù hợp.',



'en' => 'Lan Ha cruises, bay day trips and Viet Hai boats — choose a fitting itinerary.'],
        ],
    ]],
    ['listing_hubs' => [
        'tours_hub' => [
            'vi' => ['seo_body' => 'Trang tour của :brand tập hợp hành trình trekking, vịnh và combo cửa ngõ Cát Bà — thiết kế bởi chuyên gia bản địa, tuỳ chỉnh theo lịch trình của bạn.'],




'en' => ['seo_body' => ':brand tours cover Cat Ba trekking, bay days and gateway combos — designed by local experts and tailored to your schedule.'],
        ],
        'cruises_hub' => [
            'vi' => ['seo_body' => 'Du thuyền Lan Hạ, tour ngày vịnh và thuyền Việt Hải từ :brand — chọn lịch trình ngủ đêm hoặc trong ngày phù hợp.'],




'en' => ['seo_body' => 'Lan Ha cruises, bay day trips and Viet Hai boats from :brand — overnight or day trips that fit your plan.'],
        ],
        'ferries_hub' => [
            'vi' => ['seo_body' => 'Vé tàu cao tốc, phà và limousine Hà Nội / Hải Phòng / Hạ Long — Cát Bà qua :brand: lịch rõ, đổi ngày linh hoạt, e-ticket.'],




'en' => ['seo_body' => 'High-speed boats, car ferries and limousines Hanoi / Hai Phong / Ha Long — Cat Ba via :brand: clear schedules, flexible changes, e-tickets.'],
        ],
        'flights_hub' => [
            'vi' => ['seo_body' => 'Vé máy bay và đưa đón sân bay kết nối Cát Bà qua :brand — báo giá nhanh, khớp lịch tour đảo.'],




'en' => ['seo_body' => 'Flights and airport transfers connecting to Cat Ba via :brand — fast quotes aligned with your island itinerary.'],
        ],
        'stays_hub' => [
            'vi' => ['seo_body' => 'Ngủ trên vịnh Lan Hạ — cabin du thuyền, bungalow nổi và homestay Việt Hải do :brand đặt hộ. Không catalogue khách sạn đất liền; cần gợi ý lưu trú trung tâm/Cát Cò, liên hệ tư vấn.'],




'en' => ['seo_body' => 'Overnight on Lan Ha Bay — cruise cabins, floating bungalows and Viet Hai homestays booked via :brand. No mainland hotel catalogue; ask us for town or Cat Co stay suggestions.'],
        ],
        'experiences_hub' => [
            'vi' => ['seo_body' => 'Trải nghiệm Cát Bà theo đúng lý do bạn đến: vịnh Lan Hạ, trekking vườn quốc gia, làng chài Cái Bèo, Đảo Khỉ và hoạt động ban đêm — đặt lẻ hoặc gộp tour qua :brand.'],




'en' => ['seo_body' => 'Cat Ba experiences by intent: Lan Ha Bay, national park trekking, Cai Beo fishing village, Monkey Island and evening activities — à la carte or bundled via :brand.'],
        ],
        'extras_hub' => [
            'vi' => ['seo_body' => 'Dịch vụ hỗ trợ trên đảo Cát Bà: thuê xe, HDV riêng và hỗ trợ trong chuyến đi cùng :brand.'],




'en' => ['seo_body' => 'On-island support on Cat Ba: vehicle hire, private guides and trip assistance with :brand.'],
        ],
    ]],
);