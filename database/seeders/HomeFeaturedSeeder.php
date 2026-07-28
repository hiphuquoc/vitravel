<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\HomeFeaturedCountry;
use App\Models\HomeFeaturedReviewPlatform;
use App\Models\ReviewPlatform;
use App\Support\SampleData;
use Illuminate\Database\Seeder;

class HomeFeaturedSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFeaturedCountries();
        $this->seedFeaturedPlatforms();
        $this->seedCompanyContact();
    }

    protected function seedFeaturedCountries(): void
    {
        if (HomeFeaturedCountry::query()->exists()) {
            return;
        }

        $sort = 0;
        foreach (SampleData::countries() as $row) {
            $country = Country::query()
                ->whereHas('translations', fn ($q) => $q->where('slug', $row['slug']))
                ->first();

            if (! $country) {
                continue;
            }

            HomeFeaturedCountry::query()->create([
                'country_id' => $country->id,
                'sort' => $sort++,
            ]);
        }
    }

    protected function seedFeaturedPlatforms(): void
    {
        if (HomeFeaturedReviewPlatform::query()->exists()) {
            return;
        }

        ReviewPlatform::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->each(function (ReviewPlatform $platform, int $i) {
                HomeFeaturedReviewPlatform::query()->create([
                    'review_platform_id' => $platform->id,
                    'sort' => $i,
                ]);
            });
    }

    protected function seedCompanyContact(): void
    {
        $profile = CompanyProfile::query()->first();

        if ($profile) {
            $profile->fill([
                'contact_email' => $profile->contact_email ?: 'hello@vitravel.example',
                'contact_phone' => $profile->contact_phone ?: '+84 24 3999 8888',
                'contact_whatsapp' => $profile->contact_whatsapp ?: '+84 912 345 678',
                'slogan' => $profile->slogan ?: '“Hài lòng hơn cả mong đợi”',
            ])->save();

            return;
        }

        CompanyProfile::query()->create([
            'contact_email' => 'hello@vitravel.example',
            'contact_phone' => '+84 24 3999 8888',
            'contact_whatsapp' => '+84 912 345 678',
            'slogan' => '“Hài lòng hơn cả mong đợi”',
        ]);
    }
}
