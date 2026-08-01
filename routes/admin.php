<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CruiseTypeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\HomeSlideController;
use App\Http\Controllers\Admin\HelperController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\ListingHubController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\CompanyValueController;
use App\Http\Controllers\Admin\ExperienceVideoController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\ReasonToChooseUsController;
use App\Http\Controllers\Admin\ReferencePersonController;
use App\Http\Controllers\Admin\ReviewPlatformController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TourCategoryController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/he-thong', [LoginController::class, 'loginForm'])->name('admin.loginForm');
Route::post('/loginAdmin', [LoginController::class, 'loginAdmin'])->name('admin.loginAdmin');
Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::middleware(['auth', 'role:admin'])
    ->prefix('he-thong')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /* ===== Tour packages ===== */
        Route::prefix('san-pham/tour')->group(function () {
            Route::get('/', [PackageController::class, 'list'])->defaults('packageType', 'tour')->name('packages.tours');
            Route::get('/list', [PackageController::class, 'list'])->defaults('packageType', 'tour');
            Route::get('/view', [PackageController::class, 'view'])->defaults('packageType', 'tour')->name('packages.tours.view');
            Route::post('/createAndUpdate', [PackageController::class, 'createAndUpdate'])->defaults('packageType', 'tour')->name('packages.tours.save');
            Route::get('/delete', [PackageController::class, 'delete'])->defaults('packageType', 'tour')->name('packages.tours.delete');
        });

        /* ===== Cruise packages ===== */
        Route::prefix('san-pham/cruise')->group(function () {
            Route::get('/', [PackageController::class, 'list'])->defaults('packageType', 'cruise')->name('packages.cruises');
            Route::get('/list', [PackageController::class, 'list'])->defaults('packageType', 'cruise');
            Route::get('/view', [PackageController::class, 'view'])->defaults('packageType', 'cruise')->name('packages.cruises.view');
            Route::post('/createAndUpdate', [PackageController::class, 'createAndUpdate'])->defaults('packageType', 'cruise')->name('packages.cruises.save');
            Route::get('/delete', [PackageController::class, 'delete'])->defaults('packageType', 'cruise')->name('packages.cruises.delete');
        });

        /* Backward-compatible aliases */
        Route::get('/san-pham/view', fn () => redirect()->route('admin.packages.tours.view', request()->query()));
        Route::post('/san-pham/save', fn () => redirect()->route('admin.packages.tours.save'));
        Route::get('/san-pham/delete', fn () => redirect()->route('admin.packages.tours.delete', request()->query()));

        /* ===== Countries / tour destination tree ===== */
        Route::prefix('san-pham/quoc-gia')->group(function () {
            Route::get('/', [CountryController::class, 'list'])->name('countries.list');
            Route::get('/list', [CountryController::class, 'list']);
            Route::get('/view', [CountryController::class, 'view'])->name('countries.view');
            Route::post('/createAndUpdate', [CountryController::class, 'createAndUpdate'])->name('countries.save');
            Route::get('/delete', [CountryController::class, 'delete'])->name('countries.delete');
        });

        /* ===== Listing hubs (tours / cruises / guide / 5 cụm dịch vụ) ===== */
        Route::get('/san-pham/hub/{hubKey}', [ListingHubController::class, 'edit'])
            ->whereIn('hubKey', [
                'tours_hub', 'cruises_hub', 'guide_hub',
                'trains_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub',
            ])
            ->name('listingHub.edit');
        Route::post('/san-pham/hub/{hubKey}/save', [ListingHubController::class, 'save'])
            ->whereIn('hubKey', [
                'tours_hub', 'cruises_hub', 'guide_hub',
                'trains_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub',
            ])
            ->name('listingHub.save');

        /* ===== Cruise types ===== */
        Route::prefix('san-pham/loai-du-thuyen')->group(function () {
            Route::get('/', [CruiseTypeController::class, 'list'])->name('cruiseTypes.list');
            Route::get('/list', [CruiseTypeController::class, 'list']);
            Route::get('/view', [CruiseTypeController::class, 'view'])->name('cruiseTypes.view');
            Route::post('/createAndUpdate', [CruiseTypeController::class, 'createAndUpdate'])->name('cruiseTypes.save');
            Route::get('/delete', [CruiseTypeController::class, 'delete'])->name('cruiseTypes.delete');
        });

        /* ===== Tour categories ===== */
        Route::prefix('san-pham/danh-muc-tour')->group(function () {
            Route::get('/', [TourCategoryController::class, 'list'])->name('tourCategories.list');
            Route::get('/list', [TourCategoryController::class, 'list']);
            Route::get('/view', [TourCategoryController::class, 'view'])->name('tourCategories.view');
            Route::post('/createAndUpdate', [TourCategoryController::class, 'createAndUpdate'])->name('tourCategories.save');
            Route::get('/delete', [TourCategoryController::class, 'delete'])->name('tourCategories.delete');
        });

        /* ===== Service categories (train/flight/stay/experience/other) ===== */
        Route::prefix('san-pham/danh-muc-dich-vu')->group(function () {
            Route::get('/', [ServiceCategoryController::class, 'list'])->name('serviceCategories.list');
            Route::get('/list', [ServiceCategoryController::class, 'list']);
            Route::get('/view', [ServiceCategoryController::class, 'view'])->name('serviceCategories.view');
            Route::post('/createAndUpdate', [ServiceCategoryController::class, 'createAndUpdate'])->name('serviceCategories.save');
            Route::get('/delete', [ServiceCategoryController::class, 'delete'])->name('serviceCategories.delete');
        });

        /* ===== Service products ===== */
        Route::prefix('san-pham/dich-vu')->group(function () {
            Route::get('/', [ServiceController::class, 'list'])->name('services.list');
            Route::get('/list', [ServiceController::class, 'list']);
            Route::get('/view', [ServiceController::class, 'view'])->name('services.view');
            Route::post('/createAndUpdate', [ServiceController::class, 'createAndUpdate'])->name('services.save');
            Route::get('/delete', [ServiceController::class, 'delete'])->name('services.delete');
        });

        /* ===== Blog categories ===== */
        Route::prefix('chuyen-muc-blog')->group(function () {
            Route::get('/', [BlogCategoryController::class, 'list'])->name('blogCategories.list');
            Route::get('/list', [BlogCategoryController::class, 'list']);
            Route::get('/view', [BlogCategoryController::class, 'view'])->name('blogCategories.view');
            Route::post('/createAndUpdate', [BlogCategoryController::class, 'createAndUpdate'])->name('blogCategories.save');
            Route::get('/delete', [BlogCategoryController::class, 'delete'])->name('blogCategories.delete');
        });

        /* ===== Articles ===== */
        Route::prefix('bai-viet')->group(function () {
            Route::get('/', [ArticleController::class, 'list'])->name('articles.list');
            Route::get('/list', [ArticleController::class, 'list']);
            Route::get('/view', [ArticleController::class, 'view'])->name('articles.view');
            Route::post('/createAndUpdate', [ArticleController::class, 'createAndUpdate'])->name('articles.save');
            Route::get('/delete', [ArticleController::class, 'delete'])->name('articles.delete');
        });

        /* ===== Team members ===== */
        Route::prefix('doi-ngu')->group(function () {
            Route::get('/', [TeamMemberController::class, 'list'])->name('team.list');
            Route::get('/list', [TeamMemberController::class, 'list']);
            Route::get('/view', [TeamMemberController::class, 'view'])->name('team.view');
            Route::post('/createAndUpdate', [TeamMemberController::class, 'createAndUpdate'])->name('team.save');
            Route::get('/delete', [TeamMemberController::class, 'delete'])->name('team.delete');
        });

        /* ===== Customer reviews / testimonials ===== */
        Route::prefix('danh-gia')->group(function () {
            Route::get('/', [ReviewController::class, 'list'])->name('reviews.list');
            Route::get('/list', [ReviewController::class, 'list']);
            Route::get('/view', [ReviewController::class, 'view'])->name('reviews.view');
            Route::post('/createAndUpdate', [ReviewController::class, 'createAndUpdate'])->name('reviews.save');
            Route::get('/delete', [ReviewController::class, 'delete'])->name('reviews.delete');
        });

        /* ===== Leads ===== */
        Route::prefix('yeu-cau-nhanh')->group(function () {
            Route::get('/', [LeadController::class, 'quickInquiries'])->name('leads.quickInquiries');
            Route::post('/status', [LeadController::class, 'updateQuickInquiryStatus'])->name('leads.quickInquiries.status');
        });
        Route::prefix('tour-rieng')->group(function () {
            Route::get('/', [LeadController::class, 'customTours'])->name('leads.customTours');
            Route::post('/status', [LeadController::class, 'updateCustomTourStatus'])->name('leads.customTours.status');
        });
        Route::prefix('lien-he')->group(function () {
            Route::get('/', [LeadController::class, 'contacts'])->name('leads.contacts');
            Route::post('/status', [LeadController::class, 'updateContactStatus'])->name('leads.contacts.status');
        });

        /* ===== Comments ===== */
        Route::prefix('binh-luan')->group(function () {
            Route::get('/', [CommentController::class, 'list'])->name('leads.comments');
            Route::get('/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::get('/reject', [CommentController::class, 'reject'])->name('comments.reject');
        });

        /* ===== Home page content ===== */
        Route::prefix('noi-dung-trang-chu')->group(function () {
            Route::get('/', [HomeSectionController::class, 'edit'])->name('homeSections.edit');
            Route::get('/list', fn () => redirect()->route('admin.homeSections.edit', request()->query()));
            Route::get('/view', fn () => redirect()->route('admin.homeSections.edit', request()->query()));
            Route::post('/save', [HomeSectionController::class, 'save'])->name('homeSections.save');
        });

        /* ===== Home slider ===== */
        Route::prefix('slider-trang-chu')->group(function () {
            Route::get('/', [HomeSlideController::class, 'list'])->name('homeSlides.list');
            Route::get('/list', [HomeSlideController::class, 'list']);
            Route::get('/view', [HomeSlideController::class, 'view'])->name('homeSlides.view');
            Route::post('/createAndUpdate', [HomeSlideController::class, 'createAndUpdate'])->name('homeSlides.save');
            Route::get('/delete', [HomeSlideController::class, 'delete'])->name('homeSlides.delete');
        });

        /* ===== Helpers ===== */
        Route::post('/helper/convertStrToSlug', [HelperController::class, 'convertStrToSlug'])->name('helper.convertStrToSlug');
        Route::post('/helper/slug', [HelperController::class, 'convertStrToSlug'])->name('helper.slug');
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

        /* Placeholders */
        Route::get('/thu-vien-anh', fn () => redirect()->route('admin.videos.list'))->name('gallery.list');

        /* ===== Offices ===== */
        Route::prefix('van-phong')->group(function () {
            Route::get('/', [OfficeController::class, 'list'])->name('offices.list');
            Route::get('/list', [OfficeController::class, 'list']);
            Route::get('/view', [OfficeController::class, 'view'])->name('offices.view');
            Route::post('/createAndUpdate', [OfficeController::class, 'createAndUpdate'])->name('offices.save');
            Route::get('/delete', [OfficeController::class, 'delete'])->name('offices.delete');
        });

        /* ===== Company contact / About CMS ===== */
        Route::get('/cong-ty', [CompanyProfileController::class, 'edit'])->name('company.profile');
        Route::post('/cong-ty/save', [CompanyProfileController::class, 'save'])->name('company.save');

        Route::prefix('gia-tri')->group(function () {
            Route::get('/', [CompanyValueController::class, 'list'])->name('values.list');
            Route::get('/list', [CompanyValueController::class, 'list']);
            Route::get('/view', [CompanyValueController::class, 'view'])->name('values.view');
            Route::post('/createAndUpdate', [CompanyValueController::class, 'createAndUpdate'])->name('values.save');
            Route::get('/delete', [CompanyValueController::class, 'delete'])->name('values.delete');
        });

        Route::prefix('ly-do-chon')->group(function () {
            Route::get('/', [ReasonToChooseUsController::class, 'list'])->name('reasons.list');
            Route::get('/list', [ReasonToChooseUsController::class, 'list']);
            Route::get('/view', [ReasonToChooseUsController::class, 'view'])->name('reasons.view');
            Route::post('/createAndUpdate', [ReasonToChooseUsController::class, 'createAndUpdate'])->name('reasons.save');
            Route::get('/delete', [ReasonToChooseUsController::class, 'delete'])->name('reasons.delete');
        });

        Route::prefix('dai-dien')->group(function () {
            Route::get('/', [ReferencePersonController::class, 'list'])->name('referencePersons.list');
            Route::get('/list', [ReferencePersonController::class, 'list']);
            Route::get('/view', [ReferencePersonController::class, 'view'])->name('referencePersons.view');
            Route::post('/createAndUpdate', [ReferencePersonController::class, 'createAndUpdate'])->name('referencePersons.save');
            Route::get('/delete', [ReferencePersonController::class, 'delete'])->name('referencePersons.delete');
        });

        /* ===== Review platforms ===== */
        Route::prefix('nen-tang-danh-gia')->group(function () {
            Route::get('/', [ReviewPlatformController::class, 'list'])->name('reviewPlatforms.list');
            Route::get('/list', [ReviewPlatformController::class, 'list']);
            Route::get('/view', [ReviewPlatformController::class, 'view'])->name('reviewPlatforms.view');
            Route::post('/createAndUpdate', [ReviewPlatformController::class, 'createAndUpdate'])->name('reviewPlatforms.save');
            Route::get('/delete', [ReviewPlatformController::class, 'delete'])->name('reviewPlatforms.delete');
        });

        /* ===== Experience videos ===== */
        Route::prefix('video')->group(function () {
            Route::get('/', [ExperienceVideoController::class, 'list'])->name('videos.list');
            Route::get('/list', [ExperienceVideoController::class, 'list']);
            Route::get('/view', [ExperienceVideoController::class, 'view'])->name('videos.view');
            Route::post('/createAndUpdate', [ExperienceVideoController::class, 'createAndUpdate'])->name('videos.save');
            Route::get('/delete', [ExperienceVideoController::class, 'delete'])->name('videos.delete');
        });

        Route::get('/cai-dat/ngon-ngu', function () {
            return view('admin.settings.languages', [
                'languages' => \App\Models\Language::listAll(),
            ]);
        })->name('settings.languages');

        Route::get('/cai-dat/xoa-cache-html', [CacheController::class, 'clear'])->name('cache.clear');

        Route::get('/cai-dat/phong-cach', fn () => redirect()->route('admin.dashboard'))->name('settings.travelStyles');
        Route::get('/cai-dat/media', fn () => redirect()->route('admin.homeSlides.list'))->name('settings.media');
    });
