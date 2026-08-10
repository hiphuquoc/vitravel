<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\TravelStyle;
use App\Models\TravelStyleTranslation;
use App\Support\ApiResponse;
use App\Support\ProjectUnique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TravelStyleApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = TravelStyle::query()->with('translations');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->withCount('packages')->orderBy('sort')->orderByDesc('id')->paginate(
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (TravelStyle $s) => $this->serialize($s, $locale));

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

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $style = TravelStyle::query()->with('translations')->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($style, $locale));
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
        TravelStyle::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa chủ đề tour');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:travel_styles,id',
                'code' => [
                    'required',
                    'string',
                    'max:64',
                    ProjectUnique::rule('travel_styles', 'code')->ignore($request->integer('id') ?: null),
                ],
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:191',
                'description' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $style = DB::transaction(function () use ($request, $validated, $locale) {
            $style = isset($validated['id'])
                ? TravelStyle::query()->findOrFail($validated['id'])
                : new TravelStyle;

            $style->fill([
                'code' => Str::slug($validated['code']),
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $style->save();

            $slug = $validated['slug'] ?? Str::slug($validated['name']);

            $this->saveModelTranslation(
                $style,
                TravelStyleTranslation::class,
                'travel_style_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                ],
                ['name', 'slug', 'description'],
            );

            return $style->fresh(['translations']);
        });

        return ApiResponse::success(
            $this->serializeDetail($style, $locale),
            isset($validated['id']) ? 'Đã cập nhật chủ đề' : 'Đã tạo chủ đề',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(TravelStyle $style, string $locale): array
    {
        $t = $style->translation($locale);

        return [
            'id' => $style->id,
            'code' => $style->code,
            'name' => $t?->name,
            'slug' => $t?->slug,
            'sort' => $style->sort,
            'is_active' => $style->is_active,
            'packages_count' => $style->packages_count ?? $style->packages()->count(),
            'updated_at' => $style->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(TravelStyle $style, string $locale): array
    {
        $t = $style->translation($locale);

        return array_merge($this->serialize($style, $locale), [
            'description' => $t?->description,
            'translated_locales' => $this->translatedLocaleCodes($style, 'name'),
        ]);
    }
}
