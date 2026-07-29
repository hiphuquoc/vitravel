<?php

/**
 * Thông tin doanh nghiệp — single source of truth cho hotline, footer,
 * Organization schema, social, floating buttons…
 *
 * Ưu tiên ghi đè runtime:
 *  - Admin Company Profile (email / phone / whatsapp / slogan / license) nếu đã nhập
 *  - Biến môi trường COMPANY_* / SEO_* (tuỳ field)
 *
 * Sửa file này để đổi dữ liệu mẫu / mặc định toàn site.
 */
return [

    'name' => env('COMPANY_NAME', 'ViTravel'),

    'legal_name' => env('COMPANY_LEGAL_NAME', 'Công ty TNHH Du lịch ViTravel'),

    /** Tagline ngắn (meta / header). */
    'tagline' => env('COMPANY_TAGLINE', 'Hài lòng hơn cả mong đợi'),

    /** Slogan hiển thị footer / schema (có thể kèm dấu ngoặc kép). */
    'slogan' => env('COMPANY_SLOGAN', '“Hài lòng hơn cả mong đợi”'),

    /** Giấy phép lữ hành — hiện footer + schema identifier. */
    'license_number' => env('COMPANY_LICENSE', '01-2234/TCDL-GP-LHQT'),

    /*
    |--------------------------------------------------------------------------
    | Liên hệ
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email' => env('COMPANY_EMAIL', 'hello@vitravel.vn'),
        'phone' => env('COMPANY_PHONE', '+84 24 3999 8888'),
        'whatsapp' => env('COMPANY_WHATSAPP', '+84 912 345 678'),
        /** Số Zalo (mặc định = phone nếu trống). */
        'zalo' => env('COMPANY_ZALO', '+84 24 3999 8888'),
        'hotline_label' => 'Hotline',
    ],

    /*
    |--------------------------------------------------------------------------
    | Địa chỉ trụ sở (Organization / PostalAddress)
    |--------------------------------------------------------------------------
    */
    'address' => [
        'street' => env('COMPANY_ADDRESS_STREET', '88 Xã Đàn, Đống Đa'),
        'locality' => env('COMPANY_ADDRESS_LOCALITY', 'Hà Nội'),
        'region' => env('COMPANY_ADDRESS_REGION', 'Hà Nội'),
        'postal' => env('COMPANY_ADDRESS_POSTAL', '100000'),
        'country' => env('COMPANY_ADDRESS_COUNTRY', 'VN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mạng xã hội (footer + schema sameAs)
    |--------------------------------------------------------------------------
    | icon: tên x-icon trong resources/views/components/icon.blade.php
    | url để trống → ẩn khỏi footer / không đưa vào sameAs
    */
    'social' => [
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'url' => env('COMPANY_FACEBOOK', 'https://www.facebook.com/vitravel'),
        ],
        'youtube' => [
            'label' => 'YouTube',
            'icon' => 'play',
            'url' => env('COMPANY_YOUTUBE', 'https://www.youtube.com/@vitravel'),
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon' => 'photo',
            'url' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/vitravel'),
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'icon' => 'share',
            'url' => env('COMPANY_TIKTOK', 'https://www.tiktok.com/@vitravel'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema.org Organization extras
    |--------------------------------------------------------------------------
    */
    'schema' => [
        'available_language' => ['Vietnamese', 'English'],
        'contact_type' => 'customer service',
        /** Logo / OG image mặc định (path public hoặc URL tuyệt đối). */
        'logo' => env('COMPANY_LOGO', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    | :year và :license được thay khi render.
    */
    'footer' => [
        'copyright' => env(
            'COMPANY_FOOTER_COPYRIGHT',
            '© :year ViTravel. Giấy phép lữ hành quốc tế số :license.'
        ),
        'show_dmca_badge' => true,
    ],

];
