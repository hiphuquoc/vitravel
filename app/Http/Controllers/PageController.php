<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Models\TeamMember;
use App\Services\ViewDataService;

class PageController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    public function about()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.about', [
            'about' => $this->data->aboutPage(),
            'team' => $this->data->team(),
            'values' => $this->data->values(),
            'reasons' => $this->data->reasons(),
            'referencePersons' => $this->data->referencePersons(),
            'chrome' => $this->data->pageChrome('about'),
        ])->render());
    }

    public function contact()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.contact', [
            'offices' => $this->data->offices(),
            'chrome' => $this->data->pageChrome('contact'),
        ])->render());
    }

    public function customize()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.customize-tour', [
            'chrome' => $this->data->pageChrome('customize'),
            'form' => $this->data->customizeForm(),
        ])->render());
    }

    public function team()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.team', [
            'team' => $this->data->team(),
            'chrome' => $this->data->pageChrome('team'),
        ])->render());
    }

    public function teamShow(TeamMember $member)
    {
        abort_unless($member->is_active, 404);

        $payload = $this->data->formatTeamMember($member);

        return $this->cachedHtmlResponse(fn () => view('pages.team-show', [
            'member' => $payload,
        ])->render());
    }

    public function reviews()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.reviews', [
            'testimonials' => $this->data->testimonials(),
            'chrome' => $this->data->pageChrome('reviews'),
        ])->render());
    }

    public function gallery()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.gallery', [
            'albums' => $this->data->galleryAlbums(),
            'chrome' => $this->data->pageChrome('gallery'),
        ])->render());
    }

    public function videos()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.videos', [
            'videos' => $this->data->videos(),
            'chrome' => $this->data->pageChrome('videos'),
        ])->render());
    }
}
