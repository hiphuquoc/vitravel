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
    /** Max tokens riêng cho enrich chương trình (itinerary HTML dài + ảnh). */
    'enrich_max_tokens' => (int) env('AI_ENRICH_MAX_TOKENS', 16384),
    /** Bật web search khi xây dựng chương trình (OpenAI Responses / Gemini google_search). */
    'enrich_web_search' => filter_var(env('AI_ENRICH_WEB_SEARCH', true), FILTER_VALIDATE_BOOL),
    /** Timeout riêng khi có web search (thường lâu hơn). */
    'enrich_timeout' => (int) env('AI_ENRICH_TIMEOUT', 240),

    /**
     * Model theo provider (override services.*.model).
     * Để trống → dùng config services.{provider}.model.
     */
    'models' => [
        'openai' => env('AI_OPENAI_MODEL', env('OPENAI_MODEL')),
        'google' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'deepseek' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    /**
     * Model ưu tiên khi enrich + web search (Chat Completions search API).
     * Để trống → dùng Responses API + tools web_search với model openai thường.
     */
    'search_models' => [
        'openai' => env('AI_OPENAI_SEARCH_MODEL', ''),
    ],

    /** Thư mục prompt PHP (key => file). Seed → DB qua `ai:sync-prompts`. */
    'prompts_path' => resource_path('ai/prompts'),

    'prompts' => [
        'translate_page' => 'translate_page.php',
        'enrich_detail_meta' => 'enrich_detail_meta.php',
        'enrich_detail_content' => 'enrich_detail_content.php',
        'enrich_detail_faq' => 'enrich_detail_faq.php',
        'enrich_listing_meta' => 'enrich_listing_meta.php',
        'enrich_listing_body' => 'enrich_listing_body.php',
        'enrich_listing_faq' => 'enrich_listing_faq.php',
    ],
];
