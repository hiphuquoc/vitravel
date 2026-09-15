# Booking.com filter sidebar — tài liệu cho crawler discover

**Không phải contract DOM.** File dump [`filter-sidebar-booking.txt`](filter-sidebar-booking.txt) là **một** snapshot sidebar (listing Phú Quốc, tiếng Việt, ~181k HTML, 1 dòng). Booking đổi class hash, đổi thứ tự nhóm, ẩn/hiện nhóm theo điểm đến. Crawler **không** hardcode class CSS trong dump.

Dùng dump để:

- biết *loại* nhóm / attribute ổn định (`data-filters-group`, `data-filters-item`, `data-testid="filters-sidebar"`);
- viết fixture test parser;
- chốt **chính sách lấy / bỏ nhóm** (mục 4).

Khi implement: bám attribute ổn định + `name="ht_id=204"` / `nflt`, không bám `b7ef425131` hay id React `:R11j9lvmcp:`.

---

## 1. Cấu trúc quan sát được trên dump

Container:

```html
<div data-testid="filters-sidebar" role="region" aria-label="Bộ lọc">
  <h2>Chọn lọc theo:</h2>
  <!-- lặp fieldset -->
  <div data-filters-group="{group_key}" data-testid="filters-group">
    <legend><span>{Nhãn tiếng Việt}</span></legend>
    <div data-testid="filters-group-container">
      <div data-filters-item="{group_key}:{nflt}">
        <input type="checkbox" name="{nflt}" value="{nflt}" aria-label="{label}: {n} chỗ nghỉ">
      </div>
    </div>
  </div>
</div>
```

`{nflt}` dạng `ht_id=204`, `di=15803`, `hotelfacility=433`, `class=5`. URL listing = URL vùng + `nflt` (giữ `ss`, `dest_id`, `dest_type`, ngày crawl).

Dump này: **23 nhóm**, **~140 checkbox**. Nhóm `popular` **trùng** filter đã có ở nhóm chuyên biệt (ks, resort, mealplan, review_score, …) — không crawl lại nhóm này.

---

## 2. Bắt buộc click «Hiển thị thêm»

Sidebar **không** render hết option. Mỗi cụm có nút kiểu «Hiển thị thêm» / «Xem thêm» / «Show more» / «+N» — dump mẫu gần như chưa bung (chỉ thấy 1 lần «Xem thêm»).

Chrome `list_discover` **phải**:

1. Chờ `data-testid="filters-sidebar"`.
2. Với **từng** `data-testid="filters-group"`: lặp click nút expand trong nhóm cho đến khi hết (hoặc max N lần / không còn nút).
3. Mới scrape `data-filters-item`.
4. Ghi `debug.expand`: `{ group, clicks, items_before, items_after }` — màn review hiện «đã bung đủ?».

Không scrape 1 lần DOM lúc mới load rồi coi là đủ.

Selector expand: text/aria (vi + en), **không** class hash. Nhóm không có nút = đã đủ.

---

## 3. Nhóm trên dump ↔ nhãn VI

| `data-filters-group` | Legend (dump) | Ghi chú |
|---|---|---|
| `used_filters` | Dùng các bộ lọc cũ | Chip session; bỏ |
| `price` | Ngân sách của bạn (mỗi đêm) | Slider giá; bỏ |
| `popular` | Các bộ lọc phổ biến | Trùng nhóm khác; bỏ crawl |
| `ht_id` | Loại chỗ ở | Hotel/resort/villa/… |
| `stay_type` | (trong dump, 3 value) | Loại lưu trú; gần `ht_id` |
| `unit_config_grouped` | Phòng ngủ và phòng tắm | Search inventory; bỏ |
| `review_score` | Điểm đánh giá của khách | Facet điểm; không thành trang SEO |
| `mealplan` | Bữa ăn | Rate/meal; bỏ trang + bỏ list-crawl |
| `hotelfacility` | Tiện nghi | Beachfront, hồ bơi, … |
| `roomfacility` | Tiện nghi phòng | Chi tiết phòng; đã có lúc cào hotel |
| `class` | Xếp hạng chỗ nghỉ | 1–5 sao |
| `di` | Khu vực | Neighborhood / district dest |
| `ht_beach` | Lối ra biển | Beach access |
| `distance` | Khoảng cách từ trung tâm *{dest}* | Tương đối điểm search; bỏ |
| `fc` | Chính sách đặt phòng | Huỷ miễn phí, … |
| `tdb` | Tùy chọn giường | Twin/double; bỏ |
| `popular_activities` | Các hoạt động thú vị | Kayak, snorkel, … |
| `chaincode` | Thương hiệu | Marriott…; ồn với dest VN |
| `popular_nearby_landmarks` | Địa danh | Landmark dest_id |
| `rated_high` | Đặc điểm được đánh giá cao | Facet; bỏ trang |
| `SustainablePropertyLevelFilter` | Các chứng chỉ | Travel Sustainable |
| `accessible_facilities` | Tiện nghi người khuyết tật (chỗ nghỉ) | |
| `accessible_room_facilities` | Tiện nghi người khuyết tật (phòng) | |

