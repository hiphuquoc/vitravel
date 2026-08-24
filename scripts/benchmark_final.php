<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== PERFORMANCE VERIFICATION BENCHMARK ===" . PHP_EOL;

// 1. Hub /luu-tru/phu-quoc (HTML Page initial load)
$req1 = Illuminate\Http\Request::create('/luu-tru/phu-quoc', 'GET');
$t0 = microtime(true);
$res1 = $kernel->handle($req1);
$t1 = microtime(true);
echo "1. GET /luu-tru/phu-quoc (HTML Page): " . round(($t1 - $t0) * 1000, 2) . " ms (Status: " . $res1->getStatusCode() . ", Size: " . strlen($res1->getContent()) . " bytes)" . PHP_EOL;

// 2. Listing AJAX API /api/listings/services?cluster=stay
$req2 = Illuminate\Http\Request::create('/api/listings/services?cluster=stay&variant=wide', 'GET');
$t0 = microtime(true);
$res2 = $kernel->handle($req2);
$t1 = microtime(true);
echo "2. GET /api/listings/services?cluster=stay (JSON Listing API): " . round(($t1 - $t0) * 1000, 2) . " ms (Status: " . $res2->getStatusCode() . ", Size: " . strlen($res2->getContent()) . " bytes)" . PHP_EOL;

// 3. Detail Page (Full Stay Booking UI)
$req3 = Illuminate\Http\Request::create('/khach-san-resort-phu-qui/resort-bai-nho/boutique-hotel-gan-bai-nho', 'GET');
$t0 = microtime(true);
$res3 = $kernel->handle($req3);
$t1 = microtime(true);
echo "3. GET Detail Page (Full Stay UI): " . round(($t1 - $t0) * 1000, 2) . " ms (Status: " . $res3->getStatusCode() . ", Size: " . strlen($res3->getContent()) . " bytes)" . PHP_EOL;
