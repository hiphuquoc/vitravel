<?php

// Debug nhanh: xem item crawl mới nhất có pack ảnh/phòng chưa.
// Usage: php scripts/stay-crawl/inspect-item.php [item_id]

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StayCrawlItem;

$id = isset($argv[1]) ? (int) $argv[1] : 0;
$item = $id > 0
    ? StayCrawlItem::query()->find($id)
    : StayCrawlItem::query()->latest('id')->first();

if (! $item) {
    echo "Không có StayCrawlItem nào.\n";
    exit(1);
}

echo 'item_id='.$item->id.' status='.$item->status.' url='.$item->source_url."\n";
echo 'crawled_at='.$item->crawled_at.' error='.($item->error ?: '-')."\n";

$fields = is_array($item->ai_json) ? $item->ai_json : [];
echo 'mapper_version='.($fields['mapper_version'] ?? '?')."\n";
echo 'mapped photos='.count($fields['attrs']['photos'] ?? [])
    .' options='.count($fields['options'] ?? [])."\n";
foreach (array_slice($fields['options'] ?? [], 0, 5) as $opt) {
    echo '  - room: '.($opt['name'] ?? '?')
        .' | size='.($opt['attrs']['size_sqm'] ?? '-')
        .' | photos='.count($opt['attrs']['photos'] ?? [])
        .' | groups='.implode(',', array_keys($opt['attrs']['amenity_groups'] ?? []))."\n";
}

$html = (string) $item->raw_html;
echo 'raw_html_len='.strlen($html).' has_pack_tag='.(str_contains($html, 'vt-stay-pack') ? 'yes' : 'NO')."\n";

if (preg_match('#<script type="application/json" id="vt-stay-pack">(.*?)</script>#s', $html, $m)) {
    $pack = json_decode($m[1], true) ?: [];
    echo 'pack photos='.count($pack['photos'] ?? [])
        .' rooms='.count($pack['rooms'] ?? [])
        .' facilities_html_len='.strlen((string) ($pack['facilities_html'] ?? ''))
        .' policies_html_len='.strlen((string) ($pack['policies_html'] ?? ''))."\n";
    foreach (array_slice($pack['rooms'] ?? [], 0, 5) as $room) {
        echo '  - pack room: '.($room['name'] ?? '?')
            .' | photos='.count($room['photos'] ?? [])
            .' | has_rp='.(str_contains((string) ($room['html'] ?? ''), 'rp-') ? 'yes' : 'no')."\n";
    }
} else {
    echo "pack: KHÔNG tìm thấy trong raw_html\n";
}

echo 'has gallery-grid-photo-action='.(substr_count($html, 'gallery-grid-photo-action-'))."\n";
echo 'has rp-content='.(substr_count($html, 'rp-content'))."\n";
