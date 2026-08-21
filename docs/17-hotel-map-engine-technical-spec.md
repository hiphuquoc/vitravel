# HOTEL MAP ENGINE — TÀI LIỆU KỸ THUẬT TRIỂN KHAI

**Mục tiêu:** xây hệ thống bản đồ khách sạn tương tác kiểu Booking.com trên nền Laravel hiện tại, sử dụng Mapbox GL JS, tận dụng database `hotels` và crawler hiện có, nhưng kiến trúc phải đủ sạch để mở rộng sang resort, villa, tour, nhà hàng, điểm tham quan và các nguồn dữ liệu khác.

> **Chỉ dẫn quan trọng cho Cursor:** đây là tài liệu kiến trúc/đặc tả triển khai. Trước khi sửa code, hãy inspect toàn bộ codebase, migrations, models, controllers, routes, crawler/importer, jobs, queue, cấu hình cache và frontend hiện có. Không được tự ý thay thế schema hiện tại nếu chưa chứng minh cần thiết. Nếu tên bảng/cột hiện tại khác tài liệu này, tạo lớp mapping/adaptor hoặc migration tương thích và giữ backward compatibility.

---

## 1. Quyết định kiến trúc

### Stack chốt

- Backend: Laravel hiện tại.
- Database: MySQL/MariaDB hiện tại.
- Cache: Redis nếu hệ thống đã có; nếu chưa có thì thiết kế abstraction để bật Redis sau.
- Map frontend: Mapbox GL JS.
- UI hiện tại: giữ Blade/stack hiện có; dùng JavaScript module/Alpine nếu project đang dùng. Không chuyển toàn website sang React/Vue chỉ vì Map Engine.
- Rendering điểm trên map: **Mapbox Style Layers**, không tạo hàng trăm/hàng nghìn DOM `Marker`.
- Data source ban đầu: database hotel hiện tại.
- API: Laravel JSON API nội bộ cho map.
- Geospatial: latitude/longitude hiện có hoặc bổ sung `POINT`/spatial index nếu phù hợp với DB hiện tại.
- Crawler: giữ crawler hiện tại; chỉ bổ sung bước normalize/geocoding/coordinate persistence nếu thiếu.

### Lý do chọn Style Layers

Mapbox xác nhận DOM Markers phù hợp cho số lượng nhỏ và dễ custom HTML/CSS, nhưng hiệu năng giảm khi có hàng trăm marker. Style Layers được render trực tiếp trong WebGL và phù hợp với dữ liệu lớn; vector tiles phù hợp hơn nữa khi dữ liệu lên đến hàng nghìn/diện rộng.

Do đó:

- **Không**: `new mapboxgl.Marker()` cho toàn bộ khách sạn.
- **Có**: GeoJSON source + symbol/circle layers + `queryRenderedFeatures()`.
- **Chỉ dùng DOM overlay/HTML card cho hotel đang được chọn**, popup/card nổi bật hoặc UI bên ngoài map.

---

## 2. UX mục tiêu

Giao diện desktop:

```text
┌──────────────────────────┬──────────────────────────────────┐
│ HOTEL LIST               │                                  │
│                          │                                  │
│ Search / filters         │              MAP                 │
│                          │                                  │
│ Hotel A                  │       VND 1.200.000              │
│ ★ 9.3                    │             ●                    │
│ VND 1.200.000            │   VND 950K       VND 1.5M       │
│                          │                                  │
│ Hotel B                  │                ●                 │
│ ★ 9.1                    │                                  │
│ VND 950.000              │                                  │
└──────────────────────────┴──────────────────────────────────┘
```

Tương tác bắt buộc:

1. Click marker → chọn hotel.
2. Click hotel card → chọn marker tương ứng.
3. Hotel được chọn phải được highlight ở cả list và map.
4. Khi chọn hotel:
   - `easeTo`/`flyTo` hoặc pan nhẹ về hotel.
   - marker đổi trạng thái selected.
   - hiển thị mini-card/preview.
5. Nút "Xem chi tiết" → URL detail hiện có của hotel.
6. Hover card → highlight marker trên map desktop.
7. Hover marker → highlight card nếu card đang nằm trong result set.
8. Click cluster → zoom đến vùng cluster.
9. Kéo/zoom map → sau khi người dùng dừng thao tác, tải lại kết quả theo viewport.
10. Không request API liên tục trong lúc pan/zoom.
11. Filter thay đổi → list + map phải dùng cùng một query state.
12. Có nút "Tìm kiếm khu vực này" khi người dùng di chuyển map, thay vì liên tục reload kết quả nếu UX hiện tại phù hợp.
13. Mobile:
    - map full-screen hoặc gần full-screen;
    - list dạng bottom sheet;
    - nút "Bản đồ / Danh sách";
    - không để map và list cùng chiếm màn hình theo kiểu desktop.

