<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default media storage disk
    |--------------------------------------------------------------------------
    | public — local storage/app/public (dev)
    | gcs    — Google Cloud Storage (production, cùng pattern baseos.dev)
    */
    'disk' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public') === 'gcs' ? 'gcs' : 'public'),

    /*
    |--------------------------------------------------------------------------
    | Upload folder prefix on the active disk
    |--------------------------------------------------------------------------
    */
    'folder' => env('MEDIA_UPLOAD_FOLDER', 'vitravel/images'),

    'home_slider' => env('MEDIA_HOME_SLIDER_FOLDER', 'vitravel/home-slider'),

    'home_sections' => env('MEDIA_HOME_SECTIONS_FOLDER', 'vitravel/home-sections'),

    'packages' => env('MEDIA_PACKAGES_FOLDER', 'vitravel/packages'),
    'countries' => env('MEDIA_COUNTRIES_FOLDER', 'vitravel/countries'),
    'cruise_types' => env('MEDIA_CRUISE_TYPES_FOLDER', 'vitravel/cruise-types'),
    'articles' => env('MEDIA_ARTICLES_FOLDER', 'vitravel/articles'),
    'team' => env('MEDIA_TEAM_FOLDER', 'vitravel/team'),
    'reviews' => env('MEDIA_REVIEWS_FOLDER', 'vitravel/reviews'),
    'tour_categories' => env('MEDIA_TOUR_CATEGORIES_FOLDER', 'vitravel/tour-categories'),
    'videos' => env('MEDIA_VIDEOS_FOLDER', 'vitravel/videos'),
    'video_files' => env('MEDIA_VIDEO_FILES_FOLDER', 'vitravel/video-files'),

    /*
    |--------------------------------------------------------------------------
    | Master image (full) — cạnh dài tối đa khi upload
    |--------------------------------------------------------------------------
    */
    'max_edge' => (int) env('MEDIA_MAX_EDGE', 1920),
    'jpeg_quality' => (int) env('MEDIA_JPEG_QUALITY', 82),

    /*
    |--------------------------------------------------------------------------
    | Responsive variants (sinh thêm file khi upload)
    |--------------------------------------------------------------------------
    | full = path chính trên bảng media (max_edge).
    | Các key khác lưu trong media.meta.variants[{name}].path
    |
    | Dùng:
    |   thumb — avatar, thumbnail nhỏ (~112–200px hiển thị)
    |   card  — card tour/blog/điểm đến (~300–600px)
    |   lg    — section / gallery / detail (~800–1280px)
    |   full  — hero / ảnh lớn full-bleed
    */
    'variants' => [
        'thumb' => [
            'max_edge' => (int) env('MEDIA_VARIANT_THUMB', 400),
            'quality' => (int) env('MEDIA_VARIANT_THUMB_Q', 78),
        ],
        'card' => [
            'max_edge' => (int) env('MEDIA_VARIANT_CARD', 800),
            'quality' => (int) env('MEDIA_VARIANT_CARD_Q', 80),
        ],
        'lg' => [
            'max_edge' => (int) env('MEDIA_VARIANT_LG', 1280),
            'quality' => (int) env('MEDIA_VARIANT_LG_Q', 82),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alias ngữ cảnh → variant (API media_url / coverUrl)
    |--------------------------------------------------------------------------
    */
    'aliases' => [
        'avatar' => 'thumb',
        'icon' => 'thumb',
        'gallery' => 'card',
        'banner' => 'lg',
        'section' => 'lg',
        'detail' => 'lg',
        'hero' => 'full',
        'original' => 'full',
        'master' => 'full',
    ],

    /*
    |--------------------------------------------------------------------------
    | sizes= presets cho <x-img preset="…">
    |--------------------------------------------------------------------------
    */
    'sizes_presets' => [
        'avatar' => '112px',
        'thumb' => '(max-width: 640px) 50vw, 200px',
        'card' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 420px',
        'card-wide' => '(max-width: 640px) 100vw, (max-width: 1024px) 40vw, 480px',
        'country' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
        'section' => '(max-width: 1024px) 100vw, 50vw',
        'gallery' => '(max-width: 768px) 50vw, 200px',
        'hero' => '100vw',
        'detail' => '(max-width: 1024px) 100vw, 70vw',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max upload size (KB) — validated at controller level
    |--------------------------------------------------------------------------
    */
    'max_upload_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 5120),

    /** Video file (mp4/webm/mov) — mặc định 1GB */
    'max_video_upload_kb' => (int) env('MEDIA_MAX_VIDEO_UPLOAD_KB', 1048576),
];
