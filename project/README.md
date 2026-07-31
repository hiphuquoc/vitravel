# Seed đa dự án (`PROJECT_SEED`)

> **Cho AI / contributor:** thêm tính năng hoặc block nội dung mới → mở rộng file `project/seed_*.php` của profile đang chạy; seeder/UI chỉ đọc `ProjectSeed` / `SampleData`. Không hardcode data trong seeder. Rule Cursor: `.cursor/rules/project-seed.mdc` · `AGENTS.md`.

Mỗi môi trường / bản deploy chỉ **trỏ một file** dữ liệu qua `.env` — không ghi đè lẫn các file seed khác.

```env
PROJECT_SEED=vitravel
# PROJECT_SEED_DIR=project
```

| `PROJECT_SEED` | File load |
|----------------|-----------|
| `vitravel` | `project/seed_vitravel.php` |
| `other` | `project/seed_other.php` |
| `seed_bali.php` | `project/seed_bali.php` |

Fallback: nếu thiếu `seed_vitravel.php` nhưng còn `project/seed.php` → vẫn load (tương thích cũ).

```bash
php artisan migrate --seed
```

`ProjectSeed::path()` / `ProjectSeed::profile()` — xem file đang active.

---

## Layout thư mục gợi ý

```
project/
  README.md                 ← schema (file này)
  seed_vitravel.php         ← entry ViTravel (require seed.php hoặc tự chứa data)
  seed.php                  ← data ViTravel đầy đủ (hoặc rename thành seed_vitravel.php)
  seed_phuquoc.php          ← dự án khác (file độc lập)
  seed_combo_xyz.php
```

**Dự án mới:** copy `seed_vitravel.php` / `seed.php` → `seed_{ten}.php`, sửa nội dung, đặt `PROJECT_SEED={ten}`.

---

## Schema cho AI / clone (từng key)

File seed là **single source** cho seed + fallback UI (`ProjectSeed` → `SampleData`).

**Khi nhờ AI dựng lại:** đưa README này + liệt kê phần giữ/đổi/bỏ. Chỉ sửa file seed tương ứng `PROJECT_SEED` (và nói rõ nếu cần đổi seeder/SEO type).

### Quy tắc chung

1. Key top-level optional về sản phẩm, nhưng **đúng shape** nếu seeder còn đọc.
2. `slug` = kebab-case ASCII, unique trong cùng loại.
3. i18n: thường `vi` / `en`. Locale mặc định = `vi`.
4. Package: `tours` → `type=tour`; `cruises` → `type=cruise` (tên technical — có thể = combo/dịch vụ khác nếu giữ pipeline SEO).
5. Đổi slug cha/con → seed lại `SeoHierarchySeeder`.

### 1. `meta`

| Field | Ý nghĩa |
|-------|---------|
| `brand`, `tagline` | Tên / slogan |
| `admin.*` | User admin lúc seed |
| `country_codes` | `countries[].slug` → mã DB |
| `schema` | Version schema seed |

### 2. Taxonomy

- `content_tag_map` — `{ "Nhãn trên bài" => "code" }`
- `travel_styles` — `{ code => { vi, en } }`
- `travel_style_labels` — `{ code => "nhãn" }`
- `content_tags` — taxonomy blog CMS
- `review_platforms` — `{ code, name, rating, … }[]`
- `duration_buckets` — filter thời lượng

### 3. Điểm đến

- `countries` — `{ slug, name, size, tagline }[]` → SEO `country`
- `country_translations` — i18n theo slug
- `tour_categories` — danh mục con dưới country

### 4. Sản phẩm

- `tours` → `package_tour`
- `cruises` + `cruise_types` → `package_cruise` (đổi nghĩa dịch vụ: giữ key hoặc bảo AI đổi seeder/`config/seo.php`)

### 4b. Catalogue dịch vụ (5 cụm)

File **`project/seed_services.php`** — merge cuối `seed_vitravel.php` (`return array_merge($__vitravelSeed, require …)`).

| Key | Shape (tóm tắt) |
|-----|-----------------|
| `service_clusters` | `[{ code, nav_label, label, icon, hub_key, sort }]` — 5 cụm: `train`, `flight`, `stay`, `experience`, `other` |
| `service_categories` | `[{ cluster, slug, name, sort, intro? }]` — danh mục con dưới hub |
| `services` | `[{ code, cluster, category_slug, country_slug?, title, slug, price_from, currency, rating, highlights[], inclusions[], exclusions[], notes[], attrs{}, options[], faqs[], en? }]` |
| `service_listing_faqs` | `[{ q, a }]` — FAQ chung hub/listing dịch vụ |

Demo seed: **22 categories**, **32 services** (4 train, 4 flight, 8 stay, 9 experience, 7 other). Config runtime: `config/services_catalog.php`.

### 5. Blog

- `blog_categories`, `popular_keywords`, `articles` (`tags[]` ∈ keys của `content_tag_map`)

### 6–8. Team, about, home, footer

- `testimonials`, `team`, `videos`, `gallery_albums`, `usps`, `offices`
- `value_definitions`, `reason_definitions`, `reference_persons`, `about_page`
- `home_slides`, `hero_pills`, `home_sections`, `footer_*`, `listing_faqs`

### Map seeder

| Key | Seeder | SEO |
|-----|--------|-----|
| countries / tours | ContentSeeder | country / package_tour |
| cruise_types / cruises | CruiseType + Content | cruise_type / package_cruise |
| service_categories / services | **ServiceCatalogSeeder** | `service_category` / `service` (+ 5 hub types) |
| tour_categories | TourCategorySeeder | tour_category |
| (cuối) | **SeoHierarchySeeder** | rebuild cây |

### Prompt gợi ý

```
PROJECT_SEED hiện tại: vitravel (file project/seed_vitravel.php).
Dựa trên project/README.md, [giữ|đổi|xóa] key: …
Xuất đủ shape vào đúng file seed của profile, không hardcode seeder.
Nhắc: php artisan migrate --seed
```
