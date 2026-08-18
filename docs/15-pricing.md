# Bảng giá chi tiết (đa chiều)

> **Ngày:** 2026-08-18  
> **Phạm vi:** tour / cruise (`packages`) và mọi cụm dịch vụ (`services`).  
> **Chưa làm:** checkout, giỏ hàng, thanh toán, khóa tồn kho. Quote engine đã có để nối booking sau.

`price_from` trên package/service **giữ nguyên** — giá “từ” cho listing/sidebar. Bảng giá chi tiết là lớp riêng, 1:1 theo chương trình.

## 1. Các chiều

| Chiều | Cách lưu | Ghi chú |
|---|---|---|
| Thời gian | `price_periods.kind` = `date` \| `range` \| `year` | `starts_on` / `ends_on` luôn materialize (năm → 01/01–31/12) |
| Tuỳ chọn | `price_variants` | `source`: `custom` \| `cabin` \| `service_option` + `source_id` |
| Đối tượng khách | `price_guest_types` (theo project) | **Không** hardcode người lớn/trẻ em — admin CRUD |
| Khuyến mãi | `price_periods.is_promo` + `priority` | Promo **đè** giá gốc khi quote cùng ngày |

Ô giá: `price_rates` unique `(period_id, variant_id, guest_type_id)` — cả 3 FK bắt buộc. `compare_at_amount` = giá gạch ngang (tuỳ chọn). `min_qty` / `max_qty` dành booking sau.

Đơn vị hiển thị: `price_tables.unit` (`per_person` \| `per_room` \| `per_vehicle` \| `per_group` \| `per_unit`) — `config/pricing.php`.

## 2. Schema

```
price_guest_types ── translations
packages|services ──morph── price_tables ── variants (+ translations)
                                    └── periods ── rates
```

Morph map: `package`, `service`. Trait `HasPriceTable`. Soft-delete chương trình **không** xóa bảng giá; `forceDelete` mới cascade.

Seed đối tượng khách: `PriceGuestTypeSeeder` (sau `TaxonomySeeder` trong `project:seed`). Chỉ **insert mã còn thiếu** — không ghi đè name/age admin đã sửa. Override: seed key `price_guest_types`.

Bảng giá mẫu: `PriceTableSeeder` (sau `ServiceCatalogSeeder`). Đọc `price_table_defaults` (+ optional `tours/cruises/services[].price_table`). **Bỏ qua** package/service đã có `price_rates`. Không đụng `price_from` nếu đã có giá. Clone dự án mới: copy key `price_table_defaults` từ `seed_vitravel.php`.

Chạy độc lập (mọi project, không `migrate:fresh` / không `project:seed`):

```bash
php artisan db:seed --class=PriceGuestTypeSeeder
php artisan db:seed --class=PriceTableSeeder
```

## 3. Quote (booking-ready)

`PriceTableService::quote($priceable, $date, $variantId, $guests)`:

1. Lấy period `is_active` phủ ngày.
2. Ưu tiên period `is_promo`, rồi `priority`, rồi id.
3. Mỗi dòng khách: giá promo nếu có ô, không thì giá gốc.
4. Trả `lines[]` + `total` / `total_formatted`. Thiếu ô → `null` (không đoán).

Admin preview: `GET /api/v1/admin/packages/{id}/price-quote` (và `services`).

## 4. Admin API

Base `/api/v1/admin` + Bearer + `X-Project-Code`.

### Đối tượng khách — quyền `packages.*`

| Method | Path |
|---|---|
| GET/POST | `/price-guest-types` |
| PUT/DELETE | `/price-guest-types/{id}` |

Không xóa khi đang có `price_rates`.

### Bảng giá chương trình

Lồng `price_table` trên GET/PUT package và service, **hoặc** endpoint tách:

| Method | Path |
|---|---|
| GET/PUT | `/packages/{id}/price-table` |
| GET | `/packages/{id}/price-quote?date=&variant_id=&guests[]` |
| GET/PUT | `/services/{id}/price-table` |
| GET | `/services/{id}/price-quote` |

PUT lồng chỉ sync khi client gửi key `price_table` (form cũ không mất giá).

Payload (rút gọn):

```json
{
  "price_table": {
    "currency": "VND",
    "unit": "per_person",
    "notes": "Giá chưa gồm phụ thu lễ",
    "variants": [
      { "code": "deluxe", "name": "Cabin Deluxe", "source": "cabin", "source_id": 12 }
    ],
    "periods": [
      {
        "kind": "range",
        "starts_on": "2026-04-01",
        "ends_on": "2026-09-30",
        "label": "Mùa hè",
        "is_promo": false,
        "priority": 0,
        "rates": [
          { "variant_code": "deluxe", "guest_type_code": "adult", "amount": 3500000 }
        ]
      }
    ]
  }
}
```

GET trả thêm `guest_types`, `suggested_variants` (cabin / service option chưa gắn), `units`, `period_kinds`. Rate có thể gửi `variant_id` / `guest_type_id` hoặc `*_code`.

UI form bảng giá nằm repo **`admin.vitravel.dev`** (chưa có trong Laravel).

## 5. Public

`ViewDataService` map `priceTable` (camelCase) qua `PriceTableService::publicPayload`:

- Chỉ hiện period còn hiệu lực (`ends_on >= hôm nay`), `is_active`, có ít nhất một rate.
- Cột khách = các type thực sự xuất hiện trong rate.
- Nếu `price_from` trống: điền min rate còn hạn (listing + sidebar).

Blade: `x-shared.detail-price-table` — tab **Bảng giá** trên tour/cruise/service detail khi có dữ liệu.

## 6. Mở rộng sau

- Checkout: gọi `quote()` + snapshot `lines` vào booking (không đọc lại rate lúc thanh toán nếu giá đã lock).
- Tồn kho / allotment: bảng riêng theo `variant_id` + ngày, **không** nhồi vào `price_rates`.
- Occupancy (2 khách/phòng): thêm `price_guest_types` hoặc unit `per_room` — không đổi schema ô giá.
- Extra (phụ thu lễ, transfer): period promo hoặc variant `source=custom`.
