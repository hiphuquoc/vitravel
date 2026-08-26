# Listing / danh mục (hub + category)

Áp dụng cho tour hub/country/category, cruise hub/type, service hub/category (stay khu vực).

**Nguồn đo mới:** `optimize/hotel_category_dekstop.json` (typo tên file; URL `…/luu-tru`) · desktop · Lighthouse 13.

## Điểm số tham chiếu (stay hub `/luu-tru`)

| Category | Score |
|----------|------:|
| Performance | 83 |
| Accessibility | 93 |
| Best Practices | 96 |
| SEO | 100 |

### Metrics

| Metric | Giá trị | Ghi chú |
|--------|---------|---------|
| TTFB | **~2.7s** | Nút thắt lớn nhất (cache miss / origin) |
| FCP | 0.5s | Tốt sau khi HTML về |
| LCP | 1.1s | Banner page-header (CSS bg trước đây) |
| SI | 2.4s | Kéo bởi TTFB + font/ảnh |
| TBT | 220ms | Có nhiễu extension |
| CLS | 0.094 | Main + Dancing Script |

Ignore: `unused-javascript` / `unused-css` toàn `chrome-extension://`.

---

## Đã tối ưu gần đây (code)

| Hạng mục | Ghi chú |
|----------|---------|
| SSR seed 5 card đầu (`ListingSeed`) | Giảm chờ XHR đợt 1 |
| Skeleton wide/compact đồng bộ card | Giảm CLS |
| Progressive fetch 5 → 10 → scroll | Không chặn chrome |
| `lockedFilters` (country/category/type) | Filter/sort không “gãy” context trang |
| Sort dropdown rộng về phía trong | CSS `listing-sort__select .vt-select__options` |
| Related trên detail dùng cùng `listingGrid` | Lazy IO |
| **LCP banner → `<img>` + preload** | `page-header.blade.php` (không còn chỉ CSS `background-image`) |
| **2 card seed đầu `loading=eager`** | `service.card` + `listing-cards` |
| Contrast filter count / preset sub / rating-badge | `ink-soft` + `bg-primary-600` |
| A11y: wordmark / region switcher name | aria-label khớp text visible |
| A11y: stay media pill `role="img"` | `card-media-tags` |
| Sao `role="img"` | `stars.blade.php` (pass trước) |

---

## Code có thể làm tiếp

1. **Filter duration keys** — đồng bộ key seed với `ListingController::filterByDurationAndStyle`.
2. **Prefetch** listing API khi hover filter (cân nhắc bandwidth).
3. **SEO body / FAQ** dưới fold: `content-visibility` nếu đo còn nặng.
4. Card tour wide: cùng pattern `imagePriority` nếu LCP/SI listing tour cần.

---

## Không xử lý hết bằng code — phương án

| Vấn đề | Phương án |
|--------|-----------|
| **TTFB listing HTML ~2.7s** | HTML cache hit + CDN edge; warm `/luu-tru` + category slugs; xem [04-shared-infrastructure.md](./04-shared-infrastructure.md) |
| JSON `/api/listings/*` chậm | Cache fragment Redis theo query hash; cursor pagination |
| Ảnh card GCS / Booking hotlink | Variant `card` đúng kích thước; hydrate Booking → GCS; TTL dài trên bucket |
| CSS render-blocking `app-*.css` | Chấp nhận bundle chung; critical CSS chỉ khi đo chứng minh ROI |
| Font `/build` TTL 0 | Nginx `1y immutable` (mẫu deploy) |
| CLS Dancing Script | Font chỉ dùng eyebrow/quote; cân nhắc `font-display: optional` hoặc bỏ accent trên listing |

## Đo đề xuất

- `optimize/hotel_category_dekstop.json` (hiện có)
- `optimize/listing_tour_desktop.json`
- Đo lại sau deploy + `npm run build` + clear HTML cache, **tắt extension**