---

## 3. Nguyên tắc dữ liệu

### 3.1 Canonical hotel

Hotel trong DB của hệ thống là entity chính.

Tọa độ phải được lưu lâu dài:

```text
latitude
longitude
```

Nếu schema/DB phù hợp, bổ sung:

```text
location POINT
```

và spatial index.

Không phụ thuộc Mapbox để lưu tọa độ.

### 3.2 Source identity

Không dùng slug hoặc tên hotel làm ID nguồn.

Nếu crawler đã có source ID, giữ:

```text
source
source_hotel_id
source_url
```

Khuyến nghị nếu schema hiện tại chưa có bảng mapping:

```text
hotel_sources
----------------
id
hotel_id
source
source_hotel_id
source_url
last_synced_at
source_status
created_at
updated_at

UNIQUE(source, source_hotel_id)
INDEX(hotel_id)
```

Nếu crawler hiện tại đã có cơ chế tương đương thì tái sử dụng, không tạo bảng trùng.

### 3.3 Static vs dynamic

Static/semi-static, phù hợp cho map:

- hotel ID
- name
- slug
- address
- city/destination
- latitude
- longitude
- star rating
- review score
- review count
- property type
- thumbnail nếu hệ thống có quyền sử dụng
- status/published

Dynamic:

- price
- availability
- room
- promotion
- cancellation
- meal plan
- check-in/check-out dependent data

Không thiết kế map database dựa vào giá cố định.

Nếu map hiển thị giá, API phải lấy `display_price` từ nguồn giá hiện tại/cache hợp lệ; nếu không có giá hợp lệ thì fallback sang trạng thái "Xem giá".

---

## 4. Database / migration

### 4.1 Không phá schema hiện tại

Cursor phải:

1. đọc migration hiện tại;
2. đọc `Hotel` model;
3. đọc crawler/importer;
4. xác định các cột đã tồn tại;
5. chỉ thêm migration cho phần thiếu.

Không đổi tên cột đang được frontend/backend dùng nếu không cần.

### 4.2 Các field tối thiểu

Canonical hotel cần có khả năng cung cấp:

```text
id
name
slug
address
city / destination
country
latitude
longitude
star_rating
review_score
review_count
property_type
status
```

Nếu đã có `latitude`/`longitude`, không tạo bản sao khác.

### 4.3 Coordinate validation

Khi crawler/import:

- latitude phải trong [-90, 90].
- longitude phải trong [-180, 180].
- reject/null nếu invalid.
- không tự động ghi tọa độ 0,0.
- đánh dấu `coordinate_status` nếu hệ thống cần audit.
- lưu nguồn tọa độ nếu có thể: `source`, `geocoded`, `manual`, etc.

### 4.4 Spatial field

Nếu MySQL/MariaDB version và schema hiện tại cho phép:

```text
location POINT SRID 4326
```

và index phù hợp.

Nếu việc thêm POINT gây rủi ro tương thích với DB hiện tại, v1 có thể dùng composite/index trên latitude/longitude và triển khai abstraction `GeoQueryService`; sau đó chuyển sang spatial query mà không đổi API.

---

## 5. Mapbox architecture

### 5.1 Map instance

Tạo một module duy nhất:

```text
resources/js/map/
    HotelMap.js
    MapDataSource.js
    MapInteractions.js
    MapState.js
    MapMarkers.js
    MapFilters.js
    MapApi.js
```

Nếu project đang có convention khác, follow convention hiện tại.

Không để logic Mapbox rải rác trong Blade template.

### 5.2 Map source

V1:

```javascript
map.addSource('hotels', {
    type: 'geojson',
    data: emptyFeatureCollection,
    cluster: true,
    clusterMaxZoom: ...,
    clusterRadius: ...
});
```

Nhưng không hard-code dữ liệu khách sạn trong HTML.

API Laravel trả GeoJSON hoặc JSON rồi frontend chuyển thành GeoJSON.

### 5.3 Layers

Tối thiểu:

```text
hotel-clusters
hotel-cluster-count
hotel-points
hotel-price-label
hotel-selected
```

