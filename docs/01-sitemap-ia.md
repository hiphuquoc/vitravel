# Sitemap & Kiến trúc thông tin (IA)

Dựa trên khảo sát cấu trúc URL/menu của autourasia.com và đối chiếu trực quan với ảnh chụp thực tế autourasia.it (xem `06-tham-chieu-hinh-anh.md`).

## 1. Menu chính (Header Navigation)

Header thực tế (ảnh `image_clone/trang-chu.png`, `lien-he-contact.png`) gọn hơn giả định ban đầu:

```
HOME
DESTINAZIONI ▾        → mega menu, chia theo quốc gia (tương đương "Tours" ở bản .com)
CROCIERE ▾             → mega menu Cruises
COSE DA FARE ▾         → mega menu Travel Guide / Blog
CHI SIAMO              → About Us (link thẳng, không dropdown trong ảnh — có thể có dropdown ẩn)
CONTATTACI             → Contact
🔍 (icon search)
[Personalizza il tour] → nút CTA nổi bật (button màu cam/đỏ), luôn hiển thị
🇫🇷 FR | 🇬🇧 EN         → language switcher dạng cờ, đặt cuối cùng bên phải
```

### 1.1 DESTINAZIONI (mega menu) — tương đương "Tours" bản .com
```
├─ Vietnam Tours
│   ├─ All Vietnam Tours          /tours/vietnam
│   ├─ 10 days in Vietnam         /tours/{slug}-c{id}/vietnam
│   ├─ 2 weeks in Vietnam
│   ├─ 15 days in Vietnam
│   ├─ 3 weeks in Vietnam
│   ├─ Vietnam Packages Tours
│   ├─ Vietnam Golf Tours
│   ├─ North / Central / South Vietnam Tours
│   ├─ Sapa Tour From Hanoi
│   └─ Vietnam Day Trips
├─ Thailand / Cambodia / Laos / Myanmar Tours (All + Day Trips + Packages)
└─ Combined Countries Tours
    ├─ Others Combination Tours
    ├─ Vietnam and Cambodia Tours
    ├─ Vietnam and Laos Tours
    ├─ Vietnam Thailand Tours
    └─ Indochina Tours
```
Ghi chú: trang chủ .it còn show thêm destination **Bali (Indonesia)** ở "Le destinazioni più amate" và trong form Customize Tour — nghĩa là danh sách quốc gia phục vụ cần gồm cả **Bali**, không chỉ 5 quốc gia Đông Dương.

### 1.2 CROCIERE (mega menu) — Cruises
```
├─ Halong Bay Cruises          /cruises/halong-bay-cruises/
│   ├─ Halong Bay Classic Cruises (From Tuan Chau)
│   ├─ Lan Ha Bay Cruises (From Tuan Chau / Bai Chay / Cat Ba)
│   ├─ Bai Tu Long Bay Cruises (From Hon Gai / Bai Chay)
│   ├─ Halong Bay Cruises (From Bai Chay)
│   ├─ Luxury Cruises Halong
│   ├─ Private Cruises
│   └─ Day Cruises
├─ Mekong Cruises              /cruises/mekong-cruises/
│   ├─ Mekong Cruises in Vietnam
│   ├─ Private Mekong Cruises
│   ├─ Vietnam Cambodia Cruises
│   └─ Mekong Cruises in Laos
└─ Myanmar River Cruises       /cruises/myanmar-river-cruises/
    ├─ Irrawaddy River Cruises
    └─ Chindwin River Cruises
```

### 1.3 COSE DA FARE (mega menu) — Travel Guide/Blog
Đây là mục quan trọng cần làm kỹ hơn dự kiến ban đầu — xem ảnh `image_clone/danh-muc-blog.png` và `noi-dung-blog.png`:
```
├─ All Travel Guide            /travel-guide (hoặc /cose-da-fare)
├─ Vietnam / Thailand / Cambodia / Laos / Myanmar Travel Guide
│   → mỗi quốc gia có danh mục con theo THÀNH PHỐ/ĐIỂM ĐẾN
│     (VD Cambogia: Koh Rong, Phnom Penh, Siem Reap, Battambang, Mondulkiri, Sihanoukville)
├─ Experience video            /experience-video
└─ Album of experience         /experience-gallery
```

