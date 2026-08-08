<?php

/**
 * Thông tin doanh nghiệp — fallback rỗng khi DB chưa seed.
 *
 * Runtime brand lấy từ `company_profiles` (per project) qua CompanyProfile::contact().
 * File này chỉ cấu trúc rỗng / null — không đọc env COMPANY_*.
 * Không sửa file này để đổi nội dung runtime — dùng admin hoặc key `company` trong project/seed_{name}.php.
 */
return [

    'name' => null,

    'legal_name' => null,

    'tagline' => null,

    'slogan' => null,

    'license_number' => null,

    'contact' => [
        'email' => null,
        'phone' => null,
        'whatsapp' => null,
        'zalo' => null,
        'hotline_label' => null,
    ],

    'address' => [
        'street' => null,
        'locality' => null,
        'region' => null,
        'postal' => null,
        'country' => null,
    ],

    'social' => [
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'url' => null,
        ],
        'youtube' => [
            'label' => 'YouTube',
            'icon' => 'play',
            'url' => null,
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon' => 'photo',
            'url' => null,
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'icon' => 'share',
            'url' => null,
        ],
    ],

    'schema' => [
        'available_language' => ['Vietnamese', 'English'],
        'contact_type' => 'customer service',
        'logo' => null,
    ],

    'footer' => [
        'copyright' => null,
        'show_dmca_badge' => true,
    ],

];
