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
6. **Tour / cruise / service listing** dùng chung `ListingChrome` + `partials/listing-catalog` + card components (`x-tour.card`, `x-service.card`) — không chỉnh riêng từng trang danh mục.
7. **Rating** dùng `x-shared.rating` + `x-shared.stars` duy nhất — không tự vẽ sao/`★` rời trên frontend.
8. Body dài (blog, SEO intro, itinerary) dùng `.prose-travel` (`text-base` + leading ~1.75).

### 2.4 Class dùng chung (định nghĩa trong `app.css`)

| Class | Vai trò |
|---|---|
| `.section-title` | H2 section, Fraunces bold — size theo `--fs-section-title` |
| `.section-eyebrow` | Script accent trên tiêu đề — size theo `--fs-section-eyebrow` |
| `.section-subtitle` | Mô tả dưới H2 — `text-base` |
| `.section-band` / `.section-band--sm` | Nhịp dọc section — `padding-block` theo `--space-section-y*`; follower liền nhau auto `padding-top: 0` (§3.5) |
| `.section-band--spaced-top` | Khôi phục `padding-top` khi cần tách rộng section follower |
| `.site-gap` / `.site-gap-sm` / `.site-gap-lg` | Gap lưới theo token |
| `.site-stack` / `.site-stack-lg` | Xếp dọc theo `--space-stack*` |
| `.site-mt` / `.site-mt-lg` / `.site-mb` | Margin block theo token |
| `.site-pad` / `.site-pad-x` / `.site-pad-y` | Padding khung theo gutter/card |
| `.body-text` | **Đoạn nội dung chính trong box** — `text-base` + `--lh-body` |
| `.max-line-1` / `.max-line-2` / `.max-line-3` | Giới hạn số dòng chữ (ellipsis) — dùng chung toàn site |
| `.item-title` | Tiêu đề cấp item (sans bold) |
| `.tour-card-title` / `.tour-card-places` / `.tour-card-quote` / `.tour-card-route*` | Typography + layout route trên tour card |
| `.nav-link` / `.nav-panel-item` / `.nav-panel-meta` / `.nav-panel-link` | Menu chính + mega menu |
| `.count-badge` / `.nav-panel-count` | Badge số lượng (tour/bài) — primary-500, dùng chung header + blog sidebar |
| `.vt-check` / `__input` / `__box` / `__icon` / `__text` | Checkbox thương hiệu (listing + form) |
| `.vt-chip` / `.vt-chip-list` | Chip tag sidebar / filter phụ |

