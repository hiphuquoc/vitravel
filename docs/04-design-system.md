# Design System / Style Guide

Đây là hướng dẫn thiết kế theo **pattern** quan sát được từ site tham khảo (đối chiếu với ảnh thật trong `image_clone/`) — không copy y hệt màu/logo (tránh vi phạm bản quyền thương hiệu), mà build lại đúng cấu trúc UI với bộ nhận diện riêng của bạn.

## 1. Tông màu — hệ thương hiệu "xanh lá rừng"

Màu chính của thương hiệu là **xanh lá rừng (leaf green)**, neo tại `#6b8f3f` — gợi thiên nhiên núi rừng miền Bắc, tin cậy và cao cấp. Terracotta ấm hạ xuống vai trò **accent** (badge khuyến mãi, điểm nhấn nóng, eyebrow script). Nền tổng thể là **kem ngả xanh nhẹ**, card trắng thuần nổi trên nền + bo góc + đổ bóng nhẹ. Footer nền **xanh rêu đậm**.

Token khai báo trong `resources/css/app.css` (`@theme`):

```css
/* Primary — xanh lá rừng, đủ scale 50→900 (CTA, link, rating, icon chip, logo) */
--color-primary-50:  #f5f8ec;
--color-primary-100: #e9efd6;
--color-primary-200: #d3e0b2;
--color-primary-300: #b5cb86;
--color-primary-400: #93b25e;
--color-primary-500: #6b8f3f;   /* màu neo thương hiệu */
--color-primary-600: #567834;   /* hover CTA, link đậm */
--color-primary-700: #455f2c;
--color-primary-800: #3a4e27;
--color-primary-900: #324323;

/* Accent — terracotta ấm (badge "Ưu đãi"/"Bán chạy", eyebrow script, điểm nhấn nóng) */
--color-accent-50:  #fdf3ef;  --color-accent-100: #fbe3da;
--color-accent-500: #d9704f;  --color-accent-600: #c15d3d;  --color-accent-700: #a14a2f;

/* Nền & trung tính (ấm, ngả xanh nhẹ để hoà với primary) */
--color-page: #f2f0e3;        /* nền tổng thể */
--color-page-soft: #f8f6ec;   /* nền section xen kẽ */
--color-strip: #e4e0c9;       /* dải liên hệ trên footer */
--color-ink: #272b23;         /* chữ chính (ngả xanh rêu) */
--color-ink-soft: #514f45;    /* chữ phụ */
--color-muted: #817d6e;       /* meta */
--color-line: #ddd9c2;        /* border */

/* Footer — xanh rêu đậm */
--color-footer: #222a1b;  --color-footer-soft: #303a26;
```

Nguyên tắc dùng màu:
- **Primary xanh lá**: mọi CTA chính, link, badge rating, icon chip tin cậy (USP, bao gồm), nút WhatsApp.
- **Accent terracotta**: CHỈ cho yếu tố cần "nóng"/khẩn cấp — badge khuyến mãi, "Bán chạy nhất", eyebrow script, hộp giá. Không dùng cho CTA chính.
- Card luôn trắng thuần `#ffffff` trên nền kem.

## 2. Typography

Hệ font gồm **3 tầng**, tất cả nạp qua Bunny Fonts (`vite.config.js`) với subset `latin + vietnamese` (bắt buộc — thiếu subset vietnamese sẽ vỡ dấu). Khai báo thành biến trong `resources/css/app.css` (`@theme`), Tailwind sinh utility tương ứng.

| Biến | Font | Weights | Utility | Vai trò |
|---|---|---|---|---|
| `--font-display` | **Fraunces** (serif, fallback Georgia) | 600, 700 | `font-display` | Hero, H1, tiêu đề section (H2 — class `.section-title`), số liệu lớn (404, điểm 5.0), wordmark ViTravel, heading hộp booking |
| `--font-sans` | **Be Vietnam Pro** (fallback system-ui) | 400, 500, 600, 700 | `font-sans` (mặc định body) | Toàn bộ nội dung, nav, button, form, meta — và **tiêu đề cấp item** (card tour/blog, widget sidebar, label nhóm) qua class `.item-title` |
| `--font-accent` | **Dancing Script** (script, fallback cursive) | 600, 700 | `font-accent` | Điểm nhấn cảm xúc: dòng phụ hero, eyebrow phía trên section-title (class `.section-eyebrow`, màu accent terracotta) — **dùng tiết chế**, không quá 1 lần/section, không dùng cho chữ nhỏ hơn ~20px |

### 2.1 Phân tầng vai trò

