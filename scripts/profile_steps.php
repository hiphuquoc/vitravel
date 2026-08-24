<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$vds = app(App\Services\ViewDataService::class);

$t0 = microtime(true);
$cat = $vds->serviceCategory('stay', 'phu-quoc');
$t1 = microtime(true);
echo "serviceCategory: " . round(($t1 - $t0) * 1000, 2) . " ms" . PHP_EOL;

$categories = $vds->serviceCategories('stay');
$t2 = microtime(true);
echo "serviceCategories: " . round(($t2 - $t1) * 1000, 2) . " ms" . PHP_EOL;

$hub = $vds->serviceHub('stay');
$t3 = microtime(true);
echo "serviceHub: " . round(($t3 - $t2) * 1000, 2) . " ms" . PHP_EOL;

$faqs = $vds->serviceListingFaqs();
$t4 = microtime(true);
echo "serviceListingFaqs: " . round(($t4 - $t3) * 1000, 2) . " ms" . PHP_EOL;

$schema = $vds->serviceSchemaItems('stay', 'phu-quoc');
$t5 = microtime(true);
echo "serviceSchemaItems: " . round(($t5 - $t4) * 1000, 2) . " ms" . PHP_EOL;

$req = Illuminate\Http\Request::create('/luu-tru/phu-quoc', 'GET');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$t_before_route = microtime(true);
$res = $kernel->handle($req);
$t_after_route = microtime(true);
echo "Full HTTP request through kernel: " . round(($t_after_route - $t_before_route) * 1000, 2) . " ms" . PHP_EOL;
