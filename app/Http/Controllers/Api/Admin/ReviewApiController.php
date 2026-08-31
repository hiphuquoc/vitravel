<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()->with('avatar')->orderBy('sort')->orderByDesc('id');
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('author_name', 'like', "%{$search}%")
                    ->orWhere('author_country', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        $paginator = $query->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $media = app(MediaService::class);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (Review $r) => [
                'id' => $r->id,
                'author_name' => $r->author_name,
                'author_country' => $r->author_country,
                'rating' => $r->rating,
                'status' => $r->status,
                'is_featured' => $r->is_featured,
                'show_on_home' => $r->show_on_home,
                'sort' => $r->sort,
                'avatar' => $media->adminMediaPayload($r->avatar, 'thumb'),
                'updated_at' => $r->updated_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'statuses' => [
                    ['value' => 'published', 'label' => 'Xuất bản'],
                    ['value' => 'draft', 'label' => 'Nháp'],
                    ['value' => 'hidden', 'label' => 'Ẩn'],
                ],
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        return ApiResponse::success([
            'statuses' => [
                ['value' => 'published', 'label' => 'Xuất bản'],
                ['value' => 'draft', 'label' => 'Nháp'],
                ['value' => 'hidden', 'label' => 'Ẩn'],
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $r = Review::query()->with(['avatar', 'mediaAttachments.media'])->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($r));
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
        $row = Review::query()->findOrFail($id);
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return ApiResponse::success(null, 'Đã xóa cảm nhận (kèm media)');
    }

    private function save(Request $request): JsonResponse
    {
        try {
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
                'avatar_media_id' => 'nullable|integer|exists:media,id',
                'remove_avatar' => 'nullable|boolean',
                'gallery_media_ids' => 'nullable|array',
                'gallery_media_ids.*' => 'integer|exists:media,id',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

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

            app(MediaService::class)->syncDirectMediaId(
                $review,
                'avatar_media_id',
                isset($validated['avatar_media_id']) ? (int) $validated['avatar_media_id'] : null,
                $request->boolean('remove_avatar'),
            );

            $this->syncGalleryMediaIds($review, $validated['gallery_media_ids'] ?? []);

            return $review->fresh(['avatar', 'mediaAttachments.media']);
        });

        return ApiResponse::success(
            $this->serializeDetail($review),
            isset($validated['id']) ? 'Đã cập nhật' : 'Đã tạo',
            isset($validated['id']) ? 200 : 201,
        );
    }

    private function syncGalleryMediaIds(Review $review, array $mediaIds): void
    {
        app(MediaService::class)->syncGalleryMediaIds($review, $mediaIds);
    }

    /** @return array<string, mixed> */
    private function serializeDetail(Review $r): array
    {
        $media = app(MediaService::class);

        return [
            'id' => $r->id,
            'author_name' => $r->author_name,
            'author_country' => $r->author_country,
            'author_country_code' => $r->author_country_code,
            'rating' => $r->rating,
            'reviewed_on' => $r->reviewed_on?->format('Y-m-d'),
            'question_title' => $r->question_title,
            'content' => $r->content,
            'photos_count' => $r->photos_count,
            'sort' => $r->sort,
            'status' => $r->status,
            'is_featured' => $r->is_featured,
            'show_on_home' => $r->show_on_home,
            'avatar' => $media->adminMediaPayload($r->avatar, 'card'),
            'gallery' => $media->adminGalleryPayload($r, 'card'),
        ];
    }
}
