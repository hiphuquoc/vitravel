<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\CompanyValue;
use App\Models\CompanyValueTranslation;
use App\Models\Language;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyValueApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);
        $paginator = CompanyValue::query()->with('translations')->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (CompanyValue $v) use ($locale) {
                $t = $v->translation($locale);

                return [
                    'id' => $v->id,
                    'name' => $t?->name,
                    'description' => $t?->description,
                    'sort' => $v->sort,
                    'is_active' => $v->is_active,
                    'updated_at' => $v->updated_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $v = CompanyValue::query()->with('translations')->findOrFail($id);
        $t = $v->translation($locale);

        return ApiResponse::success([
            'id' => $v->id,
            'name' => $t?->name,
            'description' => $t?->description,
            'sort' => $v->sort,
            'is_active' => $v->is_active,
            'translated_locales' => $this->translatedLocaleCodes($v, 'name'),
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
        CompanyValue::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa giá trị');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:company_values,id',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'name' => 'required|string|max:120',
                'description' => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $value = DB::transaction(function () use ($request, $validated, $locale) {
            $value = isset($validated['id'])
                ? CompanyValue::query()->findOrFail($validated['id'])
                : new CompanyValue;
            $value->fill([
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $value->save();
            $this->saveModelTranslation(
                $value,
                CompanyValueTranslation::class,
                'company_value_id',
                $locale,
                ['name' => $validated['name'], 'description' => $validated['description'] ?? null],
                ['name', 'description'],
            );

            return $value->fresh('translations');
        });

        return ApiResponse::success(
            [
                'id' => $value->id,
                'name' => $value->translation($locale)?->name,
                'description' => $value->translation($locale)?->description,
                'sort' => $value->sort,
                'is_active' => $value->is_active,
                'translated_locales' => $this->translatedLocaleCodes($value, 'name'),
            ],
            isset($validated['id']) ? 'Đã cập nhật' : 'Đã tạo',
            isset($validated['id']) ? 200 : 201,
        );
    }
}
