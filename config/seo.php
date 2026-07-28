<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO entity types & layered URL rules (Hitour pattern)
    |--------------------------------------------------------------------------
    */
    'types' => [
        'country' => [
            'label' => 'Quốc gia',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'package_tour' => [
            'label' => 'Gói Tour',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'package_cruise' => [
            'label' => 'Gói Cruise',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'tour_category' => [
            'label' => 'Danh mục Tour',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'article' => [
            'label' => 'Bài viết',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'blog_category' => [
            'label' => 'Chuyên mục Blog',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'static_page' => [
            'label' => 'Trang tĩnh',
            'parent_type' => null,
            'parent_relation' => null,
        ],
    ],
];
