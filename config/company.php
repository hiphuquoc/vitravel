<?php

/**
 * Thông tin doanh nghiệp — fallback khi DB chưa seed.
 * Nguồn chính: ProjectSeed key `company` → bảng company_profiles (admin «Thông tin dự án»).
 *
 * Không sửa file này để đổi nội dung runtime — dùng admin hoặc project/seed_company.php.
 */
return [

    'name' => env('COMPANY_NAME', null),

    'legal_name' => env('COMPANY_LEGAL_NAME', null),

    'tagline' => env('COMPANY_TAGLINE', null),

    'slogan' => env('COMPANY_SLOGAN', null),

    'license_number' => env('COMPANY_LICENSE', null),

    'contact' => [
        'email' => env('COMPANY_EMAIL', null),
        'phone' => env('COMPANY_PHONE', null),
        'whatsapp' => env('COMPANY_WHATSAPP', null),
        'zalo' => env('COMPANY_ZALO', null),
        'hotline_label' => env('COMPANY_HOTLINE_LABEL', null),
    ],

    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', null),
        'locality' => env('COMPANY_ADDRESS_LOCALITY', null),
        'region' => env('COMPANY_ADDRESS_REGION', null),
        'postal' => env('COMPANY_ADDRESS_POSTAL', null),
        'country' => env('COMPANY_ADDRESS_COUNTRY', null),
    ],

    'social' => [
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'url' => env('COMPANY_FACEBOOK', null),
        ],
        'youtube' => [
            'label' => 'YouTube',
            'icon' => 'play',
            'url' => env('COMPANY_YOUTUBE', null),
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon' => 'photo',
            'url' => env('COMPANY_INSTAGRAM', null),
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'icon' => 'share',
            'url' => env('COMPANY_TIKTOK', null),
        ],
    ],

    'schema' => [
        'available_language' => ['Vietnamese', 'English'],
        'contact_type' => 'customer service',
        'logo' => env('COMPANY_LOGO', null),
    ],

    'footer' => [
        'copyright' => env('COMPANY_FOOTER_COPYRIGHT', null),
        'show_dmca_badge' => true,
    ],

];
