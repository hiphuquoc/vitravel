<?php

$envOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
)));

$adminApp = rtrim((string) env('ADMIN_APP_URL', ''), '/');
if ($adminApp !== '') {
    $envOrigins[] = $adminApp;
}

$origins = array_values(array_unique(array_filter(array_merge([
    'http://localhost:3100',
    'http://127.0.0.1:3100',
    'https://admin.vitravel.dev',
    'https://admin.vitravel.net',
], $envOrigins))));

return [

    'paths' => ['api/*', 'api/v1/admin/*', 'api/v1/admin'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [
        '#^https?://(www\.)?admin\.[a-z0-9.-]+(:[0-9]+)?$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
