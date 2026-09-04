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
# Đảo Phú Quốc: phuquocangiang.dev (local) + phuquocangiang.com (prod)
php artisan project:seed phuquoc --domain=phuquocangiang.dev --domain=phuquocangiang.com --name="Đảo Phú Quốc"
# culaocham.net: culaocham.dev (local) + culaocham.net (prod)
php artisan project:seed culaocham --domain=culaocham.dev --domain=culaocham.net --name="culaocham.net"
# Đà Lạt: hidalat.dev (local) + hidalat.com (prod) + hidalat.vn
php artisan project:seed hidalat --domain=hidalat.dev --domain=hidalat.com --domain=hidalat.vn --name="Hi Đà Lạt"
# Cần Thơ: hicantho.dev (local) + hicantho.com (prod)
php artisan project:seed hicantho --domain=hicantho.dev --domain=hicantho.com --name="Hi Cần Thơ"
# Hà Giang: hihagiang.dev (local) + hihagiang.com (prod chính) + hihagiang.vn
php artisan project:seed hihagiang --domain=hihagiang.dev --domain=hihagiang.com --domain=hihagiang.vn --name="Hi Hà Giang"
# Hạ Long: hihalong.dev (local) + hihalong.vn (prod)
php artisan project:seed hihalong --domain=hihalong.dev --domain=hihalong.vn --name="Hi Hạ Long"
# Mũi Né: himuine.dev (local) + himuine.com (prod)
php artisan project:seed himuine --domain=himuine.dev --domain=himuine.com --name="Hi Mũi Né"
# Tam Đảo: hitamdao.dev (local) + hitamdao.com (prod)
php artisan project:seed hitamdao --domain=hitamdao.dev --domain=hitamdao.com --name="Hi Tam Đảo"
# Sa Pa: hisapa.dev (local) + hisapa.vn (prod)
php artisan project:seed hisapa --domain=hisapa.dev --domain=hisapa.vn --name="Hi Sa Pa"

# Chỉ seed lại cụm tour — XÓA SẠCH chủ đề/danh mục/SEO/chi tiết tour rồi tạo lại (không đụng bài viết/home/services/cruises)
php artisan project:seed hidalat --only=tours

