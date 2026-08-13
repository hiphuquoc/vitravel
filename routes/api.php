<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\AiApiController;
use App\Http\Controllers\Api\Admin\ArticleApiController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BlogCategoryApiController;
use App\Http\Controllers\Api\Admin\CacheApiController;
use App\Http\Controllers\Api\Admin\CommentApiController;
use App\Http\Controllers\Api\Admin\CompanyProfileApiController;
use App\Http\Controllers\Api\Admin\CompanyValueApiController;
use App\Http\Controllers\Api\Admin\CountryApiController;
use App\Http\Controllers\Api\Admin\CruiseTypeApiController;
use App\Http\Controllers\Api\Admin\ExperienceAlbumApiController;
use App\Http\Controllers\Api\Admin\ExperienceVideoApiController;
use App\Http\Controllers\Api\Admin\HomeSectionApiController;
use App\Http\Controllers\Api\Admin\HomeSlideApiController;
use App\Http\Controllers\Api\Admin\LanguageApiController;
use App\Http\Controllers\Api\Admin\LeadApiController;
use App\Http\Controllers\Api\Admin\ListingHubApiController;
use App\Http\Controllers\Api\Admin\MediaApiController;
use App\Http\Controllers\Api\Admin\MediaLibraryApiController;
use App\Http\Controllers\Api\Admin\MetaApiController;
use App\Http\Controllers\Api\Admin\OfficeApiController;
use App\Http\Controllers\Api\Admin\PackageApiController;
use App\Http\Controllers\Api\Admin\ReasonApiController;
use App\Http\Controllers\Api\Admin\ReferencePersonApiController;
use App\Http\Controllers\Api\Admin\ReviewApiController;
use App\Http\Controllers\Api\Admin\ReviewPlatformApiController;
use App\Http\Controllers\Api\Admin\ServiceApiController;
use App\Http\Controllers\Api\Admin\ServiceCategoryApiController;
use App\Http\Controllers\Api\Admin\TeamMemberApiController;
use App\Http\Controllers\Api\Admin\TourCategoryApiController;
use App\Http\Controllers\Api\Admin\TravelStyleApiController;
use App\Http\Controllers\Api\Admin\UserApiController;
use App\Http\Controllers\Api\Admin\ProjectApiController;
use App\Http\Middleware\AuthenticateAdminApi;
use App\Http\Middleware\AuthorizeAdminPermission;
use App\Http\Middleware\ResolveAdminProject;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Console API (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1/admin')->group(function () {
    // Health / smoke check (browser GET) — không thay thế auth
    Route::get('/', function () {
        return \App\Support\ApiResponse::success([
            'service' => 'vitravel-admin-api',
            'version' => 'v1',
            'login' => url('/api/v1/admin/auth/login'),
        ], 'Admin API OK');
    });
    Route::get('/health', function () {
        return \App\Support\ApiResponse::success(['ok' => true], 'OK');
    });

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware([AuthenticateAdminApi::class])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/me', [AuthController::class, 'updateProfile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/projects', [ProjectApiController::class, 'index']);
        Route::get('/projects/{id}', [ProjectApiController::class, 'show'])->whereNumber('id');

        Route::middleware([ResolveAdminProject::class, AuthorizeAdminPermission::class])->group(function () {
            Route::get('/users/meta', [UserApiController::class, 'meta']);
            Route::get('/users', [UserApiController::class, 'index']);
            Route::post('/users', [UserApiController::class, 'store']);
            Route::get('/users/{id}', [UserApiController::class, 'show'])->whereNumber('id');
            Route::put('/users/{id}', [UserApiController::class, 'update'])->whereNumber('id');
            Route::delete('/users/{id}', [UserApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/meta/languages', [MetaApiController::class, 'languages']);

            Route::get('/ai/status', [AiApiController::class, 'status']);
            Route::post('/ai/translate-page', [AiApiController::class, 'translatePage']);
            Route::post('/ai/enrich-detail-program', [AiApiController::class, 'enrichDetailProgram']);
            Route::post('/ai/enrich-listing-page', [AiApiController::class, 'enrichListingPage']);
            Route::get('/ai/prompts', [AiApiController::class, 'prompts']);
            Route::post('/ai/prompts/sync', [AiApiController::class, 'syncPrompts']);
            Route::get('/ai/prompts/{key}', [AiApiController::class, 'showPrompt']);
            Route::put('/ai/prompts/{key}', [AiApiController::class, 'updatePrompt']);
            Route::get('/ai/usage', [AiApiController::class, 'usage']);

            Route::get('/media/meta', [MediaApiController::class, 'meta']);
            Route::get('/media/video-meta', [MediaApiController::class, 'videoMeta']);
            Route::post('/media/upload', [MediaApiController::class, 'upload']);
            Route::post('/media/upload-video', [MediaApiController::class, 'uploadVideo']);
            Route::get('/media/library', [MediaLibraryApiController::class, 'index']);
            Route::get('/media/library/{id}', [MediaLibraryApiController::class, 'show'])->whereNumber('id');
            Route::put('/media/library/{id}', [MediaLibraryApiController::class, 'update'])->whereNumber('id');
            Route::delete('/media/library/{id}', [MediaLibraryApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/packages/meta', [PackageApiController::class, 'meta']);
            Route::get('/packages', [PackageApiController::class, 'index']);
            Route::post('/packages', [PackageApiController::class, 'store']);
            Route::get('/packages/{id}', [PackageApiController::class, 'show'])->whereNumber('id');
            Route::put('/packages/{id}', [PackageApiController::class, 'update'])->whereNumber('id');
            Route::delete('/packages/{id}', [PackageApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/tour-categories/meta', [TourCategoryApiController::class, 'meta']);
            Route::get('/tour-categories', [TourCategoryApiController::class, 'index']);
            Route::post('/tour-categories', [TourCategoryApiController::class, 'store']);
            Route::get('/tour-categories/{id}', [TourCategoryApiController::class, 'show'])->whereNumber('id');
            Route::put('/tour-categories/{id}', [TourCategoryApiController::class, 'update'])->whereNumber('id');
            Route::delete('/tour-categories/{id}', [TourCategoryApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/cruise-types/meta', [CruiseTypeApiController::class, 'meta']);
            Route::get('/cruise-types', [CruiseTypeApiController::class, 'index']);
            Route::post('/cruise-types', [CruiseTypeApiController::class, 'store']);
            Route::get('/cruise-types/{id}', [CruiseTypeApiController::class, 'show'])->whereNumber('id');
            Route::put('/cruise-types/{id}', [CruiseTypeApiController::class, 'update'])->whereNumber('id');
            Route::delete('/cruise-types/{id}', [CruiseTypeApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/travel-styles', [TravelStyleApiController::class, 'index']);
            Route::post('/travel-styles', [TravelStyleApiController::class, 'store']);
            Route::get('/travel-styles/{id}', [TravelStyleApiController::class, 'show'])->whereNumber('id');
            Route::put('/travel-styles/{id}', [TravelStyleApiController::class, 'update'])->whereNumber('id');
            Route::delete('/travel-styles/{id}', [TravelStyleApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/countries/meta', [CountryApiController::class, 'meta']);
            Route::get('/countries', [CountryApiController::class, 'index']);
            Route::post('/countries', [CountryApiController::class, 'store']);
            Route::get('/countries/{id}', [CountryApiController::class, 'show'])->whereNumber('id');
            Route::put('/countries/{id}', [CountryApiController::class, 'update'])->whereNumber('id');
            Route::patch('/countries/{id}/active', [CountryApiController::class, 'setActive'])->whereNumber('id');
            Route::delete('/countries/{id}', [CountryApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/service-categories/meta', [ServiceCategoryApiController::class, 'meta']);
            Route::get('/service-categories', [ServiceCategoryApiController::class, 'index']);
            Route::post('/service-categories', [ServiceCategoryApiController::class, 'store']);
            Route::get('/service-categories/{id}', [ServiceCategoryApiController::class, 'show'])->whereNumber('id');
            Route::put('/service-categories/{id}', [ServiceCategoryApiController::class, 'update'])->whereNumber('id');
            Route::delete('/service-categories/{id}', [ServiceCategoryApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/services/meta', [ServiceApiController::class, 'meta']);
            Route::get('/services', [ServiceApiController::class, 'index']);
            Route::post('/services', [ServiceApiController::class, 'store']);
            Route::get('/services/{id}', [ServiceApiController::class, 'show'])->whereNumber('id');
            Route::put('/services/{id}', [ServiceApiController::class, 'update'])->whereNumber('id');
            Route::delete('/services/{id}', [ServiceApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/home-slides/meta', [HomeSlideApiController::class, 'meta']);
            Route::get('/home-slides', [HomeSlideApiController::class, 'index']);
            Route::post('/home-slides', [HomeSlideApiController::class, 'store']);
            Route::get('/home-slides/{id}', [HomeSlideApiController::class, 'show'])->whereNumber('id');
            Route::put('/home-slides/{id}', [HomeSlideApiController::class, 'update'])->whereNumber('id');
            Route::delete('/home-slides/{id}', [HomeSlideApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/home-sections', [HomeSectionApiController::class, 'show']);
            Route::put('/home-sections', [HomeSectionApiController::class, 'update']);

            Route::get('/blog-categories/meta', [BlogCategoryApiController::class, 'meta']);
            Route::get('/blog-categories', [BlogCategoryApiController::class, 'index']);
            Route::post('/blog-categories', [BlogCategoryApiController::class, 'store']);
            Route::get('/blog-categories/{id}', [BlogCategoryApiController::class, 'show'])->whereNumber('id');
            Route::put('/blog-categories/{id}', [BlogCategoryApiController::class, 'update'])->whereNumber('id');
            Route::delete('/blog-categories/{id}', [BlogCategoryApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/articles/meta', [ArticleApiController::class, 'meta']);
            Route::get('/articles', [ArticleApiController::class, 'index']);
            Route::post('/articles', [ArticleApiController::class, 'store']);
            Route::get('/articles/{id}', [ArticleApiController::class, 'show'])->whereNumber('id');
            Route::put('/articles/{id}', [ArticleApiController::class, 'update'])->whereNumber('id');
            Route::delete('/articles/{id}', [ArticleApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/team-members/meta', [TeamMemberApiController::class, 'meta']);
            Route::get('/team-members', [TeamMemberApiController::class, 'index']);
            Route::post('/team-members', [TeamMemberApiController::class, 'store']);
            Route::get('/team-members/{id}', [TeamMemberApiController::class, 'show'])->whereNumber('id');
            Route::put('/team-members/{id}', [TeamMemberApiController::class, 'update'])->whereNumber('id');
            Route::delete('/team-members/{id}', [TeamMemberApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/offices/meta', [OfficeApiController::class, 'meta']);
            Route::get('/offices', [OfficeApiController::class, 'index']);
            Route::post('/offices', [OfficeApiController::class, 'store']);
            Route::get('/offices/{id}', [OfficeApiController::class, 'show'])->whereNumber('id');
            Route::put('/offices/{id}', [OfficeApiController::class, 'update'])->whereNumber('id');
            Route::delete('/offices/{id}', [OfficeApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/company-profile', [CompanyProfileApiController::class, 'show']);
            Route::put('/company-profile', [CompanyProfileApiController::class, 'update']);

            Route::get('/company-values', [CompanyValueApiController::class, 'index']);
            Route::post('/company-values', [CompanyValueApiController::class, 'store']);
            Route::get('/company-values/{id}', [CompanyValueApiController::class, 'show'])->whereNumber('id');
            Route::put('/company-values/{id}', [CompanyValueApiController::class, 'update'])->whereNumber('id');
            Route::delete('/company-values/{id}', [CompanyValueApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/reasons', [ReasonApiController::class, 'index']);
            Route::post('/reasons', [ReasonApiController::class, 'store']);
            Route::get('/reasons/{id}', [ReasonApiController::class, 'show'])->whereNumber('id');
            Route::put('/reasons/{id}', [ReasonApiController::class, 'update'])->whereNumber('id');
            Route::delete('/reasons/{id}', [ReasonApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/reference-persons', [ReferencePersonApiController::class, 'index']);
            Route::post('/reference-persons', [ReferencePersonApiController::class, 'store']);
            Route::get('/reference-persons/{id}', [ReferencePersonApiController::class, 'show'])->whereNumber('id');
            Route::put('/reference-persons/{id}', [ReferencePersonApiController::class, 'update'])->whereNumber('id');
            Route::delete('/reference-persons/{id}', [ReferencePersonApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/reviews', [ReviewApiController::class, 'index']);
            Route::get('/reviews/meta', [ReviewApiController::class, 'meta']);
            Route::post('/reviews', [ReviewApiController::class, 'store']);
            Route::get('/reviews/{id}', [ReviewApiController::class, 'show'])->whereNumber('id');
            Route::put('/reviews/{id}', [ReviewApiController::class, 'update'])->whereNumber('id');
            Route::delete('/reviews/{id}', [ReviewApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/review-platforms', [ReviewPlatformApiController::class, 'index']);
            Route::post('/review-platforms', [ReviewPlatformApiController::class, 'store']);
            Route::get('/review-platforms/{id}', [ReviewPlatformApiController::class, 'show'])->whereNumber('id');
            Route::put('/review-platforms/{id}', [ReviewPlatformApiController::class, 'update'])->whereNumber('id');
            Route::delete('/review-platforms/{id}', [ReviewPlatformApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/gallery-albums/meta', [ExperienceAlbumApiController::class, 'meta']);
            Route::get('/gallery-albums', [ExperienceAlbumApiController::class, 'index']);
            Route::post('/gallery-albums', [ExperienceAlbumApiController::class, 'store']);
            Route::get('/gallery-albums/{id}', [ExperienceAlbumApiController::class, 'show'])->whereNumber('id');
            Route::put('/gallery-albums/{id}', [ExperienceAlbumApiController::class, 'update'])->whereNumber('id');
            Route::delete('/gallery-albums/{id}', [ExperienceAlbumApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/videos/meta', [ExperienceVideoApiController::class, 'meta']);
            Route::get('/videos', [ExperienceVideoApiController::class, 'index']);
            Route::post('/videos', [ExperienceVideoApiController::class, 'store']);
            Route::get('/videos/{id}', [ExperienceVideoApiController::class, 'show'])->whereNumber('id');
            Route::put('/videos/{id}', [ExperienceVideoApiController::class, 'update'])->whereNumber('id');
            Route::delete('/videos/{id}', [ExperienceVideoApiController::class, 'destroy'])->whereNumber('id');

            Route::get('/leads/quick-inquiries', [LeadApiController::class, 'quickInquiries']);
            Route::put('/leads/quick-inquiries/{id}/status', [LeadApiController::class, 'updateQuickInquiryStatus'])->whereNumber('id');
            Route::get('/leads/custom-tours', [LeadApiController::class, 'customTours']);
            Route::put('/leads/custom-tours/{id}/status', [LeadApiController::class, 'updateCustomTourStatus'])->whereNumber('id');
            Route::get('/leads/contacts', [LeadApiController::class, 'contacts']);
            Route::put('/leads/contacts/{id}/status', [LeadApiController::class, 'updateContactStatus'])->whereNumber('id');

            Route::get('/comments', [CommentApiController::class, 'index']);
            Route::post('/comments/{id}/approve', [CommentApiController::class, 'approve'])->whereNumber('id');
            Route::post('/comments/{id}/reject', [CommentApiController::class, 'reject'])->whereNumber('id');

            Route::get('/languages', [LanguageApiController::class, 'index']);
            Route::get('/cache/meta', [CacheApiController::class, 'meta']);
            Route::post('/cache/clear', [CacheApiController::class, 'clear']);
            Route::post('/cache/clear-batch', [CacheApiController::class, 'clearBatch']);
            Route::post('/cache/finish', [CacheApiController::class, 'finish']);
            Route::get('/listing-hubs/{hubKey}', [ListingHubApiController::class, 'show']);
            Route::put('/listing-hubs/{hubKey}', [ListingHubApiController::class, 'update']);
        });
    });
});
