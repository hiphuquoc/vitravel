<?php

declare(strict_types=1);

/**
 * Bảng giá chi tiết — dùng chung tour / cruise / service.
 * Guest types mặc định chỉ seed khi project chưa có mã tương ứng (không ghi đè admin).
 */
return [
    'units' => [
        'per_person' => 'Người',
        'per_room' => 'Phòng',
        'per_vehicle' => 'Xe',
        'per_group' => 'Nhóm',
        'per_unit' => 'Đơn vị',
    ],

    'period_kinds' => [
        'date' => 'Theo ngày',
        'range' => 'Khoảng ngày',
        'year' => 'Theo năm',
    ],

    'default_guest_types' => [
        [
            'code' => 'adult',
            'sort' => 10,
            'age_min' => 12,
            'age_max' => 59,
            'name' => ['vi' => 'Người lớn', 'en' => 'Adult'],
        ],
        [
            'code' => 'child',
            'sort' => 20,
            'age_min' => 2,
            'age_max' => 11,
            'name' => ['vi' => 'Trẻ em', 'en' => 'Child'],
        ],
        [
            'code' => 'senior',
            'sort' => 30,
            'age_min' => 60,
            'age_max' => null,
            'name' => ['vi' => 'Cao tuổi (60+)', 'en' => 'Senior (60+)'],
        ],
    ],

    /*
    | Fallback khi seed file chưa có `price_table_defaults`.
    | {year} được thay bằng năm hiện tại lúc seed.
    */
    'sample' => [
        'unit' => 'per_person',
        'notes' => 'Giá tham khảo theo người. Trẻ em và cao tuổi giảm theo bảng. Liên hệ để chốt báo giá chính xác.',
        'guest_multipliers' => [
            'adult' => 1,
            'child' => 0.7,
            'senior' => 0.85,
        ],
        'cluster_units' => [
            'stay' => 'per_room',
        ],
        'periods' => [
            [
                'kind' => 'year',
                'label' => 'Giá năm {year}',
                'is_promo' => false,
                'priority' => 0,
            ],
            [
                'kind' => 'range',
                'label' => 'Ưu đãi hè {year}',
                'starts_on' => '{year}-06-01',
                'ends_on' => '{year}-08-31',
                'is_promo' => true,
                'priority' => 10,
                'amount_multiplier' => 0.9,
            ],
        ],
    ],
];
