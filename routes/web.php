<?php

use App\Http\Controllers\CruiseController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');

// ── Tours ────────────────────────────────────────────────────────────────
Route::get('/tours/{country}', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{country}/{slug}', [TourController::class, 'show'])->name('tours.show');

// ── Cruises ──────────────────────────────────────────────────────────────
Route::get('/cruises/{type}', [CruiseController::class, 'index'])->name('cruises.index');
Route::get('/cruises/{type}/{slug}', [CruiseController::class, 'show'])->name('cruises.show');

// ── Travel Guide / Blog ──────────────────────────────────────────────────
Route::get('/cam-nang-du-lich', [GuideController::class, 'index'])->name('guide.index');
Route::get('/cam-nang-du-lich/{country}', [GuideController::class, 'country'])->name('guide.country');
Route::get('/cam-nang-du-lich/{country}/{slug}', [GuideController::class, 'show'])->name('guide.show');

// ── Trang thương hiệu & form ─────────────────────────────────────────────
Route::get('/ve-chung-toi', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::get('/thiet-ke-tour-rieng', [PageController::class, 'customize'])->name('customize');
Route::get('/doi-ngu', [PageController::class, 'team'])->name('team');
Route::get('/cam-nhan-khach-hang', [PageController::class, 'reviews'])->name('reviews');
Route::get('/thu-vien-khoanh-khac', [PageController::class, 'gallery'])->name('gallery');
Route::get('/video-trai-nghiem', [PageController::class, 'videos'])->name('videos');

// ── Lead forms ───────────────────────────────────────────────────────────
Route::post('/leads/quick-inquiry', [LeadController::class, 'storeQuickInquiry'])->name('leads.quick-inquiry');
Route::post('/leads/custom-tour', [LeadController::class, 'storeCustomTour'])->name('leads.custom-tour');
Route::post('/leads/contact', [LeadController::class, 'storeContact'])->name('leads.contact');
Route::post('/leads/comment', [LeadController::class, 'storeComment'])->name('leads.comment');

Route::fallback(fn () => response()->view('errors.404', [], 404));
