<?php

/**
 * Cấu hình “dự án nội dung” — chọn file seed trong thư mục project/.
 *
 * .env:
 *   PROJECT_SEED=vitravel          → project/seed_vitravel.php
 *   PROJECT_SEED=seed_other.php    → project/seed_other.php
 *   PROJECT_SEED=island/phu-quoc   → project/seed_island_phu-quoc.php  (slash → _)
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Project seed profile
    |--------------------------------------------------------------------------
    |
    | Mỗi bản deploy / máy local chỉ trỏ một file dữ liệu. Không ghi đè lẫn nhau:
    | giữ nhiều file seed_* trong project/, đổi PROJECT_SEED khi chuyển dự án.
    |
    */
    'seed' => env('PROJECT_SEED', 'vitravel'),

    /*
    |--------------------------------------------------------------------------
    | Thư mục chứa file seed (relative to base_path)
    |--------------------------------------------------------------------------
    */
    'seed_dir' => env('PROJECT_SEED_DIR', 'project'),

];