Có thể thêm:

```text
hotel-hover
hotel-favorite
hotel-unavailable
```

### 5.4 Price marker

Marker kiểu Booking phải là **symbol layer**, không phải DOM element cho từng hotel.

Dữ liệu feature:

```json
{
  "type": "Feature",
  "id": 123,
  "geometry": {
    "type": "Point",
    "coordinates": [103.958421, 10.194832]
  },
  "properties": {
    "hotel_id": 123,
    "name": "Example Resort",
    "price": 1200000,
    "display_price": "1,200,000đ",
    "rating": 9.3
  }
}
```

Style layer dùng `text-field`/expression để render giá.

Không đưa description, facilities, HTML hoặc dữ liệu lớn vào properties của map.

Chỉ đưa dữ liệu cần render/interact.

---

## 6. Clustering

Dùng native clustering của Mapbox cho GeoJSON ở giai đoạn API/GeoJSON.

Cluster:

```text
cluster: true
clusterMaxZoom
clusterRadius
```

Các layer:

```text
cluster circles
cluster count
unclustered hotel price
```

Click cluster:

1. lấy `cluster_id`;
2. gọi `getClusterExpansionZoom`;
3. `easeTo`/`flyTo` đến cluster;
4. không tải toàn bộ hotel của cluster về frontend chỉ để tính zoom.

Cluster styling phải data-driven theo số lượng:

```text
1–9
10–49
50–199
200+
```

Ngưỡng có thể cấu hình.

---

## 7. Khi nào dùng API GeoJSON, khi nào chuyển Vector Tiles

### V1 — GeoJSON API

Phù hợp khi:

- dữ liệu map theo viewport đã được giới hạn;
- mỗi request chỉ trả vài trăm đến vài nghìn features;
- cần filter động theo giá/rating/property type;
- muốn triển khai nhanh trên Laravel hiện tại.

Endpoint:

```http
GET /api/hotels/map
```

### V2 — Vector Tiles

Khi có:

- hàng trăm nghìn/millions hotel/POI;
- phạm vi toàn quốc/toàn cầu;
- lượng concurrent users lớn;
- GeoJSON payload bắt đầu lớn;
- map pan/zoom liên tục tạo áp lực lên Laravel.

Khi đó chuyển sang:

```text
Vector Tile source
z/x/y
```

và để Mapbox GL JS tải tile theo viewport.

Quan trọng: API contract phía UI vẫn giữ abstraction `MapDataSource`, để frontend không phụ thuộc việc backend đang dùng GeoJSON hay vector tiles.

---

## 8. API contract

### 8.1 Endpoint

```http
GET /api/hotels/map
```

Query:

```text
north
south
east
west
zoom

q
destination
min_price
max_price
min_rating
min_stars
property_types[]
amenities[]
sort
```

Có thể dùng:

```text
bounds=north,south,east,west
```

nhưng backend phải parse/validate chặt chẽ.

### 8.2 Response

Khuyến nghị:

```json
{
  "data": {
    "type": "FeatureCollection",
    "features": []
  },
  "meta": {
    "count": 123,
    "has_more": false,
    "zoom": 12
  }
}
```

Mỗi feature chỉ chứa field map cần thiết.

### 8.3 Hard limits

Backend phải giới hạn:

```text
max viewport area
max features
max bounds span
max query execution time
```

Không cho client request toàn thế giới với 500.000 records.

Nếu viewport quá lớn:

- trả cluster/summary;
- hoặc yêu cầu zoom in;
- hoặc dùng vector tile pipeline.

---

## 9. Query strategy

Không dùng:

```sql
SELECT * FROM hotels
```

Map query phải là projection tối thiểu:

```text
id
name
slug
latitude
longitude
price/display_price
review_score
star_rating
thumbnail nhỏ nếu cần
```

Filter theo:

1. published/active;
2. valid coordinates;
3. viewport;
4. destination;
5. price;
6. rating;
7. property type;
8. amenities.

### Viewport query

V1 có thể dùng:

```text
latitude BETWEEN south AND north
longitude BETWEEN west AND east
```

với index phù hợp.

Nếu dùng spatial:

```text
ST_Contains / ST_Within / ST_Distance...
```

tùy DB/version và benchmark thực tế.

Không đoán hiệu năng; benchmark trên dữ liệu thật.

---

## 10. Cache

Redis/cache là lớp quan trọng.

Cache key phải bao gồm:

