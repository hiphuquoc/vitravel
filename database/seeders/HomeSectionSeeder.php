<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\HeroPill;
use App\Models\HeroPillTranslation;
use App\Models\HomeFeaturedCruise;
use App\Models\HomeFeaturedTour;
use App\Models\HomeSection;
use App\Models\HomeSectionTranslation;
use App\Models\Language;
use App\Models\Package;
use App\Models\TourCategory;
use App\Models\Usp;
use App\Models\UspTranslation;
use App\Support\HomePageDefaults;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $viId = Language::idByCode('vi');
        $enId = Language::idByCode('en');

        foreach (HomePageDefaults::sections() as $row) {
            $section = HomeSection::query()->updateOrCreate(
                ['key' => $row['key']],
                ['sort' => $row['sort'], 'is_active' => true],
            );

            foreach (['vi' => $viId, 'en' => $enId] as $code => $langId) {
                if (! $langId || empty($row[$code])) {
                    continue;
                }

                HomeSectionTranslation::query()->updateOrCreate(
                    ['home_section_id' => $section->id, 'language_id' => $langId],
                    $row[$code],
                );
            }
        }

        foreach (HomePageDefaults::usps() as $row) {
            $usp = Usp::query()->updateOrCreate(
                ['sort' => $row['sort']],
                ['icon' => $row['icon'], 'is_active' => true],
            );

            foreach (['vi' => $viId, 'en' => $enId] as $code => $langId) {
                if (! $langId) {
                    continue;
                }

                UspTranslation::query()->updateOrCreate(
                    ['usp_id' => $usp->id, 'language_id' => $langId],
                    [
                        'title' => $row[$code]['title'],
                        'description' => $row[$code]['description'],
                    ],
                );
            }
        }

        $this->seedHeroPills($viId, $enId);
        $this->seedFeaturedTours();
        $this->seedFeaturedCruises();
    }

    public static function seedHeroPillsIfEmpty(): void
    {
        if (HeroPill::query()->exists()) {
            return;
        }

        (new self)->seedHeroPills(Language::idByCode('vi'), Language::idByCode('en'));
    }

    public static function seedFeaturedToursIfEmpty(): void
    {
        if (HomeFeaturedTour::query()->exists()) {
            return;
        }

        (new self)->seedFeaturedTours();
    }

    public static function seedFeaturedCruisesIfEmpty(): void
    {
        if (HomeFeaturedCruise::query()->exists()) {
            return;
        }

        (new self)->seedFeaturedCruises();
    }

    protected function seedFeaturedTours(): void
    {
        $packages = Package::query()
            ->where('type', Package::TYPE_TOUR)
            ->where('status', 'published')
            ->orderBy('sort')
            ->limit(3)
            ->get();

        foreach ($packages as $sort => $package) {
            HomeFeaturedTour::query()->updateOrCreate(
                ['package_id' => $package->id],
                ['sort' => $sort],
            );
        }
    }

    protected function seedFeaturedCruises(): void
    {
        $packages = Package::query()
            ->where('type', Package::TYPE_CRUISE)
            ->where('status', 'published')
            ->orderBy('sort')
            ->limit(3)
            ->get();

        foreach ($packages as $sort => $package) {
            HomeFeaturedCruise::query()->updateOrCreate(
                ['package_id' => $package->id],
                ['sort' => $sort],
            );
        }
    }

    protected function seedHeroPills(?int $viId, ?int $enId): void
    {
        foreach (HomePageDefaults::heroPills() as $row) {
            $categoryId = null;
            $countryId = null;

            if (! empty($row['category_slug'])) {
                $categoryId = TourCategory::query()
                    ->whereHas('translations', fn ($q) => $q->where('slug', $row['category_slug']))
                    ->value('id');
            }

            if (! empty($row['country_slug'])) {
                $countryId = Country::query()
                    ->whereHas('translations', fn ($q) => $q->where('slug', $row['country_slug']))
                    ->value('id');
            }

            $pill = HeroPill::query()->updateOrCreate(
                ['sort' => $row['sort']],
                [
                    'tour_category_id' => $categoryId,
                    'country_id' => $countryId,
                    'target_url' => null,
                    'is_active' => true,
                ],
            );

            foreach (['vi' => $viId, 'en' => $enId] as $code => $langId) {
                if (! $langId || empty($row[$code]['label'])) {
                    continue;
                }

                HeroPillTranslation::query()->updateOrCreate(
                    ['hero_pill_id' => $pill->id, 'language_id' => $langId],
                    ['label' => $row[$code]['label']],
                );
            }
        }
    }
}
