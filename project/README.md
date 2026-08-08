# Seed đa dự án + multi-project runtime

> **Cho AI / contributor:** thêm tính năng hoặc block nội dung mới → mở rộng file `project/seed_*.php` của profile; seeder/UI chỉ đọc `ProjectSeed` / `SampleData`. Không hardcode data trong seeder.
>
> **Hướng dẫn vận hành (seed, đổi dự án public/admin, domain):**  
> [`docs/11-multi-project-architecture.md`](../docs/11-multi-project-architecture.md)

**Không dùng `PROJECT_SEED` / `COMPANY_*` trong `.env`.** Profile chọn bằng CLI / discover:

```bash
# Seed tất cả seed_*.php
php artisan migrate:fresh --seed

# Một profile — Cát Bà Hub: hicatba.dev (local) + hicatba.com (prod); code = hicatba
php artisan project:seed hicatba --domain=hicatba.dev --domain=hicatba.com
php artisan project:ensure vitravel --domain=vitravel.dev
# phuquy.net: phuquy.dev (local) + phuquy.net (prod)
php artisan project:seed phuquy --domain=phuquy.dev --domain=phuquy.net --name="phuquy.net"
```

| Profile | File | Domain |
|---------|------|--------|
| `vitravel` | `project/seed_vitravel.php` | `vitravel.dev` |
| `hicatba` | `project/seed_hicatba.php` | `hicatba.dev` / `hicatba.com` |
| `phuquy` | `project/seed_phuquy.php` | `phuquy.dev` / `phuquy.net` |

Tuỳ chọn `meta.primary_domain` / `meta.domains` → bảng `project_domains`.

Local cùng Host: `https://vitravel.dev/?project=hicatba` — chi tiết mục 2 trong docs/11.

`ProjectSeed::useProfile()` / `ProjectContext` — set bởi middleware / `project:seed`.

---

## Layout thư mục gợi ý

```
project/
  README.md                 ← schema (file này)
  seed_vitravel.php         ← 1 dự án = 1 file (tours + company + dịch vụ + …)
  seed_hicatba.php
  seed_phuquy.php           ← đảo Phú Quý (Bình Thuận): phuquy.dev / phuquy.net
```

**Dự án mới:** copy `seed_*.php`, sửa `meta` / `company` / catalogue / tours…; dev: thêm file rồi `migrate:fresh --seed` hoặc `project:seed {ten}`.

---

## Schema cho AI / clone (từng key)

File seed là **single source** cho seed + fallback UI (`ProjectSeed` → `SampleData`).

**Khi nhờ AI dựng lại:** đưa README này + liệt kê phần giữ/đổi/bỏ. Chỉ sửa file seed của profile (và nói rõ nếu cần đổi seeder/SEO type).

### Quy tắc chung

1. Key top-level optional về sản phẩm, nhưng **đúng shape** nếu seeder còn đọc.
2. `slug` = kebab-case ASCII, unique trong cùng loại **và cùng project**.
3. i18n: thường `vi` / `en`. Locale mặc định = `vi`.
4. Package: `tours` → `type=tour`; `cruises` → `type=cruise` (tên technical — có thể = combo/dịch vụ khác nếu giữ pipeline SEO).
5. Đổi slug cha/con → seed lại `SeoHierarchySeeder`.

### 1b. `company` (thông tin dự án)

Key top-level **`company`** trong file seed dự án (`project/seed_{name}.php`). Seed → bảng `company_profiles` (có `project_id`). Admin: **Cài đặt → Thông tin dự án** (`/settings/site`) + header `X-Project-Code`.

| Field | Ý nghĩa |
|-------|---------|
| `name`, `legal_name`, `tagline`, `slogan`, `license_number` | Thương hiệu |
| `contact.*` | email, phone, whatsapp, zalo, hotline_label |
| `address.*` | street, locality, region, postal, country |
| `social.{facebook\|youtube\|instagram\|tiktok}` | `{ label, icon, url }` |
| `schema.*` | available_language, contact_type, logo |
| `footer.copyright`, `footer.show_dmca_badge` | Footer (`:year` / `:license` / `:name`) |

Runtime: `CompanyProfile::contact()` / `view_data()->companyContact()`. `config/company.php` chỉ còn fallback rỗng khi chưa seed (không `COMPANY_*` env).

### 1. `meta`

| Field | Ý nghĩa |
|-------|---------|
| `brand`, `tagline` | Tên / slogan (meta seed; runtime brand lấy từ `company`) |
| `admin.*` | User admin lúc seed |
| `primary_domain`, `domains[]` | Map Host → project — Cát Bà Hub: **`hicatba.dev`** + **`hicatba.com`** (multi-domain) |
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

- `countries` — `{ slug, name, size, tagline }[]` → SEO `country` (CMS entity)
- Alias dự án 1 điểm đến: **`zones` / `zoneSlug` / `zone_translations`** được `ProjectSeed` chuẩn hoá thành `countries` / `countrySlug` / `country_translations` lúc load
- `country_translations` — i18n theo slug (hoặc `zone_translations`)
- `tour_categories` — danh mục con dưới country/zone (`countrySlug` hoặc `zoneSlug`)
- `meta.country_codes` — optional; nếu thiếu, tự sinh từ slug

### 4. Sản phẩm

- `tours` → `package_tour`
- `cruises` + `cruise_types` → `package_cruise` (đổi nghĩa dịch vụ: giữ key hoặc bảo AI đổi seeder/`config/seo.php`)

### 4b. Catalogue dịch vụ (5 cụm)

Keys nằm trong cùng file seed dự án (`service_clusters`, `service_categories`, `services`, `service_listing_faqs`) — không tách file phụ.

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
- **`customize_form`** — form Tour riêng: `destinations_label`, `accommodation_label`, `budget_note`, `accommodation[]` (i18n `vi`/`en`); điểm đến mặc định từ countries/zones `show_in_customize_form` (có thể ghi đè bằng `destinations[]`)
- **`nav`** — nhãn header + hub cruise (seed-only, không admin): `about_group`, `cruise.{label,all_label,all_meta,search_hint,search_placeholder,hub_title,hub_subtitle}` — đổi «Du thuyền» thành «Thuyền & trải nghiệm» tuỳ dự án

### Map seeder

| Key | Seeder | SEO |
|-----|--------|-----|
| countries / tours | ContentSeeder | country / package_tour |
| company | **HomeFeaturedSeeder** (`seedCompanyIdentity`) | — (company_profiles) |
| cruise_types / cruises | CruiseType + Content | cruise_type / package_cruise |
| service_categories / services | **ServiceCatalogSeeder** | `service_category` / `service` (+ 5 hub types) |
| tour_categories | TourCategorySeeder | tour_category |
| (cuối) | **SeoHierarchySeeder** | rebuild cây |

### Prompt gợi ý

```
Profile: vitravel (file project/seed_vitravel.php).
Dựa trên project/README.md, [giữ|đổi|xóa] key: …
Xuất đủ shape vào đúng file seed của profile, không hardcode seeder.
Nhắc: php artisan migrate --seed  (seed all) hoặc project:seed {profile}
```
