# 09 — SEO Technical (JSON-LD Schema)

Tài liệu **JSON-LD / schema.org** cho ViTravel. Triển khai: `config/seo.php` → `App\Services\SchemaService` → helper `schema_ld()` trong Blade.

---

## 1. Inventory

| Type | Nơi emit | Trang | Brand / project |
|---|---|---|---|
| **Organization** + TravelAgency | Layout | Mọi trang public | `company_profiles` via `site_brand()` |
| **WebSite** + `SearchAction` | Layout | Mọi trang public | Cùng brand; `description` từ tagline dự án |
| **BreadcrumbList** | `x-layout.breadcrumb` | Trang có breadcrumb | — |
| **FAQPage** | `x-shared.faq` | Listing / detail / guide… | — |
| **TouristTrip** (+ rating, provider→Org) | `x-tour.detail` | Tour / Cruise detail | `provider` → Org `@id` |
| **Article** (publisher→Org `@id`) | `guide/show` | Bài guide | author fallback = brand |
| **ItemList** (SSR) | Listing hub/country/cruise/service | Schema Google | Tên list có brand |

Trang chủ: title/description = `seo_home_title()` / `seo_default_description()` (brand + tagline), không hardcode ViTravel.

---

## 1b. URL hierarchy (universal parent → slug_full)

**Quy tắc:** mọi entity public dùng `SeoService::buildSlugFull` — có cha thì `{parent.slug_full}/{slug}`, không cha (hub) thì `/{slug}`. Không hardcode prefix theo type.

**Public routing:** catch-all `RoutingController` tra `seo_entry_translations.slug_full` theo locale (middleware `DetectLocale` → `DetectCurrency` → `CheckRedirect`). Default locale không prefix; locale khác: `/{locale}/{slug_full…}`. Named helpers `locale_route('tours.*'|'cruises.*'|'guide.*')` resolve qua `SeoService::namedSeoPath` (slug_full hiện tại), không phụ thuộc URI Laravel cứng.

Hubs (`config/seo.php` → `hubs`) — slug mặc định, **có thể đổi trong admin** (vd. hub cruise → `/du-thuyen`):

| Default slug_full | Route name | Ghi chú |
|---|---|---|
| `/tours` | `tours.hub` | Hub tour + lọc quốc gia |
| `/cruises` | `cruises.hub` | Hub du thuyền + loại |
| `/cam-nang-du-lich` | `guide.index` | Hub cẩm nang |

| Pattern (sau hub) | Route name | SEO `type` |
|---|---|---|
| `{hub}/{country}` | `tours.index` | `country` |
| `{hub}/{country}/{slug}` | `tours.show` | `package_tour` |
| `{hub}/{type}` | `cruises.index` | `cruise_type` |
| `{hub}/{type}/{slug}` | `cruises.show` | `package_cruise` |
| `{hub}/{cat}` | `guide.country` | `blog_category` |
| `{hub}/{cat}/{slug}` | `guide.show` | `article` |
| `/api/listings/*` | `api.listings.*` | JSON `{count,html}` (không qua SEO catch-all) |

**Đổi slug cha:** `syncSeo` → `cascadeSlugFullChildren` cập nhật mọi con cùng locale + ghi `redirect_info` (301) qua `createRedirect301` (prefix `/{locale}` nếu không phải default). Uniqueness `(language_id, slug_full)` enforce trong `SeoService::assertSlugFullUnique` trước khi lưu.

**Bộ lọc (UX):** checkbox mặc định = tất cả option (hub) hoặc chỉ country hiện tại + tất cả duration/style (trang quốc gia). Đổi checkbox → `fetch` cập nhật grid, không reload. **ItemList / FAQ schema vẫn SSR.**

---

## 2. Organization (bắt buộc sitewide)

Layout gọi **2 script riêng** (root `@type`, không chỉ nằm trong `@graph`):

```blade
{!! schema_ld(schema()->organization()) !!}
{!! schema_ld(schema()->website()) !!}
```

Organization:

```json
{
  "@context": "https://schema.org",
  "@type": ["Organization", "TravelAgency"],
  "@id": "https://example.com/#organization",
  "name": "ViTravel",
  "url": "https://example.com/",
  "telephone": "…",
  "email": "…",
  "address": { "@type": "PostalAddress", "…" },
  "contactPoint": { "@type": "ContactPoint", "…" }
}
```

**Lưu ý kiểm tra:** Google Rich Results Test thường **không liệt kê Organization** như một “rich result” (FAQ/Product…). Hãy dùng:

1. View Source → Ctrl+F `Organization`
2. [Schema Markup Validator](https://validator.schema.org/)
3. DevTools → Elements → `script[type="application/ld+json"]`

Nguồn dữ liệu: **`company_profiles`** (per project) qua `CompanyProfile::contact()` / helpers `site_brand()`, `seo_home_title()`, `seo_default_description()`, `apply_site_brand()`.  
`config/seo.php` → `site` (`SEO_SITE_*`) chỉ là **fallback** khi DB trống — `SchemaService::siteConfig()` **ưu tiên CompanyProfile** (không để `SEO_SITE_NAME=ViTravel` ghi đè WebSite/OG trên domain dự án khác).

Hub SEO defaults trong `config/seo.hubs.*.default_seo_*` dùng placeholder `:brand` (runtime → tên dự án). Copy cũ còn «ViTravel» cũng được `apply_site_brand()` thay khi đọc hub / chrome / home sections.

---

## 3. API Blade

```blade
{!! schema_ld(schema()->organization()) !!}
{!! schema_ld(schema()->breadcrumbList($items)) !!}
{!! schema_ld(schema()->faqPage($faqs)) !!}
{!! schema_ld(schema()->touristTrip($item, $url, $isCruise)) !!}
{!! schema_ld(schema()->article($article, $url)) !!}
```

- Dùng **`schema_ld()`** (PHP helper) — không phụ thuộc Blade component prop (tránh xung đột `data`).
- Image / URL luôn absolute `https://…`
- `AggregateRating` chỉ khi có `reviewCount > 0`

---

## 4. Checklist Rich Results

1. View Source trang chủ → tìm `"Organization"` trong JSON-LD
2. Google Rich Results Test / Schema Markup Validator
3. Article: publisher trỏ `@id` Organization (không nhân bản tên rời)
4. Tour hub (slug_full hiện tại, mặc định `/tours`): BreadcrumbList + FAQ; listing country/detail tương tự
5. Contact: Local TravelAgency per office + Organization sitewide

---

## 5. Liên kết

- Company (hotline / social / Organization): `config/company.php`
- Config SEO meta: `config/seo.php`
- Service: `app/Services/SchemaService.php`
- Helpers: `schema()`, `schema_ld()`, `company()` trong `app/helpers.php`
- Layout: `resources/views/layouts/app.blade.php`
