<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$queries = [];
$totalDbTime = 0;

$app->make('events')->listen('Illuminate\\Database\\Events\\QueryExecuted', function ($query) use (&$queries, &$totalDbTime) {
    $queries[] = [
        'sql' => $query->sql,
        'time' => $query->time,
    ];
    $totalDbTime += $query->time;
});

$t0 = microtime(true);
$req = Illuminate\Http\Request::create('/api/listings/services?cluster=stay&variant=wide', 'GET');
$res = $kernel->handle($req);
$t1 = microtime(true);

echo "Status: " . $res->getStatusCode() . PHP_EOL;
echo "Total queries: " . count($queries) . PHP_EOL;
echo "Total DB time: " . $totalDbTime . " ms" . PHP_EOL;
echo "Total HTTP handling time: " . round(($t1 - $t0) * 1000, 2) . " ms" . PHP_EOL;

$queryPatterns = [];
foreach ($queries as $q) {
    $pattern = preg_replace('/\d+/', 'N', $q['sql']);
    $pattern = preg_replace('/\?+/', '?', $pattern);
    $queryPatterns[$pattern] = ($queryPatterns[$pattern] ?? 0) + 1;
}

arsort($queryPatterns);
echo "Top query patterns in JSON listing:" . PHP_EOL;
foreach (array_slice($queryPatterns, 0, 10) as $pattern => $count) {
    echo "Count: {$count} | SQL: " . substr($pattern, 0, 130) . PHP_EOL;
}