```text
map version
bounds normalized
zoom bucket
filters hash
language
currency
price mode
```

Ví dụ khái niệm:

```text
hotel-map:v1:{hash}
```

Không cache raw request string không chuẩn hóa vì cùng một viewport có thể tạo nhiều key khác nhau.

### Bounds normalization

Làm tròn tọa độ trước khi tạo cache key, ví dụ theo zoom.

Mục tiêu:

```text
request A
north=10.123456

request B
north=10.123459
```

có thể dùng cùng cache bucket nếu sai số UI không đáng kể.

### Cache TTL

- static hotel map: vài phút đến hàng chục phút tùy dữ liệu;
- dynamic price: TTL ngắn hơn;
- metadata/destination: dài hơn.

Cache phải có version để invalidate khi crawler cập nhật hotel.

---

## 11. Không request khi người dùng đang kéo map

Frontend event flow:

```text
movestart
   ↓
set isMoving=true

move
   ↓
NO API request

moveend
   ↓
debounce
   ↓
build bounds
   ↓
GET /api/hotels/map
```

Debounce khoảng 250–500ms, benchmark và điều chỉnh.

Nếu UX có nút:

```text
[Tìm kiếm khu vực này]
```

thì sau `moveend` chỉ hiển thị nút thay vì tự request ngay.

Trên mobile nên ưu tiên cơ chế này để tránh request liên tục.

---

## 12. Request cancellation

Nếu request A đang chạy:

```text
viewport A
```

người dùng tiếp tục di chuyển:

```text
viewport B
```

request A phải bị abort bằng `AbortController` hoặc cơ chế tương đương.

Chỉ response của request mới nhất được phép cập nhật state.

Không để response cũ ghi đè response mới.

---

## 13. State management

Tạo một state object duy nhất:

```javascript
{
  bounds,
  center,
  zoom,
  selectedHotelId,
  hoveredHotelId,
  filters,
  search,
  hotels,
  loading,
  error,
  lastRequestId
}
```

List và Map đọc cùng state.

Không để:

```text
hotel-list.js có state riêng
hotel-map.js có state riêng
```

vì sẽ gây lệch marker/list.

---

## 14. List ↔ Map synchronization

### Click list

```text
selectHotel(id)
  ↓
state.selectedHotelId = id
  ↓
highlight list card
  ↓
set feature state selected
  ↓
map.easeTo({center})
```

### Click map

```text
queryRenderedFeatures()
  ↓
hotel_id
  ↓
selectHotel(id)
  ↓
highlight list
  ↓
show preview
```

Không tìm hotel bằng cách duyệt toàn bộ DOM.

Mapbox `queryRenderedFeatures()` được thiết kế để lấy feature đang được render tại điểm/vùng tương tác.

### Hover

Dùng `feature-state` hoặc state layer, không tạo hàng trăm DOM listeners.

---

## 15. Selected hotel

Chỉ một hotel selected tại một thời điểm.

Khi selected:

```text
hotel-points
      ↓
normal
      ↓
selected layer/state
      ↓
highlight
```

Nếu cần HTML card:

```text
SelectedHotelCard
```

là một DOM overlay duy nhất.

Không tạo 1 HTML popup cố định cho 1.000 hotels.

---

## 16. Detail navigation

Hotel detail URL phải lấy từ hệ thống hiện tại:

```text
route('hotels.show', hotel.slug)
```

Không hard-code URL trong JS nếu Laravel đã có route helper.

API có thể trả:

```text
detail_url
```

đã được backend tạo.

Click:

```text
[Xem chi tiết]
```

→ chuyển đến URL chính thức của hệ thống.

---

## 17. Search/filter architecture

Map query phải dùng cùng filter state với hotel list.

Ví dụ:

```text
price
rating
stars
property_type
amenities
destination
```

Flow:

```text
User filter
   ↓
Search State
   ↓
Hotel List API
   ↓
Map API
```

Không để map dùng filter khác list.

Nếu list API hiện tại đã tồn tại, ưu tiên refactor thành service dùng chung:

```text
HotelSearchService
```

với hai output:

```text
paginate()
mapFeatures()
```

Cả hai dùng cùng một query builder/specification.

---

## 18. Tách HotelSearchService

Tạo:

```text
app/Services/HotelSearchService.php
```

Chịu trách nhiệm:

- base query;
- filters;
- destination;
- price;
- rating;
- stars;
- property type;
- amenities;
- spatial constraint.

