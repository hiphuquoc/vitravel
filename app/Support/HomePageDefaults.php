<?php

namespace App\Support;

use App\Models\HomeSection;

class HomePageDefaults
{
    /** @return list<array{icon: string, sort: int, vi: array{title: string, description: string}, en: array{title: string, description: string}}> */
    public static function usps(): array
    {
        return [
            [
                'icon' => 'expert',
                'sort' => 0,
                'vi' => [
                    'title' => 'Thiết kế bởi chuyên gia bản địa',
                    'description' => 'Lịch trình cá nhân hoá từ người am hiểu từng cung đường, mùa đẹp và văn hoá địa phương.',
                ],
                'en' => [
                    'title' => 'Designed by local experts',
                    'description' => 'Personalised itineraries from people who know every route, season and local culture.',
                ],
            ],
            [
                'icon' => 'refund',
                'sort' => 1,
                'vi' => [
                    'title' => 'Cam kết hoàn tiền minh bạch',
                    'description' => 'Chính sách huỷ linh hoạt, điều khoản rõ ràng ngay từ báo giá đầu tiên.',
                ],
                'en' => [
                    'title' => 'Transparent refund promise',
                    'description' => 'Flexible cancellation with clear terms from your very first quote.',
                ],
            ],
            [
                'icon' => 'value',
                'sort' => 2,
                'vi' => [
                    'title' => 'Giá trị vượt trội',
                    'description' => 'Chất lượng cao cấp, giá cạnh tranh — làm việc trực tiếp với đối tác, không qua trung gian.',
                ],
                'en' => [
                    'title' => 'Outstanding value',
                    'description' => 'Premium quality at competitive prices — direct partnerships, no middlemen.',
                ],
            ],
            [
                'icon' => 'support',
                'sort' => 3,
                'vi' => [
                    'title' => 'Hỗ trợ 24/7 tại chỗ',
                    'description' => 'Đội ngũ bản địa luôn sẵn sàng — từ lúc lên ý tưởng đến khi bạn về nhà an toàn.',
                ],
                'en' => [
                    'title' => '24/7 on-the-ground support',
                    'description' => 'Local team always on call — from first idea to safe return home.',
                ],
            ],
        ];
    }

