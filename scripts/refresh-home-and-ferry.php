#!/usr/bin/env php
<?php

/**
 * Refresh home sections / slides / ferry / company without full ContentSeeder.
 */

use App\Models\CompanyProfile;
use App\Models\HomeSection;
use App\Models\HomeSectionTranslation;
use App\Models\HomeSlide;
use App\Models\HomeSlideTranslation;
use App\Models\Language;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\HomePageDefaults;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Database\Seeders\HomeSlideSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$profiles = ['phuquy', 'hicatba', 'vitravel'];

foreach ($profiles as $code) {
    $project = Project::query()->where('code', $code)->first();
    if (! $project) {
        echo "skip missing project {$code}\n";
        continue;
    }

    ProjectSeed::clearProfile();
    ProjectSeed::useProfile($code);
    ProjectContext::set($project);

    echo "=== {$code} ===\n";

    // Home sections from ProjectSeed via HomePageDefaults
    $viId = Language::idByCode('vi');
    $enId = Language::idByCode('en');
    foreach (HomePageDefaults::sections() as $row) {
        $section = HomeSection::query()->updateOrCreate(
            ['key' => $row['key']],
            ['sort' => $row['sort'], 'is_active' => true],
        );
        foreach (['vi' => $viId, 'en' => $enId] as $loc => $langId) {
            if (! $langId || empty($row[$loc])) {
                continue;
            }
            HomeSectionTranslation::query()->updateOrCreate(
                ['home_section_id' => $section->id, 'language_id' => $langId],
                $row[$loc],
            );
        }
    }
    $qi = HomeSection::query()->where('key', 'quick_inquiry')->first();
    $qiTitle = $qi?->translation('vi')?->title;
    echo "  QI: {$qiTitle}\n";

    // USP from seed
    foreach (HomePageDefaults::usps() as $row) {
        $usp = \App\Models\Usp::query()->updateOrCreate(
            ['sort' => $row['sort']],
            ['icon' => $row['icon'], 'is_active' => true],
        );
        foreach (['vi' => $viId, 'en' => $enId] as $loc => $langId) {
            if (! $langId) {
                continue;
            }
            \App\Models\UspTranslation::query()->updateOrCreate(
                ['usp_id' => $usp->id, 'language_id' => $langId],
                [
                    'title' => $row[$loc]['title'],
                    'description' => $row[$loc]['description'],
                ],
            );
        }
    }

    if ($code === 'phuquy') {
        // Force company brand
        $company = ProjectSeed::get('company', []);
        $attrs = CompanyProfile::attributesFromSeed(is_array($company) ? $company : []);
        $profile = CompanyProfile::query()->first();
        if ($profile) {
            $profile->fill($attrs)->save();
        } else {
            CompanyProfile::query()->create($attrs);
        }
        echo '  company: '.(CompanyProfile::query()->value('name'))."\n";

        // Refresh slides: wipe + reseeds
        HomeSlideTranslation::query()->delete();
        HomeSlide::query()->delete();
        (new HomeSlideSeeder)->run();
        $slide = HomeSlide::query()->orderBy('sort')->first();
        $desc = $slide?->translation('vi')?->description;
        echo '  slide0: '.mb_substr((string) $desc, 0, 80)."\n";

        // SoftDeletes + unique(project_id, cluster, slug/code): force-delete
        // trashed ferry rows or ServiceCatalogSeeder insert will conflict.
        Service::withTrashed()->where('cluster', 'ferry')->each(function (Service $s) {
            $s->options()->each(function ($o) {
                $o->translations()->delete();
                $o->delete();
            });
            $s->translations()->delete();
            $s->forceDelete();
        });
        ServiceCategory::withTrashed()->where('cluster', 'ferry')->forceDelete();
        (new ServiceCatalogSeeder)->run();
        $ferryCodes = Service::query()->where('cluster', 'ferry')->orderBy('id')->pluck('code')->all();
        echo '  ferry: '.implode(', ', $ferryCodes)."\n";
    }

    ProjectSeed::clearProfile();
    ProjectContext::clear();
}

echo "done\n";
