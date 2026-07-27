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
    'articles' => env('MEDIA_ARTICLES_FOLDER', 'vitravel/articles'),
    'team' => env('MEDIA_TEAM_FOLDER', 'vitravel/team'),
    'tour_categories' => env('MEDIA_TOUR_CATEGORIES_FOLDER', 'vitravel/tour-categories'),

    /*
    |--------------------------------------------------------------------------
    | Image optimization
    |--------------------------------------------------------------------------
    */
    'max_edge' => (int) env('MEDIA_MAX_EDGE', 1920),
    'jpeg_quality' => (int) env('MEDIA_JPEG_QUALITY', 82),

    /*
    |--------------------------------------------------------------------------
    | Max upload size (KB) — validated at controller level
    |--------------------------------------------------------------------------
    */
    'max_upload_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 5120),
];
