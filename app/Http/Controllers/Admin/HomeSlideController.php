<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use App\Models\HomeSlideTranslation;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeSlideController extends Controller
{
    use ManagesTranslations;

    public function __construct(protected MediaService $mediaService) {}

    public function list(Request $request): View
    {
        $query = HomeSlide::query()->with(['translations', 'image', 'imageMobile']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        $slides = $query->orderBy('sort')->orderByDesc('id')->paginate(20)->withQueryString();
        $alignOptions = HomeSlide::alignOptions();
        $title = 'Slider trang chủ';

        return view('admin.home-slide.list', compact('slides', 'alignOptions', 'title'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $language = $locale;

        $slide = $request->filled('id')
            ? HomeSlide::query()
                ->with(['translations', 'image', 'imageMobile'])
                ->findOrFail($request->integer('id'))
            : null;

        $languages = $this->activeLanguages();
        $translation = $slide?->translation($locale);
        $alignOptions = HomeSlide::alignOptions();
        $title = $slide ? 'Chỉnh sửa slide' : 'Thêm slide mới';
        $mediaDisk = $this->mediaService->defaultDisk();

        return view('admin.home-slide.view', compact(
            'slide', 'locale', 'language', 'languages', 'translation',
            'alignOptions', 'title', 'mediaDisk',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();
        $maxKb = (int) config('media.max_upload_kb', 5120);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:home_slides,id',
            'text_align' => 'required|string|in:'.implode(',', array_keys(HomeSlide::alignOptions())),
            'link_url' => 'nullable|string|max:500',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'title' => 'nullable|string|max:255',
            'title_accent' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_label' => 'nullable|string|max:100',
            'image_alt' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:'.$maxKb,
            'image_mobile' => 'nullable|image|max:'.$maxKb,
            'remove_image' => 'nullable|boolean',
            'remove_image_mobile' => 'nullable|boolean',
        ]);

        if (empty($validated['id']) && ! $request->hasFile('image')) {
            return redirect()->back()->withInput()->withErrors([
                'image' => 'Vui lòng upload ảnh desktop khi tạo slide mới.',
            ]);
        }

        $slide = DB::transaction(function () use ($request, $validated, $locale) {
            $slide = isset($validated['id'])
                ? HomeSlide::query()->with(['image', 'imageMobile'])->findOrFail($validated['id'])
                : new HomeSlide;

            $slide->fill([
                'text_align' => $validated['text_align'],
                'link_url' => $validated['link_url'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $folder = config('media.home_slider', 'vitravel/home-slider');

            if ($request->boolean('remove_image') && $slide->image) {
                $this->mediaService->deleteMedia($slide->image);
                $slide->image_media_id = null;
            }

            if ($request->boolean('remove_image_mobile') && $slide->imageMobile) {
                $this->mediaService->deleteMedia($slide->imageMobile);
                $slide->image_mobile_media_id = null;
            }

            if ($request->hasFile('image')) {
                if ($slide->image) {
                    $this->mediaService->deleteMedia($slide->image);
                }
                $media = $this->mediaService->storeUploadedFile($request->file('image'), $folder);
                $slide->image_media_id = $media->id;
            }

            if ($request->hasFile('image_mobile')) {
                if ($slide->imageMobile) {
                    $this->mediaService->deleteMedia($slide->imageMobile);
                }
                $media = $this->mediaService->storeUploadedFile($request->file('image_mobile'), $folder.'/mobile');
                $slide->image_mobile_media_id = $media->id;
            }

            $slide->save();

            $this->saveModelTranslation(
                $slide,
                HomeSlideTranslation::class,
                'home_slide_id',
                $locale,
                [
                    'title' => $validated['title'] ?? null,
                    'title_accent' => $validated['title_accent'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'button_label' => $validated['button_label'] ?? null,
                    'image_alt' => $validated['image_alt'] ?? null,
                ],
                ['title', 'title_accent', 'description', 'button_label', 'image_alt'],
            );

            if ($slide->image && filled($validated['image_alt'] ?? null)) {
                $slide->image->update(['alt' => $validated['image_alt']]);
            }

            return $slide;
        });

        return redirect()
            ->route('admin.homeSlides.view', ['id' => $slide->id, 'language' => $locale])
            ->with('success', 'Đã lưu slide thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $slide = HomeSlide::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($slide);

        return redirect()->route('admin.homeSlides.list')->with('success', 'Đã xóa slide thành công.');
    }
}