| `.btn-primary` | CTA chính — radius 12px, `text-base`, vệt sáng hover |
| `.btn-primary-sm` | CTA gọn — radius 10px, cùng `text-base` |
| `.btn-outline` | CTA viền — radius 12px |
| `.btn-ghost` | Link-CTA (thường + `arrow-right` bên phải) |
| `.btn-whatsapp` | CTA WhatsApp — radius 10px |
| `.btn-chip` | Chip chọn nhanh (hero pills, loại cruise) — radius 10px |
| `.kicker` | Nhãn viết hoa nhỏ — `--fs-kicker` |
| `.header-wordmark__tagline` | Tagline dưới logo header — ẩn ≤567px |
| `.rating` / `.rating-badge` | Hàng rating + badge điểm tròn — `--size-rating-badge` |
| `.prose-travel` | Nội dung dài (blog / SEO) |
| **Card inner** | |
| `.card-body` | Padding card — `--space-card` |
| `.card-inner` | Stack dọc trong card — `--space-card-stack` |
| `.card-meta-row` | Hàng meta (rating + badge…) — `--space-gap-sm` / `--space-gap` |
| `.card-footer` / `.card-footer-row` | Footer card / hàng CTA — `--space-card-stack-lg` |
| **Tour / blog card** | |
| `.tour-card-*` | Typography + media + highlights accordion |
| `.blog-card-meta` | Meta hàng blog card |
| **Grid block dùng chung** | |
| `.usp-item` / `.usp-item__title` | 4 cam kết dưới hero — title `text-transform: capitalize` |
| `.team-card` / `.team-card-avatar` | Grid đội ngũ — 1 cột ≤567, gap avatar↔tên |
| `.review-card` / `.review-card__brand` | 3 card nền tảng đánh giá — stars căn giữa |
| `.snap-carousel` / `.snap-carousel__item` | Testimonial carousel — width theo `--space-gap` |
| **Footer** | |
| `.footer-contact__*` | Contact strip (brand, channels, office, QR) |
| `.footer-nav__*` / `.footer-seo` / `.footer-bottom` | Footer chính, SEO links, copyright |
| `.footer-col__title` | Tiêu đề cột — gạch ngang brand |
| **About page** | |
| `.signpost-card` / `.signpost-card__*` | Block Sứ mệnh / Tầm nhìn (ảnh + copy) |
| `.values-diagram` / `.values-diagram__*` | Sơ đồ vòng tròn 4 giá trị |
| `.reason-list` / `.reason-list__*` | Danh sách đánh số "Vì sao chọn" |
| `.ref-person-card` / `.ref-person-card__*` | Card đại diện nước ngoài |
| `.company-intro__license` | Khối giấy phép trong company intro |
| **Listing tour/cruise** | |
| `.listing-layout` | Grid sidebar 280px + danh sách — `--space-gap-lg` |
| `.listing-toolbar` / `__count` / `__count-num` / `__count-label` | Count pill (Fraunces + primary) + sort |
| `.listing-empty` / `.listing-seo` / `.listing-faq` | Empty state, khối SEO, offset FAQ |
| `.listing-rating-summary` | Điểm 5.0 tổng danh mục tour |
| `.cruise-type-nav` | Pill chuyển tuyến du thuyền |
| `.filter-sidebar__*` | Drawer filter mobile + panel desktop |
| `.sort-dropdown__*` | Dropdown sắp xếp danh mục |
| `.faq-list` / `.faq-item__*` | FAQ accordion (dùng chung) |
| **Service catalogue** | |
| `.header-more-btn` / `.header-more-panel` | Drawer nav phụ (Cẩm nang, About, Contact…) |
| `x-service.card` | Card ngang listing dịch vụ — reuse `.tour-card-*`, `.card-body` |
| `x-service.detail` | Detail dịch vụ — reuse `.detail-layout`, `.detail-sidebar__*` |
| `.tour-card-duration` | Chip thời lượng góc ảnh — font/padding khớp `.tour-card-badge` |
| `.tour-card-price` / `__label` / `__value` | Label `fs-meta`; giá Fraunces `accent-500` |
| **Detail tour/cruise** | `.detail-*`, `.detail-sidebar__*`, `.cabin-card__body` — H1 dùng `--fs-section-title`; section H2 `--fs-detail-title`; giá sidebar `--fs-detail-price*`; spacing `--space-stack*` / `--space-card*` |
| **Blog** | `.blog-layout`, `.blog-sidebar__*`, `.blog-article-*`, `.blog-inline-links`, `.blog-card-tag` |
| **Form / liên hệ** | `.customize-form`, `.form-section__*`, `.form-stepper__*`, `.form-pill__label`, `.contact-*`, `.form-success` |
| **Gallery / reviews / search** | `.gallery-card__*`, `.reviews-summary`, `.search-section__*`, `.page-follow` |

## 2.5 Buttons (CTA)

Padding và min-height **không hardcode** — dùng token responsive:

| Token | Vai trò |
|---|---|
| `--btn-pad-x` / `--btn-pad-y` | `.btn-primary`, `.btn-outline` |
| `--btn-pad-x-sm` / `--btn-pad-y-sm` | `.btn-primary-sm`, `.btn-whatsapp`, `.btn-zalo`, `.nav-link` |
| `--btn-pad-x-chip` / `--btn-pad-y-chip` | `.btn-chip` |
| `--btn-gap` | Khoảng cách icon ↔ chữ trong mọi `.btn-*` |
| `--space-hit` | `min-height` tất cả CTA (≥44px mobile) |

| Class | Radius | Font | Padding (desktop) | Dùng khi |
|---|---|---|---|---|
| `.btn-primary` | **12px** | `--fs-body` | `--btn-pad-x/y` | CTA chính trong nội dung / form |
| `.btn-primary-sm` | **10px** | `--fs-body` | `--btn-pad-x-sm/y-sm` | Header, filter, card tour, FAB |
| `.btn-outline` | **12px** | `--fs-body` | `--btn-pad-x/y` | CTA phụ |
| `.btn-ghost` | — | `--fs-body` | — | Link-CTA trong card |
| `.btn-whatsapp` / `.btn-zalo` | **10px** | `--fs-body` | `--btn-pad-x-sm/y-sm` | FAB chat |
| `.btn-chip` | **10px** | `--fs-body` | `--btn-pad-x-chip/y-chip` | Chip hero / loại cruise |

- **Cấm** `rounded-full` trên CTA (tránh pill tròn).
- **Vệt sáng:** lớp `::after` gradient trắng mỏng (~28% opacity) trượt ngang khi hover — **không** nhuộm đậm nền button; nền giữ `primary-500` / hover `primary-600` như palette cũ.
- **Icon:** trái = khởi tạo (`search`, `mail`, `sparkles`, `whatsapp`, `filter`, `check`); phải = đi tiếp (`arrow-right`). Không chồng 2 icon hai phía trừ khi có lý do rõ.
- Không override `text-[13px]` / `text-sm` trên class `.btn-*` — giữ một chuẩn chữ.

Body line-height mục tiêu: **1.6–1.75** (`.body-text` / `.prose-travel`).

## 3. Layout Grid & Responsive (chuẩn bắt buộc)

