<?php

/**
 * ============================================================================
 * DỮ LIỆU culaocham.net — profile `culaocham` (project:seed / migrate --seed)
 * ============================================================================
 *
 * Một file seed / một dự án: chứa đủ tours, cruises, company, catalogue dịch vụ.
 * Đây là trang thông tin du lịch + dịch vụ + kết nối TOÀN DIỆN của Cù Lao Chàm
 * (xã đảo Tân Hiệp, Hội An — Quảng Nam), Khu dự trữ sinh quyển thế giới được
 * UNESCO công nhận năm 2009. Khác với ViTravel (đa quốc gia), Cù Lao Chàm là
 * MỘT điểm đến duy nhất, nên khái niệm "countries" trong template ViTravel được
 * thay bằng "zones" (các khu vực trên đảo: Bãi Làng, Bãi Chồng, Bãi Xếp,
 * Bãi Hương, rạn san hô, hải đăng Hòn Lao và tuyến kết hợp Hội An / Cửa Đại).
 * Tương tự, cụm dịch vụ "train" (tàu hoả liên tỉnh) được đổi thành "ferry"
 * (cano cao tốc / tàu gỗ từ bến Cửa Đại — Hội An) vì ra đảo chỉ có đường biển,
 * hành trình cano thường 25–35 phút, tàu gỗ chợ khoảng 1.5–2 giờ.
 *
 * Lưu ý nội dung: đảo áp dụng quy định "Nói không với túi ni lông" và hạn chế
 * nhựa dùng một lần từ 2009 — mọi copy nên nhắc quy tắc sinh thái này.
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
 * Schema: project/README.md | Loader: App\Support\ProjectSeed::useProfile('culaocham')
 *
 * @return array<string, mixed>
 */

