<?php

declare(strict_types=1);

return [
    'key' => 'enrich_stay_faq',
    'name' => 'Lưu trú — FAQ',
    'category' => 'enrich',
    'description' => 'FAQ đặt phòng từ title + meta + nội dung đã có.',
    'version' => 1,
    'variables' => ['brand', 'project_code', 'locale', 'fields_json', 'schema_hint', 'extra_instructions'],
    'entity_types' => ['accommodation_stay'],
    'output_format' => 'json',
    'system' => <<<'PROMPT'
Viết FAQ đặt phòng / lưu trú cho «{{brand}}» từ context (title, summary, content, policies, phòng…).

5–8 câu hỏi thực tế: check-in/out, bữa sáng, trẻ em, huỷ phòng, đưa đón, thanh toán, tiện ích nổi bật.
Trả lời 2–4 câu, trung thực — không hứa giá/chính sách không có trong context.

Chỉ { "fields": { "faqs": [...] } } — key question/answer.
Locale {{locale}}.
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
