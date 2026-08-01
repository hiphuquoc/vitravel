<?php

namespace App\Support;

/**
 * Chrome (eyebrow / title / subtitle / SEO) cho các trang tĩnh ngoài trang chủ.
 * VI + EN — dùng qua SampleData::pageChrome() / ViewDataService::pageChrome().
 */
class PageChromeDefaults
{
    /**
     * @return array<string, array{vi: array<string, mixed>, en: array<string, mixed>}>
     */
    public static function all(): array
    {
        return [
            'about' => [
                'vi' => [
                    'seo_title' => 'Về chúng tôi — ViTravel, kết nối du khách quốc tế với Việt Nam & Đông Nam Á',
                    'seo_description' => 'Câu chuyện, sứ mệnh và đội ngũ ViTravel — thiết kế hành trình riêng tại Việt Nam và các điểm đến Đông Nam Á cho du khách quốc tế.',
                    'page_title' => 'Về chúng tôi',
                    'page_subtitle' => 'Hành trình chân thật tại Việt Nam & Đông Nam Á — thiết kế bởi người bản địa yêu nghề',
                    'banner_label' => 'Ảnh banner: đội ngũ ViTravel',
                    'eyebrow' => 'Câu chuyện ViTravel',
                ],
                'en' => [
                    'seo_title' => 'About us — ViTravel, connecting international travellers with Vietnam & Southeast Asia',
                    'seo_description' => 'Our story, mission and team at ViTravel — designing private journeys across Vietnam and Southeast Asia for international guests.',
                    'page_title' => 'About us',
                    'page_subtitle' => 'Authentic journeys in Vietnam & Southeast Asia — designed by locals who love their craft',
                    'banner_label' => 'Banner: the ViTravel team',
                    'eyebrow' => 'The ViTravel story',
                ],
            ],
            'team' => [
                'vi' => [
                    'seo_title' => 'Đội ngũ của chúng tôi — ViTravel',
                    'seo_description' => 'Gặp gỡ chuyên gia bản địa ViTravel — những người thiết kế và đồng hành cùng hành trình Việt Nam & Đông Nam Á của bạn.',
                    'page_title' => 'Đội ngũ của chúng tôi',
                    'page_subtitle' => 'Người bản địa yêu nghề — thiết kế và chăm chút từng ngày trên đường',
                    'banner_label' => 'Ảnh banner: đội ngũ ViTravel',
                    'eyebrow' => 'Con người ViTravel',
                    'section_title' => 'Những khuôn mặt đứng sau mỗi hành trình',
                    'section_subtitle' => 'Từ tư vấn viên đến điều hành tuyến — cùng một cam kết: chuyến đi suôn sẻ, đáng nhớ.',
                ],
                'en' => [
                    'seo_title' => 'Our team — ViTravel',
                    'seo_description' => 'Meet ViTravel’s local experts — the people who design and guide your Vietnam & Southeast Asia journey.',
                    'page_title' => 'Our team',
                    'page_subtitle' => 'Locals who love their craft — shaping every day on the road with care',
                    'banner_label' => 'Banner: the ViTravel team',
                    'eyebrow' => 'The ViTravel people',
                    'section_title' => 'The faces behind every itinerary',
                    'section_subtitle' => 'From trip designers to on-route managers — one promise: a smooth, memorable journey.',
                ],
            ],
            'reviews' => [
                'vi' => [
                    'seo_title' => 'Cảm nhận khách hàng — ViTravel',
                    'seo_description' => 'Du khách quốc tế đã khám phá Việt Nam và Đông Nam Á cùng ViTravel — đọc cảm nhận thật sau mỗi chuyến đi.',
                    'page_title' => 'Cảm nhận khách hàng',
                    'page_subtitle' => 'Trải nghiệm thật — không chỉnh sửa, không kịch bản',
                    'banner_label' => 'Ảnh banner: khách hàng ViTravel',
                    'eyebrow' => 'Khách hàng kể lại',
                    'section_title' => 'Những lời gửi lại sau chuyến đi',
                    'section_subtitle' => 'Mỗi đánh giá là một câu chuyện có thật từ hành trình cùng chúng tôi.',
                ],
                'en' => [
                    'seo_title' => 'Guest reviews — ViTravel',
                    'seo_description' => 'International travellers have explored Vietnam and Southeast Asia with ViTravel — read real reviews after every trip.',
                    'page_title' => 'Guest reviews',
                    'page_subtitle' => 'Real experiences — unedited, unscripted',
                    'banner_label' => 'Banner: ViTravel guests',
                    'eyebrow' => 'Guest stories',
                    'section_title' => 'Words left after the journey',
                    'section_subtitle' => 'Every review is a true story from a trip with us.',
                ],
            ],
            'gallery' => [
                'vi' => [
                    'seo_title' => 'Thư viện khoảnh khắc — ViTravel',
                    'seo_description' => 'Album ảnh trải nghiệm thật từ chuyến đi Việt Nam và Đông Nam Á cùng ViTravel.',
                    'page_title' => 'Thư viện khoảnh khắc',
                    'page_subtitle' => 'Album từ những chuyến đi có thật của khách hàng chúng tôi',
                    'banner_label' => 'Ảnh banner: thư viện khoảnh khắc',
                    'eyebrow' => 'Khoảnh khắc thật',
                    'section_title' => 'Việt Nam & Đông Nam Á qua ống kính khách hàng',
                    'section_subtitle' => 'Những khung hình chân thật — từ vịnh biển Việt Nam đến đền Angkor, đảo Bali.',
                ],
                'en' => [
                    'seo_title' => 'Photo gallery — ViTravel',
                    'seo_description' => 'Real photo albums from Vietnam and Southeast Asia trips with ViTravel.',
                    'page_title' => 'Photo gallery',
                    'page_subtitle' => 'Albums from real journeys with our guests',
                    'banner_label' => 'Banner: photo gallery',
                    'eyebrow' => 'Real moments',
                    'section_title' => 'Vietnam & Southeast Asia through our guests’ lenses',
                    'section_subtitle' => 'Honest frames — from Vietnam’s bays to Angkor and Bali.',
                ],
            ],
            'videos' => [
                'vi' => [
                    'seo_title' => 'Video trải nghiệm — ViTravel',
                    'seo_description' => 'Video hành trình thật do khách hàng và đội ngũ ViTravel ghi lại tại Việt Nam và Đông Nam Á.',
                    'page_title' => 'Video trải nghiệm',
                    'page_subtitle' => 'Xem tận mắt những hành trình chúng tôi đã thực hiện',
                    'banner_label' => 'Ảnh banner: video trải nghiệm',
                    'eyebrow' => 'Thư viện video',
                    'section_title' => 'Hành trình qua từng thước phim',
                    'section_subtitle' => 'Những thước phim chân thật từ chuyến đi cùng ViTravel — chọn một video để xem toàn màn hình.',
                ],
                'en' => [
                    'seo_title' => 'Experience videos — ViTravel',
                    'seo_description' => 'Real journey films shot by guests and the ViTravel team across Vietnam and Southeast Asia.',
                    'page_title' => 'Experience videos',
                    'page_subtitle' => 'See the journeys we have already made happen',
                    'banner_label' => 'Banner: experience videos',
                    'eyebrow' => 'Video library',
                    'section_title' => 'Journeys in unforgettable frames',
                    'section_subtitle' => 'Authentic films from trips with ViTravel — tap any video to watch full screen.',
                ],
            ],
            'customize' => [
                'vi' => [
                    'seo_title' => 'Thiết kế tour riêng — nhận lịch trình & báo giá trong 24 giờ | ViTravel',
                    'seo_description' => 'Cho chúng tôi biết mong muốn của bạn — chuyên gia ViTravel thiết kế lịch trình riêng tại Việt Nam & Đông Nam Á và gửi báo giá trong 24 giờ làm việc, miễn phí.',
                    'page_title' => 'Thiết kế tour riêng',
                    'page_subtitle' => 'Kể chuyến đi trong mơ của bạn — phần còn lại để chuyên gia bản địa lo',
                    'banner_label' => 'Ảnh banner: hành trình theo yêu cầu',
                    'eyebrow' => 'Tour theo yêu cầu',
                    'section_title' => 'Bắt đầu từ ý tưởng của bạn',
                    'section_subtitle' => 'Điểm đến, nhịp độ, ngân sách — chúng tôi dựng lịch trình vừa vặn trong 24 giờ.',
                ],
                'en' => [
                    'seo_title' => 'Custom tour design — itinerary & quote within 24 hours | ViTravel',
                    'seo_description' => 'Tell us what you want — ViTravel experts design a private itinerary across Vietnam & Southeast Asia and send a quote within one business day, free of charge.',
                    'page_title' => 'Design your private tour',
                    'page_subtitle' => 'Share your dream trip — leave the rest to our local experts',
                    'banner_label' => 'Banner: made-to-measure journeys',
                    'eyebrow' => 'Tailor-made tours',
                    'section_title' => 'Start from your idea',
                    'section_subtitle' => 'Destinations, pace, budget — we shape a fitting itinerary within 24 hours.',
                ],
            ],
            'contact' => [
                'vi' => [
                    'seo_title' => 'Liên hệ ViTravel — chúng tôi luôn sẵn sàng lắng nghe',
                    'seo_description' => 'Liên hệ đội ngũ ViTravel qua email, điện thoại, WhatsApp hoặc gửi lời nhắn — phản hồi trong 24 giờ làm việc.',
                    'page_title' => 'Liên hệ',
                    'page_subtitle' => 'Tư vấn hành trình Việt Nam & Đông Nam Á, hỗ trợ trong chuyến đi, hoặc đơn giản là trò chuyện về điểm đến.',
                    'banner_label' => 'Ảnh banner: liên hệ ViTravel',
                    'eyebrow' => 'Giữ liên lạc',
                    'section_title' => 'Chúng tôi luôn sẵn sàng lắng nghe',
                    'section_subtitle' => 'Email, điện thoại, WhatsApp hoặc form bên cạnh — phản hồi trong vòng 24 giờ làm việc.',
                ],
                'en' => [
                    'seo_title' => 'Contact ViTravel — we are ready to listen',
                    'seo_description' => 'Reach the ViTravel team by email, phone, WhatsApp or message form — we reply within one business day.',
                    'page_title' => 'Contact',
                    'page_subtitle' => 'Trip advice for Vietnam & Southeast Asia, on-trip support, or simply a chat about destinations.',
                    'banner_label' => 'Banner: contact ViTravel',
                    'eyebrow' => 'Stay in touch',
                    'section_title' => 'We are ready to listen',
                    'section_subtitle' => 'Email, phone, WhatsApp or the form beside — reply within one business day.',
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function get(string $key, ?string $locale = null): array
    {
        $all = self::all();
        if (! isset($all[$key])) {
            return [];
        }

        $picked = LocaleContent::pick($all[$key], $locale, []);

        return is_array($picked) ? $picked : [];
    }
}