    /** @return list<array{key: string, sort: int, vi: array<string, mixed>, en: array<string, mixed>}> */
    public static function sections(): array
    {
        return [
            [
                'key' => HomeSection::KEY_COMPANY_INTRO,
                'sort' => 0,
                'vi' => [
                    'eyebrow' => 'Chuyên gia du lịch miền Bắc',
                    'title' => 'Hành trình chân thật, thiết kế bởi người bản địa',
                    'body' => 'ViTravel là đại lý lữ hành đặt trụ sở tại Hà Nội, với hơn 10 năm đồng hành cùng du khách khám phá miền Bắc Việt Nam — từ vịnh Hạ Long, Sa Pa, Ninh Bình tới cao nguyên đá Hà Giang. Chúng tôi không bán những tour đóng gói sẵn — mỗi hành trình đều được <strong class="font-semibold text-ink">thiết kế riêng từ trải nghiệm thật</strong> của đội ngũ chuyên gia sinh ra và lớn lên tại chính điểm đến.',
                    'meta_line' => 'Giấy phép lữ hành quốc tế số 01-2234/TCDL-GP-LHQT',
                    'cta_label' => 'Tìm hiểu về chúng tôi',
                    'cta_url' => '/ve-chung-toi',
                    'image_alt' => 'Ảnh đội ngũ ViTravel tại văn phòng Hà Nội',
                ],
                'en' => [
                    'eyebrow' => 'Northern Vietnam travel experts',
                    'title' => 'Authentic journeys, designed by locals',
                    'body' => 'ViTravel is a Hanoi-based travel agency with over 10 years of guiding guests through Northern Vietnam — from Halong Bay, Sapa and Ninh Binh to the karst highlands of Ha Giang. We do not sell off-the-shelf packages — every itinerary is tailored from real on-the-ground experience by experts born and raised in the destinations themselves.',
                    'meta_line' => 'International travel license No. 01-2234/TCDL-GP-LHQT',
                    'cta_label' => 'Learn about us',
                    'cta_url' => '/ve-chung-toi',
                    'image_alt' => 'ViTravel team at our Hanoi office',
                ],
            ],
            [
                'key' => HomeSection::KEY_FEATURED_TOURS,
                'sort' => 1,
                'vi' => [
                    'eyebrow' => 'Được yêu thích nhất',
                    'title' => 'Những tour được yêu cầu nhiều nhất',
                    'subtitle' => 'Ba hành trình khách hàng đặt và đánh giá cao nhất trong 12 tháng qua.',
                ],
                'en' => [
                    'eyebrow' => 'Most popular',
                    'title' => 'Our most requested tours',
                    'subtitle' => 'Three itineraries our guests book and rate highest over the past 12 months.',
                ],
            ],
            [
                'key' => HomeSection::KEY_DESTINATIONS,
                'sort' => 2,
                'vi' => ['title' => 'Những điểm đến được yêu thích nhất'],
                'en' => ['title' => 'Our most loved destinations'],
            ],
            [
                'key' => HomeSection::KEY_TESTIMONIALS,
                'sort' => 3,
                'vi' => [
                    'eyebrow' => 'Khách hàng kể lại',
                    'title' => 'Trải nghiệm chân thật từ khách hàng',
                    'subtitle' => 'Hơn 5.000 du khách đã đồng hành cùng chúng tôi — đây là những gì họ kể lại.',
                    'cta_label' => 'Xem tất cả cảm nhận',
                    'cta_url' => '/cam-nhan-khach-hang',
                ],
                'en' => [
                    'eyebrow' => 'Guest stories',
                    'title' => 'Real experiences from our travellers',
                    'subtitle' => 'Over 5,000 guests have travelled with us — here is what they say.',
                    'cta_label' => 'Read all reviews',
                    'cta_url' => '/cam-nhan-khach-hang',
                ],
            ],
            [
                'key' => HomeSection::KEY_REVIEW_PLATFORMS,
                'sort' => 4,
                'vi' => ['title' => 'ViTravel được đánh giá cao trên'],
                'en' => ['title' => 'ViTravel is highly rated on'],
            ],
            [
                'key' => HomeSection::KEY_TEAM,
                'sort' => 5,
                'vi' => [
                    'eyebrow' => 'Con người ViTravel',
                    'title' => 'Đội ngũ tận tâm của chúng tôi',
                    'subtitle' => 'Những con người bản địa hiểu điểm đến hơn bất kỳ ai — đồng hành từ lúc lên ý tưởng tới khi về nhà.',
                    'cta_label' => 'Gặp gỡ cả đội ngũ',
                    'cta_url' => '/doi-ngu',
                ],
                'en' => [
                    'eyebrow' => 'The ViTravel team',
                    'title' => 'Our dedicated local experts',
                    'subtitle' => 'People who know the destinations better than anyone — with you from the first idea until you are home.',
                    'cta_label' => 'Meet the full team',
                    'cta_url' => '/doi-ngu',
                ],
            ],
            [
                'key' => HomeSection::KEY_VIDEOS,
                'sort' => 6,
                'vi' => [
                    'title' => 'Video trải nghiệm chân thật',
                    'cta_label' => 'Xem tất cả video',
                    'cta_url' => '/video-trai-nghiem',
                ],
                'en' => [
                    'title' => 'Authentic experience videos',
                    'cta_label' => 'View all videos',
                    'cta_url' => '/video-trai-nghiem',
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function heroPills(): array
    {
        return [
            [
                'sort' => 0,
                'category_slug' => 'viet-nam-3-tuan',
                'vi' => ['label' => 'Việt Nam 3 tuần'],
                'en' => ['label' => 'Vietnam 3 Weeks'],
            ],
            [
                'sort' => 1,
                'country_slug' => 'tour-ket-hop',
                'vi' => ['label' => 'Tour kết hợp'],
                'en' => ['label' => 'Combined Tours'],
            ],
        ];
    }
}