# Xóa toàn bộ data 1 project rồi seed lại (giữ project khác)
php artisan project:seed hihagiang --fresh-project --domain=hihagiang.dev --name="Hi Hà Giang"
```

| Profile | File | Domain |
|---------|------|--------|
| `vitravel` | `project/seed_vitravel.php` | `vitravel.dev` |
| `hicatba` | `project/seed_hicatba.php` | `hicatba.dev` / `hicatba.com` |
| `phuquy` | `project/seed_phuquy.php` | `phuquy.dev` / `phuquy.net` |
| `phuquoc` | `project/seed_phuquoc.php` | `phuquocangiang.dev` / `phuquocangiang.com` |
| `culaocham` | `project/seed_culaocham.php` | `culaocham.dev` / `culaocham.net` |
| `hidalat` | `project/seed_hidalat.php` | `hidalat.dev` / `hidalat.com` / `hidalat.vn` |
| `hicantho` | `project/seed_hicantho.php` | `hicantho.dev` / `hicantho.com` |
| `hihagiang` | `project/seed_hihagiang.php` | `hihagiang.dev` / `hihagiang.com` / `hihagiang.vn` |
| `hihalong` | `project/seed_hihalong.php` | `hihalong.dev` / `hihalong.vn` |
| `himuine` | `project/seed_himuine.php` | `himuine.dev` / `himuine.com` |
| `hitamdao` | `project/seed_hitamdao.php` | `hitamdao.dev` / `hitamdao.com` |
| `hisapa` | `project/seed_hisapa.php` | `hisapa.dev` / `hisapa.vn` |

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
  seed_phuquoc.php          ← đảo Phú Quốc (An Giang): phuquocangiang.dev / phuquocangiang.com — Đảo Phú Quốc
  seed_culaocham.php        ← Cù Lao Chàm (Quảng Nam): culaocham.dev / culaocham.net
  seed_hidalat.php         ← Đà Lạt (Lâm Đồng): hidalat.dev / hidalat.com — Hi Đà Lạt
  seed_hicantho.php        ← Cần Thơ (ĐBSCL): hicantho.dev / hicantho.com — Hi Cần Thơ
  seed_hihagiang.php       ← Hà Giang (cao nguyên đá): hihagiang.dev / hihagiang.vn — Hi Hà Giang
  seed_hihalong.php        ← Hạ Long (vịnh UNESCO): hihalong.dev / hihalong.vn — Hi Hạ Long
  seed_himuine.php         ← Mũi Né (Bình Thuận): himuine.dev / himuine.com — Hi Mũi Né
  seed_hitamdao.php        ← Tam Đảo (Vĩnh Phúc): hitamdao.dev / hitamdao.com — Hi Tam Đảo
  seed_hisapa.php          ← Sa Pa (Lào Cai): hisapa.dev / hisapa.vn — Hi Sa Pa
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

### 3. Điểm đến / khu vực + phân luồng Danh mục / Chủ đề tour

- `countries` — `{ slug, name, size, tagline }[]` → SEO type `country` (CMS entity, admin: **Điểm đến / khu vực**).
  Runtime listing: `tagline` → `subtitle`; `long_form`/`intro_text` (nếu có) → `seoBody`
- Alias hub 1 địa danh: **`zones` / `zoneSlug` / `zone_translations`** → chuẩn hoá thành `countries` / `countrySlug` / `country_translations` lúc load (`ProjectSeed`)
- `country_translations` / `zone_translations` — i18n theo slug
- `meta.country_codes` — optional; nếu thiếu, tự sinh từ slug

#### SEO — trang địa điểm **không có trang cha**

> Bắt buộc mọi seed / seeder / rebuild: `parent_id = null` cho SEO type `country`.
> URL public = `/{slug}` (vd `/vinh-lan-ha`), **không** `/tours/{slug}`.
> Danh mục tour (`tour_category`) và gói tour vẫn có thể gắn **dưới** điểm đến → `/{zone}/{category}` / `/{zone}/{tour}`.
> `SeoService::attachCountriesToToursHub()` là no-op (không ép về tours_hub).

#### Quy tắc phân khu vực (zones) — mẫu cho dự án mới

Phân khu theo **insight tìm kiếm / đặt chỗ** của khách, không theo ranh giới hành chính cứng:

| Loại zone | Ví dụ slug | Khi nào tạo | Gắn sản phẩm |
|-----------|------------|-------------|--------------|
| **Hub zone** (1 cái, đứng đầu) | `trung-tam-*`, `thi-tran-*`, `duong-dong` | Thị trấn / cửa ngõ khách nghĩ tới khi gõ tên địa danh | Chủ đề tour (`theme`) gắn đây; stay trung tâm; không gắn train/ferry/flight |
| **GEO zone** | `vinh-lan-ha`, `bai-sao`, `dong-van` | Khu khách **search / chọn KS / book vé** riêng (bãi, vịnh, cao nguyên…) | `tours.zoneSlug`, `services.zone_slug` (stay/experience/other), danh mục `type=region` |
| **Combo** `ket-hop-*` | `ket-hop-ha-long` | Tuyến nối địa danh khác — **không** phải chỗ nghỉ vật lý | Chỉ tour combo + danh mục region; **không** gắn stay “ảo” |

**Không làm:**
- Clone POI nhỏ thành zone nếu khách không search / không ở khu đó (gom vào hub hoặc GEO cha).
- Gắn `zone_slug` cho `ferry` / `flight` / `train` — cụm di chuyển **không** dùng điểm đến (`country_id` luôn null).
- Trùng tên zone với chủ đề tour (theme không được tên “Vịnh Lan Hạ” khi đã có zone `vinh-lan-ha`).

**Checklist seed 1 hub mới:** khai báo `zones` + `zone_translations` → map mọi `tours[].zoneSlug` / `stay|experience|other.zone_slug` → tạo `tour_categories` region (1/zone + ket-hop) → theme gắn hub zone → ghi chú insight ngay đầu file seed.

#### Admin UI (phân luồng menu)

| CMS | Menu admin | Ghi chú |
|-----|------------|---------|
| `countries` (điểm đến / khu vực) | **Nội dung / Thông tin → Điểm đến** | Không nằm trong nhóm Sản phẩm tour; **không** gắn train/ferry/flight; SEO không trang cha |
| `tour_categories` (region + theme) | **Sản phẩm → Danh mục Tour** | Drawer menu Tour public lấy các bản ghi `type=theme` |

#### Quy tắc phân tách danh mục / chủ đề (bắt buộc — mọi hub)

> Package ↔ category/theme đã là **nhiều–nhiều** (`package_tour_category` + `packageSlugs[]`).
> Một tour có thể thuộc nhiều danh mục GEO **và** nhiều chủ đề cùng lúc.

| Layer | `tour_categories.type` | Gắn `zoneSlug` | Nội dung |
|-------|------------------------|----------------|----------|
| **Danh mục** | `region` | Vùng GEO hoặc `ket-hop-*` | Nhóm theo **khu vực / combo** (Đồng Văn, Mèo Vạc, Combo Sapa…). **Không** chia theo số ngày. |
| **Chủ đề** | `theme` | Hub zone (thị trấn / cửa ngõ) | (A) **Thời lượng chương trình**: trong ngày · 2N1D · 3N2D · 4N3D · từ 5 ngày. (B) **Tính chất / phân khúc**: gia đình, trăng mật, teambuilding, cuối tuần, hoạt động signature. |
| ~~Thời lượng dưới zone~~ | ~~`duration`~~ | — | **Cấm trên hub** — trùng chủ đề thời lượng. Chỉ còn hợp lệ trên `vitravel` (đa quốc gia). |

**Không chồng lấn:**
- Chủ đề **không** clone tên zone GEO (vd: không theme “Vịnh Lan Hạ” khi đã có zone `vinh-lan-ha`).
- Danh mục **không** đặt tên “Tour 1 ngày / 2–3 ngày…”.
- `travel_styles` = mã filter khớp chủ đề (duration + insight) — **không** tạo trang SEO riêng; trang SEO = `tour_categories`.

**Seed lại riêng cụm taxonomy + chi tiết tour** (xóa sạch chủ đề/danh mục/SEO/packages type=tour rồi tạo lại; không đụng bài viết/home/services/cruises):

```bash
php artisan project:seed hidalat --only=tours
```

**Seed đè toàn bộ 1 project** (xóa sạch data project rồi seed lại):

```bash
php artisan project:seed hihagiang --fresh-project --domain=hihagiang.dev --name="Hi Hà Giang"
```

`tour_categories` shape:

```
{
  countrySlug|zoneSlug, slug, type, sort, minDays?, maxDays?, packageSlugs?[],
  name: { vi, en },
  subtitle: { vi, en },   // short copy dưới H1 (DB: description)
  seo_body: { vi, en },   // prose SEO dưới lưới (DB: seo_intro)
  faqs?: [{ q, a }]
}
```

  Public URL: `/tours/{country}/{slug}` (SEO type `tour_category` → `TourController::category`). Legacy seed keys `description` / `seoIntro` vẫn được seeder đọc nếu còn.
### 4. Sản phẩm

- `tours` → `package_tour`
- `cruises` + `cruise_types` → `package_cruise`. Listing chrome trên loại du thuyền: `intro` (subtitle dưới H1), `seo_body` (HTML cuối listing).

### 4b. Catalogue dịch vụ (5 cụm)

Keys nằm trong cùng file seed dự án (`service_clusters`, `service_categories`, `services`, `service_listing_faqs`) — không tách file phụ.

| Key | Shape (tóm tắt) |
|-----|-----------------|
| `service_clusters` | `[{ code, nav_label, label, icon, hub_key, sort }]` — 5 cụm: `train`, `flight`, `stay`, `experience`, `other` |
| `service_categories` | `[{ cluster, slug, name, sort, intro?, seo_body? }]` — danh mục con dưới hub. `intro` = subtitle dưới H1; `seo_body` = HTML cuối listing |
| `services` | `[{ code, cluster, category_slug, country_slug?, title, slug, price_from, currency, rating, star_rating?, highlights[], inclusions[], exclusions[], notes[], content?, attrs{}, options[], faqs[], en? }]` — **`cluster=stay`**: `attrs` (property_type, address, amenities, **amenity_groups**, highlight_badges, **nearby_groups**, **review_scores** tag list, policies…) + `options[]` overlay hạng phòng (`attrs.unit_type`, `beds`, `amenity_groups`, `photos`, bathroom_count…). Chi tiết: [16-accommodation-stays.md](../docs/16-accommodation-stays.md) |
| `service_listing_faqs` | `[{ q, a }]` — FAQ chung hub/listing dịch vụ |
| `price_guest_types` | optional `[{ code, sort, age_min?, age_max?, name: { vi, en }, description?: { vi, en } }]` — chỉ **thêm mã chưa có**; mặc định adult/child/senior từ `config/pricing.php`. |
| `price_table_defaults` | template bảng giá mẫu khi clone dự án / seed chương trình chưa có rate. Shape: `{ unit, notes?, guest_multipliers: { adult: 1, child: 0.7, senior: 0.85 }, cluster_units?: { stay: per_room }, periods: [{ kind, label, starts_on?, ends_on?, is_promo, priority, amount_multiplier? }] }`. `{year}` được thay năm hiện tại. Fallback: `config/pricing.php` → `sample`. |
| `tours[].price_table` / `cruises[].price_table` / `services[].price_table` | optional override đầy đủ (variants + periods + rates). Ví dụ: tour `VN10D-01` trong `seed_vitravel.php`. Không khai báo → `PriceTableSeeder` dựng từ `price_from` + cabin / service option. **An toàn:** bỏ qua chương trình đã có `price_rates`. |

Demo seed: **22 categories**, **32 services** (4 train, 4 flight, 8 stay, 9 experience, 7 other). Config runtime: `config/services_catalog.php`.

### 5. Blog

- `blog_categories`, `popular_keywords`, `articles` (`tags[]` ∈ keys của `content_tag_map`)

### 6–8. Team, about, home, footer

- `testimonials`, `team`, `videos`, `gallery_albums`, `usps`, `offices`
- `value_definitions`, `reason_definitions`, `reference_persons`, `about_page`
- `home_slides`, `hero_pills`, `home_sections`, `footer_*`, `listing_faqs`
- **`customize_form`** — form Tour riêng: `destinations_label`, `accommodation_label`, `budget_note`, `accommodation[]` (i18n `vi`/`en`); điểm đến mặc định từ countries/zones `show_in_customize_form` (có thể ghi đè bằng `destinations[]`)
- **`nav`** — nhãn header + hub cruise (legacy seed): `about_group`, `tours.{label}`, `cruise.{…}`. Runtime ưu tiên bảng `navigation_items` (admin **Nội dung → Menu chính**); chưa lưu DB thì dùng `nav_menu` (tự sinh từ `nav` + `service_clusters` qua `project/includes/nav_menu.php`).
- **`nav_menu`** — cây menu public đầy đủ: `main[]`, `more[]`, `cta[]` — mỗi item: `kind`, `key`, `label` (vi/en), `lead_label`, `meta`, `reference` (cluster/route). Cụm **Lưu trú** = `service_cluster` + `reference: stay`.
- **`listing_hubs`** — đoạn SEO cuối trang hub (`tours_hub`, `cruises_hub`, `ferries_hub`/`trains_hub`, `flights_hub`, `stays_hub`, `experiences_hub`, `extras_hub`): `{ hubKey: { vi|en: { seo_body } } }`. Runtime: cột `static_page_translations.seo_body` (admin **Cài đặt → Hub**). `body`/`subtitle` = copy ngắn dưới H1; `seo_body` = prose cuối listing. Rỗng = ẩn khối. Nếu DB trống mà seed có `seo_body`, lần mở hub sẽ soft-fill (không cần `project:seed` lại). Hỗ trợ `:brand`.

### Listing chrome (public + AI)

Mọi trang listing (tours hub / country / chủ đề tour / cruise type / service hub|category) dùng chung `App\Support\ListingChrome` + `partials/listing-catalog.blade.php`.

| Canonical | Ý nghĩa | Seed / DB (tuỳ entity) |
|-----------|---------|------------------------|
| `title` | H1 | `name` / hub title |
| `subtitle` | Copy ngắn dưới H1 | `tour_categories.subtitle`, country `tagline`, hub `body`, cruise `intro`, service `intro` |
| `seoBody` | Prose HTML dưới lưới | `tour_categories.seo_body`, country `long_form`, hub `seo_body`, cruise `seo_body`, service `seo_body` |
| `banner` | Hero | cover / listing banner |

Admin API chấp nhận cả tên cũ lẫn canonical (`ListingFields`).

### Map seeder

| Key | Seeder | SEO |
|-----|--------|-----|
| countries / tours | ContentSeeder | country / package_tour |
| company | **HomeFeaturedSeeder** (`seedCompanyIdentity`) | — (company_profiles) |
| cruise_types / cruises | CruiseType + Content | cruise_type / package_cruise |
| service_categories / services | **ServiceCatalogSeeder** | `service_category` / `service` (+ 5 hub types) |
| price_guest_types / price_table_defaults | **PriceGuestTypeSeeder** + **PriceTableSeeder** | — (chỉ thêm mã / bảng giá còn thiếu) |
| tour_categories | TourCategorySeeder | tour_category |
| (cuối) | **SeoHierarchySeeder** | rebuild cây |

### Prompt gợi ý

```
Profile: vitravel (file project/seed_vitravel.php).
Dựa trên project/README.md, [giữ|đổi|xóa] key: …
Xuất đủ shape vào đúng file seed của profile, không hardcode seeder.
Nhắc: php artisan migrate --seed  (seed all) hoặc project:seed {profile}
```
