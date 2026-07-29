<?php

namespace Database\Seeders;

use App\Services\SeoService;
use Illuminate\Database\Seeder;

class ToursHubSeeder extends Seeder
{
    public function run(): void
    {
        app(SeoService::class)->attachCountriesToToursHub('vi');
    }
}
