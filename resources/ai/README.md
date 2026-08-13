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
| `enrich_detail_program` | enrich | AI xây dựng chương trình tour/cruise/service |
| `enrich_listing_page` | enrich | AI xây dựng trang listing (hub / danh mục / chủ đề); input chỉ tiêu đề |

Chi tiết sản phẩm: [`docs/14-ai-system-prompts.md`](../../docs/14-ai-system-prompts.md).
