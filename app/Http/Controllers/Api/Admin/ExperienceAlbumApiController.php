<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExperienceAlbum;
use App\Models\ExperienceAlbumTranslation;
use App\Models\Language;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExperienceAlbumApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $paginator = ExperienceAlbum::query()->with(['translations', 'cover', 'country.translations'])
            ->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $media = app(MediaService::class);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (ExperienceAlbum $a) use ($locale, $media) {
                $t = $a->translation($locale);

                return [
                    'id' => $a->id,
                    'title' => $t?->title,
                    'customer_name' => $a->customer_name,
                    'status' => $a->status,
                    'sort' => $a->sort,
                    'photo_count' => $a->photo_count,
                    'cover' => $media->adminMediaPayload($a->cover, 'thumb'),
                    'updated_at' => $a->updated_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'countries' => Country::query()->with('translations')->orderBy('sort')->get()->map(
                fn (Country $c) => ['id' => $c->id, 'name' => $c->translation($locale)?->name ?? $c->code]
            )->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $a = ExperienceAlbum::query()->with(['translations', 'cover', 'country'])->findOrFail($id);
        $t = $a->translation($locale);

        return ApiResponse::success([
            'id' => $a->id,
            'country_id' => $a->country_id,
            'customer_name' => $a->customer_name,
            'trip_date' => $a->trip_date?->format('Y-m-d'),
            'sort' => $a->sort,
            'status' => $a->status,
            'title' => $t?->title,
            'description' => $t?->description,
            'translated_locales' => $this->translatedLocaleCodes($a, 'title'),
            'cover' => app(MediaService::class)->adminMediaPayload($a->cover, 'card'),
        ]);
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
        ExperienceAlbum::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa album');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:experience_albums,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'customer_name' => 'nullable|string|max:255',
                'trip_date' => 'nullable|date',
                'sort' => 'nullable|integer|min:0',
                'status' => 'nullable|in:draft,published',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $album = DB::transaction(function () use ($request, $validated, $locale) {
            $album = isset($validated['id'])
                ? ExperienceAlbum::query()->findOrFail($validated['id'])
                : new ExperienceAlbum;
            $album->fill([
                'country_id' => $validated['country_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'trip_date' => $validated['trip_date'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'status' => $validated['status'] ?? 'draft',
            ]);
            $album->save();
            $this->saveModelTranslation(
                $album,
                ExperienceAlbumTranslation::class,
                'experience_album_id',
                $locale,
                ['title' => $validated['title'], 'description' => $validated['description'] ?? null],
                ['title', 'description'],
            );
            app(MediaService::class)->syncDirectMediaId(
                $album,
                'cover_media_id',
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            return $album->fresh(['translations', 'cover']);
        });

        return $this->show($request->merge(['locale' => $locale]), $album->id);
    }
}
