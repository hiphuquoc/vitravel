<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewPlatform;
use App\Support\ProjectUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewPlatformController extends Controller
{
    public function list(): View
    {
        $platforms = ReviewPlatform::query()->orderBy('sort')->orderBy('id')->paginate(20);

        return view('admin.review-platform.list', [
            'platforms' => $platforms,
            'title' => 'Nền tảng đánh giá',
        ]);
    }

    public function view(Request $request): View
    {
        $id = $request->integer('id');
        $platform = $id > 0 ? ReviewPlatform::query()->findOrFail($id) : null;

        return view('admin.review-platform.view', [
            'platform' => $platform,
            'title' => $platform ? 'Chỉnh sửa nền tảng' : 'Thêm nền tảng',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:review_platforms,id',
            'code' => [
                'required',
                'string',
                'max:32',
                'alpha_dash',
                ProjectUnique::rule('review_platforms', 'code')->ignore($request->input('id')),
            ],
            'name' => 'required|string|max:120',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'url' => 'nullable|url|max:500',
            'quote' => 'nullable|string|max:1000',
            'link_label' => 'nullable|string|max:160',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'show_on_home' => 'nullable|boolean',
        ]);

        $platform = isset($validated['id'])
            ? ReviewPlatform::query()->findOrFail($validated['id'])
            : new ReviewPlatform;

        $platform->fill([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'rating' => $validated['rating'] ?? null,
            'review_count' => $validated['review_count'] ?? null,
            'url' => $validated['url'] ?? null,
            'quote' => $validated['quote'] ?? null,
            'link_label' => $validated['link_label'] ?? null,
            'sort' => $validated['sort'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'show_on_home' => $request->boolean('show_on_home'),
        ]);
        $platform->save();

        return redirect()
            ->route('admin.reviewPlatforms.view', ['id' => $platform->id])
            ->with('success', 'Đã lưu nền tảng đánh giá.');
    }

    public function delete(Request $request): RedirectResponse
    {
        ReviewPlatform::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.reviewPlatforms.list')->with('success', 'Đã xóa nền tảng.');
    }
}
