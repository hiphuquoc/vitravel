<?php

/**
 * Catalogue 5 cụm dịch vụ mở rộng (ngoài tour / cruise).
 * Hub SEO keys khớp config/seo.php → hubs.
 */
return [
    'clusters' => [
        'train' => [
            'hub_key' => 'trains_hub',
            'label' => 'Vé tàu hỏa',
            'nav_label' => 'Tàu',
            'icon' => 'train',
            'unit_label' => 'vé tàu',
            'sort' => 1,
        ],
        'flight' => [
            'hub_key' => 'flights_hub',
            'label' => 'Vé máy bay',
            'nav_label' => 'Máy bay',
            'icon' => 'plane',
            'unit_label' => 'vé máy bay',
            'sort' => 2,
        ],
        'stay' => [
            'hub_key' => 'stays_hub',
            'label' => 'Khách sạn & Resort',
            'nav_label' => 'Lưu trú',
            'icon' => 'building',
            'unit_label' => 'lưu trú',
            'sort' => 3,
        ],
        'experience' => [
            'hub_key' => 'experiences_hub',
            'label' => 'Vé vui chơi & trải nghiệm',
            'nav_label' => 'Vui chơi',
            'icon' => 'sparkles',
            'unit_label' => 'trải nghiệm',
            'sort' => 4,
        ],
        'other' => [
            'hub_key' => 'extras_hub',
            'label' => 'Dịch vụ khác',
            'nav_label' => 'Dịch vụ',
            'icon' => 'briefcase',
            'unit_label' => 'dịch vụ',
            'sort' => 5,
        ],
    ],

    'hub_to_cluster' => [
        'trains_hub' => 'train',
        'flights_hub' => 'flight',
        'stays_hub' => 'stay',
        'experiences_hub' => 'experience',
        'extras_hub' => 'other',
    ],
];
