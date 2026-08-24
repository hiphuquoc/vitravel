<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$req = Illuminate\Http\Request::create('/luu-tru/phu-quoc', 'GET');
try {
    $res = $kernel->handle($req);
    echo "Status: " . $res->getStatusCode() . PHP_EOL;
    if ($res->getStatusCode() >= 400) {
        echo substr(strip_tags($res->getContent()), 0, 500) . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "TRACE: " . $e->getTraceAsString() . PHP_EOL;
}