### 3.1 Container & lưới
- Container max-width ~1200–1280px (`.container-site` → `max-w-7xl`)
- **Breadcrumb + H1 luôn nằm trong 1 card trắng bo góc đè lên banner ảnh** (không phải text rời trên nền ảnh) — pattern quan sát nhất quán ở Tour Listing, About Us
- Trang danh mục Tour: sidebar filter trái cố định (~280px desktop, chuyển thành offcanvas mobile) + list card phải
- Trang danh mục Blog: grid bài viết 2 cột (khu vực chính, rộng hơn) + sidebar phải hẹp hơn (~300px) — **ngược vị trí so với Tour Listing** (Tour: filter bên TRÁI; Blog: sidebar bên PHẢI) — cần lưu ý khi build layout component, không dùng chung 1 layout 2 cột cứng cho cả 2 loại trang

### 3.2 Breakpoint ladder (max-width — bắt buộc toàn site)

Hệ responsive dùng **5 mốc `max-width`** cố định (không thêm mốc ad-hoc mới trừ khi cập nhật doc này). Token spacing / typography / container gutter được cascade qua CSS variables trong `resources/css/app.css` (`:root` + `@media (max-width: …)`).

| Mốc | `max-width` | Vai trò thiết kế | Hành vi chính |
|---|---|---|---|
| **BP-XL** | `1199px` | Laptop nhỏ / tablet ngang lớn | Thu gutter container, section Y nhẹ, H1/H2 giảm 1 bậc |
| **BP-LG** | `1023px` | Tablet ngang / dưới desktop | Ẩn mega menu → mobile nav; grid 2–3 cột → 2 cột; sidebar → drawer |
| **BP-MD+** | `990px` | Tablet trung | Section stack; card listing chuyển layout dọc khi cần; form 2 cột → 1 cột |
| **BP-MD** | `768px` | Tablet dọc / mobile lớn | Typography mobile; FAB chỉ icon; tour card full-bleed ảnh trên |
| **BP-SM** | `567px` | Mobile hẹp | Gutter tối thiểu; giảm section Y; chữ hero/section compact; tránh hover-only UX |

**Quy tắc triển khai:**
1. **Token trước, utility sau** — spacing section dùng `var(--space-section-y)`, gutter dùng `var(--space-gutter)`, không hardcode `py-16` / `px-8` rời trên nhiều trang nếu có class dùng chung (`.section-band`, `.container-site`).
2. **Mobile không phụ thuộc hover** — mega menu, tooltip, drawer phải mở bằng tap; FAB luôn đủ hit-area ≥44px.
3. **Performance** — ảnh `loading="lazy"` dưới fold; section dài dùng `.cv-auto` (`content-visibility`); tránh layout shift (aspect-ratio / kích thước cố định cho media).
4. **Một nguồn sự thật** — mọi media query custom mới phải map vào 1 trong 5 mốc trên (hoặc `min-width` đối xứng: `1200px` / `1024px` / `991px` / `769px` / `568px`). Không dùng `900px`, `1100px`, `600px` ad-hoc.
5. **Tailwind `sm/md/lg/xl`** vẫn dùng được khi khớp gần mốc (`md≈768`, `lg≈1024`); với `990` / `1199` / `567` ưu tiên CSS variable + `@media (max-width: …)` trong `app.css`.

### 3.3 Spacing scale (responsive)

**Gutter khung (bắt buộc — `.container-site` + khung lớn):**

| Token | ≥1200 | ≤1199 | ≤1023 | ≤990 | ≤768 | ≤567 |
|---|---|---|---|---|---|---|
| `--space-gutter` | **2rem** | **1.25rem** | **1rem** | **1rem** | **0.75rem** | **0.75rem** |

**Gap / margin / card (đồng bộ qua utility `.site-gap*`, `.site-stack*`, `.site-mt*`, `.site-pad*`):**

