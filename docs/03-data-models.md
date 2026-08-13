# Data Models / Schema

Thiết kế dạng CMS-agnostic (áp dụng được cho Sanity/Strapi/Payload/Contentful, hoặc bảng SQL). Field kiểu `richText` = nội dung rich text/MDX; `image`/`image[]` = asset reference.

> **Cập nhật so với bản trước:** đã bổ sung field phát hiện từ ảnh chụp thực tế (xem `06-tham-chieu-hinh-anh.md`) — đặc biệt là facet `travelStyle` cho Tour, toàn bộ model cho hệ Blog (BlogCategory, ContentTypeTag, PopularKeywordTag, Comment), model cho About Us (CompanyValue, ReasonToChooseUs, ReferencePersonAbroad), model `Office`, và model `CustomTourRequest` chi tiết đúng theo form thật.

## 1. `Country` (Quốc gia điểm đến)
```
- id
- name              string      // "Vietnam"
- slug              string      // "vietnam"
- bannerImage        image
- introText          richText    // đoạn intro ngắn hiển thị đầu trang listing
- longFormContent    richText    // block "Things you should know" nhiều H3 con
- homeGridSize        enum        // "large" | "normal"  — dùng cho bento grid "Le destinazioni più amate" ở Home
- order              number      // thứ tự hiển thị ở menu/homepage
```
Ghi chú: danh sách quốc gia thực tế gồm **Vietnam, Thailand, Cambodia, Laos, Myanmar, Bali (Indonesia)** — Bali xuất hiện trong Customize Tour form và bento grid Home dù không có trong mega-menu Tours ở bản .com khảo sát trước, cần bổ sung.

## 2. `Destination` (Điểm đến/thành phố con — dùng cho filter & travel guide)
```
- id
- name               string      // "Sapa" / "Phnom Penh"
- slug               string
- country            ref(Country)
- image
- introText          richText
```

## 3. `TourCategory` (Danh mục con: theo duration/theme, VD "10 days in Vietnam")
```
- id
- name               string
- slug               string
- country            ref(Country)
- type               enum        // "duration" | "region" | "theme" | "day-trip" | "package"
- subtitle           richText    // copy ngắn dưới H1 (DB cột description; seed key subtitle)
- seoBody            richText    // prose SEO dưới lưới listing (DB cột seo_intro; seed key seo_body)
- faqs               FAQItem[]   // FAQ riêng theo category
- coverImage         image       // banner listing
```

**Public:** `/tours/{country}/{slug}` — SEO type `tour_category` → `TourController::category` + `ListingChrome` (cùng layout với hub/country/cruise/service listing).

**Seed:** `project/seed_*.php` → `tour_categories[]` (`subtitle` / `seo_body`); seeder vẫn nhận legacy `description` / `seoIntro`.

**Admin/AI aliases:** `subtitle` ↔ `description`, `seo_body` ↔ `seo_intro` (`ListingFields`).

## 4. `Tour`
```
- id
- title              string
- slug               string
- country            ref(Country)
- categories         ref(TourCategory)[]
- travelStyles        enum[]       // "long-duration" | "heritage-rich" | "nature-homestay" | "culture-history"
                                    // | "balanced" | "beach" | "honeymoon" | "family" | "trekking"
                                    // | "multi-country-combo" | "small-group"
                                    // (facet "Stile di viaggio" quan sát từ danh-muc-tour.png)
- tourCode            string       // "VN14D-2"
- durationDays         number
- durationNights        number
- startLocation        string
- endLocation          string
- placesToVisit        string[]
- coverImage           image
- gallery              image[]
- mapImage             image
- rating               number       // 0-5
- reviewCount           number
- isHotDeal             boolean
- discountBadge         string       // "Offerta speciale" / "Flash Sale 10% OFF"
- priceFrom             number       // optional
- currency              string
- featuredQuote          {text: string, author: string}   // trích review ngắn hiển thị ngay trong card listing
- highlightsIntro         richText
- highlightBullets        string[]
- itineraryDays           ItineraryDay[]
- inclusions              string[]
- exclusions              string[]
- notes                   string[]
- faqs                    FAQItem[]      // FAQ riêng của tour, hiển thị cuối Tour Detail
- reviews                 Review[]
- relatedTourIds           ref(Tour)[]
- featured                 boolean       // hiển thị ở "I tour più richiesti" Home (giới hạn hiển thị 3)
- seo                      SEOFields
```

