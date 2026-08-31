<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Quy tắc SEO thống nhất cho prompt AI + schema_hint runtime.
 */
final class SeoPromptRules
{
    /** Giới hạn lưu DB / validation admin (seo_entry_translations.seo_description). */
    public const DESCRIPTION_MAX = 350;

    public const TITLE_CONTENT_MIN = 65;

    public const TITLE_CONTENT_MAX = 85;

    /**
     * Khối hướng dẫn chèn vào system prompt (có {{brand}}).
     */
    public static function promptBlock(): string
    {
        return <<<'TXT'
═══ QUY TẮC SEO (seo_title / seo_description) ═══

**seo_title** — tiêu đề SERP:
- Cấu trúc BẮT BUỘC: `{nội dung} | {{brand}}` — dùng dấu gạch đứng `|` (có khoảng trắng hai bên). KHÔNG dùng « — » hay « - » giữa nội dung và thương hiệu.
- Phần `{nội dung}` (trước `|`): khoảng 65–85 ký tự. Không cắt cứng 60. Phải nói được tổng quan trang (điểm đến / loại sản phẩm / USP / đối tượng khách), tối ưu SEO & CTR, bám insight người tìm kiếm.
- Nếu ngăn cách ý trong phần nội dung, dùng em dash `—` (U+2014), không dùng gạch ngắn `-`.
- Không lặp brand; không spam từ khóa; không viết HOA toàn bộ.
- Ví dụ: `Tour Phú Quốc 3 ngày 2 đêm — biển đảo & resort cao cấp | {{brand}}`

**seo_description** — meta description:
- Độ dài linh hoạt 200–350 ký tự (ưu tiên đủ ý, không cắt cho vừa 160).
- Mô tả chi tiết, cụ thể: phạm vi trang, điểm đến/dịch vụ, lợi ích, USP, lý do chọn «{{brand}}», CTA nhẹ (đặt tour, khám phá, liên hệ…).
- Giọng tin cậy, hấp dẫn, tối ưu CTR — không sáo rỗng, không nhồi từ khóa, không bịa giá/số liệu.
- Một đoạn plain text liền mạch (cấm HTML).

**seo_slug**: Latin, chữ thường, ngắn, ngăn cách `-`.
TXT;
    }

    public static function schemaTitleJsonHint(): string
    {
        return '"seo_title": "string — `{nội dung 65–85 ký tự} | {{brand}}`"';
    }

    public static function schemaDescriptionJsonHint(): string
    {
        return '"seo_description": "string — 200–350 ký tự, plain text, chi tiết + CTR"';
    }
}
