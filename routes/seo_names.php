<?php

/**
 * Named SEO routes for locale_route()/route() name resolution.
 * Public URLs are served by RoutingController fallback via slug_full — these
 * stubs must not collide with real public paths.
 */
use Illuminate\Support\Facades\Route;

Route::prefix('__seo')->group(function () {
    Route::get('/tours/hub', fn () => abort(404))->name('tours.hub');
    Route::get('/tours/{country}', fn () => abort(404))->name('tours.index');
    Route::get('/tours/{country}/{slug}', fn () => abort(404))->name('tours.show');

    Route::get('/cruises/hub', fn () => abort(404))->name('cruises.hub');
    Route::get('/cruises/{type}', fn () => abort(404))->name('cruises.index');
    Route::get('/cruises/{type}/{slug}', fn () => abort(404))->name('cruises.show');

    Route::get('/guide', fn () => abort(404))->name('guide.index');
    Route::get('/guide/{country}', fn () => abort(404))->name('guide.country');
    Route::get('/guide/{country}/{slug}', fn () => abort(404))->name('guide.show');

    Route::get('/services/{cluster}/hub', fn () => abort(404))->name('services.hub');
    Route::get('/services/{cluster}/{category}', fn () => abort(404))->name('services.index');
    Route::get('/services/{cluster}/{category}/{slug}', fn () => abort(404))->name('services.show');
});