### `ItineraryDay` (object con trong Tour/Cruise)
```
- dayNumber           number
- title               string
- mealsIncluded        string       // "B; L; D"
- transportIcons       enum[]       // car | train | plane | boat | bus | bike | walking | trekking | kayak | sampan
- distanceInfo          string
- image                 image
- content               richText
- overnightAt            string
- internalLinks          {label: string, url: string}[]
```

## 5. `Cruise`
Kế thừa field của `Tour` (bao gồm cả `travelStyles`), thêm:
```
- cruiseType           enum        // "halong-bay" | "mekong" | "myanmar-river"
- departurePort         string
- boatClass             enum        // "classic" | "deluxe" | "luxury" | "private"
- cabinTypes             CabinType[]
- nightsOnBoard          number
```
```
CabinType:
- name / capacity / priceFrom / amenities[]
```

## 5a. `ServiceCategory` / `Service` / `ServiceOption` (catalogue dịch vụ — 5 cụm)

Tách khỏi bảng `packages` (tour/cruise). **5 cụm** (`cluster` code): `train` | `flight` | `stay` | `experience` | `other` — map hub SEO qua `config/services_catalog.php`.

### `ServiceCategory`
```
- id
- cluster            enum        // train | flight | stay | experience | other
- slug               string
- name               string
- intro              richText    // SEO intro trang danh mục
- sort / is_active
- seo                SEOFields   // type service_category, parent = hub cụm
```

### `Service`
```
- id
- cluster            enum
- category           ref(ServiceCategory)
- country            ref(Country)?   // optional — lưu trú / tour kết hợp
- code               string          // mã nội bộ
- title / slug       string          // qua service_translations
- location_label     string?
- summary            richText
- highlights         string[]
- inclusions / exclusions / notes   string[]
- price_from / currency             display-only (lead-gen)
- rating / review_count / star_rating?
- is_featured / is_hot_deal / discount_badge
- attrs              json            // cluster-specific: from/to, train_number, check_in, venue…
- options            ref(ServiceOption)[]
- faqs               FAQItem[]
- relatedServiceIds  ref(Service)[]  // optional
- seo                SEOFields       // type service
```

### `ServiceOption` (biến thể giá — ghế tàu, loại phòng, combo vé…)
```
- id
- service            ref(Service)
- code / name
- price_from / currency
- sort
```

**Public:** hub → category listing → detail; named routes `services.hub`, `services.index`, `services.show`. Dữ liệu qua `ServiceCatalogSeeder` + keys dịch vụ trong `project/seed_{name}.php`.

## 6. `Review` (đánh giá tổng hợp — dùng cho Tour Detail, Listing quote, và trang Reviews tổng hợp)
```
- id
- targetType            enum        // "tour" | "cruise" | "company"
- targetId              ref
- authorName             string
- authorCountry           string      // hiển thị dạng "Italia IT" trong testimonial card — cần cả tên nước + mã quốc gia cho cờ
- avatarImage             image        // ưu tiên ảnh thật; fallback generate avatar nếu không có
- rating                  number       // 1-5
- date                     date
- questionTitle            string      // format review dạng Q&A ở Tour Detail
- content                  richText
- photos                   image[]      // dải ảnh thumbnail đính kèm review (thấy ở testimonial card Home, dạng "+N")
- countryTag                ref(Country)
```

