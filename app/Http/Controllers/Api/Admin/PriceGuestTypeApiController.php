<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\PriceGuestType;
use App\Support\ApiResponse;
use App\Support\ProjectContext;
use App\Support\ProjectUnique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PriceGuestTypeApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        $items = PriceGuestType::query()
            ->with('translations')
            ->withCount('rates')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (PriceGuestType $type) => $this->serialize($type, $locale));

        return ApiResponse::success([
            'items' => $items,
            'units' => config('pricing.units', []),
            'period_kinds' => config('pricing.period_kinds', []),
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
        $type = PriceGuestType::query()->findOrFail($id);
        if ($type->rates()->exists()) {
            return ApiResponse::error(
                'Không xóa được: đối tượng khách đang được dùng trong bảng giá.',
                'IN_USE',
                422,
            );
        }

        $type->translations()->delete();
        $type->delete();

        return ApiResponse::success(null, 'Đã xóa đối tượng khách');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        try {
            $id = $request->integer('id') ?: null;
            $unique = ProjectUnique::rule('price_guest_types', 'code');
            if ($id) {
                $unique = $unique->ignore($id);
            }

            $validated = $request->validate([
                'id' => 'nullable|integer|exists:price_guest_types,id',
                'code' => ['nullable', 'string', 'max:64', $unique],
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'age_min' => 'nullable|integer|min:0|max:120',
                'age_max' => 'nullable|integer|min:0|max:120',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $type = DB::transaction(function () use ($request, $validated, $locale) {
            $type = isset($validated['id'])
                ? PriceGuestType::query()->findOrFail($validated['id'])
                : new PriceGuestType;

            $code = trim((string) ($validated['code'] ?? ''));
            if ($code === '') {
                $code = Str::slug($validated['name']) ?: 'guest-'.uniqid();
            }

            $type->fill([
                'project_id' => $type->project_id ?: ProjectContext::id(),
                'code' => $code,
                'age_min' => $validated['age_min'] ?? null,
                'age_max' => $validated['age_max'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $type->save();

            $this->saveModelTranslation(
                $type,
                \App\Models\PriceGuestTypeTranslation::class,
                'price_guest_type_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                ],
                ['name', 'description'],
            );

            return $type->fresh('translations');
        });

        return ApiResponse::success(
            $this->serialize($type, $locale),
            isset($validated['id']) ? 'Đã cập nhật đối tượng khách' : 'Đã tạo đối tượng khách',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(PriceGuestType $type, string $locale): array
    {
        $t = $type->translation($locale);

        return [
            'id' => $type->id,
            'code' => $type->code,
            'name' => $t?->name ?? $type->code,
            'description' => $t?->description,
            'age_min' => $type->age_min,
            'age_max' => $type->age_max,
            'sort' => $type->sort,
            'is_active' => $type->is_active,
            'rates_count' => $type->rates_count ?? $type->rates()->count(),
        ];
    }
}
