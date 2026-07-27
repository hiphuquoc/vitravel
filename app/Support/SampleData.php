<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Dữ liệu mẫu (mock) cho toàn bộ giao diện — thay bằng CMS/DB thật ở giai đoạn sau.
 * Cấu trúc field bám theo docs/03-data-models.md.
 */
class SampleData
{
    public static function countries(): array
    {
        return [
            ['slug' => 'viet-nam', 'name' => 'Việt Nam', 'size' => 'large', 'tourCount' => 42, 'tagline' => 'Từ vịnh Hạ Long tới đồng bằng sông Cửu Long'],
            ['slug' => 'campuchia', 'name' => 'Campuchia', 'size' => 'normal', 'tourCount' => 18, 'tagline' => 'Angkor huyền bí & biển hồ Tonlé Sap'],
            ['slug' => 'bali', 'name' => 'Bali (Indonesia)', 'size' => 'normal', 'tourCount' => 9, 'tagline' => 'Đảo của các vị thần'],
            ['slug' => 'thai-lan', 'name' => 'Thái Lan', 'size' => 'normal', 'tourCount' => 21, 'tagline' => 'Xứ sở chùa vàng'],
            ['slug' => 'lao', 'name' => 'Lào', 'size' => 'normal', 'tourCount' => 12, 'tagline' => 'Nhịp sống chậm bên dòng Mekong'],
            ['slug' => 'tour-ket-hop', 'name' => 'Tour kết hợp', 'size' => 'normal', 'tourCount' => 15, 'tagline' => 'Đông Dương trong một hành trình'],
        ];
    }

    public static function country(string $slug): ?array
    {
        return Arr::first(static::countries(), fn ($c) => $c['slug'] === $slug);
    }

    public static function travelStyles(): array
    {
        return [
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
        ];
    }

    public static function durationBuckets(): array
    {
        return [
            'lt7' => 'Dưới 7 ngày',
            '7-10' => '7 – 10 ngày',
            '11-15' => '11 – 15 ngày',
            'gt16' => 'Trên 16 ngày',
        ];
    }