## 7. Hệ Blog / Travel Guide (mở rộng đáng kể so với bản trước)

### 7.1 `BlogCategory` (danh mục blog — theo ĐIỂM ĐẾN, hiển thị ở sidebar "Categorie del blog")
```
- id
- name                 string       // "Phnom Penh" / "Koh Rong" / "Cose da vedere in Vietnam" (cấp quốc gia)
- slug                  string
- level                  enum         // "country" | "destination"
- country                ref(Country)
- destination             ref(Destination)   // null nếu level=country
- order                   number
```

### 7.2 `ContentTypeTag` (tag lọc theo LOẠI nội dung — nhóm "Filtra articoli")
```
- id
- label                string       // "Dove mangiare e bere?" | "Dove dormire?" | "Cosa fare e vedere?"
                                     // | "Consigli di viaggio?" | "Com'è stato il viaggio?" | "Quale tour scegliere?"
- slug                  string
```

### 7.3 `PopularKeywordTag` (tag cloud SEO — "Mots-clés populaires")
```
- id
- label                string       // "Cose da vedere in Cambogia", "Miglior Tour Operator Cambogia"...
- targetUrl              string       // link tới trang liên quan (category, tour listing...)
```

### 7.4 `Article` (bài viết Travel Guide)
```
- id
- title                 string
- slug                  string
- country               ref(Country)
- destination            ref(Destination)
- blogCategory            ref(BlogCategory)
- contentTypeTags         ref(ContentTypeTag)[]
- keywordTags             ref(PopularKeywordTag)[]
- coverImage             image
- galleryImages           image[]       // ảnh collage đầu bài (hero + 4 ảnh nhỏ, xem noi-dung-blog.png)
- excerpt                 string
- content                 richText/MDX  // heading H2/H3 tự động sinh mục lục (TOC)
- inlineRelatedLinks      {label: string, url: string}[]   // box "Vedi di più:" chèn giữa bài
- relatedTourIds           ref(Tour)[]   // internal link cuối bài — cầu nối content→product, RẤT quan trọng
- relatedArticleIds        ref(Article)[] // "Blog uguale"
- faqs                     FAQItem[]     // FAQ riêng cuối bài viết
- viewCount                 number        // hiển thị "N vues"
- authorName                 string
- rating                     number        // rating cuối bài (khác với comment)
- ratingCount                 number
- publishedAt                 date
- updatedAt                   date        // hiển thị "Aggiornato il..."
- seo                         SEOFields
```

### 7.5 `Comment` (bình luận bài viết — trước đây chưa có model này)
```
- id
- article              ref(Article)
- fullName              string
- email                  string
- phone                  string        // optional
- content                richText
- createdAt               date
- status                  enum          // "pending" | "approved" | "rejected"  (kiểm duyệt trước khi hiển thị)
```

### 7.6 FAQ riêng cho trang danh mục Blog
Trang danh mục blog (`danh-muc-blog.png`) có FAQ riêng ở cấp category — thêm field vào `BlogCategory`:
```
BlogCategory.faqs        FAQItem[]
BlogCategory.seoIntro     richText   // đoạn văn SEO cuối trang danh mục
```

## 8. `TeamMember`
```
team_members:
- id
- department            string?
- avatar_media_id       FK media?
- phone / email / area  string?
- years_experience      uint?
- languages             json?          // list ngôn ngữ
- stat_clients / stat_tours / stat_awards  uint (default 0)
- is_verified           bool (default true)
- sort / is_active / show_on_home
- soft deletes

team_member_translations:
- name, role, short_bio, bio_html (longText — intro đầy đủ trên trang CV)

Child tables (ordering):
- team_member_achievements (content)
- team_member_skills (skill, percent 0–100)
- team_member_experiences (title, company?) + team_member_experience_items (content)
- team_member_degrees (title, school?) + team_member_degree_items (content)
- team_member_activity_images (media_id?, ordering)

SEO: type `team_member`, parent `team_hub` → slug_full `/doi-ngu/{slug}`
Public: list `/doi-ngu`, profile via catch-all slug_full.
```