Sau đó:

```text
HotelController
HotelMapController
```

đều dùng service.

Không copy/paste query.

---

## 19. Caching service

Tạo abstraction:

```text
HotelMapCacheService
```

Nhiệm vụ:

- normalize params;
- generate cache key;
- get;
- put;
- invalidate/version.

Không đặt Redis logic trực tiếp trong controller.

---

## 20. Map API security

Mapbox public access token có thể xuất hiện ở frontend, nhưng phải cấu hình URL restrictions trong Mapbox account.

Laravel API:

- validate tất cả query params;
- giới hạn bounds;
- giới hạn số kết quả;
- rate limit;
- không cho arbitrary SQL;
- không nhận raw SQL/filter expression từ client;
- whitelist filter fields;
- whitelist sort fields.

Ví dụ:

```text
sort=price_asc
sort=rating_desc
```

không nhận:

```text
sort=some_raw_sql
```

---

## 21. Rate limiting

Endpoint map có thể bị gọi rất nhiều.

Áp dụng rate limiter riêng:

```text
hotel-map
```

Không nhất thiết dùng cùng limit với API authentication.

Ví dụ kiến trúc:

```text
guest:
    giới hạn theo IP/session

authenticated:
    giới hạn cao hơn

internal:
    token riêng
```

Không hard-code một con số duy nhất trước khi benchmark traffic thực tế.

---

## 22. Mapbox token

Frontend token:

```text
MAPBOX_PUBLIC_TOKEN
```

Không đặt secret token trong repository.

Trong Laravel:

```text
.env
```

và expose qua config/view variable an toàn.

Không commit token.

Thiết lập URL restrictions trong Mapbox account cho production domains.

---

## 23. Accessibility

Không được chỉ làm bản đồ.

Mọi hotel phải vẫn có:

- tên;
- rating;
- giá;
- link chi tiết;
- focus state.

Người dùng keyboard vẫn có thể dùng hotel list dù không thao tác map.

Map là lớp visual enhancement, không phải nguồn UI duy nhất.

---

## 24. Mobile

Desktop:

```text
List 35–45%
Map 55–65%
```

Mobile:

```text
Map 100%
       +
Bottom sheet list
```

Có:

```text
[Bản đồ]
[Danh sách]
```

và:

```text
[Tìm kiếm khu vực này]
```

Không render 2 cột desktop trên màn hình điện thoại.

---

## 25. Loading UX

Không để map trắng khi API loading.

Hiển thị:

```text
skeleton list
```

nhưng map vẫn tương tác được.

Khi request mới:

- giữ markers cũ;
- hiển thị loading nhỏ;
- thay dataset khi response mới thành công.

Không xóa toàn bộ map trước khi request xong.

---

## 26. Error handling

Nếu map API lỗi:

```text
Không thể tải khách sạn trong khu vực này.
[Thử lại]
```

Mapbox lỗi:

```text
Bản đồ không thể tải.
```

Hotel list vẫn phải có khả năng hoạt động độc lập.

Không để exception JS phá toàn bộ trang.

---

## 27. Performance budgets

Mục tiêu v1:

- map initial render nhanh;
- không tạo hàng trăm DOM markers;
- map pan/zoom không trigger API liên tục;
- API map trả payload nhỏ;
- response API phải được cache;
- frontend chỉ parse dữ liệu cần thiết;
- không gửi description/facilities/images lớn trong map response.

Theo Mapbox, Style Layers phù hợp hơn DOM markers khi có nhiều điểm; với dữ liệu hàng nghìn điểm và phạm vi lớn, vector tiles có thể mang lại hiệu quả đáng kể vì chỉ tải dữ liệu theo viewport.

---

## 28. Khi scale lớn

Kiến trúc target:

```text
                         CDN
                          │
                    Static assets
                          │
Users ──────────────── Load Balancer
                          │
                 ┌────────┴────────┐
                 │                 │
             Laravel #1       Laravel #N
                 │                 │
                 └────────┬────────┘
                          │
                        Redis
                          │
                       MySQL
                          │
                 Geo / Hotel Data
```

Crawler:

```text
Crawler
   ↓
Queue
   ↓
Normalize
   ↓
Hotel DB
   ↓
Cache invalidation
```

Không chạy crawler đồng bộ trong HTTP request.

---

## 29. Crawler integration

Không viết crawler mới nếu crawler hiện tại đã hoạt động.

Cursor phải inspect:

```text
crawler
parser
normalizer
import command
jobs
migrations
```

Bổ sung pipeline:

```text
crawl
 ↓
parse
 ↓
normalize
 ↓
validate coordinate
 ↓
upsert hotel/source
 ↓
update canonical hotel
 ↓
invalidate map cache
```

Nếu crawler đã có latitude/longitude:

```text
GIỮ NGUYÊN
```

Không geocode lại hàng loạt.

Nếu crawler chưa có tọa độ:

```text
source address
 ↓
geocoding pipeline
 ↓
validate
 ↓
persist lat/lng
```

Geocoding phải có cache để không gửi cùng một địa chỉ nhiều lần.

---

## 30. Không geocode mỗi lần hiển thị map

Đây là nguyên tắc bắt buộc.

Sai:

```text
User mở map
 ↓
Laravel
 ↓
Geocoding API
 ↓
Hotel coordinates
```

Đúng:

```text
Crawler/import
 ↓
coordinate persisted
 ↓
Hotel DB
 ↓
Map
```

Map chỉ đọc tọa độ.

---

## 31. Destination / map viewport

Nên hỗ trợ 2 kiểu:

### Destination map

```text
/phu-quoc/khach-san
```

Laravel biết destination bounds/center.

### Free map

```text
/map
```

Frontend gửi viewport.

Không hard-code tọa độ Phú Quốc trong JS.

---

## 32. Destination entity

Nếu hệ thống đã có bảng destinations:

```text
destinations
```

hãy tái sử dụng.

Nếu chưa có và hệ thống cần scale, nên có:

```text
destination
----------------
id
name
slug
country
parent_id
latitude
longitude
bbox/polygon
```

Hotel có thể thuộc destination thông qua relation hiện có.

Map API không nên tự suy luận destination bằng tên text nếu DB đã có relation.

---

## 33. Map data versioning

Tạo version hoặc timestamp logic:

```text
hotel_map_data_version
```

Khi crawler/import thay đổi:

```text
version++
```

Cache key:

```text
hotel-map:v{version}:{hash}
```

Điều này đơn giản hóa cache invalidation.

Nếu không muốn bảng riêng, có thể dùng cache key version.

---

## 34. Favorites / user interaction mở rộng

Kiến trúc phải cho phép sau này:

```text
favorite
compare
recently viewed
share location
```

Nhưng không đưa logic này vào Mapbox module.

Ví dụ:

```text
HotelMap
HotelSearch
HotelFavorite
HotelCompare
```

Map chỉ phát event:

```text
hotel:selected
hotel:hovered
hotel:opened
```

Business logic xử lý ở ngoài.

---

## 35. Analytics

Có thể phát event:

```text
map_open
map_move
map_search_area
hotel_marker_click
hotel_card_click
hotel_detail_open
cluster_click
map_filter_change
```

Không gửi event `mousemove` liên tục.

Dùng debounce/throttle.

---

## 36. Observability

Log:

```text
hotel_map_request
```

với:

```text
zoom
bounds bucket
filters hash
result count
duration_ms
cache_hit
```

Không log dữ liệu nhạy cảm.

Metrics cần theo dõi:

```text
map API p50
map API p95
map API p99
cache hit ratio
DB query duration
payload size
frontend map errors
Mapbox errors
```

---

## 37. Test bắt buộc

### Backend

- valid bounds;
- invalid bounds;
- bounds quá lớn;
- no-coordinate hotels;
- filter price;
- filter rating;
- filter stars;
- destination;
- pagination/list compatibility;
- cache hit;
- cache invalidation;
- query performance.

### Frontend

- map load;
- API loading;
- API error;
- click marker;
- click list;
- selected state;
- cluster;
- zoom;
- pan;
- request cancellation;
- stale response protection;
- filter synchronization;
- mobile mode.

### Integration

Scenario:

```text
Open Phu Quoc
→ map loads
→ hotels appear
→ click hotel
→ list selected
→ map centers
→ click detail
→ detail page opens
```

---

## 38. Performance benchmark

Trước khi production, tạo dataset test:

```text
10,000 hotels
50,000 hotels
100,000 hotels
500,000 hotels
1,000,000 hotels
```

Benchmark:

```text
map query p50
map query p95
DB CPU
Redis hit rate
payload size
browser FPS
memory
```

Không quyết định chuyển vector tiles chỉ dựa trên cảm giác.

---

## 39. Vector tile migration path

Khi GeoJSON không còn phù hợp:

