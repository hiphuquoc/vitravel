# Chi tiết khách sạn / lưu trú (hotel stay detail)

**Nguồn:** `optimize/hotel_detail_desktop.json`  
**URL mẫu:** `…/luu-tru/…/maison-hai-homestay`  
**Form factor:** desktop · Lighthouse 13

## Điểm số

| Category | Score | Ghi chú |
|----------|------:|---------|
| Performance | 76 | TTFB + ảnh gallery + font/CSS cache |
| Accessibility | 93 | aria / contrast |
| Best Practices | 96 | GCS cookie noise |
| SEO | 92 | meta description trống (đã vá code) |

### Metrics chính

| Metric | Giá trị | Ý nghĩa |
|--------|---------|---------|
| TTFB | ~1.43s | **Nút thắt lớn nhất** — HTML cache / query / CDN |
| LCP | ~1.4s | Cover gallery; bị TTFB + tải ảnh GCS |
| SI | ~2.2s | Font/CSS + ảnh phụ |
| TBT | ~310ms | Có nhiễu extension; JS site ~26 KiB |
| CLS | 0.08 | Cần `sizes` / kích thước ảnh ổn định |

---

## Đã xử lý trong code (pass này)

| Vấn đề Lighthouse | Thay đổi |
|-------------------|----------|
| SEO thiếu meta description (stay `summary` bị xóa trong payload) | `pages/services/show.blade.php` + `seoDescription` trên `mapService` |
| Thumb gallery tải ảnh quá lớn (`card`/`lg`) | `mapGalleryAttachments` thêm `thumb`; `detail-gallery` dùng thumb + `sizes` |
| JSON Alpine lightbox nhồi cả gallery (HTML ~606 KiB) | Seed tối đa `config('stay.detail_lightbox_seed')` (mặc định 20) |
| `aria-label` trên `<span>` sao | `stars.blade.php` thêm `role="img"` |
| `label` / thumb `aria-label` mismatch | Thumb button: aria-label khớp ngữ cảnh tiêu đề |
| Contrast breadcrumb / rating-count / stay facts / tagline | Đổi `muted` → `ink-soft` ở các chỗ LH báo |
| Cache `/build` ngắn / font TTL 0 (mẫu Nginx) | `docs/deploy/nginx-site.conf.example` → `1y immutable` |
| Font accent thừa weight | `vite.config.js`: Dancing Script chỉ weight 600 |

---

## Chưa xử lý hết bằng app code — phương án

### 1. TTFB ~1.4s (server / cache)

**Không phải lỗi Blade.** HTML đã có `RendersWithHtmlCache` nhưng vẫn chậm nếu:

- Cache miss (TTL ngắn, key lệch locale/currency, purge thường xuyên)
- Origin PHP lạnh / DB chậm khi miss
- Không có CDN edge cache HTML

**Phương án:**

1. Xác nhận cache hit trên URL public (`HtmlCacheService` + Redis/file).
2. CDN (Cloudflare / GCS behind CDN) cache HTML GET anonymous 5–30 phút với bypass cookie session.
3. Warm cache sau deploy / crawl.
4. Tiếp tục giảm payload SSR (rooms photos, FAQ dài) — xem mục 3.

Chi tiết chung: [04-shared-infrastructure.md](./04-shared-infrastructure.md).

### 2. Ảnh GCS gallery (~307 KiB waste)

LH: thumb hiển thị ~293×196 nhưng tải webp ~668×683.

**Code đã:** ưu tiên variant `thumb` + `sizes`.

**Vẫn cần ops/media:**

1. Re-encode / generate đủ biến thể `thumb` / `card` / `lg` trên GCS (pipeline MediaService).
2. Cache-Control GCS: nếu URL content-addressed → TTL dài (30–365 ngày), không chỉ 1 ngày.
3. Cover LCP: encode riêng ~1200–1600px, quality thấp hơn một chút, AVIF/WebP.
4. Ảnh remote Booking hotlink (chưa upload media): không có thumb thật — nên hydrate lên GCS.

### 3. HTML document lớn (~606 KiB raw)

Nguyên nhân thường gặp trên stay detail:

- Toàn bộ `rooms` + ảnh phòng trong Alpine `stayRooms`
- Gallery còn lại / attrs crawl
- FAQ + policies dài

**Phương án tiếp:**

1. API `GET /api/detail/stay/{id}/gallery?after=` — lightbox load phần còn lại khi mở drawer (thay seed 20).
2. SSR rooms: chỉ metadata giá; ảnh phòng lazy khi mở hạng phòng.
3. `content-visibility` đã có trên related; áp dụng thêm khối phòng/amenities dưới fold.

### 4. CSS render-blocking (`app-*.css` ~72 KiB transfer / ~347 KiB raw)

**Phương án:**

1. Giữ một CSS bundle hiện tại (đơn giản) nhưng purge chặt Tailwind `@source`.
2. Tách critical CSS cho above-the-fold stay (thử nghiệm) — chi phí bảo trì cao.
3. Brotli/gzip đã có; đảm bảo Nginx `gzip_types` / Brotli cho `text/css`.

### 5. Font chain (.woff + .woff2)

Bunny fonts plugin emit cả `.woff` lẫn `.woff2`. Trình duyệt hiện đại chỉ cần woff2.

**Phương án:**

1. Kiểm tra output `@font-face` sau build — bỏ `src: url(*.woff)` nếu có thể (patch plugin / postcss).
2. Giảm weight Be Vietnam Pro nếu UI không dùng 400+700 cùng lúc trên first paint.
3. `font-display: swap` (thường plugin đã set).

### 6. Accessibility còn lại

| Issue | Hướng xử lý |
|-------|-------------|
| Region switcher / header wordmark name mismatch | Đồng bộ `aria-label` với text visible hoặc bỏ label thừa |
| Contrast giá phòng `<s>` / tax / unit | Tăng độ đậm màu gạch giá trong `stay-detail.css` |
| Cookie GCS third-party | Bỏ cookie trên object public; dùng CDN riêng domain |

### 7. Bỏ qua khi đọc báo cáo

- `unused-javascript` / `unused-css-rules` từ **Chrome extensions** — không phải `app-*.js` của site.

---

## Checklist sau khi deploy

1. `npm run build` (Node 20+) + deploy `/build` + **reload Nginx** với Cache-Control immutable.
2. Clear HTML page cache stay detail.
3. Lighthouse lại bằng Chrome sạch.
4. Xác nhận Network: cover LCP = variant phù hợp; thumb = `thumb` URL; `/build/*` có `immutable`.
