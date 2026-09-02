<?php
/**
 * Pass 2: chuẩn hoá chuỗi tiếng Việt trong seed (vi fields + copy VN không bọc en).
 * Run sau cleanup_seed_language.php
 */
declare(strict_types=1);

$dir = __DIR__;
$files = glob($dir.'/seed_*.php') ?: [];
$files = array_values(array_filter($files, fn ($f) => ! str_ends_with($f, 'seed_vitravel.php')));

function clean_vi_text(string $text): string
{
    static $map = [
        '2N1D' => '2 ngày 1 đêm',
        '3N2D' => '3 ngày 2 đêm',
        '4N3D' => '4 ngày 3 đêm',
        '2N1Đ' => '2 ngày 1 đêm',
        'HCMC' => 'Sài Gòn',
        'SGN weekend' => 'cuối tuần Sài Gòn',
        'weekend SGN' => 'cuối tuần Sài Gòn',
        'Weekend SGN' => 'Cuối tuần Sài Gòn',
        'SGN —' => 'Sài Gòn —',
        '— SGN' => '— Sài Gòn',
        'về SGN' => 'về Sài Gòn',
        'khách SGN' => 'khách Sài Gòn',
        'tour SGN' => 'tour Sài Gòn',
        'limousine SGN' => 'limousine Sài Gòn',
        'bay SGN' => 'bay Sài Gòn',
        'Weekend 2N1D' => 'Cuối tuần 2 ngày 1 đêm',
        'weekend 2N1D' => 'cuối tuần 2 ngày 1 đêm',
        'Weekend 2 ngày' => 'Cuối tuần 2 ngày',
        'Weekend phổ biến' => 'Cuối tuần phổ biến',
        'Weekend ' => 'Cuối tuần ',
        'weekend ' => 'cuối tuần ',
        'HN →' => 'Hà Nội →',
        'HN ↔' => 'Hà Nội ↔',
        ' từ HN' => ' từ Hà Nội',
        'từ HN ' => 'từ Hà Nội ',
        'Cửa ngõ chính từ HN' => 'Cửa ngõ chính từ Hà Nội',
        'dân văn phòng HN' => 'dân văn phòng Hà Nội',
        'từ HN.' => 'từ Hà Nội.',
        '320km từ HN' => '320km từ Hà Nội',
        'cruises hub' => 'mục du thuyền',
        'thuyền biển hub' => 'mục thuyền biển',
        'resort hub' => 'mục resort',
        'signature ' => 'đặc trưng ',
        ' signature' => ' đặc trưng',
        'Agri-tourism' => 'Du lịch nông nghiệp',
        'anti-cannibalize' => 'tách biệt',
        'Cuối tuần hot' => 'Cuối tuần đông khách',
        'snowbird' => 'khách mùa đông',
        'Peak quốc tế' => 'Cao điểm quốc tế',
        'peak kite-surf' => 'mùa kite-surf cao điểm',
        'budget phổ biến' => 'giá phổ biến',
        'Private' => 'Xe riêng',
        'Tour ngày & weekend' => 'Tour ngày và cuối tuần',
        'mẹo weekend' => 'mẹo cuối tuần',
        'đi weekend' => 'đi cuối tuần',
        'khách nội địa weekend' => 'khách nội địa cuối tuần',
        'Weekend 2N1D du thuyền' => 'Cuối tuần 2 ngày 1 đêm du thuyền',
        'Sa Pa 2N1D' => 'Sa Pa 2 ngày 1 đêm',
        'Sa Pa 3N2D' => 'Sa Pa 3 ngày 2 đêm',
        'Tam Đảo 2N1D' => 'Tam Đảo 2 ngày 1 đêm',
        'Tam Đảo 3N2D' => 'Tam Đảo 3 ngày 2 đêm',
        'Gia đình 2N1D' => 'Gia đình 2 ngày 1 đêm',
        'Đà Lạt — Mũi Né 3N2D' => 'Đà Lạt — Mũi Né 3 ngày 2 đêm',
        'Kite-surf 3N2D' => 'Kite-surf 3 ngày 2 đêm',
        'Du thuyền 5 sao 2N1D' => 'Du thuyền 5 sao 2 ngày 1 đêm',
        'Du thuyền 4 sao 2N1D' => 'Du thuyền 4 sao 2 ngày 1 đêm',
        'Gia đình Bãi Cháy 2N1D' => 'Gia đình Bãi Cháy 2 ngày 1 đêm',
        'tour 2N1D' => 'tour 2 ngày 1 đêm',
        'chuyến 2N1D' => 'chuyến 2 ngày 1 đêm',
        'combo 2N1D' => 'combo 2 ngày 1 đêm',
        'ghép 2N1D' => 'ghép 2 ngày 1 đêm',
        'sau du thuyền 2N1D' => 'sau du thuyền 2 ngày 1 đêm',
        'Du thuyền Hạ Long 2N1D' => 'Du thuyền Hạ Long 2 ngày 1 đêm',
        'du thuyền 2N1D' => 'du thuyền 2 ngày 1 đêm',
        'Du thuyền 2N1D' => 'Du thuyền 2 ngày 1 đêm',
        'hoặc 2N1D' => 'hoặc 2 ngày 1 đêm',
        'hay 2N1D' => 'hay 2 ngày 1 đêm',
        'như 2N1D' => 'như 2 ngày 1 đêm',
        'đã từng 2N1D' => 'đã từng 2 ngày 1 đêm',
        'Mệt hơn 2N1D' => 'Mệt hơn 2 ngày 1 đêm',
        'Thiết kế tour 2N1D' => 'Thiết kế tour 2 ngày 1 đêm',
        'gợi ý 2N1D' => 'gợi ý 2 ngày 1 đêm',
        'Bái Tử Long 2N1D' => 'Bái Tử Long 2 ngày 1 đêm',
        '3N2D cả hai' => '3 ngày 2 đêm cả hai',
        '3N2D hai vịnh' => '3 ngày 2 đêm hai vịnh',
        'hoặc 3N2D' => 'hoặc 3 ngày 2 đêm',
        '2N1D hoặc 3N2D' => '2 ngày 1 đêm hoặc 3 ngày 2 đêm',
        '2N1D, 3N2D' => '2 ngày 1 đêm, 3 ngày 2 đêm',
        'trải nghiệm signature' => 'trải nghiệm đặc trưng',
        'núi signature' => 'núi đặc trưng',
        'nước signature' => 'nước đặc trưng',
        'buổi tối signature' => 'buổi tối đặc trưng',
        'HDV foodie' => 'HDV am thực',
        'resort strip' => 'dải resort',
        'resort VIP' => 'resort cao cấp',
        'limousine SGN &' => 'limousine Sài Gòn &',
        'transfer CXR' => 'transfer Cam Ranh',
        'bay CXR' => 'bay Cam Ranh',
        'tour ngày SGN' => 'tour ngày từ Sài Gòn',
        'Tour ngày SGN' => 'Tour ngày từ Sài Gòn',
        '99% khách SGN' => '99% khách Sài Gòn',
        'khách Nga/EU snowbird' => 'khách Nga/EU mùa đông',
        'kite-surf mùa gió, thuyền' => 'kite-surf mùa gió, thuyền',
        'SGN limo' => 'limo Sài Gòn',
        'fly SGN' => 'bay Sài Gòn',
        'Ham Tien resort hub' => 'danh mục resort Ham Tiến',
        'Ham Tien resort only' => 'chỉ resort Ham Tiến',
        'SEO zone' => 'Trang chủ đề',
        'Zone ket-hop-sai-gon' => 'Combo từ Sài Gòn',
        'half hoặc full day' => 'nửa ngày hoặc cả ngày',
        'resort overnight' => 'resort qua đêm',
        'tour full ' => 'tour trọn ngày ',
        'tách thuyền biển hub' => 'tách mục thuyền biển',
        'tách cruises hub' => 'tách mục du thuyền',
        'real overnight cruises' => 'du thuyền qua đêm thật',
        'wood-boat vs luxury' => 'tàu gỗ và du thuyền cao cấp',
    ];

    $out = $text;
    foreach ($map as $from => $to) {
        $out = str_replace($from, $to, $out);
    }

    return $out;
}

$stats = ['files' => 0, 'changes' => 0];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }
    $original = $content;
    $changes = 0;

    // Chỉ xử lý 'vi' => '...' — an toàn, không đụng en
    $content = preg_replace_callback(
        "/'vi'\\s*=>\\s*'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            $inner = str_replace("\\'", "'", $m[1]);
            $clean = clean_vi_text($inner);
            if ($clean === $inner) {
                return $m[0];
            }
            $changes++;

            return "'vi' => '".str_replace("'", "\\'", $clean)."'";
        },
        $content
    );

    if ($content !== $original) {
        file_put_contents($file, $content);
        $stats['files']++;
        $stats['changes'] += $changes;
        echo basename($file).": {$changes} vi strings cleaned\n";
    }
}

echo "\nDone: {$stats['files']} files, {$stats['changes']} string updates.\n";
