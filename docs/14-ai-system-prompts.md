# AI hệ thống — prompts, dịch trang, xây dựng chương trình

## Tổng quan

| Feature | Endpoint | Prompt key | AiPermission |
|---|---|---|---|
| Trạng thái provider | `GET /ai/status` | — | `ai.use` |
| Dịch toàn trang form | `POST /ai/translate-page` | `translate_page` | `ai.use` |
| Xây dựng chương trình chi tiết | `POST /ai/enrich-detail-program` | `enrich_detail_meta` · `enrich_detail_content` · `enrich_detail_faq` (theo `stage`) | `ai.use` |
| Xây dựng trang listing | `POST /ai/enrich-listing-page` | `enrich_listing_meta` · `enrich_listing_body` · `enrich_listing_faq` (theo `stage`) | `ai.use` |
| Xây dựng trang lưu trú | `POST /ai/enrich-stay` | `enrich_stay_meta` · `enrich_stay_property` · `enrich_stay_faq` (theo `stage`) | `ai.use` |
| Crawler lưu trú | `POST /stay-crawls/*` | **không AI** — `StayHtmlMapper` (HTML → schema). Prompt `crawl_stay_extract` chỉ còn CLI `stay:crawl ai` | `services.create` |
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

Ba luồng **độc lập**, mỗi luồng một prompt. Nút **AI chương trình** mặc định chạy **tuần tự** meta → content → faq (bước sau gửi form đã merge bước trước). Có thể chọn 1 luồng nếu kết quả chưa đạt.

| `stage` | Prompt | Input | Output |
|---|---|---|---|
| `meta` | `enrich_detail_meta` | **chỉ `title`** | summary, highlights_intro, quote, places, start/end (cruise: port/class), SEO |
| `content` | `enrich_detail_content` | title + toàn bộ thông tin chương trình | itinerary HTML (tour/cruise) hoặc `content` HTML (service) + bullets/inclusions |
| `faq` | `enrich_detail_faq` | title + SEO + nội dung chi tiết | `faqs` 5–8 cặp `question`/`answer` |

Body bắt buộc có `stage`. AI trả `{ "fields": { … } }` đúng schema luồng đó (không nhét field luồng khác).

Prompt content ưu tiên **HTML lịch trình từng ngày**: `<strong>` khung giờ + địa danh, unique SEO, cuối mỗi ngày 1 `<figure>` ảnh tạm (`placehold.co`).

**Thương hiệu:** prompt nhận `{{brand}}` / `{{project_code}}` từ `CompanyProfile` + `ProjectContext` (header `X-Project-Code`). Không hardcode ViTravel.

**Web search** (mặc định bật): `AI_ENRICH_WEB_SEARCH=true`. Timeout/tokens: `AI_ENRICH_TIMEOUT`, `AI_ENRICH_MAX_TOKENS` (content dùng max; meta/faq giới hạn thấp hơn).

Ví dụ body (luồng nội dung):

```json
{
  "entity_type": "tour_package",
  "locale": "vi",
  "stage": "content",
  "instructions": "Nhấn mạnh trải nghiệm biển đảo, 3 ngày 2 đêm",
  "fields": {
    "title": "…",
    "duration_days": 3,
    "summary": "…",
    "itinerary": []
  }
}
```

Luồng `meta` chỉ cần `{ "title": "…" }`. Admin UI: chọn luồng trên modal xác nhận.

**Quy tắc SEO (meta):** `seo_title` = `{nội dung 65–85 ký tự} | {{brand}}` (em dash `—` trong nội dung nếu cần); `seo_description` = 200–350 ký tự, chi tiết + CTR. Xem `App\Services\AI\SeoPromptRules`.

## Enrich trang listing (hub / country / chủ đề / cruise type / service category)

Cùng pattern 3 luồng (prompt riêng, `schema_hint` tuỳ `entity_type`):

| `stage` | Prompt | Input | Output |
|---|---|---|---|
| `meta` | `enrich_listing_meta` | **chỉ `title`** | title, subtitle (plain), seo_title, seo_description, seo_slug |
| `body` | `enrich_listing_body` | title + meta | `seo_body` HTML (3–5 `<p>` + `<strong>`) |
| `faq` | `enrich_listing_faq` | title + SEO + seo_body | `faqs` 5–6 cặp |

`entity_type`: `listing_hub` | `country` | `tour_category` | `cruise_type` | `service_category`

Admin map về form:

| entity_type | subtitle → | seo_body → |
|---|---|---|
| `listing_hub` | `body` | `seo_body` |
| `country` | `tagline` | `long_form_content` |
| `tour_category` | `description` | `seo_intro` |
| `service_category` | `intro` | `seo_body` |
| `cruise_type` | `intro` | `seo_body` |

Ví dụ body (luồng meta):

```json
{
  "title": "Tour biển đảo Phú Quốc",
  "entity_type": "tour_category",
  "stage": "meta",
  "locale": "vi",
  "instructions": "Nhấn mạnh snorkeling và sunset"
}
```

Luồng `body`/`faq` gửi thêm `fields` (subtitle, seo_* đã có trên form). Form listing chưa có UI FAQ thì FAQ chỉ nằm trong state (toast nhắc).

**Quy tắc SEO (meta):** giống enrich chương trình — `SeoPromptRules`.

## Quản lý UI

**Cài đặt → Prompt AI** (`/settings/ai-prompts/`) — list, sửa system/user, sync seed, xem usage gần đây. Quyền `ai.manage` (owner/admin).

## CLI

```bash
php artisan migrate
php artisan ai:sync-prompts
php artisan ai:sync-prompts --force   # ghi đè cả bản đã customize
```
