# Tech Stack & Hướng dẫn triển khai (cho Cursor)

> **Cập nhật:** đã bổ sung yêu cầu kỹ thuật phát sinh từ việc đối chiếu ảnh thật (xem `06-tham-chieu-hinh-anh.md`) — chủ yếu liên quan tới hệ Blog (TOC tự sinh, comment có kiểm duyệt), form Customize Tour (field phức tạp, không phải wizard), và layout 2 kiểu sidebar khác nhau giữa Tour Listing và Blog Listing.

## 1. Stack đề xuất

| Lớp | Lựa chọn đề xuất | Lý do |
|---|---|---|
| Framework | **Next.js 14+ (App Router)** *(đề xuất ban đầu)* / **Laravel 13 + Blade** *(đang triển khai tại `vitravel.dev`)* | UI hiện tại đã dựng bằng Laravel Blade; schema DB đã sẵn sàng cho Eloquent |
| Ngôn ngữ | TypeScript / PHP 8.3 | |
| Styling | Tailwind CSS (+ Alpine trên Laravel) | |
| Nội dung/CMS | Eloquent + admin (giai đoạn sau); hoặc Sanity/Payload nếu tách headless | Schema SQL: `docs/07-database-architecture.md` |
| Form/Lead | Validate + lưu `quick_inquiry_leads` / `custom_tour_requests` / `contact_messages` | 3 bảng tách — xem `03-data-models.md` §15 & §18 |
| Ảnh | `media` + `media_attachments` + CDN sau | |
| Search/Filter | Query param + index trên `packages` (duration, country, travel_style pivot) | |
| i18n/SEO | Pattern Hitour: `languages` + `seo_entries` + `*_translations` | |

> **Cập nhật 2026-07:** CSDL production-ready đã migrate trên MySQL `vitravel`. UI vẫn dùng `SampleData` — bước tiếp theo là wire Eloquent thay mock.

## 2. Cấu trúc thư mục đề xuất (App Router)

```
src/
  app/
    (site)/
      layout.tsx                     # Header + Footer + Floating buttons
      page.tsx                       # Home
      tours/
        [country]/
          page.tsx                   # Tour Listing theo quốc gia (+ ?category=&duration=&price=)
          [slug]/
            page.tsx                 # Tour Detail
            inquiry/page.tsx         # Price Request form (nếu không dùng modal)
      cruises/
        [type]/
          page.tsx
          [slug]/page.tsx
      travel-guide/  (= "COSE DA FARE" trên menu thật)
        page.tsx                     # All travel guide
        [country]/
          [destination]/
            page.tsx                 # Danh mục theo điểm đến (sidebar: BlogCategory/ContentTypeTag/PopularKeywordTag)
            [slug]/page.tsx           # Bài viết (TOC tự sinh, comment form, inline related links)
      about-us/page.tsx              # Trang tổng hợp dài (Chi Siamo): intro, team, mission/vision, values circle,
                                      # sale policy, reasons, reference persons, video — ráp từ section component riêng
      our-team/page.tsx              # Optional: tách riêng nếu muốn URL riêng ngoài About Us
      customers-reviews/page.tsx
      experience-gallery/page.tsx
      experience-video/page.tsx
      contact-us/page.tsx            # Form tối giản: Nome/Email/Telefono/Indirizzo/Messaggio
      customize-tour/page.tsx        # Form 1 trang dài, 3 khối card, KHÔNG chia wizard
    api/
      leads/quick-inquiry/route.ts   # QuickInquiryLead
      leads/customize-tour/route.ts  # CustomTourRequest (field phức tạp, xem 03-data-models.md §15.2)
      leads/contact/route.ts         # ContactMessage
      comments/route.ts              # Nhận comment bài blog, set status="pending" chờ duyệt
  components/
    layout/ (Header, Footer, ContactStrip, MegaMenu, FloatingButtons, Breadcrumb, PageHeaderCard)
    home/ (HeroWithQuickPills, HeroSearchBox, USPBadgeGroup, BentoDestinationGrid, TeamGrid, VideoShowcase)
    tour/ (TourCard, FilterSidebar, ImageGallery, ItineraryAccordion, BookingSidebar, InclusionExclusionList)
    blog/ (BlogCard, BlogSidebar, ArticleTOC, HeroCollageGallery, InlineRelatedLinksBox, ArticleRatingAndShare, CommentForm, CommentList)
    about/ (ValuesCircleDiagram, SignpostImageBlock, ReasonsListWithImage, ReferencePersonCard)
    forms/ (QuickInquiryForm, CustomizeTourForm — TravelerCountStepper, CheckboxPillGroup, BudgetInput, ContactForm)
    shared/ (RatingStars, ReviewList, FAQAccordion, RelatedGrid, TestimonialCarousel, TestimonialLogos, SortDropdown, Pagination)
    ui/ (shadcn primitives)
  lib/
    cms/ (client + query functions: getTourBySlug, getToursByCountry, ...)
    schema/ (zod schemas khớp data-models.md)
    seo/ (helper generate metadata, JSON-LD)
  types/
    tour.ts, cruise.ts, article.ts, country.ts, review.ts ...  # khớp 03-data-models.md
```

## 3. SEO kỹ thuật cần làm đúng ngay từ đầu
- Mỗi route generate `generateMetadata()` riêng (title, description, OG image) theo field `seo` trong data model.
- JSON-LD:
  - Home/Company: `Organization` + `LocalBusiness` (nhiều địa chỉ chi nhánh)
  - Tour Detail: `TouristTrip` hoặc `Product` + `AggregateRating` + `Review[]`
  - Travel Guide Article: `Article`
  - Mọi trang: `BreadcrumbList`
  - FAQ block: `FAQPage`
