# AI prompts (ViTravel)

Thư mục seed **file** cho system prompt. Runtime ưu tiên đọc bảng `ai_system_prompts` (đã sync / admin sửa); file là nguồn seed + fallback.

## Thêm prompt mới

1. Tạo `resources/ai/prompts/{key}.php` trả về array:
   - `key`, `name`, `category`, `description`, `version`
   - `variables` (list tên `{{var}}`)
   - `entity_types` (gợi ý)
   - `system`, `user`, `output_format` (`json` | `text`)
2. Đăng ký trong `config/ai.php` → `prompts`.
3. Chạy `php artisan ai:sync-prompts` (hoặc migrate --seed qua `AiPromptSeeder`).
4. Gọi qua `PromptRepository::renderPrompt('key', $vars)` + `AiGateway::chat()`.
5. Ghi usage bằng `AiUsageLogger`.

## Quản lý

- Admin API: `GET/PUT /api/v1/admin/ai/prompts`, `POST .../sync`, `GET .../usage`
- UI: **Cài đặt → Prompt AI**
- Prompt đã `is_customized` sẽ không bị file seed ghi đè (trừ `--force`).

## Prompt hiện có

| Key | Category | Mục đích |
|---|---|---|
| `translate_page` | translate | AI dịch toàn trang form |
| `enrich_detail_meta` | enrich | Chương trình chi tiết — thông tin bài + SEO (chỉ tiêu đề) |
| `enrich_detail_content` | enrich | Chương trình chi tiết — lịch trình / HTML dịch vụ |
| `enrich_detail_faq` | enrich | Chương trình chi tiết — FAQ |
| `enrich_listing_meta` | enrich | Listing — H1 + subtitle + meta (chỉ tiêu đề) |
| `enrich_listing_body` | enrich | Listing — seo_body HTML |
| `enrich_listing_faq` | enrich | Listing — FAQ |
| `crawl_stay_extract` | crawl | *(legacy CLI)* HTML Booking → JSON — ingest/admin dùng `StayHtmlMapper` |
| `enrich_stay_meta` | enrich | Lưu trú — meta + SEO |
| `enrich_stay_property` | enrich | Lưu trú — bài giới thiệu SEO HTML (chỉ content) |
| `enrich_stay_faq` | enrich | Lưu trú — FAQ |

Chi tiết sản phẩm: [`docs/14-ai-system-prompts.md`](../../docs/14-ai-system-prompts.md).