- **Cấp trang/section** (H1, H2 section) → `font-display` (Fraunces). Serif chỉ xuất hiện ở size lớn (≥20px) nơi nó đủ nổi bật.
- **Cấp item** (tiêu đề card, widget, menu, form step nhỏ) → sans đậm qua class `.item-title` — KHÔNG dùng serif ở size nhỏ (khó đọc, dấu tiếng Việt rối).
- **Accent script** → chỉ để tạo cảm xúc "hand-crafted" ở hero và eyebrow của các section kể chuyện (cảm nhận khách hàng, đội ngũ, tour nổi bật). Luôn kèm `aria-hidden` khi là chữ trang trí lặp nghĩa.
- **Chữ viết hoa (uppercase)** → chỉ dùng cho nhãn nhỏ (kicker/overline, tiêu đề cột footer) qua class `.kicker`: cỡ xs + tracking rất rộng (0.22em) + semibold. KHÔNG viết hoa toàn bộ chữ cỡ lớn.

### 2.2 Base size & scale

**Base font-size 17px** (`html { font-size: 106.25% }` trong `app.css`) — toàn bộ scale rem lớn hơn ~6% so với mặc định 16px, tối ưu dễ đọc cho trang du lịch cao cấp. Utility Tailwind `text-base` = **1rem = ~17px** trên site này.

| Cấp | Class / utility | Size tham chiếu | Dùng cho |
|---|---|---|---|
| Hero H1 | custom / display | 40–72px | Hero slider |
| H1 trang | `font-display` + `text-3xl…5xl` | 32–48px | Page header card |
| H2 section | `.section-title` | 32–40px | Tiêu đề khối nội dung |
| Eyebrow script | `.section-eyebrow` | 24–30px | Accent trên H2 |
| Subtitle section | `.section-subtitle` | `text-base` + leading-7 | Đoạn mô tả dưới H2 |
| **Body chính** | **`.body-text`** hoặc **`text-base`** | **~17px, leading-7** | **Mọi đoạn nội dung trong card/box: review, bio, FAQ answer, intro trang, excerpt blog, inclusion list, contact copy, footer links…** |
| Tiêu đề card tour | `.tour-card-title` | **1.05rem** | H3 trên `card` / `card-compact` |
| Chuỗi điểm đến | `.tour-card-places` | **1.1rem / weight 900** | Places line trên tour card |
| Quote card tour | `.tour-card-quote` | 14.5px italic | Blockquote ngắn trên tour card |
| Meta / chrome | `text-sm` / `text-xs` | 13–14px / 12px | Rating label, ngày đăng, role, badge, filter, tab sticky, form label, lỗi validate, copyright |

### 2.3 Quy tắc bắt buộc (tránh lệch UX khi thiết kế mới)

1. **Không dùng `text-sm` cho nội dung đọc chính** trong card/box/section. Mặc định dùng `.body-text` (hoặc `text-base leading-7 text-ink-soft`). Chỉ dùng `text-sm`/`text-xs` cho meta, badge, nav chrome, form label, microcopy.
2. **Không hardcode `style="font-size:…"`** trên Blade — khai báo class trong `resources/css/app.css` (`@layer components`) rồi dùng lại (xem `.tour-card-*`, `.body-text`, `.section-*`).
3. **Không dùng `text-[15px]` / `text-[13px]` ad-hoc** cho body — thay bằng `.body-text` hoặc `text-base` / `text-sm` theo bảng trên.
4. **Buttons (bắt buộc)** — không dùng `rounded-full` cho CTA. Radius **12px** (`.btn-primary`, `.btn-outline`) hoặc **10px** (`.btn-primary-sm`, `.btn-whatsapp`, `.btn-chip`, `.nav-link`). **Font luôn `text-base`** trên mọi size; chỉ khác padding. Hover có **vệt sáng** (gradient chạy ngang). Icon: trái = hành động khởi tạo (`search`, `mail`, `sparkles`, `whatsapp`, `filter`, `check`); phải = điều hướng tiếp (`arrow-right`).
5. **Nav chính** — `.nav-link` (`text-base`, radius 10px); mục mega menu `.nav-panel-item` / `.nav-panel-link`. Bộ lọc: `.filter-legend` + `.filter-option`.
6. **Tour / cruise listing grid** dùng component dùng chung (`x-tour.card`, `x-tour.card-compact`) — chỉnh typography tại component + CSS class, không chỉnh riêng từng trang danh mục.
7. **Rating** dùng `x-shared.rating` + `x-shared.stars` duy nhất — không tự vẽ sao/`★` rời trên frontend.
8. Body dài (blog, SEO intro, itinerary) dùng `.prose-travel` (`text-base` + leading ~1.75).

