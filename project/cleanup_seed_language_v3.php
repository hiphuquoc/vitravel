<?php
/**
 * Pass 3 (unified): chuẩn hoá mọi copy tiếng Việt trong seed hub.
 * Xử lý: vi string, vi array nested, FAQ, highlightsIntro, excerpt, summary, content, text, bio_html...
 * Không đụng 'en' => blocks.
 */
declare(strict_types=1);

$dir = __DIR__;
$files = glob($dir.'/seed_*.php') ?: [];
$files = array_values(array_filter($files, fn ($f) => ! str_ends_with($f, 'seed_vitravel.php')));

function seed_clean_vi_text(string $text): string
{
    static $map = [
        '2N1D/3N2D' => '2 ngày 1 đêm / 3 ngày 2 đêm',
        '2N1D hoặc 3N2D' => '2 ngày 1 đêm hoặc 3 ngày 2 đêm',
        '2N1D, 3N2D' => '2 ngày 1 đêm, 3 ngày 2 đêm',
        '2N1D & 3N2D' => '2 ngày 1 đêm & 3 ngày 2 đêm',
        'tour ngày/2N1D' => 'tour ngày hoặc 2 ngày 1 đêm',
        'Du thuyền Hạ Long 2N1D' => 'Du thuyền Hạ Long 2 ngày 1 đêm',
        'Du thuyền 2N1D' => 'Du thuyền 2 ngày 1 đêm',
        'du thuyền 2N1D' => 'du thuyền 2 ngày 1 đêm',
        'Du thuyền 5 sao 2N1D' => 'Du thuyền 5 sao 2 ngày 1 đêm',
        'Du thuyền 4 sao 2N1D' => 'Du thuyền 4 sao 2 ngày 1 đêm',
        'Gia đình Bãi Cháy 2N1D' => 'Gia đình Bãi Cháy 2 ngày 1 đêm',
        'sau du thuyền 2N1D' => 'sau du thuyền 2 ngày 1 đêm',
        'Bái Tử Long 2N1D' => 'Bái Tử Long 2 ngày 1 đêm',
        'kết hợp 3N2D' => 'kết hợp 3 ngày 2 đêm',
        'du thuyền 3N2D' => 'du thuyền 3 ngày 2 đêm',
        '3N2D cả hai' => '3 ngày 2 đêm cả hai',
        '3N2D hai vịnh' => '3 ngày 2 đêm hai vịnh',
        'hoặc 3N2D' => 'hoặc 3 ngày 2 đêm',
        'gợi ý 2N1D' => 'gợi ý 2 ngày 1 đêm',
        'đã từng 2N1D' => 'đã từng 2 ngày 1 đêm',
        'như 2N1D' => 'như 2 ngày 1 đêm',
        'hay 2N1D' => 'hay 2 ngày 1 đêm',
        'hoặc 2N1D' => 'hoặc 2 ngày 1 đêm',
        'tour 2N1D' => 'tour 2 ngày 1 đêm',
        'chuyến 2N1D' => 'chuyến 2 ngày 1 đêm',
        'combo 2N1D' => 'combo 2 ngày 1 đêm',
        'ghép 2N1D' => 'ghép 2 ngày 1 đêm',
        'Thiết kế tour 2N1D' => 'Thiết kế tour 2 ngày 1 đêm',
        'Mệt hơn 2N1D' => 'Mệt hơn 2 ngày 1 đêm',
        'Sa Pa 2N1D' => 'Sa Pa 2 ngày 1 đêm',
        'Sa Pa 3N2D' => 'Sa Pa 3 ngày 2 đêm',
        'Tam Đảo 2N1D' => 'Tam Đảo 2 ngày 1 đêm',
        'Tam Đảo 3N2D' => 'Tam Đảo 3 ngày 2 đêm',
        'Gia đình 2N1D' => 'Gia đình 2 ngày 1 đêm',
        'Đà Lạt — Mũi Né 3N2D' => 'Đà Lạt — Mũi Né 3 ngày 2 đêm',
        'Kite-surf 3N2D' => 'Kite-surf 3 ngày 2 đêm',
        'Weekend 2N1D' => 'Cuối tuần 2 ngày 1 đêm',
        'weekend 2N1D' => 'cuối tuần 2 ngày 1 đêm',
        '2N1D du thuyền' => 'du thuyền 2 ngày 1 đêm',
        '(2N1D hoặc 3N2D)' => '(2 ngày 1 đêm hoặc 3 ngày 2 đêm)',
        '(2N1D)' => '(2 ngày 1 đêm)',
        '2N1D' => '2 ngày 1 đêm',
        '3N2D' => '3 ngày 2 đêm',
        '4N3D' => '4 ngày 3 đêm',
        '2N1Đ' => '2 ngày 1 đêm',
        'Agri-tourism' => 'Du lịch nông nghiệp',
        'must-see' => 'không thể bỏ qua',
        'positioning anti-crowd' => 'định vị ít đông khách',
        'anti-crowd route' => 'tuyến ít đông khách',
        'anti-crowd routes' => 'tuyến ít đông khách',
        'anti-crowd' => 'ít đông khách',
        'Positioning rõ' => 'Định vị rõ',
        'Tư vấn Bái Tử Long anti-crowd' => 'Tư vấn Bái Tử Long ít đông khách',
        'Bái Tử Long anti-crowd' => 'Bái Tử Long ít đông khách',
        'Bái Tử Long — anti-crowd route.' => 'Bái Tử Long — tuyến ít đông khách.',
        'Bái Tử Long — anti-crowd.' => 'Bái Tử Long — ít đông khách.',
        'Du thuyền Hạ Long 2N1D (rút gọn)' => 'Du thuyền Hạ Long 2 ngày 1 đêm (rút gọn)',
        'Du thuyền 2N1D tiêu chuẩn' => 'Du thuyền 2 ngày 1 đêm tiêu chuẩn',
        'Du thuyền 3–4 sao tiêu chuẩn (2N1D)' => 'Du thuyền 3–4 sao tiêu chuẩn (2 ngày 1 đêm)',
        'Điểm must-see' => 'Điểm không thể bỏ qua',
        'Ăn thử onboard' => 'Ăn thử trên tàu',
        'trái cây onboard' => 'trái cây trên tàu',
        'tiễn SGN' => 'tiễn Sài Gòn',
        'cruise hoặc tour ngày' => 'du thuyền hoặc tour ngày',
        'Tai chi bình minh' => 'Tập thể dục buổi sáng',
        'Wine pairing dinner' => 'Bữa tối kết hợp rượu vang',
        'Spa onboard' => 'Spa trên tàu',
        'positioning rõ' => 'định vị rõ',
        'sweet spot gia đình & FIT' => 'mức giá phù hợp gia đình & khách lẻ',
        'sweet spot' => 'mức giá phù hợp',
        'Peak quốc tế' => 'Cao điểm quốc tế',
        'khách nội địa weekend' => 'khách nội địa cuối tuần',
        'Gói weekend' => 'Gói cuối tuần',
        'Weekend ' => 'Cuối tuần ',
        'weekend ' => 'cuối tuần ',
        'trưa onboard' => 'trưa trên tàu',
        'Trưa onboard' => 'Trưa trên tàu',
        'chiều onboard' => 'chiều trên tàu',
        'ăn onboard' => 'ăn trên tàu',
        'bữa onboard' => 'bữa trên tàu',
        'Spa onboard' => 'Spa trên tàu',
        'wine pairing dinner' => 'bữa tối kết hợp rượu vang',
        'Wine pairing dinner' => 'Bữa tối kết hợp rượu vang',
        'Butler service' => 'Dịch vụ quản gia',
        'Tai chi bình minh' => 'Tập thể dục buổi sáng',
        'anniversary' => 'kỷ niệm',
        'cruises hub' => 'mục du thuyền',
        'thuyền biển hub' => 'mục thuyền biển',
        'resort hub' => 'mục resort',
        'Limousine SGN' => 'Limousine Sài Gòn',
        'limousine SGN' => 'limousine Sài Gòn',
        'Gộp limousine SGN' => 'Gộp limousine Sài Gòn',
        'Khách SGN' => 'Khách Sài Gòn',
        'khách SGN' => 'khách Sài Gòn',
        'tour SGN' => 'tour Sài Gòn',
        'Tour ngày SGN' => 'Tour ngày từ Sài Gòn',
        'tour ngày SGN' => 'tour ngày từ Sài Gòn',
        '99% khách SGN' => '99% khách Sài Gòn',
        'SGN weekend' => 'cuối tuần Sài Gòn',
        'weekend SGN' => 'cuối tuần Sài Gòn',
        'SGN —' => 'Sài Gòn —',
        '— SGN' => '— Sài Gòn',
        'về SGN' => 'về Sài Gòn',
        'bay SGN' => 'bay Sài Gòn',
        'SGN limo' => 'limo Sài Gòn',
        'fly SGN' => 'bay Sài Gòn',
        'SGN → VDO' => 'Sài Gòn → Vân Đồn',
        'HAN → VDO' => 'Hà Nội → Vân Đồn',
        'SGN → Đà Lạt' => 'Sài Gòn → Đà Lạt',
        'SGN ↔ Đà Lạt' => 'Sài Gòn ↔ Đà Lạt',
        'SGN → DLI' => 'Sài Gòn → Đà Lạt',
        'SGN/HAN → Cruise' => 'Sài Gòn/Hà Nội → Du thuyền',
        'shuttle DLI' => 'shuttle Đà Lạt',
        'HCMC' => 'Sài Gòn',
        'HN →' => 'Hà Nội →',
        'HN ↔' => 'Hà Nội ↔',
        ' từ HN' => ' từ Hà Nội',
        'từ HN ' => 'từ Hà Nội ',
        'từ HN.' => 'từ Hà Nội.',
        '320km từ HN' => '320km từ Hà Nội',
        'Cửa ngõ chính từ HN' => 'Cửa ngõ chính từ Hà Nội',
        'dân văn phòng HN' => 'dân văn phòng Hà Nội',
        'Thiết kế itinerary' => 'Thiết kế lịch trình',
        'FIT intl' => 'khách lẻ quốc tế',
        'FIT quốc tế' => 'khách lẻ quốc tế',
        'Combo VDO + cruise' => 'Combo VDO + du thuyền',
        'VDO + cruise' => 'VDO + du thuyền',
        'transfer + cruise' => 'transfer + du thuyền',
        'bay + cruise' => 'bay + du thuyền',
        'xe + cruise' => 'xe + du thuyền',
        'Cruise 1 đêm' => 'Du thuyền 1 đêm',
        'cruise 3–4 sao' => 'du thuyền 3–4 sao',
        'cruise 3-4 sao' => 'du thuyền 3-4 sao',
        'tàu/cruise' => 'tàu/du thuyền',
        'tàu/cruise.' => 'tàu/du thuyền.',
        'Bái Tử Long anti-crowd' => 'Bái Tử Long ít đông khách',
        'Tư vấn Bái Tử Long anti-crowd' => 'Tư vấn Bái Tử Long ít đông khách',
        'Bái Tử Long — anti-crowd' => 'Bái Tử Long — ít đông khách',
        'HDV foodie' => 'HDV am thực',
        'HDV am thuc' => 'HDV am thực',
        'foodie' => 'ẩm thực',
        'snowbird' => 'khách mùa đông',
        'resort strip' => 'dải resort',
        'resort VIP' => 'resort cao cấp',
        'half hoặc full day' => 'nửa ngày hoặc cả ngày',
        'GEO zone' => 'vùng địa lý',
        'GEO category' => 'danh mục địa lý',
        'GEO page' => 'trang địa lý',
        'per-zone GEO' => 'theo vùng địa lý',
        'intent' => 'mục đích',
        'signature ' => 'đặc trưng ',
        ' signature' => ' đặc trưng',
        'Private' => 'Xe riêng',
        'Tour ngày & weekend' => 'Tour ngày và cuối tuần',
        'mẹo weekend' => 'mẹo cuối tuần',
        'đi weekend' => 'đi cuối tuần',
        'Cuối tuần hot' => 'Cuối tuần đông khách',
        'peak kite-surf' => 'mùa kite-surf cao điểm',
        'budget phổ biến' => 'giá phổ biến',
        'transfer CXR' => 'transfer Cam Ranh',
        'bay CXR' => 'bay Cam Ranh',
        'Ham Tien resort hub' => 'danh mục resort Ham Tiến',
        'Ham Tien resort only' => 'chỉ resort Ham Tiến',
        'SEO zone' => 'Trang chủ đề',
        'Zone ket-hop-sai-gon' => 'Combo từ Sài Gòn',
        'resort overnight' => 'resort qua đêm',
        'tour full ' => 'tour trọn ngày ',
        'anti-cannibalize' => 'tách biệt',
        'trải nghiệm signature' => 'trải nghiệm đặc trưng',
        'núi signature' => 'núi đặc trưng',
        'nước signature' => 'nước đặc trưng',
        'buổi tối signature' => 'buổi tối đặc trưng',
        'limousine SGN &' => 'limousine Sài Gòn &',
        'Weekend 2 ngày' => 'Cuối tuần 2 ngày',
        'Weekend phổ biến' => 'Cuối tuần phổ biến',
        'Weekend SGN' => 'Cuối tuần Sài Gòn',
        'khách Nga/EU snowbird' => 'khách Nga/EU mùa đông',
        'food tour' => 'tour ẩm thực',
        'Food tour' => 'Tour ẩm thực',
        'thiết kế itinerary' => 'thiết kế lịch trình',
        'itinerary tuỳ chỉnh' => 'lịch trình tuỳ chỉnh',
        'itinerary cặp đôi' => 'lịch trình cặp đôi',
        'combo SGN' => 'combo từ Sài Gòn',
        'tour cuối tuần HN' => 'tour cuối tuần Hà Nội',
        'Ẩm thực & food tour' => 'Ẩm thực & tour ẩm thực',
        'fine dining' => 'ẩm thực cao cấp',
        'butler' => 'quản gia',
        'itinerary cặp đôi' => 'lịch trình cặp đôi',
        'tour cuối tuần HN,' => 'tour cuối tuần Hà Nội,',
        'bay HAN' => 'bay Hà Nội',
        'Bay HN' => 'Bay Hà Nội',
        'bay HN' => 'bay Hà Nội',
        'Bay HN +' => 'Bay Hà Nội +',
        'Khách bay HAN' => 'Khách bay Hà Nội',
        'bay HAN —' => 'bay Hà Nội —',
        'bay HAN +' => 'bay Hà Nội +',
        'Intl →' => 'Quốc tế →',
        'Door-to-resort' => 'Đón tận resort',
        'door-to-door' => 'đón tận nơi',
        'Door-to-door' => 'Đón tận nơi',
        '(HAN)' => '(Hà Nội)',
        'HAN →' => 'Hà Nội →',
        'HAN ↔' => 'Hà Nội ↔',
        '↔ HN' => '↔ Hà Nội',
        'HN/Tam' => 'Hà Nội/Tam',
        'SGN/HAN' => 'Sài Gòn/Hà Nội',
        'HAN/SGN' => 'Hà Nội/Sài Gòn',
        'SGN →' => 'Sài Gòn →',
        'PQC)' => 'Phú Quốc)',
        '(SGN → PQC)' => '(Sài Gòn → Phú Quốc)',
        '(SGN → PQC)' => '(Sài Gòn → Phú Quốc)',
        'SGN → PQC' => 'Sài Gòn → Phú Quốc',
        'HAN → DAD' => 'Hà Nội → Đà Nẵng',
        'SGN → DAD' => 'Sài Gòn → Đà Nẵng',
        'HAN → CXR' => 'Hà Nội → Cam Ranh',
        'HAN / SGN' => 'Hà Nội / Sài Gòn',
        'food tour gà' => 'tour ẩm thực gà',
        'vibe hill station' => 'không khí đồi mát',
        'chuyên itinerary' => 'chuyên lịch trình',
        'itinerary đồi cát' => 'lịch trình đồi cát',
        'cannibalize SEO' => 'trùng lặp SEO',
        'SEO không cannibalize' => 'SEO không trùng lặp',
        'Guide transport' => 'Hướng dẫn di chuyển',
    ];

    $out = $text;
    foreach ($map as $from => $to) {
        $out = str_replace($from, $to, $out);
    }

    return $out;
}

