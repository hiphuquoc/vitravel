<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\TeamMember;
use App\Models\TeamMemberTranslation;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeamMemberApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $paginator = TeamMember::query()->with(['translations', 'avatar', 'seoEntry.translations'])
            ->orderBy('sort')->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $media = app(MediaService::class);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (TeamMember $m) use ($locale, $media) {
                $t = $m->translation($locale);
                $seo = $m->seoEntry?->translation($locale);

                return [
                    'id' => $m->id,
                    'name' => $t?->name,
                    'role' => $t?->role,
                    'department' => $m->department,
                    'sort' => $m->sort,
                    'is_active' => $m->is_active,
                    'show_on_home' => $m->show_on_home,
                    'avatar' => $media->adminMediaPayload($m->avatar, 'thumb'),
                    'seo' => ['slug_full' => $seo?->slug_full],
                    'updated_at' => $m->updated_at?->toIso8601String(),
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
        $hubSeo = $this->seoService()->ensureHub('team_hub', $locale);

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'hub_seo_id' => $hubSeo->id,
            'seo_parents' => $this->mapSeoParents(
                $this->seoService()->parentOptions('team_hub'),
                $locale,
            ),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $m = TeamMember::query()->with(['translations', 'avatar', 'seoEntry.translations'])->findOrFail($id);
        $t = $m->translation($locale);
        $seo = $m->seoEntry?->translation($locale);
        $langs = is_array($m->languages) ? implode("\n", $m->languages) : (string) $m->languages;

        return ApiResponse::success([
            'id' => $m->id,
            'department' => $m->department,
            'sort' => $m->sort,
            'is_active' => $m->is_active,
            'show_on_home' => $m->show_on_home,
            'is_verified' => $m->is_verified,
            'phone' => $m->phone,
            'email' => $m->email,
            'area' => $m->area,
            'years_experience' => $m->years_experience,
            'languages' => $langs,
            'stat_clients' => $m->stat_clients,
            'stat_tours' => $m->stat_tours,
            'stat_awards' => $m->stat_awards,
            'name' => $t?->name,
            'role' => $t?->role,
            'short_bio' => $t?->short_bio,
            'bio_html' => $t?->bio_html,
            'translated_locales' => $this->translatedLocaleCodes($m, 'name'),
            'avatar' => app(MediaService::class)->adminMediaPayload($m->avatar, 'card'),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'keywords' => $seo?->keywords,
                'parent_id' => $m->seoEntry?->parent_id,
                'rating_aggregate_star' => $m->seoEntry?->rating_aggregate_star,
                'rating_aggregate_count' => $m->seoEntry?->rating_aggregate_count,
            ],
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
        TeamMember::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa thành viên');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:team_members,id',
                'department' => 'nullable|string|max:100',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'show_on_home' => 'nullable|boolean',
                'is_verified' => 'nullable|boolean',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'area' => 'nullable|string|max:255',
                'years_experience' => 'nullable|integer|min:0|max:80',
                'languages' => 'nullable|string',
                'stat_clients' => 'nullable|integer|min:0',
                'stat_tours' => 'nullable|integer|min:0',
                'stat_awards' => 'nullable|integer|min:0',
                'name' => 'required|string|max:255',
                'role' => 'nullable|string|max:255',
                'short_bio' => 'nullable|string',
                'bio_html' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:255',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:500',
                'seo_keywords' => 'nullable|string|max:500',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'avatar_media_id' => 'nullable|integer|exists:media,id',
                'remove_avatar' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $member = DB::transaction(function () use ($request, $validated, $locale) {
            $member = isset($validated['id'])
                ? TeamMember::query()->findOrFail($validated['id'])
                : new TeamMember;
            $languages = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['languages'] ?? '')))
                ->map(fn ($l) => trim($l))->filter()->values()->all();

            $member->fill([
                'department' => $validated['department'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'show_on_home' => $request->boolean('show_on_home'),
                'is_verified' => $request->boolean('is_verified'),
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'area' => $validated['area'] ?? null,
                'years_experience' => $validated['years_experience'] ?? null,
                'languages' => $languages,
                'stat_clients' => $validated['stat_clients'] ?? 0,
                'stat_tours' => $validated['stat_tours'] ?? 0,
                'stat_awards' => $validated['stat_awards'] ?? 0,
            ]);
            $member->save();

            $this->saveModelTranslation(
                $member,
                TeamMemberTranslation::class,
                'team_member_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'role' => $validated['role'] ?? null,
                    'short_bio' => $validated['short_bio'] ?? null,
                    'bio_html' => $validated['bio_html'] ?? null,
                ],
                ['name', 'role', 'short_bio', 'bio_html'],
            );

            app(MediaService::class)->syncDirectMediaId(
                $member,
                'avatar_media_id',
                isset($validated['avatar_media_id']) ? (int) $validated['avatar_media_id'] : null,
                $request->boolean('remove_avatar'),
            );

            $hubSeo = $this->seoService()->ensureHub('team_hub', $locale);
            $parentId = $validated['seo_parent_id'] ?? $hubSeo->id;
            $slug = filled($validated['seo_slug'] ?? null)
                ? Str::slug((string) $validated['seo_slug'])
                : Str::slug($validated['name']);

            $this->saveSeoTranslations(
                $member,
                [
                    $locale => [
                        'slug' => $slug,
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $member->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                    ],
                ],
                'team_member',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            return $member->fresh(['translations', 'avatar', 'seoEntry.translations']);
        });

        return $this->show($request->merge(['locale' => $locale]), $member->id);
    }
}