```text
Current:
Laravel
  ↓
GeoJSON
  ↓
Mapbox GL

Future:
Hotel DB
  ↓
Tile generation/service
  ↓
Vector Tiles
  ↓
Mapbox GL
```

Frontend chỉ thay:

```text
GeoJSONSource
```

bằng:

```text
VectorTileSource
```

Các interaction layer vẫn giữ:

```text
hotel-points
hotel-selected
hotel-hover
clusters
```

Đây là lý do phải tách `MapDataSource`.

---

## 40. File/module architecture đề xuất

Không bắt buộc đúng tên nếu project có convention khác.

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── HotelMapController.php
│   ├── Requests/
│   │   └── HotelMapRequest.php
│   └── Resources/
│       └── HotelMapResource.php
│
├── Services/
│   ├── HotelSearchService.php
│   ├── HotelMapService.php
│   ├── HotelMapCacheService.php
│   └── GeoService.php
│
├── Models/
│   ├── Hotel.php
│   └── HotelSource.php
│
resources/
├── js/
│   └── map/
│       ├── HotelMap.js
│       ├── MapState.js
│       ├── MapDataSource.js
│       ├── MapInteractions.js
│       ├── MapLayers.js
│       ├── HotelMapApi.js
│       └── index.js
│
routes/
└── api.php

tests/
├── Feature/
│   └── HotelMapApiTest.php
└── Unit/
    ├── HotelMapServiceTest.php
    └── HotelMapCacheTest.php