    public static function tours(): array
    {
        return [
            [
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
                'styles' => ['heritage-rich', 'culture-history', 'balanced'],
                'quote' => ['text' => 'Hành trình tuyệt vời, hướng dẫn viên tận tâm và lịch trình rất hợp lý cho gia đình tôi.', 'author' => 'Chị Minh Anh'],
                'places' => ['Hà Nội', 'Ninh Bình', 'Vịnh Hạ Long', 'Sa Pa', 'Mai Châu'],
                'start' => 'Hà Nội',
                'end' => 'Hà Nội',
                'highlightsIntro' => 'Tour Việt Nam 10 ngày là lựa chọn lý tưởng cho lần đầu khám phá miền Bắc: đủ chậm để cảm nhận văn hoá bản địa, đủ trọn vẹn để đi qua những di sản nổi tiếng nhất.',
                'highlights' => [
                    'Ngủ đêm trên du thuyền giữa vịnh Hạ Long — di sản thiên nhiên thế giới',
                    'Đạp xe qua làng cổ và chèo thuyền nan ở Tràng An, Ninh Bình',
                    'Trekking bản Cát Cát – Lao Chải giữa ruộng bậc thang Sa Pa',
                    'Một đêm homestay nhà sàn người Thái ở Mai Châu',
                    'Ẩm thực phố cổ Hà Nội cùng nghệ nhân địa phương',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Hà Nội — Chào đón & dạo phố cổ', 'meals' => 'Tối', 'transport' => ['plane', 'car'], 'overnight' => 'Hà Nội', 'content' => 'Đón sân bay Nội Bài, về khách sạn nghỉ ngơi. Chiều đi bộ 36 phố phường, thưởng thức cà phê trứng và bữa tối ẩm thực đường phố cùng hướng dẫn viên.'],
                    ['day' => 2, 'title' => 'Hà Nội — Thành phố ngàn năm văn hiến', 'meals' => 'Sáng; Trưa', 'transport' => ['walking', 'car'], 'overnight' => 'Hà Nội', 'content' => 'Thăm Văn Miếu – Quốc Tử Giám, Hoàng thành Thăng Long, chùa Trấn Quốc. Chiều tự do khám phá hồ Gươm, xem múa rối nước.'],
                    ['day' => 3, 'title' => 'Ninh Bình — Tràng An & Hang Múa', 'meals' => 'Sáng; Trưa', 'transport' => ['car', 'boat', 'bike'], 'overnight' => 'Ninh Bình', 'content' => 'Chèo thuyền nan xuyên hang động Tràng An, đạp xe đường làng Tam Cốc, leo 500 bậc Hang Múa ngắm hoàng hôn toàn cảnh.'],
                    ['day' => 4, 'title' => 'Vịnh Hạ Long — Lên du thuyền', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['car', 'cruise'], 'overnight' => 'Du thuyền Hạ Long', 'content' => 'Nhận cabin, ăn trưa trên du thuyền giữa vịnh. Chèo kayak khu Luồn Bò, lớp học nấu ăn hoàng hôn trên boong tàu.'],
                    ['day' => 5, 'title' => 'Hạ Long — Hang Sửng Sốt — về Hà Nội', 'meals' => 'Sáng; Trưa', 'transport' => ['cruise', 'car'], 'overnight' => 'Hà Nội', 'content' => 'Thái cực quyền đón bình minh, thăm hang Sửng Sốt, trả phòng và về Hà Nội, tối tự do.'],
                    ['day' => 6, 'title' => 'Sa Pa — Thị trấn trong sương', 'meals' => 'Sáng; Tối', 'transport' => ['car'], 'overnight' => 'Sa Pa', 'content' => 'Di chuyển cao tốc Hà Nội – Lào Cai. Chiều dạo nhà thờ đá, chợ Sa Pa; tối thưởng thức lẩu cá hồi bản địa.'],
                    ['day' => 7, 'title' => 'Sa Pa — Trekking Lao Chải – Tả Van', 'meals' => 'Sáng; Trưa', 'transport' => ['trekking'], 'overnight' => 'Sa Pa', 'content' => 'Trekking 8km qua ruộng bậc thang, ăn trưa tại nhà người H\'Mông, giao lưu văn hoá bản địa.'],
                    ['day' => 8, 'title' => 'Mai Châu — Thung lũng xanh', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['car', 'bike'], 'overnight' => 'Homestay Mai Châu', 'content' => 'Về Mai Châu, đạp xe bản Lác – Pom Coọng, đêm văn nghệ xoè Thái và ngủ nhà sàn.'],
                    ['day' => 9, 'title' => 'Hà Nội — Ngày tự do & mua sắm', 'meals' => 'Sáng', 'transport' => ['car'], 'overnight' => 'Hà Nội', 'content' => 'Về Hà Nội, chiều tự do mua quà: lụa Vạn Phúc, cà phê, đồ thủ công. Tối tiệc chia tay.'],
                    ['day' => 10, 'title' => 'Tạm biệt Việt Nam', 'meals' => 'Sáng', 'transport' => ['car', 'plane'], 'overnight' => null, 'content' => 'Xe đưa ra sân bay Nội Bài. Kết thúc hành trình — hẹn gặp lại!'],
                ],
                'inclusions' => [
                    'Khách sạn 4* trung tâm + du thuyền cabin ban công + homestay tiêu chuẩn',
                    'Toàn bộ bữa ăn theo chương trình (B/L/D)',
                    'Xe riêng đời mới, lái xe kinh nghiệm',
                    'Hướng dẫn viên tiếng Việt/Anh suốt tuyến',
                    'Vé tham quan, thuyền nan, kayak, vé tàu hỏa leo núi',
                    'Nước uống & khăn lạnh mỗi ngày trên xe',
                ],
                'exclusions' => [
                    'Vé máy bay quốc tế đến/đi Việt Nam',
                    'Đồ uống ngoài chương trình, chi phí cá nhân',
                    'Tiền tip cho hướng dẫn viên và lái xe',
                    'Bảo hiểm du lịch (khuyến nghị mua)',
                ],
                'notes' => [
                    'Lịch trình có thể thay đổi thứ tự tuỳ điều kiện thời tiết, vẫn đảm bảo đầy đủ điểm tham quan.',
                    'Tour ghép nhóm nhỏ tối đa 12 khách; có thể đặt tour riêng (private) với phụ phí.',
                ],
                'faqs' => [
                    ['q' => 'Thời điểm nào đẹp nhất để đi tour miền Bắc 10 ngày?', 'a' => 'Tháng 9 – 11 và tháng 3 – 5 là hai khoảng thời gian lý tưởng: trời khô ráo, mát mẻ, lúa chín vàng ở Sa Pa vào cuối tháng 9.'],
                    ['q' => 'Tour có phù hợp với trẻ nhỏ và người lớn tuổi không?', 'a' => 'Có. Ngày trekking Sa Pa có phương án đi xe thay thế; du thuyền và homestay đều an toàn cho trẻ em từ 4 tuổi.'],
                    ['q' => 'Tôi có thể tùy chỉnh lịch trình không?', 'a' => 'Hoàn toàn được — hãy dùng form "Thiết kế tour riêng", chuyên gia của chúng tôi sẽ điều chỉnh theo nhu cầu trong 24 giờ.'],
                ],
                'galleryCount' => 6,
            ],
            [
                'slug' => 'viet-nam-2-tuan-bac-trung-nam',
                'title' => 'Việt Nam 2 tuần — Xuyên Việt Bắc Trung Nam',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'tourCode' => 'VN14D-02',
                'duration' => '14 ngày 13 đêm',
                'days' => 14,
                'rating' => 4.9,
                'reviewCount' => 96,
                'badge' => null,
                'featured' => true,
                'styles' => ['long-duration', 'heritage-rich', 'balanced'],
                'quote' => ['text' => 'Hai tuần hoàn hảo — từ Hạ Long tới Mekong, mọi khâu tổ chức đều chỉn chu.', 'author' => 'Anh Quốc Bảo'],
                'places' => ['Hà Nội', 'Hạ Long', 'Huế', 'Hội An', 'TP. Hồ Chí Minh', 'Cần Thơ'],
                'start' => 'Hà Nội',
                'end' => 'TP. Hồ Chí Minh',
                'highlightsIntro' => 'Hành trình xuyên Việt kinh điển dành cho ai muốn thấy trọn vẹn ba miền trong một chuyến đi.',
                'highlights' => [
                    'Ngủ đêm du thuyền vịnh Hạ Long',
                    'Kinh thành Huế & thuyền rồng sông Hương',
                    'Phố cổ Hội An về đêm với đèn lồng',
                    'Chợ nổi Cái Răng lúc bình minh',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Hà Nội — Xin chào Việt Nam', 'meals' => 'Tối', 'transport' => ['plane', 'car'], 'overnight' => 'Hà Nội', 'content' => 'Đón sân bay, city tour buổi chiều và bữa tối chào mừng.'],
                    ['day' => 2, 'title' => 'Hà Nội — Vịnh Hạ Long', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['car', 'cruise'], 'overnight' => 'Du thuyền', 'content' => 'Lên du thuyền, kayak và lớp nấu ăn trên boong.'],
                    ['day' => 3, 'title' => 'Hạ Long — bay vào Huế', 'meals' => 'Sáng; Trưa', 'transport' => ['cruise', 'plane'], 'overnight' => 'Huế', 'content' => 'Ngắm bình minh trên vịnh, chiều bay vào Huế.'],
                    ['day' => 4, 'title' => 'Huế — Kinh thành & lăng tẩm', 'meals' => 'Sáng; Trưa', 'transport' => ['car', 'boat'], 'overnight' => 'Huế', 'content' => 'Đại Nội, lăng Minh Mạng, nghe ca Huế trên sông Hương.'],
                    ['day' => 5, 'title' => 'Đèo Hải Vân — Hội An', 'meals' => 'Sáng', 'transport' => ['car'], 'overnight' => 'Hội An', 'content' => 'Vượt đèo Hải Vân, dừng chân Lăng Cô, chiều dạo phố cổ Hội An.'],
                ],
                'inclusions' => ['Khách sạn 4* + du thuyền', 'Bữa ăn theo chương trình', 'Vé máy bay nội địa 2 chặng', 'Hướng dẫn viên suốt tuyến'],
                'exclusions' => ['Vé máy bay quốc tế', 'Chi phí cá nhân', 'Tip'],
                'notes' => ['Lịch trình đầy đủ 14 ngày sẽ gửi kèm báo giá chi tiết.'],
                'faqs' => [
                    ['q' => 'Có bao nhiêu chặng bay nội địa trong tour?', 'a' => 'Hai chặng: Hải Phòng/Hà Nội – Huế và Đà Nẵng – TP.HCM, đã bao gồm trong giá tour.'],
                    ['q' => '14 ngày có đủ để đi hết ba miền?', 'a' => 'Đủ cho các điểm nổi bật nhất. Nếu muốn thêm Phú Quốc hoặc Đà Lạt, nên cân nhắc lịch trình 3 tuần.'],
                ],
                'galleryCount' => 5,
            ],
            [
                'slug' => 'viet-nam-campuchia-15-ngay',
                'title' => 'Việt Nam & Campuchia 15 ngày — Mekong nối hai miền di sản',
                'countrySlug' => 'tour-ket-hop',
                'country' => 'Tour kết hợp',
                'tourCode' => 'VNKH15D-03',
                'duration' => '15 ngày 14 đêm',
                'days' => 15,
                'rating' => 5.0,
                'reviewCount' => 74,
                'badge' => 'Bán chạy nhất',
                'featured' => true,
                'styles' => ['multi-country-combo', 'long-duration', 'heritage-rich'],
                'quote' => ['text' => 'Angkor Wat lúc bình minh là khoảnh khắc không thể quên. Cảm ơn đội ngũ đã sắp xếp hoàn hảo!', 'author' => 'Cô Thanh Hà'],
                'places' => ['Hà Nội', 'Hạ Long', 'Hội An', 'TP. Hồ Chí Minh', 'Phnom Penh', 'Siem Reap'],
                'start' => 'Hà Nội',
                'end' => 'Siem Reap',
                'highlightsIntro' => 'Kết hợp tinh hoa Việt Nam với quần thể Angkor kỳ vĩ — hành trình được yêu cầu nhiều nhất của chúng tôi.',
                'highlights' => [
                    'Bình minh Angkor Wat & nụ cười Bayon',
                    'Du thuyền trên sông Mekong qua biên giới',
                    'Phố cổ Hội An và ẩm thực miền Trung',
                    'Cung điện Hoàng gia Phnom Penh',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Hà Nội — Điểm khởi đầu', 'meals' => 'Tối', 'transport' => ['plane', 'car'], 'overnight' => 'Hà Nội', 'content' => 'Đón sân bay, nhận phòng, tối dạo hồ Gươm.'],
                    ['day' => 2, 'title' => 'Vịnh Hạ Long', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['car', 'cruise'], 'overnight' => 'Du thuyền', 'content' => 'Ngủ đêm trên vịnh di sản.'],
                    ['day' => 3, 'title' => 'Bay vào Đà Nẵng — Hội An', 'meals' => 'Sáng', 'transport' => ['plane', 'car'], 'overnight' => 'Hội An', 'content' => 'Chiều thả đèn hoa đăng sông Hoài.'],
                ],
                'inclusions' => ['Khách sạn 4-5*', 'Vé máy bay nội địa & liên tuyến', 'Visa Campuchia', 'Hướng dẫn viên hai nước'],
                'exclusions' => ['Vé máy bay quốc tế', 'Chi phí cá nhân'],
                'notes' => ['Hộ chiếu cần còn hạn ít nhất 6 tháng.'],
                'faqs' => [
                    ['q' => 'Thủ tục visa Campuchia thế nào?', 'a' => 'Chúng tôi hỗ trợ e-visa trọn gói, chỉ cần ảnh hộ chiếu — phí đã bao gồm trong giá tour.'],
                ],
                'galleryCount' => 5,
            ],
            [
                'slug' => 'sa-pa-trekking-4-ngay',
                'title' => 'Sa Pa 4 ngày — Trekking bản làng & ruộng bậc thang',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'tourCode' => 'VN4D-04',
                'duration' => '4 ngày 3 đêm',
                'days' => 4,
                'rating' => 4.8,
                'reviewCount' => 52,
                'badge' => null,
                'featured' => false,
                'styles' => ['trekking', 'nature-homestay', 'small-group'],
                'quote' => ['text' => 'Đêm homestay ở Tả Van ấm áp như ở nhà. Trải nghiệm trekking đáng giá từng bước chân.', 'author' => 'Bạn Hoài Nam'],
                'places' => ['Hà Nội', 'Sa Pa', 'Lao Chải', 'Tả Van', 'Bản Hồ'],
                'start' => 'Hà Nội',
                'end' => 'Hà Nội',
                'highlightsIntro' => 'Dành cho đôi chân ưa khám phá: 3 ngày trekking xuyên thung lũng Mường Hoa với 2 đêm homestay bản địa.',
                'highlights' => ['Trekking 25km qua 5 bản làng', 'Homestay người H\'Mông & người Giáy', 'Chinh phục đồi Fansipan bằng cáp treo (tuỳ chọn)'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Hà Nội — Sa Pa', 'meals' => 'Trưa; Tối', 'transport' => ['car'], 'overnight' => 'Sa Pa', 'content' => 'Khởi hành sớm, chiều làm quen thị trấn.'],
                    ['day' => 2, 'title' => 'Lao Chải — Tả Van', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['trekking'], 'overnight' => 'Homestay Tả Van', 'content' => 'Trekking 10km men theo suối Mường Hoa.'],
                    ['day' => 3, 'title' => 'Tả Van — Bản Hồ', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['trekking'], 'overnight' => 'Homestay Bản Hồ', 'content' => 'Băng rừng trúc, thác Lavie, tắm suối nước nóng.'],
                    ['day' => 4, 'title' => 'Về Hà Nội', 'meals' => 'Sáng', 'transport' => ['car'], 'overnight' => null, 'content' => 'Sáng thư giãn, trưa khởi hành về Hà Nội.'],
                ],
                'inclusions' => ['Xe limousine khứ hồi', 'Homestay + khách sạn 3*', 'Porter hành lý khi trekking'],
                'exclusions' => ['Cáp treo Fansipan', 'Đồ uống'],
                'notes' => ['Cần giày trekking chống trượt; độ khó trung bình.'],
                'faqs' => [
                    ['q' => 'Tôi chưa từng trekking, có tham gia được không?', 'a' => 'Được — cung đường được thiết kế cho người mới, có xe hỗ trợ tại các điểm giữa chặng.'],
                ],
                'galleryCount' => 4,
            ],
            [
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
                'styles' => ['long-duration', 'balanced', 'nature-homestay'],
                'quote' => ['text' => 'Ba tuần đi chậm, ở sâu — đúng chất một chuyến đi để đời.', 'author' => 'Ông Văn Thịnh'],
                'places' => ['Hà Giang', 'Hà Nội', 'Huế', 'Hội An', 'Đà Lạt', 'TP. Hồ Chí Minh', 'Cà Mau'],
                'start' => 'Hà Nội',
                'end' => 'TP. Hồ Chí Minh',
                'highlightsIntro' => 'Lịch trình sâu nhất của chúng tôi: đèo Mã Pí Lèng, cố đô Huế, cao nguyên Đà Lạt và rừng ngập mặn Cà Mau.',
                'highlights' => ['Vòng cung Hà Giang 3 ngày', 'Đêm cắm trại Đà Lạt', 'Xuồng ba lá rừng U Minh'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Hà Nội — Khởi hành', 'meals' => 'Tối', 'transport' => ['plane', 'car'], 'overnight' => 'Hà Nội', 'content' => 'Đón khách, họp đoàn, tiệc chào mừng.'],
                    ['day' => 2, 'title' => 'Hà Giang — Cổng trời Quản Bạ', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['car'], 'overnight' => 'Hà Giang', 'content' => 'Ngược quốc lộ 2 lên cao nguyên đá.'],
                ],
                'inclusions' => ['Khách sạn 4* & homestay tuyển chọn', '3 chặng bay nội địa', 'Hướng dẫn viên chuyên tuyến'],
                'exclusions' => ['Vé máy bay quốc tế', 'Chi phí cá nhân'],
                'notes' => ['Lịch trình chi tiết 21 ngày gửi kèm báo giá.'],
                'faqs' => [],
                'galleryCount' => 5,
            ],
            [
                'slug' => 'phu-quoc-nghi-duong-5-ngay',
                'title' => 'Phú Quốc 5 ngày — Nghỉ dưỡng biển & khám phá đảo ngọc',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'tourCode' => 'VN5D-06',
                'duration' => '5 ngày 4 đêm',
                'days' => 5,
                'rating' => 4.9,
                'reviewCount' => 61,
                'badge' => null,
                'featured' => false,
                'styles' => ['beach', 'honeymoon', 'family'],
                'quote' => ['text' => 'Kỳ trăng mật hoàn hảo — resort tuyệt đẹp và tour 4 đảo rất vui.', 'author' => 'Vợ chồng Tú & Ngân'],
                'places' => ['Phú Quốc', 'Hòn Thơm', 'Rạch Vẹm', 'Bãi Sao'],
                'start' => 'Phú Quốc',
                'end' => 'Phú Quốc',
                'highlightsIntro' => 'Resort ven biển, cano 4 đảo, lặn ngắm san hô và hoàng hôn Sunset Town.',
                'highlights' => ['Cáp treo Hòn Thơm dài nhất thế giới', 'Lặn ngắm san hô Nam đảo', 'Chợ đêm Phú Quốc'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Đảo ngọc chào đón', 'meals' => 'Tối', 'transport' => ['plane', 'car'], 'overnight' => 'Phú Quốc', 'content' => 'Nhận resort, chiều tự do tắm biển.'],
                    ['day' => 2, 'title' => 'Cano 4 đảo phía Nam', 'meals' => 'Sáng; Trưa', 'transport' => ['boat'], 'overnight' => 'Phú Quốc', 'content' => 'Lặn san hô, câu cá, BBQ hải sản trên đảo.'],
                ],
                'inclusions' => ['Resort 5* sát biển', 'Tour cano 4 đảo', 'Đưa đón sân bay'],
                'exclusions' => ['Vé máy bay', 'Spa & minibar'],
                'notes' => ['Mùa đẹp nhất: tháng 11 – tháng 4.'],
                'faqs' => [],
                'galleryCount' => 4,
            ],
        ];
    }

    public static function tour(string $slug): ?array
    {
        return Arr::first(static::tours(), fn ($t) => $t['slug'] === $slug);
    }

    public static function featuredTours(int $limit = 3): array
    {
        return array_slice(array_values(array_filter(static::tours(), fn ($t) => $t['featured'])), 0, $limit);
    }

    public static function toursByCountry(string $countrySlug): array
    {
        return array_values(array_filter(static::tours(), fn ($t) => $t['countrySlug'] === $countrySlug));
    }

    public static function cruiseTypes(): array
    {
        return [
            ['slug' => 'du-thuyen-ha-long', 'name' => 'Du thuyền Hạ Long', 'count' => 14],
            ['slug' => 'du-thuyen-mekong', 'name' => 'Du thuyền Mekong', 'count' => 8],
            ['slug' => 'du-thuyen-lan-ha', 'name' => 'Du thuyền Lan Hạ', 'count' => 6],
        ];
    }

