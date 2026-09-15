# ADR-001: Stay catalog canonical + project projection

## Status

Accepted (ship P0–P11 trên vitravel.dev, 2026-09-15)

## Date

2026-09-15

## Context

Crawler Booking.com ghi chỗ nghỉ vào `services` (cluster `stay`) đang **project-scoped**. Unique crawl là `(project_id, canonical_url)`. Hệ quả: `hicatba` và `hihalong` không chia khách sạn vùng chồng (Cát Bà / Hạ Long); cào trùng, GCS trùng, tag rời.

Yêu cầu: vitravel là nguồn API chỗ nghỉ dùng chung; dự án con vẫn có danh mục SEO riêng nhưng bind theo địa điểm; crawler khu vực tự đọc filter Booking thay vì tạo danh mục tay rồi dán URL nhỏ.

Ràng buộc: URL public, HTML cache, `ViewDataService`, SEO `slug_full` đang gắn `services` + `project_id`. Map engine (docs 17) cần identity nguồn + lat/lng ổn định.

## Decision

1. **Canonical catalog** (`stay_properties` + `stay_areas` + taxon) không `BelongsToProject`. Một Booking hotel = một property (`source` + `source_hotel_key`).
2. **Project projection**: `services.stay_property_id` — trang brand, SEO, featured. Facts/media/phòng đọc catalog.
3. **`stay_places` giữ nghĩa POI lân cận.** Khu vực địa lý = `stay_areas` (bảng mới).
4. **Crawler unique theo identity Booking**, không theo project.
5. **Hai luồng crawler tách:** discover filter (màn review, super admin confirm rồi mới tạo trang + list-crawl) ≠ crawler danh mục cũ trên form stay.
6. **Admin catalog** = group menu riêng `/catalog/*`, **chỉ siêu quản trị**. Gỡ crawler khỏi menu Lưu trú.
7. **~10k chỗ nghỉ cũ:** rebuild R1 offline → R2 area → R3 list-crawl filter (gắn taxon, skip hotel) → R4 improve chọn lọc. Không cào lại 10k detail trừ chỗ thiếu.
8. **Danh mục con bind area ± taxon**, sync projection — không cào lại.

Dump filter Booking là **fixture một dest**, không hardcode DOM. Policy nhóm: [`booking-filter-sidebar.md`](booking-filter-sidebar.md).

Chi tiết schema, phase, rủi ro: [`../18-stay-catalog-platform.md`](../18-stay-catalog-platform.md).

## Alternatives considered

### A. Bỏ `project_id` trên `services` stay, dùng chung mọi domain

- Pros: ít bảng mới.
- Cons: vỡ unique SEO/slug; featured/ẩn hiện không tách brand; cache HTML theo host khó; hitravel.net bị trộn IA đảo.
- Rejected.

### B. Virtual listing — category con chỉ query catalog, không tạo `services`

- Pros: không nhân hàng.
- Cons: phải viết lại `ServiceController` / SEO / cache / related / admin chi tiết; AI copy brand không có chỗ gắn; rủi ro lớn hơn benefit giai đoạn 1.
- Hoãn (Phase 8+ có thể dùng cho hub hitravel).

### C. Cào trên vitravel rồi copy row sang project con

- Pros: nhanh.
- Cons: vẫn nhân media/options; sync improve/gallery phức tạp; không phải API nguồn.
- Rejected làm đích; chỉ chấp nhận tạm nếu P4 chậm (không khuyến nghị).

### D. Dùng `countries`/`destinations` hiện tại làm geo stay

- Pros: có sẵn.
- Cons: đang `BelongsToProject` + IA tour; Cát Bà trên hicatba ≠ Hạ Long trên hihalong.
- Rejected. `stay_areas` độc lập, `country_code` ISO.

## Consequences

- Phase 1–5 có thể ship trên schema cũ (identity, rebuild 10k, area, discover+review+confirm) trước khi tách property.
- Purge phải phân tầng projection vs catalog.
- Media global-read cho stay; GCS chuyển `catalog/stays/{id}/` theo phase.
- Crawler catalog **chỉ super_admin** (`stays.catalog.*` + gate role).
- Unique crawl migration: backfill key → dedup → drop unique `(project_id, canonical_url)`.
- Discover **không** được gộp spawn list trong cùng request.