```

Nếu project đang có cấu trúc khác, follow cấu trúc hiện tại thay vì ép đổi.

---

## 41. API example

Request:

```http
GET /api/hotels/map?north=10.30&south=10.05&east=104.10&west=103.80&zoom=12&min_rating=8
```

Response:

```json
{
  "data": {
    "type": "FeatureCollection",
    "features": [
      {
        "type": "Feature",
        "id": 1528,
        "geometry": {
          "type": "Point",
          "coordinates": [103.958421, 10.194832]
        },
        "properties": {
          "hotel_id": 1528,
          "name": "Example Resort",
          "slug": "example-resort",
          "price": 1200000,
          "display_price": "1,200,000đ",
          "rating": 9.3,
          "stars": 4,
          "detail_url": "/khach-san/example-resort"
        }
      }
    ]
  },
  "meta": {
    "count": 1,
    "zoom": 12
  }
}
```

Nếu giá không tồn tại:

```json
{
  "price": null,
  "display_price": null
}
```

Frontend phải fallback gracefully.

---

## 42. Quy tắc tuyệt đối cho Cursor

1. Không thay framework hiện tại.
2. Không chuyển toàn app sang SPA.
3. Không tạo DOM marker cho toàn bộ hotel.
4. Không query toàn bộ hotel table.
5. Không gửi toàn bộ hotel record về frontend.
6. Không geocode trong map request.
7. Không gọi crawler trong map request.
8. Không gọi supplier API trực tiếp từ browser.
9. Không để frontend tự query database.
10. Không duplicate HotelSearch query ở nhiều controller.
11. Không hard-code hotel coordinates.
12. Không hard-code hotel detail URL.
13. Không commit Mapbox token.
14. Không làm mất dữ liệu crawler hiện có.
15. Không đổi schema hiện tại nếu không cần.
16. Không xóa migration/model cũ chỉ để làm kiến trúc mới.
17. Không làm một prototype riêng không kết nối DB thật.
18. Không dùng mock data khi integration đã có DB thật.
19. Không bỏ qua mobile.
20. Không bỏ qua cache/rate limit/request cancellation.
21. Không tối ưu quá mức bằng vector tiles ngay khi chưa benchmark nếu GeoJSON viewport API đang đáp ứng tốt.
22. Nhưng phải thiết kế abstraction để chuyển sang vector tiles mà không rewrite UI.

---

## 43. Thứ tự triển khai

### Phase 0 — Audit

Cursor phải kiểm tra:

```text
Hotel model
hotels migration
crawler
crawler output
import pipeline
existing hotel listing/search
existing hotel detail route
frontend build system
Redis
queue
MySQL version
```

Output trước khi code:

```text
CURRENT SCHEMA
CURRENT CRAWLER FLOW
CURRENT HOTEL SEARCH FLOW
GAPS
MIGRATIONS REQUIRED
```

### Phase 1 — Data readiness

- xác định lat/lng hiện tại;
- thêm missing coordinate fields nếu cần;
- validate;
- thêm source identity nếu thiếu;
- thêm spatial index nếu phù hợp;
- không phá crawler.

### Phase 2 — Backend Map API

- Form Request;
- Map Service;
- shared Search Service;
- Geo query;
- Resource;
- cache;
- rate limit;
- tests.

### Phase 3 — Mapbox

- Map initialization;
- style;
- GeoJSON source;
- layers;
- cluster;
- price labels;
- click/hover;
- selected state.

### Phase 4 — List ↔ Map

- shared state;
- list selection;
- marker selection;
- filter synchronization;
- map bounds search.

### Phase 5 — UX

- loading;
- error;
- mobile;
- bottom sheet;
- search-area button;
- animation;
- accessibility.

### Phase 6 — Performance

- indexes;
- Redis;
- request cancellation;
- payload reduction;
- query optimization;
- benchmark.

### Phase 7 — Production

- Mapbox URL restriction;
- env config;
- logging;
- metrics;
- rate limiting;
- cache warming if needed.

### Phase 8 — Scale

Chỉ khi benchmark cho thấy cần:

```text
GeoJSON
→ vector tiles
```

---

## 44. Definition of Done

Tính năng được coi là hoàn thành khi:

- [ ] map render bằng Mapbox GL JS;
- [ ] hotel lấy từ DB thật;
- [ ] không dùng mock hotel data;
- [ ] tọa độ lấy từ DB;
- [ ] marker hiển thị giá/rating theo data;
- [ ] cluster hoạt động;
- [ ] click cluster zoom đúng;
- [ ] click marker chọn hotel;
- [ ] click hotel card chọn marker;
- [ ] selected state đồng bộ;
- [ ] link detail đúng route hệ thống;
- [ ] pan map không spam API;
- [ ] request cũ không thể ghi đè request mới;
- [ ] filters đồng bộ list/map;
- [ ] API có validation;
- [ ] API có rate limit;
- [ ] cache hoạt động;
- [ ] DB query có index phù hợp;
- [ ] không tạo hàng trăm DOM markers;
- [ ] mobile UX hoạt động;
- [ ] crawler vẫn chạy;
- [ ] import không làm mất tọa độ;
- [ ] tests pass;
- [ ] production env không commit token;
- [ ] benchmark được thực hiện trên dữ liệu thật.

---

## 45. Tài liệu tham khảo kỹ thuật cho Cursor

Khi triển khai Mapbox, ưu tiên tài liệu chính thức về:

- Mapbox GL JS Markers và performance considerations.
- Mapbox GL JS Style Layers.
- GeoJSON source và clustering.
- `queryRenderedFeatures`.
- `addInteraction`.
- Vector tiles và source/layer architecture.

Đặc biệt: Mapbox hiện khuyến nghị Style Layers cho lượng điểm lớn thay vì DOM Markers; clustering có sẵn trên GeoJSON source; `queryRenderedFeatures()` dùng để lấy feature đang render để xử lý hover/click. 

---

# KẾT LUẬN KIẾN TRÚC

Kiến trúc chính thức cần hướng tới:

```text
                         USERS
                           │
                           ▼
                    Laravel Website
                           │
              ┌────────────┴────────────┐
              │                         │
         Hotel List                 Map UI
              │                         │
              └────────────┬────────────┘
                           │
                    Shared Search State
                           │
                    HotelSearchService
                           │
                    HotelMapService
                           │
             ┌─────────────┴─────────────┐
             │                           │
          Redis                       MySQL
             │                           │
             │                    Hotel + Geo data
             │                           │
             └──────────────┬────────────┘
                            │
                      Map Data API
                            │
                     GeoJSON / Tiles
                            │
                       Mapbox GL JS
                            │
                  WebGL Style Layers
                            │
             ┌──────────────┼──────────────┐
             ▼              ▼              ▼
          Cluster        Price          Selected
          Layer          Layer           Layer
```

**Nguyên tắc cốt lõi:** Laravel/DB của bạn sở hữu **hotel identity + tọa độ + dữ liệu canonical**. Mapbox chỉ là **rendering engine**. Crawler chỉ là **data ingestion layer**. Giá/availability là **dynamic layer**. Frontend Map Engine là một module độc lập. Nhờ vậy sau này có thể thay GeoJSON bằng vector tiles, thêm supplier/API khác, hoặc mở rộng từ hotel sang toàn bộ POI du lịch mà không phải viết lại hệ thống.