### 1.4 CHI SIAMO (About Us) — trang tổng hợp, không phải dropdown nhiều trang con
Theo ảnh `ve-chung-toi-about.png`, **CHI SIAMO là 1 trang dài duy nhất** gộp toàn bộ nội dung sau (không phải nhiều URL con như giả định .com ban đầu — dù .com có thể vẫn tách trang riêng, .it gộp thành 1 trang):
```
/chi-siamo (hoặc /about-us)
  - Viaggio autentico (intro công ty + số giấy phép)
  - Uno staff dedicato (team grid)
  - La nostra missione / la nostra visione
  - Impegno nei valori fondamentali (sơ đồ 4 giá trị cốt lõi)
  - Politica di vendita (chính sách bán hàng)
  - Perché scegliere noi? (lý do chọn chúng tôi)
  - I nostri referenti dall'estero (reference persons abroad)
  - Video di esperienze autentiche
```
→ **Khuyến nghị khi clone**: vẫn giữ data model tách rời theo từng block (như mô tả ở `03-data-models.md`) để linh hoạt, nhưng có thể **render tất cả trên 1 route `/about-us`** thay vì tách nhiều URL riêng như bản .com — tuỳ nhu cầu SEO của bạn (nếu muốn mỗi block rank riêng 1 từ khoá thì tách trang, nếu ưu tiên trải nghiệm đọc liền mạch thì gộp 1 trang như bản .it).

### 1.5 CONTATTACI (Contact)
```
/contact-us (hoặc /contattaci)
```

## 2. Quy ước URL (URL pattern)

| Loại trang | Pattern thực tế | Đề xuất pattern khi clone |
|---|---|---|
| Trang chủ | `/` | `/` |
| **Hub tất cả tour** | — | **`/tours`** |
| Danh mục tour theo quốc gia | `/tours/{country-slug}` | `/tours/{country}` |
| Danh mục tour theo chủ đề/thời lượng | `/tours/{topic-slug}-c{id}/{country}` | `/tours/{country}/{topic-slug}` *(SEO sẵn; route public topic TBD)* |
| Chi tiết tour | `/t{id}-{slug}.html` | `/tours/{country}/{slug}` |
| Danh mục cruise | `/cruises/{cruise-type-slug}/` | `/cruises/{type}` |
| Chi tiết cruise | `/cruises/{slug}/` | `/cruises/{type}/{slug}` |
| Travel guide danh mục quốc gia | `/travel-guide/co{id}-{country}.html` | `/travel-guide/{country}` |
| Travel guide danh mục điểm đến (Cose da vedere in {city}) | — | `/travel-guide/{country}/{destination}` |
| Travel guide bài viết | `/b{id}-{slug}.html` | `/travel-guide/{country}/{destination}/{slug}` |
| Trang tĩnh | `/{slug}.html` | `/{slug}` |
| Trang Customize Tour | `/customize-tour` | `/customize-tour` |
| Form yêu cầu báo giá theo tour | `/tour/{slug}/inquiry.html` | `/tours/{country}/{slug}/inquiry` |

Ghi chú: site gốc dùng ID số (`c503`, `t589`, `b397`) do CMS cũ generate — khi clone **không cần giữ ID**, nên dùng slug sạch để thân thiện & dễ maintain hơn.

## 3. Breadcrumb pattern

Mọi trang tour/cruise/travel-guide đều có breadcrumb, đặt trong 1 card trắng đè lên banner ảnh (xem `danh-muc-tour.png` — breadcrumb + H1 nằm trong khối card bo góc, không nằm trực tiếp trên nền ảnh):
```
Home / Tour                          → /tours
Home / Tour / Tour {Country}         → /tours/{country}
Home / Tour / Tour {Country} / {Tour name}
Home / Cose da fare / Cose da vedere in {Country} / {Article title}
```

JSON-LD `BreadcrumbList` emit từ `x-layout.breadcrumb` → `SchemaService::breadcrumbList`. Layer SEO admin có thể build bằng `SeoService::breadcrumbsForEntry` (đi ngược `parent_id`).
→ Breadcrumb là component dùng chung, cần schema.org `BreadcrumbList`.

## 4. Footer (dùng chung toàn site)

