# Tham chiếu hình ảnh thiết kế (Visual Reference Index)

Thư mục `image_clone/` chứa ảnh chụp màn hình thực tế từ **autourasia.it** (bản tiếng Ý của site tham khảo) — dùng làm ground-truth trực quan cho AI/Cursor khi code UI, bổ sung cho phần mô tả bằng chữ ở các file khác. Khi Cursor đọc bộ docs này, nên **mở kèm các ảnh dưới đây** trước khi code từng trang tương ứng, vì ảnh cho chính xác: bố cục cột, khoảng cách, nhóm thành phần, và những field/filter mà bản mô tả text trước đây có thể mô tả chưa sát 100%.

> Lưu ý: đây là site bản .it (tiếng Ý), giao diện giống hệt bản .com về cấu trúc, chỉ khác nhãn menu do dịch. Bảng đối chiếu nhãn menu ở mục 5 bên dưới.

## 1. Bảng ánh xạ ảnh ↔ loại trang ↔ file spec liên quan

| Ảnh | Trang thực tế | File spec chi tiết tương ứng |
|---|---|---|
| `image_clone/trang-chu.png` | Trang chủ (Home) | `02-page-specs.md` § A |
| `image_clone/danh-muc-tour.png` | Danh mục Tour theo chủ đề + filter ("Tour Vietnam 10 giorni") | `02-page-specs.md` § B |
| `image_clone/danh-muc-blog.png` | Danh mục Blog/Travel Guide theo chủ đề ("Cose da vedere in Cambogia") | `02-page-specs.md` § E |
| `image_clone/noi-dung-blog.png` | Chi tiết bài viết Blog ("Phnom Penh quando visitare?") | `02-page-specs.md` § E |
| `image_clone/personalizza-tour-form.png` | Form "Personalizza il tour" (Customize Tour) | `02-page-specs.md` § K |
| `image_clone/lien-he-contact.png` | Trang Liên hệ (Contact) | `02-page-specs.md` § J |
| `image_clone/ve-chung-toi-about.png` | Trang Chi Siamo (About Us) — bản đầy đủ, gộp cả Mission/Vision/Values/Reasons/Reference persons | `02-page-specs.md` § F |

## 1b. Xem nhanh toàn bộ ảnh (embed trực tiếp)

**Trang chủ** — `image_clone/trang-chu.png`
![Trang chủ](image_clone/trang-chu.png)

**Danh mục Tour** — `image_clone/danh-muc-tour.png`
![Danh mục Tour](image_clone/danh-muc-tour.png)

**Danh mục Blog** — `image_clone/danh-muc-blog.png`
![Danh mục Blog](image_clone/danh-muc-blog.png)

**Nội dung bài Blog** — `image_clone/noi-dung-blog.png`
![Nội dung bài Blog](image_clone/noi-dung-blog.png)

**Form Personalizza il tour** — `image_clone/personalizza-tour-form.png`
![Form Personalizza il tour](image_clone/personalizza-tour-form.png)

**Liên hệ** — `image_clone/lien-he-contact.png`
![Liên hệ](image_clone/lien-he-contact.png)

**Về chúng tôi** — `image_clone/ve-chung-toi-about.png`
![Về chúng tôi](image_clone/ve-chung-toi-about.png)

## 2. Những chi tiết UI quan trọng phát hiện thêm từ ảnh (so với bản mô tả text ban đầu)

Đây là các điểm **cập nhật/bổ sung** so với giả định ban đầu — đã được đưa vào các file spec tương ứng, liệt kê lại ở đây để dễ đối chiếu nhanh:

