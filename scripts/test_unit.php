<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $service = app(App\Services\ViewDataService::class);
    $cat = $service->serviceCategory('stay', 'phu-quoc');
    echo "serviceCategory: " . ($cat ? 'OK' : 'NULL') . PHP_EOL;
    $schema = $service->serviceSchemaItems('stay', 'phu-quoc');
    echo "serviceSchemaItems: " . count($schema) . PHP_EOL;
    $hub = $service->serviceHub('stay');
    echo "serviceHub: " . ($hub ? 'OK' : 'NULL') . PHP_EOL;
    $categories = $service->serviceCategories('stay');
    echo "serviceCategories: " . count($categories) . PHP_EOL;
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . PHP_EOL;
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
