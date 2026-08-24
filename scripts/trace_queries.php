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
$req = Illuminate\Http\Request::create('/luu-tru/phu-quoc', 'GET');
$res = $kernel->handle($req);
$t1 = microtime(true);

echo "Status: " . $res->getStatusCode() . PHP_EOL;
echo "Total queries: " . count($queries) . PHP_EOL;
echo "Total DB time: " . $totalDbTime . " ms" . PHP_EOL;
echo "Total HTTP handling time: " . round(($t1 - $t0) * 1000, 2) . " ms" . PHP_EOL;

usort($queries, fn($a, $b) => $b['time'] <=> $a['time']);
echo "Top 10 slowest queries:" . PHP_EOL;
foreach (array_slice($queries, 0, 10) as $i => $q) {
    echo "#" . ($i + 1) . " [{$q['time']} ms]: " . substr($q['sql'], 0, 150) . PHP_EOL;
}
