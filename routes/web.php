<?php

use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
| Public GET pages — registered twice: default locale (named) + /{locale}/ (unnamed).
| Tours / cruises / guide listing URLs are catch-all via RoutingController + slug_full.
| Named SEO stubs in routes/seo_names.php keep route()/locale_route fallbacks valid.
*/
$registerPublicRoutes = function (bool $named = true): void {
    $home = Route::get('/', [HomeController::class, 'index']);
    $search = Route::get('/tim-kiem', [SearchController::class, 'index']);

    $about = Route::get('/ve-chung-toi', [PageController::class, 'about']);
    $contact = Route::get('/lien-he', [PageController::class, 'contact']);
    $customize = Route::get('/thiet-ke-tour-rieng', [PageController::class, 'customize']);
    $team = Route::get('/doi-ngu', [PageController::class, 'team']);
    $reviews = Route::get('/cam-nhan-khach-hang', [PageController::class, 'reviews']);
    $gallery = Route::get('/thu-vien-khoanh-khac', [PageController::class, 'gallery']);
    $videos = Route::get('/video-trai-nghiem', [PageController::class, 'videos']);

    if ($named) {
        $home->name('home');
        $search->name('search');
        $about->name('about');
        $contact->name('contact');
        $customize->name('customize');
        $team->name('team');
        $reviews->name('reviews');
        $gallery->name('gallery');
        $videos->name('videos');
    }
};

/* Default locale (vi) — unprefixed, named routes */
$registerPublicRoutes(true);

/* Non-default locales: /en/..., /zh-cn/..., ... — same handlers, no route names */
$localePattern = collect(config('language.list', []))
    ->reject(fn ($l) => ! empty($l['is_default']))
    ->pluck('code')
    ->map(fn ($c) => preg_quote((string) $c, '/'))
    ->implode('|');

if ($localePattern !== '') {
    Route::prefix('{locale}')
        ->where(['locale' => $localePattern])
        ->group(function () use ($registerPublicRoutes) {
            $registerPublicRoutes(false);
        });
}

/* Named SEO route stubs — URI never hit publicly; locale_route resolves via slug_full */
require __DIR__.'/seo_names.php';

Route::match(['get', 'post'], '/currency/switch', [CurrencyController::class, 'switch'])
    ->name('currency.switch');

// ── Listing JSON (filter / skeleton fetch — không reload trang) ───────────
Route::prefix('api/listings')->name('api.listings.')->group(function () {
    Route::get('/tours', [ListingController::class, 'tours'])->name('tours');
    Route::get('/cruises', [ListingController::class, 'cruises'])->name('cruises');
    Route::get('/services', [ListingController::class, 'services'])->name('services');
    Route::get('/featured-tours', [ListingController::class, 'featuredTours'])->name('featured-tours');
    Route::get('/featured-cruises', [ListingController::class, 'featuredCruises'])->name('featured-cruises');
    Route::get('/featured-services', [ListingController::class, 'featuredServices'])->name('featured-services');
    Route::get('/featured-support', [ListingController::class, 'featuredSupport'])->name('featured-support');
    Route::get('/related', [ListingController::class, 'related'])->name('related');
});

// ── Lead forms ───────────────────────────────────────────────────────────
Route::post('/leads/quick-inquiry', [LeadController::class, 'storeQuickInquiry'])->name('leads.quick-inquiry');
Route::post('/leads/custom-tour', [LeadController::class, 'storeCustomTour'])->name('leads.custom-tour');
Route::post('/leads/contact', [LeadController::class, 'storeContact'])->name('leads.contact');
Route::post('/leads/comment', [LeadController::class, 'storeComment'])->name('leads.comment');

Route::fallback([RoutingController::class, 'routing']);
