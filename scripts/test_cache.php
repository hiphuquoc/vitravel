<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$req = Illuminate\Http\Request::create('/luu-tru/phu-quoc', 'GET');
$t0 = microtime(true);
$res = $kernel->handle($req);
$t1 = microtime(true);
echo "First request: " . round(($t1 - $t0) * 1000, 2) . " ms" . PHP_EOL;

$t0 = microtime(true);
$res = $kernel->handle($req);
$t1 = microtime(true);
echo "Second request (with HTML cache): " . round(($t1 - $t0) * 1000, 2) . " ms" . PHP_EOL;
