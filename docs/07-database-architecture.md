# ViTravel — Kiến trúc cơ sở dữ liệu

> **Ngày:** 2026-08-05 (multi-project + catalogue dịch vụ)  
> **Stack:** Laravel 13 · MySQL/MariaDB (utf8mb4) · Eloquent  
> **Tham chiếu:** `docs/03-data-models.md` · `docs/11-multi-project-architecture.md` · Hitour SEO hub  
> **Phạm vi:** UI Blade + Admin API. **Không** gồm booking/payment/cart (ngoài V1).

---

## 0. Multi-project (runtime tenancy)

| Mục tiêu | Cách đạt |
|---|---|
| Nhiều domain / một codebase | Bảng `projects`, `project_domains`, `project_user` |
| Cách ly dữ liệu | Cột `project_id` trên bảng nội dung + trait `BelongsToProject` |
| URL không đụng nhau giữa site | Unique SEO/slug scope theo `project_id` (vd. `(project_id, language_id, slug_full)`) |

Chi tiết vận hành & cấu hình: **`docs/11-multi-project-architecture.md`**.

---

## 1. Mục tiêu thiết kế

| Mục tiêu | Cách đạt |
|---|---|
| Linh hoạt | Taxonomy chuẩn hoá (travel style, category, tag); JSON chỉ cho list không-facet; polymorphic FAQ/media |
| Ổn định | FK + soft delete nội dung; status workflow (`draft/published/archived`); lead/comment moderation |
| Tối ưu query | Index facet listing (`country_id`, `duration_days`, pivots); unique **`(project_id, language_id, slug_full)`** cho routing |
| Đẳng cấp i18n/SEO | Pattern Hitour: `languages` + `seo_entries` hub + `*_translations` — sẵn sàng VI/EN (và locale mới) |
| Greenfield sạch | Không kế thừa legacy Blade-as-content của Hitour; body rich text lưu DB |

---

## 2. Triết lý (vay từ Hitour, điều chỉnh cho ViTravel)

### 2.1. Giữ từ Hitour (nâng cấp)

1. **Translation Table (hướng B)** — mỗi entity có bảng `*_translations` với `UNIQUE(entity_id, language_id)`.
2. **SEO hub** — mọi URL công khai đi qua `seo_entries` + `seo_entry_translations` (slug / meta / status theo locale).
3. **Tách cột locale-independent vs dependent** — giá, ngày, rating, FK ở bảng gốc; title/content/slug ở translation.
4. **Trait `HasTranslations` + `HasSeo`** — magic accessor / fallback locale.

### 2.2. Khác Hitour (vì product khác)

| Hitour (OTA) | ViTravel (Agency / lead-gen) |
|---|---|
| `tour_info`, ship, hotel, combo, booking | `packages` (tour \| cruise), **catalogue dịch vụ** (`services`), blog, brand, **3 lead forms** |
| Blade file content theo slug | Rich text / HTML trong DB |
| Bảng `seo` legacy + migrate dần | `seo_entries` greenfield sạch |
| Commerce / giá động / booking qty | `price_from` display-only; CTA → inquiry |

### 2.3. Package thống nhất Tour + Cruise

UI có 2 route (`/tours/...`, `/cruises/...`) nhưng layout chi tiết gần như giống nhau. DB dùng **một bảng `packages`** với `type = tour|cruise` + cột cruise-only nullable (`cruise_type`, `departure_port`, `boat_class`, `nights_on_board`). Cabin types / itinerary gắn theo `package_id`.

---

## 3. Sơ đồ lớp bảng

```
┌─────────────┐     ┌──────────────┐     ┌─────────────────────┐
│  languages  │◄────│ seo_entries  │────►│ seo_entry_translations│
└─────────────┘     │  (hub URL)   │     │  slug, meta, status │
       ▲            └──────┬───────┘     └─────────────────────┘
       │                   │ morph reference_type/id
       │            ┌──────▼───────┐
       │            │   packages   │◄── pivots: categories, styles, related, destinations
       │            │ tour|cruise  │
       │            └──────┬───────┘
       │                   ├── package_translations
       │                   ├── package_itinerary_days (+ translations)
       │                   └── package_cabin_types (+ translations) [cruise]
       │
       │            ┌──────────────────┐
       │            │ service_categories│◄── cluster: train|flight|stay|experience|other
       │            └────────┬─────────┘
       │                     └── services (+ service_translations, options, attrs JSON)
       │                           └── service_options (+ service_option_translations)
       │
       ├── countries / destinations (+ translations)
       ├── taxonomies: travel_styles, tour_categories, blog_categories,
       │               content_type_tags, keyword_tags
       ├── articles (+ translations, tag pivots, related tours/articles)
       ├── comments (moderation)
       ├── reviews (morph package|company)
       ├── brand: company_*, team, offices, usps, hero_pills, static_pages
       ├── media + media_attachments (morph gallery)
       ├── faqs (morph)
       └── leads: quick_inquiries, custom_tour_requests, contact_messages
```