## 9. `ExperienceAlbum` / `ExperienceVideo`
Giữ nguyên như bản trước — xem field ở lịch sử thiết kế; không có thay đổi từ ảnh mới.
```
ExperienceAlbum: id, title, country, coverImage, photos[], photoCount, customerName, tripDate
ExperienceVideo: id, title, youtubeId/videoUrl, thumbnail, country, publishedAt
```

## 10. `StaticPage` (dùng cho các trang tĩnh còn lại ngoài About Us, VD Sale Policy nếu tách riêng)
```
- id / title / slug / bannerImage / body (richText) / seo (SEOFields)
```

## 11. Model riêng cho trang About Us (Chi Siamo) — mới bổ sung đầy đủ

### 11.1 `CompanyIntro` ("Viaggio autentico" — dùng chung Home + About Us)
```
- greetingTitle         string     // "Viaggio autentico"
- introText              richText
- licenseNumber           string     // "01-2234-2023-TCDL-GP-LHQT"
- image
```
→ **Implement:** `HomeSection` key `company_intro` (không phải CompanyProfile).

### 11.2 `CompanyValue` (cho sơ đồ "Impegno nei valori fondamentali")
```
- id
- name                 string      // "Dedizione" | "Empatia" | "Sincerità" | "Responsabilità"
- description           string      // mô tả 1 dòng
- order                  number      // vị trí quanh vòng tròn (trên/dưới/trái/phải)
```
→ **Admin:** `/gia-tri` (`admin.values.*`), i18n name/description.

### 11.3 `MissionVision` + About chrome (trong `CompanyProfile`)
```
company_profiles:
- mission_image_id / vision_image_id / policy_image_id / reasons_image_id / about_banner_media_id
- contact_* / license_number / slogan (locale-independent)

company_profile_translations:
- mission_title / mission_text / vision_title / vision_text
- sales_policy_title / sales_policy_content / sales_policy_cta_label / sales_policy_cta_url
- about_page_title / about_page_subtitle / about_seo_title / about_seo_description
- values_section_title / values_hub_label
- reasons_section_title / reasons_cta_label / reasons_cta_url
- reference_section_title / reference_section_subtitle
```
→ **Admin:** `/cong-ty` — form đa ngôn ngữ + upload ảnh.

### 11.4 `SalesPolicy`
Gộp vào `CompanyProfile` (không tách model riêng).

### 11.5 `ReasonToChooseUs`
```
- id / title / description / order / is_active
- sectionImage    // legacy per-row; UI dùng company_profiles.reasons_image_id
```
→ **Admin:** `/ly-do-chon` (`admin.reasons.*`).

### 11.6 `ReferencePersonAbroad` (`reference_persons`)
```
- id
- name             string      // "Mr. Claude MILLET"
- photo            (photo_media_id → media)
- email / phone / skype
- country            ref(Country)
- order / is_active
```
→ **Admin:** `/dai-dien` (`admin.referencePersons.*`). Helper: `ReferencePerson::photoUrl()`.
## 12. `Office` (văn phòng — dùng cho Footer + trang Contact)
```
- id
- cityLabel          string      // "Hanoi, Vietnam" / "Città di Ho Chi Minh, Vietnam" / "Siem Reap, Cambogia"
- addressLine         string
- phone               string
- whatsapp             string
- mapEmbedUrl          string      // optional, đề xuất bổ sung dù ảnh gốc không thấy hiển thị
- order
```

## 13. `FAQItem` (object con)
```
- question / answer (richText)
```

## 14. `SEOFields` (object con)
```
- metaTitle / metaDescription / ogImage / canonicalUrl (optional)
```

