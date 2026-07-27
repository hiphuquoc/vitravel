<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\CustomTourRequest;
use App\Models\ExperienceAlbum;
use App\Models\ExperienceVideo;
use App\Models\Office;
use App\Models\Package;
use App\Models\QuickInquiryLead;
use App\Models\Review;
use App\Models\TeamMember;
use App\Models\TourCategory;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard.index', [
            'stats' => [
                'tours' => Package::query()->where('type', Package::TYPE_TOUR)->count(),
                'cruises' => Package::query()->where('type', Package::TYPE_CRUISE)->count(),
                'countries' => Country::query()->count(),
                'tour_categories' => TourCategory::query()->count(),
                'articles' => Article::query()->count(),
                'blog_categories' => BlogCategory::query()->count(),
                'team_members' => TeamMember::query()->count(),
                'offices' => Office::query()->count(),
                'reviews' => Review::query()->count(),
                'gallery_albums' => ExperienceAlbum::query()->count(),
                'videos' => ExperienceVideo::query()->count(),
                'quick_inquiries' => QuickInquiryLead::query()->count(),
                'custom_tours' => CustomTourRequest::query()->count(),
                'contacts' => ContactMessage::query()->count(),
                'comments' => Comment::query()->count(),
            ],
        ]);
    }
}