function is_vi_copy(string $s): bool
{
    if (preg_match('/[àáảãạăắằẳẵặâấầẩẫậèéẻẽẹêếềểễệìíỉĩịòóỏõọôốồổỗộơớờởỡợùúủũụưứừửữựỳýỷỹỵđ]/ui', $s)) {
        return true;
    }
    static $jargon = [
        '2N1D', '3N2D', '4N3D', 'SGN', 'HAN', 'HCMC', 'DLI', 'VDO', 'CXR',
        'onboard', 'anti-crowd', 'must-see', 'weekend', 'Weekend', 'sweet spot',
        'FIT', 'Agri-tourism', 'cruises hub', 'positioning', 'Peak ', 'itinerary',
        'anniversary', 'Butler', 'Wine pairing', 'foodie', 'GEO ', 'hub ',
        'snowbird', 'signature', 'Private', 'SEO zone',
    ];
    foreach ($jargon as $j) {
        if (str_contains($s, $j)) {
            return true;
        }
    }

    return false;
}

function clean_quoted_value(string $raw, bool $force = false): array
{
    $inner = str_replace("\\'", "'", $raw);
    if (! $force && ! is_vi_copy($inner)) {
        return [$raw, false];
    }
    $clean = seed_clean_vi_text($inner);
    if ($clean === $inner) {
        return [$raw, false];
    }

    return [str_replace("'", "\\'", $clean), true];
}