## 15. Lead / Form Models (đã tách rõ theo từng form thật quan sát được — khác bản trước gộp chung 1 model)

### 15.1 `QuickInquiryLead` (form "Domanda Rapida per un Tour" — lặp lại cuối hầu hết các trang)
```
- id
- name* / email* / phone*
- address                 string       // optional, có trong form nhưng không bắt buộc
- message                  richText
- sourcePageUrl             string       // trang nào submit
- relatedTourId              ref(Tour)   // optional nếu form nhúng trong Tour Detail
- createdAt
```

### 15.2 `CustomTourRequest` (form "Personalizza il tour" — đầy đủ đúng theo ảnh thật)
```
- id
- adultsCount*             number
- childrenCount             number     // 4-10 anni
- infantsCount               number     // 0-3 anni
- durationText*              string     // "Esempio: 10 giorni" — text tự do, KHÔNG phải số cố định
- arrivalDate*                date
- countriesToVisit*           enum[]     // VIETNAM | THAILANDIA | CAMBOGIA | LAOS | BALI
- accommodationPreference*    enum[]     // "3-star" | "4-star" | "5-star" | "let-us-recommend"
- budgetAmount*                number
- budgetCurrency               string     // mặc định "€", nên hỗ trợ đa tiền tệ khi mở rộng
- budgetUnit*                  enum       // "per-person" | "per-group"
- gender*                       enum       // "mr" | "mrs"
- firstName* / lastName*
- email* / phone*
- nationality*                  string     // dropdown chọn quốc gia
- city*
- additionalNotes                richText
- status                         enum       // "new" | "contacted" | "quoted" | "closed"
- createdAt
```

### 15.3 `ContactMessage` (form trang Contact — tối giản, KHÔNG dùng chung model với Quick Inquiry vì field khác nhau)
```
- id
- name* / email* / phone* / address*
- message*
- createdAt
```

## 16. Quan hệ dữ liệu (tóm tắt, cập nhật)

```
Country 1—n TourCategory, Tour, Cruise, Destination, BlogCategory(level=country)
Destination 1—n BlogCategory(level=destination), Article
Tour   n—n TourCategory
Tour   1—n travelStyles (enum, không phải ref)
Tour   1—n ItineraryDay (embedded)
Tour   1—n Review, FAQItem (embedded)
Tour   n—n Tour (relatedTours)
ServiceCategory n—1 cluster (enum)
Service n—1 ServiceCategory, n—1 Country (optional)
Service 1—n ServiceOption
Service 1—n FAQItem (embedded/morph)
Article n—1 BlogCategory
Article n—n ContentTypeTag, PopularKeywordTag
Article n—n Tour (relatedTours — cầu nối content→product QUAN TRỌNG NHẤT hệ thống)
Article n—n Article (relatedArticles/"Blog uguale")
Article 1—n Comment
```

## 17. Facet filter cần build ở tầng query (đã cập nhật đúng theo ảnh)

**Tour Listing:**
- `durationDays` → bucket: <7 / 7-10 / 11-15 / >15 (khớp UI "Oltre 16 giorni")
- `travelStyles[]` → multi-select 11 giá trị (mục 4)
- (tuỳ chọn nâng cao, không thấy trên UI thật nhưng có thể giữ ở backend): `priceFrom` bucket

**Blog Listing:**
- `blogCategory` (theo destination, sidebar)
- `contentTypeTags[]` (nhóm "Filtra articoli")
- Sort: `latest` (Ultimi articoli) — cần thêm option `mostViewed`, `topRated` nếu muốn mở rộng dropdown "Ordina per"

---

## 18. Ánh xạ SQL Laravel (đã triển khai 2026-07-27)

> Chi tiết kiến trúc, index, quy ước i18n/SEO: xem **`07-database-architecture.md`**.  
> Pattern tham chiếu Hitour: Translation Table + SEO hub (`languages`, `seo_entries`, `*_translations`).

