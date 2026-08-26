# Chi tiết tour / du thuyền

Chưa có file Lighthouse riêng trong `optimize/` — checklist dưới đây **áp dụng cùng pattern** với stay detail (gallery + HTML cache + related lazy) và những điểm đặc thù package.

## Đã có sẵn trong code (nền)

| Hạng mục | Trạng thái |
|----------|------------|
| HTML cache (`RendersWithHtmlCache`) | Có |
| Related: skeleton + AJAX lazy (`detail-related`) | Có |
| Gallery dùng `detail-gallery` chung | Có (hưởng thumb seed / sizes) |
| Reviews: `testimonials(limit: 3)` | Có |

## Code có thể làm tiếp

1. **Meta description / OG** — đảm bảo `pages/tours/show` và `cruises/show` luôn `@section('meta_description', …)` từ SEO entry (không để trống).
2. **Itinerary SSR** — lịch trình dài: collapse mặc định + `content-visibility` (đã có accordion Alpine).
3. **Price table** — bảng rộng: giữ scroll ngang; tránh hydrate Alpine thừa khi không có `priceTable`.
4. **Cover LCP** — `fetchpriority="high"` + `sizes=gallery-cover` (đã thêm ở gallery chung).

## Không xử lý hết bằng Blade — phương án

| Vấn đề | Phương án |
|--------|-----------|
| TTFB | Giống stay: CDN HTML + warm cache; xem [04-shared-infrastructure.md](./04-shared-infrastructure.md) |
| Ảnh itinerary / gallery lớn | Generate variant thumb/card; không hotlink full |
| Schema / FAQ dài trong HTML | Giữ FAQ SSR vì SEO; cân nhắc cắt FAQ > N câu sang “xem thêm” |
| Related API nặng | Đã chuyển `relatedToursForDetail` / `relatedCruisesForDetail` card-light — giữ vậy |

## Đo đề xuất

Lưu vào `optimize/`:

- `tour_detail_desktop.json`
- `cruise_detail_desktop.json`

Rồi cập nhật bảng điểm vào doc này.
