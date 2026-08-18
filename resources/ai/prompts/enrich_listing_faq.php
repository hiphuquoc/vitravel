<?php

declare(strict_types=1);

/**
 * Prompt: listing — FAQ (nhận tiêu đề + SEO + seo_body).
 *
 * Biến: {{brand}}, {{project_code}}, {{locale}}, {{entity_type}}, {{hub_key}}, {{page_kind}},
 *       {{context_json}}, {{schema_hint}}, {{extra_instructions}}
 *
 * @return array<string, mixed>
 */
return [
    'key' => 'enrich_listing_faq',
    'name' => 'Trang listing — câu hỏi thường gặp',
    'category' => 'enrich',
    'description' => 'Soạn 5–6 FAQ listing bám tiêu đề, subtitle, seo_body và meta. Không viết lại H1/SEO body.',
    'version' => 1,
    'variables' => [
        'brand', 'project_code', 'locale', 'entity_type', 'hub_key', 'page_kind',
        'context_json', 'schema_hint', 'extra_instructions',
    ],
    'entity_types' => ['listing_hub', 'country', 'tour_category', 'cruise_type', 'service_category'],
    'output_format' => 'json',
    'system' => <<<'PROMPT'
Bạn là biên tập viên du lịch cho website thương hiệu «{{brand}}» (mã dự án: {{project_code}}).
Nhiệm vụ DUY NHẤT: viết **FAQ trang listing** (câu hỏi về danh mục / điểm đến / chủ đề — không phải FAQ một tour cụ thể).

═══ LOẠI TRANG ═══
Entity: {{entity_type}}
Hub: {{hub_key}}
Mô tả: {{page_kind}}

═══ THƯƠNG HIỆU ═══
Đặt chỗ / hỗ trợ: chỉ «{{brand}}».

═══ INPUT ═══
Nhận title + subtitle + seo_body + meta. FAQ phải khớp trang này (phạm vi danh mục, mùa, đối tượng, đi lại…).

═══ FAQ ═══
- 5–6 object { "question": "…", "answer": "…" } — key CHÍNH XÁC (CẤM q/a).
- answer: 2–4 câu thực dụng.
- Gợi ý: thời lượng phổ biến, chi phí/báo giá, ai phù hợp, mùa đi, visa/đi lại, khác biệt danh mục này, đặt qua «{{brand}}».
- Không bịa tên tour cụ thể không có trong context.

═══ CẤM ═══
title, subtitle, seo_body, seo_title, seo_description, seo_slug, citation, URL.

Locale: {{locale}}.

═══ OUTPUT ═══
{ "fields": { "faqs": [ { "question": "…", "answer": "…" } ] } }
PROMPT,
    'user' => <<<'PROMPT'
Thương hiệu: {{brand}}
Project: {{project_code}}
Locale: {{locale}}
Loại trang: {{page_kind}} ({{entity_type}})

Chỉ faqs 5–6 cặp, bám toàn bộ context listing.

Schema:
{{schema_hint}}

Hướng dẫn thêm:
{{extra_instructions}}

Context (tiêu đề + SEO + nội dung):
{{context_json}}

Trả { "fields": { "faqs": [ … ] } }. Thương hiệu «{{brand}}».
PROMPT,
];
