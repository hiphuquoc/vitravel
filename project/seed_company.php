<?php

/**
 * Thông tin dự án / liên hệ / social / footer — seed vào company_profiles.
 * Schema: project/README.md § company
 *
 * @return array<string, mixed>
 */
return [
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