---

## 4. Chi tiết nhóm bảng

### 4.1. Nền tảng

| Bảng | Vai trò |
|---|---|
| `languages` | Danh mục locale (`vi` default, `en` active) |
| `media` | Thư viện asset (path, mime, size, w/h) |
| `seo_entries` | Hub routing: `reference_type`, `reference_id`, og image, aggregate rating cache |
| `seo_entry_translations` | `slug`, `slug_full`, meta title/description, keywords, status, translation_status |

### 4.2. Địa lý & taxonomy

| Bảng | Facet / UI |
|---|---|
| `countries` | Mega menu, hub `/tours`, listing `/tours/{country}` |
| `destinations` | Filter phụ, blog sidebar level=destination |
| `travel_styles` | Facet 11 “Stile di viaggio” |
| `tour_categories` | Duration/region/theme listing + FAQ; URL `/tours/{country}/{slug}`; seed keys `subtitle` / `seo_body` |
| `blog_categories` | Sidebar “Categorie del blog” |
| `content_type_tags` | “Filtra articoli” |
| `keyword_tags` | Tag cloud SEO |

### 4.3. Sản phẩm (`packages`)

Cột quan trọng (locale-independent):

- `type`, `country_id` (quốc gia chính URL/SEO), `code`, `duration_days`, `duration_nights`
- Pivot `package_country` — nhiều quốc gia cho bộ lọc (tour kết hợp: VN + Campuchia hiện khi lọc VN hoặc Campuchia)
- `rating`, `review_count`, `price_from`, `currency`
- `is_featured`, `is_hot_deal`, `status`, `published_at`
- Cruise: `cruise_type`, `departure_port`, `boat_class`, `nights_on_board`

Translation: `title`, `start/end_location`, `places_to_visit` (JSON), `featured_quote_*`, highlights, inclusions/exclusions/notes (JSON), body fields.

Itinerary: `day_number`, meals, `transport_icons` (JSON), overnight, content (translated).

### 4.3a. Catalogue dịch vụ (`services`)

Tách entity khỏi `packages` — phục vụ 5 hub SEO độc lập (vé tàu, máy bay, lưu trú, vui chơi, dịch vụ khác).

| Bảng | Vai trò |
|---|---|
| `service_categories` | Danh mục con trong cụm (`cluster`, `slug`, `name`, `intro`, sort); SEO type `service_category`, parent = hub cụm |
| `services` | Sản phẩm dịch vụ: `cluster`, `service_category_id`, `country_id?`, `code`, giá display, rating, `attrs` (JSON), flags featured/hot |
| `service_translations` | `title`, `location_label`, `summary`, highlights/inclusions/exclusions/notes (JSON), `content` |
| `service_options` + `service_option_translations` | Biến thể giá (ghế tàu, loại phòng, combo…) |

Config: `config/services_catalog.php` (`clusters`, `hub_to_cluster`); hub copy/SEO defaults trong `config/seo.php` → `hubs`.

Seed: keys `service_clusters`, `service_categories`, `services`, `service_listing_faqs` trong `project/seed_{name}.php`. Seeder: **`ServiceCatalogSeeder`** (sau `ContentSeeder`, trước `TourCategorySeeder`).

**Admin CRUD dịch vụ:** chưa triển khai (roadmap).

### 4.4. Content hub

`articles` + translations + pivots tags + `article_package` (cầu nối content→product) + `article_related` + `comments` (`pending|approved|rejected`).

### 4.5. Social proof & brand

`reviews` (morph), `team_members` (+ achievements/skills/experiences/degrees/activity_images), `offices`, `experience_albums`/`photos`, `experience_videos`, `usps`, `company_values`, `reasons_to_choose_us`, `reference_persons`, `company_profiles` (intro/mission/vision/policy singleton-ish), `hero_pills`, `review_platforms`, `static_pages`.

### 4.6. Shared

- `faqs`: `faqable_type/id`, order, translations (question/answer)
- `media_attachments`: `mediable_type/id`, `media_id`, role (`cover|gallery|map|avatar|og`), sort

### 4.7. Leads (3 schema tách — bắt buộc)

1. `quick_inquiry_leads`
2. `custom_tour_requests` (JSON arrays cho countries/accommodation)
3. `contact_messages`

