<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$queryPatterns = [];

$app->make('events')->listen('Illuminate\\Database\\Events\\QueryExecuted', function ($query) use (&$queryPatterns) {
    $pattern = preg_replace('/\d+/', 'N', $query->sql);
    $pattern = preg_replace('/\?+/', '?', $pattern);
    $queryPatterns[$pattern] = ($queryPatterns[$pattern] ?? 0) + 1;
});

$req = Illuminate\Http\Request::create('/luu-tru/phu-quoc', 'GET');
$res = $kernel->handle($req);

arsort($queryPatterns);
echo "Query counts by pattern:" . PHP_EOL;
foreach (array_slice($queryPatterns, 0, 15) as $pattern => $count) {
    echo "Count: {$count} | SQL: " . substr($pattern, 0, 120) . PHP_EOL;
}
