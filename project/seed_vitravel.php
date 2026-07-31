<?php

/**
 * ============================================================================
 * DỮ LIỆU ViTravel — được load qua PROJECT_SEED=vitravel
 * ============================================================================
 *
 * Entry khuyến nghị: project/seed_vitravel.php (require file này hoặc tự chứa data).
 * Schema: project/README.md | Config: .env PROJECT_SEED + config/project.php
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
      'password' => 'vitravel@admin2026',
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
      'seo_title' => 'Về chúng tôi — ViTravel, đại lý du lịch bản địa Đông Nam Á',
      'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ ViTravel — đại lý lữ hành quốc tế bản địa với hơn 10 năm thiết kế hành trình riêng tại Việt Nam và Đông Nam Á.',
      'page_title' => 'Về chúng tôi',
      'page_subtitle' => 'Hành trình chân thật, được thiết kế bởi những người bản địa yêu nghề',
      'banner' => 
      array (
        'src' => NULL,
        'srcset' => NULL,
        'alt' => 'Ảnh banner: đội ngũ ViTravel',
      ),
      'mission' => 
      array (
        'title' => 'Sứ mệnh của chúng tôi',
        'text' => 'Mang đến những hành trình chân thật giúp du khách chạm vào đời sống, văn hoá và con người bản địa — đồng thời tạo sinh kế bền vững cho cộng đồng tại mỗi điểm đến chúng tôi đi qua.',
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'vision' => 
      array (
        'title' => 'Tầm nhìn của chúng tôi',
        'text' => 'Trở thành đại lý du lịch bản địa được tin cậy nhất Đông Nam Á — nơi mỗi du khách rời đi với cảm giác "hài lòng hơn cả mong đợi" và một phần trái tim ở lại với điểm đến.',
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
        'hub_label' => 'Cam kết với giá trị cốt lõi',
      ),
      'reasons_section' => 
      array (
        'title' => 'Vì sao chọn ViTravel?',
        'cta_label' => 'Bắt đầu hành trình của bạn',
        'cta_url' => NULL,
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'reference_section' => 
      array (
        'title' => 'Người đại diện của chúng tôi tại nước ngoài',
        'subtitle' => 'Bạn có thể trao đổi trực tiếp bằng ngôn ngữ của mình với đại diện ViTravel tại châu Âu và châu Úc.',
      ),
    ),
    'en' => 
    array (
      'seo_title' => 'About us — ViTravel, Southeast Asia local travel agency',
      'seo_description' => 'Our story, mission and team at ViTravel — an international local travel agency with over 10 years designing private journeys in Vietnam and Southeast Asia.',
      'page_title' => 'About us',
      'page_subtitle' => 'Authentic journeys designed by locals who love what they do',
      'banner' => 
      array (
        'src' => NULL,
        'srcset' => NULL,
        'alt' => 'ViTravel team banner',
      ),
      'mission' => 
      array (
        'title' => 'Our mission',
        'text' => 'To deliver authentic journeys that let travellers touch local life, culture and people — while creating sustainable livelihoods for communities along every route we travel.',
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'vision' => 
      array (
        'title' => 'Our vision',
        'text' => 'To become the most trusted local travel agency in Southeast Asia — where every guest leaves feeling “more than satisfied” and leaves a piece of their heart at the destination.',
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
        'title' => 'Commitment to core values',
        'hub_label' => 'Commitment to core values',
      ),
      'reasons_section' => 
      array (
        'title' => 'Why choose ViTravel?',
        'cta_label' => 'Start your journey',
        'cta_url' => NULL,
        'image' => NULL,
        'imageSrcset' => NULL,
      ),
      'reference_section' => 
      array (
        'title' => 'Our representatives abroad',
        'subtitle' => 'You can speak directly in your own language with ViTravel representatives in Europe and Australia.',
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
        'body' => 'ViTravel là đại lý lữ hành đặt trụ sở tại Hà Nội, với hơn 10 năm đồng hành cùng du khách khám phá miền Bắc Việt Nam — từ vịnh Hạ Long, Sa Pa, Ninh Bình tới cao nguyên đá Hà Giang. Chúng tôi không bán những tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của đội ngũ chuyên gia sinh ra và lớn lên tại chính điểm đến.',
        'metaLine' => 'Giấy phép lữ hành quốc tế số 01-2234/TCDL-GP-LHQT',
        'ctaLabel' => 'Tìm hiểu về chúng tôi',
        'ctaUrl' => 'https://vitravel.dev/ve-chung-toi',
        'image' => NULL,
        'imageAlt' => 'Ảnh đội ngũ ViTravel tại văn phòng Hà Nội',
      ),
      'en' => 
      array (
        'key' => 'company_intro',
        'eyebrow' => 'Vietnam travel experts',
        'title' => 'Authentic journeys, designed by locals',
        'subtitle' => NULL,
        'body' => 'ViTravel is a Hanoi-based travel agency with over 10 years of guiding guests through Northern Vietnam — from Halong Bay, Sapa and Ninh Binh to the karst highlands of Ha Giang. We do not sell off-the-shelf packages — every itinerary is tailored from real on-the-ground experience.',
        'metaLine' => 'International travel license No. 01-2234/TCDL-GP-LHQT',
        'ctaLabel' => 'Learn about us',
        'ctaUrl' => 'https://vitravel.dev/ve-chung-toi',
        'image' => NULL,
        'imageAlt' => 'ViTravel team at our Hanoi office',
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
        'eyebrow' => 'Tàu cao tốc',
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
        'eyebrow' => 'High-speed trains',
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
        'eyebrow' => 'Đông Nam Á',
        'title' => 'Những điểm đến được yêu thích nhất',
        'subtitle' => 'Mỗi điểm đến một sắc màu — chọn nơi bạn muốn lắng nghe câu chuyện bản địa.',
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
        'eyebrow' => 'Southeast Asia',
        'title' => 'Our most loved destinations',
        'subtitle' => 'Each place has its own colour — choose where you want to hear local stories.',
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
        'ctaUrl' => 'https://vitravel.dev/cam-nhan-khach-hang',
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
        'ctaUrl' => 'https://vitravel.dev/cam-nhan-khach-hang',
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
        'ctaUrl' => 'https://vitravel.dev/doi-ngu',
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
        'ctaUrl' => 'https://vitravel.dev/doi-ngu',
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
        'title' => 'Đông Nam Á qua từng thước phim đẹp',
        'subtitle' => 'Những video chân thật do khách hàng và đội ngũ ViTravel ghi lại — chọn một khoảnh khắc để xem toàn màn hình.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'Xem tất cả video',
        'ctaUrl' => 'https://vitravel.dev/video-trai-nghiem',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
      'en' => 
      array (
        'key' => 'videos',
        'eyebrow' => 'Real experiences',
        'title' => 'Southeast Asia in unforgettable frames',
        'subtitle' => 'Authentic films from guests and our local team — tap any moment to watch full screen.',
        'body' => NULL,
        'metaLine' => NULL,
        'ctaLabel' => 'View all videos',
        'ctaUrl' => 'https://vitravel.dev/video-trai-nghiem',
        'image' => NULL,
        'imageAlt' => NULL,
      ),
    ),
    'quick_inquiry' => 
    array (
      'vi' => 
      array (
        'key' => 'quick_inquiry',
        'eyebrow' => NULL,
        'title' => 'Hỏi nhanh về tour',
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
        'eyebrow' => NULL,
        'title' => 'Quick tour inquiry',
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
      'description' => 
      array (
        'vi' => 'Lựa chọn phổ biến nhất cho lần đầu khám phá Việt Nam — đủ thời gian đi Hà Nội, Hạ Long và Sa Pa.',
        'en' => 'The most popular choice for first-time visitors — enough time for Hanoi, Halong Bay and Sapa.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Xuyên Việt Bắc – Trung – Nam trong 14 ngày, phù hợp ai muốn thấy trọn vẹn ba miền.',
        'en' => 'Cross Vietnam north to south in 14 days — ideal for seeing all three regions.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Thêm thời gian cho miền Trung và Đồng bằng sông Cửu Long so với lịch trình 10 ngày.',
        'en' => 'Extra time for central Vietnam and the Mekong Delta compared to a 10-day plan.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Hành trình trọn vẹn nhất: Hà Giang, cố đô Huế, Đà Lạt và mũi Cà Mau.',
        'en' => 'The most complete journey: Ha Giang, imperial Hue, Da Lat and Ca Mau cape.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Tour ngắn ngày: Sa Pa trekking, Phú Quốc nghỉ dưỡng, Hạ Long 2N1D.',
        'en' => 'Short breaks: Sapa trekking, Phu Quoc beach, Halong 2D1N.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Hà Nội, Hạ Long, Sa Pa, Ninh Bình và các vùng cao nguyên phía Bắc.',
        'en' => 'Hanoi, Halong Bay, Sapa, Ninh Binh and the northern highlands.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Huế, Đà Nẵng, Hội An và di sản miền Trung.',
        'en' => 'Hue, Da Nang, Hoi An and central heritage sites.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Siem Reap & Angkor trong một tuần — lịch trình gọn cho người bận rộn.',
        'en' => 'Siem Reap & Angkor in one week — a compact itinerary for busy travellers.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Angkor, Phnom Penh và biển hồ Sihanoukville hoặc Kampot.',
        'en' => 'Angkor, Phnom Penh and the coast at Sihanoukville or Kampot.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Chuyên sâu đền tháp Angkor Wat, Bayon, Ta Prohm và Banteay Srei.',
        'en' => 'In-depth temples: Angkor Wat, Bayon, Ta Prohm and Banteay Srei.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Ubud, đền Tanah Lot và bãi biển Seminyak trong một tuần.',
        'en' => 'Ubud, Tanah Lot temple and Seminyak beach in one week.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Khám phá Ubud, Nusa Penida và các resort biển phía nam.',
        'en' => 'Explore Ubud, Nusa Penida and southern beach resorts.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Resort 5 sao, spa và bãi biển riêng tư — lý tưởng trăng mật.',
        'en' => '5-star resorts, spa and private beaches — ideal for honeymoons.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Bangkok, Ayutthaya và một điểm biển (Phuket hoặc Krabi).',
        'en' => 'Bangkok, Ayutthaya and a beach stop (Phuket or Krabi).',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Bắc – Trung – Nam: Chiang Mai, Bangkok và biển phía nam.',
        'en' => 'North to south: Chiang Mai, Bangkok and southern beaches.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Luang Prabang, Pak Ou và thác Kuang Si — nhịp sống chậm bên Mekong.',
        'en' => 'Luang Prabang, Pak Ou caves and Kuang Si falls — slow life on the Mekong.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Luang Prabang, Vang Vieng và Vientiane — khám phá trọn miền trung Lào.',
        'en' => 'Luang Prabang, Vang Vieng and Vientiane — central Laos in depth.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Việt Nam & Campuchia trong một hành trình — Angkor và Mekong.',
        'en' => 'Vietnam & Cambodia in one journey — Angkor and the Mekong.',
      ),
      'seoIntro' => 
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
      'description' => 
      array (
        'vi' => 'Việt Nam, Campuchia, Lào và Thái Lan — trọn vẹn Đông Dương.',
        'en' => 'Vietnam, Cambodia, Laos and Thailand — full Indochina.',
      ),
      'seoIntro' => 
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

return array_merge($__vitravelSeed, require __DIR__.'/seed_services.php');