| Token | ≥1200 | ≤1199 | ≤1023 | ≤990 | ≤768 | ≤567 | Dùng cho |
|---|---|---|---|---|---|---|---|
| `--space-gap-sm` | 0.75rem | 0.7rem | 0.65rem | 0.6rem | 0.5rem | 0.5rem | Chip, meta row, icon+text |
| `--space-gap` | 1.25rem | 1.1rem | 1rem | 0.95rem | 0.85rem | 0.75rem | Grid card mặc định |
| `--space-gap-lg` | 2rem | 1.75rem | 1.5rem | 1.35rem | 1.15rem | 1rem | Layout 2 cột, footer grid |
| `--space-stack` | 1.5rem | 1.25rem | 1.15rem | 1.1rem | 1rem | 0.85rem | Khối xếp dọc trong section |
| `--space-stack-lg` | 2.5rem | 2.15rem | 1.85rem | 1.7rem | 1.5rem | 1.25rem | Khoảng giữa các cụm lớn |
| `--space-heading-mb` | 2.25rem | 2rem | 1.75rem | 1.6rem | 1.35rem | 1.15rem | `.section-heading` → nội dung bên dưới |
| `--space-section-y` | 4.5rem | 4rem | 3.25rem | 2.85rem | 2.15rem | 1.75rem | `.section-band` |
| `--space-section-y-sm` | 3rem | 2.75rem | 2.15rem | 1.85rem | 1.45rem | 1.2rem | `.section-band--sm` |
| `--space-section-follow` | — | — | 0.45 | 0.42 | 0.38 | 0.35 | *(deprecated — dùng §3.5 padding-top: 0)* |
| `--space-card` | 1.5rem | 1.25rem | 1.15rem | 1.1rem | 1rem | 0.85rem | Padding trong card |
| `--space-card-stack` | 0.75rem | 0.7rem | 0.65rem | 0.6rem | 0.55rem | 0.5rem | Gap giữa phần tử **bên trong** card (title → meta → body) |
| `--space-card-stack-lg` | 1rem | 0.9rem | 0.85rem | 0.8rem | 0.75rem | 0.65rem | Footer card, CTA row, khối highlights |

**Class dùng chung cho card inner:** `.card-body` (padding), `.card-inner` (stack dọc), `.card-meta-row`, `.card-footer` / `.card-footer-row`. Tour/blog/review/team/testimonial grid **bắt buộc** dùng các class này thay `p-5` / `mt-3` / `pt-4` ad-hoc.

**Quy tắc:** ưu tiên `.site-gap` / `.site-gap-lg` / `.site-stack` thay cho `gap-8` / `gap-6` / `space-y-*` ad-hoc trên layout trang. Chỉ giữ Tailwind gap cứng cho khoảng micro (≤0.5rem) trong chrome UI.

### 3.4 Typography scale (responsive)

Base `html` font-size cascade theo breakpoint (giữ dễ đọc, tránh nhảy quá mạnh):

| | ≥1200 | ≤1199 | ≤1023 | ≤990 | ≤768 | ≤567 |
|---|---|---|---|---|---|---|
| `html` | 106.25% (~17px) | 106.25% | 103% | 100% | 100% | 100% |
| `--fs-body` / `text-base` | **1rem** (~17px) | 0.9875rem | 0.96875rem | **0.9375rem** (15px) | 0.90625rem | **0.875rem** (14px) |
| `.listing-toolbar__count-num` | 1.375rem | 1.3125rem | 1.25rem | 1.1875rem | 1.125rem | 1.0625rem |
| `--fs-meta` / `text-sm` | 0.875rem | 0.859rem | 0.844rem | 0.8125rem | 0.797rem | 0.781rem |
| `--fs-kicker` / `.kicker` | 0.75rem | 0.734rem | 0.719rem | 0.6875rem | 0.672rem | 0.656rem |
| `--btn-pad-x` / `--btn-pad-y` | 1.75/0.75rem | ↓ | ↓ | ↓ | 1.35/0.6rem | 1.2/0.55rem |
| `--btn-pad-x-sm` / `--btn-pad-y-sm` | 1.25/0.5rem | ↓ | ↓ | ↓ | 0.95/0.42rem | 0.85/0.4rem |
| `--lh-body` | 1.75 | 1.75 | 1.72 | 1.68 | 1.65 | 1.62 |
| `--fs-section-title` | ~2.25–2.5rem | clamp xuống | ↓ | ↓ | ~1.75rem | ~1.6rem |
| `--fs-detail-title` | clamp 1.35–1.625rem | ↓ | ↓ | ↓ | 1.35rem | 1.25rem | H2 section trong trang chi tiết (tabs, FAQ, lịch trình) |
| `--fs-detail-price` | clamp 1.4–1.75rem | ↓ | ↓ | ↓ | 1.4rem | 1.3rem | Giá sidebar booking (Fraunces) |
| `--fs-detail-price-soft` | clamp 1.125–1.35rem | — | — | — | — | 1.125rem | Giá mềm / liên hệ khi chưa có số |
| `.section-eyebrow` | ~1.875–2rem | ↓ | ↓ | ↓ | ~1.35rem | ~1.25rem |
| Hero H1 | clamp lớn | thu nhẹ | thu | thu | mobile clamp | compact |

Body dùng `.body-text` hoặc `text-base` — **cả hai** map `--fs-body` theo breakpoint (desktop giữ 1rem, mobile thu nhẹ để cân với heading/card đã scale). **Không** hạ xuống `text-sm` cho nội dung đọc chính.

### 3.5 Section liền nhau

Section cùng loại (`.section-band`, `.section-band--sm`, `.vt-dest`, `.vt-videos`) **liền nhau** tự động **bỏ `padding-top`** ở section sau — khoảng cách giữa hai block = `padding-bottom` của section trước (một lần `--space-section-y`), **mọi breakpoint**.

