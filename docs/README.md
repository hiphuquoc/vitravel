# Bộ tài liệu kỹ thuật — Website Du lịch (tham khảo cấu trúc autourasia.com / autourasia.it)

Bộ tài liệu này được soạn từ việc khảo sát trực tiếp cấu trúc autourasia.com (trang chủ, danh mục tour, chi tiết tour) **kết hợp đối chiếu 7 ảnh chụp màn hình thực tế** từ bản autourasia.it (lưu tại `image_clone/`), nhằm phục vụ việc dựng lại **kiến trúc trang / luồng UX / mô hình dữ liệu** của một website đại lý du lịch, để đưa vào Cursor làm ngữ cảnh (context) khi code.

## Cấu trúc thư mục

```
autourasia-clone-docs/
├── README.md                          ← file này
├── 00-PRD-tong-quan.md
├── 01-sitemap-ia.md
├── 02-page-specs.md                   ← đã gắn ảnh minh hoạ trực tiếp trong từng mục
├── 03-data-models.md
├── 04-design-system.md
├── 05-tech-stack-va-trien-khai.md
├── 06-tham-chieu-hinh-anh.md          ← MỚI: bảng ánh xạ ảnh ↔ trang + danh sách chi tiết UI bổ sung từ ảnh
└── image_clone/                       ← ảnh chụp màn hình thực tế (ground-truth trực quan)
    ├── trang-chu.png                  (Home)
    ├── danh-muc-tour.png              (Tour Listing + filter)
    ├── danh-muc-blog.png              (Blog/Travel Guide Listing)
    ├── noi-dung-blog.png              (Blog Article Detail)
    ├── personalizza-tour-form.png     (Customize Tour form)
    ├── lien-he-contact.png            (Contact)
    └── ve-chung-toi-about.png         (About Us / Chi Siamo)
```

## Cách dùng với Cursor

1. Copy cả thư mục `autourasia-clone-docs/` (bao gồm `image_clone/`) vào gốc repo, ví dụ `/docs`.
2. Trong Cursor, mở chat/composer — Cursor có thể đọc trực tiếp ảnh PNG kèm theo file markdown nếu bạn đính kèm hoặc trỏ đường dẫn, vì các ảnh đã được **link tương đối trong các file `.md`** (đường dẫn `image_clone/...`).
3. Prompt gợi ý để bắt đầu:
   > "Đọc toàn bộ các file trong /docs, bắt đầu từ 00-PRD, sau đó 06-tham-chieu-hinh-anh.md (mở kèm các ảnh trong docs/image_clone/). Dựa trên 01-sitemap, 02-page-specs, 03-data-models, 04-design-system, 05-tech-stack, hãy khởi tạo project Next.js theo đúng cấu trúc thư mục ở mục 2 của file 05-tech-stack-va-trien-khai.md, bắt đầu từ Giai đoạn 1."
4. Làm theo roadmap ở cuối file `05-tech-stack-va-trien-khai.md` — nên yêu cầu Cursor thực hiện **từng giai đoạn một**, và với mỗi trang cụ thể, luôn nhắc Cursor **mở ảnh tương ứng trong `image_clone/`** trước khi code UI (xem bảng ánh xạ ở `06-tham-chieu-hinh-anh.md` mục 1) để bám sát bố cục thật thay vì chỉ dựa vào mô tả chữ.

## Danh sách file

| File | Nội dung |
|---|---|
| `00-PRD-tong-quan.md` | Bối cảnh, mục tiêu, phạm vi dự án |
| `01-sitemap-ia.md` | Toàn bộ sitemap, menu, URL pattern, breadcrumb, block dùng chung |
| `02-page-specs.md` | Đặc tả UI chi tiết từng loại trang — **có ghi rõ ảnh tham chiếu ở đầu mỗi mục** |
| `03-data-models.md` | Schema dữ liệu product + ánh xạ SQL Laravel |
| `04-design-system.md` | Style guide, responsive breakpoints, **§3.5–3.14** (mapping toàn bộ trang public + nhật ký tối ưu), component inventory, UX |
| `05-tech-stack-va-trien-khai.md` | Stack đề xuất (lịch sử Next.js) + ghi chú triển khai Laravel hiện tại |
| `06-tham-chieu-hinh-anh.md` | Bảng ánh xạ ảnh ↔ trang, danh sách UI bổ sung từ ảnh |
| `07-database-architecture.md` | **Kiến trúc CSDL thực thi** — SEO hub, i18n, packages, leads (tham chiếu Hitour) |

