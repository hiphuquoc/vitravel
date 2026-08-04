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
    |--------------------------------------------------------------------------
    | Google Cloud Storage — chuẩn đa dự án
    |--------------------------------------------------------------------------
    | Hằng số .env (bắt buộc khi MEDIA_DISK=gcs):
    |   GCS_PROJECT_ID, GCS_BUCKET, GCS_KEY_FILE, GCS_PUBLIC_URL
    | Key file: đường dẫn relative base_path hoặc absolute.
    | MediaService đọc public_url để build URL ảnh/video.
    | Fallback GOOGLE_CLOUD_* chỉ migrate dự án cũ.
    */
    'gcs' => [
        'project_id' => env('GCS_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT_ID')),
        'bucket' => env('GCS_BUCKET', env('GOOGLE_CLOUD_STORAGE_BUCKET')),
        'key_file' => ($path = env('GCS_KEY_FILE', env('GOOGLE_CLOUD_KEY_FILE')))
            ? (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) ? $path : base_path($path))
            : null,
        'public_url' => env('GCS_PUBLIC_URL', env('GCS_PUBLIC_BASE_URL', env('GOOGLE_CLOUD_URL'))),
        'path_prefix' => env('GCS_PATH_PREFIX', env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI providers (OpenAI-compatible) — chuẩn BaseOS / đa dự án
    |--------------------------------------------------------------------------
    | Copy key từ baseos.dev khi cần. Provider nào có key sẽ dùng được ngay.
    */
    'openai' => [
        'key' => env('AI_OPENAI_API_KEY', env('OPENAI_API_KEY')),
        'base_url' => env('AI_OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_OPENAI_TIMEOUT', 180),
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 300),
    ],

    'google' => [
        'key' => env('GEMINI_API_KEY', env('GOOGLE_AI_API_KEY')),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 180),
    ],

];
