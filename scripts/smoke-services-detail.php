<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$urls = [
    '/luu-tru/phu-quoc/intercontinental-phu-quoc-long-beach-resort',
    '/ve-vui-choi/vinpearl/ve-vinpearl-land-nha-trang',
    '/dich-vu-khac/thue-xe',
];

// Resolve real hotel slug from DB
$hotel = App\Models\Service::query()
    ->where('cluster', 'stay')
    ->with('seoEntry.translations', 'category')
    ->first();
if ($hotel) {
    $urls[0] = $hotel->seoEntry?->translation('vi')?->slug_full ?? $urls[0];
}

$exp = App\Models\Service::query()
    ->where('cluster', 'experience')
    ->with('seoEntry.translations')
    ->first();
if ($exp) {
    $urls[1] = $exp->seoEntry?->translation('vi')?->slug_full ?? $urls[1];
}

$other = App\Models\ServiceCategory::query()
    ->where('cluster', 'other')
    ->where('slug', 'thue-xe')
    ->with('seoEntry.translations')
    ->first();
if ($other) {
    $urls[2] = $other->seoEntry?->translation('vi')?->slug_full ?? $urls[2];
}

foreach ($urls as $url) {
    $url = '/'.ltrim((string) $url, '/');
    $req = Illuminate\Http\Request::create($url, 'GET');
    try {
        $res = $kernel->handle($req);
        echo $res->getStatusCode().' '.$url.PHP_EOL;
    } catch (Throwable $e) {
        echo 'EXC '.$url.': '.$e->getMessage().PHP_EOL;
    }
}

// Header contains more button + service clusters
$home = $kernel->handle(Illuminate\Http\Request::create('/', 'GET'));
$html = $home->getContent();
foreach (['header-more-btn', 'Tàu', 'Máy bay', 'Lưu trú', 'Vui chơi', 'Dịch vụ', 'moreOpen'] as $needle) {
    echo (str_contains($html, $needle) ? 'OK' : 'MISSING')." {$needle}".PHP_EOL;
}
