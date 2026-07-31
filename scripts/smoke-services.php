<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$urls = [
    '/ve-tau-cao-toc',
    '/luu-tru',
    '/ve-vui-choi',
    '/dich-vu-khac',
    '/ve-may-bay',
    '/ve-tau-cao-toc/ha-noi-da-nang/tau-se1-ha-noi-da-nang-ghe-mem',
    '/luu-tru/phu-quoc',
];

foreach ($urls as $url) {
    $req = Illuminate\Http\Request::create($url, 'GET');
    try {
        $res = $kernel->handle($req);
        $code = $res->getStatusCode();
        $len = strlen($res->getContent());
        echo "{$code} {$len} {$url}".PHP_EOL;
        if ($code >= 400) {
            $body = strip_tags($res->getContent());
            echo '  ERR: '.substr(preg_replace('/\s+/', ' ', $body), 0, 400).PHP_EOL;
        }
    } catch (Throwable $e) {
        echo "EXC {$url}: ".$e->getMessage().PHP_EOL;
        echo '  at '.$e->getFile().':'.$e->getLine().PHP_EOL;
    }
}
