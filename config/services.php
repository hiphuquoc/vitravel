<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Google Cloud Storage (pattern baseos.dev — credentials via file path, không hard-code key)
    */
    'gcs' => [
        'project_id' => env('GCS_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT_ID')),
        'bucket' => env('GCS_BUCKET', env('GOOGLE_CLOUD_STORAGE_BUCKET')),
        'key_file' => ($path = env('GCS_KEY_FILE', env('GOOGLE_CLOUD_KEY_FILE')))
            ? (str_starts_with($path, '/') ? $path : base_path($path))
            : null,
        'public_url' => env('GCS_PUBLIC_URL'),
    ],

];