| Model docs (mục trên) | Bảng SQL | Ghi chú |
|---|---|---|
| Country | `countries` + `country_translations` | Listing: `tagline`→subtitle, `long_form_content`→seoBody; + `seo_entries` |
| Destination | `destinations` + `destination_translations` | |
| TourCategory | `tour_categories` + `tour_category_translations` | `description`/`seo_intro` columns; public aliases subtitle/seoBody; FAQ morph |
| Tour / Cruise | **`packages`** (`type=tour\|cruise`) + `package_translations` | Gộp 1 bảng sản phẩm |
| ServiceCategory | `service_categories` | `name`, `intro` on main table; listing aliases `subtitle`/`seo_body` → `intro` |
| Service | `services` + `service_translations` | `attrs` JSON theo cụm |
| ServiceOption | `service_options` + `service_option_translations` | Biến thể giá |
| ItineraryDay | `package_itinerary_days` + translations | |
| CabinType | `package_cabin_types` + translations | Chỉ cruise |
| travelStyles[] | `travel_styles` + pivot `package_travel_style` | 11 style seed sẵn |
| Review | `reviews` (morph `reviewable`) | + `media_attachments` ảnh review |
| BlogCategory | `blog_categories` + translations | |
| ContentTypeTag | `content_type_tags` + translations | |
| PopularKeywordTag | `keyword_tags` + translations | |
| Article | `articles` + `article_translations` | pivots tags / related packages / related articles |
| Comment | `comments` | status moderation |
| TeamMember | `team_members` + translations | |
| ExperienceAlbum / Video | `experience_albums` / `experience_videos` (+ photos) | |
| StaticPage | `static_pages` + translations | |
| CompanyIntro / MissionVision / SalesPolicy | `company_profiles` + translations | Singleton-ish |
| CompanyValue | `company_values` + translations | |
| ReasonToChooseUs | `reasons_to_choose_us` + translations | |
| ReferencePersonAbroad | `reference_persons` | |
| Office | `offices` + translations | |
| FAQItem | `faqs` + `faq_translations` (morph) | |
| SEOFields | `seo_entries` + `seo_entry_translations` | Hub routing đa ngữ |
| QuickInquiryLead | `quick_inquiry_leads` | |
| CustomTourRequest | `custom_tour_requests` | |
| ContactMessage | `contact_messages` | |
| (bổ sung UI) USP / HeroPill / ReviewPlatform | `usps`, `hero_pills`, `review_platforms` | |
| Media | `media` + `media_attachments` | cover/gallery/map/collage |

**Migrate:** `php artisan migrate --seed`  
**Seeders:** `LanguageSeeder` (vi/en), `TaxonomySeeder` (11 travel styles, content tags, review platforms), **`ServiceCatalogSeeder`** (trước `TourCategorySeeder` — đọc `service_categories` + `services` từ ProjectSeed).

---

## 19. Catalogue dịch vụ — triển khai (2026-07-31)

| Thành phần | Chi tiết |
|---|---|
| Bảng | `service_categories`, `services`, `service_translations`, `service_options`, `service_option_translations` |
| Config | `config/services_catalog.php` (clusters + `hub_to_cluster`); hubs + types trong `config/seo.php` |
| SEO types | `trains_hub`, `flights_hub`, `stays_hub`, `experiences_hub`, `extras_hub`, `service_category`, `service` |
| Seed keys | `service_clusters`, `service_categories`, `services`, `service_listing_faqs` — trong `project/seed_{name}.php` |
| Seeder | `ServiceCatalogSeeder` (sau `ContentSeeder`, trước `TourCategorySeeder`; `SeoHierarchySeeder` cuối) |
| Public | `ServiceController` + `ListingChrome` / `partials/listing-catalog`; `RoutingController` dispatch; views `pages/services/{hub,index,show}` |
| Admin | **Chưa có** — roadmap CRUD catalogue dịch vụ |
