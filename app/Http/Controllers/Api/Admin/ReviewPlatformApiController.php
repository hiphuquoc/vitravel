<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewPlatform;
use App\Support\ApiResponse;
use App\Support\ProjectUnique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewPlatformApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paginator = ReviewPlatform::query()->orderBy('sort')->orderBy('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (ReviewPlatform $p) => $this->serialize($p)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->serialize(ReviewPlatform::query()->findOrFail($id)));
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);

        return $this->save($request);
    }

    public function destroy(int $id): JsonResponse
    {
        ReviewPlatform::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa nền tảng');
    }

    private function save(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:review_platforms,id',
                'code' => [
                    'required', 'string', 'max:32', 'alpha_dash',
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
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

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
            'is_active' => $request->boolean('is_active', true),
            'show_on_home' => $request->boolean('show_on_home'),
        ]);
        $platform->save();

        return ApiResponse::success(
            $this->serialize($platform),
            isset($validated['id']) ? 'Đã cập nhật' : 'Đã tạo',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(ReviewPlatform $p): array
    {
        return [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'rating' => $p->rating,
            'review_count' => $p->review_count,
            'url' => $p->url,
            'quote' => $p->quote,
            'link_label' => $p->link_label,
            'sort' => $p->sort,
            'is_active' => $p->is_active,
            'show_on_home' => $p->show_on_home,
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }
}
