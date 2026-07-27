<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Review;
use App\Models\StaticPage;
use App\Models\TourCategory;
use App\Services\ViewDataService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ViewDataService::class);
    }

    public function boot(): void
    {
        Relation::enforceMorphMap([
            'package' => Package::class,
            'article' => Article::class,
            'country' => Country::class,
            'destination' => Destination::class,
            'tour_category' => TourCategory::class,
            'blog_category' => BlogCategory::class,
            'static_page' => StaticPage::class,
            'company' => CompanyProfile::class,
            'review' => Review::class,
        ]);
    }
}