## Trạng thái triển khai (2026-07)

- **UI public:** Laravel 13 + Blade, dữ liệu qua `ViewDataService` (DB + fallback `SampleData`).
- **Backend:** Controllers, FormRequests, Services (`SeoService`, `MediaService`, `ViewDataService`), lead POST endpoints.
- **Admin:** `/he-thong` — pattern liendoan.dev (sidebar, CRUD list/view/save/delete). Đăng nhập: `admin@vitravel.dev` / `vitravel@admin2026`.
- **DB:** `php artisan migrate --seed` — `ContentSeeder` nạp tour/cruise/article/brand từ mock.
- **Docs DB:** `07-database-architecture.md` + §18 trong `03-data-models.md`.

### Lệnh khởi tạo

```bash
composer dump-autoload
php artisan migrate --seed
npm run build
```

### Admin modules (đã có CRUD)

| Module | Route admin |
|---|---|
| Dashboard | `/he-thong/dashboard` |
| Gói Tour / Cruise | `/he-thong/san-pham/tour`, `/he-thong/san-pham/cruise` |
| Quốc gia | `/he-thong/san-pham/quoc-gia` |
| Bài viết | `/he-thong/bai-viet` |
| Đội ngũ | `/he-thong/doi-ngu` |
| Leads (3 loại) | `/he-thong/yeu-cau-nhanh`, `/he-thong/tour-rieng`, `/he-thong/lien-he` |
| Bình luận | `/he-thong/binh-luan` |

Các module brand còn lại (văn phòng, gallery, video, công ty…) đang placeholder — sẽ bổ sung theo cùng pattern.

## Điểm khác biệt quan trọng so với bản khảo sát ban đầu (chỉ dựa trên text)

Sau khi đối chiếu ảnh thật, một số giả định ban đầu đã được **hiệu chỉnh đáng kể** — xem chi tiết đầy đủ ở `06-tham-chieu-hinh-anh.md` mục 2, tóm tắt nhanh:
- Tour Listing có filter **"Stile di viaggio"** phong phú (11 lựa chọn) — quan trọng hơn filter giá.
- Hệ **Blog/Travel Guide** phức tạp hơn nhiều: có category theo điểm đến, tag theo loại nội dung, tag cloud SEO, mục lục tự sinh, box internal-link giữa bài, form bình luận, chia sẻ mạng xã hội.
- Trang **About Us** là 1 trang tổng hợp dài nhiều block (không phải static page đơn giản), có sơ đồ vòng tròn giá trị cốt lõi và card "referenti dall'estero".
- Form **Customize Tour** là form 1 trang dài (không phải wizard nhiều bước) với field rất cụ thể (traveler stepper theo độ tuổi, ngân sách theo per-person/per-group...).
- Có 3 loại form lead khác nhau về field (Quick Inquiry / Customize Tour / Contact) — không nên gộp chung 1 schema.

## Lưu ý quan trọng

Bộ tài liệu mô tả **mô hình/kiến trúc trang** (page pattern) của loại hình website đại lý du lịch bán tour trọn gói — không sao chép nội dung, hình ảnh hay thương hiệu gốc. Ảnh trong `image_clone/` chỉ dùng làm **tham chiếu bố cục/phong cách trực quan** cho AI khi code, không dùng để lấy lại nguyên văn nội dung tiếng Ý, logo, hay ảnh chụp thật của Autour Asia. Khi triển khai, bạn cần thay bằng thương hiệu, nội dung, ảnh, và tour thực tế của riêng mình.