function process_vi_fields(string $content, int &$changes): string
{
    // Pass 1: mọi 'vi' => ... (containers đã xử lý ở bước trước)
    $fields = 'q|a|highlightsIntro|excerpt|summary|content|bio_html|bio|short_bio|intro|location_label|trip|tagline|description|body|desc|label|name|quote';

    // 'vi' => '...'
    $content = preg_replace_callback(
        "/'vi'\\s*=>\\s*'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            [$val, $changed] = clean_quoted_value($m[1], true);
            if ($changed) {
                $changes++;
            }

            return "'vi' => '{$val}'";
        },
        $content
    );

    // 'vi' => array( ... 'key' => '...' )
    $content = preg_replace_callback(
        "/('vi'\\s*=>\\s*array\\([^;]*?)'((?:subtitle|desc|description|title|body|seo_body|text|summary|eyebrow|metaLine|ctaLabel|imageAlt))'\\s*=>\\s*'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            [$val, $changed] = clean_quoted_value($m[3], true);
            if ($changed) {
                $changes++;
            }

            return $m[1]."'{$m[2]}' => '{$val}'";
        },
        $content
    );

    // Plain VN fields (not inside en blocks — heuristic: field + vi copy)
    $content = preg_replace_callback(
        "/'({$fields})'\\s*=>\\s*'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            if ($m[1] === 'vi') {
                return $m[0];
            }
            [$val, $changed] = clean_quoted_value($m[2]);
            if ($changed) {
                $changes++;
            }

            return "'{$m[1]}' => '{$val}'";
        },
        $content
    );

    // Article blocks: 'text' => '...' (chỉ khi có tiếng Việt / jargon)
    $content = preg_replace_callback(
        "/'text'\\s*=>\\s*'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            [$val, $changed] = clean_quoted_value($m[1]);
            if ($changed) {
                $changes++;
            }

            return "'text' => '{$val}'";
        },
        $content
    );

    // Service / category titles (VN copy)
    $content = preg_replace_callback(
        "/'title'\\s*=>\\s*'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            [$val, $changed] = clean_quoted_value($m[1]);
            if ($changed) {
                $changes++;
            }

            return "'title' => '{$val}'";
        },
        $content
    );

    // Highlight / note strings trong array (mọi phần tử)
    $content = clean_list_arrays($content, $changes);

    // vi => ['seo_body' => '...']
    $content = preg_replace_callback(
        "/('vi'\\s*=>\\s*\\[\\s*'seo_body'\\s*=>\\s*)'((?:\\\\'|[^'])*)'/",
        static function (array $m) use (&$changes): string {
            [$val, $changed] = clean_quoted_value($m[2], true);
            if ($changed) {
                $changes++;
            }

            return $m[1]."'{$val}'";
        },
        $content
    );

    return $content;
}

