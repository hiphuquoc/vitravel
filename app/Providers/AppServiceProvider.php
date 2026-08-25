<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaticPage;
use App\Models\TeamMember;
use App\Models\TourCategory;
use App\Models\User;
use App\Services\CurrencyManager;
use App\Services\MediaService;
use App\Services\SeoService;
use App\Services\StayTaxonomyService;
use App\Services\ViewDataService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ViewDataService::class);
        $this->app->singleton(CurrencyManager::class);
        $this->app->singleton(SeoService::class);
        $this->app->singleton(StayTaxonomyService::class);
        $this->app->singleton(MediaService::class);

        // Avoid touch() Utime failures when PHP-FPM is not owner of compiled views (WSL).
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new \App\View\Compilers\SafeBladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
                $app['config']->get('view.check_cache_timestamps', true),
            ), function ($blade) {
                $blade->component('dynamic-component', \Illuminate\View\DynamicComponent::class);
            });
        });
    }

    public function boot(): void
    {
        // Multi-domain public: không ép APP_URL trên HTTP — ResolveProjectFromHost
        // sẽ forceRootUrl theo Host. CLI/queue vẫn dùng APP_URL.
        if ($this->app->runningInConsole()) {
            $appUrl = config('app.url');
            if (is_string($appUrl) && $appUrl !== '') {
                URL::forceRootUrl($appUrl);
            }
        }

        Relation::enforceMorphMap([
            'user' => User::class,
            'package' => Package::class,
            'article' => Article::class,
            'country' => Country::class,
            'destination' => Destination::class,
            'tour_category' => TourCategory::class,
            'blog_category' => BlogCategory::class,
            'static_page' => StaticPage::class,
            'company' => CompanyProfile::class,
            'review' => Review::class,
            'cruise_type' => \App\Models\CruiseType::class,
            'team_member' => TeamMember::class,
            'service_category' => ServiceCategory::class,
            'service' => Service::class,
        ]);
    }
}
