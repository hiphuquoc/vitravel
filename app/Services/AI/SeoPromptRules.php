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

    public const SLUG_MIN = 35;

    public const SLUG_MAX = 90;

    public const RATING_STAR_MIN = 4.7;

    public const RATING_STAR_MAX = 4.9;

    public const RATING_COUNT_MIN = 200;

    public const RATING_COUNT_MAX = 3000;

    public const PROJECT_BRIEF_MAX = 5000;

    /**
     * Khối hướng dẫn chèn vào system prompt enrich meta (có {{brand}}).
     */
    public static function promptBlock(): string
    {
        return <<<'TXT'
═══ QUY TẮC SEO (seo_title / seo_description / seo_slug / rating) ═══

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

**seo_slug** — URL segment (tối ưu SEO nâng cao):
- BẮT BUỘC bám sát phần `{nội dung}` của **seo_title** (trước `|`), không tách rời chủ đề.
- Latin, chữ thường, không dấu tiếng Việt, ngăn cách `-`. KHÔNG slug quá ngắn (tránh 1–2 từ chung chung).
- Độ dài mục tiêu 35–90 ký tự: gồm từ khóa chính (điểm đến, loại tour/dịch vụ, USP ngắn) — ngắn gọn nhưng đủ ý cho SERP.
- Bỏ từ dừng thừa (của, và, tại…) nếu không mất nghĩa; giữ tên địa danh / thương hiệu địa phương quan trọng.
- Ví dụ title `Tour Phú Quốc 3N2Đ — biển đảo resort cao cấp | Brand` → slug `tour-phu-quoc-3n2d-bien-dao-resort-cao-cap` (không chỉ `phu-quoc`).

**rating_aggregate_star** + **rating_aggregate_count** — Schema rating (JSON-LD):
- `rating_aggregate_star`: số thập phân 1 chữ số, trong khoảng **4.7 – 4.9** (thang 5). Không 5.0, không dưới 4.7.
- `rating_aggregate_count`: số nguyên **200 – 3000** — tự nhận định độ «hot» của trang/danh mục/điểm đến:
  • Ngách / ít tìm kiếm / sản phẩm đặc thù: 200–600
  • Phổ biến vừa (điểm đến quen thuộc, danh mục có lượng tìm): 700–1500
  • Rất hot (Phú Quốc, Đà Nẵng, Hạ Long, Sapa, tour Tết, khách sạn 5 sao trung tâm…): 1500–3000
- Hai field luôn đi cùng nhau; không bịa testimonial chi tiết — chỉ số aggregate cho schema.
TXT;
    }

    /**
     * Khối bối cảnh dự án (có {{project_brief}}).
     */
    public static function projectBriefBlock(): string
    {
        return <<<'TXT'
═══ BỐI CẢNH DỰ ÁN (ưu tiên khi viết) ═══
{{project_brief}}
- Dùng mô tả trên để bám đúng phong cách, đối tượng khách, USP và phạm vi sản phẩm của dự án.
- Nếu mô tả trống hoặc «(chưa cấu hình)»: suy luận từ brand + loại trang + web search.
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

    public static function schemaSlugJsonHint(): string
    {
        return '"seo_slug": "string — Latin, 35–90 ký tự, bám seo_title, dấu `-`"';
    }

    public static function schemaRatingJsonHint(): string
    {
        return '"rating_aggregate_star": "number 4.7–4.9 (1 chữ số thập phân)", "rating_aggregate_count": "integer 200–3000"';
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public static function normalizeMetaFields(array $fields): array
    {
        if (isset($fields['rating_aggregate_star'])) {
            $star = round((float) $fields['rating_aggregate_star'], 1);
            $fields['rating_aggregate_star'] = max(self::RATING_STAR_MIN, min(self::RATING_STAR_MAX, $star));
        }

        if (isset($fields['rating_aggregate_count'])) {
            $count = (int) $fields['rating_aggregate_count'];
            $fields['rating_aggregate_count'] = max(self::RATING_COUNT_MIN, min(self::RATING_COUNT_MAX, $count));
        }

        if (isset($fields['seo_slug']) && is_string($fields['seo_slug'])) {
            $slug = trim($fields['seo_slug']);
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($slug)) ?? '';
            $slug = trim($slug, '-');
            if (mb_strlen($slug) > self::SLUG_MAX) {
                $slug = rtrim(mb_substr($slug, 0, self::SLUG_MAX), '-');
            }
            $fields['seo_slug'] = $slug;
        }

        if (isset($fields['seo_description']) && is_string($fields['seo_description'])) {
            $fields['seo_description'] = mb_substr(trim($fields['seo_description']), 0, self::DESCRIPTION_MAX);
        }

        return $fields;
    }

    public static function clipProjectBrief(?string $brief): string
    {
        $brief = trim((string) $brief);
        if ($brief === '') {
            return '(chưa cấu hình — dùng brand và loại trang để suy luận)';
        }

        return mb_strlen($brief) > self::PROJECT_BRIEF_MAX
            ? mb_substr($brief, 0, self::PROJECT_BRIEF_MAX)
            : $brief;
    }
}