Theo ảnh thực tế (`trang-chu.png`, `lien-he-contact.png`), footer gồm 2 tầng rõ rệt:

**Tầng 1 — Contact strip** (nền màu be nhạt, phía trên footer đen):
- Logo + tagline ("Soddisfatto più del previsto" = "Satisfied more than expected")
- Email, Phone & WhatsApp
- **QR code WhatsApp** (ô vuông riêng, quét để chat nhanh)
- Địa chỉ 3 văn phòng cạnh nhau theo cột: Hanoi, Ho Chi Minh City, Siem Reap

**Tầng 2 — Footer chính** (nền đen/xám đậm):
- 4 cột menu cố định:
  1. **Autour Asia**: Chi siamo / Testimonianze / I clienti parlano di noi / Il nostro team / Richiedi preventivo gratuito
  2. **Tour altamente consigliati**: 10/15 giorni in Vietnam, 2/3 settimane in Vietnam
  3. **Le migliori destinazioni**: Baia di Halong, Ninh Binh, Sapa, Pu Luong, Hoi An, Delta del Mekong
  4. **Cose da sapere**: Quanto costa Vietnam 10 giorni, Vietnam cosa vedere in 15 giorni, Quanto costa Vietnam 2 settimane
- **Hàng link SEO rời** bên dưới 4 cột (không có tiêu đề, chỉ 1 dòng/2 dòng link cách nhau bằng "|"): "Cosa fare a Bali | Blog di viaggio Vietnam | Blog di viaggio Cambogia | Blog di viaggio Laos | Blog di viaggio Thailandia | Cosa fare a Saigon | Cosa fare a Bangkok | Cosa fare a Hanoi | Visita a Koh Phi Phi | Cosa fare a Vientiane" — đây chính là block internal-linking SEO đậm đặc, nên biến thành field CMS có thể chỉnh sửa được (không hard-code).
- Copyright + social icons (Facebook, YouTube, Instagram, TikTok) + badge DMCA

**Floating/Sticky elements (không nằm trong footer nhưng luôn hiển thị)**:
- Nút chat WhatsApp nổi góc màn hình
- Nút "Tailor made tour" nổi (icon riêng, link tới Customize Tour)

## 5. Các khối lặp lại toàn site (Global/Shared Sections)

Những block này xuất hiện lại ở gần như mọi trang (Home, Tour Listing, About Us...) → nên làm thành component dùng chung:
- **4 USP icon badges**: Personalizzabile da esperti locali / Garanzia di rimborso / Ottimo rapporto qualità-prezzo / Supporto locale 24/7
- **"Esperienze Autentiche dei Nostri Clienti"** — carousel testimonial: avatar + tên + quốc gia, rating "5.0 Eccellente", quote ngắn, dải ảnh thumbnail (+N ảnh còn lại), nút "Vedi di più"
- **"Autour Asia è altamente raccomandata su"** — 3 card logo (TripAdvisor / Google / Trustpilot) + quote ngắn + link "Leggi altre recensioni"
- **"Domanda Rapida per un Tour"** (Quick Inquiry) — form 2 cột (Nome/Email/Telefono/Indirizzo + textarea message) kèm 2 minh hoạ vẽ tay (xe đạp, người gánh hàng) mang phong cách illustration — đặt gần cuối hầu hết các trang, ngay trước Footer
- **"Uno staff dedicato" (Team grid)** — xuất hiện cả ở Home và About Us, mỗi thẻ có avatar tròn, tên, chức danh, đoạn bio ngắn (2-3 dòng, có "..." dẫn tới trang chi tiết)
- **"Video di esperienze autentiche"** — 1 video lớn bên trái + danh sách video nhỏ bên phải (thumbnail + tiêu đề + ngày)

## 6. So sánh với bản .com (dùng để hiểu 2 domain là cùng 1 hệ thống, khác localization)

Bản .com có route rõ hơn cho About Us (nhiều URL con: `/about/words-of-heart.html`, `/about/mission-vision-core-values.html`...), trong khi bản .it gộp lại thành 1 trang dài. Khi thiết kế **data model, vẫn nên tách theo block** (để tận dụng lại cho cả 2 cách trình bày), nhưng **routing thì tuỳ chọn theo chiến lược SEO của bạn** — xem khuyến nghị ở mục 1.4.
