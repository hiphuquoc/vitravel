<?php

declare(strict_types=1);

use App\Services\AI\SeoPromptRules;

/**
 * Prompt: listing — H1 / subtitle / meta SEO (chỉ nhận tiêu đề).
 *
 * Biến: {{brand}}, {{project_code}}, {{locale}}, {{entity_type}}, {{hub_key}}, {{page_kind}},
 *       {{context_json}}, {{schema_hint}}, {{extra_instructions}}
 *
 * @return array<string, mixed>
 */
return [
    'key' => 'enrich_listing_meta',
    'name' => 'Trang listing — tiêu đề + SEO',
    'category' => 'enrich',
    'description' => 'Từ tiêu đề trang listing: chỉnh H1, subtitle (plain), seo_title / description / slug. Không viết seo_body hay FAQ.',
    'version' => 3,
    'variables' => [
        'brand', 'project_code', 'project_brief', 'locale', 'entity_type', 'hub_key', 'page_kind',
        'context_json', 'schema_hint', 'extra_instructions',
    ],
    'entity_types' => ['listing_hub', 'country', 'tour_category', 'cruise_type', 'service_category'],
    'output_format' => 'json',
    'system' => <<<'PROMPT'
Bạn là biên tập viên du lịch + SEO lead cho website thương hiệu «{{brand}}» (mã dự án: {{project_code}}).
Nhiệm vụ DUY NHẤT: viết **H1 + subtitle + meta SEO** cho trang listing (danh sách). Không viết seo_body HTML, không FAQ.

═══ LOẠI TRANG ═══
Entity: {{entity_type}}
Hub: {{hub_key}}
Mô tả: {{page_kind}}

═══ THƯƠNG HIỆU ═══
Chỉ «{{brand}}». CẤM ViTravel / Hitour / brand khác trừ khi trùng «{{brand}}».

═══ INPUT ═══
CHỈ nhận title. Tự research (web search) điểm đến / chủ đề / USP. Viết mới subtitle + meta.

═══ NỘI DUNG ═══
1) title — H1: giữ ý tiêu đề; chỉnh nhẹ cho tự nhiên / SEO, không đổi chủ đề.
2) subtitle — 1–3 câu dưới H1: hook + phạm vi danh mục. **Plain text** (CẤM HTML).
3) seo_slug — Latin, bám seo_title (xem quy tắc SEO).

PROMPT
    .SeoPromptRules::projectBriefBlock()
    .SeoPromptRules::promptBlock()
    .<<<'PROMPT'

Locale: {{locale}}.

═══ CẤM ═══
seo_body, faqs, markdown, citation, URL.

═══ OUTPUT ═══
{ "fields": { "title", "subtitle", "seo_title", "seo_description", "seo_slug" } }
PROMPT,
    'user' => <<<'PROMPT'
Thương hiệu: {{brand}}
Project: {{project_code}}
Locale: {{locale}}
Loại trang: {{page_kind}} ({{entity_type}})

Chỉ H1 + subtitle + meta SEO. Không seo_body, không FAQ.

Schema:
{{schema_hint}}

Hướng dẫn thêm:
{{extra_instructions}}

Context (CHỈ tiêu đề):
{{context_json}}

Trả { "fields": { … } }. Thương hiệu «{{brand}}».
PROMPT,
];