Rule đặt **unlayered** (sau định nghĩa `.vt-dest` / `.vt-videos`), specificity ≥ `(0,2,0)` — **không** dùng `:where()` (specificity 0 sẽ thua `padding-block` của `.vt-dest`).

- Không gắn `py-*` / `pt-*` ad-hoc lên section Home (vd. `py-4` từng làm company intro dính tour nổi bật).
- Cần **tách rộng hơn**: thêm `.section-band--spaced-top` lên section sau.
- Media query chỉ được đổi `padding-bottom` của `.vt-videos` / `.vt-dest` — tránh `padding-block` shorthand ghi đè `padding-top: 0`.

### 3.6 Card inner & grid con (đã triển khai)

**Quy tắc:** mọi card trong grid (tour, blog, team, review, testimonial, about…) **không** dùng `p-5`, `mt-3`, `space-y-6` ad-hoc — dùng class/token below.

| Thành phần | Class Blade | Token / CSS |
|---|---|---|
| Padding card | `.card-body` / `.site-pad` | `--space-card` |
| Stack title → meta → body | `.card-inner` | `--space-card-stack` |
| Hàng rating + badge | `.card-meta-row` | `--space-gap-sm`, `--space-gap` |
| Footer card / CTA row | `.card-footer`, `.card-footer-row` | `--space-card-stack-lg` |
| Grid gap | `.site-gap`, `.site-gap-lg` | `--space-gap*` |
| Stack section con | `.site-stack`, `.site-stack-lg` | `--space-stack*` |

**Tour card** (`x-tour.card`, `x-tour.card-compact`): `.tour-card-title`, `.tour-card-places`, `.tour-card-quote`, `.tour-card-route`, `.tour-card-media`, `.tour-card-highlights`, `.tour-card-badge`, `.tour-card-duration` (meta), `.tour-card-price` (footer) — font theo `--fs-tour-*`.

**USP** (`x-shared.usp-badges`): seed lưu title **chữ thường**; hiển thị `.usp-item__title { text-transform: capitalize }`.

