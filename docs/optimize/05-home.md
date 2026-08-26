# Trang chủ (home)

**Nguồn:** `optimize/home_desktop.json`  
**Form factor:** desktop · Lighthouse 13

## Điểm số (trước tối ưu pass này)

| Category | Score |
|----------|------:|
| Performance | 59 |
| Accessibility | 92 |
| Best Practices | 96 |
| SEO | 100 |

| Metric | Giá trị | Ghi chú |
|--------|---------|---------|
| TTFB | ~1.23s | HTML cache / origin |
| FCP | 0.8s | |
| LCP | 1.2s | Hero (CSS bg trước đây) |
| SI | 2.7s | |
| TBT | **1.57s** | Featured XHR sớm + nhiễu extension |
| CLS | 0.085 | Skeleton grid ≠ carousel |

Ignore unused JS/CSS `chrome-extension://`.

## Kiến trúc tải (sau tối ưu)

| Vùng | Chiến lược |
|------|------------|
| Hero + USP + company intro | **SSR** — LCP hero = `<img>` slide 0 + preload |
| Tour / cruise / train featured | **Skeleton carousel + AJAX** khi gần viewport (`deferUntilVisible`) |
| Support curated | **AJAX** `/api/listings/featured-support` (không hydrate SSR nặng) |
| Destinations / videos / team / reviews | **SSR** dưới fold + `cv-auto` / lazy img |

Chrome HTML không chờ 3–4 XHR featured. Featured API dùng `mapPackageCard` / `mapService(..., false)`.

## Đã xử lý trong code

| Vấn đề | Thay đổi |
|--------|----------|
| `HomeController` gọi `featuredTours/Cruises` không dùng | Đã bỏ — giảm TTFB cache miss |
| 3 XHR featured fire ngay Alpine boot | `deferUntilVisible` + IO `rootMargin ~280px` |
| Skeleton 3-grid vs HTML carousel 12 card | Skeleton `layout=carousel` + `kind` đúng |
| Skeleton thiếu route start/end | Compact skeleton khớp `tour-card-route` |
| Support SSR `mapService` đầy đủ | Check tồn tại + AJAX card-light |
| `homeSection()` N query | `homeSections()` memo 1 lần |
| LCP hero CSS background | Slide 0 → `<picture>/<img fetchpriority=high>` |
| Featured API map nặng | `mapPackageCard` / `mapService(false)` |

## Phân luồng chờ (UX)

1. **Paint ngay:** header + hero img + USP  
2. **Dưới fold gần viewport:** từng khối featured tự fetch (không block first paint)  
3. **Skeleton:** snap-carousel peeks gần layout thật → ít CLS khi HTML tới  

## Không xử lý hết bằng code

| Vấn đề | Phương án |
|--------|-----------|
| TTFB HTML home | HTML cache hit + CDN — [04-shared-infrastructure.md](./04-shared-infrastructure.md) |
| Hero / dest ảnh GCS lớn | Variant đúng kích thước; TTL bucket |
| Font `/build` TTL 0 | Nginx immutable |
| Videos / testimonials SSR vẫn nặng | Có thể tách AJAX tương tự featured nếu TTFB còn cao |
| Unused JS/CSS extension | Đo lại tắt extension |

## Đo lại

1. `npm run build` (Node 20+)  
2. Clear HTML page cache  
3. Lighthouse profile sạch → cập nhật `optimize/home_desktop.json`  
4. Kiểm tra Network: featured-* chỉ khi scroll gần section  