Không gộp 1 bảng “leads” chung — reporting nguồn lead phải tách sạch.

---

## 5. Quy ước kỹ thuật

### 5.1. Naming

- Bảng: `snake_case` số nhiều (`packages`, `seo_entries`)
- Translation: `<entity>_translations`
- Pivot: alphabetical hoặc domain-first (`package_travel_style`, `article_content_type_tag`)
- Enum string ngắn, ổn định (`tour`, `cruise`, `published`, `pending`)

### 5.2. Status

| Entity | Status |
|---|---|
| Nội dung public | `draft` \| `published` \| `archived` |
| Lead | `new` \| `contacted` \| `quoted` \| `closed` \| `spam` |
| Comment | `pending` \| `approved` \| `rejected` |
| SEO locale | `draft` \| `published` \| `archived` |

### 5.3. Index bắt buộc

- `seo_entry_translations (language_id, slug_full)` UNIQUE
- `packages (type, country_id, status, is_featured)`
- `packages (duration_days)`, `packages (cruise_type)`
- Pivot FKs có index hai chiều
- `articles (status, published_at)`, `comments (article_id, status)`
- Lead `created_at`, `status`

### 5.4. Soft delete

Áp dụng cho: packages, articles, countries, destinations, taxonomies, team, media (không xoá cứng khi đang gắn). Lead/comment **không** soft-delete mặc định (giữ audit; dùng status `spam`/`rejected`).

### 5.5. Fallback locale

Đọc translation: locale hiện tại → `languages.is_default` → null. Không bao giờ “lai” `slug_full` giữa 2 locale (quy tắc Hitour).

### 5.6. Phân tầng URL (SEO hub — Hitour universal rule)

**Quy tắc duy nhất** (`SeoService::buildSlugFull`) — áp dụng cho **mọi** `type`, không hardcode path theo type:

| Điều kiện | `slug_full` |
|---|---|
| Có parent + `parent.slug_full` | `{parent.slug_full}/{slug}` |
| Không parent (hub / root) | `/{slug}` |

Hubs cấp 1 (`parent_id = null`, `level = 1`) từ `config('seo.hubs')`:

| Hub key | Public URL (mặc định) | SEO `type` | Con điển hình |
|---|---|---|---|
| `tours_hub` | `/tours` | `tours_hub` | `country` → `package_tour` / `tour_category` |
| `cruises_hub` | `/cruises` | `cruises_hub` | `cruise_type` → `package_cruise` |
| `guide_hub` | `/cam-nang-du-lich` | `guide_hub` | `blog_category` → `article` |
| `trains_hub` | `/ve-tau-cao-toc` | `trains_hub` | `service_category` → `service` |
| `flights_hub` | `/ve-may-bay` | `flights_hub` | `service_category` → `service` |
| `stays_hub` | `/luu-tru` | `stays_hub` | `service_category` → `service` |
| `experiences_hub` | `/ve-vui-choi` | `experiences_hub` | `service_category` → `service` |
| `extras_hub` | `/dich-vu-khac` | `extras_hub` | `service_category` → `service` |

Ví dụ cây:

| Layer | URL | `type` | Parent |
|---|---|---|---|
| Hub tour | `/tours` | `tours_hub` | null |
| Quốc gia | `/tours/viet-nam` | `country` | hub tour |
| Tour | `/tours/viet-nam/ha-long-5-ngay` | `package_tour` | country |
| Hub cruise | `/cruises` | `cruises_hub` | null |
| Loại | `/cruises/du-thuyen-ha-long` | `cruise_type` | hub cruise |
| Cabin | `/cruises/du-thuyen-ha-long/...` | `package_cruise` | cruise_type |
| Hub guide | `/cam-nang-du-lich` | `guide_hub` | null |
| Chuyên mục | `/cam-nang-du-lich/{cat}` | `blog_category` | hub / category |
| Bài viết | `…/{cat}/{slug}` | `article` | blog_category |
| Hub vé tàu | `/ve-tau-cao-toc` | `trains_hub` | null |
| Danh mục dịch vụ | `/ve-tau-cao-toc/ha-noi-da-nang` | `service_category` | trains_hub |
| Dịch vụ | `/ve-tau-cao-toc/ha-noi-da-nang/{slug}` | `service` | service_category |

Luồng admin:

1. Tạo/sửa **Hub** (cấp 1) — banner + SEO, `parent=null`, `level=1`
2. Entity con chọn **Trang cha** → `level = parent.level + 1`
3. `buildSlugFull` nối `parent.slug_full` + `slug` (không hardcode `/tours`/`/cruises`)
4. `assertSlugFullUnique(language_id, slug_full)` — throw ValidationException nếu trùng locale
5. Lưu `seo_entry_translations.slug_full`
6. Đổi slug/parent cha → `cascadeSlugFullChildren` cập nhật mọi con cùng locale + `redirect_info` 301 (`createRedirect301`, prefix locale nếu không default)

