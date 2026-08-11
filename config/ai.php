<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| AI runtime — dùng chung cho dịch trang, sau này mở rộng feature khác
|--------------------------------------------------------------------------
*/

return [

    /** Provider ưu tiên: openai | google | deepseek — fallback theo thứ tự dưới. */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /** Thứ tự fallback khi provider chính lỗi / thiếu key. */
    'fallback_providers' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_FALLBACK_PROVIDERS', 'openai,google,deepseek')),
    ))),

    'timeout' => (int) env('AI_TIMEOUT', 180),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 8192),
    /** Max tokens riêng cho enrich chương trình (itinerary dài). */
    'enrich_max_tokens' => (int) env('AI_ENRICH_MAX_TOKENS', 12288),

    /**
     * Model theo provider (override services.*.model).
     * Để trống → dùng config services.{provider}.model.
     */
    'models' => [
        'openai' => env('AI_OPENAI_MODEL', env('OPENAI_MODEL')),
        'google' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'deepseek' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    /** Thư mục prompt PHP (key => file). Seed → DB qua `ai:sync-prompts`. */
    'prompts_path' => resource_path('ai/prompts'),

    'prompts' => [
        'translate_page' => 'translate_page.php',
        'enrich_detail_program' => 'enrich_detail_program.php',
    ],
];