    public static function cruises(): array
    {
        return [
            [
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
                'styles' => ['balanced', 'honeymoon'],
                'quote' => ['text' => 'Cabin ban công nhìn thẳng ra vịnh, đồ ăn xuất sắc — vượt xa mong đợi.', 'author' => 'Chị Kiều Trang'],
                'places' => ['Tuần Châu', 'Hang Sửng Sốt', 'Đảo Titop', 'Làng chài Cửa Vạn'],
                'start' => 'Cảng Tuần Châu',
                'end' => 'Cảng Tuần Châu',
                'departurePort' => 'Cảng quốc tế Tuần Châu',
                'boatClass' => 'Luxury 5*',
                'nightsOnBoard' => 1,
                'cabinTypes' => [
                    ['name' => 'Deluxe Balcony', 'capacity' => 2, 'note' => 'Ban công riêng, 28m²'],
                    ['name' => 'Family Suite', 'capacity' => 4, 'note' => 'Cửa sổ toàn cảnh, 40m²'],
                    ['name' => 'Executive Suite', 'capacity' => 2, 'note' => 'Bồn tắm view vịnh, 52m²'],
                ],
                'highlightsIntro' => 'Một đêm trọn vẹn giữa di sản: kayak, lớp nấu ăn, tiệc hoàng hôn và bình minh thái cực quyền trên boong.',
                'highlights' => ['Cabin ban công riêng hướng vịnh', 'Kayak & thuyền nan làng chài', 'Tiệc BBQ hoàng hôn trên boong'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Lên tàu — khám phá vịnh', 'meals' => 'Trưa; Tối', 'transport' => ['cruise', 'kayak'], 'overnight' => 'Trên du thuyền', 'content' => 'Đón khách tại cảng, ăn trưa hải sản, chiều chèo kayak, tối tiệc hoàng hôn và câu mực đêm.'],
                    ['day' => 2, 'title' => 'Bình minh trên vịnh — trả khách', 'meals' => 'Sáng; Trưa nhẹ', 'transport' => ['cruise'], 'overnight' => null, 'content' => 'Thái cực quyền đón bình minh, thăm hang Sửng Sốt, brunch và về lại cảng.'],
                ],
                'inclusions' => ['Cabin theo hạng chọn', 'Toàn bộ bữa ăn trên tàu', 'Kayak, lớp nấu ăn, câu mực', 'Vé thắng cảnh vịnh'],
                'exclusions' => ['Xe đưa đón Hà Nội (đặt thêm)', 'Đồ uống', 'Spa trên tàu'],
                'notes' => ['Nhận phòng 12:15, trả phòng 10:30 hôm sau.'],
                'faqs' => [
                    ['q' => 'Có xe đưa đón từ Hà Nội không?', 'a' => 'Có, xe limousine khứ hồi Hà Nội – Tuần Châu với phụ phí nhỏ, đặt cùng lúc với vé du thuyền.'],
                ],
                'galleryCount' => 5,
            ],
            [
                'slug' => 'du-thuyen-lan-ha-3-ngay',
                'title' => 'Du thuyền Lan Hạ 3 ngày — Vịnh xanh vắng dấu chân người',
                'typeSlug' => 'du-thuyen-lan-ha',
                'typeName' => 'Du thuyền Lan Hạ',
                'tourCode' => 'CR3D-02',
                'duration' => '3 ngày 2 đêm',
                'days' => 3,
                'rating' => 4.9,
                'reviewCount' => 47,
                'badge' => null,
                'styles' => ['nature-homestay', 'small-group'],
                'quote' => ['text' => 'Lan Hạ yên tĩnh hơn Hạ Long rất nhiều — đúng nơi để trốn khỏi đám đông.', 'author' => 'Anh Đức Huy'],
                'places' => ['Bến Bèo', 'Vịnh Lan Hạ', 'Đảo Cát Bà', 'Hang Sáng Tối'],
                'start' => 'Cảng Bèo, Cát Bà',
                'end' => 'Cảng Bèo, Cát Bà',
                'departurePort' => 'Cảng Bèo (Cát Bà)',
                'boatClass' => 'Boutique 4*',
                'nightsOnBoard' => 2,
                'cabinTypes' => [
                    ['name' => 'Ocean View', 'capacity' => 2, 'note' => 'Cửa sổ lớn, 22m²'],
                    ['name' => 'Balcony Suite', 'capacity' => 3, 'note' => 'Ban công gỗ, 34m²'],
                ],
                'highlightsIntro' => 'Ba ngày len lỏi giữa 400 hòn đảo đá vôi ít người biết, kết hợp đạp xe làng Việt Hải.',
                'highlights' => ['Bãi tắm hoang Ba Trái Đào', 'Đạp xe làng chài Việt Hải', 'Chèo kayak Hang Sáng Tối'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Lên tàu tại Cát Bà', 'meals' => 'Trưa; Tối', 'transport' => ['cruise'], 'overnight' => 'Trên du thuyền', 'content' => 'Hải trình qua vịnh Lan Hạ, tắm biển bãi Ba Trái Đào.'],
                    ['day' => 2, 'title' => 'Việt Hải — Hang Sáng Tối', 'meals' => 'Sáng; Trưa; Tối', 'transport' => ['bike', 'kayak'], 'overnight' => 'Trên du thuyền', 'content' => 'Đạp xe xuyên rừng quốc gia, chiều kayak hang Sáng Tối.'],
                    ['day' => 3, 'title' => 'Bình minh — trả khách', 'meals' => 'Sáng', 'transport' => ['cruise'], 'overnight' => null, 'content' => 'Ngắm bình minh, brunch, về lại cảng Bèo.'],
                ],
                'inclusions' => ['Cabin 2 đêm', 'Bữa ăn trên tàu', 'Xe đạp, kayak', 'Vé vườn quốc gia'],
                'exclusions' => ['Đồ uống', 'Tip'],
                'notes' => ['Phù hợp khách đã từng đi Hạ Long, muốn trải nghiệm vắng hơn.'],
                'faqs' => [],
                'galleryCount' => 4,
            ],
            [
                'slug' => 'du-thuyen-mekong-cai-be-can-tho',
                'title' => 'Du thuyền Mekong — Cái Bè đến Cần Thơ 2 ngày',
                'typeSlug' => 'du-thuyen-mekong',
                'typeName' => 'Du thuyền Mekong',
                'tourCode' => 'CR2D-03',
                'duration' => '2 ngày 1 đêm',
                'days' => 2,
                'rating' => 4.8,
                'reviewCount' => 33,
                'badge' => null,
                'styles' => ['culture-history', 'nature-homestay'],
                'quote' => ['text' => 'Buổi sáng ở chợ nổi Cái Răng nhìn từ boong tàu là trải nghiệm rất riêng của miền Tây.', 'author' => 'Chị Phương Dung'],
                'places' => ['Cái Bè', 'Vĩnh Long', 'Chợ nổi Cái Răng', 'Cần Thơ'],
                'start' => 'Bến Cái Bè',
                'end' => 'Bến Ninh Kiều, Cần Thơ',
                'departurePort' => 'Bến tàu Cái Bè',
                'boatClass' => 'Classic gỗ truyền thống',
                'nightsOnBoard' => 1,
                'cabinTypes' => [
                    ['name' => 'Standard', 'capacity' => 2, 'note' => 'Cửa sổ sông, 16m²'],
                    ['name' => 'Superior', 'capacity' => 2, 'note' => 'Đầu mũi tàu, 20m²'],
                ],
                'highlightsIntro' => 'Xuôi dòng Mekong trên du thuyền gỗ truyền thống, ghé lò cốm, vườn trái cây và chợ nổi.',
                'highlights' => ['Chợ nổi Cái Răng bình minh', 'Xuồng ba lá rạch nhỏ', 'Đờn ca tài tử trên tàu'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Cái Bè — xuôi dòng', 'meals' => 'Trưa; Tối', 'transport' => ['cruise', 'sampan'], 'overnight' => 'Trên du thuyền', 'content' => 'Thăm làng nghề, cù lao An Bình, đêm neo giữa sông.'],
                    ['day' => 2, 'title' => 'Chợ nổi Cái Răng — Cần Thơ', 'meals' => 'Sáng', 'transport' => ['cruise'], 'overnight' => null, 'content' => 'Dậy sớm đi chợ nổi, cập bến Ninh Kiều.'],
                ],
                'inclusions' => ['Cabin máy lạnh', 'Bữa ăn đặc sản miền Tây', 'Xuồng ba lá & vé tham quan'],
                'exclusions' => ['Xe từ TP.HCM (đặt thêm)', 'Đồ uống'],
                'notes' => ['Có thể nối tuyến sang Phnom Penh (Campuchia).'],
                'faqs' => [],
                'galleryCount' => 4,
            ],
        ];
    }

    public static function cruise(string $slug): ?array
    {
        return Arr::first(static::cruises(), fn ($c) => $c['slug'] === $slug);
    }

    public static function blogCategories(): array
    {
        return [
            ['slug' => 'ha-noi', 'name' => 'Hà Nội', 'countrySlug' => 'viet-nam', 'count' => 24],
            ['slug' => 'sa-pa', 'name' => 'Sa Pa', 'countrySlug' => 'viet-nam', 'count' => 17],
            ['slug' => 'hue', 'name' => 'Huế', 'countrySlug' => 'viet-nam', 'count' => 12],
            ['slug' => 'hoi-an', 'name' => 'Hội An', 'countrySlug' => 'viet-nam', 'count' => 19],
            ['slug' => 'phu-quoc', 'name' => 'Phú Quốc', 'countrySlug' => 'viet-nam', 'count' => 11],
            ['slug' => 'siem-reap', 'name' => 'Siem Reap', 'countrySlug' => 'campuchia', 'count' => 14],
            ['slug' => 'phnom-penh', 'name' => 'Phnom Penh', 'countrySlug' => 'campuchia', 'count' => 9],
            ['slug' => 'bangkok', 'name' => 'Bangkok', 'countrySlug' => 'thai-lan', 'count' => 13],
            ['slug' => 'luang-prabang', 'name' => 'Luang Prabang', 'countrySlug' => 'lao', 'count' => 8],
        ];
    }

    public static function contentTags(): array
    {
        return [
            'Ăn gì, uống gì?',
            'Ngủ ở đâu?',
            'Chơi gì, xem gì?',
            'Mẹo du lịch',
            'Chuyến đi thế nào?',
            'Chọn tour nào?',
        ];
    }

    public static function popularKeywords(): array
    {
        return [
            'Kinh nghiệm du lịch Việt Nam', 'Tour Việt Nam 10 ngày', 'Du lịch Campuchia tự túc',
            'Tour Việt Nam 2 tuần', 'Chi phí du lịch Sa Pa', 'Đặc sản Hội An',
            'Du thuyền Hạ Long giá tốt', 'Lịch trình xuyên Việt', 'Angkor Wat mấy giờ mở cửa',
        ];
    }

