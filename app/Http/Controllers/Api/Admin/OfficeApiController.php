<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Language;
use App\Models\Office;
use App\Models\OfficeTranslation;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfficeApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $paginator = Office::query()->with(['translations', 'country.translations'])
            ->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (Office $o) use ($locale) {
                $t = $o->translation($locale);

                return [
                    'id' => $o->id,
                    'city_label' => $t?->city_label,
                    'address_line' => $t?->address_line,
                    'phone' => $o->phone,
                    'email' => $o->email,
                    'sort' => $o->sort,
                    'is_active' => $o->is_active,
                    'country' => $o->country ? [
                        'id' => $o->country->id,
                        'name' => $o->country->translation($locale)?->name,
                    ] : null,
                    'updated_at' => $o->updated_at?->toIso8601String(),
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
        $o = Office::query()->with(['translations', 'country'])->findOrFail($id);
        $t = $o->translation($locale);

        return ApiResponse::success([
            'id' => $o->id,
            'country_id' => $o->country_id,
            'phone' => $o->phone,
            'whatsapp' => $o->whatsapp,
            'email' => $o->email,
            'map_embed_url' => $o->map_embed_url,
            'sort' => $o->sort,
            'is_active' => $o->is_active,
            'city_label' => $t?->city_label,
            'address_line' => $t?->address_line,
            'translated_locales' => $this->translatedLocaleCodes($o, 'city_label'),
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
        $row = Office::query()->findOrFail($id);
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return ApiResponse::success(null, 'Đã xóa văn phòng');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:offices,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'phone' => 'nullable|string|max:40',
                'whatsapp' => 'nullable|string|max:40',
                'email' => 'nullable|email|max:120',
                'map_embed_url' => 'nullable|string|max:1000',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'city_label' => 'required|string|max:120',
                'address_line' => 'required|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $office = DB::transaction(function () use ($request, $validated, $locale) {
            $office = isset($validated['id'])
                ? Office::query()->findOrFail($validated['id'])
                : new Office;
            $office->fill([
                'country_id' => $validated['country_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'email' => $validated['email'] ?? null,
                'map_embed_url' => $validated['map_embed_url'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $office->save();
            $this->saveModelTranslation(
                $office,
                OfficeTranslation::class,
                'office_id',
                $locale,
                [
                    'city_label' => $validated['city_label'],
                    'address_line' => $validated['address_line'],
                ],
                ['city_label', 'address_line'],
            );

            return $office->fresh('translations');
        });

        return $this->show($request->merge(['locale' => $locale]), $office->id);
    }
}