Dest khác (Cát Bà, Hạ Long, Paris…) **có thể thiếu/thêm nhóm**. Parser phải: mọi `data-filters-group` lạ → vẫn lưu vào pack, gắn `policy=unknown` trên màn review, **không** im lặng bỏ, **không** tự tạo trang SEO cho unknown (mặc định). Super admin có thể tick «đưa vào crawl gắn taxon» từng nhóm unknown.

---

## 4. Chính sách lấy / không lấy (chốt cho implement)

Hai việc khác nhau:

1. **Tạo trang danh mục / taxon IA** (SEO hub catalog).
2. **List-crawl URL filter** để gắn taxon vào chỗ nghỉ (làm giàu; skip hotel đã imported).

Sau khi admin **duyệt màn review**, hệ thống mặc định tick theo bảng. Admin được bỏ tick, không được quên bung «Hiển thị thêm».

| Group | Tạo trang SEO? | List-crawl gắn taxon? | Lý do |
|---|---|---|---|
| `ht_id` | **Có** | **Có** | IA loại hình |
| `di` | **Có** (khu/phường) | **Có** | Geo con của area |
| `popular_nearby_landmarks` | **Có** nếu count ≥ ngưỡng | **Có** | Địa danh = sub-area |
| `stay_type` | Có nếu không trùng nhãn `ht_id` | **Có** | Bổ sung loại hình |
| `ht_beach` | **Có** (1 trang «Gần biển») | **Có** | IA mạnh với đảo |
| `hotelfacility` | Chỉ allowlist (beachfront, hồ bơi, spa, family…) | **Có hết item nhóm** | Làm giàu tag; không nổ 20 trang SEO |
| `class` | Không (hoặc 1 trang «5 sao» nếu product muốn) | **Có** | Facet sao |
| `popular_activities` | Không | **Có** | Tag trải nghiệm |
| `fc` | Không | Không mặc định | Chính sách rate, không identity chỗ nghỉ |
| `popular` | Không | **Không** | Trùng; tránh cào 2 lần |
| `price` | Không | **Không** | User nêu; giá thay đổi theo ngày |
| `distance` | Không | **Không** | Không phải thuộc tính KS |
| `used_filters` | Không | **Không** | Session |
| `mealplan` | Không | **Không** | Bữa ăn / rate |
| `roomfacility` | Không | **Không** | Đã lấy ở trang hotel |
| `unit_config_grouped` | Không | **Không** | Số phòng ngủ search |
| `tdb` | Không | **Không** | Kiểu giường search |
| `review_score` / `rated_high` | Không | **Không** | Điểm đã nằm trên property |
| `chaincode` | Không | Không mặc định | Thương hiệu; bật tay nếu cần |
| `accessible_*` | Không | Không mặc định | Bật tay (a11y catalog sau) |
| `SustainablePropertyLevelFilter` | Không | Không mặc định | Bật tay |
| *nhóm lạ* | Không | Không, hiện `unknown` trên review | Admin tick mới crawl |

**«Đưa hết filter»** = đưa hết item thuộc nhóm **đã include list-crawl** (cột 3 = Có), không phải 140 checkbox gồm giá/khoảng cách. Vì skip-trùng: list-crawl chủ yếu **gắn thêm danh mục/taxon**, không mở lại Chrome hotel.

Allowlist `hotelfacility` SEO trang (mã Booking đổi — map theo **label** đã chuẩn hoá, không hardcode id): bãi biển/beachfront, hồ bơi, spa, gia đình/trẻ em, view biển, miễn phí huỷ *không* thuộc facility. Id dump ví dụ `hotelfacility=433` thường là beach — chỉ dùng test Phú Quốc, **không** hardcode 433.

---

## 5. Pack discover (ổn định)

Mỗi item:

```json
{
  "group": "ht_id",
  "nflt": "ht_id=204",
  "name": "ht_id=204",
  "label": "Khách sạn",
  "count": 86,
  "filter_url": "https://www.booking.com/searchresults…&nflt=ht_id%3D204",
  "expanded": true,
  "policy": "seo_and_crawl",
  "create_page_default": true,
  "crawl_default": true
}
```

`policy` do PHP map bảng mục 4, không do Chrome. Chrome chỉ trả group/nflt/label/count/url.

---

## 6. Test

- Fixture = bản rút gọn dump (không commit 180k vào unit nếu nặng — cắt 2–3 group).
- Test: parser không phụ thuộc class hash; expand clicks trong mock; `price` → `ignore`; `ht_id` → `seo_and_crawl`; group lạ → `unknown`.
- Dest thứ hai (Cát Bà) khi có dump mới: cùng parser, nhóm có thể khác.

---

## 7. Lịch sử dump

| File | Dest (suy từ legend) | Ghi chú |
|---|---|---|
| `filter-sidebar-booking.txt` | Phú Quốc («Khoảng cách từ trung tâm Phú Quốc») | 2026-09-15; sidebar đã mở một phần |
