# PRD — Website Du Lịch (Clone kiến trúc autourasia.com / autourasia.it)

> Tài liệu này mô tả sản phẩm ở mức tổng quan để Cursor / AI coding agent hiểu bối cảnh trước khi đọc các file chi tiết khác trong bộ tài liệu.
> **Cập nhật:** đã đối chiếu với 7 ảnh chụp màn hình thực tế từ bản autourasia.it — xem `06-tham-chieu-hinh-anh.md` để có bảng ánh xạ ảnh ↔ trang, và danh sách các chi tiết UI được bổ sung/hiệu chỉnh so với bản khảo sát text ban đầu.

## 1. Bối cảnh & mục tiêu

Xây dựng một website đại lý du lịch (travel agency) theo mô hình tham khảo **autourasia.com/.it** — một website B2C giới thiệu & bán tour trọn gói (package tour), tour du thuyền (cruise), **5 cụm dịch vụ mở rộng** (vé tàu, máy bay, lưu trú, vui chơi, dịch vụ khác), có travel guide (blog SEO), và các trang thương hiệu (About us, Team, Reviews...).

Mục tiêu kinh doanh của mô hình này:
- SEO content-heavy: rất nhiều landing page theo destination/duration/style để hứng traffic tìm kiếm (topic: "10 days in Vietnam", "Cose da vedere in Cambogia"...).
- Không có giỏ hàng / thanh toán online — chuyển đổi (conversion) là **inquiry/lead form** ("Personalizza il tour", "Domanda Rapida per un Tour", WhatsApp chat), không phải checkout online.
- Nội dung tour rất chi tiết (itinerary theo từng ngày, bản đồ, inclusion/exclusion, FAQ, review) — đóng vai trò vừa bán hàng vừa SEO.
- Blog/Travel Guide là một **hệ thống nội dung riêng biệt, có chiều sâu** (category, tag, mục lục, bình luận, chia sẻ mạng xã hội) — không chỉ là vài bài viết đơn giản.
- Đa ngôn ngữ / đa quốc gia con (site .com tiếng Anh, .it tiếng Ý, .fr tiếng Pháp — có thể bỏ qua phần đa site, chỉ làm 1 ngôn ngữ trước, nhưng nên scaffold sẵn cấu trúc i18n vì site gốc coi đây là tính năng cốt lõi, không phải phụ).

## 2. Phạm vi (Scope) đề xuất cho bản clone

**Trong phạm vi (MVP → V1):**
- Trang chủ (Home) — xem ảnh `image_clone/trang-chu.png`
- Danh mục điểm đến / quốc gia (Destination hub)
- Danh sách Tour theo quốc gia/chủ đề + bộ filter phong phú (Durata + Stile di viaggio) — xem ảnh `image_clone/danh-muc-tour.png`
- Trang chi tiết Tour (Tour Detail) — itinerary, bản đồ, giá, inclusion, review, FAQ
- Danh sách Du thuyền (Cruise Listing) + Chi tiết Cruise (dùng chung layout với Tour Detail, khác field)
- **5 cụm dịch vụ (lead-gen, không checkout):** hub + danh mục + chi tiết sản phẩm dịch vụ — vé tàu cao tốc, vé máy bay, khách sạn/resort, vé vui chơi & trải nghiệm, dịch vụ hỗ trợ khác (thuê xe, spa, HDV riêng…). Chuyển đổi qua form yêu cầu báo giá / liên hệ — **không** giỏ hàng hay thanh toán trực tuyến cho dịch vụ lẻ.
- **Travel Guide/Blog đầy đủ**: danh mục theo điểm đến + tag/filter theo loại nội dung + bài viết có mục lục/tag/bình luận/chia sẻ — xem ảnh `image_clone/danh-muc-blog.png` và `image_clone/noi-dung-blog.png`
- Trang About Us (Chi Siamo) — trang tổng hợp nhiều block (mission/vision/values/reasons/reference persons) — xem ảnh `image_clone/ve-chung-toi-about.png`
- Trang Our Team (lồng trong About Us, có thể tách trang riêng nếu cần)
- Trang Customer Reviews / Experience Gallery / Experience Video
- Trang Contact — xem ảnh `image_clone/lien-he-contact.png`
- Trang Customize Tour (form chi tiết nhiều khối dữ liệu) — xem ảnh `image_clone/personalizza-tour-form.png`
- Quick Inquiry Form (form ngắn, lặp lại ở cuối hầu hết các trang)
- Footer với thông tin liên hệ đa văn phòng, QR code WhatsApp, social links, block SEO internal-link

**Ngoài phạm vi (không làm ở V1):**
- Thanh toán / booking online có giỏ hàng (áp dụng cả tour lẫn dịch vụ lẻ)
- Đa ngôn ngữ dịch đầy đủ (chỉ scaffold cấu trúc i18n, chưa cần dịch hết nội dung)
- Hệ thống tài khoản khách hàng (login/dashboard)
- CMS admin đầy đủ (có thể dùng headless CMS có sẵn thay vì tự viết) — **admin CRUD cho catalogue dịch vụ chưa triển khai** (dữ liệu qua seed + roadmap)

## 3. Đối tượng người dùng
- Khách du lịch quốc tế (Âu-Mỹ, và rõ ràng có thị trường Ý/Pháp riêng theo tên miền phụ) đang tìm tour trọn gói ở Việt Nam/Đông Nam Á.
- Hành vi: duyệt nhiều tour, so sánh itinerary, đọc blog du lịch để lên kế hoạch, đọc review, rồi điền form "Personalizza il tour" hoặc form nhanh để nhận báo giá — không mua ngay trên site.

## 4. Định hướng kỹ thuật (xem chi tiết ở `05-tech-stack-va-trien-khai.md`)
- Kiến trúc: Next.js (App Router) + Headless CMS (Sanity/Strapi/Payload) hoặc MDX cho content, để tận dụng SSG/ISR cho SEO.
- Toàn bộ trang là public, index được, tối ưu Core Web Vitals & SEO on-page (meta, OG, schema.org).
- Blog/Travel Guide cần kiến trúc content model riêng biệt (category + tag + TOC + comment) — không dùng chung schema đơn giản với static page.

## 5. Cách dùng bộ tài liệu này với Cursor
Thứ tự đọc khuyến nghị cho AI agent:
1. `00-PRD-tong-quan.md` (file này) — hiểu bối cảnh
2. `06-tham-chieu-hinh-anh.md` — **xem ảnh thật trước tiên**, nắm bố cục trực quan
3. `01-sitemap-ia.md` — hiểu toàn bộ cấu trúc URL & điều hướng
4. `02-page-specs.md` — spec chi tiết từng loại trang, thành phần UI (đã gắn ảnh minh hoạ trực tiếp trong file)
5. `03-data-models.md` — schema dữ liệu (Tour, Cruise, Destination, Article, Comment...)
6. `04-design-system.md` — style guide, layout pattern, component
7. `05-tech-stack-va-trien-khai.md` — stack đề xuất, cấu trúc thư mục, cách triển khai

Khi implement, nên tạo theo thứ tự: **Design System → Layout (Header/Footer) → Home → Tour Listing → Tour Detail → Cruise (reuse) → Service Hubs (5 cụm) → Travel Guide (Listing + Article) → Customize Tour Form → About Us → Contact/Static pages**.