### 2.4 Class dùng chung (định nghĩa trong `app.css`)

| Class | Vai trò |
|---|---|
| `.section-title` | H2 section, Fraunces bold |
| `.section-eyebrow` | Script accent trên tiêu đề |
| `.section-subtitle` | Mô tả dưới H2 — `text-base` |
| `.body-text` | **Đoạn nội dung chính trong box** — `text-base leading-7 text-ink-soft` |
| `.item-title` | Tiêu đề cấp item (sans bold) |
| `.tour-card-title` / `.tour-card-places` / `.tour-card-quote` / `.tour-card-route*` | Typography + layout route trên tour card |
| `.nav-link` / `.nav-panel-item` / `.nav-panel-meta` / `.nav-panel-link` | Menu chính + mega menu |
| `.filter-legend` / `.filter-option` | Bộ lọc danh mục tour/cruise |
| `.btn-primary` | CTA chính — radius 12px, `text-base`, vệt sáng hover |
| `.btn-primary-sm` | CTA gọn — radius 10px, cùng `text-base` |
| `.btn-outline` | CTA viền — radius 12px |
| `.btn-ghost` | Link-CTA (thường + `arrow-right` bên phải) |
| `.btn-whatsapp` | CTA WhatsApp — radius 10px |
| `.btn-chip` | Chip chọn nhanh (hero pills, loại cruise) — radius 10px |
| `.kicker` | Nhãn viết hoa nhỏ |
| `.rating` / `.rating-badge` | Hàng rating + badge điểm tròn |
| `.prose-travel` | Nội dung dài (blog / SEO) |

## 2.5 Buttons (CTA)

| Class | Radius | Font | Padding | Dùng khi |
|---|---|---|---|---|
| `.btn-primary` | **12px** | `text-base` | `px-7 py-3` | CTA chính trong nội dung / form |
| `.btn-primary-sm` | **10px** | `text-base` | `px-5 py-2` | Header, filter, card tour, FAB |
| `.btn-outline` | **12px** | `text-base` | `px-7 py-3` | CTA phụ |
| `.btn-ghost` | — | `text-base` | — | Link-CTA trong card |
| `.btn-whatsapp` | **10px** | `text-base` | như sm | Chat WhatsApp |
| `.btn-chip` | **10px** | `text-base` | `px-4 py-2` | Chip chọn (hero / loại cruise) |

- **Cấm** `rounded-full` trên CTA (tránh pill tròn).
- **Vệt sáng:** lớp `::after` gradient trắng mỏng (~28% opacity) trượt ngang khi hover — **không** nhuộm đậm nền button; nền giữ `primary-500` / hover `primary-600` như palette cũ.
- **Icon:** trái = khởi tạo (`search`, `mail`, `sparkles`, `whatsapp`, `filter`, `check`); phải = đi tiếp (`arrow-right`). Không chồng 2 icon hai phía trừ khi có lý do rõ.
- Không override `text-[13px]` / `text-sm` trên class `.btn-*` — giữ một chuẩn chữ.

Body line-height mục tiêu: **1.6–1.75** (`.body-text` / `.prose-travel`).

## 3. Layout Grid
- Container max-width ~1200–1280px
- **Breadcrumb + H1 luôn nằm trong 1 card trắng bo góc đè lên banner ảnh** (không phải text rời trên nền ảnh) — pattern quan sát nhất quán ở Tour Listing, About Us
- Trang danh mục Tour: sidebar filter trái cố định (~280px desktop, chuyển thành offcanvas mobile) + list card phải
- Trang danh mục Blog: grid bài viết 2 cột (khu vực chính, rộng hơn) + sidebar phải hẹp hơn (~300px) — **ngược vị trí so với Tour Listing** (Tour: filter bên TRÁI; Blog: sidebar bên PHẢI) — cần lưu ý khi build layout component, không dùng chung 1 layout 2 cột cứng cho cả 2 loại trang

## 4. Component Inventory (đã bổ sung component mới phát hiện từ ảnh)

