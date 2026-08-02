<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ReasonToChooseUs;
use App\Models\ReasonToChooseUsTranslation;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReasonApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $paginator = ReasonToChooseUs::query()->with('translations')->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (ReasonToChooseUs $r) use ($locale) {
                $t = $r->translation($locale);

                return [
                    'id' => $r->id,
                    'title' => $t?->title,
                    'description' => $t?->description,
                    'sort' => $r->sort,
                    'is_active' => $r->is_active,
                    'updated_at' => $r->updated_at?->toIso8601String(),
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
        $r = ReasonToChooseUs::query()->with('translations')->findOrFail($id);
        $t = $r->translation($locale);

        return ApiResponse::success([
            'id' => $r->id,
            'title' => $t?->title,
            'description' => $t?->description,
            'sort' => $r->sort,
            'is_active' => $r->is_active,
            'translated_locales' => $this->translatedLocaleCodes($r, 'title'),
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
        ReasonToChooseUs::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa lý do');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:reasons_to_choose_us,id',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $reason = DB::transaction(function () use ($request, $validated, $locale) {
            $reason = isset($validated['id'])
                ? ReasonToChooseUs::query()->findOrFail($validated['id'])
                : new ReasonToChooseUs;
            $reason->fill([
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $reason->save();
            $this->saveModelTranslation(
                $reason,
                ReasonToChooseUsTranslation::class,
                'reason_to_choose_us_id',
                $locale,
                ['title' => $validated['title'], 'description' => $validated['description'] ?? null],
                ['title', 'description'],
            );

            return $reason->fresh('translations');
        });

        return ApiResponse::success(
            [
                'id' => $reason->id,
                'title' => $reason->translation($locale)?->title,
                'description' => $reason->translation($locale)?->description,
                'sort' => $reason->sort,
                'is_active' => $reason->is_active,
                'translated_locales' => $this->translatedLocaleCodes($reason, 'title'),
            ],
            isset($validated['id']) ? 'Đã cập nhật' : 'Đã tạo',
            isset($validated['id']) ? 200 : 201,
        );
    }
}
