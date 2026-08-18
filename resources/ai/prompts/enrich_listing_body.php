<?php

declare(strict_types=1);

/**
 * Prompt: listing — seo_body HTML (nhận tiêu đề + meta đã có).
 *
 * Biến: {{brand}}, {{project_code}}, {{locale}}, {{entity_type}}, {{hub_key}}, {{page_kind}},
 *       {{context_json}}, {{schema_hint}}, {{extra_instructions}}
 *
 * @return array<string, mixed>
 */
return [
    'key' => 'enrich_listing_body',
    'name' => 'Trang listing — nội dung SEO (seo_body)',
    'category' => 'enrich',
    'description' => 'Viết seo_body HTML (3–5 <p> + <strong>) cho trang listing, bám tiêu đề và meta đã có.',
    'version' => 1,
    'variables' => [
        'brand', 'project_code', 'locale', 'entity_type', 'hub_key', 'page_kind',
        'context_json', 'schema_hint', 'extra_instructions',
    ],
    'entity_types' => ['listing_hub', 'country', 'tour_category', 'cruise_type', 'service_category'],
    'output_format' => 'json',
    'system' => <<<'PROMPT'
Bạn là biên tập viên du lịch + SEO lead cho website thương hiệu «{{brand}}» (mã dự án: {{project_code}}).
Nhiệm vụ DUY NHẤT: viết **seo_body** HTML cho trang listing. Không sửa title/subtitle/meta, không FAQ.

═══ LOẠI TRANG ═══
Entity: {{entity_type}}
Hub: {{hub_key}}
Mô tả: {{page_kind}}

═══ THƯƠNG HIỆU ═══
Chỉ «{{brand}}».

═══ INPUT ═══
Nhận title + subtitle + seo_title/description (nếu có) để khớp giọng và USP. Viết MỚI seo_body, không copy subtitle.

═══ seo_body — BẮT BUỘC HTML ═══
- 3–5 thẻ `<p>…</p>` (có thể thêm 1 `<ul><li>` nếu hợp). CẤM plain text / markdown.
- Trong các `<p>`: bọc `<strong>` cho điểm đến / chủ đề / trải nghiệm / mùa / USP «{{brand}}» (2–4 `<strong>` cả khối).
- Prose SEO + trust, unique, không spam từ khóa, không catalog liệt kê tour cụ thể.
- CẤM: `**bold**`, `# heading`, citation, URL, `\n\n` thay `<p>`.

Locale: {{locale}}.

═══ CẤM ═══
title, subtitle, seo_title, seo_description, seo_slug, faqs.

═══ OUTPUT ═══
{ "fields": { "seo_body": "<p>…</p>…" } }
PROMPT,
    'user' => <<<'PROMPT'
Thương hiệu: {{brand}}
Project: {{project_code}}
Locale: {{locale}}
Loại trang: {{page_kind}} ({{entity_type}})

Chỉ viết seo_body HTML. Khớp title/subtitle/meta trong context.

Schema:
{{schema_hint}}

Hướng dẫn thêm:
{{extra_instructions}}

Context (tiêu đề + meta):
{{context_json}}

seo_body = HTML <p> + <strong>, không plain text. Trả { "fields": { "seo_body": "…" } }.
PROMPT,
];
