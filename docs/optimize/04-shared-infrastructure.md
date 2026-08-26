# Hạ tầng dùng chung (TTFB, CDN, font, `/build`, GCS)

Áp dụng mọi loại trang. Đây là phần **Lighthouse hay báo đỏ nhưng không sửa được chỉ bằng Blade**.

## 1. TTFB / server-response-time

**Triệu chứng:** hotel detail ~1.4s; **stay hub `/luu-tru` ~2.7s** (category Lighthouse).

| Việc | Owner | Ghi chú |
|------|-------|---------|
| Xác nhận `HtmlCacheService` hit rate | DevOps + BE | Log miss/hit; TTL hợp lý |
| CDN cache HTML anonymous | DevOps | Bypass khi có session cookie |
| PHP-FPM / OPcache / Redis cùng region | DevOps | Tránh cold start |
| Warm URL quan trọng sau deploy | CI / cron | Sitemap stay hub + category + tour |
| Giảm HTML bytes | BE | Seed gallery, rooms lazy — xem doc từng trang |

## 2. Nginx `/build` (Vite hashed assets)

Mẫu đã cập nhật: [`docs/deploy/nginx-site.conf.example`](../deploy/nginx-site.conf.example)

```nginx
location ^~ /build/ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
}
```

**Triệu chứng LH:** font `/build/assets/*.woff(2)` `cacheLifetimeMs=0`, CSS TTL 12h.

Sau khi sửa Nginx: reload và hard-refresh; kiểm tra Response Headers.

## 3. GCS / ảnh

| Việc | Ghi chú |
|------|---------|
| Đủ variant `thumb` / `card` / `lg` | Media pipeline; stay crawl hydrate lên GCS |
| Cache-Control object | URL có hash/version → TTL dài |
| Không serve ảnh 600–800px cho ô thumb ~300px | Browser vẫn tải full nếu `src` sai variant |
| Prefetch/preconnect | Đã có `preconnect` GCS — giữ |

## 4. Font

| Việc | Ghi chú |
|------|---------|
| Ưu tiên woff2 only | Tránh chuỗi tải `.woff` trong CSS |
| Ít weight trên first paint | Đã bỏ Dancing Script 700 |
| `font-display: swap` | Kiểm tra output bunny plugin |
| Subset vietnamese+latin | Đã cấu hình trong `vite.config.js` |

## 5. Đo Lighthouse đúng cách

1. Chrome profile không extension (hoặc CI lighthouse-ci).
2. Desktop + Mobile tách file trong `optimize/`.
3. Ghi URL, commit SHA, thời điểm vào README bảng nguồn.
4. Bỏ qua audit “unused-*\` trỏ `chrome-extension://`.

## 6. Ưu tiên ROI

1. **TTFB / HTML cache hit** (ảnh hưởng LCP/SI ngay)
2. **Ảnh đúng variant + GCS cache** (~300 KiB trên stay detail)
3. **Nginx immutable `/build`**
4. **Giảm HTML Alpine seed** (gallery/rooms)
5. CSS split / critical CSS (ROI thấp hơn, chi phí cao)
