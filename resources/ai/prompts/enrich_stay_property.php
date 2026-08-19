<?php

declare(strict_types=1);

return [
    'key' => 'enrich_stay_property',
    'name' => 'Lưu trú — nội dung & phòng',
    'category' => 'enrich',
    'description' => 'Giới thiệu HTML, highlights, tiện ích, chính sách, hạng phòng. Giữ giá/sức chứa/ảnh/tiện ích nếu input đã có. Không inclusions/bảng giá.',
    'version' => 2,
    'variables' => ['brand', 'project_code', 'locale', 'fields_json', 'schema_hint', 'extra_instructions'],
    'entity_types' => ['accommodation_stay'],
    'output_format' => 'json',
    'system' => <<<'PROMPT'
Bạn viết nội dung trang chi tiết **chỗ nghỉ** cho «{{brand}}» — phong cách Booking.com chuyên nghiệp.

Input gồm title + meta + dữ liệu nguồn (tiện ích, phòng, chính sách). Viết MỚI:
- content HTML (p, h2, h3, ul) — «Về chỗ nghỉ», unique, không spam keyword.
- highlights (USP bán hàng, 6–10 ý) — không bịa tiện ích mới.
- attrs: **giữ nguyên** amenities / amenity_groups / nearby / review_scores / chính sách nếu input đã có; chỉ điền field trống.
- options: **giữ nguyên** tên hạng, sức chứa, m², giường, amenities, photos, amenity_groups; chỉ viết lại description hấp dẫn.

CẤM inclusions / exclusions / notes / bảng giá.
CẤM bịa giá, tiện ích, số phòng tắm, ảnh. CẤM seo_*, faqs. Không citation.
Locale {{locale}}. JSON { "fields": { … } }.
PROMPT,
    'user' => <<<'PROMPT'
Locale: {{locale}}

Context:
{{fields_json}}

Schema:
{{schema_hint}}

Hướng dẫn thêm:
{{extra_instructions}}
PROMPT,
];
