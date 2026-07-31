<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'services='.App\Models\Service::count().' cats='.App\Models\ServiceCategory::count().PHP_EOL;

$seo = app(App\Services\SeoService::class);
foreach (['train', 'flight', 'stay', 'experience', 'other'] as $c) {
    echo $c.': '.$seo->namedSeoPath('services.hub', ['cluster' => $c]).PHP_EOL;
}

$s = App\Models\Service::query()->with(['seoEntry.translations', 'category', 'translations'])->first();
if ($s) {
    echo 'sample: '.$s->title.PHP_EOL;
    echo 'slug_full: '.($s->seoEntry?->translation('vi')?->slug_full ?? 'null').PHP_EOL;
    echo 'cluster: '.$s->cluster.' cat: '.($s->category?->slug ?? '-').PHP_EOL;
}

$hubs = App\Models\SeoEntry::query()->whereIn('type', [
    'trains_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub',
])->count();
echo "service hubs seo: {$hubs}".PHP_EOL;