1. **Header thực tế đơn giản hơn dự kiến ban đầu**: `HOME | DESTINAZIONI ▾ | CROCIERE ▾ | COSE DA FARE ▾ | CHI SIAMO | CONTATTACI | 🔍 | [Personalizza il tour] | cờ ngôn ngữ`. "COSE DA FARE" chính là mục **Travel Guide/Blog**, không tách riêng như sitemap gốc .com.
2. **Trang chủ có thanh chọn nhanh loại tour** ngay trên hero (pill button: "Tour Vietnam 10 giorni / Vietnam 2 settimane / Vietnam 15 giorni") — đây là shortcut UI mới, kết hợp với ô tìm kiếm Destinazione + Durata đặt đè lên ảnh hero.
3. **"I tour più richiesti"** (Best-seller) ở home chỉ show **3 card**, không phải 4 như giả định trước.
4. **"Le destinazioni più amate"** ở home dùng **mosaic grid không đều** (bento layout: 1 ô lớn bên trái, các ô nhỏ hơn xếp phải) chứ không phải grid đều 6 cột.
5. **Tour Listing có bộ filter phong phú hơn nhiều**: ngoài "Durate" còn có nhóm **"Stile di viaggio"** (Tour di lunga durata, Molti patrimoni, Natura e homestay, Cultura e storia, Vacanza equilibrata, Vacanza al mare, Luna di miele, Vacanze in famiglia, Escursioni e trekking, Tour combinato multi-paesi, Tour per piccoli gruppi) — đây là facet quan trọng cần thêm vào data model.
6. **Card tour trong Listing** có review trích dẫn dạng "quote" (italic, có icon dấu ngoặc kép) ngay trong card, không chỉ ở review riêng.
7. **Blog/Travel Guide có kiến trúc riêng biệt & phức tạp hơn tưởng tượng ban đầu**:
   - Trang danh mục blog có sidebar: "Categorie del blog" (theo thành phố/điểm đến, dạng list cuộn), "Filtra articoli" (tag button theo loại nội dung: Dove mangiare e bere / Dove dormire / Cosa fare e vedere / Consigli di viaggio / Com'è stato il viaggio / Quale tour scegliere), "Mots-clés populaires" (tag cloud từ khóa SEO).
   - Trang chi tiết bài viết có: gallery ảnh đầu bài, **"Sommario dell'articolo"** (mục lục tự động từ heading), khối "Vedi di più:" (internal link list giữa bài), rating cuối bài, **nút share mạng xã hội**, **form bình luận** (Nome, Email, Telefono, Commento), block "Blog uguale" (bài liên quan).
8. **Trang About Us (Chi siamo) đầy đủ hơn nhiều so với giả định "static page đơn giản"** — thực chất là trang tổng hợp gồm nhiều block:
   - "Viaggio autentico" (intro + số giấy phép lữ hành)
   - "Uno staff dedicato" (team grid, có đoạn bio ngắn dưới mỗi người)
   - "La nostra missione" / "la nostra visione" — 2 block ảnh biển gỗ khắc chữ
   - **"Impegno nei valori fondamentali"** — sơ đồ vòng tròn 4 giá trị cốt lõi (Dedizione, Empatia, Sincerità, Responsabilità), mỗi giá trị có icon check + mô tả ngắn, bố trí quanh 1 vòng tròn trung tâm
   - "Politica di vendita" (điều khoản, ví dụ giảm giá trẻ em)
   - "Perché scegliere noi?" (danh sách lý do + ảnh)
   - **"I nostri referenti dall'estero"** (Reference persons abroad) — card gồm ảnh, tên, email, phone, Skype/liên hệ
9. **Form "Personalizza il tour" (Customize Tour) chi tiết hơn nhiều** so với mô tả ban đầu — đây là form quan trọng nhất site, có cấu trúc 3 khối rõ ràng:
   - Khối 1 "Le tue informazioni di viaggio": số lượng khách (Adulti/bambini/neonati — **dùng stepper +/- chứ không phải input số thường**), số ngày mong muốn (text), ngày dự kiến đến (date picker), quốc gia muốn đi (checkbox đa chọn: Vietnam/Thailandia/Cambogia/Laos/Bali), loại khách sạn (checkbox: 3*/4*/5*/Mi consigliate — "để chúng tôi tư vấn"), ngân sách dự kiến (input số + currency icon + dropdown "Per persona/Per gruppo")
   - Khối 2 "Le tue informazioni personali": giới tính (radio Signore/Signora), Nome/Cognome, Email/Telefono, Nazionalità (dropdown), Città (text)
   - Khối 3 "Altre esigenze particolari": textarea tự do
   - Cuối form có dòng cam kết thời gian phản hồi ("liên hệ trong 24h làm việc") + nút CTA lớn
10. **Trang Contact thực tế tối giản hơn dự kiến**: chỉ có 1 form ngắn (Nome, Email, Telefono, Indirizzo, Messaggio) + thông tin liên hệ text, **không có Google Map embed hiển thị trong ảnh** (có thể có nhưng không nằm trong phần chụp, không nên giả định bắt buộc phải có).
11. **QR code WhatsApp trong Footer** — Footer có ô QR code riêng để quét chat Whatsapp nhanh, đặt cạnh block liên hệ, ngoài nút floating WhatsApp.
12. **Footer 4 cột cố định nội dung**: "Autour Asia" (Chi siamo/Testimonianze/I clienti parlano di noi/Il nostro team/Richiedi preventivo gratuito), "Tour altamente consigliati" (danh sách duration phổ biến), "Le migliori destinazioni" (danh sách điểm đến hot), "Cose da sapere" (câu hỏi/chủ đề SEO) — **+ 1 hàng link rời bên dưới** kiểu "Cosa fare a Bali | Blog di viaggio Vietnam | ... " (internal link ngang, không có tiêu đề cột, đây chính là block SEO liên kết nội bộ dày đặc).

## 3. Cách Cursor nên dùng ảnh khi code

Với mỗi trang, thực hiện theo thứ tự:
1. Đọc phần mô tả text ở `02-page-specs.md` (mục tương ứng)
2. Mở ảnh tương ứng trong bảng ở mục 1 để đối chiếu bố cục thật (khoảng cách, số cột, vị trí sidebar trái/phải, breakpoint)
3. Đối chiếu component cần dùng ở `04-design-system.md`
4. Đối chiếu field dữ liệu cần ở `03-data-models.md`

Prompt mẫu cho Cursor:
> "Xem ảnh `docs/image_clone/danh-muc-tour.png` và đọc mục B trong `docs/02-page-specs.md`. Hãy code component `TourListingPage` bám sát bố cục trong ảnh: sidebar filter bên trái ~280px (Durate + Stile di viaggio dạng checkbox, nút Annulla/Applica), danh sách card bên phải dạng list ngang (ảnh trái, nội dung phải), có badge 'Top 2 - 2026' và dropdown 'Ordina per: Popolarità' ở góc phải trên danh sách."

## 4. Giới hạn của ảnh tham chiếu (Cursor cần lưu ý)

- Ảnh chỉ chụp được **phần trên/giữa** của một số trang dài (VD ảnh `trang-chu.png` không chắc chắn đã hết toàn bộ chiều dài trang) — vẫn ưu tiên nội dung đầy đủ đã liệt kê trong `02-page-specs.md`, ảnh chỉ để tham chiếu bố cục & phong cách hình ảnh.
- Đây là bản **.it (tiếng Ý)** — khi build, nhãn/copy text nên viết bằng ngôn ngữ dự án thực tế của bạn (Việt/Anh...), chỉ giữ nguyên **layout & UX pattern**, không copy nguyên văn nội dung tiếng Ý.
- Không copy logo "Autour Asia", màu thương hiệu chính xác, ảnh chụp thật, hay nội dung bài viết — chỉ dùng để tham chiếu cấu trúc.

## 5. Đối chiếu nhãn menu bản .it → tên gọi chuẩn hoá dùng trong docs

| Nhãn trên site .it | Tương đương trong docs (tiếng Việt) |
|---|---|
| HOME | Trang chủ |
| DESTINAZIONI | Tours (theo destination/quốc gia) |
| CROCIERE | Cruises (Du thuyền) |
| COSE DA FARE | Travel Guide / Blog |
| CHI SIAMO | About Us (Về chúng tôi) |
| CONTATTACI | Contact (Liên hệ) |
| Personalizza il tour | Customize Tour (nút CTA nổi bật) |
| Domanda Rapida per un Tour | Quick Tour Inquiry (form nhanh cuối trang) |