/** Dọn mọi string value trong 'vi' => array(...) hoặc 'vi' => [...] */
function process_vi_containers(string $content, int &$changes): string
{
    $offset = 0;
    while (preg_match("/'vi'\\s*=>\\s*(array|\\[)/", $content, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start = $m[0][1];
        $open = $m[1][0] === 'array' ? '(' : '[';
        $close = $open === '(' ? ')' : ']';
        $pos = $start + strlen($m[0][0]);
        $depth = 1;
        $len = strlen($content);
        while ($pos < $len && $depth > 0) {
            $ch = $content[$pos];
            if ($ch === $open) {
                $depth++;
            } elseif ($ch === $close) {
                $depth--;
            }
            $pos++;
        }
        $block = substr($content, $start, $pos - $start);
        $cleaned = preg_replace_callback(
            "/'((?:\\\\'|[^'])*)'/",
            static function (array $s) use ($block, &$changes): string {
                $idx = strpos($block, $s[0]);
                $after = $idx === false ? '' : substr($block, $idx + strlen($s[0]), 15);
                if (preg_match('/^\\s*=>/', $after)) {
                    return $s[0];
                }
                [$val, $changed] = clean_quoted_value($s[1], true);
                if ($changed) {
                    $changes++;
                }

                return "'{$val}'";
            },
            $block
        );
        if ($cleaned !== $block) {
            $content = substr($content, 0, $start).$cleaned.substr($content, $pos);
        }
        $offset = $start + strlen($cleaned);
    }

    return $content;
}

function clean_list_arrays(string $content, int &$changes): string
{
    foreach (['highlights', 'notes', 'inclusions', 'exclusions', 'items'] as $key) {
        $content = preg_replace_callback(
            "/'{$key}'\\s*=>\\s*array\\((.*?)\\)/s",
            static function (array $m) use (&$changes, $key): string {
                $inner = preg_replace_callback(
                    "/'((?:\\\\'|[^'])*)'/",
                    static function (array $s) use (&$changes): string {
                        [$val, $changed] = clean_quoted_value($s[1]);
                        if ($changed) {
                            $changes++;
                        }

                        return "'{$val}'";
                    },
                    $m[1]
                );

                return "'{$key}' => array(".$inner.')';
            },
            $content
        );
    }

    return $content;
}

/** Ẩn block 'en' => ... để regex không sửa copy tiếng Anh */
function strip_en_blocks(string $content): array
{
    $placeholders = [];
    $lines = explode("\n", $content);
    $out = [];
    $i = 0;
    $n = count($lines);
    $idx = 0;
    while ($idx < $n) {
        $line = $lines[$idx];
        if (preg_match("/'en'\\s*=>/", $line)) {
            if (preg_match("/^(.*?)(\\s*'en'\\s*=>.+)$/s", $line, $parts)) {
                $out[] = $parts[1];
                $line = ltrim($parts[2]);
            }
            $buf = [$line];
            $depth = substr_count($line, '(') + substr_count($line, '[') - substr_count($line, ')') - substr_count($line, ']');
            $idx++;
            while ($idx < $n && $depth > 0) {
                $buf[] = $lines[$idx];
                $depth += substr_count($lines[$idx], '(') + substr_count($lines[$idx], '[')
                    - substr_count($lines[$idx], ')') - substr_count($lines[$idx], ']');
                $idx++;
            }
            $key = "___EN_BLOCK_{$i}___";
            $placeholders[$key] = implode("\n", $buf);
            $out[] = $key;
            $i++;
            continue;
        }
        $out[] = $line;
        $idx++;
    }

    return [implode("\n", $out), $placeholders];
}

function restore_en_blocks(string $content, array $placeholders): string
{
    foreach ($placeholders as $key => $block) {
        $content = str_replace($key, $block, $content);
    }

    return $content;
}

$stats = ['files' => 0, 'changes' => 0];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }
    $original = $content;
    $changes = 0;

    $content = process_vi_containers($content, $changes);
    [$stripped, $enBlocks] = strip_en_blocks($content);
    $stripped = process_vi_fields($stripped, $changes);
    $content = restore_en_blocks($stripped, $enBlocks);

    if ($content !== $original) {
        file_put_contents($file, $content);
        $stats['files']++;
        $stats['changes'] += $changes;
        echo basename($file).": {$changes}\n";
    }
}

echo "\nV3 done: {$stats['files']} files, {$stats['changes']} updates.\n";
