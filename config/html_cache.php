<?php

return [
    'folderSave' => 'public/caches',
    'extension' => 'html',
    'disk' => env('HTML_CACHE_DISK', 'local'),
    'ttl' => (int) env('HTML_CACHE_TTL', 2592000),
    'use_gzip' => env('HTML_CACHE_GZIP', true),
    'use_html_min' => false,
    'use_jscss_min' => false,
    'menu_key_prefix' => 'menuMain',
    'vite_entrypoints' => [
        'resources/css/app.css',
        'resources/js/app.js',
    ],
];
