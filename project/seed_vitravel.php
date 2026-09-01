<?php

/**
 * ============================================================================
 * DỮ LIỆU ViTravel — profile `vitravel` (project:seed / migrate --seed)
 * ============================================================================
 *
 * Một file seed / một dự án: chứa đủ tours, company, catalogue dịch vụ (không require seed_company / seed_services).
 * Schema: project/README.md | Loader: App\Support\ProjectSeed::useProfile('vitravel')
 *
 * @return array<string, mixed>
 */

$__vitravelSeed = array (
  'meta' => 
  array (
    'schema' => 1,
    'brand' => 'ViTravel',
    'tagline' => 'Hài lòng hơn cả mong đợi',
    'admin' => 
    array (
      'email' => 'admin@vitravel.dev',
      'name' => 'Admin ViTravel',
      'password' => '111111',
    ),
    'country_codes' => 
    array (
      'viet-nam' => 'VN',
      'campuchia' => 'KH',
      'bali' => 'ID',
      'thai-lan' => 'TH',
      'lao' => 'LA',
      'tour-ket-hop' => 'COMBO',
    ),
    'exported_at' => '2026-07-31T04:16:51+00:00',
  ),
  'price_guest_types' =>
  array (
    0 =>
    array (
      'code' => 'adult',
      'sort' => 10,
      'age_min' => 12,
      'age_max' => 59,
      'name' =>
      array (
        'vi' => 'Người lớn',
        'en' => 'Adult',
      ),
    ),
    1 =>
    array (
      'code' => 'child',
      'sort' => 20,
      'age_min' => 2,
      'age_max' => 11,
      'name' =>
      array (
        'vi' => 'Trẻ em',
        'en' => 'Child',
      ),
    ),
    2 =>
    array (
      'code' => 'senior',
      'sort' => 30,
      'age_min' => 60,
      'age_max' => NULL,
      'name' =>
      array (
        'vi' => 'Cao tuổi (60+)',
        'en' => 'Senior (60+)',
      ),
    ),
  ),
  'price_table_defaults' =>
  array (
    'unit' => 'per_person',
    'notes' => 'Giá tham khảo theo người. Trẻ em và cao tuổi giảm theo bảng. Liên hệ để chốt báo giá chính xác.',
    'guest_multipliers' =>
    array (
      'adult' => 1,
      'child' => 0.7,
      'senior' => 0.85,
    ),
    'cluster_units' =>
    array (
      'stay' => 'per_room',
    ),
    'periods' =>
    array (
      0 =>
      array (
        'kind' => 'year',
        'label' => 'Giá năm {year}',
        'is_promo' => false,
        'priority' => 0,
      ),
      1 =>
      array (
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
  'content_tag_map' => 
  array (
    'Ăn gì, uống gì?' => 'where-to-eat',
    'Ngủ ở đâu?' => 'where-to-stay',
    'Chơi gì, xem gì?' => 'what-to-do',
    'Mẹo du lịch' => 'travel-tips',
    'Chuyến đi thế nào?' => 'trip-report',
    'Chọn tour nào?' => 'which-tour',
  ),
  'travel_styles' => 
  array (
    'long-duration' => 
    array (
      'vi' => 'Tour dài ngày',
      'en' => 'Long duration',
    ),
    'heritage-rich' => 
    array (
      'vi' => 'Nhiều di sản',
      'en' => 'Heritage rich',
    ),
    'nature-homestay' => 
    array (
      'vi' => 'Thiên nhiên & homestay',
      'en' => 'Nature & homestay',
    ),
    'culture-history' => 
    array (
      'vi' => 'Văn hoá & lịch sử',
      'en' => 'Culture & history',
    ),
    'balanced' => 
    array (
      'vi' => 'Kỳ nghỉ cân bằng',
      'en' => 'Balanced',
    ),
    'beach' => 
    array (
      'vi' => 'Nghỉ dưỡng biển',
      'en' => 'Beach',
    ),
    'honeymoon' => 
    array (
      'vi' => 'Trăng mật',
      'en' => 'Honeymoon',
    ),
    'family' => 
    array (
      'vi' => 'Gia đình',
      'en' => 'Family',
    ),
    'trekking' => 
    array (
      'vi' => 'Trekking & khám phá',
      'en' => 'Trekking',
    ),
    'multi-country-combo' => 
    array (
      'vi' => 'Tour kết hợp nhiều nước',
      'en' => 'Multi-country combo',
    ),
    'small-group' => 
    array (
      'vi' => 'Nhóm nhỏ',
      'en' => 'Small group',
    ),
  ),
  'content_tags' => 
  array (
    'where-to-eat' => 
    array (
      'vi' => 'Ăn uống ở đâu?',
      'en' => 'Where to eat & drink?',
    ),
    'where-to-stay' => 
    array (
      'vi' => 'Ở đâu?',
      'en' => 'Where to stay?',
    ),
    'what-to-do' => 
    array (
      'vi' => 'Làm gì & xem gì?',
      'en' => 'What to do & see?',
    ),
    'travel-tips' => 
    array (
      'vi' => 'Mẹo du lịch',
      'en' => 'Travel tips',
    ),
    'trip-report' => 
    array (
      'vi' => 'Cảm nhận chuyến đi',
      'en' => 'How was the trip?',
    ),
    'which-tour' => 
    array (
      'vi' => 'Chọn tour nào?',
      'en' => 'Which tour to choose?',
    ),
  ),
  'review_platforms' => 
  array (
    0 => 
    array (
      'code' => 'tripadvisor',
      'name' => 'Tripadvisor',
      'rating' => 4.9,
      'review_count' => 320,
      'sort' => 0,
      'quote' => 'Xếp hạng 5/5 từ hơn 900 đánh giá — Giải thưởng Travelers\' Choice 3 năm liên tiếp.',
      'link_label' => 'Đọc đánh giá trên Tripadvisor',
      'url' => 'https://www.tripadvisor.com',
    ),
    1 => 
    array (
      'code' => 'google',
      'name' => 'Google',
      'rating' => 4.8,
      'review_count' => 210,
      'sort' => 1,
      'quote' => '4.9/5 trên Google Maps với hơn 600 nhận xét từ du khách khắp thế giới.',
      'link_label' => 'Xem đánh giá trên Google',
      'url' => 'https://www.google.com/maps',
    ),
    2 => 
    array (
      'code' => 'trustpilot',
      'name' => 'Trustpilot',
      'rating' => 4.7,
      'review_count' => 95,
      'sort' => 2,
      'quote' => 'Điểm "Xuất sắc" trên Trustpilot — 96% khách hàng chấm 5 sao.',
      'link_label' => 'Đọc đánh giá trên Trustpilot',
      'url' => 'https://www.trustpilot.com',
    ),
  ),
  'cruise_types' => 
  array (
    0 => 
    array (
      'slug' => 'du-thuyen-ha-long',
      'name' => 'Du thuyền Hạ Long',
      'count' => 14,
      'image' => NULL,
      'imageHero' => NULL,
      'imageSrcset' => NULL,
      'sort' => 10,
    ),
    1 => 
    array (
      'slug' => 'du-thuyen-mekong',
      'name' => 'Du thuyền Mekong',
      'count' => 8,
      'image' => NULL,
      'imageHero' => NULL,
      'imageSrcset' => NULL,
      'sort' => 20,
    ),
    2 => 
    array (
      'slug' => 'du-thuyen-lan-ha',
      'name' => 'Du thuyền Lan Hạ',
      'count' => 6,
      'image' => NULL,
      'imageHero' => NULL,
      'imageSrcset' => NULL,
      'sort' => 30,
    ),
  ),
  'home_slides' => 
  array (
    0 => 
    array (
      'sort' => 0,
      'text_align' => 'center',
      'link_url' => '/tours/viet-nam',
      'vi' => 
      array (
        'title' => 'Du lịch Việt Nam',
        'title_accent' => 'theo cách của bạn',
        'description' => 'Tour trọn gói & hành trình riêng qua Hạ Long, Sa Pa, Ninh Bình, Hà Giang — thiết kế bởi chuyên gia bản địa.',
        'button_label' => 'Khám phá tour Việt Nam',
        'image_alt' => 'Vịnh Hạ Long lúc hoàng hôn',
      ),
      'en' => 
      array (
        'title' => 'Vietnam Travel',
        'title_accent' => 'your way',
        'description' => 'Fully inclusive tours & private journeys through Halong, Sapa, Ninh Binh and Ha Giang — designed by local experts.',
        'button_label' => 'Explore Vietnam tours',
        'image_alt' => 'Halong Bay at sunset',
      ),
    ),
    1 => 
    array (
      'sort' => 1,
      'text_align' => 'center',
      'link_url' => '/tours/campuchia',
      'vi' => 
      array (
        'title' => 'Campuchia huyền bí',
        'title_accent' => 'Angkor & Tonlé Sap',
        'description' => 'Khám phá đền Angkor, Phnom Penh và làng nổi — hành trình Đông Dương đầy cảm xúc.',
        'button_label' => 'Xem tour Campuchia',
        'image_alt' => 'Angkor Wat bình minh',
      ),
      'en' => 
      array (
        'title' => 'Mystical Cambodia',
        'title_accent' => 'Angkor & Tonlé Sap',
        'description' => 'Discover Angkor temples, Phnom Penh and floating villages — an emotional Indochina journey.',
        'button_label' => 'View Cambodia tours',
        'image_alt' => 'Angkor Wat at sunrise',
      ),
    ),
  ),
  'countries' => 
  array (
    0 => 
    array (
      'slug' => 'viet-nam',
      'name' => 'Việt Nam',
      'size' => 'large',
      'tourCount' => 42,
      'tagline' => 'Từ vịnh Hạ Long tới đồng bằng sông Cửu Long',
    ),
    1 => 
    array (
      'slug' => 'campuchia',
      'name' => 'Campuchia',
      'size' => 'normal',
      'tourCount' => 18,
      'tagline' => 'Angkor huyền bí & biển hồ Tonlé Sap',
    ),
    2 => 
    array (
      'slug' => 'bali',
      'name' => 'Bali (Indonesia)',
      'size' => 'normal',
      'tourCount' => 9,
      'tagline' => 'Đảo của các vị thần',
    ),
    3 => 
    array (
      'slug' => 'thai-lan',
      'name' => 'Thái Lan',
      'size' => 'normal',
      'tourCount' => 21,
      'tagline' => 'Xứ sở chùa vàng',
    ),
    4 => 
    array (
      'slug' => 'lao',
      'name' => 'Lào',
      'size' => 'normal',
      'tourCount' => 12,
      'tagline' => 'Nhịp sống chậm bên dòng Mekong',
    ),
    5 => 
    array (
      'slug' => 'tour-ket-hop',
      'name' => 'Tour kết hợp',
      'size' => 'normal',
      'tourCount' => 15,
      'tagline' => 'Đông Dương trong một hành trình',
    ),
  ),
  'country_translations' => 
  array (
    'viet-nam' => 
    array (
      'vi' => 'Việt Nam',
      'en' => 'Vietnam',
      'tagline' => 
      array (
        'vi' => 'Từ vịnh Hạ Long tới đồng bằng sông Cửu Long',
        'en' => 'From Halong Bay to the Mekong Delta',
      ),
    ),
    'campuchia' => 
    array (
      'vi' => 'Campuchia',
      'en' => 'Cambodia',
      'tagline' => 
      array (
        'vi' => 'Angkor huyền bí & biển hồ Tonlé Sap',
        'en' => 'Mystical Angkor & Tonlé Sap',
      ),
    ),
    'bali' => 
    array (
      'vi' => 'Bali (Indonesia)',
      'en' => 'Bali (Indonesia)',
      'tagline' => 
      array (
        'vi' => 'Đảo của các vị thần',
        'en' => 'Island of the Gods',
      ),
    ),
    'thai-lan' => 
    array (
      'vi' => 'Thái Lan',
      'en' => 'Thailand',
      'tagline' => 
      array (
        'vi' => 'Xứ sở chùa vàng',
        'en' => 'Land of golden temples',
      ),
    ),
    'lao' => 
    array (
      'vi' => 'Lào',
      'en' => 'Laos',
      'tagline' => 
      array (
        'vi' => 'Nhịp sống chậm bên dòng Mekong',
        'en' => 'Slow life along the Mekong',
      ),
    ),
    'tour-ket-hop' => 
    array (
      'vi' => 'Tour kết hợp',
      'en' => 'Multi-country Tours',
      'tagline' => 
      array (
        'vi' => 'Đông Dương trong một hành trình',
        'en' => 'Indochina in one journey',
      ),
    ),
  ),
  'tours' => 
  array (
    0 => 
    array (
      'slug' => 'viet-nam-10-ngay-di-san-mien-bac',
      'title' => 'Việt Nam 10 ngày — Di sản miền Bắc & vịnh Hạ Long',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'tourCode' => 'VN10D-01',
      'duration' => '10 ngày 9 đêm',
      'days' => 10,
      'rating' => 5.0,
      'reviewCount' => 128,
      'badge' => 'Ưu đãi đặc biệt',
      'featured' => true,
      'styles' => 
      array (
        0 => 'heritage-rich',
        1 => 'culture-history',
        2 => 'balanced',
      ),
      'quote' => 
      array (
        'text' => 'Hành trình tuyệt vời, hướng dẫn viên tận tâm và lịch trình rất hợp lý cho gia đình tôi.',
        'author' => 'Chị Minh Anh',
      ),
      'places' => 
      array (
        0 => 'Hà Nội',
        1 => 'Ninh Bình',
        2 => 'Vịnh Hạ Long',
        3 => 'Sa Pa',
        4 => 'Mai Châu',
      ),
      'start' => 'Hà Nội',
      'end' => 'Hà Nội',
      'highlightsIntro' => 'Tour Việt Nam 10 ngày là lựa chọn lý tưởng cho lần đầu khám phá miền Bắc: đủ chậm để cảm nhận văn hoá bản địa, đủ trọn vẹn để đi qua những di sản nổi tiếng nhất.',
      'highlights' => 
      array (
        0 => 'Ngủ đêm trên du thuyền giữa vịnh Hạ Long — di sản thiên nhiên thế giới',
        1 => 'Đạp xe qua làng cổ và chèo thuyền nan ở Tràng An, Ninh Bình',
        2 => 'Trekking bản Cát Cát – Lao Chải giữa ruộng bậc thang Sa Pa',
        3 => 'Một đêm homestay nhà sàn người Thái ở Mai Châu',
        4 => 'Ẩm thực phố cổ Hà Nội cùng nghệ nhân địa phương',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Hà Nội — Chào đón & dạo phố cổ',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Đón sân bay Nội Bài, về khách sạn nghỉ ngơi. Chiều đi bộ 36 phố phường, thưởng thức cà phê trứng và bữa tối ẩm thực đường phố cùng hướng dẫn viên.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Hà Nội — Thành phố ngàn năm văn hiến',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'walking',
            1 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Thăm Văn Miếu – Quốc Tử Giám, Hoàng thành Thăng Long, chùa Trấn Quốc. Chiều tự do khám phá hồ Gươm, xem múa rối nước.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Ninh Bình — Tràng An & Hang Múa',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
            1 => 'boat',
            2 => 'bike',
          ),
          'overnight' => 'Ninh Bình',
          'content' => 'Chèo thuyền nan xuyên hang động Tràng An, đạp xe đường làng Tam Cốc, leo 500 bậc Hang Múa ngắm hoàng hôn toàn cảnh.',
        ),
        3 => 
        array (
          'day' => 4,
          'title' => 'Vịnh Hạ Long — Lên du thuyền',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'car',
            1 => 'cruise',
          ),
          'overnight' => 'Du thuyền Hạ Long',
          'content' => 'Nhận cabin, ăn trưa trên du thuyền giữa vịnh. Chèo kayak khu Luồn Bò, lớp học nấu ăn hoàng hôn trên boong tàu.',
        ),
        4 => 
        array (
          'day' => 5,
          'title' => 'Hạ Long — Hang Sửng Sốt — về Hà Nội',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'cruise',
            1 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Thái cực quyền đón bình minh, thăm hang Sửng Sốt, trả phòng và về Hà Nội, tối tự do.',
        ),
        5 => 
        array (
          'day' => 6,
          'title' => 'Sa Pa — Thị trấn trong sương',
          'meals' => 'Sáng; Tối',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Sa Pa',
          'content' => 'Di chuyển cao tốc Hà Nội – Lào Cai. Chiều dạo nhà thờ đá, chợ Sa Pa; tối thưởng thức lẩu cá hồi bản địa.',
        ),
        6 => 
        array (
          'day' => 7,
          'title' => 'Sa Pa — Trekking Lao Chải – Tả Van',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'trekking',
          ),
          'overnight' => 'Sa Pa',
          'content' => 'Trekking 8km qua ruộng bậc thang, ăn trưa tại nhà người H\'Mông, giao lưu văn hoá bản địa.',
        ),
        7 => 
        array (
          'day' => 8,
          'title' => 'Mai Châu — Thung lũng xanh',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'car',
            1 => 'bike',
          ),
          'overnight' => 'Homestay Mai Châu',
          'content' => 'Về Mai Châu, đạp xe bản Lác – Pom Coọng, đêm văn nghệ xoè Thái và ngủ nhà sàn.',
        ),
        8 => 
        array (
          'day' => 9,
          'title' => 'Hà Nội — Ngày tự do & mua sắm',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Về Hà Nội, chiều tự do mua quà: lụa Vạn Phúc, cà phê, đồ thủ công. Tối tiệc chia tay.',
        ),
        9 => 
        array (
          'day' => 10,
          'title' => 'Tạm biệt Việt Nam',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
            1 => 'plane',
          ),
          'overnight' => NULL,
          'content' => 'Xe đưa ra sân bay Nội Bài. Kết thúc hành trình — hẹn gặp lại!',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Khách sạn 4* trung tâm + du thuyền cabin ban công + homestay tiêu chuẩn',
        1 => 'Toàn bộ bữa ăn theo chương trình (B/L/D)',
        2 => 'Xe riêng đời mới, lái xe kinh nghiệm',
        3 => 'Hướng dẫn viên tiếng Việt/Anh suốt tuyến',
        4 => 'Vé tham quan, thuyền nan, kayak, vé tàu hỏa leo núi',
        5 => 'Nước uống & khăn lạnh mỗi ngày trên xe',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay quốc tế đến/đi Việt Nam',
        1 => 'Đồ uống ngoài chương trình, chi phí cá nhân',
        2 => 'Tiền tip cho hướng dẫn viên và lái xe',
        3 => 'Bảo hiểm du lịch (khuyến nghị mua)',
      ),
      'notes' => 
      array (
        0 => 'Lịch trình có thể thay đổi thứ tự tuỳ điều kiện thời tiết, vẫn đảm bảo đầy đủ điểm tham quan.',
        1 => 'Tour ghép nhóm nhỏ tối đa 12 khách; có thể đặt tour riêng (private) với phụ phí.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Thời điểm nào đẹp nhất để đi tour miền Bắc 10 ngày?',
          'a' => 'Tháng 9 – 11 và tháng 3 – 5 là hai khoảng thời gian lý tưởng: trời khô ráo, mát mẻ, lúa chín vàng ở Sa Pa vào cuối tháng 9.',
        ),
        1 => 
        array (
          'q' => 'Tour có phù hợp với trẻ nhỏ và người lớn tuổi không?',
          'a' => 'Có. Ngày trekking Sa Pa có phương án đi xe thay thế; du thuyền và homestay đều an toàn cho trẻ em từ 4 tuổi.',
        ),
        2 => 
        array (
          'q' => 'Tôi có thể tùy chỉnh lịch trình không?',
          'a' => 'Hoàn toàn được — hãy dùng form "Thiết kế tour riêng", chuyên gia của chúng tôi sẽ điều chỉnh theo nhu cầu trong 24 giờ.',
        ),
      ),
      'galleryCount' => 6,
      'priceFrom' => 28000000.0,
      'currency' => 'VND',
      'price_table' =>
      array (
        'currency' => 'VND',
        'unit' => 'per_person',
        'variants' =>
        array (
          0 =>
          array (
            'code' => 'standard',
            'name' => 'Tiêu chuẩn',
            'source' => 'custom',
            'sort' => 0,
            'is_active' => true,
          ),
        ),
        'periods' =>
        array (
          0 =>
          array (
            'kind' => 'year',
            'year' => 2026,
            'label' => 'Giá năm 2026',
            'is_promo' => false,
            'priority' => 0,
            'rates' =>
            array (
              0 =>
              array (
                'variant_code' => 'standard',
                'guest_type_code' => 'adult',
                'amount' => 28000000,
              ),
              1 =>
              array (
                'variant_code' => 'standard',
                'guest_type_code' => 'child',
                'amount' => 19600000,
              ),
              2 =>
              array (
                'variant_code' => 'standard',
                'guest_type_code' => 'senior',
                'amount' => 23800000,
              ),
            ),
          ),
          1 =>
          array (
            'kind' => 'range',
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-08-31',
            'label' => 'Ưu đãi hè 2026',
            'is_promo' => true,
            'priority' => 10,
            'rates' =>
            array (
              0 =>
              array (
                'variant_code' => 'standard',
                'guest_type_code' => 'adult',
                'amount' => 25200000,
                'compare_at_amount' => 28000000,
              ),
              1 =>
              array (
                'variant_code' => 'standard',
                'guest_type_code' => 'child',
                'amount' => 17640000,
                'compare_at_amount' => 19600000,
              ),
              2 =>
              array (
                'variant_code' => 'standard',
                'guest_type_code' => 'senior',
                'amount' => 21420000,
                'compare_at_amount' => 23800000,
              ),
            ),
          ),
        ),
      ),
    ),
    1 => 
    array (
      'slug' => 'viet-nam-2-tuan-bac-trung-nam',
      'title' => 'Việt Nam 2 tuần — Xuyên Việt Bắc Trung Nam',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'tourCode' => 'VN14D-02',
      'duration' => '14 ngày 13 đêm',
      'days' => 14,
      'rating' => 4.9,
      'reviewCount' => 96,
      'badge' => NULL,
      'featured' => true,
      'styles' => 
      array (
        0 => 'long-duration',
        1 => 'heritage-rich',
        2 => 'balanced',
      ),
      'quote' => 
      array (
        'text' => 'Hai tuần hoàn hảo — từ Hạ Long tới Mekong, mọi khâu tổ chức đều chỉn chu.',
        'author' => 'Anh Quốc Bảo',
      ),
      'places' => 
      array (
        0 => 'Hà Nội',
        1 => 'Hạ Long',
        2 => 'Huế',
        3 => 'Hội An',
        4 => 'TP. Hồ Chí Minh',
        5 => 'Cần Thơ',
      ),
      'start' => 'Hà Nội',
      'end' => 'TP. Hồ Chí Minh',
      'highlightsIntro' => 'Hành trình xuyên Việt kinh điển dành cho ai muốn thấy trọn vẹn ba miền trong một chuyến đi.',
      'highlights' => 
      array (
        0 => 'Ngủ đêm du thuyền vịnh Hạ Long',
        1 => 'Kinh thành Huế & thuyền rồng sông Hương',
        2 => 'Phố cổ Hội An về đêm với đèn lồng',
        3 => 'Chợ nổi Cái Răng lúc bình minh',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Hà Nội — Xin chào Việt Nam',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Đón sân bay, city tour buổi chiều và bữa tối chào mừng.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Hà Nội — Vịnh Hạ Long',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'car',
            1 => 'cruise',
          ),
          'overnight' => 'Du thuyền',
          'content' => 'Lên du thuyền, kayak và lớp nấu ăn trên boong.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Hạ Long — bay vào Huế',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'cruise',
            1 => 'plane',
          ),
          'overnight' => 'Huế',
          'content' => 'Ngắm bình minh trên vịnh, chiều bay vào Huế.',
        ),
        3 => 
        array (
          'day' => 4,
          'title' => 'Huế — Kinh thành & lăng tẩm',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
            1 => 'boat',
          ),
          'overnight' => 'Huế',
          'content' => 'Đại Nội, lăng Minh Mạng, nghe ca Huế trên sông Hương.',
        ),
        4 => 
        array (
          'day' => 5,
          'title' => 'Đèo Hải Vân — Hội An',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Hội An',
          'content' => 'Vượt đèo Hải Vân, dừng chân Lăng Cô, chiều dạo phố cổ Hội An.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Khách sạn 4* + du thuyền',
        1 => 'Bữa ăn theo chương trình',
        2 => 'Vé máy bay nội địa 2 chặng',
        3 => 'Hướng dẫn viên suốt tuyến',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay quốc tế',
        1 => 'Chi phí cá nhân',
        2 => 'Tip',
      ),
      'notes' => 
      array (
        0 => 'Lịch trình đầy đủ 14 ngày sẽ gửi kèm báo giá chi tiết.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Có bao nhiêu chặng bay nội địa trong tour?',
          'a' => 'Hai chặng: Hải Phòng/Hà Nội – Huế và Đà Nẵng – TP.HCM, đã bao gồm trong giá tour.',
        ),
        1 => 
        array (
          'q' => '14 ngày có đủ để đi hết ba miền?',
          'a' => 'Đủ cho các điểm nổi bật nhất. Nếu muốn thêm Phú Quốc hoặc Đà Lạt, nên cân nhắc lịch trình 3 tuần.',
        ),
      ),
      'galleryCount' => 5,
      'priceFrom' => 39200000.0,
      'currency' => 'VND',
    ),
    2 => 
    array (
      'slug' => 'viet-nam-campuchia-15-ngay',
      'title' => 'Việt Nam & Campuchia 15 ngày — Mekong nối hai miền di sản',
      'countrySlug' => 'tour-ket-hop',
      'countrySlugs' => 
      array (
        0 => 'tour-ket-hop',
        1 => 'viet-nam',
        2 => 'campuchia',
      ),
      'country' => 'Tour kết hợp',
      'tourCode' => 'VNKH15D-03',
      'duration' => '15 ngày 14 đêm',
      'days' => 15,
      'rating' => 5.0,
      'reviewCount' => 74,
      'badge' => 'Bán chạy nhất',
      'featured' => true,
      'styles' => 
      array (
        0 => 'multi-country-combo',
        1 => 'long-duration',
        2 => 'heritage-rich',
      ),
      'quote' => 
      array (
        'text' => 'Angkor Wat lúc bình minh là khoảnh khắc không thể quên. Cảm ơn đội ngũ đã sắp xếp hoàn hảo!',
        'author' => 'Cô Thanh Hà',
      ),
      'places' => 
      array (
        0 => 'Hà Nội',
        1 => 'Hạ Long',
        2 => 'Hội An',
        3 => 'TP. Hồ Chí Minh',
        4 => 'Phnom Penh',
        5 => 'Siem Reap',
      ),
      'start' => 'Hà Nội',
      'end' => 'Siem Reap',
      'highlightsIntro' => 'Kết hợp tinh hoa Việt Nam với quần thể Angkor kỳ vĩ — hành trình được yêu cầu nhiều nhất của chúng tôi.',
      'highlights' => 
      array (
        0 => 'Bình minh Angkor Wat & nụ cười Bayon',
        1 => 'Du thuyền trên sông Mekong qua biên giới',
        2 => 'Phố cổ Hội An và ẩm thực miền Trung',
        3 => 'Cung điện Hoàng gia Phnom Penh',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Hà Nội — Điểm khởi đầu',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Đón sân bay, nhận phòng, tối dạo hồ Gươm.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Vịnh Hạ Long',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'car',
            1 => 'cruise',
          ),
          'overnight' => 'Du thuyền',
          'content' => 'Ngủ đêm trên vịnh di sản.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Bay vào Đà Nẵng — Hội An',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Hội An',
          'content' => 'Chiều thả đèn hoa đăng sông Hoài.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Khách sạn 4-5*',
        1 => 'Vé máy bay nội địa & liên tuyến',
        2 => 'Visa Campuchia',
        3 => 'Hướng dẫn viên hai nước',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay quốc tế',
        1 => 'Chi phí cá nhân',
      ),
      'notes' => 
      array (
        0 => 'Hộ chiếu cần còn hạn ít nhất 6 tháng.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Thủ tục visa Campuchia thế nào?',
          'a' => 'Chúng tôi hỗ trợ e-visa trọn gói, chỉ cần ảnh hộ chiếu — phí đã bao gồm trong giá tour.',
        ),
      ),
      'galleryCount' => 5,
      'priceFrom' => 42000000.0,
      'currency' => 'VND',
    ),
    3 => 
    array (
      'slug' => 'sa-pa-trekking-4-ngay',
      'title' => 'Sa Pa 4 ngày — Trekking bản làng & ruộng bậc thang',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'tourCode' => 'VN4D-04',
      'duration' => '4 ngày 3 đêm',
      'days' => 4,
      'rating' => 4.8,
      'reviewCount' => 52,
      'badge' => NULL,
      'featured' => false,
      'styles' => 
      array (
        0 => 'trekking',
        1 => 'nature-homestay',
        2 => 'small-group',
      ),
      'quote' => 
      array (
        'text' => 'Đêm homestay ở Tả Van ấm áp như ở nhà. Trải nghiệm trekking đáng giá từng bước chân.',
        'author' => 'Bạn Hoài Nam',
      ),
      'places' => 
      array (
        0 => 'Hà Nội',
        1 => 'Sa Pa',
        2 => 'Lao Chải',
        3 => 'Tả Van',
        4 => 'Bản Hồ',
      ),
      'start' => 'Hà Nội',
      'end' => 'Hà Nội',
      'highlightsIntro' => 'Dành cho đôi chân ưa khám phá: 3 ngày trekking xuyên thung lũng Mường Hoa với 2 đêm homestay bản địa.',
      'highlights' => 
      array (
        0 => 'Trekking 25km qua 5 bản làng',
        1 => 'Homestay người H\'Mông & người Giáy',
        2 => 'Chinh phục đồi Fansipan bằng cáp treo (tuỳ chọn)',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Hà Nội — Sa Pa',
          'meals' => 'Trưa; Tối',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Sa Pa',
          'content' => 'Khởi hành sớm, chiều làm quen thị trấn.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Lao Chải — Tả Van',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'trekking',
          ),
          'overnight' => 'Homestay Tả Van',
          'content' => 'Trekking 10km men theo suối Mường Hoa.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Tả Van — Bản Hồ',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'trekking',
          ),
          'overnight' => 'Homestay Bản Hồ',
          'content' => 'Băng rừng trúc, thác Lavie, tắm suối nước nóng.',
        ),
        3 => 
        array (
          'day' => 4,
          'title' => 'Về Hà Nội',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => NULL,
          'content' => 'Sáng thư giãn, trưa khởi hành về Hà Nội.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Xe limousine khứ hồi',
        1 => 'Homestay + khách sạn 3*',
        2 => 'Porter hành lý khi trekking',
      ),
      'exclusions' => 
      array (
        0 => 'Cáp treo Fansipan',
        1 => 'Đồ uống',
      ),
      'notes' => 
      array (
        0 => 'Cần giày trekking chống trượt; độ khó trung bình.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Tôi chưa từng trekking, có tham gia được không?',
          'a' => 'Được — cung đường được thiết kế cho người mới, có xe hỗ trợ tại các điểm giữa chặng.',
        ),
      ),
      'galleryCount' => 4,
      'priceFrom' => 11200000.0,
      'currency' => 'VND',
    ),
    4 => 
    array (
      'slug' => 'viet-nam-3-tuan-tron-ven',
      'title' => 'Việt Nam 3 tuần — Trọn vẹn từ địa đầu tới đất mũi',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'tourCode' => 'VN21D-05',
      'duration' => '21 ngày 20 đêm',
      'days' => 21,
      'rating' => 5.0,
      'reviewCount' => 38,
      'badge' => 'Ưu đãi đặc biệt',
      'featured' => false,
      'styles' => 
      array (
        0 => 'long-duration',
        1 => 'balanced',
        2 => 'nature-homestay',
      ),
      'quote' => 
      array (
        'text' => 'Ba tuần đi chậm, ở sâu — đúng chất một chuyến đi để đời.',
        'author' => 'Ông Văn Thịnh',
      ),
      'places' => 
      array (
        0 => 'Hà Giang',
        1 => 'Hà Nội',
        2 => 'Huế',
        3 => 'Hội An',
        4 => 'Đà Lạt',
        5 => 'TP. Hồ Chí Minh',
        6 => 'Cà Mau',
      ),
      'start' => 'Hà Nội',
      'end' => 'TP. Hồ Chí Minh',
      'highlightsIntro' => 'Lịch trình sâu nhất của chúng tôi: đèo Mã Pí Lèng, cố đô Huế, cao nguyên Đà Lạt và rừng ngập mặn Cà Mau.',
      'highlights' => 
      array (
        0 => 'Vòng cung Hà Giang 3 ngày',
        1 => 'Đêm cắm trại Đà Lạt',
        2 => 'Xuồng ba lá rừng U Minh',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Hà Nội — Khởi hành',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Hà Nội',
          'content' => 'Đón khách, họp đoàn, tiệc chào mừng.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Hà Giang — Cổng trời Quản Bạ',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Hà Giang',
          'content' => 'Ngược quốc lộ 2 lên cao nguyên đá.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Khách sạn 4* & homestay tuyển chọn',
        1 => '3 chặng bay nội địa',
        2 => 'Hướng dẫn viên chuyên tuyến',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay quốc tế',
        1 => 'Chi phí cá nhân',
      ),
      'notes' => 
      array (
        0 => 'Lịch trình chi tiết 21 ngày gửi kèm báo giá.',
      ),
      'faqs' => 
      array (
      ),
      'galleryCount' => 5,
      'priceFrom' => 58800000.0,
      'currency' => 'VND',
    ),
    5 => 
    array (
      'slug' => 'phu-quoc-nghi-duong-5-ngay',
      'title' => 'Phú Quốc 5 ngày — Nghỉ dưỡng biển & khám phá đảo ngọc',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'tourCode' => 'VN5D-06',
      'duration' => '5 ngày 4 đêm',
      'days' => 5,
      'rating' => 4.9,
      'reviewCount' => 61,
      'badge' => NULL,
      'featured' => false,
      'styles' => 
      array (
        0 => 'beach',
        1 => 'honeymoon',
        2 => 'family',
      ),
      'quote' => 
      array (
        'text' => 'Kỳ trăng mật hoàn hảo — resort tuyệt đẹp và tour 4 đảo rất vui.',
        'author' => 'Vợ chồng Tú & Ngân',
      ),
      'places' => 
      array (
        0 => 'Phú Quốc',
        1 => 'Hòn Thơm',
        2 => 'Rạch Vẹm',
        3 => 'Bãi Sao',
      ),
      'start' => 'Phú Quốc',
      'end' => 'Phú Quốc',
      'highlightsIntro' => 'Resort ven biển, cano 4 đảo, lặn ngắm san hô và hoàng hôn Sunset Town.',
      'highlights' => 
      array (
        0 => 'Cáp treo Hòn Thơm dài nhất thế giới',
        1 => 'Lặn ngắm san hô Nam đảo',
        2 => 'Chợ đêm Phú Quốc',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Đảo ngọc chào đón',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Phú Quốc',
          'content' => 'Nhận resort, chiều tự do tắm biển.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Cano 4 đảo phía Nam',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'boat',
          ),
          'overnight' => 'Phú Quốc',
          'content' => 'Lặn san hô, câu cá, BBQ hải sản trên đảo.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Resort 5* sát biển',
        1 => 'Tour cano 4 đảo',
        2 => 'Đưa đón sân bay',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay',
        1 => 'Spa & minibar',
      ),
      'notes' => 
      array (
        0 => 'Mùa đẹp nhất: tháng 11 – tháng 4.',
      ),
      'faqs' => 
      array (
      ),
      'galleryCount' => 4,
      'priceFrom' => 14000000.0,
      'currency' => 'VND',
    ),
    6 => 
    array (
      'slug' => 'bali-7-ngay-ubud-bien',
      'title' => 'Bali 7 ngày — Ubud, đền thiêng & biển Seminyak',
      'countrySlug' => 'bali',
      'country' => 'Bali (Indonesia)',
      'tourCode' => 'BL7D-01',
      'duration' => '7 ngày 6 đêm',
      'days' => 7,
      'rating' => 4.9,
      'reviewCount' => 47,
      'badge' => 'Mới',
      'featured' => false,
      'styles' => 
      array (
        0 => 'culture-history',
        1 => 'beach',
        2 => 'balanced',
        3 => 'honeymoon',
      ),
      'quote' => 
      array (
        'text' => 'Ubud rất thơ, Tanah Lot lúc hoàng hôn đẹp không tưởng. Cảm ơn ViTravel đã sắp xếp chỉn chu.',
        'author' => 'Chị Lan Phương',
      ),
      'places' => 
      array (
        0 => 'Denpasar',
        1 => 'Ubud',
        2 => 'Tegalalang',
        3 => 'Tanah Lot',
        4 => 'Seminyak',
        5 => 'Uluwatu',
      ),
      'start' => 'Denpasar (DPS)',
      'end' => 'Denpasar (DPS)',
      'highlightsIntro' => 'Một tuần Bali cân bằng văn hoá đền chùa ở Ubud với ngày nghỉ biển Seminyak — lịch trình gọn, nhịp vừa phải cho cặp đôi và gia đình.',
      'highlights' => 
      array (
        0 => 'Ruộng bậc thang Tegalalang & làng nghệ nhân Ubud',
        1 => 'Hoàng hôn đền Tanah Lot trên đá biển',
        2 => 'Lớp nấu ăn Bali với nguyên liệu chợ địa phương',
        3 => '2 đêm resort biển Seminyak + đền Uluwatu & Kecak',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Denpasar — Ubud chào đón',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Ubud',
          'content' => 'Đón sân bay DPS, về Ubud nhận villa. Chiều dạo trung tâm, tối thưởng thức ẩm thực Bali.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Ubud — Tegalalang & nghệ thuật',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Ubud',
          'content' => 'Ngắm ruộng bậc thang, thăm làng bạc Celuk và xưởng batik; chiều tự do spa hoặc yoga.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Ubud — Lớp nấu ăn & đền thiêng',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Ubud',
          'content' => 'Mua nguyên liệu ở chợ, học nấu món Bali; chiều thăm Tirta Empul hoặc Goa Gajah.',
        ),
        3 => 
        array (
          'day' => 4,
          'title' => 'Tanah Lot — Seminyak',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Seminyak',
          'content' => 'Hoàng hôn Tanah Lot, về resort biển Seminyak nghỉ dưỡng.',
        ),
        4 => 
        array (
          'day' => 5,
          'title' => 'Seminyak — Ngày biển',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'walking',
          ),
          'overnight' => 'Seminyak',
          'content' => 'Tự do tắm biển, hồ bơi; chiều dạo Beach Walk hoặc massage truyền thống.',
        ),
        5 => 
        array (
          'day' => 6,
          'title' => 'Uluwatu & Kecak',
          'meals' => 'Sáng; Tối',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Seminyak',
          'content' => 'Chiều đền Uluwatu trên vách đá, xem múa Kecak hoàng hôn; tối hải sản Jimbaran.',
        ),
        6 => 
        array (
          'day' => 7,
          'title' => 'Tạm biệt Bali',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
            1 => 'plane',
          ),
          'overnight' => NULL,
          'content' => 'Tuỳ giờ bay: mua sắm hoặc spa ngắn, xe đưa ra sân bay DPS.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Villa/resort 4* (3 đêm Ubud + 2 đêm Seminyak + 1 đêm tuỳ lịch)',
        1 => 'Xe riêng đời mới & tài xế tiếng Anh cơ bản',
        2 => 'Hướng dẫn viên địa phương các ngày tham quan',
        3 => 'Vé đền, lớp nấu ăn, show Kecak theo chương trình',
        4 => 'Bữa ăn ghi trong lịch trình',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay quốc tế đến/đi Bali',
        1 => 'Visa on arrival / e-VOA (nếu áp dụng)',
        2 => 'Đồ uống, tip, chi phí cá nhân',
        3 => 'Bảo hiểm du lịch',
      ),
      'notes' => 
      array (
        0 => 'Nên mặc lịch sự khi vào đền (có thể thuê sarong tại chỗ).',
        1 => 'Mùa khô đẹp nhất: tháng 4 – tháng 10.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Tour Bali 7 ngày có phù hợp trăng mật không?',
          'a' => 'Có — lịch trình kết hợp Ubud lãng mạn và resort biển; có thể nâng cấp villa riêng với phụ phí.',
        ),
        1 => 
        array (
          'q' => 'Có cần visa Indonesia không?',
          'a' => 'Công dân nhiều nước được e-VOA hoặc miễn thị thực ngắn hạn — chúng tôi hướng dẫn trước khi đi.',
        ),
      ),
      'galleryCount' => 5,
      'en' => 
      array (
        'title' => 'Bali 7 Days — Ubud, Sacred Temples & Seminyak Beach',
        'start' => 'Denpasar (DPS)',
        'end' => 'Denpasar (DPS)',
        'places' => 
        array (
          0 => 'Denpasar',
          1 => 'Ubud',
          2 => 'Tegalalang',
          3 => 'Tanah Lot',
          4 => 'Seminyak',
          5 => 'Uluwatu',
        ),
        'quote' => 
        array (
          'text' => 'Ubud was poetic and Tanah Lot at sunset unforgettable. Thank you ViTravel for a seamless trip.',
          'author' => 'Lan Phuong',
        ),
        'highlightsIntro' => 'A balanced Bali week: temple culture in Ubud and beach time in Seminyak — an easy pace for couples and families.',
        'highlights' => 
        array (
          0 => 'Tegalalang rice terraces & Ubud artisan villages',
          1 => 'Sunset at Tanah Lot sea temple',
          2 => 'Hands-on Balinese cooking class',
          3 => 'Two nights at Seminyak beach + Uluwatu & Kecak',
        ),
        'itinerary' => 
        array (
          0 => 
          array (
            'day' => 1,
            'title' => 'Denpasar — Welcome to Ubud',
            'meals' => 'Dinner',
            'transport' => 
            array (
              0 => 'plane',
              1 => 'car',
            ),
            'overnight' => 'Ubud',
            'content' => 'Airport pickup, check in at your Ubud villa. Evening stroll and Balinese dinner.',
          ),
          1 => 
          array (
            'day' => 2,
            'title' => 'Ubud — Tegalalang & crafts',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Ubud',
            'content' => 'Rice terraces, Celuk silver village and batik workshop; free afternoon for spa or yoga.',
          ),
          2 => 
          array (
            'day' => 3,
            'title' => 'Ubud — Cooking & temples',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Ubud',
            'content' => 'Market shopping and cooking class; afternoon at Tirta Empul or Goa Gajah.',
          ),
          3 => 
          array (
            'day' => 4,
            'title' => 'Tanah Lot — Seminyak',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Seminyak',
            'content' => 'Tanah Lot sunset, then check in at your Seminyak beach resort.',
          ),
          4 => 
          array (
            'day' => 5,
            'title' => 'Seminyak — Beach day',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'walking',
            ),
            'overnight' => 'Seminyak',
            'content' => 'Free day for beach, pool and optional massage.',
          ),
          5 => 
          array (
            'day' => 6,
            'title' => 'Uluwatu & Kecak',
            'meals' => 'Breakfast; Dinner',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Seminyak',
            'content' => 'Cliff-top Uluwatu temple, Kecak dance at sunset, seafood dinner in Jimbaran.',
          ),
          6 => 
          array (
            'day' => 7,
            'title' => 'Farewell Bali',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'car',
              1 => 'plane',
            ),
            'overnight' => NULL,
            'content' => 'Transfer to DPS airport according to your flight time.',
          ),
        ),
        'inclusions' => 
        array (
          0 => '4* villa/resort (Ubud + Seminyak nights as per itinerary)',
          1 => 'Private modern car & driver',
          2 => 'Local guide on sightseeing days',
          3 => 'Temple tickets, cooking class and Kecak show as listed',
          4 => 'Meals indicated in the itinerary',
        ),
        'exclusions' => 
        array (
          0 => 'International flights to/from Bali',
          1 => 'Visa on arrival / e-VOA if required',
          2 => 'Drinks, tips and personal expenses',
          3 => 'Travel insurance',
        ),
        'notes' => 
        array (
          0 => 'Dress modestly at temples (sarong rental available on site).',
          1 => 'Best dry season: April–October.',
        ),
        'faqs' => 
        array (
          0 => 
          array (
            'q' => 'Is this Bali tour good for honeymoons?',
            'a' => 'Yes — Ubud romance plus beach resort nights; private villa upgrades are available.',
          ),
          1 => 
          array (
            'q' => 'Do I need a visa for Indonesia?',
            'a' => 'Many nationalities use e-VOA or short-stay visa exemption — we guide you before departure.',
          ),
        ),
      ),
      'priceFrom' => 19600000.0,
      'currency' => 'VND',
    ),
    7 => 
    array (
      'slug' => 'thai-lan-10-ngay-bac-trung-nam',
      'title' => 'Thái Lan 10 ngày — Chiang Mai, Bangkok & biển phía Nam',
      'countrySlug' => 'thai-lan',
      'country' => 'Thái Lan',
      'tourCode' => 'TH10D-01',
      'duration' => '10 ngày 9 đêm',
      'days' => 10,
      'rating' => 4.9,
      'reviewCount' => 58,
      'badge' => 'Bán chạy',
      'featured' => false,
      'styles' => 
      array (
        0 => 'culture-history',
        1 => 'beach',
        2 => 'balanced',
        3 => 'long-duration',
      ),
      'quote' => 
      array (
        'text' => 'Chiang Mai dịu dàng, Bangkok sôi động, biển Phuket trong xanh — đúng một Thái Lan trọn vẹn.',
        'author' => 'Anh Đức Minh',
      ),
      'places' => 
      array (
        0 => 'Chiang Mai',
        1 => 'Ayutthaya',
        2 => 'Bangkok',
        3 => 'Phuket',
        4 => 'Phi Phi',
      ),
      'start' => 'Chiang Mai (CNX)',
      'end' => 'Phuket (HKT)',
      'highlightsIntro' => 'Hành trình Bắc – Trung – Nam kinh điển: văn hoá Lanna ở Chiang Mai, chùa vàng Bangkok và đảo biển phía Nam trong 10 ngày.',
      'highlights' => 
      array (
        0 => 'Đoàn xe đạp/xe tuk-tuk quanh cổ thành & đền Doi Suthep',
        1 => 'Chợ đêm Chiang Mai & lớp nấu ăn Thái',
        2 => 'Grand Palace, Wat Pho & du thuyền Chao Phraya',
        3 => 'Cano / speedboat vịnh Phuket – quần đảo Phi Phi',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Chiang Mai — Xin chào xứ Lanna',
          'meals' => 'Tối',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Chiang Mai',
          'content' => 'Đón sân bay CNX, nhận khách sạn. Chiều dạo cổ thành, tối chợ đêm.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Chiang Mai — Đền & đồi',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Chiang Mai',
          'content' => 'Wat Phra Singh, Wat Chedi Luang; chiều lên Doi Suthep ngắm toàn cảnh.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Chiang Mai — Ẩm thực & thủ công',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Chiang Mai',
          'content' => 'Lớp nấu ăn Thái; chiều làng thủ công hoặc trekking nhẹ tuỳ chọn.',
        ),
        3 => 
        array (
          'day' => 4,
          'title' => 'Bay về Bangkok',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Bangkok',
          'content' => 'Bay nội địa CNX–BKK, chiều tự do Siam / rooftop.',
        ),
        4 => 
        array (
          'day' => 5,
          'title' => 'Bangkok — Cung điện & chùa vàng',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
            1 => 'boat',
          ),
          'overnight' => 'Bangkok',
          'content' => 'Grand Palace, Wat Pho; chiều thuyền dài trên kênh khlong.',
        ),
        5 => 
        array (
          'day' => 6,
          'title' => 'Ayutthaya trong ngày',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'car',
          ),
          'overnight' => 'Bangkok',
          'content' => 'Di sản Ayutthaya, chiều về Bangkok — tối tự do Iconsiam hoặc Asiatique.',
        ),
        6 => 
        array (
          'day' => 7,
          'title' => 'Bay vào Phuket',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'plane',
            1 => 'car',
          ),
          'overnight' => 'Phuket',
          'content' => 'Bay BKK–HKT, nhận resort biển; chiều tắm biển Patong/Kata.',
        ),
        7 => 
        array (
          'day' => 8,
          'title' => 'Phi Phi / vịnh Phang Nga',
          'meals' => 'Sáng; Trưa',
          'transport' => 
          array (
            0 => 'boat',
          ),
          'overnight' => 'Phuket',
          'content' => 'Tour đảo trong ngày (Phi Phi hoặc Phang Nga tuỳ mùa), lặn ngắm san hô.',
        ),
        8 => 
        array (
          'day' => 9,
          'title' => 'Phuket — Ngày nghỉ',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'walking',
          ),
          'overnight' => 'Phuket',
          'content' => 'Tự do resort, spa hoặc Old Town Phuket về đêm.',
        ),
        9 => 
        array (
          'day' => 10,
          'title' => 'Tạm biệt Thái Lan',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'car',
            1 => 'plane',
          ),
          'overnight' => NULL,
          'content' => 'Xe đưa sân bay HKT theo giờ bay quốc tế.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Khách sạn 4* (Chiang Mai + Bangkok + Phuket)',
        1 => 'Vé máy bay nội địa CNX–BKK và BKK–HKT',
        2 => 'Xe riêng / guide các ngày tham quan',
        3 => 'Tour đảo 1 ngày phía Nam',
        4 => 'Bữa ăn theo chương trình',
      ),
      'exclusions' => 
      array (
        0 => 'Vé máy bay quốc tế đến Chiang Mai / về từ Phuket',
        1 => 'Visa Thái (nếu cần)',
        2 => 'Đồ uống, tip, chi phí cá nhân',
        3 => 'Bảo hiểm du lịch',
      ),
      'notes' => 
      array (
        0 => 'Hộ chiếu còn hạn tối thiểu 6 tháng.',
        1 => 'Mùa biển phía Nam đẹp: tháng 11 – tháng 4.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Có thể đổi điểm biển Phuket sang Krabi không?',
          'a' => 'Được — báo khi đặt tour, chuyên gia sẽ điều chỉnh vé và khách sạn trong 24 giờ.',
        ),
        1 => 
        array (
          'q' => 'Trẻ em tham gia tour đảo có an toàn không?',
          'a' => 'Có, chọn tour phù hợp độ tuổi; áo phao và hướng dẫn viên trên tàu luôn có.',
        ),
      ),
      'galleryCount' => 5,
      'en' => 
      array (
        'title' => 'Thailand 10 Days — Chiang Mai, Bangkok & Southern Beaches',
        'start' => 'Chiang Mai (CNX)',
        'end' => 'Phuket (HKT)',
        'places' => 
        array (
          0 => 'Chiang Mai',
          1 => 'Ayutthaya',
          2 => 'Bangkok',
          3 => 'Phuket',
          4 => 'Phi Phi',
        ),
        'quote' => 
        array (
          'text' => 'Gentle Chiang Mai, buzzing Bangkok and crystal Phuket — a complete Thailand in one trip.',
          'author' => 'Duc Minh',
        ),
        'highlightsIntro' => 'The classic north–centre–south route: Lanna culture in Chiang Mai, Bangkok temples and southern islands in 10 days.',
        'highlights' => 
        array (
          0 => 'Old city temples & Doi Suthep viewpoint',
          1 => 'Chiang Mai night market & Thai cooking class',
          2 => 'Grand Palace, Wat Pho & Chao Phraya cruise',
          3 => 'Phuket bay / Phi Phi island day trip',
        ),
        'itinerary' => 
        array (
          0 => 
          array (
            'day' => 1,
            'title' => 'Chiang Mai — Welcome to Lanna',
            'meals' => 'Dinner',
            'transport' => 
            array (
              0 => 'plane',
              1 => 'car',
            ),
            'overnight' => 'Chiang Mai',
            'content' => 'CNX pickup, hotel check-in, old city stroll and night market.',
          ),
          1 => 
          array (
            'day' => 2,
            'title' => 'Chiang Mai — Temples & hills',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Chiang Mai',
            'content' => 'Wat Phra Singh, Wat Chedi Luang; afternoon at Doi Suthep.',
          ),
          2 => 
          array (
            'day' => 3,
            'title' => 'Chiang Mai — Food & crafts',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Chiang Mai',
            'content' => 'Thai cooking class; optional craft village or light trek.',
          ),
          3 => 
          array (
            'day' => 4,
            'title' => 'Fly to Bangkok',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'plane',
              1 => 'car',
            ),
            'overnight' => 'Bangkok',
            'content' => 'Domestic flight CNX–BKK; free afternoon in the city.',
          ),
          4 => 
          array (
            'day' => 5,
            'title' => 'Bangkok — Palaces & temples',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'car',
              1 => 'boat',
            ),
            'overnight' => 'Bangkok',
            'content' => 'Grand Palace, Wat Pho; long-tail boat on the khlongs.',
          ),
          5 => 
          array (
            'day' => 6,
            'title' => 'Ayutthaya day trip',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'car',
            ),
            'overnight' => 'Bangkok',
            'content' => 'UNESCO Ayutthaya ruins; evening free in Bangkok.',
          ),
          6 => 
          array (
            'day' => 7,
            'title' => 'Fly to Phuket',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'plane',
              1 => 'car',
            ),
            'overnight' => 'Phuket',
            'content' => 'Flight BKK–HKT, beach resort check-in.',
          ),
          7 => 
          array (
            'day' => 8,
            'title' => 'Phi Phi / Phang Nga bay',
            'meals' => 'Breakfast; Lunch',
            'transport' => 
            array (
              0 => 'boat',
            ),
            'overnight' => 'Phuket',
            'content' => 'Island day trip with snorkeling (route by season).',
          ),
          8 => 
          array (
            'day' => 9,
            'title' => 'Phuket — Free day',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'walking',
            ),
            'overnight' => 'Phuket',
            'content' => 'Resort time, spa or Phuket Old Town by night.',
          ),
          9 => 
          array (
            'day' => 10,
            'title' => 'Farewell Thailand',
            'meals' => 'Breakfast',
            'transport' => 
            array (
              0 => 'car',
              1 => 'plane',
            ),
            'overnight' => NULL,
            'content' => 'Transfer to HKT for your international flight.',
          ),
        ),
        'inclusions' => 
        array (
          0 => '4* hotels in Chiang Mai, Bangkok and Phuket',
          1 => 'Domestic flights CNX–BKK and BKK–HKT',
          2 => 'Private transfers / guides on touring days',
          3 => 'One southern island day trip',
          4 => 'Meals as listed in the itinerary',
        ),
        'exclusions' => 
        array (
          0 => 'International flights into Chiang Mai / out of Phuket',
          1 => 'Thailand visa if required',
          2 => 'Drinks, tips and personal expenses',
          3 => 'Travel insurance',
        ),
        'notes' => 
        array (
          0 => 'Passport must be valid at least 6 months.',
          1 => 'Best southern sea season: November–April.',
        ),
        'faqs' => 
        array (
          0 => 
          array (
            'q' => 'Can we swap Phuket for Krabi?',
            'a' => 'Yes — tell us when booking and we will adjust flights and hotels within 24 hours.',
          ),
          1 => 
          array (
            'q' => 'Are island tours safe for children?',
            'a' => 'Yes — we choose age-appropriate boats with life jackets and onboard guides.',
          ),
        ),
      ),
      'priceFrom' => 28000000.0,
      'currency' => 'VND',
    ),
  ),
  'cruises' => 
  array (
    0 => 
    array (
      'slug' => 'du-thuyen-ha-long-2-ngay',
      'title' => 'Du thuyền Hạ Long 5* — 2 ngày 1 đêm giữa kỳ quan',
      'typeSlug' => 'du-thuyen-ha-long',
      'typeName' => 'Du thuyền Hạ Long',
      'tourCode' => 'CR2D-01',
      'duration' => '2 ngày 1 đêm',
      'days' => 2,
      'rating' => 5.0,
      'reviewCount' => 89,
      'badge' => 'Ưu đãi đặc biệt',
      'styles' => 
      array (
        0 => 'balanced',
        1 => 'honeymoon',
      ),
      'quote' => 
      array (
        'text' => 'Cabin ban công nhìn thẳng ra vịnh, đồ ăn xuất sắc — vượt xa mong đợi.',
        'author' => 'Chị Kiều Trang',
      ),
      'places' => 
      array (
        0 => 'Tuần Châu',
        1 => 'Hang Sửng Sốt',
        2 => 'Đảo Titop',
        3 => 'Làng chài Cửa Vạn',
      ),
      'start' => 'Cảng Tuần Châu',
      'end' => 'Cảng Tuần Châu',
      'departurePort' => 'Cảng quốc tế Tuần Châu',
      'boatClass' => 'Luxury 5*',
      'nightsOnBoard' => 1,
      'cabinTypes' => 
      array (
        0 => 
        array (
          'name' => 'Deluxe Balcony',
          'capacity' => 2,
          'note' => 'Ban công riêng, 28m²',
        ),
        1 => 
        array (
          'name' => 'Family Suite',
          'capacity' => 4,
          'note' => 'Cửa sổ toàn cảnh, 40m²',
        ),
        2 => 
        array (
          'name' => 'Executive Suite',
          'capacity' => 2,
          'note' => 'Bồn tắm view vịnh, 52m²',
        ),
      ),
      'highlightsIntro' => 'Một đêm trọn vẹn giữa di sản: kayak, lớp nấu ăn, tiệc hoàng hôn và bình minh thái cực quyền trên boong.',
      'highlights' => 
      array (
        0 => 'Cabin ban công riêng hướng vịnh',
        1 => 'Kayak & thuyền nan làng chài',
        2 => 'Tiệc BBQ hoàng hôn trên boong',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Lên tàu — khám phá vịnh',
          'meals' => 'Trưa; Tối',
          'transport' => 
          array (
            0 => 'cruise',
            1 => 'kayak',
          ),
          'overnight' => 'Trên du thuyền',
          'content' => 'Đón khách tại cảng, ăn trưa hải sản, chiều chèo kayak, tối tiệc hoàng hôn và câu mực đêm.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Bình minh trên vịnh — trả khách',
          'meals' => 'Sáng; Trưa nhẹ',
          'transport' => 
          array (
            0 => 'cruise',
          ),
          'overnight' => NULL,
          'content' => 'Thái cực quyền đón bình minh, thăm hang Sửng Sốt, brunch và về lại cảng.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Cabin theo hạng chọn',
        1 => 'Toàn bộ bữa ăn trên tàu',
        2 => 'Kayak, lớp nấu ăn, câu mực',
        3 => 'Vé thắng cảnh vịnh',
      ),
      'exclusions' => 
      array (
        0 => 'Xe đưa đón Hà Nội (đặt thêm)',
        1 => 'Đồ uống',
        2 => 'Spa trên tàu',
      ),
      'notes' => 
      array (
        0 => 'Nhận phòng 12:15, trả phòng 10:30 hôm sau.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Có xe đưa đón từ Hà Nội không?',
          'a' => 'Có, xe limousine khứ hồi Hà Nội – Tuần Châu với phụ phí nhỏ, đặt cùng lúc với vé du thuyền.',
        ),
      ),
      'galleryCount' => 5,
      'priceFrom' => 5600000.0,
      'currency' => 'VND',
    ),
    1 => 
    array (
      'slug' => 'du-thuyen-lan-ha-3-ngay',
      'title' => 'Du thuyền Lan Hạ 3 ngày — Vịnh xanh vắng dấu chân người',
      'typeSlug' => 'du-thuyen-lan-ha',
      'typeName' => 'Du thuyền Lan Hạ',
      'tourCode' => 'CR3D-02',
      'duration' => '3 ngày 2 đêm',
      'days' => 3,
      'rating' => 4.9,
      'reviewCount' => 47,
      'badge' => NULL,
      'styles' => 
      array (
        0 => 'nature-homestay',
        1 => 'small-group',
      ),
      'quote' => 
      array (
        'text' => 'Lan Hạ yên tĩnh hơn Hạ Long rất nhiều — đúng nơi để trốn khỏi đám đông.',
        'author' => 'Anh Đức Huy',
      ),
      'places' => 
      array (
        0 => 'Bến Bèo',
        1 => 'Vịnh Lan Hạ',
        2 => 'Đảo Cát Bà',
        3 => 'Hang Sáng Tối',
      ),
      'start' => 'Cảng Bèo, Cát Bà',
      'end' => 'Cảng Bèo, Cát Bà',
      'departurePort' => 'Cảng Bèo (Cát Bà)',
      'boatClass' => 'Boutique 4*',
      'nightsOnBoard' => 2,
      'cabinTypes' => 
      array (
        0 => 
        array (
          'name' => 'Ocean View',
          'capacity' => 2,
          'note' => 'Cửa sổ lớn, 22m²',
        ),
        1 => 
        array (
          'name' => 'Balcony Suite',
          'capacity' => 3,
          'note' => 'Ban công gỗ, 34m²',
        ),
      ),
      'highlightsIntro' => 'Ba ngày len lỏi giữa 400 hòn đảo đá vôi ít người biết, kết hợp đạp xe làng Việt Hải.',
      'highlights' => 
      array (
        0 => 'Bãi tắm hoang Ba Trái Đào',
        1 => 'Đạp xe làng chài Việt Hải',
        2 => 'Chèo kayak Hang Sáng Tối',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Lên tàu tại Cát Bà',
          'meals' => 'Trưa; Tối',
          'transport' => 
          array (
            0 => 'cruise',
          ),
          'overnight' => 'Trên du thuyền',
          'content' => 'Hải trình qua vịnh Lan Hạ, tắm biển bãi Ba Trái Đào.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Việt Hải — Hang Sáng Tối',
          'meals' => 'Sáng; Trưa; Tối',
          'transport' => 
          array (
            0 => 'bike',
            1 => 'kayak',
          ),
          'overnight' => 'Trên du thuyền',
          'content' => 'Đạp xe xuyên rừng quốc gia, chiều kayak hang Sáng Tối.',
        ),
        2 => 
        array (
          'day' => 3,
          'title' => 'Bình minh — trả khách',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'cruise',
          ),
          'overnight' => NULL,
          'content' => 'Ngắm bình minh, brunch, về lại cảng Bèo.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Cabin 2 đêm',
        1 => 'Bữa ăn trên tàu',
        2 => 'Xe đạp, kayak',
        3 => 'Vé vườn quốc gia',
      ),
      'exclusions' => 
      array (
        0 => 'Đồ uống',
        1 => 'Tip',
      ),
      'notes' => 
      array (
        0 => 'Phù hợp khách đã từng đi Hạ Long, muốn trải nghiệm vắng hơn.',
      ),
      'faqs' => 
      array (
      ),
      'galleryCount' => 4,
      'priceFrom' => 8400000.0,
      'currency' => 'VND',
    ),
    2 => 
    array (
      'slug' => 'du-thuyen-mekong-cai-be-can-tho',
      'title' => 'Du thuyền Mekong — Cái Bè đến Cần Thơ 2 ngày',
      'typeSlug' => 'du-thuyen-mekong',
      'typeName' => 'Du thuyền Mekong',
      'tourCode' => 'CR2D-03',
      'duration' => '2 ngày 1 đêm',
      'days' => 2,
      'rating' => 4.8,
      'reviewCount' => 33,
      'badge' => NULL,
      'styles' => 
      array (
        0 => 'culture-history',
        1 => 'nature-homestay',
      ),
      'quote' => 
      array (
        'text' => 'Buổi sáng ở chợ nổi Cái Răng nhìn từ boong tàu là trải nghiệm rất riêng của miền Tây.',
        'author' => 'Chị Phương Dung',
      ),
      'places' => 
      array (
        0 => 'Cái Bè',
        1 => 'Vĩnh Long',
        2 => 'Chợ nổi Cái Răng',
        3 => 'Cần Thơ',
      ),
      'start' => 'Bến Cái Bè',
      'end' => 'Bến Ninh Kiều, Cần Thơ',
      'departurePort' => 'Bến tàu Cái Bè',
      'boatClass' => 'Classic gỗ truyền thống',
      'nightsOnBoard' => 1,
      'cabinTypes' => 
      array (
        0 => 
        array (
          'name' => 'Standard',
          'capacity' => 2,
          'note' => 'Cửa sổ sông, 16m²',
        ),
        1 => 
        array (
          'name' => 'Superior',
          'capacity' => 2,
          'note' => 'Đầu mũi tàu, 20m²',
        ),
      ),
      'highlightsIntro' => 'Xuôi dòng Mekong trên du thuyền gỗ truyền thống, ghé lò cốm, vườn trái cây và chợ nổi.',
      'highlights' => 
      array (
        0 => 'Chợ nổi Cái Răng bình minh',
        1 => 'Xuồng ba lá rạch nhỏ',
        2 => 'Đờn ca tài tử trên tàu',
      ),
      'itinerary' => 
      array (
        0 => 
        array (
          'day' => 1,
          'title' => 'Cái Bè — xuôi dòng',
          'meals' => 'Trưa; Tối',
          'transport' => 
          array (
            0 => 'cruise',
            1 => 'sampan',
          ),
          'overnight' => 'Trên du thuyền',
          'content' => 'Thăm làng nghề, cù lao An Bình, đêm neo giữa sông.',
        ),
        1 => 
        array (
          'day' => 2,
          'title' => 'Chợ nổi Cái Răng — Cần Thơ',
          'meals' => 'Sáng',
          'transport' => 
          array (
            0 => 'cruise',
          ),
          'overnight' => NULL,
          'content' => 'Dậy sớm đi chợ nổi, cập bến Ninh Kiều.',
        ),
      ),
      'inclusions' => 
      array (
        0 => 'Cabin máy lạnh',
        1 => 'Bữa ăn đặc sản miền Tây',
        2 => 'Xuồng ba lá & vé tham quan',
      ),
      'exclusions' => 
      array (
        0 => 'Xe từ TP.HCM (đặt thêm)',
        1 => 'Đồ uống',
      ),
      'notes' => 
      array (
        0 => 'Có thể nối tuyến sang Phnom Penh (Campuchia).',
      ),
      'faqs' => 
      array (
      ),
      'galleryCount' => 4,
      'priceFrom' => 5600000.0,
      'currency' => 'VND',
    ),
  ),
  'blog_categories' => 
  array (
    0 => 
    array (
      'slug' => 'ha-noi',
      'name' => 'Hà Nội',
      'countrySlug' => 'viet-nam',
      'count' => 24,
    ),
    1 => 
    array (
      'slug' => 'sa-pa',
      'name' => 'Sa Pa',
      'countrySlug' => 'viet-nam',
      'count' => 17,
    ),
    2 => 
    array (
      'slug' => 'hue',
      'name' => 'Huế',
      'countrySlug' => 'viet-nam',
      'count' => 12,
    ),
    3 => 
    array (
      'slug' => 'hoi-an',
      'name' => 'Hội An',
      'countrySlug' => 'viet-nam',
      'count' => 19,
    ),
    4 => 
    array (
      'slug' => 'phu-quoc',
      'name' => 'Phú Quốc',
      'countrySlug' => 'viet-nam',
      'count' => 11,
    ),
    5 => 
    array (
      'slug' => 'siem-reap',
      'name' => 'Siem Reap',
      'countrySlug' => 'campuchia',
      'count' => 14,
    ),
    6 => 
    array (
      'slug' => 'phnom-penh',
      'name' => 'Phnom Penh',
      'countrySlug' => 'campuchia',
      'count' => 9,
    ),
    7 => 
    array (
      'slug' => 'bangkok',
      'name' => 'Bangkok',
      'countrySlug' => 'thai-lan',
      'count' => 13,
    ),
    8 => 
    array (
      'slug' => 'luang-prabang',
      'name' => 'Luang Prabang',
      'countrySlug' => 'lao',
      'count' => 8,
    ),
  ),
  'popular_keywords' => 
  array (
    0 => 'Kinh nghiệm du lịch Việt Nam',
    1 => 'Tour Việt Nam 10 ngày',
    2 => 'Du lịch Campuchia tự túc',
    3 => 'Tour Việt Nam 2 tuần',
    4 => 'Chi phí du lịch Sa Pa',
    5 => 'Đặc sản Hội An',
    6 => 'Du thuyền Hạ Long giá tốt',
    7 => 'Lịch trình xuyên Việt',
    8 => 'Angkor Wat mấy giờ mở cửa',
  ),
  'articles' => 
  array (
    0 => 
    array (
      'slug' => 'sa-pa-mua-nao-dep-nhat',
      'title' => 'Sa Pa mùa nào đẹp nhất? Cẩm nang chọn thời điểm cho từng kiểu du khách',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'category' => 'Sa Pa',
      'categorySlug' => 'sa-pa',
      'tags' => 
      array (
        0 => 'Mẹo du lịch',
        1 => 'Chơi gì, xem gì?',
      ),
      'author' => 'Lan Hương',
      'publishedAt' => '12/06/2026',
      'updatedAt' => '20/07/2026',
      'views' => 1284,
      'rating' => 4.9,
      'ratingCount' => 41,
      'excerpt' => 'Mùa lúa chín, mùa săn mây hay mùa tuyết rơi — mỗi thời điểm Sa Pa lại mang một gương mặt khác. Bài viết giúp bạn chọn đúng mùa cho đúng trải nghiệm.',
      'content' => 
      array (
        0 => 
        array (
          'type' => 'p',
          'text' => 'Sa Pa nằm ở độ cao 1.500m so với mực nước biển, khí hậu ôn đới quanh năm mát mẻ nhưng thay đổi rất rõ theo mùa. Chọn đúng thời điểm sẽ quyết định 70% chất lượng chuyến đi của bạn — đặc biệt nếu bạn nhắm tới ruộng bậc thang mùa lúa chín hay những biển mây cuối đông.',
        ),
        1 => 
        array (
          'type' => 'h2',
          'id' => 'khi-hau-tong-quan',
          'text' => 'I. Khí hậu Sa Pa tổng quan theo bốn mùa',
        ),
        2 => 
        array (
          'type' => 'p',
          'text' => 'Xuân (tháng 2 – 5) là mùa hoa đào, hoa mận nở trắng thung lũng, nhiệt độ 15 – 18°C. Hè (tháng 6 – 8) mát mẻ nhất miền Bắc nhưng có mưa rào bất chợt. Thu (tháng 9 – 11) là mùa vàng của ruộng bậc thang. Đông (tháng 12 – 1) lạnh sâu, có năm xuất hiện băng tuyết trên đỉnh Fansipan.',
        ),
        3 => 
        array (
          'type' => 'image',
          'caption' => 'Ruộng bậc thang Mường Hoa vào cuối tháng 9 — thời điểm được săn đón nhất năm.',
        ),
        4 => 
        array (
          'type' => 'h3',
          'id' => 'mua-lua-chin',
          'text' => '1. Mùa lúa chín (giữa tháng 9 – đầu tháng 10)',
        ),
        5 => 
        array (
          'type' => 'p',
          'text' => 'Đây là "mùa vàng" theo đúng nghĩa đen. Thung lũng Mường Hoa, bản Lao Chải, Tả Van phủ một màu vàng óng. Lưu ý đặt phòng trước ít nhất 3 tuần vì đây là mùa cao điểm của cả khách trong nước lẫn quốc tế.',
        ),
        6 => 
        array (
          'type' => 'h3',
          'id' => 'mua-san-may',
          'text' => '2. Mùa săn mây (tháng 11 – tháng 3)',
        ),
        7 => 
        array (
          'type' => 'p',
          'text' => 'Biển mây thường xuất hiện sau những ngày mưa nhỏ, trời hửng nắng. Các điểm săn mây đẹp nhất: đỉnh Fansipan, đèo Ô Quy Hồ, Hàm Rồng. Nên xem dự báo độ ẩm trên 85% và chênh lệch nhiệt độ ngày đêm lớn.',
        ),
        8 => 
        array (
          'type' => 'links',
          'title' => 'Xem thêm:',
          'links' => 
          array (
            0 => 
            array (
              'label' => 'Tour Sa Pa 4 ngày trekking bản làng',
              'route' => 
              array (
                0 => 'tours.show',
                1 => 
                array (
                  'country' => 'viet-nam',
                  'slug' => 'sa-pa-trekking-4-ngay',
                ),
              ),
            ),
            1 => 
            array (
              'label' => 'Kinh nghiệm trekking Lao Chải – Tả Van',
              'route' => 
              array (
                0 => 'guide.country',
                1 => 
                array (
                  'country' => 'viet-nam',
                ),
              ),
            ),
            2 => 
            array (
              'label' => 'Tour Việt Nam 10 ngày có Sa Pa',
              'route' => 
              array (
                0 => 'tours.show',
                1 => 
                array (
                  'country' => 'viet-nam',
                  'slug' => 'viet-nam-10-ngay-di-san-mien-bac',
                ),
              ),
            ),
          ),
        ),
        9 => 
        array (
          'type' => 'h2',
          'id' => 'goi-y-theo-kieu-du-khach',
          'text' => 'II. Gợi ý theo kiểu du khách',
        ),
        10 => 
        array (
          'type' => 'ul',
          'items' => 
          array (
            0 => 'Gia đình có trẻ nhỏ: tháng 3 – 5, thời tiết ôn hoà, ít mưa.',
            1 => 'Nhiếp ảnh gia: cuối tháng 9 (lúa chín) hoặc tháng 12 – 1 (biển mây).',
            2 => 'Người mê trekking: tháng 10 – 11, đường khô ráo, trời trong.',
            3 => 'Cặp đôi trăng mật: tháng 12, se lạnh, phố núi lãng mạn trong sương.',
          ),
        ),
        11 => 
        array (
          'type' => 'h2',
          'id' => 'ket-luan',
          'text' => 'III. Kết luận',
        ),
        12 => 
        array (
          'type' => 'p',
          'text' => 'Không có mùa "xấu" ở Sa Pa — chỉ có mùa phù hợp với bạn hay không. Nếu vẫn phân vân, hãy để chuyên gia bản địa của chúng tôi tư vấn lịch trình miễn phí qua form thiết kế tour riêng.',
        ),
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Sa Pa có tuyết vào tháng nào?',
          'a' => 'Nếu có, tuyết thường rơi cuối tháng 12 đến giữa tháng 1, tập trung ở đỉnh Fansipan và đèo Ô Quy Hồ. Đây là hiện tượng không chắc chắn hằng năm.',
        ),
        1 => 
        array (
          'q' => 'Đi Sa Pa 2 ngày cuối tuần có kịp không?',
          'a' => 'Kịp với cao tốc Hà Nội – Lào Cai (5 giờ xe). Tuy nhiên 3 – 4 ngày sẽ thoải mái hơn nếu bạn muốn trekking và nghỉ homestay.',
        ),
        2 => 
        array (
          'q' => 'Nên ở khách sạn trung tâm hay homestay trong bản?',
          'a' => 'Kết hợp cả hai: 1 đêm trung tâm để dạo phố, 1 – 2 đêm homestay Tả Van để trải nghiệm văn hoá bản địa.',
        ),
      ),
      'galleryCount' => 5,
    ),
    1 => 
    array (
      'slug' => 'an-gi-o-hoi-an-24-gio',
      'title' => 'Ăn gì ở Hội An trong 24 giờ? Bản đồ ẩm thực từ sáng tới khuya',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'category' => 'Hội An',
      'categorySlug' => 'hoi-an',
      'tags' => 
      array (
        0 => 'Ăn gì, uống gì?',
      ),
      'author' => 'Minh Trí',
      'publishedAt' => '02/07/2026',
      'updatedAt' => '15/07/2026',
      'views' => 956,
      'rating' => 4.8,
      'ratingCount' => 28,
      'excerpt' => 'Cao lầu, bánh mì Phượng, cơm gà bà Buội, chè bắp Cẩm Nam — lịch ăn dày đặc cho một ngày trọn vẹn ở phố Hội.',
      'content' => 
      array (
        0 => 
        array (
          'type' => 'p',
          'text' => 'Hội An nhỏ nhưng mật độ món ngon trên mỗi mét vuông có lẽ cao nhất Việt Nam. Bài viết này xếp lịch ăn theo khung giờ để bạn không bỏ lỡ món nào trong 24 giờ.',
        ),
        1 => 
        array (
          'type' => 'h2',
          'id' => 'buoi-sang',
          'text' => 'I. Buổi sáng: cao lầu và cà phê phố cổ',
        ),
        2 => 
        array (
          'type' => 'p',
          'text' => 'Bắt đầu với tô cao lầu sợi mì vàng ươm — món chỉ đúng vị khi nước được lấy từ giếng Bá Lễ. Sau đó là cà phê muối ở một quán gác gỗ nhìn xuống đường Trần Phú.',
        ),
        3 => 
        array (
          'type' => 'h2',
          'id' => 'buoi-chieu-toi',
          'text' => 'II. Chiều tối: chợ đêm và ăn vặt bờ sông',
        ),
        4 => 
        array (
          'type' => 'p',
          'text' => 'Từ 17h, khu chợ đêm Nguyễn Hoàng lên đèn. Nhất định thử bánh bao – bánh vạc "hoa hồng trắng" và kết thúc bằng chè bắp Cẩm Nam.',
        ),
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Cao lầu khác mì Quảng thế nào?',
          'a' => 'Cao lầu sợi dày, dai, ít nước dùng và ăn kèm xá xíu; mì Quảng sợi mềm hơn, nhiều nước lèo và đậu phộng.',
        ),
      ),
      'galleryCount' => 4,
    ),
    2 => 
    array (
      'slug' => 'angkor-wat-kinh-nghiem-binh-minh',
      'title' => 'Kinh nghiệm ngắm bình minh Angkor Wat: đi mấy giờ, đứng ở đâu?',
      'countrySlug' => 'campuchia',
      'country' => 'Campuchia',
      'category' => 'Siem Reap',
      'categorySlug' => 'siem-reap',
      'tags' => 
      array (
        0 => 'Chơi gì, xem gì?',
        1 => 'Mẹo du lịch',
      ),
      'author' => 'Lan Hương',
      'publishedAt' => '28/06/2026',
      'updatedAt' => '10/07/2026',
      'views' => 1731,
      'rating' => 5.0,
      'ratingCount' => 52,
      'excerpt' => 'Vị trí hồ sen phía trái cổng Tây, có mặt trước 5h15, và những mẹo nhỏ để có khung hình phản chiếu hoàn hảo.',
      'content' => 
      array (
        0 => 
        array (
          'type' => 'p',
          'text' => 'Bình minh Angkor Wat là khoảnh khắc được chờ đợi nhất ở Siem Reap — nhưng cũng đông đúc nhất. Chuẩn bị đúng sẽ giúp bạn có trải nghiệm trọn vẹn thay vì chen chúc.',
        ),
        1 => 
        array (
          'type' => 'h2',
          'id' => 'gio-vang',
          'text' => 'I. Khung giờ vàng và vé vào cửa',
        ),
        2 => 
        array (
          'type' => 'p',
          'text' => 'Cổng bán vé mở từ 4h30. Hãy mua vé Angkor Pass từ chiều hôm trước để sáng hôm sau đi thẳng vào cổng, có mặt tại hồ sen trước 5h15.',
        ),
        3 => 
        array (
          'type' => 'h2',
          'id' => 'vi-tri-dep',
          'text' => 'II. Vị trí đứng đẹp nhất',
        ),
        4 => 
        array (
          'type' => 'p',
          'text' => 'Hồ nước phía trái lối vào chính (hướng Tây) cho khung hình 5 ngọn tháp phản chiếu. Đứng lệch trái 10m so với đám đông, bạn sẽ có tiền cảnh hoa sen.',
        ),
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Vé Angkor Pass giá bao nhiêu?',
          'a' => 'Vé 1 ngày 37 USD, 3 ngày 62 USD, 7 ngày 72 USD — thanh toán được bằng thẻ quốc tế.',
        ),
      ),
      'galleryCount' => 5,
    ),
    3 => 
    array (
      'slug' => 'ha-noi-36-pho-phuong-mot-ngay',
      'title' => 'Một ngày lang thang 36 phố phường Hà Nội: lộ trình đi bộ chi tiết',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'category' => 'Hà Nội',
      'categorySlug' => 'ha-noi',
      'tags' => 
      array (
        0 => 'Chơi gì, xem gì?',
      ),
      'author' => 'Minh Trí',
      'publishedAt' => '18/06/2026',
      'updatedAt' => '01/07/2026',
      'views' => 842,
      'rating' => 4.7,
      'ratingCount' => 19,
      'excerpt' => 'Từ Hàng Mã rực rỡ tới Tạ Hiện về đêm — lộ trình đi bộ 6km xuyên trái tim phố cổ kèm điểm dừng ăn uống.',
      'content' => 
      array (
        0 => 
        array (
          'type' => 'p',
          'text' => 'Phố cổ Hà Nội đẹp nhất khi đi bộ. Lộ trình dưới đây dài khoảng 6km, bắt đầu từ chợ Đồng Xuân và kết thúc ở bia hơi Tạ Hiện.',
        ),
        1 => 
        array (
          'type' => 'h2',
          'id' => 'buoi-sang',
          'text' => 'I. Buổi sáng: chợ Đồng Xuân — Hàng Mã — Hàng Bạc',
        ),
        2 => 
        array (
          'type' => 'p',
          'text' => 'Ăn sáng phở gánh trong chợ, dạo phố Hàng Mã và nghe câu chuyện nghề kim hoàn trăm năm ở Hàng Bạc.',
        ),
      ),
      'faqs' => 
      array (
      ),
      'galleryCount' => 4,
    ),
    4 => 
    array (
      'slug' => 'chi-phi-du-lich-viet-nam-10-ngay',
      'title' => 'Chi phí du lịch Việt Nam 10 ngày hết bao nhiêu? Bảng dự trù 2026',
      'countrySlug' => 'viet-nam',
      'country' => 'Việt Nam',
      'category' => 'Hà Nội',
      'categorySlug' => 'ha-noi',
      'tags' => 
      array (
        0 => 'Mẹo du lịch',
        1 => 'Chọn tour nào?',
      ),
      'author' => 'Lan Hương',
      'publishedAt' => '05/06/2026',
      'updatedAt' => '25/06/2026',
      'views' => 2103,
      'rating' => 4.9,
      'ratingCount' => 63,
      'excerpt' => 'So sánh chi tiết ba mức ngân sách — tiết kiệm, tầm trung, cao cấp — cho hành trình 10 ngày phổ biến nhất.',
      'content' => 
      array (
        0 => 
        array (
          'type' => 'p',
          'text' => 'Câu hỏi chúng tôi nhận nhiều nhất: "10 ngày ở Việt Nam tốn bao nhiêu?". Câu trả lời phụ thuộc phong cách đi, nhưng bảng dự trù dưới đây sẽ cho bạn con số sát thực tế.',
        ),
        1 => 
        array (
          'type' => 'h2',
          'id' => 'ba-muc-ngan-sach',
          'text' => 'I. Ba mức ngân sách phổ biến',
        ),
        2 => 
        array (
          'type' => 'ul',
          'items' => 
          array (
            0 => 'Tiết kiệm: 450 – 600 USD/người (hostel, xe khách, ăn địa phương).',
            1 => 'Tầm trung: 900 – 1.300 USD/người (khách sạn 3-4*, tour ghép, 1 chặng bay nội địa).',
            2 => 'Cao cấp: từ 2.000 USD/người (khách sạn 5*, du thuyền, xe riêng, hướng dẫn viên riêng).',
          ),
        ),
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Đặt tour trọn gói có rẻ hơn tự túc?',
          'a' => 'Với nhóm 2 khách trở lên và lịch trình nhiều điểm, tour trọn gói thường tối ưu hơn 10 – 15% nhờ giá đối tác khách sạn và vận chuyển.',
        ),
      ),
      'galleryCount' => 4,
    ),
    5 => 
    array (
      'slug' => 'luang-prabang-cham-binh-minh-khat-thuc',
      'title' => 'Luang Prabang: nghi lễ khất thực bình minh và những điều nên biết',
      'countrySlug' => 'lao',
      'country' => 'Lào',
      'category' => 'Luang Prabang',
      'categorySlug' => 'luang-prabang',
      'tags' => 
      array (
        0 => 'Chơi gì, xem gì?',
        1 => 'Mẹo du lịch',
      ),
      'author' => 'Minh Trí',
      'publishedAt' => '22/05/2026',
      'updatedAt' => '30/05/2026',
      'views' => 617,
      'rating' => 4.8,
      'ratingCount' => 15,
      'excerpt' => 'Tak Bat là nghi lễ thiêng liêng, không phải hoạt cảnh du lịch — cách tham dự tôn trọng và những điều tuyệt đối tránh.',
      'content' => 
      array (
        0 => 
        array (
          'type' => 'p',
          'text' => 'Mỗi sáng từ 5h30, hàng trăm nhà sư áo cam đi khất thực dọc phố cổ Luang Prabang. Là du khách, bạn được chào đón quan sát — với điều kiện hiểu và tôn trọng quy tắc.',
        ),
        1 => 
        array (
          'type' => 'h2',
          'id' => 'quy-tac',
          'text' => 'I. Quy tắc ứng xử khi xem Tak Bat',
        ),
        2 => 
        array (
          'type' => 'ul',
          'items' => 
          array (
            0 => 'Giữ khoảng cách tối thiểu 3m, không chắn lối đi của đoàn sư.',
            1 => 'Tắt flash, không dí máy ảnh sát mặt các nhà sư.',
            2 => 'Ăn mặc kín vai và gối; ngồi thấp hơn các nhà sư nếu dâng lễ.',
          ),
        ),
      ),
      'faqs' => 
      array (
      ),
      'galleryCount' => 4,
    ),
  ),
  'testimonials' => 
  array (
    0 => 
    array (
      'name' => 'Nguyễn Minh Anh',
      'country' => 'Việt Nam',
      'flag' => '🇻🇳',
      'rating' => 5.0,
      'quote' => 'Từ lúc lên lịch trình đến khi kết thúc chuyến đi, mọi thứ đều chỉn chu. Hướng dẫn viên am hiểu và cực kỳ nhiệt tình.',
      'photos' => 5,
      'trip' => 'Việt Nam 10 ngày',
      'avatar' => NULL,
      'photoUrls' => 
      array (
      ),
    ),
    1 => 
    array (
      'name' => 'Sarah Mitchell',
      'country' => 'Úc',
      'flag' => '🇦🇺',
      'rating' => 5.0,
      'quote' => 'Chuyến đi Việt Nam – Campuchia 15 ngày vượt xa kỳ vọng. Angkor lúc bình minh thật sự kỳ diệu.',
      'photos' => 8,
      'trip' => 'Việt Nam & Campuchia 15 ngày',
      'avatar' => NULL,
      'photoUrls' => 
      array (
      ),
    ),
    2 => 
    array (
      'name' => 'Trần Quốc Bảo',
      'country' => 'Việt Nam',
      'flag' => '🇻🇳',
      'rating' => 4.9,
      'quote' => 'Du thuyền Lan Hạ yên tĩnh, đồ ăn ngon, nhân viên chu đáo. Sẽ quay lại cùng gia đình.',
      'photos' => 4,
      'trip' => 'Du thuyền Lan Hạ 3 ngày',
      'avatar' => NULL,
      'photoUrls' => 
      array (
      ),
    ),
    3 => 
    array (
      'name' => 'Claude Millet',
      'country' => 'Pháp',
      'flag' => '🇫🇷',
      'rating' => 5.0,
      'quote' => 'Đội ngũ tư vấn phản hồi trong vòng vài giờ và điều chỉnh lịch trình theo đúng mong muốn của chúng tôi.',
      'photos' => 6,
      'trip' => 'Xuyên Việt 2 tuần',
      'avatar' => NULL,
      'photoUrls' => 
      array (
      ),
    ),
    4 => 
    array (
      'name' => 'Lê Hoài Nam',
      'country' => 'Việt Nam',
      'flag' => '🇻🇳',
      'rating' => 4.8,
      'quote' => 'Trekking Sa Pa 4 ngày là trải nghiệm đáng nhớ nhất năm của tôi. Homestay ấm cúng, cảnh đẹp mê hồn.',
      'photos' => 7,
      'trip' => 'Sa Pa trekking 4 ngày',
      'avatar' => NULL,
      'photoUrls' => 
      array (
      ),
    ),
    5 => 
    array (
      'name' => 'Emma Rossi',
      'country' => 'Ý',
      'flag' => '🇮🇹',
      'rating' => 5.0,
      'quote' => 'Một công ty địa phương thực sự hiểu khách châu Âu. Mọi khách sạn đều được chọn rất tinh tế.',
      'photos' => 3,
      'trip' => 'Việt Nam 3 tuần',
      'avatar' => NULL,
      'photoUrls' => 
      array (
      ),
    ),
  ),
  'team' => 
  array (
    0 => 
    array (
      'slug' => 'pham-thu-trang',
      'name' => 'Phạm Thu Trang',
      'role' => 'Giám đốc điều hành',
      'bio' => 'Hơn 15 năm dẫn dắt các đoàn khách quốc tế khắp Đông Nam Á, Trang tin rằng mỗi hành trình phải kể một câu chuyện riêng...',
      'phone' => '+84 24 3999 8888',
      'email' => 'trang.pham@vitravel.dev',
      'area' => 'Hà Nội, Việt Nam',
      'years_experience' => 15,
      'languages' => 
      array (
        0 => 'Tiếng Việt',
        1 => 'English',
        2 => 'Français',
      ),
      'stat_clients' => 3200,
      'stat_tours' => 480,
      'stat_awards' => 6,
      'is_verified' => true,
      'bio_html' => '<p>Hơn 15 năm dẫn dắt các đoàn khách quốc tế khắp Đông Nam Á, Phạm Thu Trang tin rằng mỗi hành trình phải kể một câu chuyện riêng — không phải bản sao của lịch trình có sẵn.</p><p>Với bề dày kinh nghiệm điều hành và thiết kế tour cao cấp, Trang xây dựng ViTravel như một đội ngũ bản địa đồng hành sát sao từ lúc lên ý tưởng đến khi khách về nhà.</p>',
      'bio_html_en' => '<p>With over 15 years guiding international groups across Southeast Asia, Thu Trang believes every journey should tell its own story.</p><p>She built ViTravel as a local expert team that stays close from the first idea to the moment guests return home.</p>',
      'name_en' => 'Pham Thu Trang',
      'role_en' => 'Chief Executive Officer',
      'short_bio_en' => 'Over 15 years leading international groups across Southeast Asia — every journey must tell its own story.',
      'achievements' => 
      array (
        0 => 'Giải Travelers\' Choice TripAdvisor 3 năm liên tiếp (cùng đội ngũ ViTravel)',
        1 => 'Thiết kế hơn 200 hành trình riêng cho khách châu Âu & Úc',
        2 => 'Diễn giả khách mời tại diễn đàn du lịch bền vững Hà Nội 2024',
      ),
      'skills' => 
      array (
        0 => 
        array (
          'skill' => 'Thiết kế tour cao cấp',
          'percent' => 95,
        ),
        1 => 
        array (
          'skill' => 'Quản trị trải nghiệm khách',
          'percent' => 92,
        ),
        2 => 
        array (
          'skill' => 'Đàm phán đối tác địa phương',
          'percent' => 90,
        ),
        3 => 
        array (
          'skill' => 'Du lịch bền vững',
          'percent' => 88,
        ),
      ),
      'experiences' => 
      array (
        0 => 
        array (
          'title' => 'Giám đốc điều hành',
          'company' => 'ViTravel',
          'items' => 
          array (
            0 => 'Điều hành chiến lược sản phẩm tour & du thuyền Đông Nam Á',
            1 => 'Xây dựng tiêu chuẩn dịch vụ và quy trình chăm sóc khách',
          ),
        ),
        1 => 
        array (
          'title' => 'Trưởng phòng sản phẩm',
          'company' => 'Đại lý du lịch inbound',
          'items' => 
          array (
            0 => 'Phát triển các tuyến xuyên Việt và Campuchia cho thị trường châu Âu',
          ),
        ),
      ),
      'degrees' => 
      array (
        0 => 
        array (
          'title' => 'Cử nhân Quản trị Du lịch',
          'school' => 'Đại học Khoa học Xã hội & Nhân văn',
          'items' => 
          array (
            0 => 'Chuyên ngành Lữ hành quốc tế',
          ),
        ),
        1 => 
        array (
          'title' => 'Chứng chỉ hướng dẫn viên quốc tế',
          'school' => 'Tổng cục Du lịch Việt Nam',
          'items' => 
          array (
          ),
        ),
      ),
    ),
    1 => 
    array (
      'slug' => 'do-viet-cuong',
      'name' => 'Đỗ Việt Cường',
      'role' => 'Trưởng phòng thiết kế tour',
      'bio' => 'Cường đã đặt chân tới 63 tỉnh thành và tự tay vẽ từng lịch trình để mỗi ngày của khách đều có một điểm chạm đáng nhớ...',
      'phone' => '+84 24 3999 8812',
      'email' => 'cuong.do@vitravel.dev',
      'area' => 'Hà Nội & toàn quốc',
      'years_experience' => 12,
      'languages' => 
      array (
        0 => 'Tiếng Việt',
        1 => 'English',
      ),
      'stat_clients' => 2100,
      'stat_tours' => 650,
      'stat_awards' => 3,
      'is_verified' => true,
      'bio_html' => '<p>Đỗ Việt Cường đã đặt chân tới 63 tỉnh thành và tự tay vẽ từng lịch trình để mỗi ngày của khách đều có một điểm chạm đáng nhớ — từ quán cà phê góc phố đến bình minh trên đèo.</p><p>Anh chuyên cân bằng nhịp độ chuyến đi: vừa đủ khám phá, vừa đủ nghỉ ngơi, và luôn chừa chỗ cho bất ngờ đẹp.</p>',
      'bio_html_en' => '<p>Viet Cuong has traveled all 63 provinces and handcrafts each itinerary so every day has a memorable touchpoint.</p>',
      'name_en' => 'Do Viet Cuong',
      'role_en' => 'Head of Tour Design',
      'short_bio_en' => 'Has set foot in all 63 provinces — crafting itineraries with a memorable touch every day.',
      'achievements' => 
      array (
        0 => 'Thiết kế tuyến xuyên Việt 21 ngày được khách Ý bình chọn cao nhất 2025',
        1 => 'Khảo sát thực địa hơn 120 điểm đến phụ tại miền núi phía Bắc',
      ),
      'skills' => 
      array (
        0 => 
        array (
          'skill' => 'Lập lịch trình đa quốc gia',
          'percent' => 96,
        ),
        1 => 
        array (
          'skill' => 'Khảo sát thực địa',
          'percent' => 94,
        ),
        2 => 
        array (
          'skill' => 'Tối ưu logistics đoàn',
          'percent' => 90,
        ),
        3 => 
        array (
          'skill' => 'Kể chuyện điểm đến',
          'percent' => 85,
        ),
      ),
      'experiences' => 
      array (
        0 => 
        array (
          'title' => 'Trưởng phòng thiết kế tour',
          'company' => 'ViTravel',
          'items' => 
          array (
            0 => 'Phụ trách sản phẩm tour trọn gói 5 quốc gia Đông Nam Á',
            1 => 'Đào tạo đội tư vấn về cấu trúc lịch trình và nhịp độ chuyến đi',
          ),
        ),
      ),
      'degrees' => 
      array (
        0 => 
        array (
          'title' => 'Thạc sĩ Địa lý Du lịch',
          'school' => 'Đại học Khoa học Tự nhiên',
          'items' => 
          array (
            0 => 'Luận văn: Hành lang di sản miền núi phía Bắc',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'slug' => 'le-mai-chi',
      'name' => 'Lê Mai Chi',
      'role' => 'Chuyên gia tư vấn cao cấp',
      'bio' => 'Thành thạo ba ngoại ngữ, Chi là người bạn đồng hành tin cậy của các gia đình châu Âu khi đến Việt Nam lần đầu...',
      'phone' => '+84 28 3888 9901',
      'email' => 'chi.le@vitravel.dev',
      'area' => 'TP. Hồ Chí Minh',
      'years_experience' => 10,
      'languages' => 
      array (
        0 => 'Tiếng Việt',
        1 => 'English',
        2 => 'Italiano',
        3 => 'Français',
      ),
      'stat_clients' => 1800,
      'stat_tours' => 410,
      'stat_awards' => 2,
      'is_verified' => true,
      'bio_html' => '<p>Thành thạo nhiều ngoại ngữ, Lê Mai Chi là người bạn đồng hành tin cậy của các gia đình châu Âu khi đến Việt Nam lần đầu.</p><p>Chi lắng nghe nhu cầu tinh tế — từ nhịp nghỉ của trẻ nhỏ đến sở thích ẩm thực — rồi chuyển thành lịch trình vừa vặn và ấm áp.</p>',
      'bio_html_en' => '<p>Fluent in multiple languages, Mai Chi is a trusted companion for European families visiting Vietnam for the first time.</p>',
      'name_en' => 'Le Mai Chi',
      'role_en' => 'Senior Travel Consultant',
      'short_bio_en' => 'Fluent in three languages — a trusted companion for European families visiting Vietnam.',
      'achievements' => 
      array (
        0 => 'Đồng hành hơn 400 gia đình khách châu Âu (2018–2026)',
        1 => 'Tỷ lệ giới thiệu lại (referral) trên 70% trong phân khúc gia đình',
      ),
      'skills' => 
      array (
        0 => 
        array (
          'skill' => 'Tư vấn gia đình & đa thế hệ',
          'percent' => 97,
        ),
        1 => 
        array (
          'skill' => 'Giao tiếp đa ngôn ngữ',
          'percent' => 95,
        ),
        2 => 
        array (
          'skill' => 'Thiết kế tour riêng',
          'percent' => 90,
        ),
        3 => 
        array (
          'skill' => 'Chăm sóc khách VIP',
          'percent' => 88,
        ),
      ),
      'experiences' => 
      array (
        0 => 
        array (
          'title' => 'Chuyên gia tư vấn cao cấp',
          'company' => 'ViTravel',
          'items' => 
          array (
            0 => 'Phụ trách thị trường Ý, Pháp và khách nói tiếng Anh',
            1 => 'Thiết kế tour riêng theo sở thích ẩm thực & nhịp nghỉ gia đình',
          ),
        ),
      ),
      'degrees' => 
      array (
        0 => 
        array (
          'title' => 'Cử nhân Ngôn ngữ Anh',
          'school' => 'Đại học Sư phạm TP.HCM',
          'items' => 
          array (
            0 => 'Chứng chỉ tiếng Ý B2',
          ),
        ),
      ),
    ),
    3 => 
    array (
      'slug' => 'hoang-anh-tuan',
      'name' => 'Hoàng Anh Tuấn',
      'role' => 'Điều hành tuyến miền Bắc',
      'bio' => 'Sinh ra ở Lào Cai, Tuấn thuộc từng khúc cua trên đèo Ô Quy Hồ và biết chính xác bản nào có mùa lúa đẹp nhất...',
      'phone' => '+84 214 3888 112',
      'email' => 'tuan.hoang@vitravel.dev',
      'area' => 'Lào Cai — Hà Giang — Sa Pa',
      'years_experience' => 14,
      'languages' => 
      array (
        0 => 'Tiếng Việt',
        1 => 'English',
        2 => 'H\'Mông (cơ bản)',
      ),
      'stat_clients' => 2500,
      'stat_tours' => 720,
      'stat_awards' => 4,
      'is_verified' => true,
      'bio_html' => '<p>Sinh ra ở Lào Cai, Hoàng Anh Tuấn thuộc từng khúc cua trên đèo Ô Quy Hồ và biết chính xác bản nào có mùa lúa đẹp nhất trong năm.</p><p>Tuấn điều hành các đoàn trekking và tour văn hóa miền núi với tiêu chí an toàn trước, trải nghiệm sâu sau — luôn tôn trọng cộng đồng địa phương.</p>',
      'bio_html_en' => '<p>Born in Lao Cai, Anh Tuan knows every bend of O Quy Ho Pass and which village has the best rice season.</p>',
      'name_en' => 'Hoang Anh Tuan',
      'role_en' => 'Northern Routes Operations',
      'short_bio_en' => 'Born in Lao Cai — knows every bend of O Quy Ho Pass and the best rice-season villages.',
      'achievements' => 
      array (
        0 => 'Hơn 700 đoàn trekking / văn hóa miền Bắc an toàn không sự cố lớn',
        1 => 'Đối tác lâu năm với cộng đồng bản ở Sa Pa, Hà Giang',
      ),
      'skills' => 
      array (
        0 => 
        array (
          'skill' => 'Trekking & an toàn núi',
          'percent' => 98,
        ),
        1 => 
        array (
          'skill' => 'Điều hành đoàn thực địa',
          'percent' => 95,
        ),
        2 => 
        array (
          'skill' => 'Hiểu biết văn hóa dân tộc',
          'percent' => 92,
        ),
        3 => 
        array (
          'skill' => 'Logistics miền núi',
          'percent' => 90,
        ),
      ),
      'experiences' => 
      array (
        0 => 
        array (
          'title' => 'Điều hành tuyến miền Bắc',
          'company' => 'ViTravel',
          'items' => 
          array (
            0 => 'Phụ trách Sa Pa, Hà Giang, Cao Bằng và các tuyến kết hợp',
            1 => 'Huấn luyện hướng dẫn viên địa phương về tiêu chuẩn an toàn',
          ),
        ),
        1 => 
        array (
          'title' => 'Hướng dẫn viên quốc tế',
          'company' => 'Độc lập / đối tác inbound',
          'items' => 
          array (
            0 => 'Dẫn đoàn trekking Fansipan và vòng cung Đông Bắc',
          ),
        ),
      ),
      'degrees' => 
      array (
        0 => 
        array (
          'title' => 'Chứng chỉ hướng dẫn viên quốc tế',
          'school' => 'Sở Du lịch Lào Cai',
          'items' => 
          array (
            0 => 'Chuyên tuyến trekking & văn hóa dân tộc',
          ),
        ),
      ),
    ),
  ),
  'videos' => 
  array (
    0 => 
    array (
      'title' => 'Hành trình xuyên Việt 14 ngày cùng gia đình chị Sarah',
      'description' => 'Những khoảnh khắc chân thật trên cung đường dài.',
      'date' => '05/07/2026',
      'duration' => '12:40',
      'tag' => 'Xuyên Việt',
      'image' => 'https://i.ytimg.com/vi/LXb3EKWsInQ/hqdefault.jpg',
      'imageSrcset' => NULL,
      'embedUrl' => 'https://www.youtube.com/embed/LXb3EKWsInQ?autoplay=1&rel=0&modestbranding=1&playsinline=1',
      'provider' => 'youtube',
      'youtubeId' => 'LXb3EKWsInQ',
    ),
    1 => 
    array (
      'title' => 'Một đêm trên du thuyền vịnh Lan Hạ',
      'description' => 'Bình minh trên vịnh — góc nhìn từ boong tàu.',
      'date' => '21/06/2026',
      'duration' => '08:15',
      'tag' => 'Vịnh Lan Hạ',
      'image' => 'https://i.ytimg.com/vi/sNPnbDn9b4k/hqdefault.jpg',
      'imageSrcset' => NULL,
      'embedUrl' => 'https://www.youtube.com/embed/sNPnbDn9b4k?autoplay=1&rel=0&modestbranding=1&playsinline=1',
      'provider' => 'youtube',
      'youtubeId' => 'sNPnbDn9b4k',
    ),
    2 => 
    array (
      'title' => 'Trekking mùa lúa chín Sa Pa — nhật ký bằng hình',
      'description' => 'Leo núi, bản làng và mùa vàng.',
      'date' => '02/06/2026',
      'duration' => '10:02',
      'tag' => 'Sa Pa',
      'image' => 'https://i.ytimg.com/vi/C0DPdy98e4c/hqdefault.jpg',
      'imageSrcset' => NULL,
      'embedUrl' => 'https://www.youtube.com/embed/C0DPdy98e4c?autoplay=1&rel=0&modestbranding=1&playsinline=1',
      'provider' => 'youtube',
      'youtubeId' => 'C0DPdy98e4c',
    ),
    3 => 
    array (
      'title' => 'Bình minh Angkor Wat qua ống kính khách hàng',
      'description' => 'Khoảnh khắc thiêng liêng trước đền tháp.',
      'date' => '18/05/2026',
      'duration' => '06:47',
      'tag' => 'Angkor',
      'image' => 'https://i.ytimg.com/vi/ScMzIvxBSi4/hqdefault.jpg',
      'imageSrcset' => NULL,
      'embedUrl' => 'https://www.youtube.com/embed/ScMzIvxBSi4?autoplay=1&rel=0&modestbranding=1&playsinline=1',
      'provider' => 'youtube',
      'youtubeId' => 'ScMzIvxBSi4',
    ),
  ),
  'gallery_albums' => 
  array (
    0 => 
    array (
      'title' => 'Gia đình Mitchell — Việt Nam & Campuchia 15 ngày',
      'photos' => 24,
      'date' => '07/2026',
    ),
    1 => 
    array (
      'title' => 'Đoàn khách Ý — Xuyên Việt 3 tuần',
      'photos' => 36,
      'date' => '06/2026',
    ),
    2 => 
    array (
      'title' => 'Trăng mật Tú & Ngân — Phú Quốc',
      'photos' => 18,
      'date' => '06/2026',
    ),
    3 => 
    array (
      'title' => 'Nhóm trekking Sa Pa mùa lúa',
      'photos' => 29,
      'date' => '05/2026',
    ),
    4 => 
    array (
      'title' => 'Du thuyền Hạ Long — kỷ niệm 20 năm ngày cưới',
      'photos' => 15,
      'date' => '04/2026',
    ),
    5 => 
    array (
      'title' => 'Đoàn khách Pháp — Mekong hai quốc gia',
      'photos' => 31,
      'date' => '03/2026',
    ),
  ),
  'usps' => 
  array (
    0 => 
    array (
      'icon' => 'expert',
      'sort' => 0,
      'vi' => 
      array (
        'title' => 'trải nghiệm bản địa',
        'description' => 'Hành trình do chuyên gia địa phương thiết kế, chọn đúng mùa đẹp, tuyến điểm hay và trải nghiệm văn hoá chân thực.',
      ),
      'en' => 
      array (
        'title' => 'local-led experiences',
        'description' => 'Curated by local experts who pick the best seasons, routes, and truly authentic cultural moments.',
      ),
    ),
    1 => 
    array (
      'icon' => 'refund',
      'sort' => 1,
      'vi' => 
      array (
        'title' => 'giá rõ ràng, minh bạch',
        'description' => 'Báo giá trọn gói, không chi phí ẩn. Chính sách huỷ linh hoạt và hoàn tiền rõ ràng ngay từ đầu.',
      ),
      'en' => 
      array (
        'title' => 'clear, transparent pricing',
        'description' => 'All-in pricing with no hidden fees. Flexible cancellation and clear refund terms from the start.',
      ),
    ),
    2 => 
    array (
      'icon' => 'value',
      'sort' => 2,
      'vi' => 
      array (
        'title' => 'giá trị xứng đáng',
        'description' => 'Dịch vụ chọn lọc chất lượng cao, tối ưu chi phí để bạn nhận được trải nghiệm tốt nhất trong ngân sách.',
      ),
      'en' => 
      array (
        'title' => 'best value for your trip',
        'description' => 'Carefully selected quality services to give you the best travel experience within your budget.',
      ),
    ),
    3 => 
    array (
      'icon' => 'support',
      'sort' => 3,
      'vi' => 
      array (
        'title' => 'hỗ trợ xuyên suốt',
        'description' => 'Đội ngũ đồng hành trước, trong và sau chuyến đi, hỗ trợ nhanh chóng để hành trình luôn suôn sẻ.',
      ),
      'en' => 
      array (
        'title' => 'seamless travel support',
        'description' => 'Dedicated support before, during, and after your trip, ensuring everything runs smoothly.',
      ),
    ),
  ),
  'offices' => 
  array (
    0 => 
    array (
      'city' => 'Hà Nội, Việt Nam',
      'address' => 'Tầng 5, 88 Xã Đàn, Đống Đa',
      'phone' => '+84 24 3999 8888',
    ),
    1 => 
    array (
      'city' => 'TP. Hồ Chí Minh, Việt Nam',
      'address' => '125 Nguyễn Huệ, Quận 1',
      'phone' => '+84 28 3888 9999',
    ),
    2 => 
    array (
      'city' => 'Siem Reap, Campuchia',
      'address' => 'Sivutha Blvd, Svay Dangkum',
      'phone' => '+855 63 96 8888',
    ),
  ),
  'values' => 
  array (
    0 => 
    array (
      'vi' => 
      array (
        'name' => 'Tận tâm',
        'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân',
      ),
      'en' => 
      array (
        'name' => 'Dedication',
        'desc' => 'Every trip is crafted with the care we give our own family',
      ),
    ),
    1 => 
    array (
      'vi' => 
      array (
        'name' => 'Thấu cảm',
        'desc' => 'Lắng nghe để hiểu điều bạn thực sự mong muốn',
      ),
      'en' => 
      array (
        'name' => 'Empathy',
        'desc' => 'We listen to understand what you truly want',
      ),
    ),
    2 => 
    array (
      'vi' => 
      array (
        'name' => 'Chân thành',
        'desc' => 'Tư vấn trung thực, giá cả minh bạch',
      ),
      'en' => 
      array (
        'name' => 'Sincerity',
        'desc' => 'Honest advice and transparent pricing',
      ),
    ),
    3 => 
    array (
      'vi' => 
      array (
        'name' => 'Trách nhiệm',
        'desc' => 'Du lịch bền vững, tôn trọng cộng đồng bản địa',
      ),
      'en' => 
      array (
        'name' => 'Responsibility',
        'desc' => 'Sustainable travel that respects local communities',
      ),
    ),
  ),
  'value_definitions' => 
  array (
    0 => 
    array (
      'vi' => 
      array (
        'name' => 'Tận tâm',
        'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân',
      ),
      'en' => 
      array (
        'name' => 'Dedication',
        'desc' => 'Every trip is crafted with the care we give our own family',
      ),
    ),
    1 => 
    array (
      'vi' => 
      array (
        'name' => 'Thấu cảm',
        'desc' => 'Lắng nghe để hiểu điều bạn thực sự mong muốn',
      ),
      'en' => 
      array (
        'name' => 'Empathy',
        'desc' => 'We listen to understand what you truly want',
      ),
    ),
    2 => 
    array (
      'vi' => 
      array (
        'name' => 'Chân thành',
        'desc' => 'Tư vấn trung thực, giá cả minh bạch',
      ),
      'en' => 
      array (
        'name' => 'Sincerity',
        'desc' => 'Honest advice and transparent pricing',
      ),
    ),
    3 => 
    array (
      'vi' => 
      array (
        'name' => 'Trách nhiệm',
        'desc' => 'Du lịch bền vững, tôn trọng cộng đồng bản địa',
      ),
      'en' => 
      array (
        'name' => 'Responsibility',
        'desc' => 'Sustainable travel that respects local communities',
      ),
    ),
  ),
  'reasons' => 
  array (
    0 => 
    array (
      'vi' => 
      array (
        'title' => 'Chuyên gia bản địa thiết kế tour riêng',
        'desc' => 'Đội ngũ sinh ra và lớn lên tại điểm đến, hiểu từng cung đường và mùa đẹp nhất.',
      ),
      'en' => 
      array (
        'title' => 'Local experts design private journeys',
        'desc' => 'Our team grew up at the destinations — we know every road and the best season to go.',
      ),
    ),
    1 => 
    array (
      'vi' => 
      array (
        'title' => 'Cam kết hoàn tiền rõ ràng',
        'desc' => 'Chính sách huỷ/hoàn minh bạch, được ghi rõ trong hợp đồng trước khi thanh toán.',
      ),
      'en' => 
      array (
        'title' => 'Clear refund commitment',
        'desc' => 'Transparent cancellation and refund terms written into the contract before payment.',
      ),
    ),
    2 => 
    array (
      'vi' => 
      array (
        'title' => 'Giá trị vượt trội trên từng đồng chi phí',
        'desc' => 'Làm việc trực tiếp với khách sạn, nhà thuyền — không qua trung gian.',
      ),
      'en' => 
      array (
        'title' => 'Outstanding value for every dollar',
        'desc' => 'We work directly with hotels and boat owners — no middlemen.',
      ),
    ),
    3 => 
    array (
      'vi' => 
      array (
        'title' => 'Hỗ trợ 24/7 trong suốt hành trình',
        'desc' => 'Hotline và WhatsApp trực người thật, phản hồi trong vòng 15 phút.',
      ),
      'en' => 
      array (
        'title' => '24/7 support throughout your trip',
        'desc' => 'Real people on hotline and WhatsApp, responding within 15 minutes.',
      ),
    ),
    4 => 
    array (
      'vi' => 
      array (
        'title' => 'Du lịch có trách nhiệm & bền vững',
        'desc' => 'Ưu tiên homestay bản địa, hạn chế nhựa dùng một lần trên mọi tour.',
      ),
      'en' => 
      array (
        'title' => 'Responsible & sustainable travel',
        'desc' => 'We favour local homestays and minimise single-use plastic on every tour.',
      ),
    ),
  ),
  'reason_definitions' => 
  array (
    0 => 
    array (
      'vi' => 
      array (
        'title' => 'Chuyên gia bản địa thiết kế tour riêng',
        'desc' => 'Đội ngũ sinh ra và lớn lên tại điểm đến, hiểu từng cung đường và mùa đẹp nhất.',
      ),
      'en' => 
      array (
        'title' => 'Local experts design private journeys',
        'desc' => 'Our team grew up at the destinations — we know every road and the best season to go.',
      ),
    ),
    1 => 
    array (
      'vi' => 
      array (
        'title' => 'Cam kết hoàn tiền rõ ràng',
        'desc' => 'Chính sách huỷ/hoàn minh bạch, được ghi rõ trong hợp đồng trước khi thanh toán.',
      ),
      'en' => 
      array (
        'title' => 'Clear refund commitment',
        'desc' => 'Transparent cancellation and refund terms written into the contract before payment.',
      ),
    ),
    2 => 
    array (
      'vi' => 
      array (
        'title' => 'Giá trị vượt trội trên từng đồng chi phí',
        'desc' => 'Làm việc trực tiếp với khách sạn, nhà thuyền — không qua trung gian.',
      ),
      'en' => 
      array (
        'title' => 'Outstanding value for every dollar',
        'desc' => 'We work directly with hotels and boat owners — no middlemen.',
      ),
    ),
    3 => 
    array (
      'vi' => 
      array (
        'title' => 'Hỗ trợ 24/7 trong suốt hành trình',
        'desc' => 'Hotline và WhatsApp trực người thật, phản hồi trong vòng 15 phút.',
      ),
      'en' => 
      array (
        'title' => '24/7 support throughout your trip',
        'desc' => 'Real people on hotline and WhatsApp, responding within 15 minutes.',
      ),
    ),
    4 => 
    array (
      'vi' => 
      array (
        'title' => 'Du lịch có trách nhiệm & bền vững',
        'desc' => 'Ưu tiên homestay bản địa, hạn chế nhựa dùng một lần trên mọi tour.',
      ),
      'en' => 
      array (
        'title' => 'Responsible & sustainable travel',
        'desc' => 'We favour local homestays and minimise single-use plastic on every tour.',
      ),
    ),
  ),
  'reference_persons' => 
  array (
    0 => 
    array (
      'name' => 'Mr. Claude Millet',
      'country' => 'Pháp',
      'email' => 'claude@vitravel.example',
      'phone' => '+33 6 12 34 56 78',
      'skype' => 'claude.millet',
      'image' => NULL,
      'imageSrcset' => NULL,
    ),
    1 => 
    array (
      'name' => 'Ms. Emma Rossi',
      'country' => 'Ý',
      'email' => 'emma@vitravel.example',
      'phone' => '+39 320 123 4567',
      'skype' => 'emma.rossi.travel',
      'image' => NULL,
      'imageSrcset' => NULL,
    ),
    2 => 
    array (
      'name' => 'Mr. David Chen',
      'country' => 'Úc',
      'email' => 'david@vitravel.example',
      'phone' => '+61 4 1234 5678',
      'skype' => 'david.chen.au',
      'image' => NULL,
      'imageSrcset' => NULL,
    ),
  ),
  'about_page' => 
  array (
    'vi' => 
    array (
      'seo_title' => 'Về chúng tôi — ViTravel, kết nối du khách quốc tế với Việt Nam & Đông Nam Á',
      'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ ViTravel — thiết kế hành trình riêng tại Việt Nam và các điểm đến Đông Nam Á cho du khách quốc tế.',
      'page_title' => 'Về chúng tôi',
      'page_subtitle' => 'Hành trình chân thật tại Việt Nam & Đông Nam Á — thiết kế bởi người bản địa yêu nghề',
      'banner' => 
      array (
        'src' => NULL,
        'srcset' => NULL,
        'alt' => 'Ảnh banner: đội ngũ ViTravel',
      ),
      'mission' => 
      array (
        'title' => 'Sứ mệnh của chúng tôi',
        'text' => 'Mang đến những hành trình chân thật giúp du khách quốc tế chạm vào đời sống, văn hoá và thiên nhiên Việt Nam cùng Đông Nam Á — đồng thời tạo sinh kế bền vững cho cộng đồng tại mỗi điểm đến chúng tôi đi qua.',
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'vision' => 
      array (
        'title' => 'Tầm nhìn của chúng tôi',
        'text' => 'Trở thành cầu nối tin cậy giữa du khách quốc tế với Việt Nam và Đông Nam Á — nơi mỗi người rời đi với cảm giác "hài lòng hơn cả mong đợi" và mang theo một phần trái tim của vùng đất đã đi qua.',
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'sales_policy' => 
      array (
        'title' => 'Chính sách bán hàng minh bạch',
        'content' => 'Mọi báo giá của ViTravel đều liệt kê rõ từng hạng mục — không phụ phí ẩn, không "giá từ" gây hiểu lầm. Trẻ em dưới 4 tuổi được miễn phí dịch vụ mặt đất; trẻ 4–10 tuổi giảm 25% giá tour khi ngủ chung giường với bố mẹ. Chính sách huỷ/hoàn tiền được ghi rõ trong hợp đồng: miễn phí trước 30 ngày, minh bạch theo từng mốc thời gian.',
        'cta_label' => 'Hỏi thêm về chính sách',
        'cta_url' => NULL,
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'values_section' => 
      array (
        'title' => 'Cam kết với giá trị cốt lõi',
        'hub_label' => 'Giá trị cốt lõi',
        'eyebrow' => 'Điều chúng tôi tin',
        'subtitle' => 'Bốn giá trị dẫn dắt mọi lịch trình chúng tôi thiết kế tại Việt Nam và Đông Nam Á.',
      ),
      'reasons_section' => 
      array (
        'title' => 'Vì sao chọn ViTravel?',
        'eyebrow' => 'Lý do đồng hành',
        'subtitle' => 'Am hiểu điểm đến, minh bạch và luôn có người bản địa bên bạn.',
        'cta_label' => 'Bắt đầu hành trình của bạn',
        'cta_url' => NULL,
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'reference_section' => 
      array (
        'title' => 'Người đại diện của chúng tôi tại nước ngoài',
        'eyebrow' => 'Mạng lưới toàn cầu',
        'subtitle' => 'Bạn có thể trao đổi trực tiếp bằng ngôn ngữ của mình với đại diện ViTravel tại châu Âu và châu Úc.',
      ),
    ),
    'en' => 
    array (
      'seo_title' => 'About us — ViTravel, connecting international travellers with Vietnam & Southeast Asia',
      'seo_description' => 'Our story, mission and team at ViTravel — designing private journeys across Vietnam and Southeast Asia for international guests.',
      'page_title' => 'About us',
      'page_subtitle' => 'Authentic journeys in Vietnam & Southeast Asia — designed by locals who love their craft',
      'banner' => 
      array (
        'src' => NULL,
        'srcset' => NULL,
        'alt' => 'ViTravel team banner',
      ),
      'mission' => 
      array (
        'title' => 'Our mission',
        'text' => 'To deliver authentic journeys that let international travellers touch local life, culture and nature across Vietnam and Southeast Asia — while creating sustainable livelihoods for communities along every route we travel.',
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'vision' => 
      array (
        'title' => 'Our vision',
        'text' => 'To become the trusted bridge between international guests and Vietnam & Southeast Asia — where every traveller leaves feeling “more than satisfied” and carries a piece of the places they visited.',
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'sales_policy' => 
      array (
        'title' => 'Transparent sales policy',
        'content' => 'Every ViTravel quote lists each line item clearly — no hidden fees, no misleading “from” prices. Children under 4 travel free on ground services; ages 4–10 get 25% off the tour price when sharing a bed with parents. Cancellation and refund terms are written into the contract: free before 30 days, transparent by each deadline.',
        'cta_label' => 'Ask about our policy',
        'cta_url' => NULL,
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'values_section' => 
      array (
        'title' => 'Commitment to our core values',
        'hub_label' => 'Core values',
        'eyebrow' => 'What we believe',
        'subtitle' => 'Four values that guide every itinerary we design across Vietnam and Southeast Asia.',
      ),
      'reasons_section' => 
      array (
        'title' => 'Why choose ViTravel?',
        'eyebrow' => 'Why travel with us',
        'subtitle' => 'Deep destination knowledge, clear pricing, and local people by your side.',
        'cta_label' => 'Start your journey',
        'cta_url' => NULL,
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'reference_section' => 
      array (
        'title' => 'Our representatives abroad',
        'eyebrow' => 'A global network',
        'subtitle' => 'Speak directly in your own language with ViTravel representatives in Europe and Australia.',
      ),
    ),
  ),
  'hero_pills' => 
  array (
    0 => 
    array (
      'category_slug' => 'viet-nam-3-tuan',
      'vi' => 
      array (
        'label' => 'Việt Nam 3 tuần',
      ),
      'en' => 
      array (
        'label' => 'Vietnam 3 Weeks',
      ),
      'url' => '/tours/viet-nam/viet-nam-3-tuan',
    ),
    1 => 
    array (
      'country_slug' => 'tour-ket-hop',
      'vi' => 
      array (
        'label' => 'Tour kết hợp',
      ),
      'en' => 
      array (
        'label' => 'Combined Tours',
      ),
      'url' => '/tours/tour-ket-hop',
    ),
  ),
  'home_sections' => 
  array (
    'company_intro' => 
    array (
      'vi' => 
      array (
        'key' => 'company_intro',
        'eyebrow' => 'Chuyên gia du lịch Việt',
        'title' => 'Hành trình chân thật, thiết kế bởi người bản địa',
        'subtitle' => NULL,
        'body' => 'ViTravel là đại lý lữ hành đặt trụ sở tại Việt Nam, kết nối du khách quốc tế với Việt Nam và Đông Nam Á. Chúng tôi không bán những tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của đội ngũ chuyên gia bản địa tại từng điểm đến.',
        'metaLine' => 'Giấy phép lữ hành quốc tế số 01-2234/TCDL-GP-LHQT',
        'ctaLabel' => 'Tìm hiểu về chúng tôi',
        'ctaUrl' => '/ve-chung-toi',
        'image' => NULL,
        'imageAlt' => 'Ảnh đội ngũ ViTravel tại văn phòng',
      ),
      'en' => 
      array (
        'key' => 'company_intro',
        'eyebrow' => 'Vietnam travel experts',
        'title' => 'Authentic journeys, designed by locals',
        'subtitle' => NULL,
        'body' => 'ViTravel is a Vietnam-based travel agency connecting international guests with Vietnam and Southeast Asia. We do not sell off-the-shelf packages — every itinerary is tailored from real on-the-ground experience.',
        'metaLine' => 'International travel license No. 01-2234/TCDL-GP-LHQT',
        'ctaLabel' => 'Learn about us',
        'ctaUrl' => '/ve-chung-toi',
        'image' => NULL,
        'imageAlt' => 'ViTravel team at our office',
      ),
    ),
    'featured_tours' => 
    array (
      'vi' => 
      array (
        'key' => 'featured_tours',
        'eyebrow' => 'Được yêu thích nhất',
        'title' => 'Những tour được yêu cầu nhiều nhất',
        'subtitle' => 'Ba hành trình được khách hàng đặt và đánh giá cao nhất trong 12 tháng qua.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'featured_tours',
        'eyebrow' => 'Most popular',
        'title' => 'Our most requested tours',
        'subtitle' => 'Three itineraries our guests book and rate highest over the past 12 months.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'featured_cruises' => 
    array (
      'vi' => 
      array (
        'key' => 'featured_cruises',
        'eyebrow' => 'Hành trình biển',
        'title' => 'Trải nghiệm trên mặt nước đáng nhớ',
        'subtitle' => 'Những hải trình được yêu thích nhất — nơi việc di chuyển trở thành một phần của trải nghiệm, mở ra góc nhìn trọn vẹn về biển đảo.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'featured_cruises',
        'eyebrow' => 'Sea journeys',
        'title' => 'Memorable experiences on the water',
        'subtitle' => 'Our most loved voyages — where getting there becomes part of the experience, opening a fuller view of islands and sea.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'featured_trains' => 
    array (
      'vi' => 
      array (
        'key' => 'featured_trains',
        'eyebrow' => 'Vé tàu hỏa',
        'title' => 'Di chuyển nhanh, đặt vé linh hoạt',
        'subtitle' => 'Chọn hạng ghế hoặc giường nằm theo từng tuyến. Chủ động thời gian, dễ dàng tích hợp vào lịch trình riêng của bạn.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'featured_trains',
        'eyebrow' => 'Train tickets',
        'title' => 'Travel fast, book with flexibility',
        'subtitle' => 'Choose seat or sleeper by route. Stay in control of your schedule and fit tickets into your own itinerary.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'support_services' => 
    array (
      'vi' => 
      array (
        'key' => 'support_services',
        'eyebrow' => 'Dịch vụ bổ trợ',
        'title' => 'Chỉ chọn những gì bạn cần',
        'subtitle' => 'Lưu trú, vui chơi và các dịch vụ hỗ trợ — linh hoạt đặt riêng theo kế hoạch, tối ưu trải nghiệm du lịch trọn vẹn hơn.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'support_services',
        'eyebrow' => 'Add-on services',
        'title' => 'Choose only what you need',
        'subtitle' => 'Stays, activities, and support services — book à la carte around your plan for a more complete trip.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'destinations' => 
    array (
      'vi' => 
      array (
        'key' => 'destinations',
        'eyebrow' => 'Điểm đến Đông Nam Á',
        'title' => 'Những điểm đến được yêu thích nhất',
        'subtitle' => 'Từ Việt Nam tới Campuchia, Thái Lan, Lào và Bali — chọn nơi bạn muốn lắng nghe câu chuyện bản địa.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'destinations',
        'eyebrow' => 'Southeast Asia destinations',
        'title' => 'Our most loved destinations',
        'subtitle' => 'From Vietnam to Cambodia, Thailand, Laos and Bali — choose where you want to hear local stories.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'testimonials' => 
    array (
      'vi' => 
      array (
        'key' => 'testimonials',
        'eyebrow' => 'Khách hàng kể lại',
        'title' => 'Trải nghiệm chân thật từ khách hàng',
        'subtitle' => 'Hơn 5.000 du khách đã đồng hành cùng chúng tôi — đây là những gì họ kể lại.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'Xem tất cả cảm nhận',
        'ctaUrl' => '/cam-nhan-khach-hang',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'testimonials',
        'eyebrow' => 'Guest stories',
        'title' => 'Real experiences from our travellers',
        'subtitle' => 'Over 5,000 guests have travelled with us — here is what they say.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'Read all reviews',
        'ctaUrl' => '/cam-nhan-khach-hang',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'review_platforms' => 
    array (
      'vi' => 
      array (
        'key' => 'review_platforms',
        'eyebrow' => NULL,
        'title' => 'ViTravel được đánh giá cao trên',
        'subtitle' => NULL,
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'review_platforms',
        'eyebrow' => NULL,
        'title' => 'ViTravel is highly rated on',
        'subtitle' => NULL,
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'team' => 
    array (
      'vi' => 
      array (
        'key' => 'team',
        'eyebrow' => 'Con người ViTravel',
        'title' => 'Đội ngũ tận tâm của chúng tôi',
        'subtitle' => 'Những con người bản địa hiểu điểm đến hơn bất kỳ ai — và sẽ đồng hành cùng bạn từ lúc lên ý tưởng tới khi về nhà.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'Gặp gỡ cả đội ngũ',
        'ctaUrl' => '/doi-ngu',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'team',
        'eyebrow' => 'The ViTravel team',
        'title' => 'Our dedicated local experts',
        'subtitle' => 'People who know the destinations better than anyone — with you from the first idea until you are home.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'Meet the full team',
        'ctaUrl' => '/doi-ngu',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'videos' => 
    array (
      'vi' => 
      array (
        'key' => 'videos',
        'eyebrow' => 'Trải nghiệm thật',
        'title' => 'Hành trình qua từng thước phim đẹp',
        'subtitle' => 'Video chân thật do khách hàng và đội ngũ ViTravel ghi lại — chọn một khoảnh khắc để xem toàn màn hình.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'Xem tất cả video',
        'ctaUrl' => '/video-trai-nghiem',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'videos',
        'eyebrow' => 'Real experiences',
        'title' => 'Journeys in unforgettable frames',
        'subtitle' => 'Authentic films from guests and our local team — tap any moment to watch full screen.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'View all videos',
        'ctaUrl' => '/video-trai-nghiem',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'quick_inquiry' => 
    array (
      'vi' => 
      array (
        'key' => 'quick_inquiry',
        'eyebrow' => 'Tư vấn miễn phí',
        'title' => 'Gửi lời nhắn cho chúng tôi',
        'subtitle' => NULL,
        'body' => 'Bạn chưa chắc nên đi đâu, đi mùa nào, ngân sách bao nhiêu? Để lại lời nhắn — chuyên gia bản địa của chúng tôi sẽ phản hồi trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>, hoàn toàn miễn phí.',
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'quick_inquiry',
        'eyebrow' => 'Free advice',
        'title' => 'Send us a message',
        'subtitle' => NULL,
        'body' => 'Not sure where to go, which season, or your budget? Leave a note — our local experts will reply within <strong class="font-semibold text-ink">1 business day</strong>, free of charge.',
        'metaLine' => NULL,
        'ctaLabel' => NULL,
        'ctaUrl' => NULL,
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
  ),
  'footer_columns' => 
  array (
    0 => 
    array (
      'title' => 'ViTravel',
      'links' => 
      array (
        0 => 
        array (
          'label' => 'Về chúng tôi',
          'route' => 
          array (
            0 => 'about',
          ),
        ),
        1 => 
        array (
          'label' => 'Cảm nhận khách hàng',
          'route' => 
          array (
            0 => 'reviews',
          ),
        ),
        2 => 
        array (
          'label' => 'Đội ngũ của chúng tôi',
          'route' => 
          array (
            0 => 'team',
          ),
        ),
        3 => 
        array (
          'label' => 'Thư viện khoảnh khắc',
          'route' => 
          array (
            0 => 'gallery',
          ),
        ),
        4 => 
        array (
          'label' => 'Nhận báo giá miễn phí',
          'route' => 
          array (
            0 => 'customize',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'Tour được yêu thích',
      'links' => 
      array (
        0 => 
        array (
          'label' => 'Việt Nam 10 ngày',
          'route' => 
          array (
            0 => 'tours.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'viet-nam-10-ngay-di-san-mien-bac',
            ),
          ),
        ),
        1 => 
        array (
          'label' => 'Xuyên Việt 2 tuần',
          'route' => 
          array (
            0 => 'tours.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'viet-nam-2-tuan-bac-trung-nam',
            ),
          ),
        ),
        2 => 
        array (
          'label' => 'Việt Nam & Campuchia 15 ngày',
          'route' => 
          array (
            0 => 'tours.show',
            1 => 
            array (
              'country' => 'tour-ket-hop',
              'slug' => 'viet-nam-campuchia-15-ngay',
            ),
          ),
        ),
        3 => 
        array (
          'label' => 'Việt Nam 3 tuần trọn vẹn',
          'route' => 
          array (
            0 => 'tours.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'viet-nam-3-tuan-tron-ven',
            ),
          ),
        ),
      ),
    ),
    2 => 
    array (
      'title' => 'Điểm đến nổi bật',
      'links' => 
      array (
        0 => 
        array (
          'label' => 'Vịnh Hạ Long',
          'route' => 
          array (
            0 => 'cruises.index',
            1 => 
            array (
              'type' => 'du-thuyen-ha-long',
            ),
          ),
        ),
        1 => 
        array (
          'label' => 'Sa Pa',
          'route' => 
          array (
            0 => 'tours.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'sa-pa-trekking-4-ngay',
            ),
          ),
        ),
        2 => 
        array (
          'label' => 'Hội An',
          'route' => 
          array (
            0 => 'guide.country',
            1 => 
            array (
              'country' => 'viet-nam',
            ),
          ),
        ),
        3 => 
        array (
          'label' => 'Phú Quốc',
          'route' => 
          array (
            0 => 'tours.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'phu-quoc-nghi-duong-5-ngay',
            ),
          ),
        ),
        4 => 
        array (
          'label' => 'Đồng bằng sông Cửu Long',
          'route' => 
          array (
            0 => 'cruises.index',
            1 => 
            array (
              'type' => 'du-thuyen-mekong',
            ),
          ),
        ),
      ),
    ),
    3 => 
    array (
      'title' => 'Cẩm nang du lịch',
      'links' => 
      array (
        0 => 
        array (
          'label' => 'Chi phí du lịch Việt Nam 10 ngày',
          'route' => 
          array (
            0 => 'guide.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'chi-phi-du-lich-viet-nam-10-ngay',
            ),
          ),
        ),
        1 => 
        array (
          'label' => 'Sa Pa mùa nào đẹp nhất?',
          'route' => 
          array (
            0 => 'guide.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'sa-pa-mua-nao-dep-nhat',
            ),
          ),
        ),
        2 => 
        array (
          'label' => 'Ăn gì ở Hội An trong 24 giờ',
          'route' => 
          array (
            0 => 'guide.show',
            1 => 
            array (
              'country' => 'viet-nam',
              'slug' => 'an-gi-o-hoi-an-24-gio',
            ),
          ),
        ),
        3 => 
        array (
          'label' => 'Bình minh Angkor Wat',
          'route' => 
          array (
            0 => 'guide.show',
            1 => 
            array (
              'country' => 'campuchia',
              'slug' => 'angkor-wat-kinh-nghiem-binh-minh',
            ),
          ),
        ),
      ),
    ),
  ),
  'footer_seo_links' => 
  array (
    0 => 
    array (
      'label' => 'Cẩm nang du lịch Việt Nam',
      'route' => 
      array (
        0 => 'guide.country',
        1 => 
        array (
          'country' => 'viet-nam',
        ),
      ),
    ),
    1 => 
    array (
      'label' => 'Cẩm nang du lịch Campuchia',
      'route' => 
      array (
        0 => 'guide.country',
        1 => 
        array (
          'country' => 'campuchia',
        ),
      ),
    ),
    2 => 
    array (
      'label' => 'Cẩm nang du lịch Lào',
      'route' => 
      array (
        0 => 'guide.country',
        1 => 
        array (
          'country' => 'lao',
        ),
      ),
    ),
    3 => 
    array (
      'label' => 'Tour Việt Nam trọn gói',
      'route' => 
      array (
        0 => 'tours.index',
        1 => 
        array (
          'country' => 'viet-nam',
        ),
      ),
    ),
    4 => 
    array (
      'label' => 'Du thuyền Hạ Long',
      'route' => 
      array (
        0 => 'cruises.index',
        1 => 
        array (
          'type' => 'du-thuyen-ha-long',
        ),
      ),
    ),
    5 => 
    array (
      'label' => 'Du thuyền Mekong',
      'route' => 
      array (
        0 => 'cruises.index',
        1 => 
        array (
          'type' => 'du-thuyen-mekong',
        ),
      ),
    ),
    6 => 
    array (
      'label' => 'Thiết kế tour riêng',
      'route' => 
      array (
        0 => 'customize',
      ),
    ),
    7 => 
    array (
      'label' => 'Video trải nghiệm',
      'route' => 
      array (
        0 => 'videos',
      ),
    ),
  ),
  'tour_categories' => 
  array (
    0 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'viet-nam-10-ngay',
      'type' => 'duration',
      'sort' => 0,
      'minDays' => 9,
      'maxDays' => 11,
      'name' => 
      array (
        'vi' => 'Tour Việt Nam 10 ngày',
        'en' => 'Vietnam 10 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Lựa chọn phổ biến nhất cho lần đầu khám phá Việt Nam — đủ thời gian đi Hà Nội, Hạ Long và Sa Pa.',
        'en' => 'The most popular choice for first-time visitors — enough time for Hanoi, Halong Bay and Sapa.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Việt Nam 10 ngày là lịch trình lý tưởng để cảm nhận miền Bắc: di sản, ẩm thực và cảnh quan thiên nhiên trong một hành trình cân bằng.',
        'en' => 'A 10-day Vietnam tour is the ideal itinerary to experience the north: heritage, cuisine and nature in one balanced journey.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Tour 10 ngày Việt Nam đi được những đâu?',
          'a' => 'Thường gồm Hà Nội, Ninh Bình, vịnh Hạ Long, Sa Pa hoặc Mai Châu — có thể tùy chỉnh thêm Hội An nếu bay nội địa.',
        ),
        1 => 
        array (
          'q' => '10 ngày có đủ cho gia đình có trẻ nhỏ?',
          'a' => 'Có. Lịch trình có ngày nghỉ xen kẽ, trekking Sa Pa có phương án đi xe thay thế.',
        ),
      ),
    ),
    1 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'viet-nam-2-tuan',
      'type' => 'duration',
      'sort' => 1,
      'minDays' => 12,
      'maxDays' => 16,
      'name' => 
      array (
        'vi' => 'Tour Việt Nam 2 tuần',
        'en' => 'Vietnam 2 Weeks Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Xuyên Việt Bắc – Trung – Nam trong 14 ngày, phù hợp ai muốn thấy trọn vẹn ba miền.',
        'en' => 'Cross Vietnam north to south in 14 days — ideal for seeing all three regions.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Việt Nam 2 tuần kết nối Hà Nội, Huế, Hội An và TP. Hồ Chí Minh — hành trình kinh điển được đặt nhiều nhất.',
        'en' => 'A two-week Vietnam tour links Hanoi, Hue, Hoi An and Ho Chi Minh City — our classic best-selling route.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Tour 2 tuần có mấy chặng bay nội địa?',
          'a' => 'Thường 1–2 chặng (Hà Nội–Huế/Đà Nẵng và Đà Nẵng–TP.HCM), đã bao gồm trong giá tour trọn gói.',
        ),
      ),
    ),
    2 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'viet-nam-15-ngay',
      'type' => 'duration',
      'sort' => 2,
      'minDays' => 14,
      'maxDays' => 16,
      'name' => 
      array (
        'vi' => 'Tour Việt Nam 15 ngày',
        'en' => 'Vietnam 15 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Thêm thời gian cho miền Trung và Đồng bằng sông Cửu Long so với lịch trình 10 ngày.',
        'en' => 'Extra time for central Vietnam and the Mekong Delta compared to a 10-day plan.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Việt Nam 15 ngày cho phép đi chậm hơn, ở lâu hơn ở Hội An và khám phá sâu miền Tây.',
        'en' => 'A 15-day Vietnam tour lets you travel slower, stay longer in Hoi An and explore the Mekong in depth.',
      ),
      'faqs' => 
      array (
      ),
    ),
    3 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'viet-nam-3-tuan',
      'type' => 'duration',
      'sort' => 3,
      'minDays' => 17,
      'maxDays' => 25,
      'name' => 
      array (
        'vi' => 'Tour Việt Nam 3 tuần',
        'en' => 'Vietnam 3 Weeks Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Hành trình trọn vẹn nhất: Hà Giang, cố đô Huế, Đà Lạt và mũi Cà Mau.',
        'en' => 'The most complete journey: Ha Giang, imperial Hue, Da Lat and Ca Mau cape.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Việt Nam 3 tuần dành cho du khách muốn trải nghiệm sâu từng vùng miền mà không vội vàng.',
        'en' => 'A 3-week Vietnam tour is for travellers who want an in-depth, unhurried experience of every region.',
      ),
      'faqs' => 
      array (
      ),
    ),
    4 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'duoi-7-ngay',
      'type' => 'duration',
      'sort' => 4,
      'minDays' => 1,
      'maxDays' => 8,
      'name' => 
      array (
        'vi' => 'Tour Việt Nam dưới 7 ngày',
        'en' => 'Vietnam Short Tours (under 7 days)',
      ),
      'subtitle' => 
      array (
        'vi' => 'Tour ngắn ngày: Sa Pa trekking, Phú Quốc nghỉ dưỡng, Hạ Long 2N1D.',
        'en' => 'Short breaks: Sapa trekking, Phu Quoc beach, Halong 2D1N.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Các tour Việt Nam dưới 7 ngày phù hợp nghỉ phép ngắn hoặc kết hợp công tác.',
        'en' => 'Vietnam tours under 7 days suit short holidays or business-trip extensions.',
      ),
      'faqs' => 
      array (
      ),
    ),
    5 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'mien-bac',
      'type' => 'region',
      'sort' => 10,
      'packageSlugs' => 
      array (
        0 => 'viet-nam-10-ngay-di-san-mien-bac',
        1 => 'sa-pa-trekking-4-ngay',
      ),
      'name' => 
      array (
        'vi' => 'Tour miền Bắc',
        'en' => 'Northern Vietnam Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Hà Nội, Hạ Long, Sa Pa, Ninh Bình và các vùng cao nguyên phía Bắc.',
        'en' => 'Hanoi, Halong Bay, Sapa, Ninh Binh and the northern highlands.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Miền Bắc Việt Nam nổi bật với di sản thiên nhiên Hạ Long, văn hoá bản địa Tây Bắc và ẩm thực phố cổ.',
        'en' => 'Northern Vietnam is known for Halong Bay, northwest ethnic culture and Hanoi street food.',
      ),
      'faqs' => 
      array (
      ),
    ),
    6 => 
    array (
      'countrySlug' => 'viet-nam',
      'slug' => 'mien-trung',
      'type' => 'region',
      'sort' => 11,
      'packageSlugs' => 
      array (
        0 => 'viet-nam-2-tuan-bac-trung-nam',
      ),
      'name' => 
      array (
        'vi' => 'Tour miền Trung',
        'en' => 'Central Vietnam Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Huế, Đà Nẵng, Hội An và di sản miền Trung.',
        'en' => 'Hue, Da Nang, Hoi An and central heritage sites.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Miền Trung mang đến cố đô Huế, phố cổ Hội An và bãi biển Mỹ Khê — trái tim di sản Việt Nam.',
        'en' => 'Central Vietnam offers imperial Hue, ancient Hoi An and My Khe beach — the heart of Vietnamese heritage.',
      ),
      'faqs' => 
      array (
      ),
    ),
    7 => 
    array (
      'countrySlug' => 'campuchia',
      'slug' => 'campuchia-7-ngay',
      'type' => 'duration',
      'sort' => 0,
      'minDays' => 5,
      'maxDays' => 9,
      'name' => 
      array (
        'vi' => 'Tour Campuchia 7 ngày',
        'en' => 'Cambodia 7 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Siem Reap & Angkor trong một tuần — lịch trình gọn cho người bận rộn.',
        'en' => 'Siem Reap & Angkor in one week — a compact itinerary for busy travellers.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Campuchia 7 ngày tập trung vào quần thể Angkor và làng nổi Tonlé Sap.',
        'en' => 'A 7-day Cambodia tour focuses on the Angkor complex and Tonlé Sap floating villages.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => '7 ngày ở Campuchia có cần visa không?',
          'a' => 'Khách Việt Nam được miễn visa. Khách quốc tịch khác có thể làm e-visa online — chúng tôi hỗ trợ trọn gói.',
        ),
      ),
    ),
    8 => 
    array (
      'countrySlug' => 'campuchia',
      'slug' => 'campuchia-10-ngay',
      'type' => 'duration',
      'sort' => 1,
      'minDays' => 9,
      'maxDays' => 14,
      'name' => 
      array (
        'vi' => 'Tour Campuchia 10 ngày',
        'en' => 'Cambodia 10 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Angkor, Phnom Penh và biển hồ Sihanoukville hoặc Kampot.',
        'en' => 'Angkor, Phnom Penh and the coast at Sihanoukville or Kampot.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Campuchia 10 ngày kết hợp di sản Khmer và nhịp sống thủ đô Phnom Penh.',
        'en' => 'A 10-day Cambodia tour combines Khmer heritage with the rhythm of capital Phnom Penh.',
      ),
      'faqs' => 
      array (
      ),
    ),
    9 => 
    array (
      'countrySlug' => 'campuchia',
      'slug' => 'di-san-angkor',
      'type' => 'theme',
      'sort' => 2,
      'name' => 
      array (
        'vi' => 'Tour di sản Angkor',
        'en' => 'Angkor Heritage Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Chuyên sâu đền tháp Angkor Wat, Bayon, Ta Prohm và Banteay Srei.',
        'en' => 'In-depth temples: Angkor Wat, Bayon, Ta Prohm and Banteay Srei.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Các tour di sản Angkor được thiết kế cho người yêu lịch sử và nhiếp ảnh — bình minh tại Angkor Wat là điểm nhấn.',
        'en' => 'Angkor heritage tours are designed for history and photography lovers — sunrise at Angkor Wat is the highlight.',
      ),
      'faqs' => 
      array (
      ),
    ),
    10 => 
    array (
      'countrySlug' => 'bali',
      'slug' => 'bali-7-ngay',
      'type' => 'duration',
      'sort' => 0,
      'minDays' => 5,
      'maxDays' => 9,
      'name' => 
      array (
        'vi' => 'Tour Bali 7 ngày',
        'en' => 'Bali 7 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Ubud, đền Tanah Lot và bãi biển Seminyak trong một tuần.',
        'en' => 'Ubud, Tanah Lot temple and Seminyak beach in one week.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Bali 7 ngày cân bằng giữa văn hoá đền chùa và thời gian thư giãn bên biển.',
        'en' => 'A 7-day Bali tour balances temple culture with beach relaxation.',
      ),
      'faqs' => 
      array (
      ),
      'packageSlugs' => 
      array (
        0 => 'bali-7-ngay-ubud-bien',
      ),
    ),
    11 => 
    array (
      'countrySlug' => 'bali',
      'slug' => 'bali-10-ngay',
      'type' => 'duration',
      'sort' => 1,
      'minDays' => 9,
      'maxDays' => 14,
      'name' => 
      array (
        'vi' => 'Tour Bali 10 ngày',
        'en' => 'Bali 10 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Khám phá Ubud, Nusa Penida và các resort biển phía nam.',
        'en' => 'Explore Ubud, Nusa Penida and southern beach resorts.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Bali 10 ngày cho phép thêm ngày nghỉ resort và chuyến đi Nusa Penida trong ngày.',
        'en' => 'A 10-day Bali tour adds resort time and a Nusa Penida day trip.',
      ),
      'faqs' => 
      array (
      ),
    ),
    12 => 
    array (
      'countrySlug' => 'bali',
      'slug' => 'nghi-duong-bali',
      'type' => 'theme',
      'sort' => 2,
      'packageSlugs' => 
      array (
        0 => 'bali-7-ngay-ubud-bien',
      ),
      'name' => 
      array (
        'vi' => 'Tour nghỉ dưỡng Bali',
        'en' => 'Bali Beach & Wellness Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Resort 5 sao, spa và bãi biển riêng tư — lý tưởng trăng mật.',
        'en' => '5-star resorts, spa and private beaches — ideal for honeymoons.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Các tour nghỉ dưỡng Bali kết hợp villa hướng biển, yoga và ẩm thực healthy tại Ubud.',
        'en' => 'Bali wellness tours combine ocean-view villas, yoga and healthy cuisine in Ubud.',
      ),
      'faqs' => 
      array (
      ),
    ),
    13 => 
    array (
      'countrySlug' => 'thai-lan',
      'slug' => 'thai-lan-7-ngay',
      'type' => 'duration',
      'sort' => 0,
      'minDays' => 5,
      'maxDays' => 9,
      'name' => 
      array (
        'vi' => 'Tour Thái Lan 7 ngày',
        'en' => 'Thailand 7 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Bangkok, Ayutthaya và một điểm biển (Phuket hoặc Krabi).',
        'en' => 'Bangkok, Ayutthaya and a beach stop (Phuket or Krabi).',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Thái Lan 7 ngày là lựa chọn phổ biến kết hợp chùa vàng Bangkok với biển đảo phía nam.',
        'en' => 'A 7-day Thailand tour is the popular mix of Bangkok temples and southern islands.',
      ),
      'faqs' => 
      array (
      ),
    ),
    14 => 
    array (
      'countrySlug' => 'thai-lan',
      'slug' => 'thai-lan-10-ngay',
      'type' => 'duration',
      'sort' => 1,
      'minDays' => 9,
      'maxDays' => 14,
      'packageSlugs' => 
      array (
        0 => 'thai-lan-10-ngay-bac-trung-nam',
      ),
      'name' => 
      array (
        'vi' => 'Tour Thái Lan 10 ngày',
        'en' => 'Thailand 10 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Bắc – Trung – Nam: Chiang Mai, Bangkok và biển phía nam.',
        'en' => 'North to south: Chiang Mai, Bangkok and southern beaches.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Thái Lan 10 ngày khám phá cả văn hoá Lanna ở Chiang Mai lẫn sự sôi động của Bangkok.',
        'en' => 'A 10-day Thailand tour covers Lanna culture in Chiang Mai and vibrant Bangkok.',
      ),
      'faqs' => 
      array (
      ),
    ),
    15 => 
    array (
      'countrySlug' => 'lao',
      'slug' => 'lao-7-ngay',
      'type' => 'duration',
      'sort' => 0,
      'minDays' => 5,
      'maxDays' => 9,
      'name' => 
      array (
        'vi' => 'Tour Lào 7 ngày',
        'en' => 'Laos 7 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Luang Prabang, Pak Ou và thác Kuang Si — nhịp sống chậm bên Mekong.',
        'en' => 'Luang Prabang, Pak Ou caves and Kuang Si falls — slow life on the Mekong.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Lào 7 ngày tập trung vào Luang Prabang — di sản UNESCO và chợ đêm ven sông.',
        'en' => 'A 7-day Laos tour focuses on UNESCO Luang Prabang and riverside night markets.',
      ),
      'faqs' => 
      array (
      ),
    ),
    16 => 
    array (
      'countrySlug' => 'lao',
      'slug' => 'lao-10-ngay',
      'type' => 'duration',
      'sort' => 1,
      'minDays' => 9,
      'maxDays' => 14,
      'name' => 
      array (
        'vi' => 'Tour Lào 10 ngày',
        'en' => 'Laos 10 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Luang Prabang, Vang Vieng và Vientiane — khám phá trọn miền trung Lào.',
        'en' => 'Luang Prabang, Vang Vieng and Vientiane — central Laos in depth.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Lào 10 ngày thêm thời gian cho Vang Vieng và thủ đô Vientiane so với lịch trình 7 ngày.',
        'en' => 'A 10-day Laos tour adds Vang Vieng and capital Vientiane to the 7-day route.',
      ),
      'faqs' => 
      array (
      ),
    ),
    17 => 
    array (
      'countrySlug' => 'tour-ket-hop',
      'slug' => 'dong-duong-15-ngay',
      'type' => 'package',
      'sort' => 0,
      'minDays' => 13,
      'maxDays' => 17,
      'packageSlugs' => 
      array (
        0 => 'viet-nam-campuchia-15-ngay',
      ),
      'name' => 
      array (
        'vi' => 'Tour Đông Dương 15 ngày',
        'en' => 'Indochina 15 Days Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Việt Nam & Campuchia trong một hành trình — Angkor và Mekong.',
        'en' => 'Vietnam & Cambodia in one journey — Angkor and the Mekong.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Đông Dương 15 ngày là hành trình bán chạy nhất, kết nối di sản Việt Nam với Angkor Wat.',
        'en' => 'The 15-day Indochina tour is our bestseller, linking Vietnamese heritage with Angkor Wat.',
      ),
      'faqs' => 
      array (
        0 => 
        array (
          'q' => 'Tour kết hợp có cần visa cả hai nước?',
          'a' => 'Chúng tôi hỗ trợ e-visa Campuchia trọn gói; khách Việt Nam miễn visa Campuchia.',
        ),
      ),
    ),
    18 => 
    array (
      'countrySlug' => 'tour-ket-hop',
      'slug' => 'dong-duong-3-tuan',
      'type' => 'package',
      'sort' => 1,
      'minDays' => 18,
      'maxDays' => 25,
      'name' => 
      array (
        'vi' => 'Tour Đông Dương 3 tuần',
        'en' => 'Indochina 3 Weeks Tours',
      ),
      'subtitle' => 
      array (
        'vi' => 'Việt Nam, Campuchia, Lào và Thái Lan — trọn vẹn Đông Dương.',
        'en' => 'Vietnam, Cambodia, Laos and Thailand — full Indochina.',
      ),
      'seo_body' => 
      array (
        'vi' => 'Tour Đông Dương 3 tuần dành cho ai muốn một chuyến đi để đời xuyên suốt bán đảo.',
        'en' => 'A 3-week Indochina tour is for travellers wanting a once-in-a-lifetime peninsula journey.',
      ),
      'faqs' => 
      array (
      ),
    ),
  ),
  'listing_faqs' => 
  array (
    0 => 
    array (
      'q' => 'Thời tiết Việt Nam tháng nào đẹp nhất để đi tour trọn gói?',
      'a' => 'Tháng 9 – 11 và tháng 3 – 4 là thời điểm dễ chịu trên cả ba miền. Miền Bắc đẹp nhất mùa thu, miền Nam khô ráo từ tháng 12 đến tháng 4.',
    ),
    1 => 
    array (
      'q' => 'Tour trọn gói đã bao gồm vé máy bay quốc tế chưa?',
      'a' => 'Chưa — giá tour bao gồm toàn bộ dịch vụ mặt đất, bữa ăn theo chương trình và các chặng bay nội địa (nếu có ghi rõ). Chúng tôi có thể hỗ trợ săn vé quốc tế giá tốt.',
    ),
    2 => 
    array (
      'q' => 'Tôi có thể đặt tour riêng (private) cho gia đình không?',
      'a' => 'Được. Mọi tour trên website đều có phương án private với xe riêng và hướng dẫn viên riêng — dùng form "Thiết kế tour riêng" để nhận báo giá trong 24 giờ.',
    ),
    3 => 
    array (
      'q' => 'Chính sách huỷ tour như thế nào?',
      'a' => 'Miễn phí huỷ trước 30 ngày khởi hành; trước 15 ngày thu 25%; trước 7 ngày thu 50%. Chi tiết được ghi rõ trong hợp đồng.',
    ),
    4 => 
    array (
      'q' => 'Có cần visa khi đi tour kết hợp Việt Nam – Campuchia?',
      'a' => 'Khách Việt Nam được miễn visa Campuchia. Khách quốc tịch khác được chúng tôi hỗ trợ e-visa trọn gói, thường có kết quả trong 3 ngày làm việc.',
    ),
  ),
  'duration_buckets' => 
  array (
    'lt7' => 'Dưới 7 ngày',
    '7-10' => '7 – 10 ngày',
    '11-15' => '11 – 15 ngày',
    'gt16' => 'Trên 16 ngày',
  ),
  'travel_style_labels' => 
  array (
    'long-duration' => 'Tour dài ngày',
    'heritage-rich' => 'Nhiều di sản',
    'nature-homestay' => 'Thiên nhiên & homestay',
    'culture-history' => 'Văn hoá & lịch sử',
    'balanced' => 'Kỳ nghỉ cân bằng',
    'beach' => 'Nghỉ dưỡng biển',
    'honeymoon' => 'Trăng mật',
    'family' => 'Gia đình',
    'trekking' => 'Trekking & khám phá',
    'multi-country-combo' => 'Tour kết hợp nhiều nước',
    'small-group' => 'Nhóm nhỏ',
  ),
);

/* ── company + catalogue dịch vụ (gom trong seed dự án — 1 file / 1 PROJECT_SEED) ── */
$__servicesSeed = [
    'service_clusters' => [
        ['code' => 'train', 'nav_label' => 'Tàu', 'label' => 'Vé tàu hỏa', 'icon' => 'train', 'hub_key' => 'trains_hub', 'sort' => 1],
        ['code' => 'flight', 'nav_label' => 'Máy bay', 'label' => 'Vé máy bay', 'icon' => 'plane', 'hub_key' => 'flights_hub', 'sort' => 2],
        ['code' => 'stay', 'nav_label' => 'Lưu trú', 'label' => 'Khách sạn & Resort', 'icon' => 'building', 'hub_key' => 'stays_hub', 'sort' => 3],
        ['code' => 'experience', 'nav_label' => 'Vui chơi', 'label' => 'Vé vui chơi & trải nghiệm', 'icon' => 'sparkles', 'hub_key' => 'experiences_hub', 'sort' => 4],
        ['code' => 'other', 'nav_label' => 'Dịch vụ', 'label' => 'Dịch vụ khác', 'icon' => 'briefcase', 'hub_key' => 'extras_hub', 'sort' => 5],
    ],

    'service_categories' => [
        // TRAIN
        ['cluster' => 'train', 'slug' => 'ha-noi-da-nang', 'name' => 'Hà Nội — Đà Nẵng', 'sort' => 1, 'intro' => 'Tuyến Bắc — Trung xuyên dốc Hải Vân, phù hợp kết hợp tour miền Trung.'],
        ['cluster' => 'train', 'slug' => 'ha-noi-sai-gon', 'name' => 'Hà Nội — Sài Gòn', 'sort' => 2, 'intro' => 'Tuyến xuyên Việt dài nhất, trải nghiệm đất nước hình chữ S trên đường ray.'],
        ['cluster' => 'train', 'slug' => 'da-nang-sai-gon', 'name' => 'Đà Nẵng — Sài Gòn', 'sort' => 3, 'intro' => 'Kết nối miền Trung và Nam, lý tưởng sau khi khám phá Hội An — Huế.'],
        // FLIGHT
        ['cluster' => 'flight', 'slug' => 'noi-dia', 'name' => 'Vé nội địa', 'sort' => 1, 'intro' => 'Bay nhanh giữa các thành phố lớn — tiết kiệm thời gian so với đường bộ.'],
        ['cluster' => 'flight', 'slug' => 'quoc-te-chau-a', 'name' => 'Quốc tế châu Á', 'sort' => 2, 'intro' => 'Gói vé máy bay kết hợp tour Việt Nam — Bangkok, Singapore, Tokyo và hơn thế.'],
        ['cluster' => 'flight', 'slug' => 'thue-may-bay-rieng', 'name' => 'Thuê máy bay riêng', 'sort' => 3, 'intro' => 'Charter jet & trực thăng cho doanh nghiệp, đoàn VIP và sự kiện đặc biệt.'],
        // STAY
        ['cluster' => 'stay', 'slug' => 'phu-quoc', 'name' => 'Phú Quốc', 'sort' => 1, 'intro' => 'Resort biển đảo ngọc — bãi cát trắng, sunset và ẩm thực hải sản.'],
        ['cluster' => 'stay', 'slug' => 'da-nang-hoi-an', 'name' => 'Đà Nẵng & Hội An', 'sort' => 2, 'intro' => 'Resort ven biển Mỹ Khê, Sơn Trà và boutique gần phố cổ.'],
        ['cluster' => 'stay', 'slug' => 'nha-trang', 'name' => 'Nha Trang & Cam Ranh', 'sort' => 3, 'intro' => 'Bãi biển nhiệt đới, Vinpearl và resort hạng sang vịnh Cam Ranh.'],
        ['cluster' => 'stay', 'slug' => 'ha-long-cat-ba', 'name' => 'Hạ Long & miền Bắc', 'sort' => 4, 'intro' => 'Resort vịnh di sản, Sapa và nghỉ dưỡng núi rừng Tây Bắc.'],
        // EXPERIENCE
        ['cluster' => 'experience', 'slug' => 'vinpearl', 'name' => 'Vinpearl & công viên', 'sort' => 1, 'intro' => 'Vé vào cửa, combo safari và trải nghiệm giải trí VinGroup.'],
        ['cluster' => 'experience', 'slug' => 'cap-treo', 'name' => 'Cáp treo & Sun World', 'sort' => 2, 'intro' => 'Fansipan, Bà Nà Hills, Hòn Thơm — ngắm toàn cảnh từ trên cao.'],
        ['cluster' => 'experience', 'slug' => 'du-luon', 'name' => 'Dù lượn & bay nhẹ', 'sort' => 3, 'intro' => 'Paragliding Đà Nẵng, Đà Lạt và trải nghiệm bay tandem an toàn.'],
        ['cluster' => 'experience', 'slug' => 'cano', 'name' => 'Kayak & làng chài', 'sort' => 4, 'intro' => 'Chèo kayak, thuyền nan và thăm làng chài vịnh Hạ Long, Nha Trang.'],
        ['cluster' => 'experience', 'slug' => 'the-thao-bien', 'name' => 'Thể thao biển', 'sort' => 5, 'intro' => 'Jet ski, parasailing, lặn biển và các hoạt động mặn nước.'],
        // OTHER
        ['cluster' => 'other', 'slug' => 'thue-xe', 'name' => 'Thuê xe', 'sort' => 1],
        ['cluster' => 'other', 'slug' => 'spa', 'name' => 'Spa & wellness', 'sort' => 2],
        ['cluster' => 'other', 'slug' => 'massage', 'name' => 'Massage & trị liệu', 'sort' => 3],
        ['cluster' => 'other', 'slug' => 'shipper', 'name' => 'Gửi hành lý', 'sort' => 4],
        ['cluster' => 'other', 'slug' => 'huong-dan-vien', 'name' => 'Hướng dẫn viên riêng', 'sort' => 5],
        ['cluster' => 'other', 'slug' => 'y-te', 'name' => 'Hỗ trợ y tế', 'sort' => 6],
        ['cluster' => 'other', 'slug' => 'khan-cap', 'name' => 'Hỗ trợ khẩn cấp 24/7', 'sort' => 7],
    ],

    'services' => [
        // ── TRAIN (4) ────────────────────────────────────────────────────────
        [
            'code' => 'train-se1-soft-han-dad',
            'cluster' => 'train',
            'category_slug' => 'ha-noi-da-nang',
            'country_slug' => 'viet-nam',
            'title' => 'Tàu SE1 — Hà Nội → Đà Nẵng (Ghế mềm điều hoà)',
            'slug' => 'tau-se1-ha-noi-da-nang-ghe-mem',
            'price_from' => 450000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 312,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Ga Hà Nội → Ga Đà Nẵng',
            'summary' => 'Chuyến tàu SE1 khởi hành buổi tối từ Hà Nội, đến Đà Nẵng sáng hôm sau — tiết kiệm một đêm khách sạn và ngắm cảnh dọc đường qua Huế, đèo Hải Vân.',
            'highlights' => [
                'Ghế mềm điều hoà toa 5–6, chỗ ngồi rộng rãi',
                'Xuất phát 19:30 ga Hà Nội, đến Đà Nẵng ~11:30 sáng hôm sau',
                'Phục vụ suất ăn nhẹ và nước uống trên tàu (tùy toa)',
                'Phù hợp du khách muốn tiết kiệm thời gian và chi phí so với bay',
                'ViTravel hỗ trợ đặt vé, giao tận tay hoặc e-ticket',
            ],
            'inclusions' => ['Vé ghế mềm điều hoà theo hành trình', 'Phí dịch vụ đặt vé ViTravel', 'Hướng dẫn lên tàu qua Zalo/email'],
            'exclusions' => ['Suất ăn chính (có thể đặt thêm trên tàu)', 'Đưa đón sân bay/ khách sạn', 'Bảo hiểm du lịch'],
            'notes' => ['Giá tham khảo theo mùa cao điểm Tết — vui lòng báo giá lại trước khi thanh toán.', 'Trẻ em dưới 4 tuổi miễn phí nếu ngồi cùng ba mẹ, không chiếm ghế.'],
            'attrs' => [
                'from' => 'Hà Nội',
                'to' => 'Đà Nẵng',
                'duration_hours' => 16,
                'operator' => 'Đường sắt Việt Nam',
                'train_class' => 'ghế mềm điều hoà',
                'train_number' => 'SE1',
            ],
            'options' => [
                ['code' => 'se1-soft-standard', 'name' => 'Ghế mềm toa 5', 'price_from' => 450000],
                ['code' => 'se1-soft-vip', 'name' => 'Ghế mềm toa 6 (VIP)', 'price_from' => 520000],
            ],
            'faqs' => [
                ['q' => 'Có thể đổi ngày sau khi mua vé không?', 'a' => 'Đổi ngày trước 12 giờ so với giờ tàu chạy, phí đổi theo quy định ĐSVN (thường 20–30% giá vé).'],
            ],
            'en' => [
                'title' => 'SE1 Train — Hanoi to Da Nang (Air-conditioned Soft Seat)',
                'summary' => 'Overnight SE1 service departing Hanoi in the evening, arriving Da Nang next morning — save a hotel night and enjoy the scenic route via Hue and Hai Van Pass.',
            ],
        ],
        [
            'code' => 'train-se1-sleeper-han-dad',
            'cluster' => 'train',
            'category_slug' => 'ha-noi-da-nang',
            'country_slug' => 'viet-nam',
            'title' => 'Tàu SE1 — Hà Nội → Đà Nẵng (Giường nằm 4–6 chỗ)',
            'slug' => 'tau-se1-ha-noi-da-nang-giuong-nam',
            'price_from' => 780000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 198,
            'is_featured' => true,
            'is_hot_deal' => true,
            'discount_badge' => 'Giường nằm',
            'location_label' => 'Ga Hà Nội → Ga Đà Nẵng',
            'summary' => 'Giường nằm kín điều hoà trên tàu SE1 — nghỉ ngơi thoải mái suốt hành trình xuyên đêm, phù hợp gia đình và cặp đôi.',
            'highlights' => [
                'Giường nằm 4 hoặc 6 chỗ, chăn ga gối sạch sẽ',
                'Toa kín có điều hoà, ổ cắm sạc điện thoại',
                'Hành trình qua Huế — đèo Hải Vân — Đà Nẵng',
                'Nhân viên tàu hỗ trợ suốt chuyến đi',
                'ViTravel ưu tiên giường dưới khi còn chỗ',
            ],
            'inclusions' => ['Vé giường nằm theo hành trình', 'Chăn ga gối trên tàu', 'Phí đặt vé ViTravel'],
            'exclusions' => ['Bữa ăn (đặt thêm trên tàu hoặc mang theo)', 'Đưa đón ga'],
            'notes' => ['Giường 4 chỗ đắt hơn giường 6 chỗ khoảng 150.000–200.000đ/vé.', 'Mang theo CMND/CCCD khi lên tàu.'],
            'attrs' => [
                'from' => 'Hà Nội',
                'to' => 'Đà Nẵng',
                'duration_hours' => 16,
                'operator' => 'Đường sắt Việt Nam',
                'train_class' => 'giường nằm kín điều hoà',
                'train_number' => 'SE1',
            ],
            'options' => [
                ['code' => 'se1-sleeper-6', 'name' => 'Giường nằm 6 chỗ', 'price_from' => 780000],
                ['code' => 'se1-sleeper-4', 'name' => 'Giường nằm 4 chỗ', 'price_from' => 950000],
            ],
        ],
        [
            'code' => 'train-se3-soft-han-sgn',
            'cluster' => 'train',
            'category_slug' => 'ha-noi-sai-gon',
            'country_slug' => 'viet-nam',
            'title' => 'Tàu SE3 — Hà Nội → Sài Gòn (Ghế mềm điều hoà)',
            'slug' => 'tau-se3-ha-noi-sai-gon-ghe-mem',
            'price_from' => 850000,
            'currency' => 'VND',
            'rating' => 4.5,
            'review_count' => 245,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Ga Hà Nội → Ga Sài Gòn',
            'summary' => 'Hành trình xuyên Việt ~30 giờ trên tàu SE3 — trải nghiệm đích thực đất nước hình chữ S, dừng chân tại các thành phố lớn dọc đường.',
            'highlights' => [
                'Tuyến dài nhất Việt Nam — trải nghiệm độc đáo cho du khách thích khám phá',
                'Ghế mềm điều hoà, có thể đi bộ trên boong tàu',
                'Đi qua Vinh, Huế, Đà Nẵng, Nha Trang trước khi đến Sài Gòn',
                'Giá vé hợp lý so với nhiều chặng bay nối chuyến',
                'ViTravel tư vấn lịch trình kết hợp dừng chân',
            ],
            'inclusions' => ['Vé ghế mềm SE3 toàn tuyến', 'Phí đặt vé'],
            'exclusions' => ['Suất ăn', 'Khách sạn nghỉ giữa chặng nếu xuống ga dừng'],
            'notes' => ['Có thể mua vé từng chặng (vd: Hà Nội — Huế) — liên hệ tư vấn.'],
            'attrs' => [
                'from' => 'Hà Nội',
                'to' => 'Sài Gòn',
                'duration_hours' => 30,
                'operator' => 'Đường sắt Việt Nam',
                'train_class' => 'ghế mềm điều hoà',
                'train_number' => 'SE3',
            ],
        ],
        [
            'code' => 'train-se3-sleeper-han-sgn',
            'cluster' => 'train',
            'category_slug' => 'ha-noi-sai-gon',
            'country_slug' => 'viet-nam',
            'title' => 'Tàu SE3/SE4 — Hà Nội ↔ Sài Gòn (Giường nằm)',
            'slug' => 'tau-se3-se4-ha-noi-sai-gon-giuong-nam',
            'price_from' => 1350000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 167,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Ga Hà Nội ↔ Ga Sài Gòn',
            'summary' => 'Giường nằm kín trên tuyến Bắc — Nam dài nhất — nghỉ ngơi trọn vẹn hai đêm trên tàu, lựa chọn của du khách muốn trải nghiệm đường sắt Việt Nam đầy đủ.',
            'highlights' => [
                'Giường nằm 4/6 chỗ, điều hoà suốt hành trình',
                'SE3 chiều Bắc → Nam, SE4 chiều Nam → Bắc',
                'Phù hợp backpacker và du khách thích di chuyển chậm',
                'Có thể kết hợp xuống ga Nha Trang, Đà Nẵng giữa đường',
                'ViTravel hỗ trợ đặt khứ hồi và vé từng chặng',
            ],
            'inclusions' => ['Vé giường nằm một chiều', 'Chăn ga gối', 'Phí dịch vụ đặt vé'],
            'exclusions' => ['Vé chiều về (đặt riêng)', 'Ăn uống trên tàu'],
            'notes' => ['Hành trình ~30–32 giờ tùy tàu và thời điểm trong năm.'],
            'attrs' => [
                'from' => 'Hà Nội',
                'to' => 'Sài Gòn',
                'duration_hours' => 31,
                'operator' => 'Đường sắt Việt Nam',
                'train_class' => 'giường nằm kín',
                'train_number' => 'SE3/SE4',
            ],
            'options' => [
                ['code' => 'se3-sleeper-6', 'name' => 'Giường 6 chỗ', 'price_from' => 1350000],
                ['code' => 'se3-sleeper-4', 'name' => 'Giường 4 chỗ', 'price_from' => 1580000],
            ],
        ],

        // ── FLIGHT (4) ───────────────────────────────────────────────────────
        [
            'code' => 'flight-sgn-han-economy',
            'cluster' => 'flight',
            'category_slug' => 'noi-dia',
            'country_slug' => 'viet-nam',
            'title' => 'Vé máy bay Sài Gòn — Hà Nội (Economy)',
            'slug' => 've-may-bay-sai-gon-ha-noi',
            'price_from' => 1290000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 892,
            'is_featured' => true,
            'is_hot_deal' => true,
            'discount_badge' => 'Bay thẳng',
            'location_label' => 'SGN Tân Sơn Nhất → HAN Nội Bài',
            'summary' => 'Chuyến bay nội địa phổ biến nhất Việt Nam — khoảng 2 giờ, nhiều chuyến/ngày từ Vietnam Airlines, Vietjet và Bamboo Airways. ViTravel báo giá tổng hợp, không phí ẩn.',
            'highlights' => [
                'Bay thẳng ~2 giờ 10 phút',
                'Nhiều khung giờ sáng — chiều — tối',
                'Hành lý ký gửi 7–20 kg tùy hãng',
                'Hỗ trợ đổi ngày theo điều kiện vé',
                'Giao e-ticket trong 30 phút sau thanh toán',
            ],
            'inclusions' => ['Vé máy bay một chiều (economy)', 'Thuế và phí sân bay', 'Phí dịch vụ ViTravel'],
            'exclusions' => ['Hành lý quá cân', 'Suất ăn trên máy bay (trừ hạng Business)', 'Đưa đón sân bay'],
            'notes' => ['Giá tham khảo — báo giá chính xác theo ngày bay khi đặt.', 'Lead-gen: ViTravel là đại lý, không phải hãng hàng không.'],
            'attrs' => [
                'from' => 'SGN',
                'to' => 'HAN',
                'airlines' => ['Vietnam Airlines', 'Vietjet Air', 'Bamboo Airways'],
                'flight_time' => '2h10m',
            ],
            'en' => ['title' => 'Ho Chi Minh City — Hanoi Flight (Economy)', 'summary' => 'Most popular domestic route in Vietnam — approx. 2 hours, multiple daily departures.'],
        ],
        [
            'code' => 'flight-han-dad-economy',
            'cluster' => 'flight',
            'category_slug' => 'noi-dia',
            'country_slug' => 'viet-nam',
            'title' => 'Vé máy bay Hà Nội — Đà Nẵng (Economy)',
            'slug' => 've-may-bay-ha-noi-da-nang',
            'price_from' => 990000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 654,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'HAN Nội Bài → DAD Đà Nẵng',
            'summary' => 'Bay nhanh miền Bắc — miền Trung trong 1 giờ 20 phút — lý tưởng kết hợp tour Hội An, Huế, Bà Nà Hills sau khi rời Hà Nội.',
            'highlights' => [
                'Thời gian bay ~1h20',
                '10+ chuyến/ngày mùa cao điểm',
                'Sân bay Đà Nẵng cách biển Mỹ Khê 15 phút',
                'Có thể kết hợp vé khứ hồi giá tốt',
                'Tư vấn nối chuyến tour miền Trung',
            ],
            'inclusions' => ['Vé một chiều economy', 'Thuế phí sân bay'],
            'exclusions' => ['Hành lý mua thêm', 'Chọn chỗ ngồi trả phí'],
            'notes' => ['Giá thấp nhất thường vào sáng sớm hoặc khuya.'],
            'attrs' => [
                'from' => 'HAN',
                'to' => 'DAD',
                'airlines' => ['Vietnam Airlines', 'Vietjet Air', 'Bamboo Airways'],
                'flight_time' => '1h20m',
            ],
        ],
        [
            'code' => 'flight-han-bkk-package',
            'cluster' => 'flight',
            'category_slug' => 'quoc-te-chau-a',
            'country_slug' => 'viet-nam',
            'title' => 'Gói vé Hà Nội — Bangkok + tour nối chuyến',
            'slug' => 'goi-ve-ha-noi-bangkok-tour',
            'price_from' => 3200000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 143,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'HAN → BKK (Suvarnabhumi)',
            'summary' => 'Gói lead-gen kết hợp vé quốc tế Hà Nội — Bangkok và tư vấn tour Thái Lan nối chuyến — phù hợp du khách Việt muốn mở rộng hành trình Đông Nam Á.',
            'highlights' => [
                'Bay thẳng ~2 giờ với Thai Airways, Vietjet hoặc Vietnam Airlines',
                'Tư vấn visa Thái Lan (miễn visa 30 ngày cho công dân VN)',
                'Combo tour Bangkok — Pattaya — Ayutthaya 3–5 ngày',
                'Hỗ trợ đặt khách sạn và đưa đón sân bay BKK',
                'Báo giá minh bạch, không cam kết giá cố định trên website',
            ],
            'inclusions' => ['Vé máy bay HAN–BKK (economy, tham khảo)', 'Tư vấn lịch trình Thái Lan', 'Phí dịch vụ đặt vé'],
            'exclusions' => ['Tour mặt đất Thái Lan (báo giá riêng)', 'Bảo hiểm du lịch quốc tế'],
            'notes' => ['Đây là gói tư vấn & đặt vé — giá thay đổi theo ngày bay và hãng.', 'Có thể thay BKK bằng SIN, KUL tùy nhu cầu.'],
            'attrs' => [
                'from' => 'HAN',
                'to' => 'BKK',
                'airlines' => ['Thai Airways', 'Vietjet Air', 'Vietnam Airlines'],
                'flight_time' => '2h00m',
            ],
            'faqs' => [
                ['q' => 'Công dân Việt có cần visa Thái Lan không?', 'a' => 'Hiện công dân Việt Nam được miễn visa nhập cảnh Thái Lan tối đa 30 ngày — vui lòng kiểm tra quy định mới nhất trước ngày bay.'],
            ],
        ],
        [
            'code' => 'flight-private-charter',
            'cluster' => 'flight',
            'category_slug' => 'thue-may-bay-rieng',
            'country_slug' => 'viet-nam',
            'title' => 'Thuê máy bay riêng & charter nội địa',
            'slug' => 'thue-may-bay-rieng-charter',
            'price_from' => 85000000,
            'currency' => 'VND',
            'rating' => 5.0,
            'review_count' => 28,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Toàn quốc — theo yêu cầu',
            'summary' => 'Dịch vụ charter jet và máy bay nhẹ cho doanh nghiệp, đoàn MICE, đám cưới destination và khách VIP — linh hoạt sân bay, giờ bay và lộ trình.',
            'highlights' => [
                'Máy bay phản lực nhẹ (Cessna Citation, Pilatus) hoặc trực thăng',
                'Bay thẳng các sân bay nội địa và một số sân bay khu vực',
                'Phục vụ riêng tư, tiết kiệm thời gian so với bay thương mại',
                'Phù hợp khảo sát dự án, FAM trip, sự kiện cao cấp',
                'ViTravel kết nối đối tác vận hành được cấp phép CAACV',
            ],
            'inclusions' => ['Tư vấn và báo giá charter', 'Sắp xếp slot sân bay', 'Hỗ trợ thủ tục hàng không'],
            'exclusions' => ['Chi phí bay thực tế (báo giá case-by-case)', 'Catering cao cấp', 'Ground handling nước ngoài'],
            'notes' => ['Giá from 85 triệu/chuyến ngắn — báo giá chi tiết sau khi nhận yêu cầu.', 'Đặt trước tối thiểu 7–14 ngày.'],
            'attrs' => [
                'from' => 'Theo yêu cầu',
                'to' => 'Theo yêu cầu',
                'airlines' => ['Đối tác charter Việt Nam'],
                'flight_time' => 'Linh hoạt',
            ],
            'en' => ['title' => 'Private Jet & Domestic Charter', 'summary' => 'Charter jets and light aircraft for corporate, MICE and VIP travel across Vietnam.'],
        ],

        // ── STAY (8) ─────────────────────────────────────────────────────────
        [
            'code' => 'stay-intercontinental-phu-quoc',
            'cluster' => 'stay',
            'category_slug' => 'phu-quoc',
            'country_slug' => 'viet-nam',
            'title' => 'InterContinental Phu Quoc Long Beach Resort',
            'slug' => 'intercontinental-phu-quoc-long-beach',
            'price_from' => 6500000,
            'currency' => 'VND',
            'rating' => 4.9,
            'review_count' => 421,
            'is_featured' => true,
            'is_hot_deal' => true,
            'discount_badge' => 'Resort 5★',
            'star_rating' => 5,
            'location_label' => 'Bãi Dài, Bắc đảo Phú Quốc',
            'summary' => 'Resort 5 sao dọc bãi biển dài nhất Phú Quốc — thiết kế hiện đại hướng biển, hồ bơi vô cực, spa I-Spa by ISLANDS và ẩm thực đa quốc gia. Lựa chọn hàng đầu cho kỳ nghỉ cao cấp trên đảo ngọc.',
            'highlights' => [
                'Mặt tiền bãi biển riêng dài hơn 1 km, cát trắng mịn',
                'Club InterContinental với lounge riêng và butler service',
                '4 nhà hàng & bar: Sora & Umi, LAVA, Sea Shack, Lobby Lounge',
                'Hồ bơi vô cực nhìn ra biển và hồ bơi trẻ em',
                'Spa I-Spa, phòng gym, kids club INK 360',
                'Gần sân bay Phú Quốc (~20 phút), thuận tiện kết hợp tour biển',
            ],
            'inclusions' => ['Phòng nghỉ theo loại đã chọn', 'Wi-Fi miễn phí', 'Phí dịch vụ đặt phòng ViTravel'],
            'exclusions' => ['Ăn sáng (tùy gói rate)', 'Minibar', 'Spa & hoạt động ngoài resort'],
            'notes' => ['Giá from/đêm tham khảo mùa thấp điểm — báo giá lại theo ngày check-in.', 'Trẻ em dưới 12 tuổi ngủ chung miễn phí (tùy chính sách phòng).'],
            'attrs' => [
                'amenities' => ['Hồ bơi vô cực', 'Spa', 'Bãi biển riêng', 'Kids club', 'Gym', 'Nhà hàng', 'Club lounge', 'WiFi miễn phí', 'Đưa đón sân bay'],
                'highlight_badges' => ['Bãi biển riêng', 'Hồ bơi vô cực', 'WiFi miễn phí', 'Spa', 'Kids club', 'Đưa đón sân bay'],
                'check_in' => '15:00',
                'check_out' => '12:00',
                'property_type' => 'resort',
                'address' => 'Bãi Dài, Gành Dầu, Phú Quốc, Kiên Giang',
                'cancellation_policy' => 'Huỷ/đổi ngày theo chính sách resort và gói rate — báo giá chi tiết khi đặt qua :brand.',
                'child_policy' => 'Trẻ em dưới 12 tuổi ngủ chung miễn phí (tùy loại phòng); extra bed báo giá riêng.',
                'extra_bed_policy' => 'Giường phụ / cũi theo sức chứa hạng phòng — xác nhận khi báo giá, không tự ý thêm khách.',
                'age_restriction' => '18',
                'pet_policy' => 'Không cho phép thú cưng (trừ hỗ trợ đặc biệt theo yêu cầu).',
                'smoking_policy' => 'Không hút thuốc trong phòng; khu hút thuốc ngoài trời theo hướng dẫn lễ tân.',
                'payment_policy' => 'Đặt cọc / thanh toán theo xác nhận booking — hỗ trợ chuyển khoản, thẻ (tùy gói).',
                'payment_cards' => ['Visa', 'Mastercard', 'JCB', 'American Express'],
                'id_required_policy' => 'CCCD/hộ chiếu bản gốc khi check-in — tên khách khớp booking.',
                'amenity_groups' => [
                    'popular' => ['Bãi biển riêng', 'Hồ bơi vô cực', 'WiFi miễn phí', 'Spa', 'Kids club', 'Đưa đón sân bay'],
                    'pool_beach' => ['Hồ bơi vô cực nhìn ra biển', 'Hồ bơi trẻ em', 'Bãi biển riêng', 'Ghế nằm & khăn bãi biển'],
                    'dining' => ['Sora & Umi', 'LAVA', 'Sea Shack', 'Lobby Lounge', 'Room service (theo gói)'],
                    'wellness' => ['I-Spa by ISLANDS', 'Phòng gym', 'Yoga / wellness (theo lịch)'],
                    'family' => ['Kids club INK 360', 'Hồ bơi trẻ em'],
                    'parking' => ['Đưa đón sân bay (tính phí / theo gói)', 'Bãi đậu xe'],
                    'general' => ['WiFi miễn phí', 'Lễ tân 24h', 'Club InterContinental lounge'],
                    'safety' => ['Báo cháy', 'Bình chữa cháy', 'Thẻ từ / kiểm soát ra vào'],
                ],
                'nearby' => [
                    ['name' => 'Sân bay Phú Quốc', 'distance' => '~20 phút', 'icon' => 'plane', 'category' => 'transport'],
                    ['name' => 'Bãi Dài', 'distance' => 'Sát resort', 'icon' => 'umbrella', 'category' => 'beach'],
                    ['name' => 'Trung tâm Dương Đông', 'distance' => '~25 phút', 'icon' => 'map-pin', 'category' => 'landmark'],
                ],
                'review_scores' => [
                    'staff' => 9.1,
                    'facilities' => 8.8,
                    'cleanliness' => 9.0,
                    'comfort' => 8.9,
                    'value' => 8.2,
                    'location' => 9.2,
                    'wifi' => 8.6,
                ],
            ],
            'content' => '<p>InterContinental Phu Quoc Long Beach Resort sở hữu mặt tiền bãi biển dài bậc nhất đảo ngọc — lựa chọn hàng đầu cho kỳ nghỉ cao cấp kết hợp tour biển Phú Quốc.</p><h2>Không gian & phong cách</h2><p>Thiết kế hiện đại hướng biển, hồ bơi vô cực và khu Club InterContinental riêng tư với butler service.</p><h2>Ẩm thực & giải trí</h2><p>Bốn nhà hàng & bar, spa I-Spa, kids club INK 360 và bãi biển riêng phục vụ trọn ngày.</p>',
            'options' => [
                [
                    'code' => 'ic-pq-deluxe',
                    'name' => 'Deluxe Ocean View',
                    'description' => 'Phòng Deluxe hướng biển với ban công riêng, máy lạnh độc lập, TV màn hình phẳng và phòng tắm kính. Điểm nhấn là tầm nhìn ra bãi Dài và hồ bơi vô cực — phù hợp cặp đôi hoặc kỳ nghỉ ngắn ngày.',
                    'price_from' => 6500000,
                    'capacity' => 2,
                    'amenities' => ['Giường king', 'Ban công hướng biển', '55m²', 'Máy điều hòa', 'TV màn hình phẳng', 'WiFi miễn phí', 'Phòng tắm riêng', 'Cách âm'],
                    'attrs' => [
                        'unit_type' => 'hotel_room',
                        'bed' => '1 giường king',
                        'size_sqm' => 55,
                        'view' => 'Hướng biển',
                        'bathroom_count' => 1,
                        'bedroom_count' => 1,
                        'smoking' => 'Không hút thuốc',
                        'comfort_score' => 8.8,
                        'comfort_reviews' => 72,
                        'highlights' => ['Ban công', 'Nhìn ra biển', 'Máy điều hòa', 'TV màn hình phẳng', 'Cách âm', 'WiFi miễn phí'],
                        'beds' => [
                            ['name' => 'Phòng ngủ', 'items' => [['type' => 'king', 'count' => 1, 'label' => '1 giường king']]],
                        ],
                        'amenity_groups' => [
                            'bathroom' => ['Đồ vệ sinh cá nhân miễn phí', 'Áo choàng tắm', 'Bồn tắm hoặc vòi sen', 'Khăn tắm', 'Máy sấy tóc', 'Giấy vệ sinh'],
                            'view' => ['Nhìn ra biển', 'Nhìn ra hồ bơi'],
                            'outdoor' => ['Ban công'],
                            'media' => ['TV màn hình phẳng', 'WiFi miễn phí'],
                            'general' => ['Máy điều hòa', 'Cách âm', 'Két an toàn', 'Minibar', 'Ấm đun nước điện'],
                        ],
                    ],
                ],
                [
                    'code' => 'ic-pq-suite',
                    'name' => 'One Bedroom Suite',
                    'description' => 'Suite một phòng ngủ với phòng khách riêng, bồn tắm và tầm nhìn biển. Sức chứa linh hoạt hơn Deluxe khi có trẻ em ngủ chung (xác nhận sức chứa lúc báo giá).',
                    'price_from' => 9800000,
                    'capacity' => 3,
                    'amenities' => ['Phòng khách riêng', 'Bồn tắm', '85m²', 'Ban công hướng biển', 'Máy điều hòa'],
                    'attrs' => [
                        'unit_type' => 'hotel_room',
                        'bed' => '1 giường king',
                        'size_sqm' => 85,
                        'view' => 'Hướng biển',
                        'bathroom_count' => 1,
                        'bedroom_count' => 1,
                        'smoking' => 'Không hút thuốc',
                        'highlights' => ['Phòng khách riêng', 'Bồn tắm', 'Nhìn ra biển', 'Ban công'],
                        'beds' => [
                            ['name' => 'Phòng ngủ', 'items' => [['type' => 'king', 'count' => 1, 'label' => '1 giường king']]],
                            ['name' => 'Phòng khách', 'items' => [['type' => 'sofa', 'count' => 1, 'label' => '1 giường sofa (tùy bố trí)']]],
                        ],
                        'amenity_groups' => [
                            'bathroom' => ['Bồn tắm', 'Vòi sen', 'Áo choàng tắm', 'Máy sấy tóc'],
                            'living' => ['Phòng khách riêng', 'Khu vực ghế ngồi'],
                            'view' => ['Nhìn ra biển'],
                            'outdoor' => ['Ban công'],
                            'general' => ['Máy điều hòa', 'WiFi miễn phí'],
                        ],
                    ],
                ],
                [
                    'code' => 'ic-pq-villa',
                    'name' => 'Beachfront Villa',
                    'description' => 'Villa sát bãi biển với hồ bơi riêng và butler theo chính sách Club. Nguyên căn — phù hợp gia đình hoặc nhóm nhỏ; sức chứa và giường phụ xác nhận khi báo giá.',
                    'price_from' => 14500000,
                    'capacity' => 4,
                    'amenities' => ['Hồ bơi riêng', 'Sát bãi biển', 'Butler', 'Bếp nhỏ', 'Ban công'],
                    'attrs' => [
                        'unit_type' => 'entire_villa',
                        'bed' => 'King + extra',
                        'size_sqm' => 160,
                        'view' => 'Sát bãi biển',
                        'bathroom_count' => 2,
                        'bedroom_count' => 2,
                        'smoking' => 'Không hút thuốc trong villa',
                        'highlights' => ['1 villa nguyên căn', 'Hồ bơi riêng', 'Nhìn ra biển', 'Bếp riêng', 'Ban công'],
                        'beds' => [
                            ['name' => 'Phòng ngủ 1', 'items' => [['type' => 'king', 'count' => 1, 'label' => '1 giường king']]],
                            ['name' => 'Phòng ngủ 2', 'items' => [['type' => 'twin', 'count' => 2, 'label' => '2 giường đơn']]],
                        ],
                        'amenity_groups' => [
                            'kitchen' => ['Bếp nhỏ', 'Ấm đun nước điện', 'Tủ lạnh'],
                            'bathroom' => ['2 phòng tắm', 'Bồn tắm hoặc vòi sen', 'Áo choàng tắm'],
                            'view' => ['Nhìn ra biển'],
                            'outdoor' => ['Hồ bơi riêng', 'Ban công', 'Sát bãi biển'],
                            'general' => ['Máy điều hòa độc lập', 'WiFi miễn phí', 'Butler (theo gói Club)'],
                        ],
                    ],
                ],
            ],
            'faqs' => [
                ['q' => 'Club InterContinental gồm những gì?', 'a' => 'Lounge, giờ check-in/out linh hoạt hơn và butler theo chính sách club lúc báo giá — không mặc định với mọi hạng phòng Deluxe.'],
                ['q' => 'Cách sân bay bao xa?', 'a' => 'Khoảng 20 phút tới sân bay Phú Quốc. Đưa đón sân bay tính theo gói, không tự ý gồm trong giá từ.'],
            ],
            'en' => ['title' => 'InterContinental Phu Quoc Long Beach Resort', 'summary' => '5-star beachfront resort on Phu Quoc\'s longest beach — infinity pool, I-Spa and world-class dining.'],
        ],
        [
            'code' => 'stay-jw-marriott-phu-quoc',
            'cluster' => 'stay',
            'category_slug' => 'phu-quoc',
            'country_slug' => 'viet-nam',
            'title' => 'JW Marriott Phu Quoc Emerald Bay Resort & Spa',
            'slug' => 'jw-marriott-phu-quoc-emerald-bay',
            'price_from' => 7200000,
            'currency' => 'VND',
            'rating' => 4.9,
            'review_count' => 387,
            'is_featured' => true,
            'is_hot_deal' => false,
            'star_rating' => 5,
            'location_label' => 'Khem Beach, Nam đảo Phú Quốc',
            'summary' => 'Kiệt tác thiết kế bởi Bill Bensley — resort all-lakefront lấy cảm hứng từ trường đại học Pháp thuộc, mỗi phòng là một câu chuyện. Emerald Bay là biểu tượng luxury tại Phú Quốc.',
            'highlights' => [
                'Thiết kế Bill Bensley độc bản — không resort nào giống',
                'Bãi Khem trong vắt, ít đông đúc',
                'Chanterelle Spa by JW và hồ bơi hình con sên',
                'Nhà hàng Pink Pearl, Tempus Fugit, Red Rum',
                'Phòng ban công hướng hồ hoặc biển, nội thất cổ điển Á — Âu',
                'Phù hợp honeymoon và kỷ niệm đặc biệt',
            ],
            'inclusions' => ['Phòng nghỉ', 'Wi-Fi', 'Đưa đón sân bay (theo gói ưu đãi)'],
            'exclusions' => ['F&B ngoài gói', 'Tour ngoài resort'],
            'notes' => ['Resort adult-friendly, có khu vực gia đình.'],
            'content' => '<p>JW Marriott Phu Quoc Emerald Bay — thiết kế Bill Bensley, bãi Khem ít đông hơn Bãi Trường. Hạng Lagoon View hoặc Ocean View; spa Chanterelle và nhà hàng fine dining trong resort.</p><h2>Đối tượng</h2><p>Hợp honeymoon và kỷ niệm. Resort adult-friendly nhưng vẫn có khu gia đình — xác nhận loại phòng khi đặt.</p>',
            'attrs' => [
                'amenities' => ['Spa', 'Hồ bơi', 'Bãi biển', 'Fine dining', 'Art gallery', 'Gym', 'Wi-Fi'],
                'check_in' => '15:00',
                'check_out' => '12:00',
                'property_type' => 'resort',
                'nearby' => [
                    ['name' => 'Bãi Khem', 'distance' => 'Sát resort', 'icon' => 'umbrella'],
                    ['name' => 'Sân bay Phú Quốc', 'distance' => 'Xe đưa đón tuỳ gói', 'icon' => 'plane'],
                ],
            ],
            'options' => [
                ['code' => 'jw-pq-lagoon', 'name' => 'Lagoon View King', 'price_from' => 7200000, 'capacity' => 2, 'amenities' => ['Giường king', 'Hướng hồ'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Hướng hồ']],
                ['code' => 'jw-pq-ocean', 'name' => 'Ocean View King', 'price_from' => 8900000, 'capacity' => 2, 'amenities' => ['Giường king', 'Hướng biển'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Hướng biển']],
                ['code' => 'jw-pq-suite', 'name' => 'JW Suite', 'price_from' => 12800000, 'capacity' => 3, 'amenities' => ['Phòng khách', 'Bồn tắm'], 'attrs' => ['bed' => 'King', 'view' => 'Hướng hồ / biển']],
            ],
            'faqs' => [
                ['q' => 'Resort có phù hợp gia đình không?', 'a' => 'Adult-friendly với khu vực gia đình. Một số cánh/hạng yên hơn — nói rõ có trẻ em khi báo giá để xếp đúng phòng.'],
            ],
        ],
        [
            'code' => 'stay-vinpearl-ha-long',
            'cluster' => 'stay',
            'category_slug' => 'ha-long-cat-ba',
            'country_slug' => 'viet-nam',
            'title' => 'Vinpearl Resort & Spa Hạ Long',
            'slug' => 'vinpearl-resort-spa-ha-long',
            'price_from' => 3800000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 512,
            'is_featured' => true,
            'is_hot_deal' => true,
            'discount_badge' => 'View vịnh',
            'star_rating' => 5,
            'location_label' => 'Đảo Rều, Vịnh Hạ Long, Quảng Ninh',
            'summary' => 'Resort trên đảo riêng giữa vịnh Hạ Long — view núi đá vôi, cáp treo nối đất liền, công viên nước Vinpearl Land và spa — kết hợp hoàn hảo nghỉ dưỡng & khám phá di sản.',
            'highlights' => [
                'View trực diện vịnh Hạ Long UNESCO',
                'Cáp treo Vinpearl nối Bãi Cháy — đảo Rều',
                'Vinpearl Land, sân golf, bể bơi ngoài trời lớn',
                'Spa Vincharm, nhà hàng hải sản và buffet quốc tế',
                'Thuận tiện kết hợp tour du thuyền 1–2 ngày',
                'Gia đình có trẻ nhỏ — nhiều hoạt động trong resort',
            ],
            'inclusions' => ['Phòng nghỉ', 'Wi-Fi', 'Khu vui chơi cơ bản (tùy gói)'],
            'exclusions' => ['Vinpearl Land vé lẻ', 'Tour du thuyền vịnh'],
            'notes' => ['Mùa đông có thể sương mù — view vịnh vẫn đẹp nhưng tắm biển hạn chế.'],
            'content' => '<p>Vinpearl Resort &amp; Spa Hạ Long trên đảo Rều — view vịnh UNESCO, cáp treo nối Bãi Cháy. Vé Vinpearl Land và tour du thuyền không mặc định gồm trong phòng.</p><h2>Mùa đông</h2><p>Sương mù có thể làm view mờ; tắm biển hạn chế. Công viên nước / hồ bơi trong resort vẫn dùng được tuỳ thời tiết.</p>',
            'attrs' => [
                'amenities' => ['Spa', 'Hồ bơi', 'Công viên nước', 'Golf', 'Nhà hàng', 'Cáp treo', 'Wi-Fi'],
                'check_in' => '14:00',
                'check_out' => '12:00',
                'property_type' => 'resort',
                'child_policy' => 'Phù hợp gia đình có trẻ nhỏ; extra bed / cũi báo giá riêng. Vé công viên không mặc định gồm trong phòng.',
                'nearby' => [
                    ['name' => 'Vịnh Hạ Long', 'distance' => 'View trực diện / đảo Rều', 'icon' => 'waves'],
                    ['name' => 'Bãi Cháy', 'distance' => 'Cáp treo Vinpearl', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => 'vp-hl-deluxe', 'name' => 'Deluxe Bay View', 'price_from' => 3800000, 'capacity' => 2, 'amenities' => ['Hướng vịnh'], 'attrs' => ['bed' => '1 giường đôi / twin', 'view' => 'Hướng vịnh']],
                ['code' => 'vp-hl-suite', 'name' => 'Executive Suite', 'price_from' => 5500000, 'capacity' => 3, 'amenities' => ['Phòng khách', 'Hướng vịnh'], 'attrs' => ['bed' => 'King + extra', 'view' => 'Hướng vịnh']],
            ],
            'faqs' => [
                ['q' => 'Vé Vinpearl Land đã gồm chưa?', 'a' => 'Chưa — trừ khi báo giá ghi combo. Tour du thuyền vịnh đặt riêng, không tự động gồm trong phòng.'],
            ],
        ],
        [
            'code' => 'stay-premier-village-phu-quoc',
            'cluster' => 'stay',
            'category_slug' => 'phu-quoc',
            'country_slug' => 'viet-nam',
            'title' => 'Premier Village Phu Quoc Resort',
            'slug' => 'premier-village-phu-quoc-resort',
            'price_from' => 5800000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 276,
            'is_featured' => false,
            'is_hot_deal' => false,
            'star_rating' => 5,
            'location_label' => 'Mũi Ông Đội, Nam Phú Quốc',
            'summary' => 'Toàn bộ là villa riêng biệt — mỗi căn có hồ bơi riêng, sân vườn và view biển. Managed by Accor, Premier Village là lựa chọn villa resort hàng đầu Phú Quốc cho nhóm bạn và gia đình.',
            'highlights' => [
                '100% villa — không có phòng khách sạn',
                'Hồ bơi riêng từng villa, bếp mini đầy đủ',
                'Bãi biển riêng Mũi Ông Đội — yên tĩnh',
                'The Market restaurant & Plumeria Spa',
                'Gần Sunset Sanato và các quán hải sản địa phương',
                'Phù hợp nhóm 4–8 người thuê villa 3 phòng ngủ',
            ],
            'inclusions' => ['Villa theo loại', 'Wi-Fi', 'Bãi biển riêng'],
            'exclusions' => ['Ăn sáng (có thể mua thêm)', 'BBQ setup'],
            'notes' => ['Villa 3BR phù hợp 6–8 khách — báo giá theo số người.'],
            'content' => '<p>Premier Village Phu Quoc — 100% villa, mỗi căn hồ bơi riêng, bếp mini. Bãi Mũi Ông Đội yên hơn khu tây đảo. Ăn sáng và BBQ setup mua thêm.</p>',
            'attrs' => [
                'amenities' => ['Hồ bơi riêng', 'Bếp mini', 'Spa', 'Bãi biển', 'Gym', 'Kids area', 'Wi-Fi'],
                'check_in' => '15:00',
                'check_out' => '12:00',
                'property_type' => 'villa',
                'child_policy' => 'Villa 2–4 phòng ngủ phù hợp gia đình / nhóm; sức chứa theo hạng đã chốt, extra bed báo giá riêng.',
                'nearby' => [
                    ['name' => 'Bãi Mũi Ông Đội', 'distance' => 'Bãi riêng', 'icon' => 'umbrella'],
                    ['name' => 'Sunset Sanato', 'distance' => 'Gần khu nam đảo', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => 'pv-pq-2br', 'name' => '2 Bedroom Pool Villa', 'price_from' => 5800000, 'capacity' => 4, 'amenities' => ['Hồ bơi riêng', 'Bếp mini'], 'attrs' => ['bed' => '2 phòng ngủ', 'view' => 'Hồ bơi / vườn']],
                ['code' => 'pv-pq-3br', 'name' => '3 Bedroom Pool Villa', 'price_from' => 8200000, 'capacity' => 6, 'amenities' => ['Hồ bơi riêng', 'Bếp'], 'attrs' => ['bed' => '3 phòng ngủ']],
                ['code' => 'pv-pq-4br', 'name' => '4 Bedroom Beach Villa', 'price_from' => 11500000, 'capacity' => 8, 'amenities' => ['Sát biển', 'Hồ bơi riêng'], 'attrs' => ['bed' => '4 phòng ngủ', 'view' => 'Hướng biển']],
            ],
            'faqs' => [
                ['q' => 'Có phòng khách sạn thường không?', 'a' => 'Không — toàn bộ là villa. Chọn số phòng ngủ theo số khách; không tự ý thêm người ngoài sức chứa đã xác nhận.'],
            ],
        ],
        [
            'code' => 'stay-furama-resort-danang',
            'cluster' => 'stay',
            'category_slug' => 'da-nang-hoi-an',
            'country_slug' => 'viet-nam',
            'title' => 'Furama Resort Danang',
            'slug' => 'furama-resort-danang',
            'price_from' => 4200000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 698,
            'is_featured' => true,
            'is_hot_deal' => false,
            'star_rating' => 5,
            'location_label' => '105 Võ Nguyên Giáp, Mỹ Khê, Đà Nẵng',
            'summary' => 'Resort biển kinh điển Đà Nẵng từ 1990s — bãi Mỹ Khê được Forbes bình chọn, laguna hồ bơi, Furama Villas và ẩm thực Việt — Nhật — Âu. Chuẩn mực nghỉ dưỡng miền Trung.',
            'highlights' => [
                'Bãi biển Mỹ Khê — top beach châu Á',
                'Lagoon pool 2.000 m² và Furama Villas riêng biệt',
                'Spa Fusion Maia, cooking class ẩm thực Việt',
                '5 nhà hàng: Café Indochine, Don Cipriani\'s, Tàya House…',
                '15 phút sân bay, 20 phút Hội An',
                'Tổ chức tiệc cưới beachfront uy tín',
            ],
            'inclusions' => ['Phòng/villa', 'Wi-Fi', 'Bãi biển & hồ bơi'],
            'exclusions' => ['Ăn sáng (trừ gói B&B)', 'Đưa đón'],
            'notes' => ['Có wing Furama Villas tách biệt — yên tĩnh hơn main building.'],
            'content' => '<p>Furama Resort Danang trên bãi Mỹ Khê — lagoon pool, spa Fusion Maia, nhiều nhà hàng. Cánh Furama Villas yên hơn toà chính. Ăn sáng chỉ gồm khi chọn gói B&amp;B.</p>',
            'attrs' => [
                'amenities' => ['Lagoon pool', 'Spa', 'Bãi biển', 'Nhà hàng', 'Gym', 'Tennis', 'Wi-Fi'],
                'check_in' => '14:00',
                'check_out' => '12:00',
                'property_type' => 'resort',
                'nearby' => [
                    ['name' => 'Bãi Mỹ Khê', 'distance' => 'Sát resort', 'icon' => 'umbrella'],
                    ['name' => 'Sân bay Đà Nẵng', 'distance' => '~15 phút', 'icon' => 'plane'],
                    ['name' => 'Hội An', 'distance' => '~20 phút', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => 'furama-garden', 'name' => 'Garden Superior', 'price_from' => 4200000, 'capacity' => 2, 'amenities' => ['Hướng vườn'], 'attrs' => ['bed' => '1 giường đôi / twin', 'view' => 'Hướng vườn']],
                ['code' => 'furama-ocean', 'name' => 'Ocean Deluxe', 'price_from' => 5100000, 'capacity' => 2, 'amenities' => ['Hướng biển'], 'attrs' => ['bed' => '1 giường đôi / twin', 'view' => 'Hướng biển']],
                ['code' => 'furama-villa', 'name' => 'Furama Villa 1BR', 'price_from' => 7800000, 'capacity' => 3, 'amenities' => ['Villa riêng'], 'attrs' => ['bed' => '1 phòng ngủ', 'view' => 'Vườn / hồ']],
            ],
            'faqs' => [
                ['q' => 'Furama Villas khác phòng khách sạn thế nào?', 'a' => 'Cánh villa tách biệt, yên hơn toà chính. Giá và sức chứa khác Garden/Ocean — chọn đúng hạng trên báo giá.'],
            ],
        ],
        [
            'code' => 'stay-six-senses-ninh-van-bay',
            'cluster' => 'stay',
            'category_slug' => 'nha-trang',
            'country_slug' => 'viet-nam',
            'title' => 'Six Senses Ninh Van Bay',
            'slug' => 'six-senses-ninh-van-bay',
            'price_from' => 12500000,
            'currency' => 'VND',
            'rating' => 5.0,
            'review_count' => 189,
            'is_featured' => true,
            'is_hot_deal' => false,
            'star_rating' => 5,
            'location_label' => 'Vịnh Ninh Vân, Cam Ranh (đón từ Nha Trang)',
            'summary' => 'Resort chỉ đi bằng thuyền — villa trên đồi và sát biển, triết lý sustainable luxury của Six Senses. Trải nghiệm nghỉ dưỡng biểt lập đẳng cấp nhất miền Trung Việt Nam.',
            'highlights' => [
                'Boat-only access — cảm giác riêng tư tuyệt đối',
                'Villa hồ bơi riêng — Hill Top, Beach Front, Water Pool',
                'Six Senses Spa với liệu pháp holistic',
                'Dining by Design — ăn tối riêng trên bãi biển',
                'Organic garden, sustainability tour',
                'Phù hợp honeymoon ultra-luxury',
            ],
            'inclusions' => ['Villa', 'Boat transfer từ Nha Trang', 'Wi-Fi'],
            'exclusions' => ['F&B', 'Spa treatments', 'Bay transfer ngoài giờ'],
            'notes' => ['Resort không phù hợp trẻ em dưới 12 tuổi một số mùa — kiểm tra policy.'],
            'content' => '<p>Six Senses Ninh Van Bay chỉ đi bằng thuyền từ Nha Trang — villa hồ bơi riêng trên đồi hoặc sát biển. F&amp;B và spa không mặc định gồm; boat transfer trong khung giờ resort.</p>',
            'attrs' => [
                'amenities' => ['Private pool villa', 'Spa', 'Private beach', 'Organic garden', 'Yoga', 'Wi-Fi'],
                'check_in' => '14:00',
                'check_out' => '12:00',
                'property_type' => 'villa',
                'child_policy' => 'Một số mùa / hạng villa không phù hợp trẻ em dưới 12 tuổi — xác nhận policy khi đặt, không tự ý đưa trẻ nếu chỗ nghỉ từ chối.',
                'nearby' => [
                    ['name' => 'Vịnh Ninh Vân', 'distance' => 'Boat-only từ Nha Trang', 'icon' => 'ship'],
                    ['name' => 'Nha Trang', 'distance' => 'Thuyền đưa đón theo khung giờ', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => '6s-hill-villa', 'name' => 'Hill Top Pool Villa', 'price_from' => 12500000, 'capacity' => 2, 'amenities' => ['Hồ bơi riêng', 'Hướng vịnh'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Hướng vịnh / đồi']],
                ['code' => '6s-beach-villa', 'name' => 'Beach Front Pool Villa', 'price_from' => 15800000, 'capacity' => 2, 'amenities' => ['Sát biển', 'Hồ bơi riêng'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Sát biển']],
                ['code' => '6s-water-villa', 'name' => 'Water Pool Villa', 'price_from' => 18200000, 'capacity' => 2, 'amenities' => ['Hồ bơi riêng'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Hướng nước']],
            ],
            'faqs' => [
                ['q' => 'Làm sao tới resort?', 'a' => 'Chỉ đi thuyền theo khung giờ Six Senses từ Nha Trang. Bay transfer ngoài giờ tính phí riêng — không tự lái xe vào resort.'],
                ['q' => 'Có nhận trẻ em không?', 'a' => 'Một số mùa / hạng không phù hợp trẻ dưới 12 tuổi. Nói rõ độ tuổi khi đặt để chỗ nghỉ xác nhận.'],
            ],
            'en' => ['title' => 'Six Senses Ninh Van Bay', 'summary' => 'Boat-access only luxury resort — private pool villas and sustainable wellness in Ninh Van Bay.'],
        ],
        [
            'code' => 'stay-hotel-de-la-coupole-sapa',
            'cluster' => 'stay',
            'category_slug' => 'ha-long-cat-ba',
            'country_slug' => 'viet-nam',
            'title' => 'Hotel de la Coupole — MGallery Sapa',
            'slug' => 'hotel-de-la-coupole-mgallery-sapa',
            'price_from' => 4800000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 334,
            'is_featured' => true,
            'is_hot_deal' => false,
            'star_rating' => 5,
            'location_label' => '01 Hoàng Liên, Sa Pa, Lào Cai',
            'summary' => 'Khách sạn biểu tượng Sa Pa — thiết kế lấy cảm hứng Haute Couture Pháp gặp văn hoá vùng cao Tây Bắc. View ruộng bậc thang và Fansipan, indoor pool kính trong suốt nổi tiếng trên mạng xã hội.',
            'highlights' => [
                'Thiết kế độc đáo — dome, màu sắc và nội thất couture',
                'Indoor pool view núi Hoàng Liên Sơn',
                'Nhà hàng Chic, Absinthe Bar',
                'Spa Hmong heritage treatments',
                'Gần ga cáp treo Fansipan và bản Cát Cát',
                'Ấm áp mùa đông — lò sưởi và không khí alpine',
            ],
            'inclusions' => ['Phòng nghỉ', 'Wi-Fi', 'Indoor pool'],
            'exclusions' => ['Ăn sáng', 'Cáp treo Fansipan', 'Tour trekking'],
            'notes' => ['Sa Pa mùa đông có thể dưới 10°C — mang áo ấm.', 'Book sớm dịp lễ 30/4 và lễ hội mùa xuân.'],
            'content' => '<p>Hotel de la Coupole — MGallery Sapa: thiết kế haute couture, indoor pool kính, view Hoàng Liên. Ăn sáng và cáp treo Fansipan không mặc định gồm trong phòng.</p>',
            'attrs' => [
                'amenities' => ['Indoor pool', 'Spa', 'Nhà hàng', 'Bar', 'Gym', 'View núi', 'Wi-Fi'],
                'check_in' => '15:00',
                'check_out' => '12:00',
                'property_type' => 'boutique',
                'nearby' => [
                    ['name' => 'Ga cáp treo Fansipan', 'distance' => 'Gần', 'icon' => 'map-pin'],
                    ['name' => 'Bản Cát Cát', 'distance' => 'Xe ngắn', 'icon' => 'map-pin'],
                    ['name' => 'Hoàng Liên Sơn', 'distance' => 'View từ hồ bơi / phòng Valley', 'icon' => 'eye'],
                ],
            ],
            'options' => [
                ['code' => 'coupole-classic', 'name' => 'Classic Room', 'price_from' => 4800000, 'capacity' => 2, 'attrs' => ['bed' => '1 giường đôi / twin']],
                ['code' => 'coupole-valley', 'name' => 'Valley View', 'price_from' => 6200000, 'capacity' => 2, 'amenities' => ['View thung lũng'], 'attrs' => ['bed' => '1 giường đôi / twin', 'view' => 'Hướng thung lũng']],
                ['code' => 'coupole-suite', 'name' => 'Couture Suite', 'price_from' => 8900000, 'capacity' => 3, 'amenities' => ['Suite'], 'attrs' => ['bed' => 'King + khu ngồi', 'view' => 'Hướng núi / valley']],
            ],
            'faqs' => [
                ['q' => 'Vé cáp treo Fansipan đã gồm chưa?', 'a' => 'Chưa. Phòng gồm Wi-Fi và quyền dùng indoor pool; cáp treo và tour trekking đặt riêng.'],
            ],
        ],
        [
            'code' => 'stay-azerai-ke-ga-bay',
            'cluster' => 'stay',
            'category_slug' => 'nha-trang',
            'country_slug' => 'viet-nam',
            'title' => 'Azerai Ke Ga Bay',
            'slug' => 'azerai-ke-ga-bay',
            'price_from' => 8500000,
            'currency' => 'VND',
            'rating' => 4.9,
            'review_count' => 156,
            'is_featured' => false,
            'is_hot_deal' => true,
            'discount_badge' => 'Boutique',
            'star_rating' => 5,
            'location_label' => 'Bình Thuận — bán đảo Ke Ga, cách Phan Thiết 30 km',
            'summary' => 'Resort boutique 32 villa của Adrian Zecha (Aman founder) — bán đảo hoang sơ Ke Ga, hải đăng cổ, bãi biển riêng và spa tĩnh lặng. Trải nghiệm nghỉ dưỡng tinh tế, xa đám đông Nha Trang.',
            'highlights' => [
                'Chỉ 32 villa — không đông, không ồn',
                'Hải đăng Ke Ga — biểu tượng Bình Thuận',
                'Spa trong villa hoặc pavilion riêng',
                'Nhà hàng The Dining Room — seafood & Vietnamese',
                'Hoạt động: kayak, biking, tour hải đăng',
                'Kết hợp tốt với Mũi Né sand dunes',
            ],
            'inclusions' => ['Villa', 'Wi-Fi', 'Bãi biển riêng', 'Xe đạp'],
            'exclusions' => ['F&B', 'Spa', 'Transfer từ sân bay (có thu phí)'],
            'notes' => ['Resort cách Nha Trang ~2.5 giờ — phù hợp nghỉ cuối/chặng riêng.'],
            'content' => '<p>Azerai Ke Ga Bay — 32 villa boutique trên bán đảo Ke Ga, hải đăng cổ, bãi riêng. F&amp;B, spa và transfer sân bay tính riêng. Cách Nha Trang khoảng 2,5 giờ — hợp chặng riêng, không phải “gần Nha Trang đi bộ”.</p>',
            'attrs' => [
                'amenities' => ['Private beach', 'Spa', 'Restaurant', 'Kayak', 'Bicycle', 'Lighthouse tour', 'Wi-Fi'],
                'check_in' => '14:00',
                'check_out' => '12:00',
                'property_type' => 'boutique',
                'nearby' => [
                    ['name' => 'Hải đăng Ke Ga', 'distance' => 'Tour / xe đạp tuỳ gói', 'icon' => 'map-pin'],
                    ['name' => 'Phan Thiết', 'distance' => '~30 km', 'icon' => 'car'],
                    ['name' => 'Nha Trang', 'distance' => '~2,5 giờ xe', 'icon' => 'car'],
                ],
            ],
            'options' => [
                ['code' => 'azerai-garden-villa', 'name' => 'Garden Villa', 'price_from' => 8500000, 'capacity' => 2, 'amenities' => ['Hướng vườn'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Hướng vườn']],
                ['code' => 'azerai-ocean-villa', 'name' => 'Ocean Villa', 'price_from' => 10200000, 'capacity' => 2, 'amenities' => ['Hướng biển'], 'attrs' => ['bed' => '1 giường king', 'view' => 'Hướng biển']],
                ['code' => 'azerai-pool-villa', 'name' => 'Pool Villa', 'price_from' => 11800000, 'capacity' => 3, 'amenities' => ['Hồ bơi riêng'], 'attrs' => ['bed' => 'King + extra', 'view' => 'Hồ bơi riêng']],
            ],
            'faqs' => [
                ['q' => 'Azerai có gần Nha Trang không?', 'a' => 'Không — khoảng 2,5 giờ xe. Phù hợp nghỉ riêng tại Ke Ga / Phan Thiết, không phải resort nội đô Nha Trang.'],
            ],
        ],

        // ── EXPERIENCE (9) ───────────────────────────────────────────────────
        [
            'code' => 'exp-vinpearl-land-nha-trang',
            'cluster' => 'experience',
            'category_slug' => 'vinpearl',
            'country_slug' => 'viet-nam',
            'title' => 'Vé Vinpearl Land Nha Trang (cáp treo + công viên)',
            'slug' => 've-vinpearl-land-nha-trang',
            'price_from' => 950000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 1243,
            'is_featured' => true,
            'is_hot_deal' => true,
            'discount_badge' => 'Combo',
            'location_label' => 'Đảo Hòn Tre, Nha Trang',
            'summary' => 'Vé combo cáp treo vượt biển dài nhất Việt Nam và vào cửa Vinpearl Land — tàu lượn, công viên nước, show nhạc nước và thủy cung.',
            'highlights' => [
                'Cáp treo 3.320m — kỷ lục Việt Nam',
                'Tàu lượn, công viên nước, khu trò chơi trong nhà',
                'Show nhạc nước buổi tối (theo lịch)',
                'Thủy cung và khu vui chơi trẻ em',
                'ViTravel giao vé QR, không xếp hàng mua tại quầy',
            ],
            'inclusions' => ['Vé cáp treo khứ hồi', 'Vé vào Vinpearl Land', 'Phí dịch vụ'],
            'exclusions' => ['Ăn uống trong công viên', 'Trò chơi tính phí riêng', 'Đưa đón khách sạn'],
            'notes' => ['Trẻ em cao dưới 1m miễn phí (theo quy định Vinpearl).'],
            'attrs' => ['duration_hours' => 6, 'venue' => 'Vinpearl Land Nha Trang', 'ticket_type' => 'combo'],
        ],
        [
            'code' => 'exp-vinpearl-safari-phu-quoc',
            'cluster' => 'experience',
            'category_slug' => 'vinpearl',
            'country_slug' => 'viet-nam',
            'title' => 'Vinpearl Safari Phú Quốc — vé vào cửa',
            'slug' => 'vinpearl-safari-phu-quoc',
            'price_from' => 650000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 876,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Bãi Dài, Phú Quốc',
            'summary' => 'Safari bán hoang dã lớn nhất Việt Nam — xe bus mui trần qua chuồng thú mở, nhà hơi, show chim và khu trưng bày ban ngày.',
            'highlights' => [
                '300+ loài, 3.000 cá thể động vật',
                'Safari bus mui trần — hươu cao cổ ăn từ tay',
                'Show chim và show hải cẩn (theo lịch)',
                'Khu Giraffe Restaurant view bầy hươu cao cổ',
                'Phù hợp gia đình có trẻ em',
            ],
            'inclusions' => ['Vé vào cửa', 'Safari bus', 'Phí dịch vụ ViTravel'],
            'exclusions' => ['Ăn uống', 'Đưa đón', 'Gói combo VinWonders'],
            'notes' => ['Nên đi sáng sớm — thú hoạt động mạnh hơn buổi trưa.'],
            'attrs' => ['duration_hours' => 4, 'venue' => 'Vinpearl Safari Phú Quốc', 'ticket_type' => 'admission'],
        ],
        [
            'code' => 'exp-fansipan-cable-car',
            'cluster' => 'experience',
            'category_slug' => 'cap-treo',
            'country_slug' => 'viet-nam',
            'title' => 'Cáp treo Fansipan Legend — Sun World Fansipan',
            'slug' => 'cap-treo-fansipan-legend',
            'price_from' => 750000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 2105,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Sa Pa, Lào Cai — đỉnh Fansipan 3.143m',
            'summary' => 'Chinh phục nóc nhà Đông Dương bằng cáp treo 3 cáp nối tiếp — không cần leo 2–3 ngày, view ruộng bậc thang và đỉnh núi phủ sương.',
            'highlights' => [
                'Độ cao 3.143m — cao nhất Đông Nam Á',
                'Cáp treo 3 cáp, giữ kỷ lục Guinness',
                'Chùa và tượng Phật trên đỉnh',
                'View Hoàng Liên Sơn 360°',
                'Kết hợp tốt với lưu trú Hotel de la Coupole',
            ],
            'inclusions' => ['Vé cáp treo khứ hồi', 'Phí dịch vụ'],
            'exclusions' => ['Ăn uống trên đỉnh', 'Đưa đón Sa Pa', 'Quần áo ấm (mùa đông)'],
            'notes' => ['Nhiệt độ đỉnh có thể 5–10°C — mang áo khoác.', 'Sương mù che view — may rủi theo thời tiết.'],
            'attrs' => ['duration_hours' => 3, 'venue' => 'Sun World Fansipan Legend', 'peak_elevation' => '3143m'],
        ],
        [
            'code' => 'exp-sunworld-ba-na-hills',
            'cluster' => 'experience',
            'category_slug' => 'cap-treo',
            'country_slug' => 'viet-nam',
            'title' => 'Sun World Ba Na Hills — vé cáp treo & công viên',
            'slug' => 'sunworld-ba-na-hills',
            'price_from' => 900000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 3421,
            'is_featured' => true,
            'is_hot_deal' => true,
            'discount_badge' => 'Hot',
            'location_label' => 'Bà Nà Hills, Đà Nẵng',
            'summary' => 'Làng Pháp trên mây, Cầu Vàng Golden Bridge, cáp treo dài và vui chơi giải trí — điểm đến số 1 Đà Nẵng cho mọi lứa tuổi.',
            'highlights' => [
                'Cầu Vàng — biểu tượng du lịch Việt Nam',
                'Làng Pháp cổ điển, nhà thờ, quảng trường',
                'Cáp treo 4 cáp — kỷ lục dài',
                'Fantasy Park — trò chơi trong nhà',
                'Buffet trưa optional tại nhà hàng Morin',
            ],
            'inclusions' => ['Vé cáp treo khứ hồi', 'Vé vào cổng', 'Phí dịch vụ'],
            'exclusions' => ['Buffet trưa', 'Đưa đón', 'Trò chơi tính phí'],
            'notes' => ['Nên đi sớm 7–8h tránh đông.', 'Mùa mưa sương mù — view hạn chế.'],
            'attrs' => ['duration_hours' => 5, 'venue' => 'Sun World Ba Na Hills'],
        ],
        [
            'code' => 'exp-hon-thom-cable-car',
            'cluster' => 'experience',
            'category_slug' => 'cap-treo',
            'country_slug' => 'viet-nam',
            'title' => 'Cáp treo Hòn Thơm — Sun World Hon Thom',
            'slug' => 'cap-treo-hon-thom-phu-quoc',
            'price_from' => 600000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 987,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Nam Phú Quốc — An Thới',
            'summary' => 'Cáp treo vượt biển 7.899m nối An Thới — đảo Hòn Thơm, kết hợp bãi biển hoang sơ và công viên nước Aquatopia.',
            'highlights' => [
                'Cáp treo biển dài nhất thế giới (theo Sun Group)',
                'Bãi biển Kem và bãi Sao gần đó',
                'Aquatopia water park trên Hòn Thơm',
                'View toàn cảnh quần đảo An Thới',
                'Combo vé cáp treo + công viên nước tiết kiệm',
            ],
            'inclusions' => ['Vé cáp treo khứ hồi', 'Phí dịch vụ'],
            'exclusions' => ['Aquatopia (mua combo riêng)', 'Ăn uống', 'Đưa đón'],
            'notes' => ['Thời tiết xấu có thể tạm dừng cáp treo — hoàn vé theo quy định.'],
            'attrs' => ['duration_hours' => 4, 'venue' => 'Sun World Hon Thom Nature Park'],
        ],
        [
            'code' => 'exp-paragliding-da-nang',
            'cluster' => 'experience',
            'category_slug' => 'du-luon',
            'country_slug' => 'viet-nam',
            'title' => 'Dù lượn tandem Đà Nẵng — Son Tra / Hòa Nhơn',
            'slug' => 'du-luon-tandem-da-nang',
            'price_from' => 1800000,
            'currency' => 'VND',
            'rating' => 4.9,
            'review_count' => 412,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Sơn Trà / Hòa Nhơn, Đà Nẵng',
            'summary' => 'Bay tandem cùng phi công chứng nhận — lượn trên bán đảo Sơn Trà hoặc đồi Hòa Nhơn, nhìn toàn cảnh biển Mỹ Khê, cầu Rồng và đèo Hải Vân.',
            'highlights' => [
                'Phi công tandem kinh nghiệm 500+ chuyến',
                'Thời gian bay 15–20 phút tùy gió',
                'GoPro video optional (phí riêng)',
                'Briefing an toàn trước chuyến bay',
                'Khung giờ sáng sớm — gió ổn định nhất',
            ],
            'inclusions' => ['1 chuyến bay tandem', 'Thiết bị bảo hộ', 'Phi công', 'Phí dịch vụ'],
            'exclusions' => ['Video/photo GoPro', 'Đưa đón', 'Bảo hiểm mua thêm'],
            'notes' => ['Phụ thuộc thời tiết — có thể hoãn sang ngày khác.', 'Cân nặng 40–90 kg.'],
            'attrs' => ['duration_hours' => 2, 'activity' => 'paragliding', 'location' => 'Da Nang'],
        ],
        [
            'code' => 'exp-kayak-halong-floating-village',
            'cluster' => 'experience',
            'category_slug' => 'cano',
            'country_slug' => 'viet-nam',
            'title' => 'Kayak & làng chài Vung Viêng — Vịnh Hạ Long',
            'slug' => 'kayak-lang-chai-vung-vieng-ha-long',
            'price_from' => 450000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 567,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Vùng vịnh Hạ Long — làng chài Vung Viêng',
            'summary' => 'Chèo kayak qua hang động và vùng nước yên tĩnh, ghé làng chài nổi Vung Viêng — trải nghiệm đích thực cuộc sống trên vịnh.',
            'highlights' => [
                'Kayak 2 người qua hang và vùng nước trong xanh',
                'Thăm làng chài — xem nuôi cá bè',
                'Hướng dẫn viên địa phương',
                'Thường kết hợp tour day boat hoặc du thuyền',
                'Áo phao và thiết bị an toàn đầy đủ',
            ],
            'inclusions' => ['Kayak & thiết bị', 'HDV', 'Phí làng chài'],
            'exclusions' => ['Tour tàu/du thuyền (book riêng hoặc combo)', 'Ăn trưa'],
            'notes' => ['Hoạt động ngoài trời — mặc đồ nhanh khô.', 'Có thể book combo day cruise + kayak.'],
            'attrs' => ['duration_hours' => 3, 'activity' => 'kayak', 'location' => 'Ha Long Bay'],
        ],
        [
            'code' => 'exp-jet-ski-nha-trang',
            'cluster' => 'experience',
            'category_slug' => 'the-thao-bien',
            'country_slug' => 'viet-nam',
            'title' => 'Jet ski Nha Trang — Bãi Trí Nguyên',
            'slug' => 'jet-ski-nha-trang',
            'price_from' => 800000,
            'currency' => 'VND',
            'rating' => 4.5,
            'review_count' => 334,
            'is_featured' => false,
            'is_hot_deal' => true,
            'discount_badge' => '15 phút',
            'location_label' => 'Bãi Trí Nguyên, Nha Trang',
            'summary' => 'Lái jet ski trên vịnh Nha Trang — 15 phút adrenaline với hướng dẫn cơ bản, view cầu Trần Phú và đảo xa bờ.',
            'highlights' => [
                'Jet ski Yamaha — bảo dưỡng định kỳ',
                '15 phút lái (có thể gia hạn)',
                'Áo phao và briefing an toàn',
                'Không cần bằng lái (theo khu vực có giám sát)',
                'Chụp ảnh action optional',
            ],
            'inclusions' => ['15 phút jet ski', 'Áo phao', 'Briefing'],
            'exclusions' => ['Gia hạn thêm phút', 'Photo package', 'Đưa đón'],
            'notes' => ['Trẻ em dưới 16 tuổi ngồi sau người lớn.'],
            'attrs' => ['duration_hours' => 1, 'activity' => 'jet_ski', 'location' => 'Nha Trang'],
            'options' => [
                ['code' => 'jetski-15', 'name' => '15 phút', 'price_from' => 800000],
                ['code' => 'jetski-30', 'name' => '30 phút', 'price_from' => 1400000],
            ],
        ],
        [
            'code' => 'exp-parasailing-phu-quoc',
            'cluster' => 'experience',
            'category_slug' => 'the-thao-bien',
            'country_slug' => 'viet-nam',
            'title' => 'Parasailing Phú Quốc — Bãi Sao / Long Beach',
            'slug' => 'parasailing-phu-quoc',
            'price_from' => 650000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 289,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Bãi Sao / Bãi Dài, Phú Quốc',
            'summary' => 'Bay parasail cao 80–100m phía trên biển Phú Quốc — view toàn cảnh bãi cát trắng và rừng nhiệt đới, 8–10 phút trên không.',
            'highlights' => [
                'Bay đôi hoặc đơn — tùy cân nặng',
                'Tàu kéo an toàn, thiết bị kiểm định',
                'View bãi Sao từ trên cao',
                'Không cần kinh nghiệm — ngồi harness',
                'Hoạt động buổi sáng — gió và sóng ổn',
            ],
            'inclusions' => ['1 lần parasail (~8 phút)', 'Thiết bị an toàn', 'Phí dịch vụ'],
            'exclusions' => ['Photo/video trên không', 'Đưa đón'],
            'notes' => ['Hủy nếu thời tiết xấu — hoàn 100%.'],
            'attrs' => ['duration_hours' => 1, 'activity' => 'parasailing', 'location' => 'Phu Quoc'],
        ],

        // ── OTHER (7) ────────────────────────────────────────────────────────
        [
            'code' => 'other-car-rental',
            'cluster' => 'other',
            'category_slug' => 'thue-xe',
            'country_slug' => 'viet-nam',
            'title' => 'Thuê xe — tự lái & có lái',
            'slug' => 'thue-xe-tu-lai-co-lai',
            'price_from' => 850000,
            'currency' => 'VND',
            'rating' => 4.7,
            'review_count' => 1123,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Hà Nội, Đà Nẵng, Sài Gòn, Phú Quốc — toàn quốc',
            'summary' => 'Thuê xe tự lái sedan/SUV hoặc xe có tài xế theo ngày — giao tận khách sạn, phù hợp city tour, intercity và lịch trình tour tùy chỉnh.',
            'highlights' => [
                'Tự lái: Toyota Vios, Fortuner — xe mới dưới 3 năm',
                'Có lái: tài xế kinh nghiệm, tiếng Anh cơ bản (yêu cầu trước)',
                'Giao — nhận xe tại khách sạn hoặc sân bay',
                'Bảo hiểm vật chất cơ bản (tự lái) / xăng + lương tài xế (có lái)',
                'Hỗ trợ 24/7 khi sự cố trên đường',
            ],
            'inclusions' => ['Xe theo gói đã chọn', 'Bảo hiểm/phí cơ bản theo loại', 'Phí dịch vụ ViTravel'],
            'exclusions' => ['Xăng dầu (tự lái)', 'Tip tài xế', 'Overtime', 'Phí giao xe ngoài nội thành'],
            'notes' => ['Tự lái cần GPLX Việt Nam hoặc IDP.', 'Intercity có lái — báo giá theo chuyến.'],
            'attrs' => ['service_type' => 'car_rental', 'rental_modes' => ['self_drive', 'with_driver']],
            'options' => [
                ['code' => 'rent-vios-self', 'name' => 'Vios tự lái (4 chỗ/ngày)', 'price_from' => 850000, 'description' => 'Giới hạn 200 km/ngày'],
                ['code' => 'rent-fortuner-self', 'name' => 'Fortuner tự lái (7 chỗ/ngày)', 'price_from' => 1350000],
                ['code' => 'rent-with-driver', 'name' => 'Xe có lái (8–10 giờ/ngày)', 'price_from' => 1200000, 'description' => 'Sedan 4–7 chỗ, xăng trong giới hạn km'],
            ],
        ],
        [
            'code' => 'other-spa-wellness',
            'cluster' => 'other',
            'category_slug' => 'spa',
            'country_slug' => 'viet-nam',
            'title' => 'Spa & wellness — gói thư giãn 90 phút',
            'slug' => 'spa-wellness-goi-90-phut',
            'price_from' => 650000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 234,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Đối tác tại Hà Nội, Đà Nẵng, Sài Gòn, Phú Quốc',
            'summary' => 'Gói spa 90 phút — body scrub, massage body và facial — tại spa 4–5 sao đối tác hoặc in-room tại resort (tùy địa điểm).',
            'highlights' => [
                'Spa đối tác chuẩn vệ sinh và dịch vụ',
                'Nguyên liệu tự nhiên — sả, gừng, cà phê',
                'In-room spa tại resort (Phú Quốc, Đà Nẵng)',
                'Đặt lịch linh hoạt 9h–21h',
                'Quà voucher cho khách tour ViTravel',
            ],
            'inclusions' => ['Gói spa 90 phút', 'Đồ uống chào mừng', 'Phí đặt lịch'],
            'exclusions' => ['Tip kỹ thuật viên', 'Vận chuyển đến spa'],
            'notes' => ['Thai massage / hot stone — phụ phí 100–200k.'],
            'attrs' => ['service_type' => 'spa', 'duration_minutes' => 90],
        ],
        [
            'code' => 'other-massage-foot-body',
            'cluster' => 'other',
            'category_slug' => 'massage',
            'country_slug' => 'viet-nam',
            'title' => 'Massage chân & body — 60 phút',
            'slug' => 'massage-chan-body-60-phut',
            'price_from' => 350000,
            'currency' => 'VND',
            'rating' => 4.5,
            'review_count' => 567,
            'is_featured' => false,
            'is_hot_deal' => true,
            'discount_badge' => 'Phổ biến',
            'location_label' => 'Hà Nội, Hội An, Nha Trang, Sài Gòn',
            'summary' => 'Massage truyền thống Việt Nam — xoa bóp chân, vai gáy sau ngày dài đi tour — spa uy tín, không ép tip.',
            'highlights' => [
                'Massage chân + body 60 phút',
                'Spa được ViTravel kiểm duyệt — an toàn, sạch',
                'Không ép mua thêm dịch vụ',
                'Đặt lịch nhanh qua Zalo ViTravel',
                'Giá cố định — không phát sinh',
            ],
            'inclusions' => ['Massage 60 phút', 'Khăn, đồ uống trà'],
            'exclusions' => ['Tip (tuỳ ý)', 'Vận chuyển'],
            'notes' => ['Couple room — đặt trước 24h.'],
            'attrs' => ['service_type' => 'massage', 'duration_minutes' => 60],
        ],
        [
            'code' => 'other-luggage-shipper',
            'cluster' => 'other',
            'category_slug' => 'shipper',
            'country_slug' => 'viet-nam',
            'title' => 'Gửi hành lý — hotel to hotel / airport',
            'slug' => 'gui-hanh-ly-hotel-to-hotel',
            'price_from' => 250000,
            'currency' => 'VND',
            'rating' => 4.6,
            'review_count' => 189,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Nội thành Hà Nội, Đà Nẵng, Sài Gòn',
            'summary' => 'Gửi vali từ khách sạn này sang khách sạn khác hoặc ra sân bay — đi tour nhẹ tay, không kéo vali cả ngày.',
            'highlights' => [
                'Pickup tại khách sạn — giao đích trong 24h',
                'Tracking qua SMS/Zalo',
                'Bảo hiểm hàng hoá cơ bản',
                'Hỗ trợ gửi ra sân bay trước chuyến bay',
                'Vali 20–30 kg — phụ phí vali lớn',
            ],
            'inclusions' => ['Gửi 1 vali ≤20 kg', 'Tracking', 'Bảo hiểm cơ bản'],
            'exclusions' => ['Vali quá cân', 'Gửi ngoại thành', 'Đồ dễ vỡ không đóng gói'],
            'notes' => ['Đặt trước 12h so với giờ pickup.'],
            'attrs' => ['service_type' => 'luggage_shipping', 'max_weight_kg' => 20],
        ],
        [
            'code' => 'other-private-guide',
            'cluster' => 'other',
            'category_slug' => 'huong-dan-vien',
            'country_slug' => 'viet-nam',
            'title' => 'Hướng dẫn viên riêng — tiếng Anh / Pháp / Nhật',
            'slug' => 'huong-dan-vien-rieng-da-ngon-ngu',
            'price_from' => 1500000,
            'currency' => 'VND',
            'rating' => 4.9,
            'review_count' => 312,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Hà Nội, Huế, Hội An, Sài Gòn, Phú Quốc',
            'summary' => 'HDV riêng thẻ hành nghề — dẫn city tour, di sản, ẩm thực đường phố — linh hoạt lịch trình, không ghép đoàn.',
            'highlights' => [
                'HDV thẻ quốc tế TCHQ / địa phương',
                'Tiếng Anh, Pháp, Nhật, Hàn (theo yêu cầu)',
                '8 giờ/ngày — overtime báo trước',
                'Am hiểu lịch sử, ẩm thực, chụp ảnh',
                'Phù hợp gia đình và VIP cần riêng tư',
            ],
            'inclusions' => ['HDV 8 giờ', 'Phí dịch vụ ViTravel'],
            'exclusions' => ['Vé tham quan', 'Ăn uống HDV', 'Xe (thuê riêng)'],
            'notes' => ['Tết và lễ — phụ phí 20–30%.'],
            'attrs' => ['service_type' => 'private_guide', 'languages' => ['en', 'fr', 'ja', 'ko']],
            'options' => [
                ['code' => 'guide-en', 'name' => 'HDV tiếng Anh', 'price_from' => 1500000],
                ['code' => 'guide-fr', 'name' => 'HDV tiếng Pháp', 'price_from' => 1800000],
            ],
        ],
        [
            'code' => 'other-medical-assistance',
            'cluster' => 'other',
            'category_slug' => 'y-te',
            'country_slug' => 'viet-nam',
            'title' => 'Hỗ trợ y tế du lịch — tư vấn & đặt lịch bệnh viện',
            'slug' => 'ho-tro-y-te-du-lich',
            'price_from' => 500000,
            'currency' => 'VND',
            'rating' => 4.8,
            'review_count' => 87,
            'is_featured' => false,
            'is_hot_deal' => false,
            'location_label' => 'Toàn quốc — đối tác bệnh viện quốc tế',
            'summary' => 'Tư vấn y tế khi du lịch — kết nối phòng khám quốc tế, dịch thuật y khoa và đặt lịch khám nhanh tại Hà Nội, Đà Nẵng, Sài Gòn.',
            'highlights' => [
                'Hotline tư vấn y tế tiếng Anh/Việt',
                'Đặt lịch bệnh viện quốc tế (Vinmec, FV, Family Medical)',
                'Dịch thuật viên y tế đi cùng (optional)',
                'Hỗ trợ mua thuốc và telemedicine',
                'Phối hợp bảo hiểm du lịch nếu có',
            ],
            'inclusions' => ['Tư vấn & đặt lịch', 'Phí dịch vụ coordination'],
            'exclusions' => ['Chi phí khám chữa bệnh', 'Thuốc', 'Cấp cứu vận chuyển'],
            'notes' => ['Không thay thế cấp cứu 115 — gọi 115 khi nguy hiểm tính mạng.'],
            'attrs' => ['service_type' => 'medical_assistance'],
        ],
        [
            'code' => 'other-emergency-support-247',
            'cluster' => 'other',
            'category_slug' => 'khan-cap',
            'country_slug' => 'viet-nam',
            'title' => 'Hỗ trợ khẩn cấp 24/7 — hotline ViTravel',
            'slug' => 'ho-tro-khan-cap-24-7',
            'price_from' => 0,
            'currency' => 'VND',
            'rating' => 5.0,
            'review_count' => 156,
            'is_featured' => true,
            'is_hot_deal' => false,
            'location_label' => 'Toàn quốc — 24 giờ / 7 ngày',
            'summary' => 'Đường dây nóng hỗ trợ khách tour ViTravel — mất hộ chiếu, tai nạn, hủy chuyến đột xuất, hỗ trợ pháp lý cơ bản. Miễn phí cho khách đặt tour qua ViTravel.',
            'highlights' => [
                'Hotline 24/7 — tiếng Việt & tiếng Anh',
                'Hỗ trợ báo mất giấy tờ, hướng dẫn thủ tục',
                'Phối hợp cấp cứu và bệnh viện',
                'Hỗ trợ đổi lịch tour/hotel khi force majeure',
                'Miễn phí cho booking tour ViTravel — liên hệ để biết điều kiện',
            ],
            'inclusions' => ['Hotline 24/7', 'Coordination cơ bản'],
            'exclusions' => ['Chi phí thay thế dịch vụ', 'Luật sư', 'Vận chuyển y tế'],
            'notes' => ['Giá hiển thị "liên hệ" — dịch vụ miễn phí cho khách tour.', 'Số hotline gửi trong voucher tour.'],
            'attrs' => ['service_type' => 'emergency_support', 'availability' => '24/7', 'price_label' => 'Liên hệ'],
            'faqs' => [
                ['q' => 'Ai được dùng hotline miễn phí?', 'a' => 'Khách đã đặt và thanh toán tour hoặc combo dịch vụ qua ViTravel — số hotline in trên voucher xác nhận.'],
            ],
        ],
    ],

    'service_listing_faqs' => [
        [
            'q' => 'Giá "từ" trên trang dịch vụ có phải giá cố định không?',
            'a' => 'Không — giá "từ" là mức tham khảo theo mùa thấp điểm hoặc loại tiêu chuẩn. ViTravel báo giá chính xác sau khi nhận ngày sử dụng, số lượng khách và yêu cầu cụ thể, trước khi bạn thanh toán.',
        ],
        [
            'q' => 'Tôi có thể kết hợp nhiều dịch vụ (vé tàu + khách sạn + tour) trong một đơn không?',
            'a' => 'Có. ViTravel thiết kế itinerary tùy chỉnh — gộp vé tàu/máy bay, lưu trú, vé vui chơi và dịch vụ hỗ trợ vào một báo giá minh bạch, một đầu mối chăm sóc.',
        ],
        [
            'q' => 'Chính sách hoàn/hủy dịch vụ lẻ khác tour trọn gói như thế nào?',
            'a' => 'Mỗi loại dịch vụ tuân theo điều kiện nhà cung cấp (hãng bay, resort, công viên…). ViTravel ghi rõ điều kiện hoàn trên báo giá và voucher — thường vé tàu/máy bay và vé công viên khó hoàn hơn dịch vụ spa hoặc thuê xe.',
        ],
        [
            'q' => 'ViTravel có bán vé máy bay và vé tàu trực tiếp trên website không?',
            'a' => 'Website hiển thị tham khảo và form yêu cầu báo giá (lead-gen). Sau khi bạn gửi yêu cầu, tư vấn viên xác nhận chỗ, giá và gửi link thanh toán hoặc hóa đơn — đảm bảo không phí ẩn.',
        ],
        [
            'q' => 'Dịch vụ hỗ trợ khẩn cấp 24/7 áp dụng khi nào?',
            'a' => 'Miễn phí cho khách đã đặt tour hoặc combo qua ViTravel. Hotline hỗ trợ mất giấy tờ, tai nạn, thay đổi lịch trình khẩn cấp và phối hợp cơ quan chức năng — số điện thoại ghi trên voucher xác nhận của bạn.',
        ],
    ],
];

$__companySeed = [
    'name' => 'ViTravel',
    'legal_name' => 'Công ty TNHH Du lịch ViTravel',
    'tagline' => 'Hài lòng hơn cả mong đợi',
    'slogan' => '“Hài lòng hơn cả mong đợi”',
    'license_number' => '01-2234/TCDL-GP-LHQT',
    'contact' => [
        'email' => 'hello@vitravel.vn',
        'phone' => '+84 24 3999 8888',
        'whatsapp' => '+84 912 345 678',
        'zalo' => '+84 24 3999 8888',
        'hotline_label' => 'Hotline',
    ],
    'address' => [
        'street' => '88 Xã Đàn, Đống Đa',
        'locality' => 'Hà Nội',
        'region' => 'Hà Nội',
        'postal' => '100000',
        'country' => 'VN',
    ],
    'social' => [
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'url' => 'https://www.facebook.com/vitravel',
        ],
        'youtube' => [
            'label' => 'YouTube',
            'icon' => 'play',
            'url' => 'https://www.youtube.com/@vitravel',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon' => 'photo',
            'url' => 'https://www.instagram.com/vitravel',
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'icon' => 'share',
            'url' => 'https://www.tiktok.com/@vitravel',
        ],
    ],
    'schema' => [
        'available_language' => ['Vietnamese', 'English'],
        'contact_type' => 'customer service',
        'logo' => null,
    ],
    'footer' => [
        'copyright' => '© :year ViTravel. Giấy phép lữ hành quốc tế số :license.',
        'show_dmca_badge' => true,
    ],
];

return array_merge(
    $__vitravelSeed,
    $__servicesSeed,
    ['company' => $__companySeed],
    ['customize_form' => [
        'destinations_label' => [
            'vi' => 'Bạn muốn ghé thăm quốc gia nào?',
            'en' => 'Which countries would you like to visit?',
        ],
        'accommodation_label' => [
            'vi' => 'Bạn thích loại lưu trú nào?',
            'en' => 'What kind of accommodation do you prefer?',
        ],
        'budget_note' => [
            'vi' => 'Ngân sách dự kiến (chưa gồm vé máy bay quốc tế)',
            'en' => 'Estimated budget (excluding international flights)',
        ],
        'accommodation' => [
            'vi' => [
                'Tiêu chuẩn (khách sạn 3*)',
                'Cao cấp (khách sạn 4*)',
                'Sang trọng (khách sạn 5*)',
                'Nhờ tư vấn giúp tôi',
            ],
            'en' => [
                'Standard (3* hotel)',
                'Superior (4* hotel)',
                'Deluxe (5* hotel)',
                'Please advise me',
            ],
        ],
    ]],
    ['nav' => [
        'about_group' => [
            'vi' => 'Về ViTravel',
            'en' => 'About ViTravel',
        ],
        'cruise' => [
            'label' => ['vi' => 'Du thuyền', 'en' => 'Cruises'],
            'all_label' => ['vi' => 'Tất cả du thuyền', 'en' => 'All cruises'],
            'all_meta' => ['vi' => 'Xem toàn bộ lịch trình du thuyền', 'en' => 'Browse all cruise itineraries'],
            'search_hint' => ['vi' => 'Tour, điểm đến, du thuyền, cẩm nang…', 'en' => 'Tours, destinations, cruises, guides…'],
            'search_placeholder' => ['vi' => 'Tìm tour, điểm đến, du thuyền, bài viết…', 'en' => 'Search tours, destinations, cruises, articles…'],
            'hub_title' => ['vi' => 'Du thuyền', 'en' => 'Cruises'],
            'hub_subtitle' => ['vi' => 'Chọn lịch trình du thuyền phù hợp với bạn.', 'en' => 'Choose a cruise itinerary that fits you.'],
        ],
    ]],
    ['listing_hubs' => [
        'tours_hub' => [
            'vi' => ['seo_body' => 'Trang tour trọn gói của :brand tập hợp hành trình Đông Nam Á — Việt Nam, Campuchia, Lào, Thái Lan và Bali. Mỗi lịch trình do chuyên gia bản địa thiết kế và có thể tuỳ chỉnh 100%.'],
            'en' => ['seo_body' => ':brand package tours across Southeast Asia — Vietnam, Cambodia, Laos, Thailand and Bali. Every itinerary is designed by local experts and fully customisable.'],
        ],
        'cruises_hub' => [
            'vi' => ['seo_body' => 'Trang du thuyền của :brand tuyển chọn hành trình ngủ đêm trên vịnh — Hạ Long, Lan Hạ và Mekong.'],
            'en' => ['seo_body' => ':brand cruises feature overnight bay journeys — Halong, Lan Ha and the Mekong.'],
        ],
        'trains_hub' => [
            'vi' => ['seo_body' => 'Đặt vé tàu cao tốc Việt Nam qua :brand — ghế mềm, giường nằm, hỗ trợ đổi ngày và giao vé.'],
            'en' => ['seo_body' => 'Book Vietnam train tickets via :brand — soft seats, sleeper berths, date changes and ticket delivery.'],
        ],
        'flights_hub' => [
            'vi' => ['seo_body' => 'Vé máy bay nội địa & châu Á qua chuyên gia :brand — báo giá nhanh trong 24 giờ.'],
            'en' => ['seo_body' => 'Domestic and Asia flights via :brand experts — quotes within 24 hours.'],
        ],
        'stays_hub' => [
            'vi' => ['seo_body' => 'Khách sạn & resort cao cấp do :brand tuyển chọn — Phú Quốc, Đà Nẵng, Nha Trang, Hạ Long…'],
            'en' => ['seo_body' => 'Hotels & resorts curated by :brand — Phu Quoc, Da Nang, Nha Trang, Halong and more.'],
        ],
        'experiences_hub' => [
            'vi' => ['seo_body' => 'Vé vui chơi & trải nghiệm Việt Nam qua :brand — Vinpearl, Fansipan, Bà Nà và thể thao biển.'],
            'en' => ['seo_body' => 'Vietnam tickets & experiences via :brand — Vinpearl, Fansipan, Ba Na and sea sports.'],
        ],
        'extras_hub' => [
            'vi' => ['seo_body' => 'Dịch vụ hỗ trợ du lịch của :brand: thuê xe, spa, HDV riêng và hotline khẩn cấp 24/7.'],
            'en' => ['seo_body' => ':brand travel support: vehicle hire, spa, private guides and 24/7 emergency hotline.'],
        ],
    ]],
);