| Component | Mô tả | Dùng ở trang |
|---|---|---|
| `Header` (mega menu) | Logo, nav DESTINAZIONI/CROCIERE/COSE DA FARE + CHI SIAMO/CONTATTACI, search icon, CTA button, language flags | Toàn site |
| `HeroWithQuickPills` | Ảnh nền hero + hàng pill chọn nhanh danh mục tour đặt sát mép trên | Home |
| `HeroSearchBox` | Card trắng nổi đè lên hero: dropdown Destinazione + Durata + nút Cerca | Home |
| `USPBadgeGroup` | 4 badge icon+text ngang hàng | Home, About Us |
| `PageHeaderCard` | **Card breadcrumb + H1 đè lên banner** — component dùng chung cho mọi trang danh mục/tĩnh | Tour Listing, Blog Listing, About Us... |
| `TourCard` (variant `listing`) | Ảnh trái + nội dung phải: badge rating tròn, badge duration, quote review, địa danh, accordion "Attrazioni principali", CTA | Tour Listing |
| `TourCard` (variant `home-compact`) | Ảnh trên + nội dung dưới, gọn hơn, chỉ 3 hiển thị | Home |
| `FilterSidebar` (variant Tour) | Checkbox "Durate" + "Stile di viaggio" + nút Annulla/Applica | Tour/Cruise Listing |
| `BentoDestinationGrid` | Grid không đều (1 ô lớn + nhiều ô nhỏ), overlay tên quốc gia | Home |
| `TestimonialCarousel` | 3 card cùng lúc + nút điều hướng, mỗi card: avatar+quốc gia, rating, quote, dải ảnh +N | Dùng chung nhiều trang |
| `TestimonialLogos` | 3 logo (TripAdvisor/Google/Trustpilot) + quote + link | Dùng chung nhiều trang |
| `TeamGrid` (variant có bio) | Avatar + tên + chức danh + đoạn bio cắt "..." | Home, About Us |
| `VideoShowcase` | 1 video lớn trái + list video nhỏ phải | Home, About Us |
| `QuickInquiryForm` | Form 2 cột + 2 hình minh hoạ illustration (xe đạp, gánh hàng) | Cuối hầu hết các trang |
| `Breadcrumb` | Auto-generate từ route, schema.org markup | Toàn site |
| `ImageGallery` | Ảnh chính + thumbnail strip, lightbox | Tour/Cruise Detail |
| `HeroCollageGallery` | 1 ảnh lớn + lưới 4 ảnh nhỏ (kiểu collage) | Blog Article đầu bài |
| `TourMapViewer` | Ảnh bản đồ + lightbox zoom | Tour/Cruise Detail |
| `StickyTabNav` | Anchor tab sticky (Overview/Highlight/Itinerary...) | Tour/Cruise Detail |
| `ItineraryAccordion` | Accordion "Expand All", mỗi item = 1 ngày | Tour/Cruise Detail |
| `TransportIconRow` | Hàng icon phương tiện trong ngày | Trong ItineraryAccordion |
| `InclusionExclusionList` | Bullet list ✓/✗ | Tour/Cruise Detail |
| `BookingSidebar` (sticky) | Giá/CTA/USP thu nhỏ | Tour/Cruise Detail |
| `ReviewList` (Q&A style) | Avatar + tên + ngày + rating + Q&A | Tour Detail |
| `FAQAccordion` | Câu hỏi/trả lời expand, schema FAQPage | Tour Listing, Tour Detail, Blog Listing, Blog Article |
| `RelatedGrid` | Carousel/grid item liên quan | Nhiều trang |
| `BlogCard` | Ảnh + ngày/views + tiêu đề + tác giả/tag + excerpt | Blog Listing |
| `BlogSidebar` | "Categorie del blog" (list scroll) + "Filtra articoli" (tag button) + "Mots-clés populaires" (tag cloud) | Blog Listing, Blog Article |
| `ArticleTOC` | "Sommario dell'articolo" — mục lục tự sinh từ heading | Blog Article |
| `InlineRelatedLinksBox` | Box "Vedi di più:" chèn giữa nội dung bài | Blog Article |
| `ArticleRatingAndShare` | Rating cuối bài + nút share social | Blog Article |
| `CommentForm` + `CommentList` | Form bình luận (Nome, Email, Telefono, Commento) | Blog Article |
| `ValuesCircleDiagram` | Sơ đồ vòng tròn 4 giá trị cốt lõi quanh 1 tâm | About Us |
| `SignpostImageBlock` | Block ảnh biển gỗ khắc chữ (Mission/Vision) + text cạnh | About Us |
| `ReasonsListWithImage` | Ảnh trái (device mockup) + danh sách lý do phải | About Us |
| `ReferencePersonCard` | Ảnh + tên + email/phone/skype | About Us |
| `TravelerCountStepper` | Nhóm stepper [−] số [+] cho Adulti/bambini/Neonati | Customize Tour form |
| `CheckboxPillGroup` | Checkbox dạng ngang cho chọn quốc gia/loại khách sạn | Customize Tour form |
| `BudgetInput` | Input số + currency icon + dropdown Per persona/Per gruppo | Customize Tour form |
| `ContactStrip` | Dải liên hệ (logo, email, phone, QR code WhatsApp, địa chỉ 3 văn phòng) — nằm giữa nội dung trang và Footer đen | Toàn site |
| `Footer` | 4 cột menu + hàng link SEO rời + copyright + social | Toàn site |
| `FloatingActionButtons` | WhatsApp chat + "Tailor made tour" nổi góc màn hình | Toàn site |
| `Pagination` | Số trang | Tour/Cruise/Blog Listing |
| `RatingStars` | Hiển thị + input rating | Nhiều nơi |
| `SortDropdown` | "Ordina per: Popolarità / Ultimi articoli..." | Tour Listing, Blog Listing |

