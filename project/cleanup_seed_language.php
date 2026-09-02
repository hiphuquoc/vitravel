<?php
/**
 * Dọn nội dung seed: trường `vi` và copy tiếng Việt thuần — bỏ jargon EN lẫn lộn.
 * Run: php project/cleanup_seed_language.php
 */
declare(strict_types=1);

$dir = __DIR__;
$files = glob($dir.'/seed_*.php') ?: [];
$files = array_values(array_filter($files, fn ($f) => ! str_ends_with($f, 'seed_vitravel.php')));

/** @var array<string, string> */
$replacements = [
    // ── Theme duration (chung 11 hub) ──
    "'vi' => 'Lọc theo thời lượng — khác trang danh mục GEO từng vùng.'" =>
        "'vi' => 'Lọc theo thời lượng — khác trang danh mục theo từng vùng.'",
    "'vi' => 'Sweet spot 2N1D — lọc theo số ngày trên hub.'" =>
        "'vi' => 'Lựa chọn phổ biến 2 ngày 1 đêm — lọc theo số ngày.'",
    "'vi' => 'Gói 3N2D phổ biến — không trùng zone GEO.'" =>
        "'vi' => 'Gói 3 ngày 2 đêm phổ biến — không trùng danh mục theo vùng.'",
    "'vi' => 'Lịch 4N3D — bộ lọc thời lượng tại hub.'" =>
        "'vi' => 'Lịch 4 ngày 3 đêm — lọc theo thời lượng.'",
    "'vi' => 'Tour dài & combo — insight thời lượng, không phải trang GEO.'" =>
        "'vi' => 'Tour dài và combo — lọc theo thời lượng, không trùng danh mục vùng.'",
    "'vi' => 'Sweet spot cho khách Hà Nội cuối tuần.'" =>
        "'vi' => 'Lựa chọn phổ biến cho khách Hà Nội cuối tuần.'",
    "'vi' => 'Sweet spot cho khách Sài Gòn cuối tuần.'" =>
        "'vi' => 'Lựa chọn phổ biến cho khách Sài Gòn cuối tuần.'",
    "'vi' => 'Sweet spot VND 2.5–6M — gia đình & lần đầu ngủ vịnh.'" =>
        "'vi' => 'Mức giá 2,5–6 triệu — gia đình và lần đầu ngủ trên vịnh.'",

    // ── Theme insight (Hà Giang + chung) ──
    "'vi' => 'Trải nghiệm signature cho người có kinh nghiệm đèo.'" =>
        "'vi' => 'Trải nghiệm đặc trưng cho người có kinh nghiệm đèo.'",
    "'vi' => 'Loop 2–3 ngày, xe đêm HN — sweet spot dân văn phòng.'" =>
        "'vi' => 'Loop 2–3 ngày, xe đêm Hà Nội — phù hợp dân văn phòng.'",
    "'vi' => 'Bộ lọc intent khách Hà Nội — không trùng danh mục GEO từng huyện.'" =>
        "'vi' => 'Bộ lọc khách Hà Nội — không trùng danh mục theo từng huyện.'",
    "'vi' => 'Peak season — đặt xe và homestay trước 3–4 tuần.'" =>
        "'vi' => 'Mùa cao điểm — đặt xe và homestay trước 3–4 tuần.'",

    // ── Sa Pa / Tam Đảo (duration cũ + theme intent) ──
    "'vi' => 'Duration theme — khác URL zone fansipan hay muong-hoa. Phù hợp ghép weekend 2N1D.'" =>
        "'vi' => 'Tour theo thời lượng — khác trang Fansipan hay Mường Hoa. Phù hợp ghép chuyến cuối tuần 2 ngày 1 đêm.'",
    "'vi' => 'Duration theme — khác URL zone thac-bac hay vqg. Phù hợp ghép weekend 2N1D.'" =>
        "'vi' => 'Tour theo thời lượng — khác trang Thác Bạc hay VQG. Phù hợp ghép chuyến cuối tuần 2 ngày 1 đêm.'",
    "'vi' => 'Trekking nhiều ngày — bổ sung cho zone GEO homestay (địa lý) bằng intent duration.'" =>
        "'vi' => 'Trekking nhiều ngày — bổ sung danh mục homestay theo thời lượng.'",
    "'vi' => 'Trekking nhiều ngày — bổ sung cho zone GEO vqg (địa lý) bằng intent duration.'" =>
        "'vi' => 'Trekking nhiều ngày — bổ sung danh mục VQG theo thời lượng.'",
    "'vi' => 'Intent URL riêng — không trùng zone GEO thị trấn. Combo tàu đêm + Fansipan phổ biến dân văn phòng HN (~320km).'" =>
        "'vi' => 'Trang riêng cuối tuần từ Hà Nội — không trùng danh mục thị trấn. Combo tàu đêm + Fansipan phổ biến dân văn phòng (~320km).'",
    "'vi' => 'Intent URL riêng — không trùng zone GEO thị trấn. Combo limo + Thác Bạc phổ biến dân văn phòng HN (~80km).'" =>
        "'vi' => 'Trang riêng cuối tuần từ Hà Nội — không trùng danh mục thị trấn. Combo limo + Thác Bạc phổ biến dân văn phòng (~80km).'",
    "'vi' => 'Theme intent — URL riêng zone fansipan-sun-world (GEO) vs category fansipan (intent).'" =>
        "'vi' => 'Trang chủ đề Fansipan — khác danh mục khu Fansipan Sun World.'",
    "'vi' => 'Theme intent — URL riêng zone thi-tran-sapa (GEO) vs category ẩm thực (intent).'" =>
        "'vi' => 'Trang chủ đề ẩm thực — khác danh mục thị trấn Sa Pa.'",
    "'vi' => 'Theme intent — URL riêng zone suoi-ca-am-thuc (GEO) vs category ẩm thực (intent).'" =>
        "'vi' => 'Trang chủ đề ẩm thực — khác danh mục suối cá.'",
    "'vi' => 'Adventure intent — HLV H\\'Mông/Tày bản địa, tôn trọng phong tục bản làng.'" =>
        "'vi' => 'Tour phiêu lưu — HLV H\\'Mông/Tày bản địa, tôn trọng phong tục bản làng.'",
    "'vi' => 'Adventure intent — HLV địa phương, không xả rác trong rừng.'" =>
        "'vi' => 'Tour phiêu lưu — HLV địa phương, không xả rác trong rừng.'",
    "'vi' => 'Romance intent — mini trăng mật không cần bay xa từ HN.'" =>
        "'vi' => 'Tour lãng mạn — kỳ nghỉ ngắn không cần bay xa từ Hà Nội.'",
    "'vi' => 'Family intent — không trek nặng, ưu tiên cáp treo & tàu hoả.'" =>
        "'vi' => 'Tour gia đình — không trek nặng, ưu tiên cáp treo và tàu leo núi.'",
    "'vi' => 'Family intent — không trek nặng, ưu tiên suối cá & phố đi bộ.'" =>
        "'vi' => 'Tour gia đình — không trek nặng, ưu tiên suối cá và phố đi bộ.'",
    "'vi' => 'Ghép vào chuyến 2N1D cuối tuần — slot chiều/tối.'" =>
        "'vi' => 'Ghép vào chuyến 2 ngày 1 đêm cuối tuần — khung chiều/tối.'",
    "'vi' => 'Tàu đêm SP/CH, 2N1D & tour ngày.'" =>
        "'vi' => 'Tàu đêm SP/CH, 2 ngày 1 đêm và tour trong ngày.'",
    "'vi' => 'Limousine 2–2.5h, 2N1D & tour ngày.'" =>
        "'vi' => 'Limousine 2–2,5 giờ, 2 ngày 1 đêm và tour trong ngày.'",

    // ── Mũi Né ──
    "'vi' => 'Jeep đỏ, trắng, Suối Tiên — half hoặc full day.'" =>
        "'vi' => 'Jeep đỏ, trắng, Suối Tiên — nửa ngày hoặc cả ngày.'",
    "'vi' => 'Tour ngày đồi cát Mũi Né: jeep 150–300k, tour full 400–900k — khác resort overnight.'" =>
        "'vi' => 'Tour ngày đồi cát Mũi Né: jeep 150–300k, tour trọn ngày 400–900k — khác gói resort qua đêm.'",
    "'vi' => 'Gói 2–3 ngày resort strip — phân biệt tour ngày SGN một ngày.'" =>
        "'vi' => 'Gói 2–3 ngày dải resort — phân biệt tour ngày từ Sài Gòn.'",
    "'vi' => 'Tour ngày SGN ~1.1M+ — limo riêng 250–350k/chiều.'" =>
        "'vi' => 'Tour ngày từ Sài Gòn ~1,1 triệu+ — limo riêng 250–350k/chiều.'",
    "'vi' => 'Weekend 2N1D từ SGN'" =>
        "'vi' => 'Cuối tuần 2 ngày 1 đêm từ Sài Gòn'",
    "'vi' => 'Alternative bay SGN — Cam Ranh airport + Mũi Né.'" =>
        "'vi' => 'Phương án bay thay Sài Gòn — sân bay Cam Ranh + Mũi Né.'",
    "'vi' => 'SEO zone doi-cat-do — không nhầm với Ham Tien resort hub.'" =>
        "'vi' => 'Trang chủ đề đồi cát — không nhầm với danh mục resort Ham Tiến.'",
    "'vi' => 'Đỏ sunrise, trắng Bàu Trắng, jeep private.'" =>
        "'vi' => 'Đồi đỏ bình minh, Bàu Trắng, jeep riêng.'",
    "'vi' => 'Thiên đường kite Việt Nam — gói 3N2D từ 4.2M tham khảo.'" =>
        "'vi' => 'Thiên đường kite Việt Nam — gói 3 ngày 2 đêm từ 4,2 triệu tham khảo.'",
    "'vi' => 'Phân khúc SGN weekend gia đình — resort 4* + jeep nhẹ.'" =>
        "'vi' => 'Phân khúc gia đình cuối tuần từ Sài Gòn — resort 4 sao + jeep nhẹ.'",
    "'vi' => 'Zone ket-hop-sai-gon — anti-cannibalize vs Ham Tien resort only.'" =>
        "'vi' => 'Combo từ Sài Gòn — tách biệt với danh mục resort Ham Tiến.'",
    "'vi' => 'Ham Tien + jeep + kite (tuỳ chọn).'" =>
        "'vi' => 'Ham Tiến + jeep + kite (tùy chọn).'",

    // ── Hạ Long ──
    "'vi' => 'Du thuyền 5 sao Hạ Long 7–12M+ — sản phẩm chính trên cruises hub, không nhầm tour ngày.'" =>
        "'vi' => 'Du thuyền 5 sao Hạ Long 7–12 triệu+ — xem mục du thuyền, không nhầm tour ngày.'",
    "'vi' => 'Tour ngày khác du thuyền: tàu gỗ mui che, về bờ 16h — xem cruises hub nếu cần cabin.'" =>
        "'vi' => 'Tour ngày khác du thuyền: tàu gỗ mui che, về bờ 16h — xem mục du thuyền nếu cần cabin.'",
    "'vi' => 'Phân khúc khách nội địa weekend: Sun World, tour ngày, trẻ em.'" =>
        "'vi' => 'Phân khúc khách nội địa cuối tuần: Sun World, tour ngày, trẻ em.'",

    // ── Cần Thơ / Đà Lạt / Cát Bà ──
    "'vi' => 'Trải nghiệm signature của Cần Thơ.'" =>
        "'vi' => 'Trải nghiệm đặc trưng của Cần Thơ.'",
    "'vi' => 'Agri-tourism signature của Lâm Đồng.'" =>
        "'vi' => 'Du lịch nông nghiệp đặc trưng Lâm Đồng.'",
    "'vi' => 'Rừng nguyên sinh, Ao Ếch và làng Việt Hải — từ nửa ngày tới overnight.'" =>
        "'vi' => 'Rừng nguyên sinh, Ao Ếch và làng Việt Hải — từ nửa ngày tới qua đêm.'",

    // ── Zone taglines ──
    "'vi' => 'Tour ngày & weekend 2N1D từ HN — tàu/limo ~320km'" =>
        "'vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — tàu/limo ~320km'",
    "'vi' => 'Tour ngày & weekend 2N1D từ HN — limo ~80km'" =>
        "'vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — limo ~80km'",
    "'vi' => 'Tour ngày & weekend 2N1D từ HCMC — limo 4–5h'" =>
        "'vi' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Sài Gòn — limo 4–5 giờ'",
    "'vi' => 'Tour ngày hoặc 2N1D từ thủ đô — limousine 3–4 giờ'" =>
        "'vi' => 'Tour ngày hoặc 2 ngày 1 đêm từ thủ đô — limousine 3–4 giờ'",
    "'vi' => 'Nguyễn Đình Chiểu — resort strip chính, kite school & bãi biển'" =>
        "'vi' => 'Nguyễn Đình Chiểu — dải resort chính, trường kite và bãi biển'",
    "'vi' => 'Sunrise jeep — điểm chụp iconic Mũi Né'" =>
        "'vi' => 'Jeep bình minh — điểm chụp biểu tượng Mũi Né'",
    "'vi' => 'Sa mạc mini ~30km — trượt cát, hồ Bàu Trắng'" =>
        "'vi' => 'Sa mạc nhỏ ~30km — trượt cát, hồ Bàu Trắng'",

    // ── Tour copy (không i18n) ──
    'HDV foodie' => 'HDV am thực',
    'dẫn ăn 6–8 món đặc sản với HDV foodie' => 'dẫn ăn 6–8 món đặc sản với HDV am thực',
    'HDV foodie dẫn quán địa phương' => 'HDV am thực dẫn quán địa phương',
    'Hoạt động signature của Đà Lạt' => 'Hoạt động đặc trưng của Đà Lạt',
    'Hoạt động signature Tam Đảo' => 'Hoạt động đặc trưng Tam Đảo',
    'Hoạt động signature Sa Pa' => 'Hoạt động đặc trưng Sa Pa',
    'Phân khúc budget phổ biến khách nội địa weekend' => 'Phân khúc giá phổ biến khách nội địa cuối tuần',
    'Cruise chiều: nhạc nhẹ, đồ uống, ngắm mặt trời lặn — không overnight.' =>
        'Du thuyền chiều: nhạc nhẹ, đồ uống, ngắm hoàng hôn — không qua đêm trên tàu.',
  "có những \"peak season\" rõ ràng" => 'có những mùa cao điểm rõ ràng',
    'Gói weekend loop' => 'Gói loop cuối tuần',
    'không chỉ strip resort' => 'không chỉ dải resort',

    // ── Service catalog seo_body (tiếng Việt) ──
    "'seo_body' => 'Xem cruises hub — không overnight.'" =>
        "'seo_body' => 'Xem mục du thuyền — không qua đêm trên tàu.'",
    "'seo_body' => 'Xem cruises hub — suối cá, picnic hồ.'" =>
        "'seo_body' => 'Xem mục du thuyền — suối cá, picnic hồ.'",

    // ── Pass 2: tagline plain, reviews, itinerary, services ──
    "'tagline' => 'Tour ngày & weekend 2N1D từ HN — limo ~80km'" =>
        "'tagline' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — limo ~80km'",
    "'tagline' => 'Tour ngày & weekend 2N1D từ HN — tàu/limo ~320km'" =>
        "'tagline' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Hà Nội — tàu/limo ~320km'",
    "'tagline' => 'Tour ngày & weekend 2N1D từ HCMC — limo 4–5h'" =>
        "'tagline' => 'Tour ngày và cuối tuần 2 ngày 1 đêm từ Sài Gòn — limo 4–5 giờ'",
    "'tagline' => 'Tour ngày hoặc 2N1D từ thủ đô — limousine 3–4 giờ'" =>
        "'tagline' => 'Tour ngày hoặc 2 ngày 1 đêm từ thủ đô — limousine 3–4 giờ'",
    "'quote' => 'Điểm \"Xuất sắc\" — đặc biệt combo 2N1D từ Hà Nội và hỗ trợ đổi lịch khi sương mù dày.'" =>
        "'quote' => 'Điểm \"Xuất sắc\" — đặc biệt combo 2 ngày 1 đêm từ Hà Nội và hỗ trợ đổi lịch khi sương mù dày.'",
    'Mệt hơn 2N1D — khuyên cuối tuần nghỉ lại.' => 'Mệt hơn 2 ngày 1 đêm — khuyên cuối tuần nghỉ lại.',
    'trải nghiệm signature Tam Đảo' => 'trải nghiệm đặc trưng Tam Đảo',
    'trải nghiệm signature Sa Pa' => 'trải nghiệm đặc trưng Sa Pa',
    'ghép vào chuyến 2N1D cuối tuần' => 'ghép vào chuyến 2 ngày 1 đêm cuối tuần',
    "'quote' => '2N1D cuối tuần từ HN vừa đủ" => "'quote' => '2 ngày 1 đêm cuối tuần từ Hà Nội vừa đủ",
    "'trip' => 'Tam Đảo 2N1D cuối tuần'" => "'trip' => 'Tam Đảo 2 ngày 1 đêm cuối tuần'",
    "'trip' => 'Tam Đảo 3N2D tổng quan'" => "'trip' => 'Tam Đảo 3 ngày 2 đêm tổng quan'",
    'Thiết kế tour 2N1D HN được đánh giá' => 'Thiết kế tour 2 ngày 1 đêm từ Hà Nội được đánh giá',
    "array('label' => 'Tam Đảo 2N1D cuối tuần'" => "array('label' => 'Tam Đảo 2 ngày 1 đêm cuối tuần'",
    "array('label' => 'Tam Đảo 3N2D tổng quan'" => "array('label' => 'Tam Đảo 3 ngày 2 đêm tổng quan'",
    "'title' => 'Fish stream boat'" => "'title' => 'Thuyền suối cá'",
    "'title' => 'Morning lake picnic'" => "'title' => 'Picnic sáng bên hồ'",
    "'title' => 'Lake paddle'" => "'title' => 'Chèo kayak trên hồ'",
    "'title' => 'Boat Nho Que'" => "'title' => 'Thuyền sông Nho Quế'",
    "'title' => 'Morning boat'" => "'title' => 'Thuyền buổi sáng'",
    "'title' => 'Morning paddle'" => "'title' => 'Chèo kayak buổi sáng'",
    "'summary' => 'Weekend 2N1D — tiết kiệm hơn 2 chiều lẻ.'" => "'summary' => 'Cuối tuần 2 ngày 1 đêm — tiết kiệm hơn 2 chiều lẻ.'",
    "'summary' => 'Slot 16h–18h — combo weekend phổ biến.'" => "'summary' => 'Khung 16h–18h — combo cuối tuần phổ biến.'",
    "'highlights' => ['Weekend slot']" => "'highlights' => ['Khung cuối tuần']",
    "'location_label' => 'HN ↔ Tam Đảo'" => "'location_label' => 'Hà Nội ↔ Tam Đảo'",
    "'location_label' => 'HN → Tam Đảo'" => "'location_label' => 'Hà Nội → Tam Đảo'",
    "'title' => 'Limousine T6 chiều HN → Tam Đảo'" => "'title' => 'Limousine thứ 6 chiều Hà Nội → Tam Đảo'",
    "'title' => 'Weekend Phan Thiết — Phú Quý 2 ngày 1 đêm'" => "'title' => 'Cuối tuần Phan Thiết — Phú Quý 2 ngày 1 đêm'",
    "array('label' => 'Weekend Phan Thiết — Phú Quý 2N1Đ'" => "array('label' => 'Cuối tuần Phan Thiết — Phú Quý 2 ngày 1 đêm'",
    "'title' => 'City tour Tam Đảo" => "'title' => 'Tour thành phố Tam Đảo",
    "'title' => 'City tour Sa Pa" => "'title' => 'Tour thành phố Sa Pa",
    "'title' => 'City tour Đà Lạt" => "'title' => 'Tour thành phố Đà Lạt",
    "'title' => 'City tour Cần Thơ" => "'title' => 'Tour thành phố Cần Thơ",
    "'title' => 'City tour Hà Giang" => "'title' => 'Tour thành phố Hà Giang",
    "'title' => 'City tour trọn ngày'" => "'title' => 'Tour thành phố trọn ngày'",
    "'title' => 'City tour — Tiễn'" => "'title' => 'Tour thành phố — Tiễn'",
    "'title' => 'Food tour ẩm thực Tam Đảo" => "'title' => 'Tour ẩm thực Tam Đảo",
    "'title' => 'Food tour ẩm thực Sa Pa" => "'title' => 'Tour ẩm thực Sa Pa",
    "'title' => 'Food tour Hà Giang" => "'title' => 'Tour ẩm thực Hà Giang",
    "'title' => 'Food tour Cần Thơ" => "'title' => 'Tour ẩm thực Cần Thơ",
    "'title' => 'Food tour chợ đêm Đà Lạt" => "'title' => 'Tour ẩm thực chợ đêm Đà Lạt",
    "'title' => 'Food tour Phan Thiết" => "'title' => 'Tour ẩm thực Phan Thiết",
    "'title' => 'Food tour tối'" => "'title' => 'Tour ẩm thực tối'",
    "'title' => 'Food tour'" => "'title' => 'Tour ẩm thực'",
    "'title' => 'Photo tour sương mù Tam Đảo" => "'title' => 'Tour chụp ảnh sương mù Tam Đảo",
    "'title' => 'Photo tour ruộng bậc thang" => "'title' => 'Tour chụp ảnh ruộng bậc thang",
    "'title' => 'Photo tour sương mù — photographer 4h'" => "'title' => 'Tour chụp ảnh sương mù — photographer 4 giờ'",
    "'title' => 'Photo tour'" => "'title' => 'Tour chụp ảnh'",
    "'title' => 'Food tour gà đồi & suối cá'" => "'title' => 'Tour ẩm thực gà đồi & suối cá'",
    "'title' => 'Food tour thắng cố & cá hồi'" => "'title' => 'Tour ẩm thực thắng cố & cá hồi'",
    "'meals' => 'Snack'" => "'meals' => 'Đồ ăn nhẹ'",
    "'notes' => ['Nov–Feb best.']" => "'notes' => ['Tháng 11–2 đẹp nhất.']",
    'Gói weekend loop — đi tối thứ 6' => 'Gói loop cuối tuần — đi tối thứ 6',

    // ── Pass 2b: plain VN fields (không bọc vi/en) ──
    "'quote' => 'Điểm \"Xuất sắc\" — đặc biệt combo 2N1D tàu đêm từ Hà Nội và hỗ trợ đổi lịch khi sương dày.'" =>
        "'quote' => 'Điểm \"Xuất sắc\" — đặc biệt combo 2 ngày 1 đêm tàu đêm từ Hà Nội và hỗ trợ đổi lịch khi sương dày.'",
    "'quote' => '2N1D cuối tuần tàu SP vừa đủ — Fansipan trong sương và lẩu cá hồi tối là nhớ nhất.'" =>
        "'quote' => '2 ngày 1 đêm cuối tuần tàu SP vừa đủ — Fansipan trong sương và lẩu cá hồi tối là nhớ nhất.'",
    "'trip' => 'Sa Pa 2N1D cuối tuần'" => "'trip' => 'Sa Pa 2 ngày 1 đêm cuối tuần'",
    "'trip' => 'Sa Pa 2N1D lãng mạn'" => "'trip' => 'Sa Pa 2 ngày 1 đêm lãng mạn'",
    "'achievements' => array('Thiết kế tour 2N1D tàu đêm được đánh giá 4.9/5')" =>
        "'achievements' => array('Thiết kế tour 2 ngày 1 đêm tàu đêm được đánh giá 4.9/5')",
    "array('label' => 'Sa Pa 2N1D cuối tuần'" => "array('label' => 'Sa Pa 2 ngày 1 đêm cuối tuần'",
    "array('label' => 'Sa Pa 3N2D tổng quan'" => "array('label' => 'Sa Pa 3 ngày 2 đêm tổng quan'",
    "'seo_body' => 'Cửa ngõ chính từ HN — :brand giá cabin 4/6 người. Shuttle 38km lên Sa Pa đặt kèm.'" =>
        "'seo_body' => 'Cửa ngõ chính từ Hà Nội — :brand giá cabin 4/6 người. Shuttle 38km lên Sa Pa đặt kèm.'",
    "'location_label' => 'HN → Lào Cai'" => "'location_label' => 'Hà Nội → Lào Cai'",
    "'location_label' => 'HN ↔ Lào Cai'" => "'location_label' => 'Hà Nội ↔ Lào Cai'",
    "'location_label' => 'HN → Sa Pa'" => "'location_label' => 'Hà Nội → Sa Pa'",
    "'summary' => 'Weekend 2N1D — tiết kiệm hơn 2 chiều lẻ.'" => "'summary' => 'Cuối tuần 2 ngày 1 đêm — tiết kiệm hơn 2 chiều lẻ.'",
    "'highlights' => ['Private', 'Gia đình']" => "'highlights' => ['Xe riêng', 'Gia đình']",
    "'highlights' => ['Tàu SP nhanh', 'Cabin 4/6', 'Cuối tuần hot']" => "'highlights' => ['Tàu SP nhanh', 'Cabin 4/6', 'Cuối tuần đông khách']",
    "'badge' => 'SGN weekend'" => "'badge' => 'Cuối tuần Sài Gòn'",
    "'highlightsIntro' => 'Weekend phổ biến khách SGN: limousine khứ hồi + resort + jeep sunrise.'" =>
        "'highlightsIntro' => 'Cuối tuần phổ biến khách Sài Gòn: limousine khứ hồi + resort + jeep bình minh.'",
    "'title' => 'SGN — Mũi Né'" => "'title' => 'Sài Gòn — Mũi Né'",
    "'title' => 'SGN — Mũi Né — SGN'" => "'title' => 'Sài Gòn — Mũi Né — Sài Gòn'",
    "'title' => 'Gia đình Mũi Né 2N1D — Resort, biển & jeep nhẹ'" =>
        "'title' => 'Gia đình Mũi Né 2 ngày 1 đêm — Resort, biển & jeep nhẹ'",
    "'title' => 'Trăng mật Mũi Né 3N2D — Resort boutique & private jeep'" =>
        "'title' => 'Trăng mật Mũi Né 3 ngày 2 đêm — Resort boutique & jeep riêng'",
    "'title' => 'Từ Sài Gòn đi Mũi Né thế nào? Limousine, xe khách & mẹo weekend'" =>
        "'title' => 'Từ Sài Gòn đi Mũi Né thế nào? Limousine, xe khách & mẹo cuối tuần'",
    "'excerpt' => 'Mũi Né không có sân bay — 99% khách SGN dùng limousine/xe khách 4–5 giờ. So sánh giá 250–350k/chiều và mẹo đi weekend.'" =>
        "'excerpt' => 'Mũi Né không có sân bay — 99% khách Sài Gòn dùng limousine/xe khách 4–5 giờ. So sánh giá 250–350k/chiều và mẹo đi cuối tuần.'",
    "'trip' => 'SGN — Mũi Né 2N1D'" => "'trip' => 'Sài Gòn — Mũi Né 2 ngày 1 đêm'",
    "'trip' => 'Gia đình 2N1D'" => "'trip' => 'Gia đình 2 ngày 1 đêm'",
    "'quote' => 'Weekend SGN — limo đón đúng giờ, jeep đồi cát đỏ sunrise đẹp tuyệt.'" =>
        "'quote' => 'Cuối tuần Sài Gòn — limo đón đúng giờ, jeep đồi cát đỏ bình minh đẹp tuyệt.'",
    "'description' => 'Weekend 2N1D du thuyền tiêu chuẩn, cáp treo Sun World Tuần Châu, tour ngày tuyến 1 — phù hợp khách nội địa và gia đình có trẻ em.'" =>
        "'description' => 'Cuối tuần 2 ngày 1 đêm du thuyền tiêu chuẩn, cáp treo Sun World Tuần Châu, tour ngày tuyến 1 — phù hợp khách nội địa và gia đình có trẻ em.'",
    "'highlightsIntro' => 'Tour ngày trên tàu gỗ — sản phẩm cruises hub cho khách không ở lại đêm, giá 800k–1.5M.'" =>
        "'highlightsIntro' => 'Tour ngày trên tàu gỗ — mục du thuyền cho khách không ở lại đêm, giá 800k–1,5 triệu.'",
    "'quote' => 'Du thuyền 4 sao 2N1D đúng kỳ vọng — Titop đẹp, limousine đón đúng giờ.'" =>
        "'quote' => 'Du thuyền 4 sao 2 ngày 1 đêm đúng kỳ vọng — Titop đẹp, limousine đón đúng giờ.'",
    "'trip' => 'Du thuyền 4 sao 2N1D'" => "'trip' => 'Du thuyền 4 sao 2 ngày 1 đêm'",
    "'trip' => 'Gia đình Bãi Cháy 2N1D'" => "'trip' => 'Gia đình Bãi Cháy 2 ngày 1 đêm'",
    "array('label' => 'Du thuyền 5 sao 2N1D'" => "array('label' => 'Du thuyền 5 sao 2 ngày 1 đêm'",
    "'text' => 'Cá suối ăn ngay trên thuyền — trải nghiệm signature Tam Đảo.'" =>
        "'text' => 'Cá suối ăn ngay trên thuyền — trải nghiệm đặc trưng Tam Đảo.'",
    "'text' => 'Cáp treo ba đoạn lên 3143m — trải nghiệm signature Sa Pa.'" =>
        "'text' => 'Cáp treo ba đoạn lên 3143m — trải nghiệm đặc trưng Sa Pa.'",
    "'text' => 'Câu mực đêm trên biển — trải nghiệm signature buổi tối Mũi Né.'" =>
        "'text' => 'Câu mực đêm trên biển — trải nghiệm đặc trưng buổi tối Mũi Né.'",
    "'subtitle' => 'Chinh phục đỉnh 3143m — trải nghiệm núi signature Sa Pa.'" =>
        "'subtitle' => 'Chinh phục đỉnh 3143m — trải nghiệm núi đặc trưng Sa Pa.'",
    "'seo_body' => 'Cáp treo Fansipan & tàu Mười Mây Sun World — trải nghiệm núi signature từ :brand.'" =>
        "'seo_body' => 'Cáp treo Fansipan & tàu Mười Mây Sun World — trải nghiệm núi đặc trưng từ :brand.'",
    "'description' => 'Cruises hub là tàu qua đêm có cabin; tour ngày tách riêng — tránh nhầm tàu gỗ với du thuyền luxury.'" =>
        "'description' => 'Mục du thuyền là tàu qua đêm có cabin; tour ngày tách riêng — tránh nhầm tàu gỗ với du thuyền cao cấp.'",
    "'subtitle' => '2N1D, 3N2D — 3 sao đến 5 sao luxury. Đây là trải nghiệm signature Hạ Long.'" =>
        "'subtitle' => '2 ngày 1 đêm, 3 ngày 2 đêm — 3 sao đến 5 sao cao cấp. Đây là trải nghiệm đặc trưng Hạ Long.'",
    "'subtitle' => 'Đồi cát, SGN weekend, Đà Lạt combo — tách thuyền biển hub.'" =>
        "'subtitle' => 'Đồi cát, cuối tuần Sài Gòn, combo Đà Lạt — tách mục thuyền biển.'",
    "'desc' => 'Mỗi weekend SGN được chăm như khách resort VIP'" =>
        "'desc' => 'Mỗi cuối tuần Sài Gòn được chăm như khách resort cao cấp'",
    "'title' => 'limousine SGN & bay CXR một đầu mối'" =>
        "'title' => 'Limousine Sài Gòn & bay Cam Ranh một đầu mối'",
    "'title' => 'Limousine SGN & transfer CXR'" => "'title' => 'Limousine Sài Gòn & transfer Cam Ranh'",
    "'desc' => 'Weekend và khách bay Nha Trang.'" => "'desc' => 'Cuối tuần và khách bay Nha Trang.'",
    "'body' => 'Hi Mũi Né kết nối du khách với <strong class=\"font-semibold text-ink\">resort Ham Tien</strong>, jeep đồi cát, kite-surf mùa gió, thuyền câu mực và limousine Sài Gòn — Mũi Né. Không có sân bay tại Mũi Né — bay SGN hoặc Cam Ranh + xe.'" =>
        "'body' => 'Hi Mũi Né kết nối du khách với <strong class=\"font-semibold text-ink\">resort Ham Tiến</strong>, jeep đồi cát, kite-surf mùa gió, thuyền câu mực và limousine Sài Gòn — Mũi Né. Không có sân bay tại Mũi Né — bay Sài Gòn hoặc Cam Ranh + xe.'",
    "'description' => 'Thiên đường kite-surf Việt Nam, khách Nga/EU snowbird, gia đình Sài Gòn weekend — limo 250–350k/chiều.'" =>
        "'description' => 'Thiên đường kite-surf Việt Nam, khách Nga/EU mùa đông, gia đình Sài Gòn cuối tuần — limo 250–350k/chiều.'",
    "'text' => 'Resort 4 sao + jeep đồi cát — weekend Sài Gòn chuẩn.'" =>
        "'text' => 'Resort 4 sao + jeep đồi cát — cuối tuần Sài Gòn chuẩn.'",
    "'a' => 'Không — bay Sài Gòn (SGN) + limo 4–5h, hoặc Cam Ranh/Nha Trang (CXR) + xe ~2h. Tuy Hòa (TBB) cũng có thể nối xe.'" =>
        "'a' => 'Không — bay Sài Gòn + limo 4–5h, hoặc Cam Ranh/Nha Trang + xe ~2h. Tuy Hòa cũng có thể nối xe.'",
];

$stats = ['files' => 0, 'replacements' => 0];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        fwrite(STDERR, "Cannot read {$file}\n");
        continue;
    }
    $original = $content;
    $count = 0;
    foreach ($replacements as $from => $to) {
        $n = 0;
        $content = str_replace($from, $to, $content, $n);
        $count += $n;
    }
    if ($content !== $original) {
        file_put_contents($file, $content);
        $stats['files']++;
        $stats['replacements'] += $count;
        echo basename($file).": {$count} replacements\n";
    }
}

echo "\nDone: {$stats['files']} files, {$stats['replacements']} total replacements.\n";
