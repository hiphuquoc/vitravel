<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    use ManagesCoverImage;

    public function list(Request $request): View
    {
        $query = Review::query()->with('avatar')->orderBy('sort')->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('author_name', 'like', "%{$search}%")
                    ->orWhere('author_country', 'like', "%{$search}%")
                    ->orWhere('question_title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $reviews = $query->paginate(20)->withQueryString();

        return view('admin.review.list', compact('reviews'));
    }

    public function view(Request $request): View
    {
        $review = $request->filled('id')
            ? Review::query()->with(['avatar', 'mediaAttachments.media'])->findOrFail($request->integer('id'))
            : null;

        $title = $review ? 'Chỉnh sửa cảm nhận' : 'Thêm cảm nhận mới';
        $countryCodes = $this->countryCodeOptions();

        return view('admin.review.view', compact('review', 'title', 'countryCodes'));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $maxKb = (int) config('media.max_upload_kb', 5120);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:reviews,id',
            'author_name' => 'required|string|max:255',
            'author_country' => 'nullable|string|max:120',
            'author_country_code' => 'nullable|string|max:8',
            'rating' => 'required|integer|min:1|max:5',
            'reviewed_on' => 'nullable|date',
            'question_title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'photos_count' => 'nullable|integer|min:0|max:99',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:published,draft,hidden',
            'is_featured' => 'nullable|boolean',
            'show_on_home' => 'nullable|boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:'.$maxKb,
            'remove_gallery' => 'nullable|array',
            'remove_gallery.*' => 'integer',
            ...$this->coverImageRules(),
        ]);

        $review = DB::transaction(function () use ($request, $validated) {
            $review = isset($validated['id'])
                ? Review::query()->findOrFail($validated['id'])
                : new Review;

            $review->fill([
                'author_name' => $validated['author_name'],
                'author_country' => $validated['author_country'] ?? null,
                'author_country_code' => $validated['author_country_code'] ?? null,
                'rating' => $validated['rating'],
                'reviewed_on' => $validated['reviewed_on'] ?? null,
                'question_title' => $validated['question_title'] ?? null,
                'content' => $validated['content'],
                'photos_count' => $validated['photos_count'] ?? 0,
                'sort' => $validated['sort'] ?? 0,
                'status' => $validated['status'],
                'is_featured' => $request->boolean('is_featured'),
                'show_on_home' => $request->boolean('show_on_home'),
                'reviewable_type' => $review->reviewable_type ?: 'company',
                'reviewable_id' => $review->reviewable_id,
            ]);
            $review->save();

            $this->syncDirectCover($review, 'avatar_media_id', $request, config('media.reviews'));
            $this->syncGallery($review, $request);

            return $review->fresh(['avatar', 'mediaAttachments.media']);
        });

        return redirect()
            ->route('admin.reviews.view', ['id' => $review->id])
            ->with('success', 'Đã lưu cảm nhận thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $row = Review::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return redirect()->route('admin.reviews.list')->with('success', 'Đã xóa cảm nhận thành công.');
    }

    protected function syncGallery(Review $review, Request $request): void
    {
        $mediaService = app(MediaService::class);
        $folder = config('media.reviews');

        foreach ((array) $request->input('remove_gallery', []) as $attachmentId) {
            $attachment = $review->mediaAttachments()
                ->where('role', 'gallery')
                ->where('id', (int) $attachmentId)
                ->with('media')
                ->first();
            if (! $attachment) {
                continue;
            }
            $mediaService->deleteMedia($attachment->media);
            $attachment->delete();
        }

        if (! $request->hasFile('gallery')) {
            return;
        }

        $sort = (int) $review->mediaAttachments()->where('role', 'gallery')->max('sort');

        foreach ($request->file('gallery', []) as $file) {
            if (! $file) {
                continue;
            }
            $media = $mediaService->storeUploadedFile($file, $folder);
            $sort++;
            $review->mediaAttachments()->create([
                'media_id' => $media->id,
                'role' => 'gallery',
                'sort' => $sort,
            ]);
        }
    }

    /** @return array<string, string> */
    protected function countryCodeOptions(): array
    {
        return [
            'VN' => '🇻🇳 Việt Nam',
            'AU' => '🇦🇺 Úc',
            'FR' => '🇫🇷 Pháp',
            'IT' => '🇮🇹 Ý',
            'US' => '🇺🇸 Mỹ',
            'GB' => '🇬🇧 Anh',
            'DE' => '🇩🇪 Đức',
            'KR' => '🇰🇷 Hàn Quốc',
            'JP' => '🇯🇵 Nhật Bản',
            'CN' => '🇨🇳 Trung Quốc',
            'TH' => '🇹🇭 Thái Lan',
            'KH' => '🇰🇭 Campuchia',
            'LA' => '🇱🇦 Lào',
            'SG' => '🇸🇬 Singapore',
            'ID' => '🇮🇩 Indonesia',
            'ES' => '🇪🇸 Tây Ban Nha',
            'RU' => '🇷🇺 Nga',
        ];
    }
}