## 5. Nguyên tắc UX quan trọng cần giữ khi clone

1. **Card overlay cho tiêu đề trang** — không đặt H1/breadcrumb trực tiếp lên ảnh banner; luôn bọc trong card trắng bo góc để đảm bảo contrast & thống nhất phong cách.
2. **Nội dung dài nhưng có cấu trúc rõ** — accordion cho itinerary/FAQ, mục lục (TOC) cho bài blog dài.
3. **CTA lặp lại nhiều lần** — Quick Inquiry ở cuối hầu hết trang + sticky booking sidebar ở Tour Detail + nút Customize Tour nổi bật trên header mọi trang.
4. **Trust signal dày đặc và lặp lại** — testimonial carousel + logo TripAdvisor/Google/Trustpilot xuất hiện lại ở Home, Tour Listing, About Us — không chỉ 1 lần.
5. **Internal linking chủ động, đặt NGAY TRONG luồng đọc** — không chỉ ở cuối bài; box "Vedi di più:" chèn giữa nội dung Blog Article là ví dụ điển hình cần tái tạo đúng.
6. **Illustration mộc mạc, mang bản sắc địa phương** — 2 hình minh hoạ vẽ tay (xe đạp chở hoa, người gánh hàng) ở Quick Inquiry Form là điểm nhấn thương hiệu, không phải ảnh chụp — nên tạo bộ illustration riêng phù hợp brand mới nếu clone.
7. **2 loại layout sidebar khác nhau, không dùng chung 1 layout**: Tour Listing = filter TRÁI, danh sách PHẢI; Blog Listing/Article = nội dung TRÁI, sidebar thông tin PHẢI.
8. **Mobile-first cho filter phức tạp** — cả FilterSidebar (Tour) và BlogSidebar (Blog) nên chuyển thành drawer/accordion trên mobile.
9. **Form dài nhưng KHÔNG chia bước (wizard)** — Customize Tour là form 1 trang dài chia khối bằng card, không phải multi-step; giữ nguyên pattern này trừ khi có lý do UX mạnh để đổi sang wizard.
10. **Rating hiển thị dạng badge số tròn kèm nhãn** (VD "5.0 Xuất sắc") thay vì chỉ hiện sao — dùng nhất quán qua `x-shared.rating` / `x-shared.stars` ở mọi nơi có rating.
11. **Typography body = `text-base` (~17px)** — mọi đoạn nội dung chính trong card/box dùng `.body-text`; không thu nhỏ bằng `text-sm` trừ meta/chrome. Chi tiết bảng scale & quy tắc ở mục 2.
12. **Button không bo tròn pill** — CTA dùng radius 10–12px (xem `.btn-*`), font `text-base` thống nhất, hover vệt sáng; icon trái/phải theo quy ước mục 2.3. Không dùng `rounded-full` cho nút CTA (FAB icon-only cũng ưu tiên radius 10px qua `.btn-whatsapp` / `.btn-primary-sm`).

## 6. Icon set cần chuẩn bị
Phương tiện di chuyển (itinerary): car, bus, train, plane, boat/cruise, sampan, bicycle, walking, trekking/hiking, kayak.
UI icon: search, filter, star, whatsapp, checkmark, cross, map-pin, calendar, chevron, phone, mail, quote-mark (dấu ngoặc kép cho review), eye (lượt xem blog), share/facebook/twitter, skype.
Illustration: 2 minh hoạ vẽ tay phong cách bản địa cho Quick Inquiry Form (tuỳ biến theo brand riêng).
Diagram: sơ đồ vòng tròn cho `ValuesCircleDiagram` (có thể build bằng SVG absolute-position hoặc CSS grid circular layout).