**Public routing (Hitour catch-all):** `Route::fallback(RoutingController)` → `SeoService::findBySlugFull` → dispatch theo `seo_entries.type` (gồm `trains_hub` … `service`). Default locale unprefixed; khác: `/{locale}/…`. `locale_route()` resolve `tours.*` / `cruises.*` / `guide.*` / **`services.*`** qua slug_full hiện tại — **không** hardcoded path trên public GET.

Bảng `redirect_info` (`url_old`, `url_new`) + middleware `CheckRedirect` (sau DetectLocale / DetectCurrency).

Breadcrumb UI + JSON-LD: chuỗi parent SEO (`SeoService::breadcrumbsForEntry`); schema qua `SchemaService::breadcrumbList`.

---

## 6. Mapping UI → bảng

| UI / Route | Bảng chính |
|---|---|
| Home hero pills | `hero_pills` |
| Home featured tours | `packages` (`is_featured`, type=tour) |
| Bento destinations | `countries.home_grid_size` |
| Tour listing + filter | `packages` + `travel_styles` + duration buckets + `category[]` |
| Tour topic (`tour_category`) | `TourController::category` + `ListingChrome`; URL `/tours/{country}/{slug}` |
| Tour/Cruise detail | `packages` + itinerary + cabin + faqs + reviews |
| Service hub / listing / detail | `services` + `service_categories` + `ListingChrome`; hub StaticPage / `listing_hubs` |
| Blog listing/detail | `articles` + blog taxonomies + comments |
| About | `company_profiles`, values, reasons, reference_persons, team |
| Reviews page | `reviews` (company + package) |
| Gallery / Video | `experience_albums`, `experience_videos` |
| Customize / Contact / Quick inquiry | 3 bảng lead |
| Header nav dịch vụ | `service_categories` (group by `cluster`) + `config/services_catalog.php` |
| Header VI/EN | `languages` + seo translations |

---

## 7. Lộ trình dùng schema

1. **Migrate** toàn bộ file `database/migrations/2026_07_27_*`
2. **Seed** `LanguageSeeder` + `TaxonomySeeder` + `ContentSeeder` + **`ServiceCatalogSeeder`**
3. **(Tiếp theo)** Seeder nội dung từ `SampleData` → thay mock trong controllers
4. **(Tiếp theo)** API/Form submit → 3 lead tables + comments pending
5. **(Tiếp theo)** Admin CMS đọc/ghi qua Eloquent + SEO hub

---

## 8. File liên quan

| File | Nội dung |
|---|---|
| `docs/03-data-models.md` | Product field spec (đã cập nhật ánh xạ SQL) |
| `database/migrations/2026_07_27_*` | Schema thật |
| `app/Models/*` | Eloquent + relations |
| `app/Models/Concerns/HasTranslations.php` | Trait i18n |
| `app/Models/Concerns/HasSeo.php` | Trait SEO hub |
| `database/seeders/LanguageSeeder.php` | vi / en |
| `database/seeders/TaxonomySeeder.php` | travel styles & tags khung |
| `database/seeders/CruiseTypeSeeder.php` | loại du thuyền (trước ContentSeeder) |
| `database/seeders/ContentSeeder.php` | countries / packages / articles + SEO parent_id |
| `database/seeders/ServiceCatalogSeeder.php` | **5 cụm dịch vụ** — categories + services + SEO parent |
| `database/seeders/TourCategorySeeder.php` | danh mục tour theo quốc gia |
| `config/services_catalog.php` | Cluster codes, hub_key map, nav labels |
| `project/seed_{name}.php` | Seed data dự án (tours, company, dịch vụ, …) — 1 file / 1 profile |
| `database/seeders/SeoHierarchySeeder.php` | **bước cuối** — `SeoService::rebuildPublicSeoTree` (slug_full hub→con) |

---

## 9. Quyết định đã chốt

1. Một bảng `packages` cho tour + cruise (DRY, vẫn phục vụ 2 URL group).
2. **Catalogue dịch vụ tách bảng `services`** — 5 hub SEO, không gộp vào `packages`.
3. SEO hub bắt buộc cho mọi entity có URL public.
4. Ba bảng lead tách — khớp form thật & analytics.
5. FAQ / gallery polymorphic — tránh nhân bảng FAQ cho từng entity.
6. Không implement booking tables trong V1.