**Team grid**: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`; `.team-card` + gap avatar↔copy.

**Review platforms**: `.review-card .card-inner { align-items: center }` — sao căn giữa.

**Testimonial carousel**: `.snap-carousel__item` — `calc((100% - var(--space-gap) * n) / m)` theo breakpoint.

**Video showcase** (`.vt-videos`): `padding-block: var(--space-section-y)`; caption/play scale ≤567px.

**Destinations** (`.vt-dest`): grid portrait gap `--space-gap-sm` → `--space-gap-lg`; copy padding `--space-card-stack*`.

### 3.7 Quick inquiry teaser (padding 2 tầng)

`.qi-teaser` dùng 2 lớp inset:

| Token | Vai trò |
|---|---|
| `--qi-accent-inset` | Mép box → đường góc trang trí |
| `--qi-content-gap` | Đường góc → nội dung chữ |
| `--qi-pad` | `inset + gap` — padding thực của teaser |

Đường góc: `.qi-teaser__accent::before/after` — top-left & bottom-right. Modal: `.vt-letter--modal` (padding `--space-card`).

### 3.8 Footer & header chrome

**Footer contact strip:** `.footer-contact__brand-row`, `__channels`, `.footer-office`, `__qr` — typography `--fs-body`, `--fs-meta`, `--fs-footer-brand`. `--footer-contact-pad-top` cho overlap quick inquiry.

**Footer main:** `.footer-nav__link`, `.footer-seo`, `.footer-copyright` (`--fs-meta`), `.footer-social__link`, `.footer-badge` (`--fs-kicker`).

**Header:** `.header-wordmark__tagline` — **ẩn** `@media (max-width: 567px)`.
**Header more drawer:** `.header-more-btn` (icon list, `aria-label="Thêm mục"`) mở `.header-more-panel` — chứa Cẩm nang, Video, Gallery, Về chúng tôi, Liên hệ…; đóng khi hover rời / click outside. Nav chính `headerMain` giữ Điểm đến, Du thuyền, **5 cụm dịch vụ** (mega menu `.nav-panel-*`).

### 3.9 Hero slider (responsive)

- `.hero-slider-stage` — height theo breakpoint
- `.hero-slide-copy` — padding trái/phải tránh nút nav; `max-line-1/2` cho title/desc
- Text shadow tăng contrast trên mọi breakpoint

### 3.10 Checklist khi build / rà soát trang mới

1. Section wrapper: `.section-band` hoặc `.section-band--sm` + `.container-site`
2. Grid: `.site-gap` / `.site-gap-lg` — không `gap-6`, `gap-8`
3. Stack dọc: `.site-stack` — không `space-y-*` (trừ micro ≤0.5rem)
4. Card nội dung: `.card-body` + `.card-inner`
5. Typography body: `.body-text`; meta: `.text-sm` (map `--fs-meta`)
6. CTA: `.btn-*` — không override padding/font
7. Section heading: `x-shared.section-heading` (margin `--space-heading-mb`)
8. Media query custom chỉ tại 5 mốc §3.2
9. Section liền nhau: CSS §3.5 tự bỏ `padding-top` follower — chỉ thêm `.section-band--spaced-top` khi cần tách rộng

### 3.11 Trang Về chúng tôi — mapping component

| Block | Component / class | Ghi chú |
|---|---|---|
| Page header | `x-layout.page-header` | Token page-header__* |
| Company intro | `x-shared.company-intro` | `.company-intro__cta`, `__license` |
| Team | `x-shared.team-grid` | Token — spacing liền intro do §3.5 |
| Sứ mệnh / Tầm nhìn | `.signpost-card` ×2 | Grid `site-gap lg:grid-cols-2` |
| Giá trị cốt lõi | `.values-diagram` | Hub + 4 nhãn; side stacks `.site-stack-lg` |
| Chính sách bán hàng | `.card` + `.site-pad` | CTA `.btn-ghost` + `.site-mt` |
| Vì sao chọn | `.reason-list` | Ảnh + `.section-title` + numbered items |
| USP / Review / Testimonial | shared components | USP + review + carousel |
| Đại diện nước ngoài | `.ref-person-card` | Grid `site-gap sm:2 lg:3` |
| Video | `x-shared.video-showcase` | Token vt-videos |

### 3.12 Nhật ký tối ưu responsive (2026-07)

Tài liệu này ghi lại **toàn bộ** thay đổi responsive đã triển khai — từ token toàn site đến cấp con trong card/grid — để rà soát trang mới không lặp lại hardcode cũ.

#### A. Hệ token & breakpoint (`resources/css/app.css`)

| Hạng mục | Chi tiết |
|---|---|
| Breakpoint | 5 mốc `max-width`: **1199 / 1023 / 990 / 768 / 567** — cascade `:root` variables |
| Gutter | `--space-gutter` trên `.container-site` |
| Section | `--space-section-y`, `--space-section-y-sm`; follower liền nhau → `padding-top: 0` (§3.5) |
| Layout gap | `--space-gap`, `--space-gap-sm`, `--space-gap-lg` → utility `.site-gap*` |
| Stack | `--space-stack`, `--space-stack-lg` → `.site-stack*` |
| Margin block | `.site-mt`, `.site-mt-lg`, `.site-mb` |
| Padding khung | `.site-pad`, `.site-pad-x`, `.site-pad-y` |
| Card inner | `--space-card`, `--space-card-stack`, `--space-card-stack-lg` → `.card-body`, `.card-inner`, `.card-footer*` |
| Typography | `--fs-body`, `--fs-meta`, `--fs-kicker`, `--fs-item-title`, `--fs-tour-*`, `--fs-detail-title`, `--fs-detail-price*`, `--fs-footer-brand` — override `.text-base`, `.text-sm`, `.body-text` |
| Buttons | `--btn-pad-*`, `--btn-gap`, `--space-hit` (min-height ≥44px mobile) |
| Quick inquiry | `--qi-accent-inset`, `--qi-content-gap`, `--qi-pad` (padding 2 tầng) |
| Footer | `--footer-contact-pad-top`, class `.footer-contact__*`, `.footer-nav__*`, `.footer-copyright` |

#### B. Layout & chrome toàn site

| Thành phần | Thay đổi |
|---|---|
| `.section-band` | Padding block theo token; section liền nhau auto `padding-top: 0` (§3.5) |
| Header | `.header-wordmark__tagline` ẩn ≤567px |
| Footer contact strip | Token padding + typography từng hàng (brand, channels, office, QR) |
| Footer nav / SEO / copyright | `.footer-nav__link`, `.footer-seo`, `.footer-copyright` (`--fs-meta`) |
| Hero slider | `.hero-slider-stage`, `.hero-slide-copy` — height/padding theo breakpoint |

#### C. Grid & card dùng chung (cấp con)

| Component | File | Token / class áp dụng |
|---|---|---|
| Tour card (listing) | `x-tour.card` | `.tour-card-*`, `.card-body`, `.card-inner`, `.card-footer-row` |
| Tour card (compact) | `x-tour.card-compact` | Cùng pattern, media + highlights accordion |
| Blog card | `x-shared.blog/card` | `.card-body`, `.blog-card-meta` |
| USP badges | `x-shared.usp-badges` | `.usp-item`, `.usp-item__title { capitalize }` — seed title chữ thường |
| Team grid | `x-shared.team-grid` | `grid-cols-1 sm:2 lg:4`, `.team-card`, gap avatar↔tên |
| Review platforms | `x-shared.review-platforms` | `.review-card`, stars **căn giữa** (`.card-inner { align-items: center }`) |
| Testimonial carousel | `x-shared.testimonial-carousel` | `.snap-carousel__item` width theo `--space-gap` |
| Destinations bento | `components/home/destinations` | `.vt-dest` — gap + copy padding token |
| Video showcase | `x-shared.video-showcase` | `.vt-videos` — section Y, caption/play scale ≤567px |
| Company intro | `x-shared.company-intro` | `.site-pad`, `.company-intro__cta`, `.company-intro__license*` |

#### D. Trang danh mục & chi tiết — rà soát hoàn tất

- **Listing:** `tours/index`, `cruises/index` — §3.13
- **Detail:** `x-tour.detail` — `.detail-*` toàn bộ section (gallery, tabs, itinerary, sidebar booking)
- **Blog:** `guide/index`, `guide/show`, `x-blog.sidebar`, `x-blog.card`
- **Form:** `contact`, `customize-tour`, `x-form.stepper`, `x-form.checkbox-pill`
- **Khác:** `search`, `gallery`, `reviews`; `videos`/`team` qua shared components
- **Home / About:** đã token trước đó (§3.11 + components dùng chung)

#### E. Trang Về chúng tôi — rà soát hoàn tất (`about.blade.php`)

| Block | Trước (hardcode) | Sau (token/class) |
|---|---|---|
| Company intro license | `mt-6 pt-5 text-sm text-base sm:text-lg` | `.company-intro__license*` |
| Team | — | Spacing liền intro/sứ mệnh do §3.5 |
| Sứ mệnh / Tầm nhìn | `p-7`, `text-xl`, `mt-3`, `sm:grid-cols-[200px_1fr]` | `.signpost-card`, `.signpost-card__*` |
| Giá trị cốt lõi | `space-y-8`, `size-64/72`, `text-xs/sm/base` | `.values-diagram`, `.values-diagram__*` |
| Chính sách | `mt-4`, `mt-5`, `lg:grid-cols-[1fr_320px]` | `.site-pad`, `.about-policy__lead/cta`, `minmax(0,20rem)` |
| Vì sao chọn | `mt-6 space-y-5`, `size-9`, `mt-7`, `h-80` | `.reason-list*`, `.about-mockup`, `.reason-list__cta` |
| USP / Review / Testimonial | shared components | Spacing liền nhau do §3.5 |
| Đại diện nước ngoài | `p-7`, `mt-4`, `space-y-2.5`, `text-xs/base` | `.ref-person-card*` |

#### F. Quy trình rà soát trang tiếp theo

1. Mở Blade → grep `p-[567]`, `mt-[34567]`, `space-y-[567]`, `gap-[678]`, `text-xl` ad-hoc
2. Map vào bảng §3.3–3.4 hoặc tạo class component mới trong `app.css` (không inline Tailwind spacing lặp lại)
3. Section liền nhau: §3.5 tự xử lý — dùng `.section-band--spaced-top` nếu cần tách rộng
4. Chạy `npm run build` + kiểm visual tại 5 breakpoint

**Nguồn triển khai:** `resources/css/app.css` (`:root` + `@layer components`); Blade trong `resources/views/components/` và `resources/views/pages/`.

### 3.13 Trang danh mục Tour / Du thuyền / Dịch vụ — mapping component

Chrome dùng chung: `App\Support\ListingChrome` → `partials/listing-catalog.blade.php` (hub / country / chủ đề tour / cruise type / service hub|category).

| Block | Component / class | Ghi chú |
|---|---|---|
| Page header | `x-layout.page-header` | Token page-header; `title` / `subtitle` / `banner` |
| Layout 2 cột | `.listing-layout` | Filter trái ~17.5rem + list phải; mobile stack |
| Bộ lọc | `x-tour.filter-sidebar` | `.filter-sidebar__*` — drawer ≤1023, cột ≥1024 |
| Toolbar | `.listing-toolbar` | Count (số Fraunces brand + nhãn body) `align-items: flex-end` + `sort-dropdown` |
| Danh sách | `x-tour.card` / `x-service.card` | `.tour-card-*`, `.card-body`, `.card-inner` — đã token |
| Empty state | `.listing-empty` | Padding `--space-stack-lg` |
| Rating tổng (tour) | `.listing-rating-summary` | Score clamp responsive |
| SEO prose | `.prose-travel.listing-seo` | `seoBody` từ ListingChrome |
| FAQ | `x-shared.faq.listing-faq` | `.faq-list`, `.faq-item__*` |
| Cruise pills | `.cruise-type-nav` | `btn-chip` chuyển tuyến (nếu còn dùng ngoài chrome) |

**Không dùng:** badge pill “Top N — 2026” / đếm sản phẩm cạnh sort — toolbar chỉ giữ sắp xếp.

### 3.14 Các trang còn lại — mapping component

| Trang | File / component | Class chính |
|---|---|---|
| Chi tiết tour/cruise | `x-tour.detail` | `.detail-*`, `.detail-sidebar__*`, `.cabin-card__body` |
| Blog listing | `guide/index` | `.blog-layout`, `.blog-toolbar`, `.blog-seo`, `.page-follow` |
| Blog chi tiết | `guide/show` | `.blog-article-gallery`, `.blog-share-bar`, `.comment-*`, `.blog-inline-links`, `.article-toc*` (TOC đầu bài + FAB/drawer) |
| Blog sidebar | `x-blog.sidebar` | `.blog-sidebar__card` / `__title` / `__nav-link` / `__tag` |
| Blog TOC | `x-blog.toc` | Mục lục đầu bài (đóng/mở) + FAB trái dưới + drawer — không sticky sidebar |
| Liên hệ | `contact.blade.php` | `.contact-page`, `.contact-layout`, `.office-card__*` |
| Tour riêng | `customize-tour.blade.php` | `.customize-form`, `.form-section__*`, `.form-grid`, `.form-pills` |
| Form controls | `x-form.stepper`, `x-form.checkbox-pill`, `x-form.check` | `.form-stepper__*`, `.form-pill__label`, `.vt-check*` |
| Tìm kiếm | `search.blade.php` | `.search-section`, `.search-dest-card`, `.listing-empty` |
| Gallery | `gallery.blade.php` | `.gallery-card__*` |
| Reviews | `reviews.blade.php` | `.reviews-summary`, `.review-masonry-card` |
| Videos / Team | `videos`, `team` | Dùng `page-header` + shared components đã token |
| Home | `home.blade.php` | Đã token từ các component dùng chung |
| About | `about.blade.php` | §3.11 |
| Danh mục tour/cruise/service | `tours/{hub,index,category}`, `cruises/{hub,index}`, `services/{hub,index}` → `partials/listing-catalog` | §3.13 |

Utility chung: `.page-follow` (margin-top section phụ), `.form-success` (trạng thái gửi form thành công), `.listing-empty`.

## 4. Component Inventory (đã bổ sung component mới phát hiện từ ảnh)

| Component | Mô tả | Dùng ở trang |
|---|---|---|
| `Header` (mega menu) | Logo, nav Điểm đến/Du thuyền/**5 cụm dịch vụ**, drawer Thêm (Cẩm nang/About/Contact), CTA Tour riêng | Toàn site |
| `HeroWithQuickPills` | Ảnh nền hero + hàng pill chọn nhanh danh mục tour đặt sát mép trên | Home |
| `HeroSearchBox` | Card trắng nổi đè lên hero: dropdown Destinazione + Durata + nút Cerca | Home |
| `USPBadgeGroup` | 4 badge icon+text ngang hàng | Home, About Us |
| `PageHeaderCard` | **Card breadcrumb + H1 đè lên banner** — component dùng chung cho mọi trang danh mục/tĩnh | Tour Listing, Blog Listing, About Us... |
| `TourCard` (variant `listing`) | Ảnh trái + nội dung phải: badge rating tròn, badge duration, quote review, địa danh, accordion "Attrazioni principali", CTA | Tour Listing |
| `ServiceCard` (`x-service.card`) | Cùng layout card ngang listing; icon/attrs theo cụm dịch vụ | Service hub/listing |
| `ServiceDetail` (`x-service.detail`) | Detail + sidebar CTA báo giá (không cart) | Service detail |
| `TourCard` (variant `home-compact`) | Ảnh trên + nội dung dưới, gọn hơn, chỉ 3 hiển thị | Home |
| `FilterSidebar` (variant Tour) | Checkbox "Durate" + "Stile di viaggio" + nút Annulla/Applica | Tour/Cruise Listing |
| `BentoDestinationGrid` | Grid không đều (1 ô lớn + nhiều ô nhỏ), overlay tên quốc gia | Home |
| `TestimonialCarousel` | 3 card cùng lúc + nút điều hướng, mỗi card: avatar+quốc gia, rating, quote, dải ảnh +N | Dùng chung nhiều trang |
| `TestimonialLogos` | 3 logo (TripAdvisor/Google/Trustpilot) + quote + link | Dùng chung nhiều trang |
| `TeamGrid` (variant có bio) | Avatar + tên + chức danh + đoạn bio cắt "..." | Home, About Us |
| `VideoShowcase` | 1 video lớn trái + list video nhỏ phải | Home, About Us |
| `QuickInquiryForm` | Form 2 cột + 2 hình minh hoạ illustration (xe đạp, gánh hàng) | Cuối hầu hết các trang |
| `Breadcrumb` | Text gọn (`fs-meta`→`fs-kicker`), không pill nền; `.breadcrumb--page` cho khoảng → H1 | Toàn site |
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
| `ArticleTOC` | `x-blog.toc` — mục lục đầu bài (đóng/mở), FAB trái dưới + drawer khi scroll qua TOC | Blog Article |
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
