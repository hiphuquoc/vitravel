<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\HomeFeaturedCountry;
use App\Models\HomeFeaturedReviewPlatform;
use App\Models\Country;
use App\Models\ReviewPlatform;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;

class HomeFeaturedSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanyIdentity();
        $this->seedFeaturedCountries();
        $this->seedFeaturedReviewPlatforms();
    }

    /**
     * Thông tin dự án / liên hệ từ ProjectSeed `company` → company_profiles.
     */
    protected function seedCompanyIdentity(): void
    {
        $src = ProjectSeed::get('company', []);
        if (! is_array($src) || $src === []) {
            $src = config('company', []);
        }
        if (! is_array($src) || $src === []) {
            return;
        }

        $attrs = CompanyProfile::attributesFromSeed($src);
        $profile = CompanyProfile::query()->first();

        if ($profile) {
            // Chỉ fill ô trống — không đè dữ liệu admin đã chỉnh.
            $fill = [];
            foreach ($attrs as $key => $value) {
                $current = $profile->{$key};
                $empty = $current === null
                    || $current === ''
                    || (is_array($current) && $current === []);
                if ($empty && $value !== null && $value !== '') {
                    $fill[$key] = $value;
                }
            }
            if ($fill !== []) {
                $profile->fill($fill)->save();
            }

            return;
        }

        CompanyProfile::query()->create($attrs);
    }

    protected function seedFeaturedCountries(): void
    {
        if (HomeFeaturedCountry::query()->exists()) {
            return;
        }

        Country::query()
            ->orderBy('sort')
            ->limit(8)
            ->get()
            ->each(function (Country $country, int $i) {
                HomeFeaturedCountry::query()->create([
                    'country_id' => $country->id,
                    'sort' => $i,
                ]);
            });
    }

    protected function seedFeaturedReviewPlatforms(): void
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
}