    public static function articles(): array
    {
        return [
            [
                'slug' => 'sa-pa-mua-nao-dep-nhat',
                'title' => 'Sa Pa mùa nào đẹp nhất? Cẩm nang chọn thời điểm cho từng kiểu du khách',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'category' => 'Sa Pa',
                'categorySlug' => 'sa-pa',
                'tags' => ['Mẹo du lịch', 'Chơi gì, xem gì?'],
                'author' => 'Lan Hương',
                'publishedAt' => '12/06/2026',
                'updatedAt' => '20/07/2026',
                'views' => 1284,
                'rating' => 4.9,
                'ratingCount' => 41,
                'excerpt' => 'Mùa lúa chín, mùa săn mây hay mùa tuyết rơi — mỗi thời điểm Sa Pa lại mang một gương mặt khác. Bài viết giúp bạn chọn đúng mùa cho đúng trải nghiệm.',
                'content' => [
                    ['type' => 'p', 'text' => 'Sa Pa nằm ở độ cao 1.500m so với mực nước biển, khí hậu ôn đới quanh năm mát mẻ nhưng thay đổi rất rõ theo mùa. Chọn đúng thời điểm sẽ quyết định 70% chất lượng chuyến đi của bạn — đặc biệt nếu bạn nhắm tới ruộng bậc thang mùa lúa chín hay những biển mây cuối đông.'],
                    ['type' => 'h2', 'id' => 'khi-hau-tong-quan', 'text' => 'I. Khí hậu Sa Pa tổng quan theo bốn mùa'],
                    ['type' => 'p', 'text' => 'Xuân (tháng 2 – 5) là mùa hoa đào, hoa mận nở trắng thung lũng, nhiệt độ 15 – 18°C. Hè (tháng 6 – 8) mát mẻ nhất miền Bắc nhưng có mưa rào bất chợt. Thu (tháng 9 – 11) là mùa vàng của ruộng bậc thang. Đông (tháng 12 – 1) lạnh sâu, có năm xuất hiện băng tuyết trên đỉnh Fansipan.'],
                    ['type' => 'image', 'caption' => 'Ruộng bậc thang Mường Hoa vào cuối tháng 9 — thời điểm được săn đón nhất năm.'],
                    ['type' => 'h3', 'id' => 'mua-lua-chin', 'text' => '1. Mùa lúa chín (giữa tháng 9 – đầu tháng 10)'],
                    ['type' => 'p', 'text' => 'Đây là "mùa vàng" theo đúng nghĩa đen. Thung lũng Mường Hoa, bản Lao Chải, Tả Van phủ một màu vàng óng. Lưu ý đặt phòng trước ít nhất 3 tuần vì đây là mùa cao điểm của cả khách trong nước lẫn quốc tế.'],
                    ['type' => 'h3', 'id' => 'mua-san-may', 'text' => '2. Mùa săn mây (tháng 11 – tháng 3)'],
                    ['type' => 'p', 'text' => 'Biển mây thường xuất hiện sau những ngày mưa nhỏ, trời hửng nắng. Các điểm săn mây đẹp nhất: đỉnh Fansipan, đèo Ô Quy Hồ, Hàm Rồng. Nên xem dự báo độ ẩm trên 85% và chênh lệch nhiệt độ ngày đêm lớn.'],
                    ['type' => 'links', 'title' => 'Xem thêm:', 'links' => [
                        ['label' => 'Tour Sa Pa 4 ngày trekking bản làng', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'sa-pa-trekking-4-ngay']]],
                        ['label' => 'Kinh nghiệm trekking Lao Chải – Tả Van', 'route' => ['guide.country', ['country' => 'viet-nam']]],
                        ['label' => 'Tour Việt Nam 10 ngày có Sa Pa', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'viet-nam-10-ngay-di-san-mien-bac']]],
                    ]],
                    ['type' => 'h2', 'id' => 'goi-y-theo-kieu-du-khach', 'text' => 'II. Gợi ý theo kiểu du khách'],
                    ['type' => 'ul', 'items' => [
                        'Gia đình có trẻ nhỏ: tháng 3 – 5, thời tiết ôn hoà, ít mưa.',
                        'Nhiếp ảnh gia: cuối tháng 9 (lúa chín) hoặc tháng 12 – 1 (biển mây).',
                        'Người mê trekking: tháng 10 – 11, đường khô ráo, trời trong.',
                        'Cặp đôi trăng mật: tháng 12, se lạnh, phố núi lãng mạn trong sương.',
                    ]],
                    ['type' => 'h2', 'id' => 'ket-luan', 'text' => 'III. Kết luận'],
                    ['type' => 'p', 'text' => 'Không có mùa "xấu" ở Sa Pa — chỉ có mùa phù hợp với bạn hay không. Nếu vẫn phân vân, hãy để chuyên gia bản địa của chúng tôi tư vấn lịch trình miễn phí qua form thiết kế tour riêng.'],
                ],
                'faqs' => [
                    ['q' => 'Sa Pa có tuyết vào tháng nào?', 'a' => 'Nếu có, tuyết thường rơi cuối tháng 12 đến giữa tháng 1, tập trung ở đỉnh Fansipan và đèo Ô Quy Hồ. Đây là hiện tượng không chắc chắn hằng năm.'],
                    ['q' => 'Đi Sa Pa 2 ngày cuối tuần có kịp không?', 'a' => 'Kịp với cao tốc Hà Nội – Lào Cai (5 giờ xe). Tuy nhiên 3 – 4 ngày sẽ thoải mái hơn nếu bạn muốn trekking và nghỉ homestay.'],
                    ['q' => 'Nên ở khách sạn trung tâm hay homestay trong bản?', 'a' => 'Kết hợp cả hai: 1 đêm trung tâm để dạo phố, 1 – 2 đêm homestay Tả Van để trải nghiệm văn hoá bản địa.'],
                ],
                'galleryCount' => 5,
            ],
            [
                'slug' => 'an-gi-o-hoi-an-24-gio',
                'title' => 'Ăn gì ở Hội An trong 24 giờ? Bản đồ ẩm thực từ sáng tới khuya',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'category' => 'Hội An',
                'categorySlug' => 'hoi-an',
                'tags' => ['Ăn gì, uống gì?'],
                'author' => 'Minh Trí',
                'publishedAt' => '02/07/2026',
                'updatedAt' => '15/07/2026',
                'views' => 956,
                'rating' => 4.8,
                'ratingCount' => 28,
                'excerpt' => 'Cao lầu, bánh mì Phượng, cơm gà bà Buội, chè bắp Cẩm Nam — lịch ăn dày đặc cho một ngày trọn vẹn ở phố Hội.',
                'content' => [
                    ['type' => 'p', 'text' => 'Hội An nhỏ nhưng mật độ món ngon trên mỗi mét vuông có lẽ cao nhất Việt Nam. Bài viết này xếp lịch ăn theo khung giờ để bạn không bỏ lỡ món nào trong 24 giờ.'],
                    ['type' => 'h2', 'id' => 'buoi-sang', 'text' => 'I. Buổi sáng: cao lầu và cà phê phố cổ'],
                    ['type' => 'p', 'text' => 'Bắt đầu với tô cao lầu sợi mì vàng ươm — món chỉ đúng vị khi nước được lấy từ giếng Bá Lễ. Sau đó là cà phê muối ở một quán gác gỗ nhìn xuống đường Trần Phú.'],
                    ['type' => 'h2', 'id' => 'buoi-chieu-toi', 'text' => 'II. Chiều tối: chợ đêm và ăn vặt bờ sông'],
                    ['type' => 'p', 'text' => 'Từ 17h, khu chợ đêm Nguyễn Hoàng lên đèn. Nhất định thử bánh bao – bánh vạc "hoa hồng trắng" và kết thúc bằng chè bắp Cẩm Nam.'],
                ],
                'faqs' => [
                    ['q' => 'Cao lầu khác mì Quảng thế nào?', 'a' => 'Cao lầu sợi dày, dai, ít nước dùng và ăn kèm xá xíu; mì Quảng sợi mềm hơn, nhiều nước lèo và đậu phộng.'],
                ],
                'galleryCount' => 4,
            ],
            [
                'slug' => 'angkor-wat-kinh-nghiem-binh-minh',
                'title' => 'Kinh nghiệm ngắm bình minh Angkor Wat: đi mấy giờ, đứng ở đâu?',
                'countrySlug' => 'campuchia',
                'country' => 'Campuchia',
                'category' => 'Siem Reap',
                'categorySlug' => 'siem-reap',
                'tags' => ['Chơi gì, xem gì?', 'Mẹo du lịch'],
                'author' => 'Lan Hương',
                'publishedAt' => '28/06/2026',
                'updatedAt' => '10/07/2026',
                'views' => 1731,
                'rating' => 5.0,
                'ratingCount' => 52,
                'excerpt' => 'Vị trí hồ sen phía trái cổng Tây, có mặt trước 5h15, và những mẹo nhỏ để có khung hình phản chiếu hoàn hảo.',
                'content' => [
                    ['type' => 'p', 'text' => 'Bình minh Angkor Wat là khoảnh khắc được chờ đợi nhất ở Siem Reap — nhưng cũng đông đúc nhất. Chuẩn bị đúng sẽ giúp bạn có trải nghiệm trọn vẹn thay vì chen chúc.'],
                    ['type' => 'h2', 'id' => 'gio-vang', 'text' => 'I. Khung giờ vàng và vé vào cửa'],
                    ['type' => 'p', 'text' => 'Cổng bán vé mở từ 4h30. Hãy mua vé Angkor Pass từ chiều hôm trước để sáng hôm sau đi thẳng vào cổng, có mặt tại hồ sen trước 5h15.'],
                    ['type' => 'h2', 'id' => 'vi-tri-dep', 'text' => 'II. Vị trí đứng đẹp nhất'],
                    ['type' => 'p', 'text' => 'Hồ nước phía trái lối vào chính (hướng Tây) cho khung hình 5 ngọn tháp phản chiếu. Đứng lệch trái 10m so với đám đông, bạn sẽ có tiền cảnh hoa sen.'],
                ],
                'faqs' => [
                    ['q' => 'Vé Angkor Pass giá bao nhiêu?', 'a' => 'Vé 1 ngày 37 USD, 3 ngày 62 USD, 7 ngày 72 USD — thanh toán được bằng thẻ quốc tế.'],
                ],
                'galleryCount' => 5,
            ],
            [
                'slug' => 'ha-noi-36-pho-phuong-mot-ngay',
                'title' => 'Một ngày lang thang 36 phố phường Hà Nội: lộ trình đi bộ chi tiết',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'category' => 'Hà Nội',
                'categorySlug' => 'ha-noi',
                'tags' => ['Chơi gì, xem gì?'],
                'author' => 'Minh Trí',
                'publishedAt' => '18/06/2026',
                'updatedAt' => '01/07/2026',
                'views' => 842,
                'rating' => 4.7,
                'ratingCount' => 19,
                'excerpt' => 'Từ Hàng Mã rực rỡ tới Tạ Hiện về đêm — lộ trình đi bộ 6km xuyên trái tim phố cổ kèm điểm dừng ăn uống.',
                'content' => [
                    ['type' => 'p', 'text' => 'Phố cổ Hà Nội đẹp nhất khi đi bộ. Lộ trình dưới đây dài khoảng 6km, bắt đầu từ chợ Đồng Xuân và kết thúc ở bia hơi Tạ Hiện.'],
                    ['type' => 'h2', 'id' => 'buoi-sang', 'text' => 'I. Buổi sáng: chợ Đồng Xuân — Hàng Mã — Hàng Bạc'],
                    ['type' => 'p', 'text' => 'Ăn sáng phở gánh trong chợ, dạo phố Hàng Mã và nghe câu chuyện nghề kim hoàn trăm năm ở Hàng Bạc.'],
                ],
                'faqs' => [],
                'galleryCount' => 4,
            ],
            [
                'slug' => 'chi-phi-du-lich-viet-nam-10-ngay',
                'title' => 'Chi phí du lịch Việt Nam 10 ngày hết bao nhiêu? Bảng dự trù 2026',
                'countrySlug' => 'viet-nam',
                'country' => 'Việt Nam',
                'category' => 'Hà Nội',
                'categorySlug' => 'ha-noi',
                'tags' => ['Mẹo du lịch', 'Chọn tour nào?'],
                'author' => 'Lan Hương',
                'publishedAt' => '05/06/2026',
                'updatedAt' => '25/06/2026',
                'views' => 2103,
                'rating' => 4.9,
                'ratingCount' => 63,
                'excerpt' => 'So sánh chi tiết ba mức ngân sách — tiết kiệm, tầm trung, cao cấp — cho hành trình 10 ngày phổ biến nhất.',
                'content' => [
                    ['type' => 'p', 'text' => 'Câu hỏi chúng tôi nhận nhiều nhất: "10 ngày ở Việt Nam tốn bao nhiêu?". Câu trả lời phụ thuộc phong cách đi, nhưng bảng dự trù dưới đây sẽ cho bạn con số sát thực tế.'],
                    ['type' => 'h2', 'id' => 'ba-muc-ngan-sach', 'text' => 'I. Ba mức ngân sách phổ biến'],
                    ['type' => 'ul', 'items' => [
                        'Tiết kiệm: 450 – 600 USD/người (hostel, xe khách, ăn địa phương).',
                        'Tầm trung: 900 – 1.300 USD/người (khách sạn 3-4*, tour ghép, 1 chặng bay nội địa).',
                        'Cao cấp: từ 2.000 USD/người (khách sạn 5*, du thuyền, xe riêng, hướng dẫn viên riêng).',
                    ]],
                ],
                'faqs' => [
                    ['q' => 'Đặt tour trọn gói có rẻ hơn tự túc?', 'a' => 'Với nhóm 2 khách trở lên và lịch trình nhiều điểm, tour trọn gói thường tối ưu hơn 10 – 15% nhờ giá đối tác khách sạn và vận chuyển.'],
                ],
                'galleryCount' => 4,
            ],
            [
                'slug' => 'luang-prabang-cham-binh-minh-khat-thuc',
                'title' => 'Luang Prabang: nghi lễ khất thực bình minh và những điều nên biết',
                'countrySlug' => 'lao',
                'country' => 'Lào',
                'category' => 'Luang Prabang',
                'categorySlug' => 'luang-prabang',
                'tags' => ['Chơi gì, xem gì?', 'Mẹo du lịch'],
                'author' => 'Minh Trí',
                'publishedAt' => '22/05/2026',
                'updatedAt' => '30/05/2026',
                'views' => 617,
                'rating' => 4.8,
                'ratingCount' => 15,
                'excerpt' => 'Tak Bat là nghi lễ thiêng liêng, không phải hoạt cảnh du lịch — cách tham dự tôn trọng và những điều tuyệt đối tránh.',
                'content' => [
                    ['type' => 'p', 'text' => 'Mỗi sáng từ 5h30, hàng trăm nhà sư áo cam đi khất thực dọc phố cổ Luang Prabang. Là du khách, bạn được chào đón quan sát — với điều kiện hiểu và tôn trọng quy tắc.'],
                    ['type' => 'h2', 'id' => 'quy-tac', 'text' => 'I. Quy tắc ứng xử khi xem Tak Bat'],
                    ['type' => 'ul', 'items' => [
                        'Giữ khoảng cách tối thiểu 3m, không chắn lối đi của đoàn sư.',
                        'Tắt flash, không dí máy ảnh sát mặt các nhà sư.',
                        'Ăn mặc kín vai và gối; ngồi thấp hơn các nhà sư nếu dâng lễ.',
                    ]],
                ],
                'faqs' => [],
                'galleryCount' => 4,
            ],
        ];
    }

    public static function article(string $slug): ?array
    {
        return Arr::first(static::articles(), fn ($a) => $a['slug'] === $slug);
    }

    public static function articlesByCountry(string $countrySlug): array
    {
        return array_values(array_filter(static::articles(), fn ($a) => $a['countrySlug'] === $countrySlug));
    }

    public static function testimonials(): array
    {
        return [
            ['name' => 'Nguyễn Minh Anh', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 5.0, 'quote' => 'Từ lúc lên lịch trình đến khi kết thúc chuyến đi, mọi thứ đều chỉn chu. Hướng dẫn viên am hiểu và cực kỳ nhiệt tình.', 'photos' => 5, 'trip' => 'Việt Nam 10 ngày'],
            ['name' => 'Sarah Mitchell', 'country' => 'Úc', 'flag' => '🇦🇺', 'rating' => 5.0, 'quote' => 'Chuyến đi Việt Nam – Campuchia 15 ngày vượt xa kỳ vọng. Angkor lúc bình minh thật sự kỳ diệu.', 'photos' => 8, 'trip' => 'Việt Nam & Campuchia 15 ngày'],
            ['name' => 'Trần Quốc Bảo', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.9, 'quote' => 'Du thuyền Lan Hạ yên tĩnh, đồ ăn ngon, nhân viên chu đáo. Sẽ quay lại cùng gia đình.', 'photos' => 4, 'trip' => 'Du thuyền Lan Hạ 3 ngày'],
            ['name' => 'Claude Millet', 'country' => 'Pháp', 'flag' => '🇫🇷', 'rating' => 5.0, 'quote' => 'Đội ngũ tư vấn phản hồi trong vòng vài giờ và điều chỉnh lịch trình theo đúng mong muốn của chúng tôi.', 'photos' => 6, 'trip' => 'Xuyên Việt 2 tuần'],
            ['name' => 'Lê Hoài Nam', 'country' => 'Việt Nam', 'flag' => '🇻🇳', 'rating' => 4.8, 'quote' => 'Trekking Sa Pa 4 ngày là trải nghiệm đáng nhớ nhất năm của tôi. Homestay ấm cúng, cảnh đẹp mê hồn.', 'photos' => 7, 'trip' => 'Sa Pa trekking 4 ngày'],
            ['name' => 'Emma Rossi', 'country' => 'Ý', 'flag' => '🇮🇹', 'rating' => 5.0, 'quote' => 'Một công ty địa phương thực sự hiểu khách châu Âu. Mọi khách sạn đều được chọn rất tinh tế.', 'photos' => 3, 'trip' => 'Việt Nam 3 tuần'],
        ];
    }

    public static function reviewPlatforms(): array
    {
        return [
            ['name' => 'TripAdvisor', 'quote' => 'Xếp hạng 5/5 từ hơn 900 đánh giá — Giải thưởng Travelers\' Choice 3 năm liên tiếp.', 'link' => 'Đọc đánh giá trên TripAdvisor'],
            ['name' => 'Google', 'quote' => '4.9/5 trên Google Maps với hơn 600 nhận xét từ du khách khắp thế giới.', 'link' => 'Xem đánh giá trên Google Maps'],
            ['name' => 'Trustpilot', 'quote' => 'Điểm "Xuất sắc" trên Trustpilot — 96% khách hàng chấm 5 sao.', 'link' => 'Đọc đánh giá trên Trustpilot'],
        ];
    }

    public static function team(): array
    {
        return [
            ['name' => 'Phạm Thu Trang', 'role' => 'Giám đốc điều hành', 'bio' => 'Hơn 15 năm dẫn dắt các đoàn khách quốc tế khắp Đông Nam Á, Trang tin rằng mỗi hành trình phải kể một câu chuyện riêng...'],
            ['name' => 'Đỗ Việt Cường', 'role' => 'Trưởng phòng thiết kế tour', 'bio' => 'Cường đã đặt chân tới 63 tỉnh thành và tự tay vẽ từng lịch trình để mỗi ngày của khách đều có một điểm chạm đáng nhớ...'],
            ['name' => 'Lê Mai Chi', 'role' => 'Chuyên gia tư vấn cao cấp', 'bio' => 'Thành thạo ba ngoại ngữ, Chi là người bạn đồng hành tin cậy của các gia đình châu Âu khi đến Việt Nam lần đầu...'],
            ['name' => 'Hoàng Anh Tuấn', 'role' => 'Điều hành tuyến miền Bắc', 'bio' => 'Sinh ra ở Lào Cai, Tuấn thuộc từng khúc cua trên đèo Ô Quy Hồ và biết chính xác bản nào có mùa lúa đẹp nhất...'],
        ];
    }

    public static function videos(): array
    {
        return [
            ['title' => 'Hành trình xuyên Việt 14 ngày cùng gia đình chị Sarah', 'date' => '05/07/2026', 'duration' => '12:40'],
            ['title' => 'Một đêm trên du thuyền vịnh Lan Hạ', 'date' => '21/06/2026', 'duration' => '08:15'],
            ['title' => 'Trekking mùa lúa chín Sa Pa — nhật ký bằng hình', 'date' => '02/06/2026', 'duration' => '10:02'],
            ['title' => 'Bình minh Angkor Wat qua ống kính khách hàng', 'date' => '18/05/2026', 'duration' => '06:47'],
        ];
    }

    public static function galleryAlbums(): array
    {
        return [
            ['title' => 'Gia đình Mitchell — Việt Nam & Campuchia 15 ngày', 'photos' => 24, 'date' => '07/2026'],
            ['title' => 'Đoàn khách Ý — Xuyên Việt 3 tuần', 'photos' => 36, 'date' => '06/2026'],
            ['title' => 'Trăng mật Tú & Ngân — Phú Quốc', 'photos' => 18, 'date' => '06/2026'],
            ['title' => 'Nhóm trekking Sa Pa mùa lúa', 'photos' => 29, 'date' => '05/2026'],
            ['title' => 'Du thuyền Hạ Long — kỷ niệm 20 năm ngày cưới', 'photos' => 15, 'date' => '04/2026'],
            ['title' => 'Đoàn khách Pháp — Mekong hai quốc gia', 'photos' => 31, 'date' => '03/2026'],
        ];
    }

    public static function usps(): array
    {
        $locale = app()->getLocale();
        $code = $locale === 'en' ? 'en' : 'vi';

        return array_map(fn (array $row) => [
            'icon' => $row['icon'],
            'title' => $row[$code]['title'],
            'desc' => $row[$code]['description'],
        ], HomePageDefaults::usps());
    }

    public static function offices(): array
    {
        return [
            ['city' => 'Hà Nội, Việt Nam', 'address' => 'Tầng 5, 88 Xã Đàn, Đống Đa', 'phone' => '+84 24 3999 8888'],
            ['city' => 'TP. Hồ Chí Minh, Việt Nam', 'address' => '125 Nguyễn Huệ, Quận 1', 'phone' => '+84 28 3888 9999'],
            ['city' => 'Siem Reap, Campuchia', 'address' => 'Sivutha Blvd, Svay Dangkum', 'phone' => '+855 63 96 8888'],
        ];
    }

    public static function values(): array
    {
        return [
            ['name' => 'Tận tâm', 'desc' => 'Mỗi chuyến đi được chăm chút như dành cho người thân'],
            ['name' => 'Thấu cảm', 'desc' => 'Lắng nghe để hiểu điều bạn thực sự mong muốn'],
            ['name' => 'Chân thành', 'desc' => 'Tư vấn trung thực, giá cả minh bạch'],
            ['name' => 'Trách nhiệm', 'desc' => 'Du lịch bền vững, tôn trọng cộng đồng bản địa'],
        ];
    }

    public static function reasons(): array
    {
        return [
            ['title' => 'Chuyên gia bản địa thiết kế tour riêng', 'desc' => 'Đội ngũ sinh ra và lớn lên tại điểm đến, hiểu từng cung đường và mùa đẹp nhất.'],
            ['title' => 'Cam kết hoàn tiền rõ ràng', 'desc' => 'Chính sách huỷ/hoàn minh bạch, được ghi rõ trong hợp đồng trước khi thanh toán.'],
            ['title' => 'Giá trị vượt trội trên từng đồng chi phí', 'desc' => 'Làm việc trực tiếp với khách sạn, nhà thuyền — không qua trung gian.'],
            ['title' => 'Hỗ trợ 24/7 trong suốt hành trình', 'desc' => 'Hotline và WhatsApp trực người thật, phản hồi trong vòng 15 phút.'],
            ['title' => 'Du lịch có trách nhiệm & bền vững', 'desc' => 'Ưu tiên homestay bản địa, hạn chế nhựa dùng một lần trên mọi tour.'],
        ];
    }

    public static function referencePersons(): array
    {
        return [
            ['name' => 'Mr. Claude Millet', 'country' => 'Pháp', 'email' => 'claude@vitravel.example', 'phone' => '+33 6 12 34 56 78', 'skype' => 'claude.millet'],
            ['name' => 'Ms. Emma Rossi', 'country' => 'Ý', 'email' => 'emma@vitravel.example', 'phone' => '+39 320 123 4567', 'skype' => 'emma.rossi.travel'],
            ['name' => 'Mr. David Chen', 'country' => 'Úc', 'email' => 'david@vitravel.example', 'phone' => '+61 4 1234 5678', 'skype' => 'david.chen.au'],
        ];
    }

    public static function heroPills(): array
    {
        $locale = app()->getLocale();
        $code = $locale === 'en' ? 'en' : 'vi';

        return array_map(fn (array $row) => [
            'label' => $row[$code]['label'] ?? $row['vi']['label'],
            'url' => $row['url'],
        ], self::heroPillDefinitions());
    }

    /** @return list<array<string, mixed>> */
    protected static function heroPillDefinitions(): array
    {
        return [
            [
                'category_slug' => 'viet-nam-3-tuan',
                'vi' => ['label' => 'Việt Nam 3 tuần'],
                'en' => ['label' => 'Vietnam 3 Weeks'],
                'url' => '/tours/viet-nam/viet-nam-3-tuan',
            ],
            [
                'country_slug' => 'tour-ket-hop',
                'vi' => ['label' => 'Tour kết hợp'],
                'en' => ['label' => 'Combined Tours'],
                'url' => '/tours/tour-ket-hop',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function homeSlides(): array
    {
        if (app()->getLocale() === 'en') {
            return [
                [
                    'image' => null,
                    'imageMobile' => null,
                    'imageAlt' => 'Halong Bay at sunset',
                    'title' => 'Northern Vietnam',
                    'titleAccent' => 'your way',
                    'description' => 'Fully inclusive tours & private journeys through Halong, Sapa, Ninh Binh and Ha Giang — designed by local experts.',
                    'buttonLabel' => null,
                    'linkUrl' => null,
                    'textAlign' => 'center',
                ],
            ];
        }

        return [
            [
                'image' => null,
                'imageMobile' => null,
                'imageAlt' => 'Vịnh Hạ Long lúc hoàng hôn',
                'title' => 'Miền Bắc Việt Nam',
                'titleAccent' => 'theo cách của bạn',
                'description' => 'Tour trọn gói & hành trình riêng qua Hạ Long, Sa Pa, Ninh Bình, Hà Giang — thiết kế bởi chuyên gia bản địa.',
                'buttonLabel' => null,
                'linkUrl' => null,
                'textAlign' => 'center',
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public static function homeSections(): array
    {
        $locale = app()->getLocale();
        $sections = [];

        foreach (self::homeSectionDefinitions() as $key => $byLocale) {
            $sections[$key] = $byLocale[$locale] ?? $byLocale['vi'];
        }

        return $sections;
    }

    /** @return array<string, mixed> */
    public static function homeSection(string $key): array
    {
        return static::homeSections()[$key] ?? [];
    }

    /** @return array<string, array<string, array<string, mixed>>> */
    protected static function homeSectionDefinitions(): array
    {
        return [
            'company_intro' => [
                'vi' => [
                    'key' => 'company_intro',
                    'eyebrow' => 'Chuyên gia du lịch miền Bắc',
                    'title' => 'Hành trình chân thật, thiết kế bởi người bản địa',
                    'subtitle' => null,
                    'body' => 'ViTravel là đại lý lữ hành đặt trụ sở tại Hà Nội, với hơn 10 năm đồng hành cùng du khách khám phá miền Bắc Việt Nam — từ vịnh Hạ Long, Sa Pa, Ninh Bình tới cao nguyên đá Hà Giang. Chúng tôi không bán những tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của đội ngũ chuyên gia sinh ra và lớn lên tại chính điểm đến.',
                    'metaLine' => 'Giấy phép lữ hành quốc tế số 01-2234/TCDL-GP-LHQT',
                    'ctaLabel' => 'Tìm hiểu về chúng tôi',
                    'ctaUrl' => url('/ve-chung-toi'),
                    'image' => null,
                    'imageAlt' => 'Ảnh đội ngũ ViTravel tại văn phòng Hà Nội',
                ],
                'en' => [
                    'key' => 'company_intro',
                    'eyebrow' => 'Northern Vietnam travel experts',
                    'title' => 'Authentic journeys, designed by locals',
                    'subtitle' => null,
                    'body' => 'ViTravel is a Hanoi-based travel agency with over 10 years of guiding guests through Northern Vietnam — from Halong Bay, Sapa and Ninh Binh to the karst highlands of Ha Giang. We do not sell off-the-shelf packages — every itinerary is tailored from real on-the-ground experience.',
                    'metaLine' => 'International travel license No. 01-2234/TCDL-GP-LHQT',
                    'ctaLabel' => 'Learn about us',
                    'ctaUrl' => url('/ve-chung-toi'),
                    'image' => null,
                    'imageAlt' => 'ViTravel team at our Hanoi office',
                ],
            ],
            'featured_tours' => [
                'vi' => [
                    'key' => 'featured_tours',
                    'eyebrow' => 'Được yêu thích nhất',
                    'title' => 'Những tour được yêu cầu nhiều nhất',
                    'subtitle' => 'Ba hành trình được khách hàng đặt và đánh giá cao nhất trong 12 tháng qua.',
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => null,
                    'ctaUrl' => null,
                    'image' => null,
                    'imageAlt' => null,
                ],
                'en' => [
                    'key' => 'featured_tours',
                    'eyebrow' => 'Most popular',
                    'title' => 'Our most requested tours',
                    'subtitle' => 'Three itineraries our guests book and rate highest over the past 12 months.',
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => null,
                    'ctaUrl' => null,
                    'image' => null,
                    'imageAlt' => null,
                ],
            ],
            'destinations' => [
                'vi' => [
                    'key' => 'destinations',
                    'eyebrow' => null,
                    'title' => 'Những điểm đến được yêu thích nhất',
                    'subtitle' => null,
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => null,
                    'ctaUrl' => null,
                    'image' => null,
                    'imageAlt' => null,
                ],
                'en' => [
                    'key' => 'destinations',
                    'eyebrow' => null,
                    'title' => 'Our most loved destinations',
                    'subtitle' => null,
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => null,
                    'ctaUrl' => null,
                    'image' => null,
                    'imageAlt' => null,
                ],
            ],
            'testimonials' => [
                'vi' => [
                    'key' => 'testimonials',
                    'eyebrow' => 'Khách hàng kể lại',
                    'title' => 'Trải nghiệm chân thật từ khách hàng',
                    'subtitle' => 'Hơn 5.000 du khách đã đồng hành cùng chúng tôi — đây là những gì họ kể lại.',
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => 'Xem tất cả cảm nhận',
                    'ctaUrl' => url('/cam-nhan-khach-hang'),
                    'image' => null,
                    'imageAlt' => null,
                ],
                'en' => [
                    'key' => 'testimonials',
                    'eyebrow' => 'Guest stories',
                    'title' => 'Real experiences from our travellers',
                    'subtitle' => 'Over 5,000 guests have travelled with us — here is what they say.',
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => 'Read all reviews',
                    'ctaUrl' => url('/cam-nhan-khach-hang'),
                    'image' => null,
                    'imageAlt' => null,
                ],
            ],
            'review_platforms' => [
                'vi' => [
                    'key' => 'review_platforms',
                    'eyebrow' => null,
                    'title' => 'ViTravel được đánh giá cao trên',
                    'subtitle' => null,
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => null,
                    'ctaUrl' => null,
                    'image' => null,
                    'imageAlt' => null,
                ],
                'en' => [
                    'key' => 'review_platforms',
                    'eyebrow' => null,
                    'title' => 'ViTravel is highly rated on',
                    'subtitle' => null,
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => null,
                    'ctaUrl' => null,
                    'image' => null,
                    'imageAlt' => null,
                ],
            ],
            'team' => [
                'vi' => [
                    'key' => 'team',
                    'eyebrow' => 'Con người ViTravel',
                    'title' => 'Đội ngũ tận tâm của chúng tôi',
                    'subtitle' => 'Những con người bản địa hiểu điểm đến hơn bất kỳ ai — và sẽ đồng hành cùng bạn từ lúc lên ý tưởng tới khi về nhà.',
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => 'Gặp gỡ cả đội ngũ',
                    'ctaUrl' => url('/doi-ngu'),
                    'image' => null,
                    'imageAlt' => null,
                ],
                'en' => [
                    'key' => 'team',
                    'eyebrow' => 'The ViTravel team',
                    'title' => 'Our dedicated local experts',
                    'subtitle' => 'People who know the destinations better than anyone — with you from the first idea until you are home.',
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => 'Meet the full team',
                    'ctaUrl' => url('/doi-ngu'),
                    'image' => null,
                    'imageAlt' => null,
                ],
            ],
            'videos' => [
                'vi' => [
                    'key' => 'videos',
                    'eyebrow' => null,
                    'title' => 'Video trải nghiệm chân thật',
                    'subtitle' => null,
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => 'Xem tất cả video',
                    'ctaUrl' => url('/video-trai-nghiem'),
                    'image' => null,
                    'imageAlt' => null,
                ],
                'en' => [
                    'key' => 'videos',
                    'eyebrow' => null,
                    'title' => 'Authentic experience videos',
                    'subtitle' => null,
                    'body' => null,
                    'metaLine' => null,
                    'ctaLabel' => 'View all videos',
                    'ctaUrl' => url('/video-trai-nghiem'),
                    'image' => null,
                    'imageAlt' => null,
                ],
            ],
        ];
    }

    public static function footerColumns(): array
    {
        return [
            [
                'title' => 'ViTravel',
                'links' => [
                    ['label' => 'Về chúng tôi', 'route' => ['about']],
                    ['label' => 'Cảm nhận khách hàng', 'route' => ['reviews']],
                    ['label' => 'Đội ngũ của chúng tôi', 'route' => ['team']],
                    ['label' => 'Thư viện khoảnh khắc', 'route' => ['gallery']],
                    ['label' => 'Nhận báo giá miễn phí', 'route' => ['customize']],
                ],
            ],
            [
                'title' => 'Tour được yêu thích',
                'links' => [
                    ['label' => 'Việt Nam 10 ngày', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'viet-nam-10-ngay-di-san-mien-bac']]],
                    ['label' => 'Xuyên Việt 2 tuần', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'viet-nam-2-tuan-bac-trung-nam']]],
                    ['label' => 'Việt Nam & Campuchia 15 ngày', 'route' => ['tours.show', ['country' => 'tour-ket-hop', 'slug' => 'viet-nam-campuchia-15-ngay']]],
                    ['label' => 'Việt Nam 3 tuần trọn vẹn', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'viet-nam-3-tuan-tron-ven']]],
                ],
            ],
            [
                'title' => 'Điểm đến nổi bật',
                'links' => [
                    ['label' => 'Vịnh Hạ Long', 'route' => ['cruises.index', ['type' => 'du-thuyen-ha-long']]],
                    ['label' => 'Sa Pa', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'sa-pa-trekking-4-ngay']]],
                    ['label' => 'Hội An', 'route' => ['guide.country', ['country' => 'viet-nam']]],
                    ['label' => 'Phú Quốc', 'route' => ['tours.show', ['country' => 'viet-nam', 'slug' => 'phu-quoc-nghi-duong-5-ngay']]],
                    ['label' => 'Đồng bằng sông Cửu Long', 'route' => ['cruises.index', ['type' => 'du-thuyen-mekong']]],
                ],
            ],
            [
                'title' => 'Cẩm nang du lịch',
                'links' => [
                    ['label' => 'Chi phí du lịch Việt Nam 10 ngày', 'route' => ['guide.show', ['country' => 'viet-nam', 'slug' => 'chi-phi-du-lich-viet-nam-10-ngay']]],
                    ['label' => 'Sa Pa mùa nào đẹp nhất?', 'route' => ['guide.show', ['country' => 'viet-nam', 'slug' => 'sa-pa-mua-nao-dep-nhat']]],
                    ['label' => 'Ăn gì ở Hội An trong 24 giờ', 'route' => ['guide.show', ['country' => 'viet-nam', 'slug' => 'an-gi-o-hoi-an-24-gio']]],
                    ['label' => 'Bình minh Angkor Wat', 'route' => ['guide.show', ['country' => 'campuchia', 'slug' => 'angkor-wat-kinh-nghiem-binh-minh']]],
                ],
            ],
        ];
    }

    public static function footerSeoLinks(): array
    {
        return [
            ['label' => 'Cẩm nang du lịch Việt Nam', 'route' => ['guide.country', ['country' => 'viet-nam']]],
            ['label' => 'Cẩm nang du lịch Campuchia', 'route' => ['guide.country', ['country' => 'campuchia']]],
            ['label' => 'Cẩm nang du lịch Lào', 'route' => ['guide.country', ['country' => 'lao']]],
            ['label' => 'Tour Việt Nam trọn gói', 'route' => ['tours.index', ['country' => 'viet-nam']]],
            ['label' => 'Du thuyền Hạ Long', 'route' => ['cruises.index', ['type' => 'du-thuyen-ha-long']]],
            ['label' => 'Du thuyền Mekong', 'route' => ['cruises.index', ['type' => 'du-thuyen-mekong']]],
            ['label' => 'Thiết kế tour riêng', 'route' => ['customize']],
            ['label' => 'Video trải nghiệm', 'route' => ['videos']],
        ];
    }

    /**
     * Danh mục tour con (TourCategory) — trang listing /tours/{country}/{slug}.
     *
     * @return list<array<string, mixed>>
     */
    public static function tourCategories(): array
    {
        return [
            // —— Việt Nam ——
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'viet-nam-10-ngay',
                'type' => 'duration',
                'sort' => 0,
                'minDays' => 9,
                'maxDays' => 11,
                'name' => ['vi' => 'Tour Việt Nam 10 ngày', 'en' => 'Vietnam 10 Days Tours'],
                'description' => [
                    'vi' => 'Lựa chọn phổ biến nhất cho lần đầu khám phá Việt Nam — đủ thời gian đi Hà Nội, Hạ Long và Sa Pa.',
                    'en' => 'The most popular choice for first-time visitors — enough time for Hanoi, Halong Bay and Sapa.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Việt Nam 10 ngày là lịch trình lý tưởng để cảm nhận miền Bắc: di sản, ẩm thực và cảnh quan thiên nhiên trong một hành trình cân bằng.',
                    'en' => 'A 10-day Vietnam tour is the ideal itinerary to experience the north: heritage, cuisine and nature in one balanced journey.',
                ],
                'faqs' => [
                    ['q' => 'Tour 10 ngày Việt Nam đi được những đâu?', 'a' => 'Thường gồm Hà Nội, Ninh Bình, vịnh Hạ Long, Sa Pa hoặc Mai Châu — có thể tùy chỉnh thêm Hội An nếu bay nội địa.'],
                    ['q' => '10 ngày có đủ cho gia đình có trẻ nhỏ?', 'a' => 'Có. Lịch trình có ngày nghỉ xen kẽ, trekking Sa Pa có phương án đi xe thay thế.'],
                ],
            ],
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'viet-nam-2-tuan',
                'type' => 'duration',
                'sort' => 1,
                'minDays' => 12,
                'maxDays' => 16,
                'name' => ['vi' => 'Tour Việt Nam 2 tuần', 'en' => 'Vietnam 2 Weeks Tours'],
                'description' => [
                    'vi' => 'Xuyên Việt Bắc – Trung – Nam trong 14 ngày, phù hợp ai muốn thấy trọn vẹn ba miền.',
                    'en' => 'Cross Vietnam north to south in 14 days — ideal for seeing all three regions.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Việt Nam 2 tuần kết nối Hà Nội, Huế, Hội An và TP. Hồ Chí Minh — hành trình kinh điển được đặt nhiều nhất.',
                    'en' => 'A two-week Vietnam tour links Hanoi, Hue, Hoi An and Ho Chi Minh City — our classic best-selling route.',
                ],
                'faqs' => [
                    ['q' => 'Tour 2 tuần có mấy chặng bay nội địa?', 'a' => 'Thường 1–2 chặng (Hà Nội–Huế/Đà Nẵng và Đà Nẵng–TP.HCM), đã bao gồm trong giá tour trọn gói.'],
                ],
            ],
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'viet-nam-15-ngay',
                'type' => 'duration',
                'sort' => 2,
                'minDays' => 14,
                'maxDays' => 16,
                'name' => ['vi' => 'Tour Việt Nam 15 ngày', 'en' => 'Vietnam 15 Days Tours'],
                'description' => [
                    'vi' => 'Thêm thời gian cho miền Trung và Đồng bằng sông Cửu Long so với lịch trình 10 ngày.',
                    'en' => 'Extra time for central Vietnam and the Mekong Delta compared to a 10-day plan.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Việt Nam 15 ngày cho phép đi chậm hơn, ở lâu hơn ở Hội An và khám phá sâu miền Tây.',
                    'en' => 'A 15-day Vietnam tour lets you travel slower, stay longer in Hoi An and explore the Mekong in depth.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'viet-nam-3-tuan',
                'type' => 'duration',
                'sort' => 3,
                'minDays' => 17,
                'maxDays' => 25,
                'name' => ['vi' => 'Tour Việt Nam 3 tuần', 'en' => 'Vietnam 3 Weeks Tours'],
                'description' => [
                    'vi' => 'Hành trình trọn vẹn nhất: Hà Giang, cố đô Huế, Đà Lạt và mũi Cà Mau.',
                    'en' => 'The most complete journey: Ha Giang, imperial Hue, Da Lat and Ca Mau cape.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Việt Nam 3 tuần dành cho du khách muốn trải nghiệm sâu từng vùng miền mà không vội vàng.',
                    'en' => 'A 3-week Vietnam tour is for travellers who want an in-depth, unhurried experience of every region.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'duoi-7-ngay',
                'type' => 'duration',
                'sort' => 4,
                'minDays' => 1,
                'maxDays' => 8,
                'name' => ['vi' => 'Tour Việt Nam dưới 7 ngày', 'en' => 'Vietnam Short Tours (under 7 days)'],
                'description' => [
                    'vi' => 'Tour ngắn ngày: Sa Pa trekking, Phú Quốc nghỉ dưỡng, Hạ Long 2N1D.',
                    'en' => 'Short breaks: Sapa trekking, Phu Quoc beach, Halong 2D1N.',
                ],
                'seoIntro' => [
                    'vi' => 'Các tour Việt Nam dưới 7 ngày phù hợp nghỉ phép ngắn hoặc kết hợp công tác.',
                    'en' => 'Vietnam tours under 7 days suit short holidays or business-trip extensions.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'mien-bac',
                'type' => 'region',
                'sort' => 10,
                'packageSlugs' => ['viet-nam-10-ngay-di-san-mien-bac', 'sa-pa-trekking-4-ngay'],
                'name' => ['vi' => 'Tour miền Bắc', 'en' => 'Northern Vietnam Tours'],
                'description' => [
                    'vi' => 'Hà Nội, Hạ Long, Sa Pa, Ninh Bình và các vùng cao nguyên phía Bắc.',
                    'en' => 'Hanoi, Halong Bay, Sapa, Ninh Binh and the northern highlands.',
                ],
                'seoIntro' => [
                    'vi' => 'Miền Bắc Việt Nam nổi bật với di sản thiên nhiên Hạ Long, văn hoá bản địa Tây Bắc và ẩm thực phố cổ.',
                    'en' => 'Northern Vietnam is known for Halong Bay, northwest ethnic culture and Hanoi street food.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'viet-nam',
                'slug' => 'mien-trung',
                'type' => 'region',
                'sort' => 11,
                'packageSlugs' => ['viet-nam-2-tuan-bac-trung-nam'],
                'name' => ['vi' => 'Tour miền Trung', 'en' => 'Central Vietnam Tours'],
                'description' => [
                    'vi' => 'Huế, Đà Nẵng, Hội An và di sản miền Trung.',
                    'en' => 'Hue, Da Nang, Hoi An and central heritage sites.',
                ],
                'seoIntro' => [
                    'vi' => 'Miền Trung mang đến cố đô Huế, phố cổ Hội An và bãi biển Mỹ Khê — trái tim di sản Việt Nam.',
                    'en' => 'Central Vietnam offers imperial Hue, ancient Hoi An and My Khe beach — the heart of Vietnamese heritage.',
                ],
                'faqs' => [],
            ],
            // —— Campuchia ——
            [
                'countrySlug' => 'campuchia',
                'slug' => 'campuchia-7-ngay',
                'type' => 'duration',
                'sort' => 0,
                'minDays' => 5,
                'maxDays' => 9,
                'name' => ['vi' => 'Tour Campuchia 7 ngày', 'en' => 'Cambodia 7 Days Tours'],
                'description' => [
                    'vi' => 'Siem Reap & Angkor trong một tuần — lịch trình gọn cho người bận rộn.',
                    'en' => 'Siem Reap & Angkor in one week — a compact itinerary for busy travellers.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Campuchia 7 ngày tập trung vào quần thể Angkor và làng nổi Tonlé Sap.',
                    'en' => 'A 7-day Cambodia tour focuses on the Angkor complex and Tonlé Sap floating villages.',
                ],
                'faqs' => [
                    ['q' => '7 ngày ở Campuchia có cần visa không?', 'a' => 'Khách Việt Nam được miễn visa. Khách quốc tịch khác có thể làm e-visa online — chúng tôi hỗ trợ trọn gói.'],
                ],
            ],
            [
                'countrySlug' => 'campuchia',
                'slug' => 'campuchia-10-ngay',
                'type' => 'duration',
                'sort' => 1,
                'minDays' => 9,
                'maxDays' => 14,
                'name' => ['vi' => 'Tour Campuchia 10 ngày', 'en' => 'Cambodia 10 Days Tours'],
                'description' => [
                    'vi' => 'Angkor, Phnom Penh và biển hồ Sihanoukville hoặc Kampot.',
                    'en' => 'Angkor, Phnom Penh and the coast at Sihanoukville or Kampot.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Campuchia 10 ngày kết hợp di sản Khmer và nhịp sống thủ đô Phnom Penh.',
                    'en' => 'A 10-day Cambodia tour combines Khmer heritage with the rhythm of capital Phnom Penh.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'campuchia',
                'slug' => 'di-san-angkor',
                'type' => 'theme',
                'sort' => 2,
                'name' => ['vi' => 'Tour di sản Angkor', 'en' => 'Angkor Heritage Tours'],
                'description' => [
                    'vi' => 'Chuyên sâu đền tháp Angkor Wat, Bayon, Ta Prohm và Banteay Srei.',
                    'en' => 'In-depth temples: Angkor Wat, Bayon, Ta Prohm and Banteay Srei.',
                ],
                'seoIntro' => [
                    'vi' => 'Các tour di sản Angkor được thiết kế cho người yêu lịch sử và nhiếp ảnh — bình minh tại Angkor Wat là điểm nhấn.',
                    'en' => 'Angkor heritage tours are designed for history and photography lovers — sunrise at Angkor Wat is the highlight.',
                ],
                'faqs' => [],
            ],
            // —— Bali ——
            [
                'countrySlug' => 'bali',
                'slug' => 'bali-7-ngay',
                'type' => 'duration',
                'sort' => 0,
                'minDays' => 5,
                'maxDays' => 9,
                'name' => ['vi' => 'Tour Bali 7 ngày', 'en' => 'Bali 7 Days Tours'],
                'description' => [
                    'vi' => 'Ubud, đền Tanah Lot và bãi biển Seminyak trong một tuần.',
                    'en' => 'Ubud, Tanah Lot temple and Seminyak beach in one week.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Bali 7 ngày cân bằng giữa văn hoá đền chùa và thời gian thư giãn bên biển.',
                    'en' => 'A 7-day Bali tour balances temple culture with beach relaxation.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'bali',
                'slug' => 'bali-10-ngay',
                'type' => 'duration',
                'sort' => 1,
                'minDays' => 9,
                'maxDays' => 14,
                'name' => ['vi' => 'Tour Bali 10 ngày', 'en' => 'Bali 10 Days Tours'],
                'description' => [
                    'vi' => 'Khám phá Ubud, Nusa Penida và các resort biển phía nam.',
                    'en' => 'Explore Ubud, Nusa Penida and southern beach resorts.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Bali 10 ngày cho phép thêm ngày nghỉ resort và chuyến đi Nusa Penida trong ngày.',
                    'en' => 'A 10-day Bali tour adds resort time and a Nusa Penida day trip.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'bali',
                'slug' => 'nghi-duong-bali',
                'type' => 'theme',
                'sort' => 2,
                'name' => ['vi' => 'Tour nghỉ dưỡng Bali', 'en' => 'Bali Beach & Wellness Tours'],
                'description' => [
                    'vi' => 'Resort 5 sao, spa và bãi biển riêng tư — lý tưởng trăng mật.',
                    'en' => '5-star resorts, spa and private beaches — ideal for honeymoons.',
                ],
                'seoIntro' => [
                    'vi' => 'Các tour nghỉ dưỡng Bali kết hợp villa hướng biển, yoga và ẩm thực healthy tại Ubud.',
                    'en' => 'Bali wellness tours combine ocean-view villas, yoga and healthy cuisine in Ubud.',
                ],
                'faqs' => [],
            ],
            // —— Thái Lan ——
            [
                'countrySlug' => 'thai-lan',
                'slug' => 'thai-lan-7-ngay',
                'type' => 'duration',
                'sort' => 0,
                'minDays' => 5,
                'maxDays' => 9,
                'name' => ['vi' => 'Tour Thái Lan 7 ngày', 'en' => 'Thailand 7 Days Tours'],
                'description' => [
                    'vi' => 'Bangkok, Ayutthaya và một điểm biển (Phuket hoặc Krabi).',
                    'en' => 'Bangkok, Ayutthaya and a beach stop (Phuket or Krabi).',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Thái Lan 7 ngày là lựa chọn phổ biến kết hợp chùa vàng Bangkok với biển đảo phía nam.',
                    'en' => 'A 7-day Thailand tour is the popular mix of Bangkok temples and southern islands.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'thai-lan',
                'slug' => 'thai-lan-10-ngay',
                'type' => 'duration',
                'sort' => 1,
                'minDays' => 9,
                'maxDays' => 14,
                'name' => ['vi' => 'Tour Thái Lan 10 ngày', 'en' => 'Thailand 10 Days Tours'],
                'description' => [
                    'vi' => 'Bắc – Trung – Nam: Chiang Mai, Bangkok và biển phía nam.',
                    'en' => 'North to south: Chiang Mai, Bangkok and southern beaches.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Thái Lan 10 ngày khám phá cả văn hoá Lanna ở Chiang Mai lẫn sự sôi động của Bangkok.',
                    'en' => 'A 10-day Thailand tour covers Lanna culture in Chiang Mai and vibrant Bangkok.',
                ],
                'faqs' => [],
            ],
            // —— Lào ——
            [
                'countrySlug' => 'lao',
                'slug' => 'lao-7-ngay',
                'type' => 'duration',
                'sort' => 0,
                'minDays' => 5,
                'maxDays' => 9,
                'name' => ['vi' => 'Tour Lào 7 ngày', 'en' => 'Laos 7 Days Tours'],
                'description' => [
                    'vi' => 'Luang Prabang, Pak Ou và thác Kuang Si — nhịp sống chậm bên Mekong.',
                    'en' => 'Luang Prabang, Pak Ou caves and Kuang Si falls — slow life on the Mekong.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Lào 7 ngày tập trung vào Luang Prabang — di sản UNESCO và chợ đêm ven sông.',
                    'en' => 'A 7-day Laos tour focuses on UNESCO Luang Prabang and riverside night markets.',
                ],
                'faqs' => [],
            ],
            [
                'countrySlug' => 'lao',
                'slug' => 'lao-10-ngay',
                'type' => 'duration',
                'sort' => 1,
                'minDays' => 9,
                'maxDays' => 14,
                'name' => ['vi' => 'Tour Lào 10 ngày', 'en' => 'Laos 10 Days Tours'],
                'description' => [
                    'vi' => 'Luang Prabang, Vang Vieng và Vientiane — khám phá trọn miền trung Lào.',
                    'en' => 'Luang Prabang, Vang Vieng and Vientiane — central Laos in depth.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Lào 10 ngày thêm thời gian cho Vang Vieng và thủ đô Vientiane so với lịch trình 7 ngày.',
                    'en' => 'A 10-day Laos tour adds Vang Vieng and capital Vientiane to the 7-day route.',
                ],
                'faqs' => [],
            ],
            // —— Tour kết hợp ——
            [
                'countrySlug' => 'tour-ket-hop',
                'slug' => 'dong-duong-15-ngay',
                'type' => 'package',
                'sort' => 0,
                'minDays' => 13,
                'maxDays' => 17,
                'packageSlugs' => ['viet-nam-campuchia-15-ngay'],
                'name' => ['vi' => 'Tour Đông Dương 15 ngày', 'en' => 'Indochina 15 Days Tours'],
                'description' => [
                    'vi' => 'Việt Nam & Campuchia trong một hành trình — Angkor và Mekong.',
                    'en' => 'Vietnam & Cambodia in one journey — Angkor and the Mekong.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Đông Dương 15 ngày là hành trình bán chạy nhất, kết nối di sản Việt Nam với Angkor Wat.',
                    'en' => 'The 15-day Indochina tour is our bestseller, linking Vietnamese heritage with Angkor Wat.',
                ],
                'faqs' => [
                    ['q' => 'Tour kết hợp có cần visa cả hai nước?', 'a' => 'Chúng tôi hỗ trợ e-visa Campuchia trọn gói; khách Việt Nam miễn visa Campuchia.'],
                ],
            ],
            [
                'countrySlug' => 'tour-ket-hop',
                'slug' => 'dong-duong-3-tuan',
                'type' => 'package',
                'sort' => 1,
                'minDays' => 18,
                'maxDays' => 25,
                'name' => ['vi' => 'Tour Đông Dương 3 tuần', 'en' => 'Indochina 3 Weeks Tours'],
                'description' => [
                    'vi' => 'Việt Nam, Campuchia, Lào và Thái Lan — trọn vẹn Đông Dương.',
                    'en' => 'Vietnam, Cambodia, Laos and Thailand — full Indochina.',
                ],
                'seoIntro' => [
                    'vi' => 'Tour Đông Dương 3 tuần dành cho ai muốn một chuyến đi để đời xuyên suốt bán đảo.',
                    'en' => 'A 3-week Indochina tour is for travellers wanting a once-in-a-lifetime peninsula journey.',
                ],
                'faqs' => [],
            ],
        ];
    }

    /** @return array<string, array{vi: string, en: string, tagline: array{vi: string, en: string}}> */
    public static function countryTranslations(): array
    {
        return [
            'viet-nam' => [
                'vi' => 'Việt Nam',
                'en' => 'Vietnam',
                'tagline' => [
                    'vi' => 'Từ vịnh Hạ Long tới đồng bằng sông Cửu Long',
                    'en' => 'From Halong Bay to the Mekong Delta',
                ],
            ],
            'campuchia' => [
                'vi' => 'Campuchia',
                'en' => 'Cambodia',
                'tagline' => [
                    'vi' => 'Angkor huyền bí & biển hồ Tonlé Sap',
                    'en' => 'Mystical Angkor & Tonlé Sap',
                ],
            ],
            'bali' => [
                'vi' => 'Bali (Indonesia)',
                'en' => 'Bali (Indonesia)',
                'tagline' => [
                    'vi' => 'Đảo của các vị thần',
                    'en' => 'Island of the Gods',
                ],
            ],
            'thai-lan' => [
                'vi' => 'Thái Lan',
                'en' => 'Thailand',
                'tagline' => [
                    'vi' => 'Xứ sở chùa vàng',
                    'en' => 'Land of golden temples',
                ],
            ],
            'lao' => [
                'vi' => 'Lào',
                'en' => 'Laos',
                'tagline' => [
                    'vi' => 'Nhịp sống chậm bên dòng Mekong',
                    'en' => 'Slow life along the Mekong',
                ],
            ],
            'tour-ket-hop' => [
                'vi' => 'Tour kết hợp',
                'en' => 'Multi-country Tours',
                'tagline' => [
                    'vi' => 'Đông Dương trong một hành trình',
                    'en' => 'Indochina in one journey',
                ],
            ],
        ];
    }

    public static function listingFaqs(): array
    {
        return [
            ['q' => 'Thời tiết Việt Nam tháng nào đẹp nhất để đi tour trọn gói?', 'a' => 'Tháng 9 – 11 và tháng 3 – 4 là thời điểm dễ chịu trên cả ba miền. Miền Bắc đẹp nhất mùa thu, miền Nam khô ráo từ tháng 12 đến tháng 4.'],
            ['q' => 'Tour trọn gói đã bao gồm vé máy bay quốc tế chưa?', 'a' => 'Chưa — giá tour bao gồm toàn bộ dịch vụ mặt đất, bữa ăn theo chương trình và các chặng bay nội địa (nếu có ghi rõ). Chúng tôi có thể hỗ trợ săn vé quốc tế giá tốt.'],
            ['q' => 'Tôi có thể đặt tour riêng (private) cho gia đình không?', 'a' => 'Được. Mọi tour trên website đều có phương án private với xe riêng và hướng dẫn viên riêng — dùng form "Thiết kế tour riêng" để nhận báo giá trong 24 giờ.'],
            ['q' => 'Chính sách huỷ tour như thế nào?', 'a' => 'Miễn phí huỷ trước 30 ngày khởi hành; trước 15 ngày thu 25%; trước 7 ngày thu 50%. Chi tiết được ghi rõ trong hợp đồng.'],
            ['q' => 'Có cần visa khi đi tour kết hợp Việt Nam – Campuchia?', 'a' => 'Khách Việt Nam được miễn visa Campuchia. Khách quốc tịch khác được chúng tôi hỗ trợ e-visa trọn gói, thường có kết quả trong 3 ngày làm việc.'],
        ];
    }
}
