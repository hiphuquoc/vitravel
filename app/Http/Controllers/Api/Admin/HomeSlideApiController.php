<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use App\Models\HomeSlideTranslation;
use App\Models\Language;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HomeSlideApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = HomeSlide::query()->with(['translations', 'image', 'imageMobile'])->orderBy('sort')->orderBy('id');

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $items = collect($paginator->items())->map(fn (HomeSlide $s) => $this->serialize($s, $locale));

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'align_options' => collect(HomeSlide::alignOptions())->map(
                fn ($label, $value) => ['value' => $value, 'label' => $label]
            )->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);
        $slide = HomeSlide::query()->with(['translations', 'image', 'imageMobile'])->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($slide, $locale));
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
        $row = HomeSlide::query()->findOrFail($id);
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return ApiResponse::success(null, 'Đã xóa slide (kèm media)');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        try {
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
                'image_media_id' => 'nullable|integer|exists:media,id',
                'remove_image' => 'nullable|boolean',
                'image_mobile_media_id' => 'nullable|integer|exists:media,id',
                'remove_image_mobile' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        if (empty($validated['id']) && empty($validated['image_media_id'])) {
            return ApiResponse::error('Vui lòng upload ảnh desktop khi tạo slide mới.', 'VALIDATION', 422);
        }

        $slide = DB::transaction(function () use ($request, $validated, $locale) {
            $slide = isset($validated['id'])
                ? HomeSlide::query()->findOrFail($validated['id'])
                : new HomeSlide;

            $slide->fill([
                'text_align' => $validated['text_align'],
                'link_url' => $validated['link_url'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $slide->save();

            $media = app(MediaService::class);
            $media->syncDirectMediaId(
                $slide,
                'image_media_id',
                isset($validated['image_media_id']) ? (int) $validated['image_media_id'] : null,
                $request->boolean('remove_image'),
            );
            $media->syncDirectMediaId(
                $slide,
                'image_mobile_media_id',
                isset($validated['image_mobile_media_id']) ? (int) $validated['image_mobile_media_id'] : null,
                $request->boolean('remove_image_mobile'),
            );

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

            $slide->load(['image', 'imageMobile']);
            if ($slide->image && filled($validated['image_alt'] ?? null)) {
                $slide->image->update(['alt' => $validated['image_alt']]);
            }

            return $slide->fresh(['translations', 'image', 'imageMobile']);
        });

        return ApiResponse::success(
            $this->serializeDetail($slide, $locale),
            isset($validated['id']) ? 'Đã cập nhật slide' : 'Đã tạo slide',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(HomeSlide $slide, string $locale): array
    {
        $t = $slide->translation($locale);
        $media = app(MediaService::class);

        return [
            'id' => $slide->id,
            'title' => $t?->title,
            'text_align' => $slide->text_align,
            'link_url' => $slide->link_url,
            'sort' => $slide->sort,
            'is_active' => $slide->is_active,
            'image' => $media->adminMediaPayload($slide->image, 'thumb'),
            'updated_at' => $slide->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(HomeSlide $slide, string $locale): array
    {
        $t = $slide->translation($locale);
        $media = app(MediaService::class);

        return array_merge($this->serialize($slide, $locale), [
            'title_accent' => $t?->title_accent,
            'description' => $t?->description,
            'button_label' => $t?->button_label,
            'image_alt' => $t?->image_alt,
            'translated_locales' => $this->translatedLocaleCodes($slide, 'title'),
            'image' => $media->adminMediaPayload($slide->image, 'lg'),
            'image_mobile' => $media->adminMediaPayload($slide->imageMobile, 'card'),
        ]);
    }
}
