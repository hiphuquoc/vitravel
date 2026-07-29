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
        $defaults = [
            'contact_email' => (string) config('company.contact.email', 'hello@vitravel.vn'),
            'contact_phone' => (string) config('company.contact.phone', '+84 24 3999 8888'),
            'contact_whatsapp' => (string) config('company.contact.whatsapp', '+84 912 345 678'),
            'slogan' => (string) config('company.slogan', '“Hài lòng hơn cả mong đợi”'),
            'license_number' => (string) config('company.license_number', ''),
        ];

        $profile = CompanyProfile::query()->first();

        if ($profile) {
            $profile->fill([
                'contact_email' => $profile->contact_email ?: $defaults['contact_email'],
                'contact_phone' => $profile->contact_phone ?: $defaults['contact_phone'],
                'contact_whatsapp' => $profile->contact_whatsapp ?: $defaults['contact_whatsapp'],
                'slogan' => $profile->slogan ?: $defaults['slogan'],
                'license_number' => $profile->license_number ?: $defaults['license_number'],
            ])->save();

            return;
        }

        CompanyProfile::query()->create($defaults);
    }
}
