<?php
require __DIR__ . '/vendor/autoload.php';
\ = require_once __DIR__ . '/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Http\Kernel::class);
\ = \->handle(
    \ = Illuminate\Http\Request::create('/api/v1/admin/stay-crawls/jobs?service_category_id=52&page=1&per_page=12', 'GET')
);
echo 'STATUS: ' . \->getStatusCode() . PHP_EOL;
echo 'CONTENT: ' . substr(\->getContent(), 0, 500) . PHP_EOL;
