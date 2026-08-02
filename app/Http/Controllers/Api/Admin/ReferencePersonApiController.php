<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ReferencePerson;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReferencePersonApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $paginator = ReferencePerson::query()->with(['country.translations', 'photo'])
            ->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $media = app(MediaService::class);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (ReferencePerson $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'phone' => $p->phone,
                'sort' => $p->sort,
                'is_active' => $p->is_active,
                'country' => $p->country ? [
                    'id' => $p->country->id,
                    'name' => $p->country->translation($locale)?->name,
                ] : null,
                'photo' => $media->adminMediaPayload($p->photo, 'thumb'),
                'updated_at' => $p->updated_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'countries' => Country::query()->with('translations')->orderBy('sort')->get()->map(
                fn (Country $c) => ['id' => $c->id, 'name' => $c->translation($locale)?->name ?? $c->code]
            )->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $p = ReferencePerson::query()->with(['country', 'photo'])->findOrFail($id);

        return ApiResponse::success([
            'id' => $p->id,
            'name' => $p->name,
            'email' => $p->email,
            'phone' => $p->phone,
            'skype' => $p->skype,
            'country_id' => $p->country_id,
            'sort' => $p->sort,
            'is_active' => $p->is_active,
            'photo' => app(MediaService::class)->adminMediaPayload($p->photo, 'card'),
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
        ReferencePerson::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa đại diện');
    }

    private function save(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:reference_persons,id',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:120',
                'phone' => 'nullable|string|max:50',
                'skype' => 'nullable|string|max:120',
                'country_id' => 'nullable|integer|exists:countries,id',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'photo_media_id' => 'nullable|integer|exists:media,id',
                'remove_photo' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $person = DB::transaction(function () use ($request, $validated) {
            $person = isset($validated['id'])
                ? ReferencePerson::query()->findOrFail($validated['id'])
                : new ReferencePerson;
            $person->fill([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'skype' => $validated['skype'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $person->save();
            app(MediaService::class)->syncDirectMediaId(
                $person,
                'photo_media_id',
                isset($validated['photo_media_id']) ? (int) $validated['photo_media_id'] : null,
                $request->boolean('remove_photo'),
            );

            return $person->fresh('photo');
        });

        return ApiResponse::success(
            [
                'id' => $person->id,
                'name' => $person->name,
                'email' => $person->email,
                'phone' => $person->phone,
                'skype' => $person->skype,
                'country_id' => $person->country_id,
                'sort' => $person->sort,
                'is_active' => $person->is_active,
                'photo' => app(MediaService::class)->adminMediaPayload($person->photo, 'card'),
            ],
            isset($validated['id']) ? 'Đã cập nhật' : 'Đã tạo',
            isset($validated['id']) ? 200 : 201,
        );
    }
}
