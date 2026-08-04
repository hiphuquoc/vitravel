<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Optional: comma-separated origins for cross-origin admin/API (same-origin /he-thong needs none).
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
