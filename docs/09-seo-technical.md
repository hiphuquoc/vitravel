# 09 — SEO Technical (JSON-LD Schema)

Tài liệu **JSON-LD / schema.org** cho ViTravel. Triển khai: `config/seo.php` → `App\Services\SchemaService` → helper `schema_ld()` trong Blade.

---

## 1. Inventory

| Type | Nơi emit | Trang |
|---|---|---|
| **Organization** + TravelAgency | Layout (script riêng) | Mọi trang public |
| **WebSite** + `SearchAction` | Layout (script riêng) | Mọi trang public |
| **BreadcrumbList** | `x-layout.breadcrumb` | Trang có breadcrumb |
| **FAQPage** | `x-shared.faq` | Listing / detail / guide… |
| **TouristTrip** (+ rating, provider→Org) | `x-tour.detail` | Tour / Cruise detail |
| **Article** (publisher→Org `@id`) | `guide/show` | Bài guide |
| **ItemList** (SSR) | Listing hub/country/cruise | Schema Google — grid vẫn fetch client |

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

Nguồn dữ liệu: **`config/company.php`** (mẫu + env `COMPANY_*`) qua `CompanyProfile::contact()` — admin Company Profile ghi đè email/phone/whatsapp/slogan/license khi đã nhập. Meta SEO bổ sung: `config/seo.php` → `site` (title_suffix, description, og_image…).

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
