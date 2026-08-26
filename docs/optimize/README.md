# Tối ưu hiệu năng (Lighthouse) — ViTravel

Thư mục này ghi lại kết quả đo, phần **đã sửa trong code**, và phần **cần hạ tầng / media / vận hành** (không xử lý hết bằng Blade/JS).

## Nguồn đo

| File | Trang | Form factor |
|------|--------|-------------|
| [`optimize/home_desktop.json`](../../optimize/home_desktop.json) | Trang chủ | Desktop |
| [`optimize/hotel_detail_desktop.json`](../../optimize/hotel_detail_desktop.json) | Chi tiết khách sạn / lưu trú | Desktop |
| [`optimize/hotel_category_dekstop.json`](../../optimize/hotel_category_dekstop.json) | Hub lưu trú `/luu-tru` (danh mục) | Desktop |

**Lưu ý khi đo lại:** tắt Chrome extensions (Lighthouse báo “unused JS/CSS” hầu hết là `chrome-extension://…`, không phải bundle site). Khuyến nghị profile sạch hoặc `--disable-extensions`.

## Tài liệu theo loại trang

| Doc | Phạm vi |
|-----|---------|
| [05-home.md](./05-home.md) | Trang chủ — hero LCP, featured lazy, skeleton carousel |
| [01-hotel-stay-detail.md](./01-hotel-stay-detail.md) | Chi tiết lưu trú / khách sạn |
| [02-tour-cruise-detail.md](./02-tour-cruise-detail.md) | Chi tiết tour / du thuyền |
| [03-listing-catalog.md](./03-listing-catalog.md) | Hub / danh mục / filter listing |
| [04-shared-infrastructure.md](./04-shared-infrastructure.md) | TTFB, CDN, GCS, Nginx `/build`, font, HTML cache |

## Điểm số tham chiếu

### Home desktop

Xem chi tiết + checklist: [05-home.md](./05-home.md). Đo lại sau deploy để cập nhật bảng điểm.

### Hotel detail desktop

| Category | Score |
|----------|------:|
| Performance | 76 |
| Accessibility | 93 |
| Best Practices | 96 |
| SEO | 92 |

CWV nổi bật: **TTFB ~1.4s**, LCP ~1.4s, SI ~2.2s, TBT ~310ms (extension), CLS 0.08.

### Hotel category / stay hub desktop (`/luu-tru`)

| Category | Score |
|----------|------:|
| Performance | 83 |
| Accessibility | 93 |
| Best Practices | 96 |
| SEO | 100 |

CWV nổi bật: **TTFB ~2.7s** (nặng hơn detail), LCP banner page-header, SI ~2.4s, CLS ~0.09 (font accent).