- `sitemap.xml` generate động từ toàn bộ Tour/Cruise/Article/Country (dùng Next.js `app/sitemap.ts`).
- `robots.txt` cho phép crawl toàn bộ trừ trang inquiry/API.
- Ảnh: bắt buộc `alt` text mô tả (site gốc làm khá tốt việc này — mỗi ảnh itinerary đều có alt riêng).
- Core Web Vitals: ưu tiên ảnh hero preload, tránh layout shift ở gallery/carousel (đặt aspect-ratio cố định).

## 4. Form & Lead handling

Có **3 loại form khác nhau về field**, quan sát rõ từ ảnh thật — không nên gộp chung 1 schema:
1. **QuickInquiryLead** (Nome/Email/Telefono/Indirizzo/Message) — lặp lại cuối hầu hết các trang
2. **CustomTourRequest** (rất nhiều field: số khách theo độ tuổi, ngày, quốc gia, hạng khách sạn, ngân sách, thông tin cá nhân) — form dài 1 trang, submit ở `/customize-tour`
3. **ContactMessage** (Nome/Email/Telefono/Indirizzo/Messaggio) — trang Contact riêng, tương tự Quick Inquiry nhưng nên tách model để dễ báo cáo/thống kê nguồn lead riêng

Ngoài ra còn **Comment** (bình luận blog) — không phải lead bán hàng nhưng cùng nhóm "user-submitted content", cần **trạng thái kiểm duyệt** (`pending/approved/rejected`) trước khi hiển thị công khai, tránh spam/toxic content.

- Validate bằng Zod ở cả client & server, khớp đúng field bắt buộc (`*`) như trong ảnh — đặc biệt `CustomTourRequest` có tới ~13 field required.
- Sau submit: hiển thị confirmation UI (không redirect trang khác) + optionally trigger GTM `generate_lead` event (tách event name theo loại form để đo lường riêng).
- Cân nhắc rate-limit + honeypot field chống spam bot cho cả form lead và form comment.
- `TravelerCountStepper` (Adulti/bambini/Neonati) nên là 1 component dùng chung có thể tái sử dụng cho các form du lịch khác trong tương lai.

## 4b. Blog: Table of Contents (TOC) tự sinh

Trang bài viết có "Sommario dell'articolo" tự sinh từ heading H2/H3 trong nội dung — khi dùng MDX/rich text renderer:
- Parse cây heading tại build-time (hoặc render-time nếu dùng CMS block-based), gán `id` slug cho mỗi heading.
- Render sidebar TOC với anchor link `#slug`, active-state theo `IntersectionObserver` khi scroll (tương tự `StickyTabNav` ở Tour Detail).
- Không cần lưu TOC như 1 field riêng trong CMS — luôn derive từ `content` để tránh lệch dữ liệu khi sửa bài.

## 5. Roadmap triển khai đề xuất (cho Cursor thực hiện theo giai đoạn)

**Giai đoạn 1 — Nền tảng**
1. Setup Next.js + Tailwind + shadcn, định nghĩa design tokens theo `04-design-system.md`
2. Build `types/` khớp `03-data-models.md`
3. Build Header (mega menu), Footer, FloatingActionButtons, Breadcrumb — layout dùng chung

**Giai đoạn 2 — Trang tour (core bán hàng)**
4. Tour Listing page + FilterSidebar (mock data trước, CMS sau)
5. Tour Detail page đầy đủ (Gallery, StickyTabNav, ItineraryAccordion, BookingSidebar, Inclusion/Exclusion, Reviews, FAQ, RelatedGrid)
6. QuickInquiryForm + PriceRequestModal + API `/api/leads`

**Giai đoạn 3 — Nhân bản sang Cruise**
7. Reuse toàn bộ component Tour cho Cruise, chỉ đổi field đặc thù (cabinTypes, departurePort)

**Giai đoạn 4 — Content hub (Blog/Travel Guide — phức tạp hơn dự kiến ban đầu, tách kỹ)**
8a. Blog Listing: `BlogCard` grid + `BlogSidebar` (Categorie del blog / Filtra articoli / Mots-clés populaires) + FAQ + pagination
8b. Blog Article: `HeroCollageGallery` + `ArticleTOC` + nội dung MDX + `InlineRelatedLinksBox` + `ArticleRatingAndShare` + `CommentForm`/`CommentList` + related articles
9. Static pages: `CustomizeTourForm` (form dài 1 trang, không wizard), `ContactForm` (tối giản), `about-us` page (ráp các section component ở `components/about/`)

**Giai đoạn 5 — Trang chủ & hoàn thiện SEO**
10. Home page (ráp các component đã có sẵn từ giai đoạn 1-4)
11. JSON-LD, sitemap.xml, robots.txt, metadata cho toàn bộ route
12. Kết nối CMS thật (nếu dùng Sanity/Payload) thay mock data
13. QA responsive, Lighthouse audit, tối ưu ảnh

## 6. Lưu ý pháp lý khi "clone"
- Không copy nguyên văn nội dung (mô tả tour, bài travel guide), không copy ảnh gốc, không dùng logo/tên thương hiệu "Autour Asia" — chỉ tái sử dụng **cấu trúc trang & luồng UX**, còn nội dung/hình ảnh/thương hiệu phải là của bạn hoặc nguồn có bản quyền hợp lệ.
- Nếu đây là dự án học tập/demo cá nhân, vẫn nên đổi tên thương hiệu, logo, và nội dung mẫu trước khi public.
