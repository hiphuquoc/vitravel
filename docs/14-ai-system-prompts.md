# AI hệ thống — prompts, dịch trang, xây dựng chương trình

## Tổng quan

| Feature | Endpoint | Prompt key | AiPermission |
|---|---|---|---|
| Trạng thái provider | `GET /ai/status` | — | `ai.use` |
| Dịch toàn trang form | `POST /ai/translate-page` | `translate_page` | `ai.use` |
| Xây dựng chương trình chi tiết | `POST /ai/enrich-detail-program` | `enrich_detail_program` | `ai.use` |
| Danh sách / chi tiết prompt | `GET /ai/prompts`, `GET /ai/prompts/{key}` | — | `ai.manage` |
| Cập nhật prompt | `PUT /ai/prompts/{key}` | — | `ai.manage` |
| Sync file → DB | `POST /ai/prompts/sync` | — | `ai.manage` |
| Nhật ký usage | `GET /ai/usage` | — | `ai.manage` |

Base: `/api/v1/admin` · Bearer + `X-Project-Code`.

## Registry prompt

1. **Seed file:** `resources/ai/prompts/{key}.php` + đăng ký `config/ai.php` → `prompts`.
2. **DB:** bảng `ai_system_prompts` (sync bằng `php artisan ai:sync-prompts` hoặc `AiPromptSeeder`).
3. **Runtime:** `PromptRepository` đọc DB (active) trước, fallback file.
4. **Customize:** admin sửa qua API/UI → `is_customized=true`, sync file **không** ghi đè (trừ `--force` / `force: true`).
5. **Theo dõi:** bảng `ai_usage_logs` (prompt_key, provider, model, latency, success/error).

Schema file prompt tối thiểu:

```php
return [
  'key' => '…',
  'name' => '…',
  'category' => 'translate|enrich|…',
  'description' => '…',
  'version' => 1,
  'variables' => ['fields_json', …],
  'entity_types' => ['tour_package', …],
  'output_format' => 'json',
  'system' => '…',
  'user' => '… {{fields_json}} …',
];
```

Chi tiết thêm prompt: `resources/ai/README.md`.

## Provider

`.env`: `AI_DEFAULT_PROVIDER`, `AI_OPENAI_API_KEY` / `GEMINI_API_KEY` / `DEEPSEEK_API_KEY`, model overrides trong `config/ai.php`.  
Gateway: `App\Services\AI\AiGateway` (OpenAI-compatible + fallback).

## Enrich chương trình (tour / cruise / service)

Client gửi toàn bộ field nội dung hiện có + `entity_type` + `locale`.  
AI trả `{ "fields": { … } }` đúng schema form admin (itinerary HTML, bullets, FAQ, SEO…).

Prompt `enrich_detail_program` (v2+) ưu tiên **HTML lịch trình từng ngày**: mô tả điểm đến, `<strong>` khung giờ + địa danh, SEO unique, cuối mỗi ngày 1 `<figure>` ảnh tạm (`placehold.co`) với alt/figcaption chuẩn.

**Web search** (mặc định bật): `AI_ENRICH_WEB_SEARCH=true` → OpenAI dùng Responses API + `tools: web_search`; Gemini thử `google_search`. Timeout/tokens: `AI_ENRICH_TIMEOUT`, `AI_ENRICH_MAX_TOKENS`.

Ví dụ body:

```json
{
  "entity_type": "tour_package",
  "locale": "vi",
  "instructions": "Nhấn mạnh trải nghiệm biển đảo, 3 ngày 2 đêm",
  "fields": {
    "title": "…",
    "duration_days": 3,
    "summary": "…",
    "itinerary": [],
    "faqs": []
  }
}
```

Admin UI: nút **AI chương trình** trên FormFooter (sát trái nhóm Hủy / Xem / Lưu) ở form tour, du thuyền, dịch vụ. Kết quả ghi vào form (chưa auto-save).

## Quản lý UI

**Cài đặt → Prompt AI** (`/settings/ai-prompts/`) — list, sửa system/user, sync seed, xem usage gần đây. Quyền `ai.manage` (owner/admin).

## CLI

```bash
php artisan migrate
php artisan ai:sync-prompts
php artisan ai:sync-prompts --force   # ghi đè cả bản đã customize
```