$__culaochamSeed = array(
    'meta' => array(
        'schema' => 1,
        'brand' => 'culaocham.net',
        'tagline' => 'Khu dự trữ sinh quyển UNESCO ngoài khơi Hội An',
        'admin' => array(
            'email' => 'admin@culaocham.dev',
            'name' => 'Admin culaocham.net',
            'password' => '111111',
        ),
        'primary_domain' => 'culaocham.dev',
        'domains' => array('culaocham.dev', 'www.culaocham.dev', 'culaocham.net', 'www.culaocham.net'),
        'exported_at' => '2026-08-09T00:00:00+00:00',
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
        'Du lịch xanh' => 'eco-travel',
    ),

    'content_tags' => array(
        'where-to-eat' => array('vi' => 'Ăn uống ở đâu?',



'en' => 'Where to eat & drink?'),
        'where-to-stay' => array('vi' => 'Ở đâu?',



'en' => 'Where to stay?'),
        'what-to-do' => array('vi' => 'Làm gì & xem gì?',



'en' => 'What to do & see?'),
        'how-to-get-there' => array('vi' => 'Ra đảo thế nào?',



'en' => 'How to get there?'),
        'travel-tips' => array('vi' => 'Mẹo du lịch',



'en' => 'Travel tips'),
        'trip-report' => array('vi' => 'Cảm nhận chuyến đi',



'en' => 'How was the trip?'),
        'which-tour' => array('vi' => 'Chọn tour nào?',



'en' => 'Which tour to choose?'),
        'eco-travel' => array('vi' => 'Du lịch xanh & bảo tồn',



'en' => 'Eco travel & conservation'),
    ),

    'travel_styles' => array(
        // (A) thời lượng chương trình — khớp chủ đề type=theme sort 0–4
        'day-trip' => array('vi' => 'Tour trong ngày', 'en' => 'Day trip'),
        '2n1d' => array('vi' => '2 ngày 1 đêm', 'en' => '2 days 1 night'),
        '3n2d' => array('vi' => '3 ngày 2 đêm', 'en' => '3 days 2 nights'),
        '4n3d' => array('vi' => '4 ngày 3 đêm', 'en' => '4 days 3 nights'),
        '5-plus-days' => array('vi' => 'Từ 5 ngày', 'en' => '5+ days'),
        // (B) tính chất / insight địa phương — khớp chủ đề type=theme sort 10+
        'lan-bien-san-ho' => array('vi' => 'Lặn biển & ngắm san hô', 'en' => 'Diving & snorkelling'),
        'van-hoa-lang-chai' => array('vi' => 'Văn hoá & làng chài', 'en' => 'Culture & fishing villages'),
        'trekking-ngam-canh' => array('vi' => 'Trekking & ngắm cảnh', 'en' => 'Trekking & viewpoints'),
        'gia-dinh-bien-dao' => array('vi' => 'Gia đình & biển đảo', 'en' => 'Family beach & island'),
        'cuoi-tuan-da-nang-hoi-an' => array('vi' => 'Cuối tuần từ Đà Nẵng & Hội An', 'en' => 'Weekend from Da Nang & Hoi An'),
    ),

    'review_platforms' => array(
        array('code' => 'tripadvisor', 'name' => 'Tripadvisor', 'rating' => 4.8, 'review_count' => 312, 'sort' => 0,
            'quote' => 'Được khen nhiều nhất ở tour trong ngày từ Hội An và điểm snorkel nước trong quanh Hòn Lao.',
            'link_label' => 'Đọc đánh giá trên Tripadvisor', 'url' => 'https://www.tripadvisor.com'),
        array('code' => 'google', 'name' => 'Google', 'rating' => 4.7, 'review_count' => 486, 'sort' => 1,
            'quote' => '4.7/5 trên Google Maps với gần 500 nhận xét về cano Cửa Đại — Cù Lao Chàm và tour lặn ngắm san hô.',
            'link_label' => 'Xem đánh giá trên Google', 'url' => 'https://www.google.com/maps'),
        array('code' => 'trustpilot', 'name' => 'Trustpilot', 'rating' => 4.6, 'review_count' => 78, 'sort' => 2,
            'quote' => 'Điểm "Xuất sắc" — khách quốc tế đánh giá cao mô hình đảo không túi ni lông và nhóm nhỏ.',
            'link_label' => 'Đọc đánh giá trên Trustpilot', 'url' => 'https://www.trustpilot.com'),
    ),

    'cruise_types' => array(
        array('slug' => 'thuyen-vong-dao', 'name' => 'Thuyền vòng đảo Cù Lao Chàm', 'count' => 1, 'image' => NULL, 'imageHero' => NULL, 'imageSrcset' => NULL, 'sort' => 10),
        array('slug' => 'thuyen-cau-muc-dem', 'name' => 'Thuyền câu mực đêm Bãi Làng', 'count' => 1, 'image' => NULL, 'imageHero' => NULL, 'imageSrcset' => NULL, 'sort' => 20),
        array('slug' => 'thuyen-lan-san-ho', 'name' => 'Cano lặn rạn san hô', 'count' => 1, 'image' => NULL, 'imageHero' => NULL, 'imageSrcset' => NULL, 'sort' => 30),
    ),

    'home_slides' => array(
        array(
            'sort' => 0, 'text_align' => 'center', 'link_url' => '/diem-den/bai-lang',
            'vi' => array('title' => 'Cù Lao Chàm', 'title_accent' => 'khu dự trữ sinh quyển UNESCO ngoài khơi Hội An',
                'description' => 'Tám hòn đảo giữa biển Quảng Nam — làng chài Bãi Làng, chùa Hải Tạng 300 năm và rạn san hô nước trong, chỉ 30 phút cano từ bến Cửa Đại.',
                'button_label' => 'Khám phá Cù Lao Chàm', 'image_alt' => 'Toàn cảnh Cù Lao Chàm nhìn từ trên cao'),




'en' => array('title' => 'Cu Lao Cham', 'title_accent' => 'a UNESCO Biosphere Reserve off Hoi An',
                'description' => 'Eight islands off the Quang Nam coast — the Bai Lang fishing village, the 300-year-old Hai Tang pagoda and clear-water reefs, just 30 minutes by speedboat from Cua Dai pier.',
                'button_label' => 'Explore Cu Lao Cham', 'image_alt' => 'Aerial view of Cu Lao Cham islands'),
        ),
        array(
            'sort' => 1, 'text_align' => 'center', 'link_url' => '/diem-den/ran-san-ho',
            'vi' => array('title' => 'Rạn san hô Cù Lao Chàm', 'title_accent' => 'khu bảo tồn biển sống động',
                'description' => 'Hơn 165 ha rạn san hô được bảo vệ — snorkel nước nông cho người mới và fun dive cho thợ lặn có chứng chỉ, cano xuất phát ngay từ Bãi Làng.',
                'button_label' => 'Xem tour lặn & snorkel', 'image_alt' => 'Snorkel trên rạn san hô Cù Lao Chàm'),




'en' => array('title' => 'Cu Lao Cham coral reefs', 'title_accent' => 'a living marine protected area',
                'description' => 'Over 165 hectares of protected reef — shallow snorkelling for beginners and fun dives for certified divers, with boats leaving from Bai Lang.',
                'button_label' => 'View diving & snorkel tours', 'image_alt' => 'Snorkelling on Cu Lao Cham coral reef'),
        ),
        array(
            'sort' => 2, 'text_align' => 'center', 'link_url' => '/diem-den/ket-hop-hoi-an',
            'vi' => array('title' => 'Đi trong ngày từ Hội An', 'title_accent' => 'hoặc ở lại một đêm thật yên',
                'description' => 'Cano sáng từ Cửa Đại, chiều về phố cổ — hoặc ngủ homestay Bãi Làng, Bãi Hương để cảm nhận đảo sau khi đoàn khách trong ngày rời đi.',
                'button_label' => 'Chọn lịch trình phù hợp', 'image_alt' => 'Bến cano Cửa Đại đi Cù Lao Chàm'),




'en' => array('title' => 'A day trip from Hoi An', 'title_accent' => 'or one very quiet night on the island',
                'description' => 'Morning speedboat from Cua Dai and back to the old town by afternoon — or stay at a Bai Lang or Bai Huong homestay and feel the island after the day crowds leave.',
                'button_label' => 'Find your itinerary', 'image_alt' => 'Cua Dai speedboat pier to Cu Lao Cham'),
        ),
    ),

    'zones' => array(
        array('slug' => 'bai-lang', 'name' => 'Làng Bãi Làng', 'size' => 'large', 'tourCount' => 3, 'tagline' => 'Bến cano, chợ Tân Hiệp và chùa Hải Tạng 300 năm'),
        array('slug' => 'bai-chong', 'name' => 'Bãi Chồng', 'size' => 'large', 'tourCount' => 2, 'tagline' => 'Bãi tắm biểu tượng — nước trong, cát mịn, ghế nằm'),
        array('slug' => 'bai-xep', 'name' => 'Bãi Xếp', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Vịnh nhỏ kín gió — kayak, SUP và cắm trại'),
        array('slug' => 'bai-huong', 'name' => 'Bãi Hương', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Làng chài phía nam — homestay và nhịp sống thật'),
        array('slug' => 'ran-san-ho', 'name' => 'Rạn san hô & điểm lặn', 'size' => 'large', 'tourCount' => 3, 'tagline' => 'Khu bảo tồn biển — snorkel và scuba quanh Hòn Lao'),
        array('slug' => 'hai-dang', 'name' => 'Hải đăng Hòn Lao', 'size' => 'normal', 'tourCount' => 1, 'tagline' => 'Đường trek lên đỉnh — toàn cảnh tám hòn đảo'),
        array('slug' => 'ket-hop-hoi-an', 'name' => 'Kết hợp Hội An / Cửa Đại', 'size' => 'normal', 'tourCount' => 2, 'tagline' => 'Cửa ngõ đất liền — phố cổ, bến cano và Đà Nẵng'),
    ),

    'zone_translations' => array(
        'bai-lang' => array('vi' => 'Làng Bãi Làng',



'en' => 'Bai Lang Village',
            'tagline' => array('vi' => 'Bến cano, chợ Tân Hiệp và chùa Hải Tạng 300 năm',



'en' => 'Speedboat pier, Tan Hiep market and the 300-year-old Hai Tang pagoda')),
        'bai-chong' => array('vi' => 'Bãi Chồng',



'en' => 'Bai Chong Beach',
            'tagline' => array('vi' => 'Bãi tắm biểu tượng — nước trong, cát mịn, ghế nằm',



'en' => 'The island’s signature beach — clear water, fine sand, sun loungers')),
        'bai-xep' => array('vi' => 'Bãi Xếp',



'en' => 'Bai Xep Cove',
            'tagline' => array('vi' => 'Vịnh nhỏ kín gió — kayak, SUP và cắm trại',



'en' => 'A sheltered little cove — kayaking, SUP and camping')),
        'bai-huong' => array('vi' => 'Bãi Hương',



'en' => 'Bai Huong Fishing Village',
            'tagline' => array('vi' => 'Làng chài phía nam — homestay và nhịp sống thật',



'en' => 'The southern fishing hamlet — homestays and everyday island life')),
        'ran-san-ho' => array('vi' => 'Rạn san hô & điểm lặn',



'en' => 'Coral reefs & dive sites',
            'tagline' => array('vi' => 'Khu bảo tồn biển — snorkel và scuba quanh Hòn Lao',



'en' => 'Marine protected area — snorkelling and scuba around Hon Lao')),
        'hai-dang' => array('vi' => 'Hải đăng Hòn Lao',



'en' => 'Hon Lao Lighthouse',
            'tagline' => array('vi' => 'Đường trek lên đỉnh — toàn cảnh tám hòn đảo',



'en' => 'A hike to the summit — panorama over all eight islands')),
        'ket-hop-hoi-an' => array('vi' => 'Kết hợp Hội An / Cửa Đại',



'en' => 'Combined with Hoi An / Cua Dai',
            'tagline' => array('vi' => 'Cửa ngõ đất liền — phố cổ, bến cano và Đà Nẵng',



'en' => 'Mainland gateway — the old town, the speedboat pier and Da Nang')),
    ),

    'tours' => array(
        array(
            'slug' => 'cu-lao-cham-1-ngay-tu-hoi-an',
            'title' => 'Cù Lao Chàm 1 ngày từ Hội An — cano cao tốc, làng chài & snorkel',
            'zoneSlug' => 'bai-lang',
            'zone' => 'Làng Bãi Làng',
            'tourCode' => 'CLC1D-01',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 341,
            'badge' => 'Bán chạy nhất',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'lan-bien-san-ho',
                'gia-dinh-bien-dao',
                'cuoi-tuan-da-nang-hoi-an',
            ),
            'quote' => array(
                'text' => 'Rời phố cổ lúc 7 giờ sáng, 30 phút sau đã đứng trước chùa Hải Tạng — chuyến đi trong ngày đáng giá nhất ở Hội An.',
                'author' => 'Anh Quốc Trung',
            ),
            'places' => array(
                'Bến Cửa Đại',
                'Bãi Làng',
                'Chùa Hải Tạng',
                'Chợ Tân Hiệp',
                'Rạn san hô',
                'Bãi Chồng',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Lịch trình kinh điển: cano sáng từ Cửa Đại, tham quan làng Bãi Làng và chùa Hải Tạng, một buổi snorkel rạn san hô, ăn trưa hải sản và nghỉ biển Bãi Chồng.',
            'highlights' => array(
                'Cano cao tốc Cửa Đại — Cù Lao Chàm chỉ khoảng 30 phút',
                'Thăm chùa Hải Tạng hơn 300 năm và giếng cổ Chăm',
                'Snorkel ngắm san hô trong khu bảo tồn biển',
                'Ăn trưa hải sản tại Bãi Làng',
                'Nghỉ biển và ghế nằm tại Bãi Chồng',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Cửa Đại — Bãi Làng — rạn san hô — Bãi Chồng',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Đón khách sạn Hội An khoảng 07:15, ra bến Cửa Đại làm thủ tục và lên cano. Tới Bãi Làng thăm chùa Hải Tạng, giếng cổ và chợ Tân Hiệp. Sau đó cano đưa ra điểm snorkel ngắm san hô, ăn trưa hải sản, chiều nghỉ tại Bãi Chồng và về đất liền khoảng 14:30–15:30.',
                ),
            ),
            'inclusions' => array(
                'Xe đưa đón khách sạn Hội An — bến Cửa Đại',
                'Vé cano khứ hồi Cửa Đại — Cù Lao Chàm',
                'Phí tham quan khu bảo tồn biển',
                'Bữa trưa hải sản',
                'Thiết bị snorkel + áo phao',
                'HDV tiếng Việt / tiếng Anh',
            ),
            'exclusions' => array(
                'Đồ uống ngoài chương trình',
                'Ghế nằm/dù riêng tại Bãi Chồng (nếu yêu cầu loại VIP)',
                'Tiền tip',
                'Bảo hiểm du lịch',
            ),
            'notes' => array(
                'Đảo áp dụng quy định không mang túi ni lông — vui lòng dùng túi vải và bình nước cá nhân.',
                'Lịch cano phụ thuộc thời tiết; mùa gió mạnh có thể tạm ngưng chuyến.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Từ Hội An ra Cù Lao Chàm mất bao lâu?',
                    'a' => 'Xe từ phố cổ tới bến Cửa Đại khoảng 15–20 phút; cano cao tốc Cửa Đại — Bãi Làng thường 25–35 phút tuỳ sóng. Nếu đi tàu gỗ chợ thì mất khoảng 1.5–2 giờ.',
                ),
                array(
                    'q' => 'Trẻ nhỏ đi tour này được không?',
                    'a' => 'Được. Snorkel luôn có áo phao và khu nước nông; trẻ dưới 5 tuổi thường ở lại bãi cùng người lớn thay vì xuống điểm san hô sâu.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 690000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'lang-bai-lang-chua-hai-tang-nua-ngay',
            'title' => 'Làng Bãi Làng & chùa Hải Tạng — văn hoá đảo nửa ngày',
            'zoneSlug' => 'bai-lang',
            'zone' => 'Làng Bãi Làng',
            'tourCode' => 'CLCHD-02',
            'duration' => 'Nửa ngày (3–4 giờ)',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 88,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'van-hoa-lang-chai',
            ),
            'quote' => array(
                'text' => 'HDV kể chuyện thương cảng Chăm và nghề yến sào — nghe xong nhìn hòn đảo khác hẳn.',
                'author' => 'Chị Diệu Linh',
            ),
            'places' => array(
                'Chùa Hải Tạng',
                'Giếng cổ Chăm',
                'Chợ Tân Hiệp',
                'Nhà trưng bày yến sào',
                'Âu thuyền Bãi Làng',
            ),
            'start' => 'Cù Lao Chàm',
            'end' => 'Cù Lao Chàm',
            'highlightsIntro' => 'Buổi sáng hoặc chiều đi bộ quanh Bãi Làng cùng HDV bản địa: chùa cổ, giếng Chăm, chợ đảo và câu chuyện nghề yến.',
            'highlights' => array(
                'Chùa Hải Tạng hơn 300 năm',
                'Giếng cổ Chăm còn nước ngọt quanh năm',
                'Chợ Tân Hiệp và hàng hải sản khô',
                'Nghe kể về nghề khai thác yến sào',
                'Đi bộ nhẹ, phù hợp mọi lứa tuổi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đi bộ khám phá Bãi Làng',
                    'meals' => 'Nhẹ',
                    'transport' => array(
                        'walk',
                    ),
                    'overnight' => null,
                    'content' => 'Xuất phát từ bến Bãi Làng, đi bộ qua chợ Tân Hiệp, giếng cổ, chùa Hải Tạng và khu nhà trưng bày. Kết thúc tại quán ven biển với một phần đồ ăn nhẹ địa phương.',
                ),
            ),
            'inclusions' => array(
                'HDV bản địa',
                'Phần ăn nhẹ / nước',
                'Phí tham quan điểm trong chương trình',
            ),
            'exclusions' => array(
                'Vé cano ra đảo',
                'Đồ uống gọi thêm',
                'Công đức tại chùa (tuỳ tâm)',
            ),
            'notes' => array(
                'Nên mặc trang phục lịch sự khi vào chùa Hải Tạng.',
            ),
            'faqs' => array(),
            'galleryCount' => 4,
            'priceFrom' => 320000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cu-lao-cham-2-ngay-1-dem-bai-lang',
            'title' => 'Cù Lao Chàm 2 ngày 1 đêm — homestay Bãi Làng & câu mực đêm',
            'zoneSlug' => 'bai-lang',
            'zone' => 'Làng Bãi Làng',
            'tourCode' => 'CLC2D-03',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 176,
            'badge' => 'Được yêu thích',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'van-hoa-lang-chai',
                'cuoi-tuan-da-nang-hoi-an',
            ),
            'quote' => array(
                'text' => 'Buổi chiều khi cano cuối rời đảo, Bãi Làng trở nên yên đến khó tin — đó mới là Cù Lao Chàm thật.',
                'author' => 'Anh Nhật Minh',
            ),
            'places' => array(
                'Bãi Làng',
                'Chùa Hải Tạng',
                'Rạn san hô',
                'Bãi Chồng',
                'Vùng biển câu mực',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Hai ngày để thấy cả hai mặt của đảo: nhịp nhộn nhịp ban ngày và sự tĩnh lặng sau khi đoàn khách trong ngày về đất liền.',
            'highlights' => array(
                'Ngủ homestay nhà dân tại Bãi Làng',
                'Câu mực đêm cùng ngư dân',
                'Snorkel rạn san hô buổi sáng sớm ít người',
                'Bữa tối hải sản do chủ nhà nấu',
                'Đón bình minh trên âu thuyền',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ra đảo — Làng Bãi Làng — Câu mực đêm',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Bãi Làng',
                    'content' => 'Cano sáng từ Cửa Đại, nhận phòng homestay, tham quan chùa Hải Tạng và chợ Tân Hiệp. Chiều tắm Bãi Chồng, tối ăn hải sản nhà dân rồi lên thuyền câu mực khoảng 2 giờ.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Snorkel sáng — Cano về Cửa Đại',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng sớm ra điểm san hô khi biển còn lặng và chưa đông, quay về ăn trưa tại Bãi Làng, chiều lên cano về Cửa Đại và đưa về khách sạn Hội An.',
                ),
            ),
            'inclusions' => array(
                'Homestay 1 đêm tại Bãi Làng',
                'Cano khứ hồi + xe đưa đón Hội An',
                'Phí tham quan khu bảo tồn biển',
                'Bữa ăn theo chương trình',
                'Thuyền câu mực đêm',
                'Thiết bị snorkel',
                'HDV bản địa',
            ),
            'exclusions' => array(
                'Đồ uống có cồn',
                'Chi phí cá nhân',
                'Tiền tip',
            ),
            'notes' => array(
                'Homestay là nhà dân — tiện nghi đơn giản, điện và wifi có thể yếu vào giờ cao điểm.',
                'Câu mực đêm huỷ khi sóng lớn, hoàn lại phần chi phí tương ứng.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Ngủ lại đảo có cần đăng ký gì không?',
                    'a' => 'Homestay sẽ khai báo lưu trú giúp bạn — chỉ cần mang theo CCCD/hộ chiếu bản gốc.',
                ),
                array(
                    'q' => 'Có nên mang đồ ăn từ đất liền ra không?',
                    'a' => 'Hạn chế mang bao bì nhựa và túi ni lông ra đảo. Đồ ăn, nước uống trên đảo đầy đủ và bạn có thể mua bình nước tái sử dụng tại Bãi Làng.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 1690000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'bai-chong-nghi-bien-1-ngay',
            'title' => 'Bãi Chồng 1 ngày — nghỉ biển, ghế nằm & hải sản nướng',
            'zoneSlug' => 'bai-chong',
            'zone' => 'Bãi Chồng',
            'tourCode' => 'CLC1D-04',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 142,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                'day-trip',
                'gia-dinh-bien-dao',
            ),
            'quote' => array(
                'text' => 'Không chạy điểm, không vội — cả ngày chỉ tắm biển và ăn hải sản nướng dưới hàng dừa.',
                'author' => 'Gia đình chị Bảo Trâm',
            ),
            'places' => array(
                'Bãi Chồng',
                'Rạn san hô gần bờ',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Tour ngày “chỉ biển”: cano thẳng ra Bãi Chồng, ghế nằm cả ngày, một lần snorkel gần bờ và bữa trưa hải sản nướng tại bãi.',
            'highlights' => array(
                'Ghế nằm và dù che cả ngày tại Bãi Chồng',
                'Snorkel gần bờ có áo phao',
                'Hải sản nướng tại bãi',
                'Nước tắm tráng và phòng thay đồ',
                'Không lịch trình dày, phù hợp gia đình',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Một ngày trọn ở Bãi Chồng',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Cano sáng từ Cửa Đại tới Bãi Chồng, nhận ghế nằm, tự do tắm biển. Giữa buổi có một lượt snorkel gần bờ, trưa ăn hải sản nướng, chiều nghỉ tiếp rồi về đất liền.',
                ),
            ),
            'inclusions' => array(
                'Xe đưa đón Hội An',
                'Cano khứ hồi',
                'Phí tham quan khu bảo tồn biển',
                'Ghế nằm + dù',
                'Bữa trưa hải sản nướng',
                'Thiết bị snorkel',
            ),
            'exclusions' => array(
                'Đồ uống có cồn',
                'Thuê kayak/SUP (có thể thêm)',
                'Tip',
            ),
            'notes' => array(
                'Mang kem chống nắng thân thiện với rạn san hô — hạn chế oxybenzone.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Bãi Chồng có nhà tắm nước ngọt không?',
                    'a' => 'Có khu tắm tráng và thay đồ; một số dịch vụ thu phí nhỏ tại chỗ.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 750000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'bai-chong-gia-dinh-2-ngay',
            'title' => 'Bãi Chồng 2 ngày 1 đêm — gia đình: biển, snorkel nhẹ & bungalow',
            'zoneSlug' => 'bai-chong',
            'zone' => 'Bãi Chồng',
            'tourCode' => 'CLC2D-05',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.8,
            'reviewCount' => 97,
            'badge' => 'Gia đình',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'gia-dinh-bien-dao',
            ),
            'quote' => array(
                'text' => 'Hai con 6 và 9 tuổi snorkel được ngay bờ, buổi tối cả nhà đi bộ ra bãi ngắm sao.',
                'author' => 'Chị Hoàng Yến',
            ),
            'places' => array(
                'Bãi Chồng',
                'Rạn san hô gần bờ',
                'Bãi Làng',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Kỳ nghỉ ngắn cho gia đình: bungalow sát biển Bãi Chồng, một buổi snorkel nước nông và một buổi tự do không lịch trình.',
            'highlights' => array(
                'Bungalow / lều glamping sát Bãi Chồng',
                'Snorkel nước nông có áo phao trẻ em',
                'Bữa tối hải sản gia đình',
                'Buổi sáng bãi vắng trước khi cano khách trong ngày tới',
                'Nhịp độ chậm, phù hợp trẻ từ 5 tuổi',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ra đảo — Nhận phòng Bãi Chồng',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Bãi Chồng',
                    'content' => 'Cano sáng, nhận bungalow, ăn trưa tại bãi, chiều tắm biển và snorkel nhẹ gần bờ, tối ăn hải sản và đi dạo bãi.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Sáng bãi vắng — Ghé Bãi Làng — Về',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng tắm biển khi bãi còn vắng, ghé Bãi Làng và chùa Hải Tạng khoảng một giờ, ăn trưa rồi lên cano về Cửa Đại.',
                ),
            ),
            'inclusions' => array(
                'Bungalow / glamping 1 đêm',
                'Cano khứ hồi + xe Hội An',
                'Phí khu bảo tồn biển',
                'Bữa ăn theo chương trình',
                'Thiết bị snorkel người lớn & trẻ em',
            ),
            'exclusions' => array(
                'Đồ uống',
                'Kayak/SUP',
                'Tip',
            ),
            'notes' => array(
                'Áo phao trẻ em cần báo trước 24 giờ theo độ tuổi và cân nặng.',
            ),
            'faqs' => array(),
            'galleryCount' => 5,
            'priceFrom' => 2190000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'bai-xep-kayak-snorkel-nua-ngay',
            'title' => 'Bãi Xếp — kayak, SUP & snorkel vịnh kín gió nửa ngày',
            'zoneSlug' => 'bai-xep',
            'zone' => 'Bãi Xếp',
            'tourCode' => 'CLCHD-06',
            'duration' => 'Nửa ngày (3–4 giờ)',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 74,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                'day-trip',
                'lan-bien-san-ho',
            ),
            'quote' => array(
                'text' => 'Vịnh kín gió nên mặt nước phẳng như gương — chèo kayak lần đầu vẫn thoải mái.',
                'author' => 'Bạn Trúc Quỳnh',
            ),
            'places' => array(
                'Bãi Xếp',
                'Vách đá ven vịnh',
                'Điểm snorkel nước nông',
            ),
            'start' => 'Cù Lao Chàm',
            'end' => 'Cù Lao Chàm',
            'highlightsIntro' => 'Nửa buổi trong vịnh nhỏ kín gió: chèo kayak hoặc SUP dọc vách đá, rồi snorkel tại điểm nước nông ngay trong vịnh.',
            'highlights' => array(
                'Kayak đôi hoặc SUP theo lựa chọn',
                'Mặt nước phẳng, phù hợp người mới',
                'Snorkel ngay trong vịnh',
                'Nhóm tối đa 10 khách',
                'Có cứu hộ đi kèm',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Chèo vịnh Bãi Xếp & snorkel',
                    'meals' => null,
                    'transport' => array(
                        'boat',
                        'kayak',
                    ),
                    'overnight' => null,
                    'content' => 'Tập trung tại Bãi Xếp, hướng dẫn kỹ thuật cơ bản 15 phút, chèo dọc vách đá khoảng 45–60 phút, sau đó snorkel tại điểm nước nông và nghỉ trên bãi.',
                ),
            ),
            'inclusions' => array(
                'Kayak hoặc SUP',
                'Thiết bị snorkel',
                'Áo phao',
                'HDV / cứu hộ',
                'Nước uống',
            ),
            'exclusions' => array(
                'Ăn trưa',
                'Ảnh dưới nước',
                'Vé cano ra đảo',
            ),
            'notes' => array(
                'Chỉ tổ chức khi sóng nhẹ; nếu gió mạnh sẽ chuyển hoàn toàn sang snorkel gần bờ.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Chưa từng chèo kayak có đi được không?',
                    'a' => 'Được — vịnh Bãi Xếp kín gió và HDV hướng dẫn kỹ thuật trước khi xuống nước. Trẻ em chèo cùng người lớn trên kayak đôi.',
                ),
            ),
            'galleryCount' => 4,
            'priceFrom' => 480000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'cam-trai-bai-xep-2n1d',
            'title' => 'Cắm trại Bãi Xếp 2 ngày 1 đêm — ngủ lều nghe sóng',
            'zoneSlug' => 'bai-xep',
            'zone' => 'Bãi Xếp',
            'tourCode' => 'CLC2D-07',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.7,
            'reviewCount' => 53,
            'badge' => 'Bản địa',
            'featured' => false,
            'styles' => array(
                '2n1d',
                'trekking-ngam-canh',
            ),
            'quote' => array(
                'text' => 'Đêm không đèn đường, chỉ có tiếng sóng và trời đầy sao — đúng thứ tôi tìm khi rời Hội An.',
                'author' => 'Bạn Hải Đăng',
            ),
            'places' => array(
                'Bãi Xếp',
                'Bãi Chồng',
                'Điểm ngắm sao ven vịnh',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Ngủ lều ngay sát vịnh Bãi Xếp, ăn tối BBQ hải sản, sáng dậy chèo kayak khi mặt biển còn phẳng.',
            'highlights' => array(
                'Lều trại dựng sẵn có nệm và đèn năng lượng mặt trời',
                'BBQ hải sản buổi tối',
                'Kayak sáng sớm',
                'Khu vệ sinh chung sạch sẽ',
                'Nhóm nhỏ, không loa lớn',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ra đảo — Dựng trại Bãi Xếp — BBQ',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Bãi Xếp',
                    'content' => 'Cano sáng ra đảo, di chuyển tới Bãi Xếp, nhận lều đã dựng sẵn. Chiều tắm biển và snorkel nhẹ, tối BBQ hải sản và ngắm sao.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Kayak bình minh — Về đất liền',
                    'meals' => 'Sáng',
                    'transport' => array(
                        'kayak',
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng chèo kayak quanh vịnh, ăn sáng tại trại, thu dọn và di chuyển về Bãi Làng để lên cano trưa về Cửa Đại.',
                ),
            ),
            'inclusions' => array(
                'Lều trại + nệm + đèn',
                'Cano khứ hồi + xe Hội An',
                'BBQ tối và bữa sáng',
                'Kayak buổi sáng',
                'HDV / hỗ trợ trại',
            ),
            'exclusions' => array(
                'Túi ngủ riêng (có thể thuê)',
                'Đồ uống có cồn',
                'Tip',
            ),
            'notes' => array(
                'Cắm trại chỉ tổ chức trong mùa biển êm và khi được phép của ban quản lý khu bảo tồn.',
                'Rác phải mang về đất liền — trại áp dụng nguyên tắc không để lại dấu vết.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Đêm có điện và nhà vệ sinh không?',
                    'a' => 'Có khu vệ sinh chung và đèn năng lượng mặt trời. Không có ổ cắm liên tục — nên mang pin dự phòng.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 1490000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'bai-huong-lang-chai-homestay-2n1d',
            'title' => 'Bãi Hương 2 ngày 1 đêm — sống cùng làng chài phía nam đảo',
            'zoneSlug' => 'bai-huong',
            'zone' => 'Bãi Hương',
            'tourCode' => 'CLC2D-08',
            'duration' => '2 ngày 1 đêm',
            'days' => 2,
            'rating' => 4.9,
            'reviewCount' => 61,
            'badge' => 'Bản địa',
            'featured' => true,
            'styles' => array(
                '2n1d',
                'van-hoa-lang-chai',
            ),
            'quote' => array(
                'text' => 'Bãi Hương gần như không có khách trong ngày — bữa cơm với gia đình chủ nhà là phần đáng nhớ nhất.',
                'author' => 'Chị Thu Trang',
            ),
            'places' => array(
                'Bãi Hương',
                'Miếu Tổ nghề yến',
                'Rừng ven làng',
                'Bãi biển làng chài',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Ở lại làng chài phía nam Hòn Lao — nơi giữ nhịp sống đảo nguyên bản nhất, ít khách đoàn và nhiều thời gian trò chuyện cùng người dân.',
            'highlights' => array(
                'Homestay nhà dân tại Bãi Hương',
                'Theo ghe ngư dân ra thăm lưới buổi chiều',
                'Bữa cơm gia đình với cá tươi trong ngày',
                'Nghe kể về nghề yến và tín ngưỡng đảo',
                'Đi bộ ven làng và bãi biển vắng',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ra đảo — Về Bãi Hương — Thăm lưới chiều',
                    'meals' => 'Trưa; Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Bãi Hương',
                    'content' => 'Cano sáng tới Bãi Làng, chuyển xe hoặc ghe về Bãi Hương, nhận homestay. Chiều theo ghe ngư dân thăm lưới, tối ăn cơm cùng gia đình chủ nhà.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Sáng làng chài — Về Bãi Làng — Cano về',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng đi bộ quanh làng, ghé miếu Tổ nghề yến, tắm biển bãi vắng. Trưa quay lại Bãi Làng, ăn trưa và lên cano về Cửa Đại.',
                ),
            ),
            'inclusions' => array(
                'Homestay 1 đêm tại Bãi Hương',
                'Cano khứ hồi + xe Hội An',
                'Di chuyển nội đảo Bãi Làng — Bãi Hương',
                'Bữa ăn theo chương trình',
                'Trải nghiệm theo ghe ngư dân',
                'HDV bản địa',
            ),
            'exclusions' => array(
                'Đồ uống có cồn',
                'Chi phí cá nhân',
                'Tip',
            ),
            'notes' => array(
                'Hoạt động theo ghe phụ thuộc lịch đánh bắt của gia đình và điều kiện biển.',
                'Bãi Hương ít hàng quán — nên mang theo thuốc cá nhân cần thiết.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Bãi Hương cách Bãi Làng bao xa?',
                    'a' => 'Khoảng 5–6 km đường ven đảo, đi xe khoảng 15–20 phút hoặc ghe khoảng 20 phút tuỳ sóng.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 1790000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'bai-huong-cau-ca-cung-ngu-dan-nua-ngay',
            'title' => 'Câu cá cùng ngư dân Bãi Hương — nửa ngày',
            'zoneSlug' => 'bai-huong',
            'zone' => 'Bãi Hương',
            'tourCode' => 'CLCHD-09',
            'duration' => 'Nửa ngày (3–4 giờ)',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 44,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'van-hoa-lang-chai',
            ),
            'quote' => array(
                'text' => 'Cá câu được mang thẳng lên quán chế biến — bữa trưa ngon nhất chuyến đi.',
                'author' => 'Anh Đình Khoa',
            ),
            'places' => array(
                'Vùng biển Bãi Hương',
                'Rạn đá ven bờ',
            ),
            'start' => 'Bãi Hương',
            'end' => 'Bãi Hương',
            'highlightsIntro' => 'Lên ghe của gia đình ngư dân, thả câu tại rạn đá ven bờ và mang thành quả về chế biến ngay tại làng.',
            'highlights' => array(
                'Ghe gỗ địa phương, tối đa 6 khách',
                'Ngư cụ và mồi chuẩn bị sẵn',
                'Chế biến cá câu được tại quán làng',
                'Áo phao đầy đủ',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Thả câu ven rạn đá',
                    'meals' => 'Nhẹ',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Xuất bến Bãi Hương, ra rạn đá gần bờ thả câu khoảng 2 giờ, quay về làng và chế biến phần cá câu được.',
                ),
            ),
            'inclusions' => array(
                'Ghe + ngư dân dẫn',
                'Ngư cụ và mồi',
                'Áo phao',
                'Phí chế biến cơ bản',
            ),
            'exclusions' => array(
                'Đồ uống',
                'Món gọi thêm ngoài phần cá câu được',
            ),
            'notes' => array(
                'Không tổ chức khi biển động; sản lượng câu phụ thuộc con nước.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 420000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'lan-ngam-san-ho-cu-lao-cham-1-ngay',
            'title' => 'Lặn ngắm san hô Cù Lao Chàm — 1 ngày trong khu bảo tồn biển',
            'zoneSlug' => 'ran-san-ho',
            'zone' => 'Rạn san hô & điểm lặn',
            'tourCode' => 'CLC1D-10',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 268,
            'badge' => 'Được yêu thích',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'lan-bien-san-ho',
            ),
            'quote' => array(
                'text' => 'Ba điểm snorkel khác nhau trong một ngày, mỗi điểm một kiểu san hô — hơn hẳn tour ghép chỉ dừng một chỗ.',
                'author' => 'Chị Phương Anh',
            ),
            'places' => array(
                'Hòn Dài',
                'Hòn Tai',
                'Rạn ven Bãi Bắc',
                'Bãi Chồng',
            ),
            'start' => 'Cù Lao Chàm',
            'end' => 'Cù Lao Chàm',
            'highlightsIntro' => 'Ngày chuyên dưới nước: 2–3 điểm snorkel trong khu bảo tồn biển Cù Lao Chàm, HDV lặn đi kèm và bữa trưa trên bãi.',
            'highlights' => array(
                '2–3 điểm snorkel quanh Hòn Dài / Hòn Tai',
                'Thiết bị mặt nạ, ống thở, áo phao đầy đủ',
                'HDV lặn hướng dẫn kỹ thuật và quy tắc bảo tồn',
                'Bữa trưa hải sản tại bãi',
                'Có thể nâng cấp intro dive tại chỗ',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Ba điểm san hô trong khu bảo tồn',
                    'meals' => 'Trưa',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Xuất bến Bãi Làng buổi sáng, lần lượt tới 2–3 điểm san hô tuỳ độ trong nước trong ngày, nghỉ trưa tại bãi, chiều quay về bến trước 15:30.',
                ),
            ),
            'inclusions' => array(
                'Cano/thuyền cả ngày',
                'Thiết bị snorkel',
                'Phí tham quan khu bảo tồn biển',
                'Bữa trưa',
                'HDV lặn',
            ),
            'exclusions' => array(
                'Ảnh/video dưới nước',
                'Bình khí scuba',
                'Đồ uống có cồn',
            ),
            'notes' => array(
                'Tuyệt đối không chạm, đứng hay bẻ san hô — khu vực được bảo vệ nghiêm ngặt.',
                'Điểm lặn có thể đổi theo gió và độ trong nước từng ngày.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Mùa nào nước trong nhất để snorkel?',
                    'a' => 'Khoảng tháng 3 đến tháng 8 biển êm và nước trong nhất. Từ tháng 10 đến tháng 2 gió mùa Đông Bắc mạnh, nhiều ngày cano tạm ngưng.',
                ),
                array(
                    'q' => 'Không biết bơi có snorkel được không?',
                    'a' => 'Được, với áo phao và HDV kèm tại vùng nước nông — nhưng nếu chưa vững nên báo trước để được xếp nhóm riêng.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 590000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'fun-dive-cu-lao-cham-1-ngay',
            'title' => 'Fun dive Cù Lao Chàm 1 ngày — 2 lần lặn cho thợ có chứng chỉ',
            'zoneSlug' => 'ran-san-ho',
            'zone' => 'Rạn san hô & điểm lặn',
            'tourCode' => 'CLC1D-11',
            'duration' => '1 ngày',
            'days' => 1,
            'rating' => 4.9,
            'reviewCount' => 96,
            'badge' => 'Trải nghiệm sâu',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'lan-bien-san-ho',
            ),
            'quote' => array(
                'text' => 'Điểm lặn gần bờ nhưng đáy còn nhiều san hô mềm và cá rạn — briefing an toàn rất chuyên nghiệp.',
                'author' => 'Anh Trần Duy, OW diver',
            ),
            'places' => array(
                'Hòn Dài',
                'Hòn Mồ',
                'Rạn sâu ven Hòn Lao',
            ),
            'start' => 'Cù Lao Chàm',
            'end' => 'Cù Lao Chàm',
            'highlightsIntro' => 'Hai lần lặn boat dive trong ngày dành cho thợ lặn có chứng chỉ Open Water trở lên, nhóm tối đa 4 khách/dive master.',
            'highlights' => array(
                '2 lần lặn với bình khí và chì',
                'Dive master tỉ lệ 1:4',
                'Briefing an toàn trước mỗi lần lặn',
                'Nghỉ mặt nước và ăn nhẹ giữa hai lần lặn',
                'Có thể thuê full set thiết bị',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Boat dive hai lần trong ngày',
                    'meals' => 'Trưa nhẹ',
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Tập trung sáng tại Bãi Làng, kiểm tra thiết bị và sức khoẻ, lần lặn thứ nhất khoảng 09:30, nghỉ mặt nước và ăn nhẹ, lần lặn thứ hai đầu giờ chiều, về bến khoảng 15:00.',
                ),
            ),
            'inclusions' => array(
                'Thuyền lặn',
                '2 bình khí + chì',
                'Dive master',
                'Bữa nhẹ và nước',
                'Phí khu bảo tồn biển',
            ),
            'exclusions' => array(
                'Thuê full set thiết bị (BCD, regulator, wetsuit)',
                'Bảo hiểm lặn chuyên biệt',
                'Ảnh dưới nước',
            ),
            'notes' => array(
                'Bắt buộc xuất trình thẻ chứng chỉ lặn và khai báo sức khoẻ.',
                'Không bay trong vòng 18 giờ sau lần lặn cuối.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Chưa có chứng chỉ thì lặn được không?',
                    'a' => 'Bạn có thể chọn gói intro dive dành cho người mới — có dive master kèm sát tại vùng nông, không yêu cầu chứng chỉ trước.',
                ),
            ),
            'galleryCount' => 5,
            'priceFrom' => 1650000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'snorkel-nua-ngay-ran-san-ho',
            'title' => 'Snorkel rạn san hô nửa ngày — gói gọn cho người mới',
            'zoneSlug' => 'ran-san-ho',
            'zone' => 'Rạn san hô & điểm lặn',
            'tourCode' => 'CLCHD-12',
            'duration' => 'Nửa ngày (3 giờ)',
            'days' => 1,
            'rating' => 4.7,
            'reviewCount' => 121,
            'badge' => null,
            'featured' => false,
            'styles' => array(
                'day-trip',
                'lan-bien-san-ho',
                'gia-dinh-bien-dao',
            ),
            'quote' => array(
                'text' => 'Chỉ nửa buổi nhưng đủ thấy san hô và đàn cá — hợp với lịch ở đảo một đêm.',
                'author' => 'Bạn Ngọc Hân',
            ),
            'places' => array(
                'Điểm san hô gần Bãi Chồng',
                'Rạn nước nông ven Hòn Lao',
            ),
            'start' => 'Cù Lao Chàm',
            'end' => 'Cù Lao Chàm',
            'highlightsIntro' => 'Gói snorkel ngắn cho khách ngủ lại đảo hoặc muốn ghép thêm vào lịch trình — một điểm san hô, thiết bị đầy đủ, khởi hành linh hoạt.',
            'highlights' => array(
                '1–2 điểm nước nông',
                'Thiết bị và áo phao',
                'HDV kèm nhóm nhỏ',
                'Khởi hành sáng hoặc chiều',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Nửa buổi dưới nước',
                    'meals' => null,
                    'transport' => array(
                        'boat',
                    ),
                    'overnight' => null,
                    'content' => 'Lên thuyền tại Bãi Làng hoặc Bãi Chồng, ra điểm san hô gần bờ, snorkel khoảng 60–90 phút rồi quay về.',
                ),
            ),
            'inclusions' => array(
                'Thuyền',
                'Thiết bị snorkel',
                'Áo phao',
                'HDV',
            ),
            'exclusions' => array(
                'Ăn uống',
                'Phí khu bảo tồn (nếu chưa mua theo ngày)',
                'Ảnh dưới nước',
            ),
            'notes' => array(
                'Không dùng kem chống nắng chứa oxybenzone khi xuống rạn.',
            ),
            'faqs' => array(),
            'galleryCount' => 3,
            'priceFrom' => 290000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'trekking-hai-dang-hon-lao-nua-ngay',
            'title' => 'Trekking hải đăng Hòn Lao — toàn cảnh tám hòn đảo',
            'zoneSlug' => 'hai-dang',
            'zone' => 'Hải đăng Hòn Lao',
            'tourCode' => 'CLCHD-13',
            'duration' => 'Nửa ngày (3–4 giờ)',
            'days' => 1,
            'rating' => 4.8,
            'reviewCount' => 69,
            'badge' => 'Ngắm cảnh',
            'featured' => true,
            'styles' => array(
                'day-trip',
                'trekking-ngam-canh',
            ),
            'quote' => array(
                'text' => 'Lên tới hải đăng nhìn thấy cả tám hòn — leo hơi mệt nhưng hoàn toàn xứng đáng.',
                'author' => 'Anh Vũ Thành',
            ),
            'places' => array(
                'Đường lên hải đăng',
                'Rừng nguyên sinh Hòn Lao',
                'Đỉnh ngắm toàn cảnh',
            ),
            'start' => 'Bãi Làng',
            'end' => 'Bãi Làng',
            'highlightsIntro' => 'Đường trek dốc vừa qua rừng nguyên sinh lên hải đăng Hòn Lao — điểm nhìn bao quát toàn bộ quần đảo và bờ biển Hội An phía xa.',
            'highlights' => array(
                'Trek khoảng 45–60 phút mỗi chiều',
                'Rừng nguyên sinh và hệ thực vật thuốc nam của đảo',
                'Toàn cảnh tám hòn từ đỉnh',
                'Khởi hành sáng sớm hoặc chiều mát',
                'Nhóm nhỏ có HDV',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Trek lên hải đăng và xuống làng',
                    'meals' => null,
                    'transport' => array(
                        'walk',
                    ),
                    'overnight' => null,
                    'content' => 'Xuất phát từ Bãi Làng khoảng 06:30 hoặc 15:30, leo theo đường bê tông và đường mòn rừng lên hải đăng, nghỉ ngắm cảnh 30 phút rồi xuống làng.',
                ),
            ),
            'inclusions' => array(
                'HDV bản địa',
                'Nước uống',
                'Đồ ăn nhẹ',
            ),
            'exclusions' => array(
                'Vé cano ra đảo',
                'Bữa chính',
                'Gậy trek (có thể mượn)',
            ),
            'notes' => array(
                'Cần giày thể thao đế bám; không khuyến khích cho người có vấn đề tim mạch hoặc khớp gối.',
                'Không leo lúc trưa nắng gắt hoặc khi có mưa lớn.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Đường lên hải đăng có khó không?',
                    'a' => 'Dốc vừa, dài khoảng 2 km với phần lớn là đường bê tông và đoạn mòn cuối. Người có thể lực trung bình leo được trong 45–60 phút.',
                ),
            ),
            'galleryCount' => 4,
            'priceFrom' => 380000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'hoi-an-cu-lao-cham-3n2d',
            'title' => 'Hội An — Cù Lao Chàm 3 ngày 2 đêm: phố cổ & đảo sinh quyển',
            'zoneSlug' => 'ket-hop-hoi-an',
            'zone' => 'Kết hợp Hội An / Cửa Đại',
            'tourCode' => 'CLCHA3D-14',
            'duration' => '3 ngày 2 đêm',
            'days' => 3,
            'rating' => 5,
            'reviewCount' => 87,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                '3n2d',
                'lan-bien-san-ho',
                'cuoi-tuan-da-nang-hoi-an',
            ),
            'quote' => array(
                'text' => 'Một đêm phố cổ, một đêm trên đảo — cảm nhận rõ hai nhịp sống chỉ cách nhau 30 phút cano.',
                'author' => 'Anh Lê Nam',
            ),
            'places' => array(
                'Phố cổ Hội An',
                'Bến Cửa Đại',
                'Bãi Làng',
                'Chùa Hải Tạng',
                'Rạn san hô',
                'Bãi Chồng',
            ),
            'start' => 'Hội An',
            'end' => 'Hội An',
            'highlightsIntro' => 'Combo cửa ngõ và đảo: một đêm phố cổ Hội An, một đêm trên Cù Lao Chàm, xen giữa là ngày lặn ngắm san hô.',
            'highlights' => array(
                '1 đêm khách sạn Hội An + 1 đêm homestay/bungalow trên đảo',
                'Đi bộ phố cổ và làng gốm Thanh Hà (tuỳ chọn)',
                'Cano khứ hồi Cửa Đại — Cù Lao Chàm',
                'Ngày snorkel rạn san hô trong khu bảo tồn',
                'Bữa tối hải sản trên đảo',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Nhận phòng Hội An — Phố cổ về đêm',
                    'meals' => 'Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Hội An',
                    'content' => 'Đón tại sân bay Đà Nẵng hoặc khách sạn, nhận phòng Hội An. Chiều đi bộ phố cổ, tối tự do với đèn lồng và ẩm thực địa phương.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Cano ra đảo — Bãi Làng — Snorkel',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Cù Lao Chàm',
                    'content' => 'Sáng ra bến Cửa Đại, cano ra Bãi Làng, thăm chùa Hải Tạng và chợ Tân Hiệp. Chiều snorkel rạn san hô, tối ăn hải sản và nghỉ đêm trên đảo.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Bãi Chồng buổi sáng — Cano về Hội An',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng tắm biển Bãi Chồng khi còn vắng, ăn trưa trên đảo rồi lên cano về Cửa Đại, đưa về Hội An hoặc sân bay Đà Nẵng theo lịch.',
                ),
            ),
            'inclusions' => array(
                '2 đêm lưu trú (1 Hội An + 1 trên đảo)',
                'Cano khứ hồi và toàn bộ xe đưa đón',
                'Phí tham quan khu bảo tồn biển',
                'Bữa ăn theo chương trình',
                'Thiết bị snorkel',
                'HDV suốt tuyến',
            ),
            'exclusions' => array(
                'Vé máy bay',
                'Vé tham quan phố cổ Hội An',
                'Chi phí cá nhân',
                'Tip',
            ),
            'notes' => array(
                'Có thể đảo thứ tự: ra đảo trước, nghỉ Hội An sau — tuỳ lịch cano và thời tiết.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Nếu cano bị huỷ vì thời tiết thì sao?',
                    'a' => 'Chúng tôi chuyển ngày ra đảo hoặc thay thế bằng chương trình đất liền tương đương, phần chênh lệch được hoàn lại minh bạch.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 4290000,
            'currency' => 'VND',
        ),
        array(
            'slug' => 'da-nang-hoi-an-cu-lao-cham-4n3d',
            'title' => 'Đà Nẵng — Hội An — Cù Lao Chàm 4 ngày 3 đêm',
            'zoneSlug' => 'ket-hop-hoi-an',
            'zone' => 'Kết hợp Hội An / Cửa Đại',
            'tourCode' => 'CLCDN4D-15',
            'duration' => '4 ngày 3 đêm',
            'days' => 4,
            'rating' => 4.9,
            'reviewCount' => 58,
            'badge' => null,
            'featured' => true,
            'styles' => array(
                '4n3d',
                'cuoi-tuan-da-nang-hoi-an',
            ),
            'quote' => array(
                'text' => 'Bay tới Đà Nẵng buổi trưa, bốn ngày sau về mà cảm giác đã đi ba chuyến khác nhau.',
                'author' => 'Chị Kim Ngân',
            ),
            'places' => array(
                'Đà Nẵng',
                'Ngũ Hành Sơn',
                'Phố cổ Hội An',
                'Cửa Đại',
                'Bãi Làng',
                'Rạn san hô',
                'Bãi Chồng',
            ),
            'start' => 'Đà Nẵng',
            'end' => 'Đà Nẵng',
            'highlightsIntro' => 'Trọn gói miền Trung cho khách bay tới Đà Nẵng: biển Mỹ Khê, Ngũ Hành Sơn, phố cổ Hội An và hai ngày trên đảo sinh quyển.',
            'highlights' => array(
                'Đón tận sân bay Đà Nẵng (DAD)',
                '1 đêm Đà Nẵng + 1 đêm Hội An + 1 đêm Cù Lao Chàm',
                'Ngũ Hành Sơn và phố cổ Hội An',
                'Cano ra Cù Lao Chàm và ngày snorkel',
                'Toàn bộ xe và cano trong một báo giá',
            ),
            'itinerary' => array(
                array(
                    'day' => 1,
                    'title' => 'Đón sân bay Đà Nẵng — Biển Mỹ Khê',
                    'meals' => 'Tối',
                    'transport' => array(
                        'plane',
                        'car',
                    ),
                    'overnight' => 'Đà Nẵng',
                    'content' => 'Đón tại sân bay DAD, nhận phòng Đà Nẵng, chiều tự do biển Mỹ Khê, tối dạo cầu Rồng và ăn hải sản.',
                ),
                array(
                    'day' => 2,
                    'title' => 'Ngũ Hành Sơn — Về Hội An',
                    'meals' => 'Sáng; Tối',
                    'transport' => array(
                        'car',
                    ),
                    'overnight' => 'Hội An',
                    'content' => 'Sáng tham quan Ngũ Hành Sơn, trưa di chuyển về Hội An nhận phòng, chiều đi bộ phố cổ, tối tự do.',
                ),
                array(
                    'day' => 3,
                    'title' => 'Cano ra Cù Lao Chàm — Snorkel — Ngủ đảo',
                    'meals' => 'Sáng; Trưa; Tối',
                    'transport' => array(
                        'car',
                        'boat',
                    ),
                    'overnight' => 'Cù Lao Chàm',
                    'content' => 'Sáng ra bến Cửa Đại, cano ra Bãi Làng, thăm chùa Hải Tạng, chiều snorkel rạn san hô, tối hải sản và nghỉ đêm trên đảo.',
                ),
                array(
                    'day' => 4,
                    'title' => 'Bãi Chồng — Cano về — Tiễn sân bay',
                    'meals' => 'Sáng; Trưa',
                    'transport' => array(
                        'boat',
                        'car',
                    ),
                    'overnight' => null,
                    'content' => 'Sáng tắm biển Bãi Chồng, trưa cano về Cửa Đại, đưa ra sân bay Đà Nẵng theo giờ bay.',
                ),
            ),
            'inclusions' => array(
                '3 đêm lưu trú (Đà Nẵng, Hội An, Cù Lao Chàm)',
                'Toàn bộ xe đưa đón và cano khứ hồi',
                'Phí tham quan theo chương trình',
                'Bữa ăn theo chương trình',
                'HDV suốt tuyến',
            ),
            'exclusions' => array(
                'Vé máy bay đến/đi Đà Nẵng',
                'Chi phí cá nhân',
                'Đồ uống có cồn',
                'Tip',
            ),
            'notes' => array(
                'Nên chọn chuyến bay đến trước 14:00 ngày 1 và bay về sau 17:00 ngày 4.',
                'Phần ra đảo có thể dời ngày trong tuyến nếu cano tạm ngưng do thời tiết.',
            ),
            'faqs' => array(
                array(
                    'q' => 'Tour có nhận khách quốc tế không?',
                    'a' => 'Có — HDV tiếng Anh theo yêu cầu, và chúng tôi hỗ trợ thủ tục lưu trú trên đảo cho khách nước ngoài.',
                ),
            ),
            'galleryCount' => 6,
            'priceFrom' => 6490000,
            'currency' => 'VND',
        ),
    ),

    'cruises' => array(
        array(
            'slug' => 'thuyen-vong-dao-cu-lao-cham-1-ngay',
            'title' => 'Thuyền vòng quần đảo Cù Lao Chàm — 1 ngày ngắm tám hòn',
            'typeSlug' => 'thuyen-vong-dao', 'typeName' => 'Thuyền vòng đảo Cù Lao Chàm',
            'tourCode' => 'CRCLC1D-01', 'duration' => '1 ngày', 'days' => 1,
            'rating' => 4.8, 'reviewCount' => 112, 'badge' => 'Được yêu thích',
            'styles' => array('beach', 'day-trip', 'balanced', 'eco'),
            'quote' => array('text' => 'Chỉ khi vòng ra ngoài khơi mới thấy hết tám hòn và những vách đá dựng đứng phía đông.', 'author' => 'Chị Mỹ Duyên'),
            'places' => array('Hòn Lao', 'Hòn Dài', 'Hòn Tai', 'Hòn Mồ', 'Bãi Bắc'),
            'start' => 'Bãi Làng', 'end' => 'Bãi Làng',
            'departurePort' => 'Bến Bãi Làng', 'boatClass' => 'Thuyền gỗ / cano mở', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => 'Hải trình trong ngày vòng quanh quần đảo: ngắm vách đá phía đông, dừng tắm tại bãi vắng và ăn trưa trên thuyền.',
            'highlights' => array('Vòng ngoài tám hòn của quần đảo', 'Dừng tắm 1–2 điểm bãi vắng', 'Ăn trưa hải sản trên thuyền', 'HDV kể về khu dự trữ sinh quyển'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Vòng quần đảo trong ngày', 'meals' => 'Trưa',
                    'transport' => array('boat'), 'overnight' => NULL,
                    'content' => 'Xuất bến Bãi Làng 08:30, vòng ngoài Hòn Dài — Hòn Tai — Hòn Mồ, dừng tắm và snorkel ngắn, ăn trưa trên thuyền, về bến khoảng 15:00.'),
            ),
            'inclusions' => array('Thuyền cả ngày', 'Bữa trưa', 'Áo phao', 'HDV'),
            'exclusions' => array('Đồ uống', 'Thiết bị snorkel chuyên (có thể thuê thêm)'),
            'notes' => array('Không có cabin ngủ — hành trình trong ngày, về bến cùng ngày.'),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 750000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'thuyen-cau-muc-dem-bai-lang',
            'title' => 'Thuyền câu mực đêm Bãi Làng — ra khơi cùng ngư dân',
            'typeSlug' => 'thuyen-cau-muc-dem', 'typeName' => 'Thuyền câu mực đêm Bãi Làng',
            'tourCode' => 'CRCLCNT-02', 'duration' => 'Buổi tối (2.5–3 giờ)', 'days' => 1,
            'rating' => 4.7, 'reviewCount' => 84, 'badge' => 'Đêm',
            'styles' => array('seafood', 'small-group', 'culture'),
            'quote' => array('text' => 'Đèn thuyền sáng cả một vùng biển đêm — câu được mực thì nướng ăn ngay trên thuyền.', 'author' => 'Anh Hữu Nghĩa'),
            'places' => array('Vùng biển đêm quanh Hòn Lao'),
            'start' => 'Bãi Làng', 'end' => 'Bãi Làng',
            'departurePort' => 'Bến Bãi Làng', 'boatClass' => 'Thuyền câu địa phương', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => 'Trải nghiệm chỉ dành cho khách ngủ lại đảo: theo thuyền ngư dân ra vùng nước gần bờ câu mực, nướng ngay trên thuyền.',
            'highlights' => array('Thuyền của ngư dân Bãi Làng', 'Đèn và ngư cụ đầy đủ', 'Nướng mực câu được ngay trên thuyền', 'Áo phao và hướng dẫn an toàn'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Câu mực đêm quanh Hòn Lao', 'meals' => 'Đồ nướng nhẹ',
                    'transport' => array('boat'), 'overnight' => NULL,
                    'content' => 'Lên thuyền tại bến Bãi Làng khoảng 19:00, ra vùng nước câu, trải nghiệm khoảng 2 giờ và về bến trước 22:00.'),
            ),
            'inclusions' => array('Thuyền và ngư dân dẫn', 'Ngư cụ, đèn câu', 'Đồ nướng nhẹ', 'Áo phao'),
            'exclusions' => array('Đồ uống có cồn', 'Hải sản mua thêm'),
            'notes' => array('Chỉ tổ chức cho khách lưu trú qua đêm trên đảo và khi biển lặng.'),
            'faqs' => array(),
            'galleryCount' => 3, 'priceFrom' => 390000.0, 'currency' => 'VND',
        ),
        array(
            'slug' => 'cano-lan-ran-san-ho-cu-lao-cham',
            'title' => 'Cano lặn rạn san hô Cù Lao Chàm — ngày chuyên dưới nước',
            'typeSlug' => 'thuyen-lan-san-ho', 'typeName' => 'Cano lặn rạn san hô',
            'tourCode' => 'CRCLC1D-03', 'duration' => '1 ngày', 'days' => 1,
            'rating' => 4.9, 'reviewCount' => 103, 'badge' => NULL,
            'styles' => array('diving-snorkel', 'diving', 'adventure', 'eco'),
            'quote' => array('text' => 'Cano nhỏ, đổi điểm nhanh theo con nước — đúng kiểu dive boat chứ không phải tàu chở đoàn.', 'author' => 'Chị Vân Dive'),
            'places' => array('Hòn Dài', 'Hòn Tai', 'Rạn ven Bãi Bắc'),
            'start' => 'Bãi Làng', 'end' => 'Bãi Làng',
            'departurePort' => 'Bến Bãi Làng', 'boatClass' => 'Cano lặn nhóm nhỏ', 'nightsOnBoard' => 0,
            'cabinTypes' => array(),
            'highlightsIntro' => 'Cano chuyên snorkel/scuba trong khu bảo tồn biển: đổi điểm linh hoạt theo độ trong nước, nhóm nhỏ, ít thời gian chờ.',
            'highlights' => array('2–3 điểm rạn trong ngày', 'Thiết bị snorkel sẵn trên cano', 'Có thể nâng cấp fun dive', 'Nhóm tối đa 12 khách'),
            'itinerary' => array(
                array('day' => 1, 'title' => 'Ngày dưới nước quanh Hòn Lao', 'meals' => 'Trưa nhẹ',
                    'transport' => array('boat'), 'overnight' => NULL,
                    'content' => 'Xuất bến sáng, lần lượt tới các điểm rạn tốt nhất trong ngày, nghỉ trưa tại bãi hoặc trên cano, về bến đầu giờ chiều.'),
            ),
            'inclusions' => array('Cano cả ngày', 'Thiết bị snorkel', 'HDV lặn', 'Trưa nhẹ'),
            'exclusions' => array('Bình khí scuba (gói fun dive báo riêng)', 'Ảnh/video dưới nước'),
            'notes' => array('Điểm lặn tuân thủ phân vùng của Ban quản lý Khu bảo tồn biển Cù Lao Chàm.'),
            'faqs' => array(),
            'galleryCount' => 4, 'priceFrom' => 820000.0, 'currency' => 'VND',
        ),
    ),

    'blog_categories' => array(
        array('slug' => 'bai-lang', 'name' => 'Làng Bãi Làng', 'zoneSlug' => 'bai-lang', 'count' => 3),
        array('slug' => 'ran-san-ho', 'name' => 'Rạn san hô', 'zoneSlug' => 'ran-san-ho', 'count' => 1),
        array('slug' => 'hoi-an-cua-ngo', 'name' => 'Hội An (cửa ngõ)', 'zoneSlug' => 'ket-hop-hoi-an', 'count' => 2),
        array('slug' => 'bai-huong', 'name' => 'Bãi Hương', 'zoneSlug' => 'bai-huong', 'count' => 1),
        array('slug' => 'bai-chong', 'name' => 'Bãi Chồng', 'zoneSlug' => 'bai-chong', 'count' => 1),
        array('slug' => 'hai-dang', 'name' => 'Hải đăng Hòn Lao', 'zoneSlug' => 'hai-dang', 'count' => 0),
    ),

    'popular_keywords' => array(
        'Kinh nghiệm du lịch Cù Lao Chàm', 'Cù Lao Chàm mùa nào đẹp nhất', 'Cano Cửa Đại đi Cù Lao Chàm',
        'Lặn ngắm san hô Cù Lao Chàm', 'Chi phí Cù Lao Chàm 1 ngày', 'Ngủ đêm ở Cù Lao Chàm',
        'Cù Lao Chàm không túi ni lông', 'Từ Đà Nẵng đi Cù Lao Chàm thế nào',
    ),

    'articles' => array(
        array(
            'slug' => 'di-cu-lao-cham-the-nao-tu-hoi-an-da-nang',
            'title' => 'Đi Cù Lao Chàm thế nào từ Hội An và Đà Nẵng? Cẩm nang cano & tàu gỗ 2026',
            'zoneSlug' => 'ket-hop-hoi-an', 'zone' => 'Kết hợp Hội An / Cửa Đại',
            'category' => 'Hội An (cửa ngõ)', 'categorySlug' => 'hoi-an-cua-ngo',
            'tags' => array('Di chuyển thế nào?', 'Mẹo du lịch'),
            'author' => 'Minh Trí', 'publishedAt' => '05/07/2026', 'updatedAt' => '20/07/2026',
            'views' => 2140, 'rating' => 4.9, 'ratingCount' => 68,
            'excerpt' => 'Từ phố cổ ra bến Cửa Đại chỉ 15–20 phút, cano cao tốc thêm khoảng 30 phút là tới Bãi Làng — so sánh cano, tàu gỗ chợ và cách đi từ Đà Nẵng.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cù Lao Chàm cách bờ biển Hội An khoảng 15 km và chỉ có thể ra đảo bằng đường biển. Bến chính là Cửa Đại (Hội An); ngoài ra một số chuyến xuất phát từ bến Cửa Đại mới hoặc bến Hội An tuỳ mùa. Dưới đây là các lựa chọn thực tế nhất.'),
                array('type' => 'h2', 'id' => 'cano-cua-dai', 'text' => 'I. Cano cao tốc từ bến Cửa Đại (phổ biến nhất)'),
                array('type' => 'p', 'text' => 'Cano cao tốc chạy khoảng 25–35 phút tới Bãi Làng, thường khởi hành buổi sáng từ 07:30 đến 09:00 và quay về đất liền đầu giờ chiều. Đây là phương tiện của hầu hết tour trong ngày. Vé thường được bán kèm phí tham quan khu bảo tồn biển.'),
                array('type' => 'h2', 'id' => 'tau-go-cho', 'text' => 'II. Tàu gỗ chợ — chậm, rẻ, đúng chất địa phương'),
                array('type' => 'p', 'text' => 'Tàu gỗ chở khách và hàng hoá của người dân đảo mất khoảng 1.5–2 giờ, giá rẻ hơn nhiều nhưng chỉ có một vài chuyến mỗi ngày và thường rời bến rất sớm. Phù hợp khách ngủ lại đảo, không hợp lịch trình trong ngày.'),
                array('type' => 'h2', 'id' => 'tu-da-nang', 'text' => 'III. Đi từ Đà Nẵng'),
                array('type' => 'p', 'text' => 'Từ trung tâm Đà Nẵng hoặc sân bay DAD tới bến Cửa Đại khoảng 35–45 km, đi xe riêng hay limousine mất 45–60 phút. Nên đặt xe đón lúc 06:15–06:45 để kịp cano sáng; nếu bay tới trong ngày, hãy chừa buffer ít nhất 2 giờ.'),
                array('type' => 'ul', 'items' => array(
                    'Cano cao tốc: ~30 phút, chạy hằng ngày trong mùa biển êm',
                    'Tàu gỗ chợ: 1.5–2 giờ, ít chuyến, rời bến sớm',
                    'Hội An → Cửa Đại: 15–20 phút bằng taxi/xe riêng',
                    'Đà Nẵng → Cửa Đại: 45–60 phút',
                )),
                array('type' => 'links', 'title' => 'Xem thêm:', 'links' => array(
                    array('label' => 'Tour Cù Lao Chàm 1 ngày từ Hội An', 'route' => array('tours.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-1-ngay-tu-hoi-an'))),
                    array('label' => 'Combo Hội An — Cù Lao Chàm 3N2Đ', 'route' => array('tours.show', array('zone' => 'ket-hop-hoi-an', 'slug' => 'hoi-an-cu-lao-cham-3n2d'))),
                )),
            ),
            'faqs' => array(
                array('q' => 'Có cần đặt vé cano trước không?', 'a' => 'Nên đặt trước, đặc biệt từ tháng 4 đến tháng 8 và các dịp lễ. Mùa gió mùa Đông Bắc (khoảng tháng 10 đến tháng 2) nhiều ngày cano tạm ngưng — hãy chọn đơn vị có chính sách đổi ngày rõ ràng.'),
                array('q' => 'Khách nước ngoài có ra đảo được không?', 'a' => 'Được. Chỉ cần mang hộ chiếu bản gốc; nếu ngủ lại đảo, homestay sẽ hỗ trợ khai báo lưu trú.'),
            ),
            'galleryCount' => 5,
        ),
        array(
            'slug' => 'cu-lao-cham-mua-nao-dep-nhat',
            'title' => 'Cù Lao Chàm mùa nào đẹp nhất? Lịch gió, sóng và ngày cano chạy',
            'zoneSlug' => 'bai-lang', 'zone' => 'Làng Bãi Làng',
            'category' => 'Làng Bãi Làng', 'categorySlug' => 'bai-lang',
            'tags' => array('Mẹo du lịch', 'Chơi gì, xem gì?'),
            'author' => 'Lan Hương', 'publishedAt' => '12/06/2026', 'updatedAt' => '22/07/2026',
            'views' => 1720, 'rating' => 4.8, 'ratingCount' => 54,
            'excerpt' => 'Tháng 3 đến tháng 8 là mùa biển êm, nước trong và cano chạy đều; từ tháng 10 gió mùa Đông Bắc khiến nhiều ngày phải hoãn chuyến.',
            'content' => array(
                array('type' => 'p', 'text' => 'Khác với nhiều điểm biển miền Trung, khả năng ra Cù Lao Chàm phụ thuộc gần như hoàn toàn vào sóng gió. Chọn đúng mùa không chỉ để đẹp trời mà còn để chắc chắn cano được phép rời bến.'),
                array('type' => 'h2', 'id' => 'mua-dep', 'text' => 'I. Tháng 3 – tháng 8: mùa đẹp nhất'),
                array('type' => 'p', 'text' => 'Biển êm, nước trong, tầm nhìn dưới nước tốt cho snorkel và lặn. Cano chạy đều mỗi ngày. Đây cũng là mùa cao điểm nên bãi Chồng đông vào giữa trưa — mẹo là ra sớm hoặc ngủ lại một đêm.'),
                array('type' => 'h2', 'id' => 'mua-chuyen-tiep', 'text' => 'II. Tháng 9: giao mùa, nên theo dõi dự báo'),
                array('type' => 'p', 'text' => 'Vẫn còn nhiều ngày đẹp nhưng bắt đầu có áp thấp và mưa dông. Nếu đi tháng 9, hãy chừa một ngày dự phòng trong lịch trình.'),
                array('type' => 'h2', 'id' => 'mua-gio', 'text' => 'III. Tháng 10 – tháng 2: mùa gió mùa Đông Bắc'),
                array('type' => 'p', 'text' => 'Sóng lớn, nhiều đợt cấm biển kéo dài vài ngày liên tiếp. Đảo rất vắng và giá lưu trú mềm, nhưng bạn phải chấp nhận rủi ro không ra được đảo. Không nên đặt vé máy bay chỉ để đi Cù Lao Chàm trong giai đoạn này.'),
                array('type' => 'h2', 'id' => 'ket-luan', 'text' => 'IV. Kết luận'),
                array('type' => 'p', 'text' => 'Muốn lặn và tắm biển: chọn tháng 4–8. Muốn đảo vắng và chấp nhận rủi ro thời tiết: tháng 10–2, kèm chính sách đổi lịch linh hoạt.'),
            ),
            'faqs' => array(
                array('q' => 'Đi dịp 30/4 – 1/5 có đông không?', 'a' => 'Rất đông và vé cano thường kín trước cả tuần. Nên đặt sớm và ưu tiên chuyến khởi hành đầu tiên trong ngày.'),
            ),
            'galleryCount' => 5,
        ),
        array(
            'slug' => 'quy-dinh-khong-tui-ni-long-cu-lao-cham',
            'title' => 'Cù Lao Chàm nói không với túi ni lông: những quy tắc du khách cần biết',
            'zoneSlug' => 'bai-lang', 'zone' => 'Làng Bãi Làng',
            'category' => 'Làng Bãi Làng', 'categorySlug' => 'bai-lang',
            'tags' => array('Du lịch xanh', 'Mẹo du lịch'),
            'author' => 'Lan Hương', 'publishedAt' => '18/06/2026', 'updatedAt' => '25/07/2026',
            'views' => 1385, 'rating' => 5.0, 'ratingCount' => 47,
            'excerpt' => 'Từ năm 2009, Cù Lao Chàm là địa phương đầu tiên ở Việt Nam nói không với túi ni lông — đây là những gì bạn nên và không nên mang ra đảo.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cù Lao Chàm được UNESCO công nhận là Khu dự trữ sinh quyển thế giới năm 2009. Cùng năm đó, chính quyền và người dân xã đảo Tân Hiệp bắt đầu chương trình "Nói không với túi ni lông" — mô hình sau này được nhân rộng ra nhiều nơi.'),
                array('type' => 'h2', 'id' => 'nen-mang', 'text' => 'I. Nên mang theo'),
                array('type' => 'ul', 'items' => array(
                    'Túi vải hoặc túi giấy thay cho túi ni lông',
                    'Bình nước cá nhân — trên đảo có điểm tiếp nước',
                    'Kem chống nắng thân thiện với rạn san hô (không oxybenzone)',
                    'Giày đế bám nếu định trek lên hải đăng',
                )),
                array('type' => 'h2', 'id' => 'nen-tranh', 'text' => 'II. Nên tránh'),
                array('type' => 'ul', 'items' => array(
                    'Túi ni lông và hộp xốp mang từ đất liền',
                    'Ống hút nhựa, chai nước dùng một lần',
                    'Chạm, đứng hay bẻ san hô khi snorkel',
                    'Mang vỏ ốc, san hô chết về làm kỷ niệm',
                )),
                array('type' => 'h2', 'id' => 'phi-bao-ton', 'text' => 'III. Phí tham quan khu bảo tồn biển'),
                array('type' => 'p', 'text' => 'Mỗi khách ra đảo đóng phí tham quan khu bảo tồn biển — thường đã gồm trong giá tour trọn gói. Khoản phí này dùng cho công tác bảo tồn rạn san hô và thu gom rác trên đảo, nên hãy giữ lại vé khi được kiểm tra.'),
                array('type' => 'links', 'title' => 'Đi cùng chương trình xanh:', 'links' => array(
                    array('label' => 'Lặn ngắm san hô 1 ngày', 'route' => array('tours.show', array('zone' => 'ran-san-ho', 'slug' => 'lan-ngam-san-ho-cu-lao-cham-1-ngay'))),
                    array('label' => 'Bãi Hương — sống cùng làng chài', 'route' => array('tours.show', array('zone' => 'bai-huong', 'slug' => 'bai-huong-lang-chai-homestay-2n1d'))),
                )),
            ),
            'faqs' => array(
                array('q' => 'Nếu lỡ mang túi ni lông ra đảo thì sao?', 'a' => 'Bạn sẽ được nhắc nhở và hỗ trợ đổi sang túi vải tại bến. Không có phạt tiền với du khách, nhưng đây là quy ước cộng đồng nên hãy tôn trọng.'),
            ),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'meo-lan-ngam-san-ho-cu-lao-cham',
            'title' => 'Mẹo lặn & snorkel Cù Lao Chàm: chọn điểm, thiết bị và quy tắc bảo tồn',
            'zoneSlug' => 'ran-san-ho', 'zone' => 'Rạn san hô & điểm lặn',
            'category' => 'Rạn san hô', 'categorySlug' => 'ran-san-ho',
            'tags' => array('Chơi gì, xem gì?', 'Mẹo du lịch', 'Du lịch xanh'),
            'author' => 'Quốc Bảo', 'publishedAt' => '22/06/2026', 'updatedAt' => '08/07/2026',
            'views' => 1290, 'rating' => 4.9, 'ratingCount' => 43,
            'excerpt' => 'Khu bảo tồn biển Cù Lao Chàm có hơn 165 ha rạn san hô — chọn điểm theo độ trong nước, đừng chạm san hô và luôn đi cùng hướng dẫn.',
            'content' => array(
                array('type' => 'p', 'text' => 'Rạn san hô là lý do chính khiến khách chọn Cù Lao Chàm thay vì các bãi biển đất liền gần Hội An. Khu bảo tồn biển được phân vùng rõ: có vùng bảo vệ nghiêm ngặt không được vào, có vùng cho phép snorkel và lặn có hướng dẫn.'),
                array('type' => 'h2', 'id' => 'chon-diem', 'text' => 'I. Chọn điểm theo ngày, không theo tên'),
                array('type' => 'p', 'text' => 'Hướng dẫn viên thường đổi điểm giữa khu vực Hòn Dài, Hòn Tai và rạn ven Bãi Bắc tuỳ gió và độ trong nước. Đừng cố ép "phải đúng một điểm" — ưu tiên nơi nước trong và an toàn trong ngày bạn đi.'),
                array('type' => 'h2', 'id' => 'thiet-bi', 'text' => 'II. Thiết bị'),
                array('type' => 'ul', 'items' => array(
                    'Mặt nạ vừa khuôn mặt quan trọng hơn mặt nạ đắt tiền',
                    'Áo phao bắt buộc với người bơi chưa vững',
                    'Áo rash guard chống nắng tốt hơn bôi kem dày',
                    'Fun dive cần thẻ chứng chỉ Open Water trở lên',
                )),
                array('type' => 'h2', 'id' => 'bao-ton', 'text' => 'III. Quy tắc bảo tồn'),
                array('type' => 'ul', 'items' => array(
                    'Không chạm, đứng hay bám vào san hô',
                    'Không cho cá ăn và không mang sinh vật biển lên bờ',
                    'Giữ khoảng cách với rạn khi chụp ảnh',
                    'Dùng kem chống nắng không chứa oxybenzone',
                )),
                array('type' => 'links', 'title' => 'Xem tour:', 'links' => array(
                    array('label' => 'Lặn ngắm san hô 1 ngày', 'route' => array('tours.show', array('zone' => 'ran-san-ho', 'slug' => 'lan-ngam-san-ho-cu-lao-cham-1-ngay'))),
                    array('label' => 'Fun dive cho thợ có chứng chỉ', 'route' => array('tours.show', array('zone' => 'ran-san-ho', 'slug' => 'fun-dive-cu-lao-cham-1-ngay'))),
                )),
            ),
            'faqs' => array(
                array('q' => 'Trẻ em snorkel được từ mấy tuổi?', 'a' => 'Thường từ 6–8 tuổi với áo phao và người lớn kèm sát tại vùng nước nông. Một số điểm sâu chỉ dành cho người lớn.'),
            ),
            'galleryCount' => 5,
        ),
        array(
            'slug' => 'ngu-dem-o-cu-lao-cham-o-dau',
            'title' => 'Ngủ đêm ở Cù Lao Chàm: homestay Bãi Làng, bungalow Bãi Chồng hay Bãi Hương?',
            'zoneSlug' => 'bai-huong', 'zone' => 'Bãi Hương',
            'category' => 'Bãi Hương', 'categorySlug' => 'bai-huong',
            'tags' => array('Ở đâu?', 'Mẹo du lịch'),
            'author' => 'Thu Hà', 'publishedAt' => '28/06/2026', 'updatedAt' => '18/07/2026',
            'views' => 1105, 'rating' => 4.8, 'ratingCount' => 39,
            'excerpt' => 'Trên đảo không có khách sạn lớn — chủ yếu là homestay nhà dân, một số bungalow và lều glamping. Mỗi khu vực hợp với một kiểu khách khác nhau.',
            'content' => array(
                array('type' => 'p', 'text' => 'Phần lớn khách tới Cù Lao Chàm chỉ đi trong ngày. Nhưng nếu ngủ lại một đêm, bạn sẽ thấy một hòn đảo hoàn toàn khác sau khi chuyến cano cuối rời bến lúc chiều.'),
                array('type' => 'h2', 'id' => 'bai-lang', 'text' => 'I. Bãi Làng — tiện nhất'),
                array('type' => 'p', 'text' => 'Trung tâm xã đảo: nhiều homestay nhà dân, quán ăn, chợ Tân Hiệp và bến cano. Hợp với người đi lần đầu, muốn dễ ăn uống và dễ tìm dịch vụ.'),
                array('type' => 'h2', 'id' => 'bai-chong', 'text' => 'II. Bãi Chồng — sát biển'),
                array('type' => 'p', 'text' => 'Bungalow và lều glamping ngay sau bãi tắm đẹp nhất đảo. Hợp gia đình và cặp đôi muốn bước ra là xuống nước. Buổi trưa bãi đông khách trong ngày, nhưng sáng sớm và chiều muộn gần như của riêng bạn.'),
                array('type' => 'h2', 'id' => 'bai-huong', 'text' => 'III. Bãi Hương — yên nhất'),
                array('type' => 'p', 'text' => 'Làng chài phía nam, rất ít khách đoàn. Homestay đơn giản, ăn cơm cùng gia đình chủ nhà. Hợp với người muốn trải nghiệm đời sống đảo thật và không ngại tiện nghi cơ bản.'),
                array('type' => 'links', 'title' => 'Tour ngủ đêm:', 'links' => array(
                    array('label' => 'Cù Lao Chàm 2 ngày 1 đêm homestay Bãi Làng', 'route' => array('tours.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-2-ngay-1-dem-bai-lang'))),
                    array('label' => 'Bãi Hương — sống cùng làng chài', 'route' => array('tours.show', array('zone' => 'bai-huong', 'slug' => 'bai-huong-lang-chai-homestay-2n1d'))),
                )),
            ),
            'faqs' => array(
                array('q' => 'Trên đảo có điện và wifi không?', 'a' => 'Có điện lưới qua cáp ngầm và wifi tại hầu hết homestay, nhưng tốc độ chậm vào giờ cao điểm. Sóng di động ổn ở Bãi Làng, yếu hơn ở Bãi Hương.'),
            ),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'an-gi-o-cu-lao-cham',
            'title' => 'Ăn gì ở Cù Lao Chàm? Cua đá, mực một nắng và bữa cơm nhà dân',
            'zoneSlug' => 'bai-lang', 'zone' => 'Làng Bãi Làng',
            'category' => 'Làng Bãi Làng', 'categorySlug' => 'bai-lang',
            'tags' => array('Ăn gì, uống gì?'),
            'author' => 'Minh Trí', 'publishedAt' => '02/07/2026', 'updatedAt' => '19/07/2026',
            'views' => 940, 'rating' => 4.7, 'ratingCount' => 31,
            'excerpt' => 'Hải sản tươi theo con nước, mực một nắng phơi ngay trước nhà và cua đá — đặc sản chỉ được khai thác theo mùa để bảo tồn.',
            'content' => array(
                array('type' => 'p', 'text' => 'Ẩm thực Cù Lao Chàm đơn giản: cá, mực, ốc theo con nước, chế biến ngay trong ngày. Đừng kỳ vọng nhà hàng lớn — cái ngon nằm ở độ tươi và cách nấu của người đảo.'),
                array('type' => 'h2', 'id' => 'mon-nen-thu', 'text' => 'I. Món nên thử'),
                array('type' => 'ul', 'items' => array(
                    'Cua đá Cù Lao Chàm — chỉ khai thác theo mùa và có dán nhãn sinh thái',
                    'Mực một nắng nướng',
                    'Ốc vú nàng luộc hoặc nướng mỡ hành',
                    'Bào ngư, vú nàng theo mùa',
                    'Rau rừng và canh cá nấu lá é',
                )),
                array('type' => 'h2', 'id' => 'an-o-dau', 'text' => 'II. Ăn ở đâu'),
                array('type' => 'p', 'text' => 'Quán ven bến Bãi Làng và chợ Tân Hiệp là nơi tiện nhất cho khách trong ngày. Nếu ngủ lại, bữa cơm do chủ homestay nấu thường ngon và rẻ hơn nhà hàng phục vụ đoàn.'),
                array('type' => 'h2', 'id' => 'luu-y', 'text' => 'III. Lưu ý bảo tồn'),
                array('type' => 'p', 'text' => 'Cua đá được quản lý theo hạn ngạch và mùa vụ; chỉ mua cua có dán nhãn của tổ cộng đồng. Không mua hải sản kích thước quá nhỏ hoặc đang mang trứng.'),
            ),
            'faqs' => array(),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'di-trong-ngay-hay-ngu-dem-cu-lao-cham',
            'title' => 'Cù Lao Chàm: đi trong ngày hay ngủ lại một đêm?',
            'zoneSlug' => 'bai-chong', 'zone' => 'Bãi Chồng',
            'category' => 'Bãi Chồng', 'categorySlug' => 'bai-chong',
            'tags' => array('Chọn tour nào?', 'Mẹo du lịch'),
            'author' => 'Thu Hà', 'publishedAt' => '08/07/2026', 'updatedAt' => '26/07/2026',
            'views' => 1460, 'rating' => 4.8, 'ratingCount' => 45,
            'excerpt' => 'Tour trong ngày tiện và rẻ nhưng đông; ngủ lại một đêm đắt hơn khoảng 1 triệu nhưng đổi lại buổi chiều và sáng sớm gần như chỉ có bạn.',
            'content' => array(
                array('type' => 'p', 'text' => 'Đây là câu hỏi phổ biến nhất khi khách lên lịch từ Hội An. Câu trả lời phụ thuộc vào việc bạn muốn "check-in" hay muốn cảm nhận nhịp đảo.'),
                array('type' => 'h2', 'id' => 'trong-ngay', 'text' => 'I. Tour trong ngày'),
                array('type' => 'ul', 'items' => array(
                    'Ưu: gọn, rẻ, không cần đổi khách sạn ở Hội An',
                    'Ưu: đã gồm cano, phí bảo tồn, bữa trưa và snorkel',
                    'Nhược: Bãi Chồng đông từ 10:00 đến 13:00',
                    'Nhược: chỉ có khoảng 5–6 giờ trên đảo',
                )),
                array('type' => 'h2', 'id' => 'ngu-dem', 'text' => 'II. Ngủ lại một đêm'),
                array('type' => 'ul', 'items' => array(
                    'Ưu: chiều và sáng sớm bãi gần như vắng',
                    'Ưu: có thể câu mực đêm và ngắm sao',
                    'Ưu: snorkel sáng sớm nước trong hơn',
                    'Nhược: tiện nghi cơ bản, wifi chậm',
                )),
                array('type' => 'h2', 'id' => 'goi-y', 'text' => 'III. Gợi ý'),
                array('type' => 'p', 'text' => 'Nếu bạn ở Hội An từ 3 đêm trở lên và đi vào mùa biển êm, hãy dành một đêm trên đảo. Nếu chỉ có hai ngày ở Hội An hoặc đi cùng người lớn tuổi, tour trong ngày là lựa chọn hợp lý hơn.'),
                array('type' => 'links', 'title' => 'So sánh hai lựa chọn:', 'links' => array(
                    array('label' => 'Cù Lao Chàm 1 ngày từ Hội An', 'route' => array('tours.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-1-ngay-tu-hoi-an'))),
                    array('label' => 'Cù Lao Chàm 2 ngày 1 đêm homestay Bãi Làng', 'route' => array('tours.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-2-ngay-1-dem-bai-lang'))),
                )),
            ),
            'faqs' => array(
                array('q' => 'Ngủ lại đảo có tốn nhiều hơn nhiều không?', 'a' => 'Chênh lệch thường khoảng 900.000 – 1.500.000 đồng/người so với tour trong ngày, đã gồm homestay, các bữa ăn và trải nghiệm buổi tối.'),
            ),
            'galleryCount' => 4,
        ),
        array(
            'slug' => 'chi-phi-du-lich-cu-lao-cham',
            'title' => 'Chi phí du lịch Cù Lao Chàm hết bao nhiêu? Bảng dự trù 2026',
            'zoneSlug' => 'ket-hop-hoi-an', 'zone' => 'Kết hợp Hội An / Cửa Đại',
            'category' => 'Hội An (cửa ngõ)', 'categorySlug' => 'hoi-an-cua-ngo',
            'tags' => array('Mẹo du lịch', 'Chọn tour nào?'),
            'author' => 'Lan Hương', 'publishedAt' => '10/06/2026', 'updatedAt' => '21/07/2026',
            'views' => 1830, 'rating' => 4.9, 'ratingCount' => 57,
            'excerpt' => 'Tour trong ngày từ Hội An thường 600.000 – 900.000 đồng/người; ngủ lại một đêm khoảng 1.6 – 2.4 triệu. Dưới đây là bảng dự trù chi tiết.',
            'content' => array(
                array('type' => 'p', 'text' => 'Cù Lao Chàm là một trong những điểm đảo dễ tiếp cận và hợp túi tiền nhất miền Trung, vì phần lớn chi phí chỉ là cano và một bữa trưa. Dưới đây là dự trù tham khảo năm 2026, tính theo mỗi khách.'),
                array('type' => 'h2', 'id' => 'ba-muc', 'text' => 'I. Ba mức chi phí'),
                array('type' => 'ul', 'items' => array(
                    'Tour ghép trong ngày: ~600.000 – 900.000 đồng (cano khứ hồi, phí bảo tồn, trưa, snorkel)',
                    'Ngủ lại 1 đêm: ~1.600.000 – 2.400.000 đồng (thêm homestay/bungalow, bữa tối, câu mực)',
                    'Combo Hội An hoặc Đà Nẵng nhiều ngày: từ ~4.300.000 đồng',
                )),
                array('type' => 'h2', 'id' => 'khoan-le', 'text' => 'II. Các khoản lẻ thường quên'),
                array('type' => 'ul', 'items' => array(
                    'Phí tham quan khu bảo tồn biển (thường đã gồm trong tour)',
                    'Ghế nằm và tắm nước ngọt tại Bãi Chồng',
                    'Thuê xe máy trên đảo nếu tự đi Bãi Hương',
                    'Hải sản gọi thêm ngoài suất trong tour',
                )),
                array('type' => 'h2', 'id' => 'tiet-kiem', 'text' => 'III. Cách tối ưu chi phí'),
                array('type' => 'p', 'text' => 'Đặt cano và lưu trú cùng một đầu mối thường rẻ hơn ghép lẻ vào mùa cao điểm. Đi giữa tuần thay vì cuối tuần cũng giảm giá phòng và bớt đông tại Bãi Chồng.'),
            ),
            'faqs' => array(
                array('q' => 'Giá tour đã gồm phí khu bảo tồn biển chưa?', 'a' => 'Hầu hết tour trọn gói của culaocham.net đã gồm. Nếu bạn chỉ mua vé cano lẻ, hãy hỏi rõ vì đây là khoản thu riêng tại bến.'),
            ),
            'galleryCount' => 4,
        ),
    ),

    'testimonials' => array(
        array('name' => 'Nguyễn Quốc Trung', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => 'Đi trong ngày từ Hội An mà vẫn kịp chùa Hải Tạng, snorkel và nghỉ trưa ở Bãi Chồng — tổ chức rất gọn.', 'photos' => 7, 'trip' => 'Cù Lao Chàm 1 ngày', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Emma Whitfield', 'country' => 'Anh', 'flag' => '🇬🇧', 'rating' => 5.0,
            'quote' => 'A genuine UNESCO biosphere experience — no plastic bags anywhere and our guide explained the reef rules clearly.', 'photos' => 9, 'trip' => 'Snorkel & village day', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Thu Trang', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0,
            'quote' => 'Ngủ homestay Bãi Hương là quyết định đúng nhất — bữa cơm với gia đình chủ nhà nhớ mãi.', 'photos' => 6, 'trip' => 'Bãi Hương 2 ngày 1 đêm', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Hoàng Yến', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.9,
            'quote' => 'Hai con nhỏ vẫn snorkel được nhờ áo phao trẻ em và HDV kèm sát. Bungalow Bãi Chồng sạch sẽ.', 'photos' => 5, 'trip' => 'Bãi Chồng gia đình 2 ngày 1 đêm', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Marc Dubois', 'country' => 'Pháp', 'flag' => '🇫🇷', 'rating' => 4.9,
            'quote' => 'Deux plongées très bien organisées, petit groupe et briefing sérieux. Les récifs sont encore vivants.', 'photos' => 8, 'trip' => 'Fun dive 1 ngày', 'avatar' => NULL, 'photoUrls' => array()),
        array('name' => 'Yuna Park', 'country' => 'Hàn Quốc', 'flag' => '🇰🇷', 'rating' => 4.8,
            'quote' => '호이안에서 30분이면 도착해요. 스노클링 물이 맑고 비닐봉투 없는 섬이라 인상적이었습니다.', 'photos' => 6, 'trip' => 'Lặn ngắm san hô 1 ngày', 'avatar' => NULL, 'photoUrls' => array()),
    ),

    'team' => array(
        array(
            'slug' => 'tran-van-hai', 'name' => 'Trần Văn Hải', 'role' => 'Giám đốc điều hành',
            'bio' => 'Sinh ra tại xã đảo Tân Hiệp, hơn 12 năm làm việc với đội cano, homestay và ban quản lý khu bảo tồn biển Cù Lao Chàm...',
            'phone' => '+84 235 391 6666', 'email' => 'hai.tran@culaocham.dev', 'area' => 'Cù Lao Chàm & Hội An',
            'years_experience' => 12, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 2600, 'stat_tours' => 620, 'stat_awards' => 3, 'is_verified' => true,
            'bio_html' => '<p>Sinh ra và lớn lên tại xã đảo Tân Hiệp, Trần Văn Hải có hơn 12 năm vận hành tour đảo, cano cao tốc và trải nghiệm lặn tại Cù Lao Chàm.</p><p>Anh xây dựng culaocham.net theo hướng du lịch có trách nhiệm — nhóm nhỏ, đối tác là người dân đảo và tuân thủ quy tắc của khu dự trữ sinh quyển.</p>',
            'bio_html_en' => '<p>Born and raised in the island commune of Tan Hiep, Van Hai has 12 years running island tours, speedboats and dive experiences at Cu Lao Cham.</p>',
            'name_en' => 'Tran Van Hai', 'role_en' => 'Chief Executive Officer',
            'short_bio_en' => 'Born on Cu Lao Cham — 12 years connecting speedboats, homestays and dive teams.',
            'achievements' => array('Xây dựng mạng lưới homestay và chủ cano hợp tác trực tiếp trên đảo', 'Tham gia chương trình cộng đồng "Nói không với túi ni lông" từ những năm đầu'),
            'skills' => array(
                array('skill' => 'Vận hành tour đảo & cano', 'percent' => 96),
                array('skill' => 'Quan hệ đối tác địa phương', 'percent' => 93),
                array('skill' => 'Du lịch bền vững & bảo tồn', 'percent' => 94),
            ),
            'experiences' => array(array('title' => 'Giám đốc điều hành', 'company' => 'culaocham.net', 'items' => array('Điều hành chiến lược sản phẩm tour đảo và dịch vụ kết nối'))),
            'degrees' => array(array('title' => 'Cử nhân Quản trị Du lịch', 'school' => 'Đại học Duy Tân, Đà Nẵng', 'items' => array())),
        ),
        array(
            'slug' => 'le-thi-thanh-thao', 'name' => 'Lê Thị Thanh Thảo', 'role' => 'Trưởng phòng thiết kế tour',
            'bio' => 'Thảo thiết kế lịch trình cân bằng giữa phố cổ Hội An và nhịp chậm của đảo — luôn chừa thời gian cho buổi chiều không lịch trình...',
            'phone' => '+84 235 391 6677', 'email' => 'thao.le@culaocham.dev', 'area' => 'Hội An & Cù Lao Chàm',
            'years_experience' => 9, 'languages' => array('Tiếng Việt', 'English'),
            'stat_clients' => 1750, 'stat_tours' => 380, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Lê Thị Thanh Thảo phụ trách thiết kế tour gia đình, combo Hội An — Cù Lao Chàm và các chương trình ngủ đêm trên đảo.</p>',
            'bio_html_en' => '<p>Thanh Thao designs family trips, Hoi An — Cu Lao Cham combos and overnight island programmes.</p>',
            'name_en' => 'Le Thi Thanh Thao', 'role_en' => 'Head of Tour Design',
            'short_bio_en' => 'Balances old-town Hoi An with the slower rhythm of the island.',
            'achievements' => array('Chương trình 2N1Đ ngủ đảo đạt rating trung bình 4.9/5 trong 12 tháng'),
            'skills' => array(array('skill' => 'Thiết kế lịch trình đảo', 'percent' => 95), array('skill' => 'Sản phẩm gia đình & combo', 'percent' => 92)),
            'experiences' => array(array('title' => 'Trưởng phòng thiết kế tour', 'company' => 'culaocham.net', 'items' => array('Phụ trách sản phẩm tour đảo và combo cửa ngõ Hội An / Đà Nẵng'))),
            'degrees' => array(array('title' => 'Cử nhân Việt Nam học — chuyên ngành Du lịch', 'school' => 'Đại học Quảng Nam', 'items' => array())),
        ),
        array(
            'slug' => 'nguyen-quoc-bao', 'name' => 'Nguyễn Quốc Bảo', 'role' => 'Trưởng đội cano & lặn',
            'bio' => 'Xuất thân gia đình ngư dân Bãi Hương, Bảo thuộc từng con nước quanh Hòn Lao và biết khi nào nên đổi điểm lặn...',
            'phone' => '+84 235 391 6688', 'email' => 'bao.nguyen@culaocham.dev', 'area' => 'Khu bảo tồn biển Cù Lao Chàm',
            'years_experience' => 15, 'languages' => array('Tiếng Việt', 'English cơ bản'),
            'stat_clients' => 3100, 'stat_tours' => 780, 'stat_awards' => 2, 'is_verified' => true,
            'bio_html' => '<p>Nguyễn Quốc Bảo điều phối đội cano snorkel và scuba, ưu tiên an toàn khi gió đổi hướng và tuân thủ phân vùng của khu bảo tồn biển.</p>',
            'bio_html_en' => '<p>Quoc Bao coordinates snorkel and scuba boats, prioritising safety and strict compliance with marine protected area zoning.</p>',
            'name_en' => 'Nguyen Quoc Bao', 'role_en' => 'Head of Boat & Dive Operations',
            'short_bio_en' => 'From a Bai Huong fishing family — knows when to move dive sites with the tide.',
            'achievements' => array('Hơn 700 chuyến cano lặn và snorkel vận hành an toàn không sự cố lớn'),
            'skills' => array(array('skill' => 'An toàn hàng hải & lặn', 'percent' => 97), array('skill' => 'Điều hành đội cano', 'percent' => 94)),
            'experiences' => array(array('title' => 'Trưởng đội cano & lặn', 'company' => 'culaocham.net', 'items' => array('Phụ trách thuyền vòng đảo, câu mực đêm và cano lặn rạn'))),
            'degrees' => array(array('title' => 'Chứng chỉ Dive Master / thuyền trưởng phương tiện thuỷ nội địa', 'school' => 'Đối tác đào tạo PADI & Cục Đường thuỷ nội địa', 'items' => array())),
        ),
        array(
            'slug' => 'pham-thu-ha', 'name' => 'Phạm Thu Hà', 'role' => 'Chuyên gia tư vấn cao cấp',
            'bio' => 'Thành thạo tiếng Anh, Hà hỗ trợ khách quốc tế lần đầu ra đảo — từ giờ cano tới việc chọn đi trong ngày hay ngủ lại...',
            'phone' => '+84 235 391 6699', 'email' => 'ha.pham@culaocham.dev', 'area' => 'Hội An & Đà Nẵng',
            'years_experience' => 8, 'languages' => array('Tiếng Việt', 'English', '한국어 cơ bản'),
            'stat_clients' => 1420, 'stat_tours' => 300, 'stat_awards' => 1, 'is_verified' => true,
            'bio_html' => '<p>Phạm Thu Hà tư vấn combo cửa ngõ Hội An / Đà Nẵng và khách quốc tế, phản hồi nhanh trên Zalo và WhatsApp trước giờ cano.</p>',
            'bio_html_en' => '<p>Thu Ha advises first-time visitors and international guests on speedboat timing, beaches and dive options.</p>',
            'name_en' => 'Pham Thu Ha', 'role_en' => 'Senior Travel Consultant',
            'short_bio_en' => 'Helps first-time visitors choose day trips or overnight island stays.',
            'achievements' => array('Đồng hành hơn 300 đoàn khách quốc tế và gia đình (2021–2026)'),
            'skills' => array(array('skill' => 'Tư vấn khách quốc tế', 'percent' => 95), array('skill' => 'Điều phối cano & lưu trú', 'percent' => 92)),
            'experiences' => array(array('title' => 'Chuyên gia tư vấn cao cấp', 'company' => 'culaocham.net', 'items' => array('Phụ trách tư vấn và chăm sóc khách trước chuyến đi'))),
            'degrees' => array(array('title' => 'Cử nhân Ngôn ngữ Anh', 'school' => 'Đại học Ngoại ngữ — Đại học Đà Nẵng', 'items' => array())),
        ),
    ),

    'videos' => array(
        array('title' => 'Một ngày ở Cù Lao Chàm từ Hội An', 'description' => 'Cano Cửa Đại, chùa Hải Tạng và snorkel rạn san hô trong một ngày.', 'date' => '20/07/2026', 'duration' => '09:20', 'tag' => 'Tour 1 ngày',
            'image' => 'https://i.ytimg.com/vi/CLC000000B1/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CLC000000B1?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CLC000000B1'),
        array('title' => 'Rạn san hô trong khu bảo tồn biển Cù Lao Chàm', 'description' => 'San hô mềm, cá rạn và quy tắc lặn có trách nhiệm.', 'date' => '08/07/2026', 'duration' => '11:05', 'tag' => 'Rạn san hô',
            'image' => 'https://i.ytimg.com/vi/CLC000000B2/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CLC000000B2?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CLC000000B2'),
        array('title' => 'Đêm ở Bãi Hương — làng chài phía nam đảo', 'description' => 'Bữa cơm nhà dân, ghe thăm lưới và bình minh trên bãi vắng.', 'date' => '25/06/2026', 'duration' => '07:40', 'tag' => 'Bãi Hương',
            'image' => 'https://i.ytimg.com/vi/CLC000000B3/hqdefault.jpg', 'imageSrcset' => NULL,
            'embedUrl' => 'https://www.youtube.com/embed/CLC000000B3?autoplay=1&rel=0&modestbranding=1&playsinline=1', 'provider' => 'youtube', 'youtubeId' => 'CLC000000B3'),
    ),

    'gallery_albums' => array(
        array('title' => 'Gia đình Whitfield — Snorkel & Bãi Chồng', 'photos' => 21, 'date' => '07/2026'),
        array('title' => 'Đoàn fun dive — rạn san hô Hòn Dài', 'photos' => 27, 'date' => '06/2026'),
        array('title' => 'Trekking hải đăng Hòn Lao lúc bình minh', 'photos' => 18, 'date' => '06/2026'),
    ),

    'usps' => array(
        array('icon' => 'compass', 'sort' => 0,
            'vi' => array('title' => 'am hiểu đảo sinh quyển', 'description' => 'Đội ngũ sinh ra tại xã đảo Tân Hiệp — biết lịch cano, con nước, điểm san hô đẹp và quán ăn thật của người đảo.'),




'en' => array('title' => 'biosphere island expertise', 'description' => 'Our team grew up in Tan Hiep island commune — they know boat schedules, tides, the best reefs and where locals really eat.')),
        array('icon' => 'refund', 'sort' => 1,
            'vi' => array('title' => 'giá rõ ràng, minh bạch', 'description' => 'Báo giá trọn gói đã gồm cano và phí khu bảo tồn biển, không phí ẩn. Đổi lịch linh hoạt khi cano tạm ngưng vì sóng gió.'),




'en' => array('title' => 'clear, transparent pricing', 'description' => 'All-in quotes including the speedboat and marine reserve fee — no hidden charges, with flexible rescheduling when sailings pause.')),
        array('icon' => 'boat', 'sort' => 2,
            'vi' => array('title' => 'nhóm nhỏ, tôn trọng rạn san hô', 'description' => 'Ưu tiên cano nhóm nhỏ và tuân thủ phân vùng khu bảo tồn để giảm áp lực lên rạn.'),




'en' => array('title' => 'small groups, reef-first', 'description' => 'We favour small boat groups and follow marine reserve zoning to keep pressure off the coral.')),
        array('icon' => 'support', 'sort' => 3,
            'vi' => array('title' => 'hỗ trợ trước giờ cano', 'description' => 'Đồng hành từ lúc chọn giờ cano tại Cửa Đại tới khi bạn quay lại phố cổ Hội An.'),




'en' => array('title' => 'support around boat time', 'description' => 'We help from picking a Cua Dai departure through your island day and back to Hoi An.')),
    ),

    'offices' => array(
        array('city' => 'Hội An, Quảng Nam', 'address' => 'Gần bến cano Cửa Đại, phường Cửa Đại, Hội An', 'phone' => '+84 235 391 6666'),
        array('city' => 'Cù Lao Chàm (Bãi Làng)', 'address' => 'Khu vực bến Bãi Làng, xã Tân Hiệp', 'phone' => '+84 235 391 6688'),
    ),

    'values' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is crafted with the care we give our own family')),
        array('vi' => array('name' => 'Am hiểu đảo', 'desc' => 'Sinh ra tại Tân Hiệp — hiểu cano, con nước, bãi và rạn'),



'en' => array('name' => 'Island expertise', 'desc' => 'Born in Tan Hiep — we know boats, tides, beaches and reefs')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn trung thực, giá cả minh bạch'),



'en' => array('name' => 'Sincerity', 'desc' => 'Honest advice and transparent pricing')),
        array('vi' => array('name' => 'Trách nhiệm', 'desc' => 'Giữ gìn khu dự trữ sinh quyển và sinh kế người dân đảo'),



'en' => array('name' => 'Responsibility', 'desc' => 'Protecting the biosphere reserve and island livelihoods')),
    ),
    'value_definitions' => array(
        array('vi' => array('name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân'),



'en' => array('name' => 'Dedication', 'desc' => 'Every trip is crafted with the care we give our own family')),
        array('vi' => array('name' => 'Am hiểu đảo', 'desc' => 'Sinh ra tại Tân Hiệp — hiểu cano, con nước, bãi và rạn'),



'en' => array('name' => 'Island expertise', 'desc' => 'Born in Tan Hiep — we know boats, tides, beaches and reefs')),
        array('vi' => array('name' => 'Chân thành', 'desc' => 'Tư vấn trung thực, giá cả minh bạch'),



'en' => array('name' => 'Sincerity', 'desc' => 'Honest advice and transparent pricing')),
        array('vi' => array('name' => 'Trách nhiệm', 'desc' => 'Giữ gìn khu dự trữ sinh quyển và sinh kế người dân đảo'),



'en' => array('name' => 'Responsibility', 'desc' => 'Protecting the biosphere reserve and island livelihoods')),
    ),

    'reasons' => array(
        array('vi' => array('title' => 'Hướng dẫn viên và chủ cano là người đảo', 'desc' => 'Đội ngũ lớn lên tại Tân Hiệp, hiểu từng điểm san hô và giờ đẹp trên hải đăng.'),



'en' => array('title' => 'Local guides and island boat owners', 'desc' => 'Our team grew up in Tan Hiep — they know every reef and the best light on the lighthouse trail.')),
        array('vi' => array('title' => 'Chính sách rõ khi cano tạm ngưng', 'desc' => 'Đổi ngày miễn phí hoặc hoàn tiền khi biển động buộc dừng chuyến ra đảo.'),



'en' => array('title' => 'Clear rough-sea policy', 'desc' => 'Free rebooking or refunds when sailings to the island are suspended.')),
        array('vi' => array('title' => 'Làm việc trực tiếp với homestay và chủ cano', 'desc' => 'Không qua nhiều tầng trung gian — giá tốt hơn, hỗ trợ nhanh hơn.'),



'en' => array('title' => 'Direct homestay and boat partners', 'desc' => 'Fewer middlemen — better prices and faster support.')),
        array('vi' => array('title' => 'Hỗ trợ trước và sau giờ cano', 'desc' => 'Hotline và Zalo người thật, phản hồi nhanh quanh lịch ra và vào đảo.'),



'en' => array('title' => 'Support around boat schedules', 'desc' => 'Real people on hotline and Zalo, responsive around island departures and returns.')),
        array('vi' => array('title' => 'Du lịch tôn trọng khu dự trữ sinh quyển', 'desc' => 'Không túi ni lông, không chạm san hô, nhóm nhỏ và tuân thủ phân vùng bảo tồn.'),



'en' => array('title' => 'Biosphere-respecting travel', 'desc' => 'No plastic bags, no touching coral, small groups and strict zoning compliance.')),
    ),
    'reason_definitions' => array(
        array('vi' => array('title' => 'Hướng dẫn viên và chủ cano là người đảo', 'desc' => 'Đội ngũ lớn lên tại Tân Hiệp, hiểu từng điểm san hô và giờ đẹp trên hải đăng.'),



'en' => array('title' => 'Local guides and island boat owners', 'desc' => 'Our team grew up in Tan Hiep — they know every reef and the best light on the lighthouse trail.')),
        array('vi' => array('title' => 'Chính sách rõ khi cano tạm ngưng', 'desc' => 'Đổi ngày miễn phí hoặc hoàn tiền khi biển động buộc dừng chuyến ra đảo.'),



'en' => array('title' => 'Clear rough-sea policy', 'desc' => 'Free rebooking or refunds when sailings to the island are suspended.')),
        array('vi' => array('title' => 'Làm việc trực tiếp với homestay và chủ cano', 'desc' => 'Không qua nhiều tầng trung gian — giá tốt hơn, hỗ trợ nhanh hơn.'),



'en' => array('title' => 'Direct homestay and boat partners', 'desc' => 'Fewer middlemen — better prices and faster support.')),
        array('vi' => array('title' => 'Hỗ trợ trước và sau giờ cano', 'desc' => 'Hotline và Zalo người thật, phản hồi nhanh quanh lịch ra và vào đảo.'),



'en' => array('title' => 'Support around boat schedules', 'desc' => 'Real people on hotline and Zalo, responsive around island departures and returns.')),
        array('vi' => array('title' => 'Du lịch tôn trọng khu dự trữ sinh quyển', 'desc' => 'Không túi ni lông, không chạm san hô, nhóm nhỏ và tuân thủ phân vùng bảo tồn.'),



'en' => array('title' => 'Biosphere-respecting travel', 'desc' => 'No plastic bags, no touching coral, small groups and strict zoning compliance.')),
    ),

    'reference_persons' => array(
        array('name' => 'Ms. Emma Whitfield', 'country' => 'Anh', 'email' => 'emma@culaocham.example', 'phone' => '+44 7700 900123', 'skype' => 'emma.whitfield.travel', 'image' => NULL, 'imageSrcset' => NULL),
        array('name' => 'Mr. Marc Dubois', 'country' => 'Pháp', 'email' => 'marc@culaocham.example', 'phone' => '+33 6 12 34 56 78', 'skype' => 'marc.dubois.voyage', 'image' => NULL, 'imageSrcset' => NULL),
        array('name' => 'Ms. Yuna Park', 'country' => 'Hàn Quốc', 'email' => 'yuna@culaocham.example', 'phone' => '+82 10 1234 5678', 'skype' => 'yuna.park.travel', 'image' => NULL, 'imageSrcset' => NULL),
    ),

    'about_page' => array(
        'vi' => array(
            'seo_title' => 'Về chúng tôi — culaocham.net, kết nối du khách với đảo sinh quyển Cù Lao Chàm',
            'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ culaocham.net — thiết kế hành trình và kết nối dịch vụ tại Cù Lao Chàm (Hội An, Quảng Nam).',
            'page_title' => 'Về chúng tôi',
            'page_subtitle' => 'Hành trình chân thật ra đảo sinh quyển — thiết kế bởi người Tân Hiệp',
            'banner' => array('src' => NULL, 'srcset' => NULL, 'alt' => 'Ảnh banner: đội ngũ culaocham.net'),
            'mission' => array('title' => 'Sứ mệnh của chúng tôi',
                'text' => 'Mang đến những hành trình chân thật giúp du khách chạm vào rạn san hô, làng chài và rừng đảo Cù Lao Chàm — đồng thời góp phần giữ gìn Khu dự trữ sinh quyển thế giới và sinh kế của người dân xã đảo Tân Hiệp.',
                'image' => NULL, 'imageSrcset' => NULL),
            'vision' => array('title' => 'Tầm nhìn của chúng tôi',
                'text' => 'Trở thành cầu nối tin cậy nhất giữa du khách và Cù Lao Chàm — nơi mỗi người rời đảo với một thói quen xanh hơn khi mới đến.',
                'image' => NULL, 'imageSrcset' => NULL),
            'sales_policy' => array('title' => 'Chính sách bán hàng minh bạch',
                'content' => 'Mọi báo giá của culaocham.net đều liệt kê rõ từng hạng mục, gồm cả vé cano và phí tham quan khu bảo tồn biển — không phụ phí ẩn. Trẻ em dưới 5 tuổi được miễn phí hầu hết dịch vụ mặt đất; trẻ 5–10 tuổi giảm 25% giá tour. Khi cano bị tạm ngưng do thời tiết, khách được đổi ngày miễn phí hoặc hoàn 100% phần chưa sử dụng.',
                'cta_label' => 'Hỏi thêm về chính sách', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'values_section' => array('title' => 'Cam kết với giá trị cốt lõi', 'hub_label' => 'Giá trị cốt lõi', 'eyebrow' => 'Điều chúng tôi tin',
                'subtitle' => 'Bốn giá trị dẫn dắt mọi lịch trình chúng tôi thiết kế tại Cù Lao Chàm.'),
            'reasons_section' => array('title' => 'Vì sao chọn culaocham.net?', 'eyebrow' => 'Lý do đồng hành',
                'subtitle' => 'Am hiểu đảo, minh bạch và luôn có người bản địa bên bạn.', 'cta_label' => 'Bắt đầu hành trình của bạn', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'reference_section' => array('title' => 'Người đại diện của chúng tôi tại nước ngoài', 'eyebrow' => 'Mạng lưới toàn cầu',
                'subtitle' => 'Bạn có thể trao đổi trực tiếp bằng ngôn ngữ của mình với đại diện culaocham.net tại châu Á và châu Âu.'),
        ),




'en' => array(
            'seo_title' => 'About us — culaocham.net, your complete travel & service hub for Cu Lao Cham',
            'seo_description' => 'Our story, mission and team at culaocham.net — designing journeys and connecting services across Cu Lao Cham (Hoi An, Quang Nam).',
            'page_title' => 'About us',
            'page_subtitle' => 'Authentic journeys to a UNESCO biosphere island — designed by Tan Hiep locals',
            'banner' => array('src' => NULL, 'srcset' => NULL, 'alt' => 'culaocham.net team banner'),
            'mission' => array('title' => 'Our mission',
                'text' => 'To deliver authentic journeys that let travellers touch Cu Lao Cham’s reefs, fishing villages and island forest — while helping protect the UNESCO Biosphere Reserve and the livelihoods of the Tan Hiep island community.',
                'image' => NULL, 'imageSrcset' => NULL),
            'vision' => array('title' => 'Our vision',
                'text' => 'To become the most trusted bridge between travellers and Cu Lao Cham — where every guest leaves the island a little greener than they arrived.',
                'image' => NULL, 'imageSrcset' => NULL),
            'sales_policy' => array('title' => 'Transparent sales policy',
                'content' => 'Every culaocham.net quote lists each line item clearly, including the speedboat ticket and the marine protected area fee — no hidden charges. Children under 5 travel free on most ground services; ages 5–10 get 25% off. When sailings are suspended for weather, guests may rebook free of charge or receive a full refund for unused services.',
                'cta_label' => 'Ask about our policy', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'values_section' => array('title' => 'Commitment to our core values', 'hub_label' => 'Core values', 'eyebrow' => 'What we believe',
                'subtitle' => 'Four values that guide every itinerary we design at Cu Lao Cham.'),
            'reasons_section' => array('title' => 'Why choose culaocham.net?', 'eyebrow' => 'Why travel with us',
                'subtitle' => 'Deep island knowledge, clear pricing, and local people by your side.', 'cta_label' => 'Start your journey', 'cta_url' => NULL, 'image' => NULL, 'imageSrcset' => NULL),
            'reference_section' => array('title' => 'Our representatives abroad', 'eyebrow' => 'A global network',
                'subtitle' => 'Speak directly in your own language with culaocham.net representatives across Asia and Europe.'),
        ),
    ),

    'hero_pills' => array(
        array('zone_slug' => 'ran-san-ho', 'vi' => array('label' => 'Rạn san hô'),



'en' => array('label' => 'Coral reefs'), 'url' => '/diem-den/ran-san-ho'),
        array('zone_slug' => 'ket-hop-hoi-an', 'vi' => array('label' => 'Kết hợp Hội An'),



'en' => array('label' => 'Combined with Hoi An'), 'url' => '/diem-den/ket-hop-hoi-an'),
    ),

    'home_sections' => array(
        'company_intro' => array(
            'vi' => array('key' => 'company_intro', 'eyebrow' => 'Chuyên gia Cù Lao Chàm', 'title' => 'Hành trình chân thật, thiết kế bởi người đảo', 'subtitle' => NULL,
                'body' => 'culaocham.net là đơn vị lữ hành đặt trụ sở tại Hội An và có đội ngũ sinh sống trên xã đảo Tân Hiệp, kết nối du khách với Bãi Làng, Bãi Chồng, Bãi Hương, hải đăng Hòn Lao và khu bảo tồn biển. Chúng tôi không bán tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của người sống cùng nhịp đảo.',
                'metaLine' => 'Giấy phép kinh doanh dịch vụ lữ hành số 0217/2020/TCDL-GPLHQT', 'ctaLabel' => 'Tìm hiểu về chúng tôi', 'ctaUrl' => '/ve-chung-toi', 'image' => NULL, 'imageAlt' => 'Ảnh đội ngũ culaocham.net'),




'en' => array('key' => 'company_intro', 'eyebrow' => 'Cu Lao Cham experts', 'title' => 'Authentic journeys, designed by islanders', 'subtitle' => NULL,
                'body' => 'culaocham.net is a Hoi An-based travel agency with a team living in the Tan Hiep island commune, connecting guests with Bai Lang, Bai Chong, Bai Huong, the Hon Lao lighthouse and the marine protected area. We do not sell off-the-shelf packages — every itinerary is tailored from real, on-the-ground experience.',
                'metaLine' => 'Travel service license No. 0217/2020/TCDL-GPLHQT', 'ctaLabel' => 'Learn about us', 'ctaUrl' => '/ve-chung-toi', 'image' => NULL, 'imageAlt' => 'culaocham.net team'),
        ),
        'featured_tours' => array(
            'vi' => array('key' => 'featured_tours', 'eyebrow' => 'Được yêu thích nhất', 'title' => 'Những tour được yêu cầu nhiều nhất', 'subtitle' => 'Những hành trình khách hàng đặt và đánh giá cao nhất trong 12 tháng qua.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'featured_tours', 'eyebrow' => 'Most popular', 'title' => 'Our most requested tours', 'subtitle' => 'Itineraries our guests book and rate highest over the past 12 months.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'featured_cruises' => array(
            'vi' => array('key' => 'featured_cruises', 'eyebrow' => 'Hành trình trên biển', 'title' => 'Thuyền vòng đảo, câu mực đêm & cano lặn rạn', 'subtitle' => 'Những trải nghiệm trên mặt nước được yêu thích nhất — nơi việc ra khơi trở thành một phần của chuyến đi.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'featured_cruises', 'eyebrow' => 'On the water', 'title' => 'Island loops, night squid fishing & reef dive boats', 'subtitle' => 'Our most loved water experiences — where getting offshore becomes part of the trip.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'featured_trains' => array(
            'vi' => array('key' => 'featured_trains', 'eyebrow' => 'Ra đảo dễ dàng', 'title' => 'Vé cano cao tốc Cửa Đại — Cù Lao Chàm', 'subtitle' => 'Cano khoảng 30 phút từ bến Cửa Đại, tàu gỗ chợ cho khách ngủ lại — giữ chỗ trước khi ra bến, đặc biệt mùa cao điểm.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'featured_trains', 'eyebrow' => 'Easy island access', 'title' => 'Speedboat tickets Cua Dai — Cu Lao Cham', 'subtitle' => 'About 30 minutes from Cua Dai pier, plus the slow local wooden boat for overnight guests — reserve before you reach the pier in high season.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),

        'support_services' => array(
            'vi' => array('key' => 'support_services', 'eyebrow' => 'Dịch vụ bổ trợ', 'title' => 'Chỉ chọn những gì bạn cần', 'subtitle' => 'Lưu trú, vui chơi và dịch vụ hỗ trợ trên đảo cũng như tại Hội An — linh hoạt đặt riêng theo kế hoạch của bạn.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'support_services', 'eyebrow' => 'Add-on services', 'title' => 'Choose only what you need', 'subtitle' => 'Stays, activities and support services on the island and in Hoi An — book à la carte around your plan.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'destinations' => array(
            'vi' => array('key' => 'destinations', 'eyebrow' => 'Khắp Cù Lao Chàm', 'title' => 'Những điểm đến được yêu thích nhất', 'subtitle' => 'Từ làng Bãi Làng tới Bãi Chồng, Bãi Xếp, Bãi Hương, hải đăng và rạn san hô — chọn nơi bạn muốn khám phá.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'destinations', 'eyebrow' => 'Across Cu Lao Cham', 'title' => 'Our most loved destinations', 'subtitle' => 'From Bai Lang village to Bai Chong, Bai Xep, Bai Huong, the lighthouse and the coral reefs — choose where to explore.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'testimonials' => array(
            'vi' => array('key' => 'testimonials', 'eyebrow' => 'Khách hàng kể lại', 'title' => 'Trải nghiệm chân thật từ khách hàng', 'subtitle' => 'Hàng nghìn du khách đã ra đảo cùng chúng tôi — đây là những gì họ kể lại.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Xem tất cả cảm nhận', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'testimonials', 'eyebrow' => 'Guest stories', 'title' => 'Real experiences from our travellers', 'subtitle' => 'Thousands of guests have crossed to the island with us — here is what they say.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Read all reviews', 'ctaUrl' => '/cam-nhan-khach-hang', 'image' => NULL, 'imageAlt' => NULL),
        ),
        'review_platforms' => array(
            'vi' => array('key' => 'review_platforms', 'eyebrow' => NULL, 'title' => 'culaocham.net được đánh giá cao trên', 'subtitle' => NULL, 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'review_platforms', 'eyebrow' => NULL, 'title' => 'culaocham.net is highly rated on', 'subtitle' => NULL, 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),
        'team' => array(
            'vi' => array('key' => 'team', 'eyebrow' => 'Con người culaocham.net', 'title' => 'Đội ngũ tận tâm của chúng tôi', 'subtitle' => 'Những con người sống trên đảo và tại Hội An — đồng hành từ lúc lên ý tưởng tới khi bạn quay về đất liền.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Gặp gỡ cả đội ngũ', 'ctaUrl' => '/doi-ngu', 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'team', 'eyebrow' => 'The culaocham.net team', 'title' => 'Our dedicated local experts', 'subtitle' => 'People who live on the island and in Hoi An — with you from the first idea until you are back on the mainland.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Meet the full team', 'ctaUrl' => '/doi-ngu', 'image' => NULL, 'imageAlt' => NULL),
        ),
        'videos' => array(
            'vi' => array('key' => 'videos', 'eyebrow' => 'Trải nghiệm thật', 'title' => 'Cù Lao Chàm qua từng thước phim đẹp', 'subtitle' => 'Video chân thật do khách hàng và đội ngũ culaocham.net ghi lại.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'Xem tất cả video', 'ctaUrl' => '/video-trai-nghiem', 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'videos', 'eyebrow' => 'Real experiences', 'title' => 'Cu Lao Cham in unforgettable frames', 'subtitle' => 'Authentic films from guests and our local team.', 'body' => NULL, 'metaLine' => NULL, 'ctaLabel' => 'View all videos', 'ctaUrl' => '/video-trai-nghiem', 'image' => NULL, 'imageAlt' => NULL),
        ),
        'quick_inquiry' => array(
            'vi' => array('key' => 'quick_inquiry', 'eyebrow' => 'Tư vấn miễn phí', 'title' => 'Gửi lời nhắn cho chúng tôi', 'subtitle' => NULL,
                'body' => 'Bạn chưa chắc nên đi trong ngày hay ngủ lại, mùa nào cano chạy đều, ở Bãi Làng hay Bãi Hương, ngân sách bao nhiêu? Để lại lời nhắn — đội ngũ culaocham.net sẽ phản hồi trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>, hoàn toàn miễn phí.', 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),




'en' => array('key' => 'quick_inquiry', 'eyebrow' => 'Free advice', 'title' => 'Send us a message', 'subtitle' => NULL,
                'body' => 'Not sure between a day trip or an overnight stay, which season the boats run, Bai Lang or Bai Huong, or your budget? Leave a note — the culaocham.net team will reply within <strong class="font-semibold text-ink">1 business day</strong>, free of charge.', 'metaLine' => NULL, 'ctaLabel' => NULL, 'ctaUrl' => NULL, 'image' => NULL, 'imageAlt' => NULL),
        ),

    ),

    'footer_columns' => array(
        array('title' => 'culaocham.net', 'links' => array(
            array('label' => 'Về chúng tôi', 'route' => array('about')),
            array('label' => 'Cảm nhận khách hàng', 'route' => array('reviews')),
            array('label' => 'Đội ngũ của chúng tôi', 'route' => array('team')),
            array('label' => 'Thư viện khoảnh khắc', 'route' => array('gallery')),
            array('label' => 'Nhận báo giá miễn phí', 'route' => array('customize')),
        )),
        array('title' => 'Tour được yêu thích', 'links' => array(
            array('label' => 'Cù Lao Chàm 1 ngày từ Hội An', 'route' => array('tours.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-1-ngay-tu-hoi-an'))),
            array('label' => 'Cù Lao Chàm 2 ngày 1 đêm', 'route' => array('tours.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-2-ngay-1-dem-bai-lang'))),
            array('label' => 'Lặn ngắm san hô 1 ngày', 'route' => array('tours.show', array('zone' => 'ran-san-ho', 'slug' => 'lan-ngam-san-ho-cu-lao-cham-1-ngay'))),
            array('label' => 'Bãi Chồng gia đình 2 ngày 1 đêm', 'route' => array('tours.show', array('zone' => 'bai-chong', 'slug' => 'bai-chong-gia-dinh-2-ngay'))),
            array('label' => 'Trekking hải đăng Hòn Lao', 'route' => array('tours.show', array('zone' => 'hai-dang', 'slug' => 'trekking-hai-dang-hon-lao-nua-ngay'))),
            array('label' => 'Combo Hội An — Cù Lao Chàm', 'route' => array('tours.show', array('zone' => 'ket-hop-hoi-an', 'slug' => 'hoi-an-cu-lao-cham-3n2d'))),
        )),
        array('title' => 'Điểm đến nổi bật', 'links' => array(
            array('label' => 'Rạn san hô', 'route' => array('cruises.index', array('type' => 'thuyen-lan-san-ho'))),
            array('label' => 'Bãi Chồng', 'route' => array('guide.zone', array('zone' => 'bai-chong'))),
            array('label' => 'Bãi Hương', 'route' => array('guide.zone', array('zone' => 'bai-huong'))),
            array('label' => 'Bãi Xếp', 'route' => array('tours.show', array('zone' => 'bai-xep', 'slug' => 'bai-xep-kayak-snorkel-nua-ngay'))),
        )),
        array('title' => 'Cẩm nang du lịch', 'links' => array(
            array('label' => 'Đi Cù Lao Chàm thế nào?', 'route' => array('guide.show', array('zone' => 'ket-hop-hoi-an', 'slug' => 'di-cu-lao-cham-the-nao-tu-hoi-an-da-nang'))),
            array('label' => 'Cù Lao Chàm mùa nào đẹp nhất?', 'route' => array('guide.show', array('zone' => 'bai-lang', 'slug' => 'cu-lao-cham-mua-nao-dep-nhat'))),
            array('label' => 'Đảo nói không với túi ni lông', 'route' => array('guide.show', array('zone' => 'bai-lang', 'slug' => 'quy-dinh-khong-tui-ni-long-cu-lao-cham'))),
            array('label' => 'Chi phí du lịch Cù Lao Chàm', 'route' => array('guide.show', array('zone' => 'ket-hop-hoi-an', 'slug' => 'chi-phi-du-lich-cu-lao-cham'))),
        )),
    ),

    'footer_seo_links' => array(
        array('label' => 'Cẩm nang du lịch Cù Lao Chàm', 'route' => array('guide.zone', array('zone' => 'bai-lang'))),
        array('label' => 'Cẩm nang rạn san hô', 'route' => array('guide.zone', array('zone' => 'ran-san-ho'))),
        array('label' => 'Tour Cù Lao Chàm trọn gói', 'route' => array('tours.index', array('zone' => 'bai-lang'))),
        array('label' => 'Thuyền vòng đảo', 'route' => array('cruises.index', array('type' => 'thuyen-vong-dao'))),
        array('label' => 'Cano lặn rạn san hô', 'route' => array('cruises.index', array('type' => 'thuyen-lan-san-ho'))),
        array('label' => 'Thiết kế tour riêng', 'route' => array('customize')),
        array('label' => 'Video trải nghiệm', 'route' => array('videos')),
    ),

    'tour_categories' => array(
        /* ── DANH MỤC (type=region) — khu vực GEO & combo, không chia theo số ngày ── */
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'tour-bai-lang-trung-tam-dao',
            'type' => 'region',
            'sort' => 0,
            'packageSlugs' => array(
                'cu-lao-cham-1-ngay-tu-hoi-an',
                'lang-bai-lang-chua-hai-tang-nua-ngay',
                'cu-lao-cham-2-ngay-1-dem-bai-lang',
            ),
            'name' => array('vi' => 'Tour Bãi Làng & trung tâm đảo', 'en' => 'Bai Lang & island centre tours'),
            'subtitle' => array('vi' => 'Bến cano, chợ Tân Hiệp, chùa Hải Tạng 300 năm và giếng cổ Chăm — trung tâm hành chính của xã đảo.', 'en' => 'The speedboat pier, Tan Hiep market, the 300-year-old Hai Tang pagoda and the ancient Cham well.'),
            'seo_body' => array('vi' => 'Danh mục theo khu vực: mọi chương trình cập bến và lưu trú tại Bãi Làng, gồm cả tour trong ngày.', 'en' => 'GEO category: every programme landing and staying at Bai Lang, day trips included.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-chong',
            'slug' => 'tour-bai-chong',
            'type' => 'region',
            'sort' => 1,
            'packageSlugs' => array(
                'bai-chong-nghi-bien-1-ngay',
                'bai-chong-gia-dinh-2-ngay',
                'cu-lao-cham-1-ngay-tu-hoi-an',
            ),
            'name' => array('vi' => 'Tour Bãi Chồng', 'en' => 'Bai Chong beach tours'),
            'subtitle' => array('vi' => 'Bãi tắm biểu tượng của đảo — nước trong, cát mịn, ghế nằm dưới hàng dừa và hải sản nướng.', 'en' => 'The island’s signature beach — clear water, fine sand, loungers under the palms and grilled seafood.'),
            'seo_body' => array('vi' => 'Danh mục khu vực Bãi Chồng — điểm dừng nghỉ biển của gần như mọi tour ra Cù Lao Chàm.', 'en' => 'The Bai Chong GEO category — the beach stop on almost every Cu Lao Cham tour.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-xep',
            'slug' => 'tour-bai-xep',
            'type' => 'region',
            'sort' => 2,
            'packageSlugs' => array(
                'bai-xep-kayak-snorkel-nua-ngay',
                'cam-trai-bai-xep-2n1d',
            ),
            'name' => array('vi' => 'Tour Bãi Xếp', 'en' => 'Bai Xep cove tours'),
            'subtitle' => array('vi' => 'Vịnh nhỏ kín gió, mặt nước phẳng gần như cả ngày — kayak, SUP và bãi dựng lều.', 'en' => 'A sheltered little cove with near-flat water all day — kayaking, SUP and a campsite.'),
            'seo_body' => array('vi' => 'Danh mục khu vực Bãi Xếp — nơi duy nhất trên đảo được phép cắm trại qua đêm.', 'en' => 'The Bai Xep GEO category — the only place on the island where overnight camping is allowed.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-huong',
            'slug' => 'tour-bai-huong',
            'type' => 'region',
            'sort' => 3,
            'packageSlugs' => array(
                'bai-huong-lang-chai-homestay-2n1d',
                'bai-huong-cau-ca-cung-ngu-dan-nua-ngay',
            ),
            'name' => array('vi' => 'Tour Bãi Hương', 'en' => 'Bai Huong tours'),
            'subtitle' => array('vi' => 'Làng chài phía nam đảo, gần như không có khách trong ngày — homestay và nhịp sống thật.', 'en' => 'The southern fishing hamlet with almost no day visitors — homestays and everyday island life.'),
            'seo_body' => array('vi' => 'Danh mục khu vực Bãi Hương — điểm ngủ lại yên tĩnh nhất Cù Lao Chàm.', 'en' => 'The Bai Huong GEO category — the quietest overnight base on Cu Lao Cham.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ran-san-ho',
            'slug' => 'tour-ran-san-ho-diem-lan',
            'type' => 'region',
            'sort' => 4,
            'packageSlugs' => array(
                'lan-ngam-san-ho-cu-lao-cham-1-ngay',
                'fun-dive-cu-lao-cham-1-ngay',
                'snorkel-nua-ngay-ran-san-ho',
                'cu-lao-cham-1-ngay-tu-hoi-an',
                'hoi-an-cu-lao-cham-3n2d',
            ),
            'name' => array('vi' => 'Tour rạn san hô & điểm lặn', 'en' => 'Coral reef & dive site tours'),
            'subtitle' => array('vi' => 'Hơn 165 ha rạn được bảo vệ quanh Hòn Lao — snorkel nước nông và điểm scuba cho thợ có chứng chỉ.', 'en' => 'Over 165 hectares of protected reef around Hon Lao — shallow snorkelling and certified scuba sites.'),
            'seo_body' => array('vi' => 'Danh mục khu vực khu bảo tồn biển — lý do chính khiến khách vượt biển ra đảo.', 'en' => 'The marine protected area GEO category — the main reason travellers cross to the island.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'hai-dang',
            'slug' => 'tour-hai-dang-hon-lao',
            'type' => 'region',
            'sort' => 5,
            'packageSlugs' => array(
                'trekking-hai-dang-hon-lao-nua-ngay',
            ),
            'name' => array('vi' => 'Tour hải đăng Hòn Lao', 'en' => 'Hon Lao lighthouse tours'),
            'subtitle' => array('vi' => 'Đường rừng lên đỉnh và toàn cảnh tám hòn đảo — hoạt động trên cạn đáng giá nhất của đảo.', 'en' => 'A forest trail to the summit and the panorama over all eight islands — the island’s best land activity.'),
            'seo_body' => array('vi' => 'Danh mục khu vực hải đăng — nên đi buổi sáng sớm để tránh nắng và có tầm nhìn xa.', 'en' => 'The lighthouse GEO category — go early for cooler air and long views.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'ket-hop-hoi-an',
            'slug' => 'combo-hoi-an-cua-dai',
            'type' => 'region',
            'sort' => 6,
            'packageSlugs' => array(
                'hoi-an-cu-lao-cham-3n2d',
                'da-nang-hoi-an-cu-lao-cham-4n3d',
            ),
            'name' => array('vi' => 'Combo Hội An / Cửa Đại', 'en' => 'Hoi An / Cua Dai combos'),
            'subtitle' => array('vi' => 'Cửa ngõ đất liền — phố cổ Hội An, bến cano Cửa Đại và sân bay Đà Nẵng trong cùng hành trình.', 'en' => 'The mainland gateway — Hoi An old town, Cua Dai pier and Da Nang airport in one itinerary.'),
            'seo_body' => array('vi' => 'Danh mục combo đất liền — đảo: gom các chương trình có cả phố cổ và Cù Lao Chàm.', 'en' => 'The mainland-and-island combo category, grouping itineraries with both the old town and Cu Lao Cham.'),
            'faqs' => array(),
        ),

        /* ── CHỦ ĐỀ (A) type=theme — thời lượng chương trình, gắn hub zone ── */
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'tour-trong-ngay',
            'type' => 'theme',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'packageSlugs' => array(
                'cu-lao-cham-1-ngay-tu-hoi-an',
                'lang-bai-lang-chua-hai-tang-nua-ngay',
                'bai-chong-nghi-bien-1-ngay',
                'bai-xep-kayak-snorkel-nua-ngay',
                'bai-huong-cau-ca-cung-ngu-dan-nua-ngay',
                'lan-ngam-san-ho-cu-lao-cham-1-ngay',
                'fun-dive-cu-lao-cham-1-ngay',
                'snorkel-nua-ngay-ran-san-ho',
                'trekking-hai-dang-hon-lao-nua-ngay',
            ),
            'name' => array('vi' => 'Tour trong ngày', 'en' => 'Day tours'),
            'subtitle' => array('vi' => 'Trọn vẹn trong ngày — không qua đêm tại Cù Lao Chàm.', 'en' => 'A full day — no overnight in Cu Lao Cham.'),
            'seo_body' => array('vi' => 'Chủ đề theo thời lượng chương trình — khác danh mục theo khu vực / combo.', 'en' => 'Program-duration theme — distinct from GEO/combo category pages.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'tour-2-ngay-1-dem',
            'type' => 'theme',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 2,
            'packageSlugs' => array(
                'cu-lao-cham-2-ngay-1-dem-bai-lang',
                'bai-chong-gia-dinh-2-ngay',
                'cam-trai-bai-xep-2n1d',
                'bai-huong-lang-chai-homestay-2n1d',
            ),
            'name' => array('vi' => 'Tour 2 ngày 1 đêm', 'en' => '2 days 1 night'),
            'subtitle' => array('vi' => 'Cuối tuần ngắn — một đêm nghỉ tại Cù Lao Chàm.', 'en' => 'Short weekend — one overnight in Cu Lao Cham.'),
            'seo_body' => array('vi' => 'Chủ đề theo thời lượng chương trình — khác danh mục theo khu vực / combo.', 'en' => 'Program-duration theme — distinct from GEO/combo category pages.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'tour-3-ngay-2-dem',
            'type' => 'theme',
            'sort' => 2,
            'minDays' => 3,
            'maxDays' => 3,
            'packageSlugs' => array(
                'hoi-an-cu-lao-cham-3n2d',
            ),
            'name' => array('vi' => 'Tour 3 ngày 2 đêm', 'en' => '3 days 2 nights'),
            'subtitle' => array('vi' => 'Khám phá vừa đủ — hai đêm tại Cù Lao Chàm.', 'en' => 'Enough depth — two nights in Cu Lao Cham.'),
            'seo_body' => array('vi' => 'Chủ đề theo thời lượng chương trình — khác danh mục theo khu vực / combo.', 'en' => 'Program-duration theme — distinct from GEO/combo category pages.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'tour-4-ngay-3-dem',
            'type' => 'theme',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 4,
            'packageSlugs' => array(
                'da-nang-hoi-an-cu-lao-cham-4n3d',
            ),
            'name' => array('vi' => 'Tour 4 ngày 3 đêm', 'en' => '4 days 3 nights'),
            'subtitle' => array('vi' => 'Khám phá sâu hơn — ba đêm trải nghiệm Cù Lao Chàm.', 'en' => 'Deeper exploration — three nights in Cu Lao Cham.'),
            'seo_body' => array('vi' => 'Chủ đề theo thời lượng chương trình — khác danh mục theo khu vực / combo.', 'en' => 'Program-duration theme — distinct from GEO/combo category pages.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'tour-tu-5-ngay',
            'type' => 'theme',
            'sort' => 4,
            'minDays' => 5,
            'maxDays' => null,
            'packageSlugs' => array(),
            'name' => array('vi' => 'Tour từ 5 ngày', 'en' => '5+ day tours'),
            'subtitle' => array('vi' => 'Tour dài ngày và combo nhiều điểm đến từ Cù Lao Chàm.', 'en' => 'Extended tours and multi-stop combos from Cu Lao Cham.'),
            'seo_body' => array('vi' => 'Chủ đề theo thời lượng chương trình — khác danh mục theo khu vực / combo.', 'en' => 'Program-duration theme — distinct from GEO/combo category pages.'),
            'faqs' => array(),
        ),

        /* ── CHỦ ĐỀ (B) type=theme — tính chất / insight, KHÔNG clone tên zone GEO ── */
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'lan-bien-san-ho',
            'type' => 'theme',
            'sort' => 10,
            'packageSlugs' => array(
                'cu-lao-cham-1-ngay-tu-hoi-an',
                'bai-xep-kayak-snorkel-nua-ngay',
                'lan-ngam-san-ho-cu-lao-cham-1-ngay',
                'fun-dive-cu-lao-cham-1-ngay',
                'snorkel-nua-ngay-ran-san-ho',
                'hoi-an-cu-lao-cham-3n2d',
                'cano-lan-ran-san-ho-cu-lao-cham',
            ),
            'name' => array('vi' => 'Lặn biển & ngắm san hô', 'en' => 'Diving & snorkelling'),
            'subtitle' => array('vi' => 'Từ snorkel nửa ngày cho người mới tới fun dive hai bình trong khu bảo tồn biển.', 'en' => 'From a beginner half-day snorkel to two-tank fun dives inside the marine protected area.'),
            'seo_body' => array('vi' => 'Chủ đề theo hoạt động dưới nước — dùng chung tour với danh mục khu vực rạn san hô.', 'en' => 'A water-activity theme sharing tours with the coral reef GEO category.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'van-hoa-lang-chai',
            'type' => 'theme',
            'sort' => 11,
            'packageSlugs' => array(
                'lang-bai-lang-chua-hai-tang-nua-ngay',
                'cu-lao-cham-2-ngay-1-dem-bai-lang',
                'bai-huong-lang-chai-homestay-2n1d',
                'bai-huong-cau-ca-cung-ngu-dan-nua-ngay',
                'thuyen-cau-muc-dem-bai-lang',
            ),
            'name' => array('vi' => 'Văn hoá & làng chài', 'en' => 'Culture & fishing villages'),
            'subtitle' => array('vi' => 'Chùa Hải Tạng, giếng cổ Chăm, nghề yến sào và bữa cơm cùng gia đình ngư dân.', 'en' => 'Hai Tang pagoda, the ancient Cham well, the swiftlet-nest trade and meals with fishing families.'),
            'seo_body' => array('vi' => 'Chủ đề văn hoá — phần khiến Cù Lao Chàm khác một hòn đảo nghỉ dưỡng thuần tuý.', 'en' => 'A cultural theme — what sets Cu Lao Cham apart from a purely resort island.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'trekking-ngam-canh',
            'type' => 'theme',
            'sort' => 12,
            'packageSlugs' => array(
                'cam-trai-bai-xep-2n1d',
                'trekking-hai-dang-hon-lao-nua-ngay',
            ),
            'name' => array('vi' => 'Trekking & ngắm cảnh', 'en' => 'Trekking & viewpoints'),
            'subtitle' => array('vi' => 'Đường rừng lên hải đăng, mỏm đá ngắm vịnh và đêm ngủ lều nghe sóng.', 'en' => 'The forest trail to the lighthouse, clifftop viewpoints and a night in a tent by the surf.'),
            'seo_body' => array('vi' => 'Chủ đề hoạt động trên cạn — lọc theo tính chất chương trình, không theo khu vực.', 'en' => 'A land-activity theme, filtered by programme character rather than by area.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'gia-dinh-bien-dao',
            'type' => 'theme',
            'sort' => 13,
            'packageSlugs' => array(
                'cu-lao-cham-1-ngay-tu-hoi-an',
                'bai-chong-nghi-bien-1-ngay',
                'bai-chong-gia-dinh-2-ngay',
                'snorkel-nua-ngay-ran-san-ho',
            ),
            'name' => array('vi' => 'Gia đình & biển đảo', 'en' => 'Family beach & island'),
            'subtitle' => array('vi' => 'Cano lớn có mái che, bãi tắm nông và snorkel ngay bờ — trẻ từ 6 tuổi tham gia được.', 'en' => 'Larger covered speedboats, shallow beaches and snorkelling right off the sand — fine from age six.'),
            'seo_body' => array('vi' => 'Phân khúc gia đình — lọc theo nhu cầu khách, dùng chung tour với các danh mục khu vực.', 'en' => 'The family segment — a needs-based filter sharing tours with the GEO categories.'),
            'faqs' => array(),
        ),
        array(
            'zoneSlug' => 'bai-lang',
            'slug' => 'cuoi-tuan-da-nang-hoi-an',
            'type' => 'theme',
            'sort' => 14,
            'packageSlugs' => array(
                'cu-lao-cham-1-ngay-tu-hoi-an',
                'cu-lao-cham-2-ngay-1-dem-bai-lang',
                'hoi-an-cu-lao-cham-3n2d',
                'da-nang-hoi-an-cu-lao-cham-4n3d',
            ),
            'name' => array('vi' => 'Cuối tuần từ Đà Nẵng & Hội An', 'en' => 'Weekend from Da Nang & Hoi An'),
            'subtitle' => array('vi' => 'Cano 30 phút từ Cửa Đại — đi về trong ngày hoặc thêm một đêm trên đảo sau khi đoàn khách ngày rời bến.', 'en' => 'A 30-minute speedboat from Cua Dai — same-day return or one extra night once the day boats leave.'),
            'seo_body' => array('vi' => 'Chủ đề theo thị trường nguồn đất liền, không phải trang danh mục combo Hội An.', 'en' => 'A mainland source-market theme, not the Hoi An combo category page.'),
            'faqs' => array(),
        ),
    ),

    'listing_faqs' => array(
        array('q' => 'Cù Lao Chàm mùa nào đẹp nhất để đi tour trọn gói?', 'a' => 'Khoảng tháng 3 đến tháng 8 biển êm, nước trong và cano chạy đều mỗi ngày. Từ tháng 10 đến tháng 2 gió mùa Đông Bắc mạnh, nhiều ngày cấm biển — nếu đi giai đoạn này hãy chọn tour có chính sách đổi lịch linh hoạt.'),
        array('q' => 'Tour trọn gói đã bao gồm vé cano và phí khu bảo tồn biển chưa?', 'a' => 'Hầu hết tour của culaocham.net đã gồm cano khứ hồi Cửa Đại — Bãi Làng và phí tham quan khu bảo tồn biển. Mục inclusions của từng tour luôn ghi rõ.'),
        array('q' => 'Nên đi trong ngày hay ngủ lại một đêm?', 'a' => 'Đi trong ngày tiện và rẻ, phù hợp lịch trình ngắn ở Hội An. Ngủ lại một đêm đắt hơn khoảng 1 triệu đồng/người nhưng bạn có buổi chiều và sáng sớm gần như vắng khách.'),
        array('q' => 'Chính sách khi cano tạm ngưng do thời tiết như thế nào?', 'a' => 'Được đổi ngày miễn phí hoặc hoàn 100% phần dịch vụ chưa sử dụng nếu chuyến bị huỷ chính thức — chi tiết ghi trong hợp đồng và voucher.'),
        array('q' => 'Trên đảo có quy định gì đặc biệt không?', 'a' => 'Có. Cù Lao Chàm nói không với túi ni lông và hạn chế nhựa dùng một lần từ năm 2009. Vui lòng mang túi vải, bình nước cá nhân và không chạm hay lấy san hô.'),
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
        'lan-bien-san-ho' => 'Lặn biển & ngắm san hô',
        'van-hoa-lang-chai' => 'Văn hoá & làng chài',
        'trekking-ngam-canh' => 'Trekking & ngắm cảnh',
        'gia-dinh-bien-dao' => 'Gia đình & biển đảo',
        'cuoi-tuan-da-nang-hoi-an' => 'Cuối tuần từ Đà Nẵng & Hội An',
    ),
);

/* ── company + catalogue dịch vụ (gom trong seed dự án — 1 file / 1 PROJECT_SEED) ── */
$__servicesSeed = [
    'service_clusters' => [
        ['code' => 'ferry', 'nav_label' => 'Cano / Xe', 'label' => 'Vé cano cao tốc & xe kết nối', 'icon' => 'ship', 'hub_key' => 'ferries_hub', 'sort' => 1],
        ['code' => 'flight', 'nav_label' => 'Máy bay & đưa đón', 'label' => 'Vé máy bay & đưa đón sân bay Đà Nẵng', 'icon' => 'plane', 'hub_key' => 'flights_hub', 'sort' => 2],
        ['code' => 'stay', 'nav_label' => 'Lưu trú', 'label' => 'Homestay, bungalow & khách sạn', 'icon' => 'building', 'hub_key' => 'stays_hub', 'sort' => 3],
        ['code' => 'experience', 'nav_label' => 'Vui chơi', 'label' => 'Vé vui chơi & trải nghiệm', 'icon' => 'sparkles', 'hub_key' => 'experiences_hub', 'sort' => 4],
        ['code' => 'other', 'nav_label' => 'Dịch vụ', 'label' => 'Dịch vụ khác', 'icon' => 'briefcase', 'hub_key' => 'extras_hub', 'sort' => 5],
    ],

    'service_categories' => [
        // FERRY — tuyến biển Cửa Đại ↔ Cù Lao Chàm và xe kết nối đất liền
        ['cluster' => 'ferry', 'slug' => 'cano-cua-dai-cu-lao-cham', 'name' => 'Cano cao tốc Cửa Đại ↔ Cù Lao Chàm', 'sort' => 1, 'intro' => 'Cano cao tốc từ bến Cửa Đại (Hội An) ra Bãi Làng, hành trình khoảng 25–35 phút. Lịch chạy phụ thuộc sóng gió và quy định của cảng vụ.'],
        ['cluster' => 'ferry', 'slug' => 'tau-go-cua-dai-cu-lao-cham', 'name' => 'Tàu gỗ chợ Cửa Đại ↔ Bãi Làng', 'sort' => 2, 'intro' => 'Tàu gỗ chở khách và hàng của người dân đảo — chậm hơn (1.5–2 giờ), giá mềm, rời bến rất sớm. Phù hợp khách ngủ lại đảo.'],
        ['cluster' => 'ferry', 'slug' => 'xe-hoi-an-cua-dai', 'name' => 'Xe Hội An ↔ bến Cửa Đại', 'sort' => 3, 'intro' => 'Taxi hoặc xe riêng từ phố cổ Hội An tới bến cano Cửa Đại, khoảng 15–20 phút.'],
        ['cluster' => 'ferry', 'slug' => 'limousine-da-nang-cua-dai', 'name' => 'Limousine Đà Nẵng ↔ Cửa Đại', 'sort' => 4, 'intro' => 'Limousine và xe riêng từ Đà Nẵng tới bến Cửa Đại, canh giờ kịp cano sáng.'],

        // FLIGHT — cửa ngõ hàng không là sân bay quốc tế Đà Nẵng (DAD)
        ['cluster' => 'flight', 'slug' => 'noi-dia-toi-da-nang', 'name' => 'Vé nội địa tới Đà Nẵng (DAD)', 'sort' => 1, 'intro' => 'Bay tới sân bay quốc tế Đà Nẵng — cửa ngõ gần nhất để ra Cù Lao Chàm.'],
        ['cluster' => 'flight', 'slug' => 'dua-don-san-bay-da-nang', 'name' => 'Đưa đón sân bay Đà Nẵng', 'sort' => 2, 'intro' => 'Xe riêng từ DAD về Hội An hoặc thẳng bến cano Cửa Đại.'],
        ['cluster' => 'flight', 'slug' => 'combo-bay-va-dua-don', 'name' => 'Combo vé bay + đưa đón + cano', 'sort' => 3, 'intro' => 'Gộp vé bay, xe đưa đón và cano ra đảo trong một báo giá duy nhất.'],

        // STAY
        ['cluster' => 'stay', 'slug' => 'homestay-bai-lang', 'name' => 'Homestay Bãi Làng', 'sort' => 1, 'intro' => 'Nhà dân tại trung tâm xã đảo — gần bến cano, chợ Tân Hiệp và quán ăn.'],
        ['cluster' => 'stay', 'slug' => 'bungalow-resort-bai-chong', 'name' => 'Bungalow & glamping Bãi Chồng', 'sort' => 2, 'intro' => 'Lưu trú sát bãi tắm đẹp nhất đảo — bước ra là xuống nước.'],
        ['cluster' => 'stay', 'slug' => 'homestay-bai-huong', 'name' => 'Homestay Bãi Hương', 'sort' => 3, 'intro' => 'Làng chài phía nam — yên tĩnh nhất, ăn cơm cùng gia đình chủ nhà.'],
        ['cluster' => 'stay', 'slug' => 'cam-trai-bai-xep', 'name' => 'Cắm trại Bãi Xếp', 'sort' => 4, 'intro' => 'Chỗ dựng lều và thuê lều tại vịnh kín gió — phương án ngủ lại đảo tiết kiệm nhất.'],
        ['cluster' => 'stay', 'slug' => 'khach-san-hoi-an-cua-dai', 'name' => 'Khách sạn Hội An & Cửa Đại', 'sort' => 5, 'intro' => 'Lưu trú đất liền gần bến cano — tiện cho chuyến ra đảo sáng sớm.'],

        // EXPERIENCE
        ['cluster' => 'experience', 'slug' => 'lan-bien-scuba', 'name' => 'Lặn biển scuba', 'sort' => 1, 'intro' => 'Fun dive và intro dive trong khu bảo tồn biển Cù Lao Chàm.'],
        ['cluster' => 'experience', 'slug' => 'snorkel-ngam-san-ho', 'name' => 'Snorkel ngắm san hô', 'sort' => 2, 'intro' => 'Ống thở tại các điểm rạn nước nông — phù hợp người mới và gia đình.'],
        ['cluster' => 'experience', 'slug' => 'kayak-sup', 'name' => 'Kayak & SUP', 'sort' => 3, 'intro' => 'Chèo trong vịnh kín gió Bãi Xếp khi mặt nước lặng.'],
        ['cluster' => 'experience', 'slug' => 'trekking-hai-dang', 'name' => 'Trekking hải đăng & rừng đảo', 'sort' => 4, 'intro' => 'Đường mòn lên hải đăng Hòn Lao và rừng nguyên sinh trên đảo.'],
        ['cluster' => 'experience', 'slug' => 'cau-ca-cau-muc', 'name' => 'Câu cá / câu mực', 'sort' => 5, 'intro' => 'Ra khơi cùng ngư dân Bãi Làng và Bãi Hương — ban ngày hoặc ban đêm.'],
        ['cluster' => 'experience', 'slug' => 'van-hoa-lang-chai', 'name' => 'Văn hoá & làng chài', 'sort' => 6, 'intro' => 'Chùa Hải Tạng, giếng cổ Chăm, chợ Tân Hiệp và nghề yến sào.'],

        // OTHER
        ['cluster' => 'other', 'slug' => 'thue-xe-may-xe-dap', 'name' => 'Thuê xe máy & xe đạp', 'sort' => 1],
        ['cluster' => 'other', 'slug' => 'huong-dan-vien-dia-phuong', 'name' => 'Hướng dẫn viên địa phương', 'sort' => 2],
        ['cluster' => 'other', 'slug' => 'gui-hanh-ly', 'name' => 'Gửi hành lý', 'sort' => 3],
        ['cluster' => 'other', 'slug' => 'y-te-cap-cuu', 'name' => 'Y tế & cấp cứu', 'sort' => 4],
        ['cluster' => 'other', 'slug' => 'spa-massage', 'name' => 'Spa & massage', 'sort' => 5],
    ],

    'services' => [
        // ── FERRY — tuyến biển Cửa Đại ↔ Cù Lao Chàm (6) ─────────────────────
        [
            'code' => 'ferry-cano-cua-dai-clc', 'cluster' => 'ferry', 'category_slug' => 'cano-cua-dai-cu-lao-cham', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Cano cao tốc Cửa Đại → Cù Lao Chàm (một chiều)', 'slug' => 'cano-cao-toc-cua-dai-cu-lao-cham',
            'price_from' => 250000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 612,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Nhanh nhất', 'location_label' => 'Bến Cửa Đại → Bến Bãi Làng',
            'summary' => 'Vé cano cao tốc từ bến Cửa Đại (Hội An) ra Bãi Làng — hành trình khoảng 25–35 phút tuỳ sóng. Khởi hành tập trung buổi sáng, chiều quay về đất liền.',
            'highlights' => ['Chỉ 25–35 phút ra đảo', 'Nhiều khung giờ sáng 07:30 – 09:00', 'Áo phao và hướng dẫn an toàn đầy đủ', 'Hỗ trợ giữ chỗ và e-ticket', 'Tư vấn giờ để kịp lịch tour'],
            'inclusions' => ['Vé cano một chiều', 'Áo phao', 'Phí dịch vụ đặt vé'], 'exclusions' => ['Phí tham quan khu bảo tồn biển', 'Xe tới bến Cửa Đại', 'Hành lý quá khổ'],
            'notes' => ['Cano có thể tạm ngưng khi biển động — xác nhận lịch theo ngày đi trước khi thanh toán.', 'Không mang túi ni lông ra đảo theo quy định của xã Tân Hiệp.'], 'attrs' => ['from' => 'Cửa Đại (Hội An)', 'to' => 'Bãi Làng (Cù Lao Chàm)', 'duration_hours' => 0.5, 'operator' => 'Đối tác cano culaocham.net', 'vehicle_type' => 'cano cao tốc'],
        ],
        [
            'code' => 'ferry-cano-khu-hoi', 'cluster' => 'ferry', 'category_slug' => 'cano-cua-dai-cu-lao-cham', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Vé cano khứ hồi Cửa Đại ↔ Cù Lao Chàm (gồm phí bảo tồn)', 'slug' => 've-cano-khu-hoi-cua-dai-cu-lao-cham',
            'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 488,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Trọn gói', 'location_label' => 'Cửa Đại ↔ Cù Lao Chàm',
            'summary' => 'Gói khứ hồi đã gồm phí tham quan khu bảo tồn biển — giữ chỗ cả chiều về, không phải xếp hàng mua vé lẻ tại bến.',
            'highlights' => ['Giữ chỗ hai chiều trong ngày', 'Đã gồm phí khu bảo tồn biển', 'Chọn giờ về linh hoạt theo chuyến trong ngày', 'Đổi ngày khi biển động theo chính sách'],
            'inclusions' => ['2 lượt cano (đi + về)', 'Phí tham quan khu bảo tồn biển', 'Áo phao'], 'exclusions' => ['Xe hai đầu', 'Ăn uống trên đảo'],
            'notes' => ['Chiều về thường trong khoảng 13:30 – 15:30; khách ngủ lại đảo cần báo trước để chuyển vé ngày hôm sau.'], 'attrs' => ['from' => 'Cửa Đại', 'to' => 'Cù Lao Chàm', 'duration_hours' => 0.5, 'operator' => 'Đối tác cano culaocham.net', 'vehicle_type' => 'cano cao tốc'],
        ],
        [
            'code' => 'ferry-cano-charter', 'cluster' => 'ferry', 'category_slug' => 'cano-cua-dai-cu-lao-cham', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Thuê nguyên cano đi Cù Lao Chàm (charter theo nhóm)', 'slug' => 'thue-nguyen-cano-di-cu-lao-cham',
            'price_from' => 5500000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 64,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Bến Cửa Đại → Cù Lao Chàm',
            'summary' => 'Thuê trọn cano 12–20 chỗ cho nhóm bạn, gia đình lớn hoặc đoàn công ty — chủ động giờ đi và giờ về.',
            'highlights' => ['Cano 12–20 chỗ tuỳ loại', 'Chủ động giờ khởi hành', 'Có thể ghép lịch trình lặn riêng', 'Phù hợp đoàn công ty và chụp ảnh cưới'],
            'inclusions' => ['Nguyên cano khứ hồi trong ngày', 'Thuyền trưởng và áo phao'], 'exclusions' => ['Phí tham quan khu bảo tồn biển', 'Ăn uống', 'Thiết bị snorkel'],
            'notes' => ['Giá tham khảo theo cano 12 chỗ; báo lại theo ngày và số khách thực tế.'], 'attrs' => ['from' => 'Cửa Đại', 'to' => 'Cù Lao Chàm', 'duration_hours' => 0.5, 'operator' => 'Đối tác cano culaocham.net', 'vehicle_type' => 'cano charter'],
        ],
        [
            'code' => 'ferry-tau-go-cho', 'cluster' => 'ferry', 'category_slug' => 'tau-go-cua-dai-cu-lao-cham', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Tàu gỗ chợ Cửa Đại → Bãi Làng', 'slug' => 'tau-go-cho-cua-dai-bai-lang',
            'price_from' => 80000, 'currency' => 'VND', 'rating' => 4.3, 'review_count' => 96,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Bến Cửa Đại → Bến Bãi Làng',
            'summary' => 'Tàu gỗ chở khách và hàng hoá của người dân đảo — khoảng 1.5–2 giờ, giá rẻ nhất và đúng chất địa phương.',
            'highlights' => ['Giá thấp nhất trong các phương án ra đảo', 'Trải nghiệm đi cùng người dân xã đảo', 'Phù hợp khách ngủ lại đảo', 'Ít chuyến, rời bến rất sớm'],
            'inclusions' => ['Vé tàu một chiều'], 'exclusions' => ['Phí khu bảo tồn biển', 'Xe tới bến', 'Ghế ngồi cố định'],
            'notes' => ['Thường chỉ 1–2 chuyến mỗi ngày và khởi hành sớm — không phù hợp lịch trình đi về trong ngày.'], 'attrs' => ['from' => 'Cửa Đại', 'to' => 'Bãi Làng', 'duration_hours' => 1.75, 'operator' => 'Hợp tác xã tàu Cù Lao Chàm', 'vehicle_type' => 'tàu gỗ chở khách'],
        ],
        [
            'code' => 'transfer-hoi-an-cua-dai', 'cluster' => 'ferry', 'category_slug' => 'xe-hoi-an-cua-dai', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Xe đưa đón phố cổ Hội An → bến cano Cửa Đại', 'slug' => 'xe-dua-don-hoi-an-ben-cua-dai',
            'price_from' => 120000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 274,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Phố cổ Hội An → Bến Cửa Đại',
            'summary' => 'Xe riêng hoặc taxi đón tại khách sạn phố cổ, đưa tới bến cano trước giờ khởi hành khoảng 30 phút.',
            'highlights' => ['Đón tận khách sạn khu phố cổ', 'Chỉ 15–20 phút di chuyển', 'Canh giờ theo chuyến cano đã đặt', 'Có thể đặt cả chiều về'],
            'inclusions' => ['Xe một chiều', 'Hỗ trợ hành lý'], 'exclusions' => ['Vé cano', 'Phụ phí giờ sớm trước 06:00'],
            'notes' => ['Nên đặt trước tối hôm trước; giờ đón thường 06:45 – 07:15 cho cano sáng.'], 'attrs' => ['from' => 'Hội An', 'to' => 'Bến Cửa Đại', 'duration_hours' => 0.35, 'operator' => 'Đối tác vận chuyển culaocham.net', 'vehicle_type' => 'xe 4–7 chỗ'],
        ],
        [
            'code' => 'limousine-da-nang-cua-dai', 'cluster' => 'ferry', 'category_slug' => 'limousine-da-nang-cua-dai', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Limousine Đà Nẵng → bến cano Cửa Đại (nối Cù Lao Chàm)', 'slug' => 'limousine-da-nang-ben-cano-cua-dai',
            'price_from' => 250000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 188,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Nối cano', 'location_label' => 'Đà Nẵng → Bến Cửa Đại',
            'summary' => 'Limousine ghế ngồi hoặc xe riêng đón tại khách sạn Đà Nẵng, đưa thẳng bến Cửa Đại kịp cano sáng ra đảo.',
            'highlights' => ['Đón khu trung tâm Đà Nẵng và Mỹ Khê', 'Tính giờ buffer trước cano', 'Có thể gộp cùng vé cano khứ hồi', 'Phù hợp khách bay tới DAD tối hôm trước'],
            'inclusions' => ['Vé xe một chiều', 'Hỗ trợ sắp giờ nối cano'], 'exclusions' => ['Vé cano ra đảo', 'Phụ phí đón trước 05:30'],
            'notes' => ['Quãng đường 35–45 km, thời gian 45–60 phút tuỳ giờ cao điểm.'], 'attrs' => ['from' => 'Đà Nẵng', 'to' => 'Bến Cửa Đại', 'duration_hours' => 1, 'operator' => 'Đối tác xe culaocham.net', 'vehicle_type' => 'limousine / xe riêng'],
        ],

        // ── FLIGHT — cửa ngõ Đà Nẵng (DAD) (4) ───────────────────────────────
        [
            'code' => 'flight-han-dad', 'cluster' => 'flight', 'category_slug' => 'noi-dia-toi-da-nang', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Vé máy bay Hà Nội — Đà Nẵng (DAD)', 'slug' => 've-may-bay-ha-noi-da-nang',
            'price_from' => 1150000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 342,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Nối đảo', 'location_label' => 'Hà Nội → DAD',
            'summary' => 'Bay tới Đà Nẵng rồi di chuyển 45–60 phút về bến Cửa Đại để ra Cù Lao Chàm — lộ trình phổ biến nhất từ miền Bắc.',
            'highlights' => ['Rất nhiều khung giờ mỗi ngày', 'Bay khoảng 1 giờ 20 phút', 'Có thể gộp đưa đón sân bay — Cửa Đại', 'Tư vấn chọn chuyến kịp cano sáng hôm sau'],
            'inclusions' => ['Vé economy một chiều', 'Thuế phí sân bay'], 'exclusions' => ['Hành lý ký gửi mua thêm', 'Đưa đón mặt đất'],
            'notes' => ['Giá tham khảo — báo lại theo ngày bay và hạng vé.'], 'attrs' => ['from' => 'HAN', 'to' => 'DAD', 'airlines' => ['Vietnam Airlines', 'Vietjet Air', 'Bamboo Airways'], 'flight_time' => '1h20m'],
        ],
        [
            'code' => 'flight-sgn-dad', 'cluster' => 'flight', 'category_slug' => 'noi-dia-toi-da-nang', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Vé máy bay TP.HCM — Đà Nẵng (DAD)', 'slug' => 've-may-bay-tphcm-da-nang',
            'price_from' => 1050000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 296,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Sài Gòn → DAD',
            'summary' => 'Chặng bay dày nhất tới miền Trung — bay khoảng 1 giờ 15 phút, hạ cánh là có thể về Hội An trong ngày.',
            'highlights' => ['Tần suất cao, dễ chọn giờ', 'Bay khoảng 1h15', 'Có thể gộp xe đón sân bay', 'Phù hợp lịch trình 3–4 ngày'],
            'inclusions' => ['Vé economy một chiều', 'Thuế phí sân bay'], 'exclusions' => ['Hành lý ký gửi mua thêm', 'Đưa đón mặt đất'],
            'notes' => ['Nên chọn chuyến hạ cánh trước 15:00 nếu muốn ra đảo ngay hôm sau.'], 'attrs' => ['from' => 'SGN', 'to' => 'DAD', 'airlines' => ['Vietnam Airlines', 'Vietjet Air', 'Bamboo Airways'], 'flight_time' => '1h15m'],
        ],
        [
            'code' => 'transfer-dad-airport-cua-dai', 'cluster' => 'flight', 'category_slug' => 'dua-don-san-bay-da-nang', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Đưa đón sân bay Đà Nẵng → Hội An / bến Cửa Đại', 'slug' => 'dua-don-san-bay-da-nang-hoi-an-cua-dai',
            'price_from' => 380000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 231,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'DAD → Hội An / Cửa Đại',
            'summary' => 'Xe riêng đón tận sảnh đến sân bay Đà Nẵng, đưa về khách sạn Hội An hoặc thẳng bến cano Cửa Đại.',
            'highlights' => ['Đón tận sảnh có bảng tên', 'Xe 4 – 16 chỗ theo nhóm', 'Theo dõi chuyến bay trễ không tính thêm phí', 'Có thể trả thẳng bến Cửa Đại'],
            'inclusions' => ['Xe riêng một chiều', 'Hỗ trợ hành lý', 'Nước uống'], 'exclusions' => ['Phụ phí đón sau 22:00', 'Vé cano'],
            'notes' => ['Gửi số hiệu chuyến bay trước 24 giờ để tài xế theo dõi giờ hạ cánh.'], 'attrs' => ['from' => 'Sân bay Đà Nẵng', 'to' => 'Hội An / Cửa Đại', 'duration_hours' => 1, 'operator' => 'Đối tác vận chuyển culaocham.net', 'vehicle_type' => 'xe riêng 4–16 chỗ'],
        ],
        [
            'code' => 'combo-flight-transfer-clc', 'cluster' => 'flight', 'category_slug' => 'combo-bay-va-dua-don', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Combo vé bay + đưa đón + cano khứ hồi Cù Lao Chàm', 'slug' => 'combo-ve-bay-dua-don-cano-cu-lao-cham',
            'price_from' => 2450000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 74,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Một đầu mối', 'location_label' => 'Hà Nội/Sài Gòn → DAD → Cù Lao Chàm',
            'summary' => 'Một báo giá cho toàn bộ chặng: vé bay tới Đà Nẵng, xe đón sân bay và cano khứ hồi ra đảo — không phải tự ghép từng khâu.',
            'highlights' => ['Một đầu mối chăm sóc toàn tuyến', 'Đã gồm phí khu bảo tồn biển', 'Hỗ trợ đổi lịch khi biển động', 'Tính giờ nối chuyến hợp lý'],
            'inclusions' => ['Vé máy bay một chiều (hạng phổ thông)', 'Đưa đón sân bay', 'Cano khứ hồi + phí bảo tồn'], 'exclusions' => ['Lưu trú', 'Ăn uống', 'Hành lý ký gửi mua thêm'],
            'notes' => ['Giá thay đổi theo ngày bay — chốt sau khi bạn xác nhận lịch cụ thể.'], 'attrs' => ['from' => 'HAN / SGN', 'to' => 'Cù Lao Chàm', 'airlines' => ['Vietnam Airlines', 'Vietjet Air'], 'flight_time' => '1h15m – 1h20m'],
        ],

        // ── STAY (7) ─────────────────────────────────────────────────────────
        [
            'code' => 'stay-homestay-bai-lang', 'cluster' => 'stay', 'category_slug' => 'homestay-bai-lang', 'zone_slug' => 'bai-lang',
            'title' => 'Homestay nhà dân Bãi Làng', 'slug' => 'homestay-nha-dan-bai-lang',
            'price_from' => 450000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 268,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Bản địa', 'star_rating' => 2, 'location_label' => 'Bãi Làng, xã Tân Hiệp',
            'summary' => 'Phòng trong nhà dân tại trung tâm xã đảo — gần bến cano, chợ Tân Hiệp và chùa Hải Tạng, chủ nhà hỗ trợ đặt tour lặn.',
            'highlights' => ['Gần bến cano và chợ Tân Hiệp', 'Chủ nhà nấu bữa tối theo yêu cầu', 'Hỗ trợ đặt snorkel và câu mực đêm', 'Wi-Fi cơ bản'],
            'inclusions' => ['Phòng 1 đêm', 'Wi-Fi', 'Khai báo lưu trú'], 'exclusions' => ['Bữa ăn (đặt thêm)', 'Đồ uống'],
            'notes' => ['Tiện nghi đơn giản, điện có thể yếu vào giờ cao điểm buổi tối.'],
            'content' => '<p>Homestay nhà dân Bãi Làng — gần bến cano, chợ Tân Hiệp và chùa Hải Tạng. Chủ nhà hỗ trợ đặt snorkel / câu mực đêm; bữa tối đặt thêm.</p><h2>Tiện nghi</h2><p>Phòng quạt hoặc máy lạnh. Điện có thể yếu giờ cao điểm tối — không kỳ vọng resort.</p>',
            'attrs' => [
                'amenities' => ['Gần bến cano', 'Gần chợ', 'Hỗ trợ tour', 'Wi-Fi'],
                'check_in' => 'Linh hoạt', 'check_out' => '11:00', 'property_type' => 'homestay',
                'nearby' => [
                    ['name' => 'Bến cano Bãi Làng', 'distance' => 'Đi bộ / vài phút', 'icon' => 'ship'],
                    ['name' => 'Chợ Tân Hiệp', 'distance' => 'Gần', 'icon' => 'utensils'],
                    ['name' => 'Chùa Hải Tạng', 'distance' => 'Đi bộ trong làng', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => 'bl-quat', 'name' => 'Phòng quạt 2 khách', 'price_from' => 450000, 'capacity' => 2, 'attrs' => ['bed' => '1 giường đôi / 2 đơn']],
                ['code' => 'bl-may-lanh', 'name' => 'Phòng máy lạnh 2 khách', 'price_from' => 650000, 'capacity' => 2, 'amenities' => ['Máy lạnh'], 'attrs' => ['bed' => '1 giường đôi']],
            ],
            'faqs' => [
                ['q' => 'Có điện ổn không?', 'a' => 'Điện đảo có thể yếu giờ cao điểm tối. Homestay không phải khách sạn đất liền — mang sạc dự phòng nếu cần.'],
            ],
        ],
        [
            'code' => 'stay-homestay-bai-lang-family', 'cluster' => 'stay', 'category_slug' => 'homestay-bai-lang', 'zone_slug' => 'bai-lang',
            'title' => 'Phòng gia đình homestay Bãi Làng (3–5 khách)', 'slug' => 'phong-gia-dinh-homestay-bai-lang',
            'price_from' => 850000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 87,
            'is_featured' => false, 'is_hot_deal' => false, 'star_rating' => 2, 'location_label' => 'Bãi Làng',
            'summary' => 'Phòng rộng cho gia đình hoặc nhóm bạn 3–5 người — giảm chi phí mỗi khách so với đặt hai phòng đôi.',
            'highlights' => ['Sức chứa 3–5 khách', 'Máy lạnh và nhà vệ sinh riêng', 'Gần quán ăn và bến cano'],
            'inclusions' => ['Phòng family 1 đêm', 'Wi-Fi'], 'exclusions' => ['Bữa ăn', 'Giường phụ ngoài sức chứa'],
            'notes' => ['Số phòng gia đình rất hạn chế — nên đặt trước 5–7 ngày vào mùa cao điểm.'],
            'content' => '<p>Phòng gia đình homestay Bãi Làng cho 3–5 khách — máy lạnh, WC riêng. Số phòng rất hạn chế mùa cao điểm.</p>',
            'attrs' => [
                'amenities' => ['Family room', 'Máy lạnh', 'Gần bến', 'Wi-Fi'],
                'check_in' => 'Linh hoạt', 'check_out' => '11:00', 'property_type' => 'homestay',
                'child_policy' => 'Phù hợp 3–5 khách kể cả trẻ em; không thêm giường phụ ngoài sức chứa đã xác nhận.',
                'nearby' => [
                    ['name' => 'Bến cano Bãi Làng', 'distance' => 'Gần', 'icon' => 'ship'],
                    ['name' => 'Quán ăn làng', 'distance' => 'Đi bộ', 'icon' => 'utensils'],
                ],
            ],
            'options' => [
                ['code' => 'bl-family', 'name' => 'Family 3–5 khách', 'price_from' => 850000, 'capacity' => 5, 'amenities' => ['Máy lạnh', 'WC riêng'], 'attrs' => ['bed' => '1 đôi + extra / nhiều giường']],
            ],
        ],
        [
            'code' => 'stay-bungalow-bai-chong', 'cluster' => 'stay', 'category_slug' => 'bungalow-resort-bai-chong', 'zone_slug' => 'bai-chong',
            'title' => 'Bungalow view biển Bãi Chồng', 'slug' => 'bungalow-view-bien-bai-chong',
            'price_from' => 1400000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 132,
            'is_featured' => true, 'is_hot_deal' => false, 'star_rating' => 3, 'location_label' => 'Bãi Chồng, Cù Lao Chàm',
            'summary' => 'Bungalow ngay sau bãi tắm đẹp nhất đảo — buổi sáng và chiều muộn bãi gần như vắng khách.',
            'highlights' => ['Bước vài chục mét là xuống biển', 'Sáng sớm bãi vắng trước khi cano khách tới', 'Nhà hàng nhỏ tại chỗ', 'Số lượng bungalow hạn chế'],
            'inclusions' => ['Bungalow 1 đêm', 'Ăn sáng', 'Wi-Fi'], 'exclusions' => ['Bữa trưa và tối', 'Thuê kayak'],
            'notes' => ['Không nhận khách trong mùa biển động khi cano ngưng hoạt động.'],
            'content' => '<p>Bungalow sát Bãi Chồng — sáng sớm và chiều muộn bãi vắng. Ăn sáng gồm trong gói; kayak thuê thêm.</p><h2>Biển động</h2><p>Không nhận khách khi cano ngưng vì biển động — xác nhận lại lịch trước ngày đi.</p>',
            'attrs' => [
                'amenities' => ['Sát biển', 'Ăn sáng', 'Nhà hàng tại chỗ', 'Wi-Fi'],
                'check_in' => '14:00', 'check_out' => '12:00', 'property_type' => 'bungalow',
                'cancellation_policy' => 'Huỷ/đổi ngày theo chỗ nghỉ. Khi cano ngưng vì biển động, điều hành xác nhận lại — không tự ý hứa hoàn 100% trừ khi chỗ nghỉ đồng ý bằng văn bản.',
                'nearby' => [
                    ['name' => 'Bãi Chồng', 'distance' => 'Vài chục mét', 'icon' => 'umbrella'],
                    ['name' => 'Bãi Làng', 'distance' => 'Xe / đi bộ nội đảo', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => 'bc-standard', 'name' => 'Bungalow tiêu chuẩn', 'price_from' => 1400000, 'capacity' => 2, 'attrs' => ['bed' => '1 giường đôi']],
                ['code' => 'bc-seaview', 'name' => 'Bungalow hướng biển', 'price_from' => 1900000, 'capacity' => 2, 'amenities' => ['Hướng biển'], 'attrs' => ['bed' => '1 giường đôi', 'view' => 'Hướng biển']],
            ],
            'faqs' => [
                ['q' => 'Có ngủ được khi biển động không?', 'a' => 'Khi cano ngưng, bungalow cũng không nhận khách. Lịch ngủ đảo phụ thuộc thời tiết — xác nhận với điều hành trước ngày đi.'],
            ],
        ],
        [
            'code' => 'stay-glamping-bai-chong', 'cluster' => 'stay', 'category_slug' => 'bungalow-resort-bai-chong', 'zone_slug' => 'bai-chong',
            'title' => 'Lều glamping Bãi Chồng', 'slug' => 'leu-glamping-bai-chong',
            'price_from' => 900000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 78,
            'is_featured' => false, 'is_hot_deal' => true, 'discount_badge' => 'Ngủ nghe sóng', 'star_rating' => 2, 'location_label' => 'Bãi Chồng',
            'summary' => 'Lều dựng sẵn có nệm, đèn và quạt — trải nghiệm ngủ sát biển với chi phí thấp hơn bungalow.',
            'highlights' => ['Lều dựng sẵn, có nệm và đèn', 'Khu vệ sinh và tắm nước ngọt chung', 'Ngay sau bãi tắm', 'Phù hợp nhóm bạn trẻ'],
            'inclusions' => ['Lều 1 đêm', 'Nệm, gối, đèn'], 'exclusions' => ['Bữa ăn', 'Túi ngủ riêng'],
            'notes' => ['Chỉ hoạt động trong mùa biển êm và khi được ban quản lý cho phép.'],
            'content' => '<p>Glamping Bãi Chồng — lều dựng sẵn có nệm, đèn, quạt; vệ sinh và tắm nước ngọt chung. Chỉ mở mùa biển êm và khi được phép.</p>',
            'attrs' => [
                'amenities' => ['Sát biển', 'Vệ sinh chung', 'Lều dựng sẵn'],
                'check_in' => '14:00', 'check_out' => '11:00', 'property_type' => 'glamping',
                'cancellation_policy' => 'Chỉ hoạt động khi biển êm và ban quản lý cho phép. Huỷ vì thời tiết theo xác nhận điều hành.',
                'nearby' => [
                    ['name' => 'Bãi Chồng', 'distance' => 'Sát bãi', 'icon' => 'umbrella'],
                ],
            ],
            'options' => [
                ['code' => 'gl-tent', 'name' => 'Lều glamping 2 khách', 'price_from' => 900000, 'capacity' => 2, 'amenities' => ['Nệm', 'Đèn', 'Quạt']],
            ],
            'faqs' => [
                ['q' => 'Lều có WC riêng không?', 'a' => 'Không — khu vệ sinh và tắm nước ngọt chung. Muốn WC riêng chọn bungalow Bãi Chồng.'],
            ],
        ],
        [
            'code' => 'stay-homestay-bai-huong', 'cluster' => 'stay', 'category_slug' => 'homestay-bai-huong', 'zone_slug' => 'bai-huong',
            'title' => 'Homestay làng chài Bãi Hương', 'slug' => 'homestay-lang-chai-bai-huong',
            'price_from' => 400000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 96,
            'is_featured' => true, 'is_hot_deal' => false, 'star_rating' => 2, 'location_label' => 'Bãi Hương, xã Tân Hiệp',
            'summary' => 'Homestay nhà dân tại làng chài phía nam đảo — yên tĩnh nhất, ăn cơm cùng gia đình chủ nhà, gần như không có khách đoàn.',
            'highlights' => ['Khu vực yên tĩnh nhất đảo', 'Bữa cơm gia đình với cá tươi trong ngày', 'Có thể theo ghe ngư dân thăm lưới', 'Bãi biển vắng ngay trước làng'],
            'inclusions' => ['Phòng 1 đêm', 'Khai báo lưu trú'], 'exclusions' => ['Bữa ăn (đặt thêm, rất nên đặt)', 'Di chuyển từ Bãi Làng'],
            'notes' => ['Cách Bãi Làng 5–6 km — cần đặt xe hoặc ghe nội đảo.', 'Sóng di động và wifi yếu hơn Bãi Làng.'],
            'content' => '<p>Homestay Bãi Hương — làng chài phía nam, yên nhất đảo. Bữa cơm nhà dân nên đặt thêm; di chuyển từ Bãi Làng cần xe hoặc ghe nội đảo (~5–6 km).</p>',
            'attrs' => [
                'amenities' => ['Yên tĩnh', 'Bữa cơm nhà dân', 'Gần biển'],
                'check_in' => 'Linh hoạt', 'check_out' => '11:00', 'property_type' => 'homestay',
                'nearby' => [
                    ['name' => 'Bãi Hương', 'distance' => 'Sát làng', 'icon' => 'umbrella'],
                    ['name' => 'Bãi Làng', 'distance' => '~5–6 km (xe / ghe nội đảo)', 'icon' => 'ship'],
                ],
            ],
            'options' => [
                ['code' => 'bh-room', 'name' => 'Phòng nhà dân 2 khách', 'price_from' => 400000, 'capacity' => 2],
            ],
            'faqs' => [
                ['q' => 'Làm sao tới Bãi Hương?', 'a' => 'Từ Bãi Làng khoảng 5–6 km — đặt xe hoặc ghe nội đảo khi book, không tự ý đi đường rừng một mình.'],
                ['q' => 'Wifi có ổn không?', 'a' => 'Sóng và wifi yếu hơn Bãi Làng. Coi đây là nghỉ làng chài, không phải khách sạn có mạng ổn định.'],
            ],
        ],
        [
            'code' => 'stay-camping-bai-xep', 'cluster' => 'stay', 'category_slug' => 'cam-trai-bai-xep', 'zone_slug' => 'bai-xep',
            'title' => 'Cắm trại Bãi Xếp — thuê lều và chỗ dựng', 'slug' => 'cam-trai-thue-leu-bai-xep',
            'price_from' => 320000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 58,
            'is_featured' => false, 'is_hot_deal' => false, 'star_rating' => 1, 'location_label' => 'Bãi Xếp',
            'summary' => 'Chỗ dựng lều hoặc thuê lều tại vịnh kín gió Bãi Xếp — lựa chọn tiết kiệm nhất để ngủ lại đảo.',
            'highlights' => ['Vịnh kín gió, sóng nhẹ', 'Có thể thuê lều hoặc mang lều riêng', 'Khu vệ sinh chung', 'Gần điểm kayak'],
            'inclusions' => ['Chỗ dựng trại hoặc lều cơ bản'], 'exclusions' => ['Túi ngủ (thuê thêm)', 'Ăn uống', 'BBQ'],
            'notes' => ['Áp dụng nguyên tắc không để lại rác — toàn bộ rác phải mang về đất liền.'],
            'content' => '<p>Camping Bãi Xếp — vịnh kín gió, thuê lều hoặc chỗ dựng. Vệ sinh chung; rác mang về đất liền. BBQ không mặc định.</p>',
            'attrs' => [
                'amenities' => ['Camping', 'Vịnh kín gió', 'Vệ sinh chung'],
                'check_in' => 'Linh hoạt', 'check_out' => 'Linh hoạt', 'property_type' => 'camping',
                'cancellation_policy' => 'Huỷ khi gió mạnh / biển động theo xác nhận điều hành — không cắm trại nếu không an toàn.',
                'nearby' => [
                    ['name' => 'Bãi Xếp', 'distance' => 'Sát vịnh', 'icon' => 'umbrella'],
                ],
            ],
            'options' => [
                ['code' => 'bx-pitch', 'name' => 'Chỗ dựng (mang lều)', 'price_from' => 220000, 'capacity' => 2],
                ['code' => 'bx-tent', 'name' => 'Thuê lều cơ bản', 'price_from' => 320000, 'capacity' => 2],
            ],
            'faqs' => [
                ['q' => 'Có được đốt lửa / BBQ không?', 'a' => 'Chỉ khi được phép tại điểm camping và thời tiết an toàn — xác nhận khi đặt. Rác phải mang về đất liền.'],
            ],
        ],
        [
            'code' => 'stay-khach-san-hoi-an-cua-dai', 'cluster' => 'stay', 'category_slug' => 'khach-san-hoi-an-cua-dai', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Khách sạn Hội An gần bến Cửa Đại', 'slug' => 'khach-san-hoi-an-gan-ben-cua-dai',
            'price_from' => 750000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 204,
            'is_featured' => true, 'is_hot_deal' => false, 'star_rating' => 3, 'location_label' => 'Cửa Đại — Hội An',
            'summary' => 'Lưu trú đất liền cách bến cano 5–10 phút — tiện cho khách phải có mặt tại bến trước 07:30.',
            'highlights' => ['Gần bến cano Cửa Đại', 'Cách phố cổ khoảng 10–15 phút', 'Hồ bơi và ăn sáng', 'Giữ hành lý miễn phí trong ngày ra đảo'],
            'inclusions' => ['Phòng 1 đêm', 'Ăn sáng', 'Wi-Fi'], 'exclusions' => ['Minibar', 'Đưa đón sân bay'],
            'notes' => ['Nhiều khách chọn ngủ tại đây một đêm trước ngày ra đảo để tránh dậy quá sớm ở phố cổ.'],
            'content' => '<p>Khách sạn gần bến Cửa Đại — 5–10 phút tới cano, ăn sáng gồm trong gói, giữ hành lý ngày ra đảo. Đưa đón sân bay không mặc định.</p>',
            'attrs' => [
                'amenities' => ['Hồ bơi', 'Ăn sáng', 'Gần bến cano', 'Wi-Fi', 'Giữ hành lý'],
                'check_in' => '14:00', 'check_out' => '12:00', 'property_type' => 'hotel',
                'nearby' => [
                    ['name' => 'Bến cano Cửa Đại', 'distance' => '5–10 phút', 'icon' => 'ship'],
                    ['name' => 'Phố cổ Hội An', 'distance' => '~10–15 phút xe', 'icon' => 'map-pin'],
                ],
            ],
            'options' => [
                ['code' => 'cd-std', 'name' => 'Superior 2 khách', 'price_from' => 750000, 'capacity' => 2, 'amenities' => ['Điều hoà', '28m²'], 'attrs' => ['bed' => '1 giường đôi', 'size_sqm' => 28]],
            ],
            'faqs' => [
                ['q' => 'Có giữ hành lý ngày ra đảo không?', 'a' => 'Có — giữ hành lý miễn phí trong ngày ra đảo theo xác nhận chỗ nghỉ. Đưa đón sân bay tính riêng trừ khi gói ghi rõ.'],
            ],
        ],

        // ── EXPERIENCE (9) ───────────────────────────────────────────────────
        [
            'code' => 'exp-snorkel-halfday', 'cluster' => 'experience', 'category_slug' => 'snorkel-ngam-san-ho', 'zone_slug' => 'ran-san-ho',
            'title' => 'Snorkel nửa ngày — thiết bị + thuyền', 'slug' => 'snorkel-nua-ngay-cu-lao-cham',
            'price_from' => 280000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 412,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Dễ đặt nhất', 'location_label' => 'Rạn gần Bãi Chồng / Hòn Dài',
            'summary' => 'Ống thở tại điểm rạn nước nông trong khu bảo tồn biển — áo phao, HDV và thuyền đầy đủ, phù hợp người mới.',
            'highlights' => ['1–2 điểm rạn nước nông', 'Mặt nạ, ống thở và áo phao', 'HDV hướng dẫn quy tắc bảo tồn', 'Phù hợp trẻ từ 6–8 tuổi có người lớn kèm'],
            'inclusions' => ['Thuyền', 'Bộ snorkel', 'Áo phao', 'HDV'], 'exclusions' => ['Ăn uống', 'Phí khu bảo tồn nếu chưa mua theo ngày', 'Ảnh dưới nước'],
            'notes' => ['Độ trong nước thay đổi theo gió từng ngày.', 'Không dùng kem chống nắng chứa oxybenzone khi xuống rạn.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'snorkeling', 'location' => 'Cu Lao Cham marine reserve'],
        ],
        [
            'code' => 'exp-scuba-fun-dive', 'cluster' => 'experience', 'category_slug' => 'lan-bien-scuba', 'zone_slug' => 'ran-san-ho',
            'title' => 'Fun dive Cù Lao Chàm (có chứng chỉ)', 'slug' => 'fun-dive-cu-lao-cham',
            'price_from' => 1250000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 118,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Hòn Dài / Hòn Tai',
            'summary' => 'Một hoặc hai lần lặn boat dive cùng dive master — dành cho thợ lặn có chứng chỉ Open Water trở lên.',
            'highlights' => ['Boat dive nhóm nhỏ 1:4', 'Bình khí và chì', 'Briefing an toàn trước mỗi lần lặn', 'Điểm lặn chọn theo con nước'],
            'inclusions' => ['Thuyền lặn', 'Bình khí + chì', 'Dive master'], 'exclusions' => ['Thuê full set thiết bị', 'Bảo hiểm lặn', 'Ảnh dưới nước'],
            'notes' => ['Bắt buộc mang thẻ chứng chỉ lặn.', 'Không bay trong vòng 18 giờ sau lần lặn cuối.'], 'attrs' => ['duration_hours' => 4, 'activity' => 'scuba', 'location' => 'Cu Lao Cham reefs'],
            'options' => [['code' => 'fd-1', 'name' => '1 lần lặn', 'price_from' => 1250000], ['code' => 'fd-2', 'name' => '2 lần lặn', 'price_from' => 1850000]],
        ],
        [
            'code' => 'exp-intro-dive', 'cluster' => 'experience', 'category_slug' => 'lan-bien-scuba', 'zone_slug' => 'ran-san-ho',
            'title' => 'Intro dive cho người mới tại Cù Lao Chàm', 'slug' => 'intro-dive-cu-lao-cham',
            'price_from' => 1550000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 92,
            'is_featured' => true, 'is_hot_deal' => true, 'discount_badge' => 'Người mới', 'location_label' => 'Điểm nông trong khu bảo tồn',
            'summary' => 'Trải nghiệm lặn bình khí lần đầu tại vùng nước nông, có dive master kèm sát một-một trong suốt buổi lặn.',
            'highlights' => ['Không cần chứng chỉ trước', 'Dive master kèm 1:1', 'Thiết bị đầy đủ', 'Khai báo sức khoẻ bắt buộc'],
            'inclusions' => ['Thiết bị lặn', 'Dive master', 'Thuyền'], 'exclusions' => ['Ảnh/video dưới nước', 'Chứng chỉ lặn'],
            'notes' => ['Không thay thế khoá Open Water đầy đủ.', 'Không phù hợp người có bệnh tim mạch, hô hấp hoặc viêm xoang nặng.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'intro_dive', 'location' => 'Cu Lao Cham'],
        ],
        [
            'code' => 'exp-kayak-bai-xep', 'cluster' => 'experience', 'category_slug' => 'kayak-sup', 'zone_slug' => 'bai-xep',
            'title' => 'Thuê kayak / SUP vịnh Bãi Xếp', 'slug' => 'thue-kayak-sup-bai-xep',
            'price_from' => 180000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 84,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Vịnh Bãi Xếp',
            'summary' => 'Thuê kayak đôi hoặc ván SUP theo giờ trong vịnh kín gió — mặt nước phẳng, phù hợp người mới.',
            'highlights' => ['Theo giờ, linh hoạt', 'Vịnh kín gió mặt nước phẳng', 'Áo phao và hướng dẫn cơ bản', 'Có cứu hộ quan sát'],
            'inclusions' => ['Kayak hoặc SUP', 'Áo phao', 'Mái chèo'], 'exclusions' => ['HDV riêng đi kèm (có thể thêm)', 'Túi chống nước'],
            'notes' => ['Không cho thuê khi có cảnh báo gió mạnh hoặc sóng lớn.'], 'attrs' => ['duration_hours' => 1, 'activity' => 'kayak_sup', 'location' => 'Bai Xep cove'],
            'options' => [['code' => 'kayak-doi', 'name' => 'Kayak đôi / giờ', 'price_from' => 180000], ['code' => 'sup-don', 'name' => 'SUP đơn / giờ', 'price_from' => 150000]],
        ],
        [
            'code' => 'exp-glass-bottom-boat', 'cluster' => 'experience', 'category_slug' => 'snorkel-ngam-san-ho', 'zone_slug' => 'ran-san-ho',
            'title' => 'Thuyền đáy kính ngắm san hô (không cần xuống nước)', 'slug' => 'thuyen-day-kinh-ngam-san-ho',
            'price_from' => 220000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 137,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Rạn nông quanh Hòn Lao',
            'summary' => 'Ngồi trên thuyền có đáy kính để ngắm rạn san hô — lựa chọn cho người lớn tuổi, trẻ nhỏ hoặc người không bơi.',
            'highlights' => ['Không cần biết bơi', 'Phù hợp người lớn tuổi và trẻ nhỏ', 'Hành trình 45–60 phút', 'Đi cùng khung giờ với nhóm snorkel'],
            'inclusions' => ['Thuyền đáy kính', 'Áo phao', 'HDV thuyết minh'], 'exclusions' => ['Thiết bị snorkel', 'Ăn uống'],
            'notes' => ['Tầm nhìn phụ thuộc độ trong nước; ngày sóng lớn có thể tạm ngưng.'], 'attrs' => ['duration_hours' => 1, 'activity' => 'glass_bottom_boat', 'location' => 'Cu Lao Cham'],
        ],
        [
            'code' => 'exp-trekking-hai-dang', 'cluster' => 'experience', 'category_slug' => 'trekking-hai-dang', 'zone_slug' => 'hai-dang',
            'title' => 'Trekking hải đăng Hòn Lao có hướng dẫn', 'slug' => 'trekking-hai-dang-hon-lao',
            'price_from' => 350000, 'currency' => 'VND', 'rating' => 4.8, 'review_count' => 96,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Đường lên hải đăng Hòn Lao',
            'summary' => 'Trek khoảng 2 km lên hải đăng cùng HDV bản địa — điểm nhìn bao quát toàn bộ tám hòn của quần đảo.',
            'highlights' => ['Trek 45–60 phút mỗi chiều', 'HDV giới thiệu thực vật rừng đảo', 'Toàn cảnh quần đảo từ đỉnh', 'Khởi hành sáng sớm hoặc chiều mát'],
            'inclusions' => ['HDV', 'Nước uống', 'Đồ ăn nhẹ'], 'exclusions' => ['Bữa chính', 'Gậy trek (mượn miễn phí nếu còn)'],
            'notes' => ['Cần giày đế bám; không khuyến khích với người có vấn đề khớp gối hoặc tim mạch.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'trekking', 'location' => 'Hon Lao lighthouse'],
        ],
        [
            'code' => 'exp-cau-muc-dem', 'cluster' => 'experience', 'category_slug' => 'cau-ca-cau-muc', 'zone_slug' => 'bai-lang',
            'title' => 'Câu mực đêm cùng ngư dân Bãi Làng', 'slug' => 'cau-muc-dem-bai-lang',
            'price_from' => 350000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 128,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Biển đêm quanh Hòn Lao',
            'summary' => 'Thuyền câu đêm khoảng 2–3 giờ với ngư dân địa phương — đèn, cần câu và mồi đã chuẩn bị sẵn.',
            'highlights' => ['Ngư cụ và đèn câu sẵn sàng', 'Nướng mực câu được ngay trên thuyền', 'Áo phao đầy đủ', 'Chỉ dành cho khách ngủ lại đảo'],
            'inclusions' => ['Thuyền và ngư dân dẫn', 'Ngư cụ', 'Đồ nướng nhẹ'], 'exclusions' => ['Đồ uống có cồn', 'Hải sản mua thêm'],
            'notes' => ['Huỷ khi biển động; hoàn tiền 100% nếu không ra khơi được.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'night_squid_fishing', 'location' => 'Cu Lao Cham'],
        ],
        [
            'code' => 'exp-cau-ca-bai-huong', 'cluster' => 'experience', 'category_slug' => 'cau-ca-cau-muc', 'zone_slug' => 'bai-huong',
            'title' => 'Câu cá ven rạn cùng ngư dân Bãi Hương', 'slug' => 'cau-ca-ven-ran-bai-huong',
            'price_from' => 400000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 61,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Vùng biển Bãi Hương',
            'summary' => 'Ghe gỗ của gia đình ngư dân ra rạn đá ven bờ thả câu, cá câu được mang về quán làng chế biến.',
            'highlights' => ['Ghe địa phương tối đa 6 khách', 'Ngư cụ và mồi chuẩn bị sẵn', 'Chế biến cá câu được tại làng', 'Trải nghiệm gần với đời sống thật'],
            'inclusions' => ['Ghe + ngư dân dẫn', 'Ngư cụ và mồi', 'Áo phao'], 'exclusions' => ['Phí chế biến nâng cao', 'Đồ uống'],
            'notes' => ['Sản lượng câu phụ thuộc con nước — không cam kết số lượng cá.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'fishing', 'location' => 'Bai Huong'],
        ],
        [
            'code' => 'exp-van-hoa-chua-hai-tang', 'cluster' => 'experience', 'category_slug' => 'van-hoa-lang-chai', 'zone_slug' => 'bai-lang',
            'title' => 'Tour đi bộ văn hoá Bãi Làng & chùa Hải Tạng', 'slug' => 'tour-di-bo-van-hoa-bai-lang',
            'price_from' => 300000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 104,
            'is_featured' => false, 'is_hot_deal' => false, 'location_label' => 'Bãi Làng — chùa Hải Tạng',
            'summary' => 'Đi bộ 2–3 giờ cùng HDV bản địa qua chùa Hải Tạng, giếng cổ Chăm, chợ Tân Hiệp và khu trưng bày nghề yến.',
            'highlights' => ['Chùa Hải Tạng hơn 300 năm', 'Giếng cổ Chăm còn nước ngọt', 'Câu chuyện thương cảng và nghề yến sào', 'Đi bộ nhẹ, phù hợp mọi lứa tuổi'],
            'inclusions' => ['HDV bản địa', 'Nước uống', 'Vé điểm tham quan trong chương trình'], 'exclusions' => ['Ăn uống', 'Công đức tại chùa'],
            'notes' => ['Nên mặc trang phục lịch sự khi vào chùa.'], 'attrs' => ['duration_hours' => 3, 'activity' => 'cultural_walk', 'location' => 'Bai Lang'],
        ],

        // ── OTHER (5) ────────────────────────────────────────────────────────
        [
            'code' => 'other-motorbike-rental', 'cluster' => 'other', 'category_slug' => 'thue-xe-may-xe-dap', 'zone_slug' => 'bai-lang',
            'title' => 'Thuê xe máy / xe đạp trên đảo', 'slug' => 'thue-xe-may-xe-dap-cu-lao-cham',
            'price_from' => 120000, 'currency' => 'VND', 'rating' => 4.5, 'review_count' => 186,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Giao nhận tại Bãi Làng',
            'summary' => 'Xe máy hoặc xe đạp thuê theo ngày — cách linh hoạt nhất để tự đi Bãi Chồng, Bãi Hương và chân đường lên hải đăng.',
            'highlights' => ['Giao nhận tại bến Bãi Làng', 'Mũ bảo hiểm kèm theo', 'Bản đồ các bãi và điểm chính', 'Xe đạp phù hợp đoạn Bãi Làng — Bãi Chồng'],
            'inclusions' => ['Xe theo ngày', 'Mũ bảo hiểm'], 'exclusions' => ['Xăng', 'Hư hỏng do người thuê'],
            'notes' => ['Đường ven đảo hẹp và có đoạn dốc — chạy chậm, đặc biệt sau mưa.'], 'attrs' => ['service_type' => 'vehicle_rental', 'rental_modes' => ['motorbike', 'bicycle']],
            'options' => [['code' => 'rent-xe-may', 'name' => 'Xe máy / ngày', 'price_from' => 120000], ['code' => 'rent-xe-dap', 'name' => 'Xe đạp / ngày', 'price_from' => 70000]],
        ],
        [
            'code' => 'other-local-guide', 'cluster' => 'other', 'category_slug' => 'huong-dan-vien-dia-phuong', 'zone_slug' => 'bai-lang',
            'title' => 'Hướng dẫn viên địa phương theo ngày', 'slug' => 'huong-dan-vien-dia-phuong-cu-lao-cham',
            'price_from' => 650000, 'currency' => 'VND', 'rating' => 4.9, 'review_count' => 73,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Toàn đảo Cù Lao Chàm',
            'summary' => 'HDV người xã đảo Tân Hiệp theo ngày — hỗ trợ lịch trình riêng, thuyết minh văn hoá và đặt chỗ ăn.',
            'highlights' => ['8 giờ/ngày', 'Tiếng Anh cơ bản đến khá', 'Am hiểu điểm san hô và giờ cano', 'Hỗ trợ đặt homestay và thuyền'],
            'inclusions' => ['HDV 8 giờ'], 'exclusions' => ['Vé tham quan và thuê xe', 'Ăn uống của HDV'],
            'notes' => ['Dịp lễ Tết phụ phí 20–30%.'], 'attrs' => ['service_type' => 'local_guide', 'languages' => ['vi', 'en']],
        ],
        [
            'code' => 'other-luggage-storage', 'cluster' => 'other', 'category_slug' => 'gui-hanh-ly', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Gửi hành lý tại bến Cửa Đại', 'slug' => 'gui-hanh-ly-ben-cua-dai',
            'price_from' => 60000, 'currency' => 'VND', 'rating' => 4.7, 'review_count' => 152,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Gần bến cano Cửa Đại',
            'summary' => 'Gửi vali khi ra đảo trong ngày hoặc sau khi trả phòng — nhận lại theo biên nhận, không phải mang hành lý lên cano.',
            'highlights' => ['Biên nhận rõ ràng', 'Rất tiện cho tour trong ngày', 'Vali đến 20 kg', 'Mở cửa theo khung giờ cano'],
            'inclusions' => ['Ký gửi trong ngày'], 'exclusions' => ['Quá cân', 'Đồ dễ vỡ không đóng gói', 'Gửi qua đêm (tính thêm)'],
            'notes' => ['Nên đặt trước 3 giờ vào mùa cao điểm.'], 'attrs' => ['service_type' => 'luggage_storage', 'max_weight_kg' => 20],
        ],
        [
            'code' => 'other-medical-emergency', 'cluster' => 'other', 'category_slug' => 'y-te-cap-cuu', 'zone_slug' => 'bai-lang',
            'title' => 'Hỗ trợ y tế & cấp cứu du lịch Cù Lao Chàm', 'slug' => 'ho-tro-y-te-cap-cuu-cu-lao-cham',
            'price_from' => 0, 'currency' => 'VND', 'rating' => 5.0, 'review_count' => 34,
            'is_featured' => true, 'is_hot_deal' => false, 'location_label' => 'Toàn đảo — 24/7',
            'summary' => 'Hotline hỗ trợ khách đặt qua culaocham.net — tai nạn khi lặn hoặc đi xe máy, mất giấy tờ, phối hợp trạm y tế đảo và bệnh viện Hội An.',
            'highlights' => ['Hotline 24/7', 'Phối hợp trạm y tế quân dân y trên đảo', 'Hỗ trợ chuyển tuyến về Hội An khi cần', 'Miễn phí cho khách đã đặt dịch vụ'],
            'inclusions' => ['Hotline', 'Điều phối cơ bản'], 'exclusions' => ['Chi phí khám chữa bệnh', 'Vận chuyển y tế bằng cano riêng'],
            'notes' => ['Không thay thế 115 trong tình huống nguy hiểm tính mạng.', 'Tai nạn lặn cần báo ngay để phối hợp phương án buồng cao áp tại đất liền.'], 'attrs' => ['service_type' => 'medical_assistance', 'availability' => '24/7', 'price_label' => 'Liên hệ'],
        ],
        [
            'code' => 'other-spa-massage', 'cluster' => 'other', 'category_slug' => 'spa-massage', 'zone_slug' => 'ket-hop-hoi-an',
            'title' => 'Spa & massage tại Hội An sau ngày ra đảo', 'slug' => 'spa-massage-hoi-an-sau-ngay-ra-dao',
            'price_from' => 250000, 'currency' => 'VND', 'rating' => 4.6, 'review_count' => 118,
            'is_featured' => false, 'is_hot_deal' => true, 'discount_badge' => 'Sau chuyến đảo', 'location_label' => 'Hội An / Cửa Đại',
            'summary' => 'Massage chân hoặc body 60–90 phút tại đối tác đã kiểm duyệt ở Hội An — thường đặt vào buổi tối sau ngày lặn hoặc trekking.',
            'highlights' => ['60–90 phút', 'Đặt linh hoạt theo giờ về của cano', 'Không ép upsell', 'Gần khu phố cổ'],
            'inclusions' => ['Gói massage', 'Trà thảo mộc'], 'exclusions' => ['Tiền tip', 'Dịch vụ nâng cao'],
            'notes' => ['Nên đặt trước vào buổi tối cao điểm.'], 'attrs' => ['service_type' => 'spa', 'duration_minutes' => 60],
            'options' => [['code' => 'spa-60', 'name' => '60 phút', 'price_from' => 250000], ['code' => 'spa-90', 'name' => '90 phút', 'price_from' => 360000]],
        ],
    ],

    'service_listing_faqs' => [
        ['q' => 'Giá "từ" trên trang dịch vụ có phải giá cố định không?', 'a' => 'Không — giá "từ" là mức tham khảo theo mùa thấp điểm hoặc hạng tiêu chuẩn. culaocham.net báo giá chính xác sau khi nhận ngày đi, số khách và yêu cầu cụ thể.'],
        ['q' => 'Tôi có thể gộp cano + homestay + lặn trong một đơn không?', 'a' => 'Có. Chúng tôi thiết kế lịch trình tuỳ chỉnh — gộp cano Cửa Đại, lưu trú trên đảo, trải nghiệm lặn và dịch vụ hỗ trợ vào một báo giá, một đầu mối chăm sóc.'],
        ['q' => 'Vé cano đã gồm phí tham quan khu bảo tồn biển chưa?', 'a' => 'Gói khứ hồi của chúng tôi đã gồm. Vé một chiều lẻ thường chưa gồm — khoản này thu riêng tại bến và dùng cho công tác bảo tồn rạn san hô.'],
        ['q' => 'Chính sách hoàn/huỷ khi cano tạm ngưng vì thời tiết?', 'a' => 'Vé cano và tour thuyền được đổi ngày miễn phí hoặc hoàn 100% nếu chuyến bị huỷ chính thức. Lưu trú và spa áp dụng điều kiện riêng ghi trên voucher.'],
        ['q' => 'Nên ở Bãi Làng, Bãi Chồng hay Bãi Hương?', 'a' => 'Bãi Làng tiện nhất về ăn uống và dịch vụ; Bãi Chồng sát bãi tắm đẹp nhất, hợp gia đình và cặp đôi; Bãi Hương yên tĩnh nhất, hợp người muốn trải nghiệm làng chài thật.'],
        ['q' => 'Trên đảo có được mang túi ni lông không?', 'a' => 'Không. Cù Lao Chàm áp dụng quy ước cộng đồng "Nói không với túi ni lông" từ năm 2009 — hãy dùng túi vải và bình nước cá nhân.'],
    ],
];

$__companySeed = [
    'name' => 'culaocham.net',
    'legal_name' => 'Công ty TNHH Du lịch culaocham.net',
    'tagline' => 'Khu dự trữ sinh quyển UNESCO ngoài khơi Hội An',
    'slogan' => '"Ra đảo nhẹ nhàng, để lại thật ít dấu chân"',
    'license_number' => '0217/2020/TCDL-GPLHQT',
    'contact' => [
        'email' => 'hello@culaocham.net',
        'phone' => '+84 235 391 6666',
        'whatsapp' => '+84 905 391 666',
        'zalo' => '+84 235 391 6666',
        'hotline_label' => 'Hotline',
    ],
    'address' => [
        'street' => 'Gần bến cano Cửa Đại, phường Cửa Đại',
        'locality' => 'Hội An, Quảng Nam',
        'region' => 'Quảng Nam',
        'postal' => '560000',
        'country' => 'VN',
    ],
    'social' => [
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/culaochamhub'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'play', 'url' => 'https://www.youtube.com/@culaochamhub'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'photo', 'url' => 'https://www.instagram.com/culaochamhub'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'share', 'url' => 'https://www.tiktok.com/@culaochamhub'],
    ],
    'schema' => [
        'available_language' => ['Vietnamese', 'English', 'Korean'],
        'contact_type' => 'customer service',
        'logo' => null,
    ],
    'footer' => [
        'copyright' => '© :year culaocham.net. Giấy phép kinh doanh dịch vụ lữ hành số :license.',
        'show_dmca_badge' => true,
    ],
];

return array_merge(
    $__culaochamSeed,
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
            'vi' => 'Ngân sách dự kiến (đã gồm cano khứ hồi và phí khu bảo tồn biển)',




'en' => 'Estimated budget (including the return speedboat and marine reserve fee)',
        ],
        // Để trống → lấy zones/countries có show_in_customize_form
        'accommodation' => [
            'vi' => [
                'Homestay nhà dân Bãi Làng',
                'Bungalow / glamping Bãi Chồng',
                'Homestay làng chài Bãi Hương',
                'Cắm trại Bãi Xếp',
                'Chỉ đi trong ngày, ngủ tại Hội An',
                'Nhờ tư vấn giúp tôi',
            ],




'en' => [
                'Family homestay at Bai Lang',
                'Bungalow / glamping at Bai Chong',
                'Fishing-village homestay at Bai Huong',
                'Camping at Bai Xep',
                'Day trip only, staying in Hoi An',
                'Please advise me',
            ],
        ],
    ]],
    ['nav' => [
        'about_group' => [
            'vi' => 'Về culaocham.net',




'en' => 'About culaocham.net',
        ],
        'cruise' => [
            'label' => ['vi' => 'Trải nghiệm',



'en' => 'Experiences'],
            'all_label' => ['vi' => 'Tất cả trải nghiệm',



'en' => 'All experiences'],
            'all_meta' => ['vi' => 'Vòng đảo, câu mực đêm & cano lặn rạn',



'en' => 'Island loops, night squid fishing & reef dive boats'],
            'search_hint' => ['vi' => 'Tour, điểm đến, trải nghiệm, cẩm nang…',



'en' => 'Tours, places, experiences, guides…'],
            'search_placeholder' => ['vi' => 'Tìm tour, điểm đến, trải nghiệm, bài viết…',



'en' => 'Search tours, places, experiences, articles…'],
            'hub_title' => ['vi' => 'Trải nghiệm',



'en' => 'Experiences'],
            'hub_subtitle' => ['vi' => 'Vòng quần đảo, câu mực đêm và cano lặn rạn — chọn trải nghiệm trên mặt nước phù hợp.',



'en' => 'Island loops, night squid fishing and reef dive boats — pick a water experience that fits.'],
        ],
    ]],
    ['listing_hubs' => [
        'tours_hub' => [
            'vi' => ['seo_body' => 'Trang tour của :brand tập hợp hành trình Cù Lao Chàm — vòng quần đảo, làng chài và combo Hội An. Thiết kế bởi chuyên gia bản địa.'],




'en' => ['seo_body' => ':brand tours cover Cu Lao Cham — island loops, fishing villages and Hoi An combos, designed by local experts.'],
        ],
        'cruises_hub' => [
            'vi' => ['seo_body' => 'Trải nghiệm biển Cù Lao Chàm từ :brand: vòng đảo, câu mực đêm và cano lặn rạn.'],




'en' => ['seo_body' => 'Cu Lao Cham sea experiences from :brand: island loops, night squid fishing and reef dive boats.'],
        ],
        'ferries_hub' => [
            'vi' => ['seo_body' => 'Vé cano cao tốc và xe kết nối Hội An / Đà Nẵng — Cù Lao Chàm qua :brand: lịch rõ, gồm phí khu bảo tồn khi áp dụng.'],




'en' => ['seo_body' => 'Speedboats and transfers Hoi An / Da Nang — Cu Lao Cham via :brand: clear schedules, marine reserve fees included when applicable.'],
        ],
        'flights_hub' => [
            'vi' => ['seo_body' => 'Vé máy bay & đưa đón sân bay Đà Nẵng kết nối Cù Lao Chàm qua :brand.'],




'en' => ['seo_body' => 'Flights and Da Nang airport transfers connecting to Cu Lao Cham via :brand.'],
        ],
        'stays_hub' => [
            'vi' => ['seo_body' => 'Homestay, bungalow và glamping Cù Lao Chàm do :brand tuyển chọn — Bãi Làng, Bãi Chồng, Bãi Hương…'],




'en' => ['seo_body' => 'Cu Lao Cham homestays, bungalows and glamping curated by :brand — Bai Lang, Bai Chong, Bai Huong and more.'],
        ],
        'experiences_hub' => [
            'vi' => ['seo_body' => 'Vé vui chơi & trải nghiệm Cù Lao Chàm — đặt qua :brand, hỗ trợ trước chuyến đi.'],




'en' => ['seo_body' => 'Cu Lao Cham tickets & experiences — book via :brand with pre-trip support.'],
        ],
        'extras_hub' => [
            'vi' => ['seo_body' => 'Dịch vụ hỗ trợ trên đảo Cù Lao Chàm: thuê xe, HDV và hỗ trợ trong chuyến đi cùng :brand.'],




'en' => ['seo_body' => 'On-island support on Cu Lao Cham: vehicle hire, guides and trip assistance with :brand.'],
        ],
    ]],
);
