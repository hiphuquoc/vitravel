<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\TeamMember;
use App\Models\TeamMemberAchievement;
use App\Models\TeamMemberActivityImage;
use App\Models\TeamMemberDegree;
use App\Models\TeamMemberExperience;
use App\Models\TeamMemberSkill;
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
        $m = TeamMember::query()->with([
            'translations',
            'avatar',
            'seoEntry.translations',
            'achievements',
            'skills',
            'experiences.items',
            'degrees.items',
            'activityImages.media',
        ])->findOrFail($id);
        $t = $m->translation($locale);
        $seo = $m->seoEntry?->translation($locale);
        $media = app(MediaService::class);
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
            'avatar' => $media->adminMediaPayload($m->avatar, 'card'),
            'achievements' => $m->achievements->map(fn (TeamMemberAchievement $a) => [
                'content' => $a->content,
            ])->values()->all(),
            'skills' => $m->skills->map(fn (TeamMemberSkill $s) => [
                'skill' => $s->skill,
                'percent' => (int) $s->percent,
            ])->values()->all(),
            'experiences' => $m->experiences->map(fn (TeamMemberExperience $e) => [
                'title' => $e->title,
                'company' => $e->company,
                'items' => $e->items->pluck('content')->implode("\n"),
            ])->values()->all(),
            'degrees' => $m->degrees->map(fn (TeamMemberDegree $d) => [
                'title' => $d->title,
                'school' => $d->school,
                'items' => $d->items->pluck('content')->implode("\n"),
            ])->values()->all(),
            'activity_images' => $m->activityImages->map(fn (TeamMemberActivityImage $img) => [
                'id' => $img->id,
                'media' => $media->adminMediaPayload($img->media, 'card'),
            ])->values()->all(),
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
                'achievements' => 'nullable|array',
                'achievements.*.content' => 'nullable|string',
                'skills' => 'nullable|array',
                'skills.*.skill' => 'nullable|string|max:255',
                'skills.*.percent' => 'nullable|integer|min:0|max:100',
                'experiences' => 'nullable|array',
                'experiences.*.title' => 'nullable|string|max:255',
                'experiences.*.company' => 'nullable|string|max:255',
                'experiences.*.items' => 'nullable|string',
                'degrees' => 'nullable|array',
                'degrees.*.title' => 'nullable|string|max:255',
                'degrees.*.school' => 'nullable|string|max:255',
                'degrees.*.items' => 'nullable|string',
                'activity_media_ids' => 'nullable|array',
                'activity_media_ids.*' => 'integer|exists:media,id',
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
            $parentId = $request->has('seo_parent_id') ? ((int) $request->input('seo_parent_id') ?: null) : $hubSeo->id;
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

            $this->syncAchievements($member, $validated['achievements'] ?? []);
            $this->syncSkills($member, $validated['skills'] ?? []);
            $member->load(['experiences.items', 'degrees.items']);
            $this->syncExperiences($member, $validated['experiences'] ?? []);
            $this->syncDegrees($member, $validated['degrees'] ?? []);
            $this->syncActivityImages($member, $validated['activity_media_ids'] ?? []);

            return $member->fresh([
                'translations',
                'avatar',
                'seoEntry.translations',
                'achievements',
                'skills',
                'experiences.items',
                'degrees.items',
                'activityImages.media',
            ]);
        });

        return $this->show($request->merge(['locale' => $locale]), $member->id);
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function syncAchievements(TeamMember $member, array $rows): void
    {
        $member->achievements()->delete();
        $order = 0;
        foreach ($rows as $row) {
            $content = trim((string) ($row['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            TeamMemberAchievement::query()->create([
                'team_member_id' => $member->id,
                'content' => $content,
                'ordering' => $order++,
            ]);
        }
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function syncSkills(TeamMember $member, array $rows): void
    {
        $member->skills()->delete();
        $order = 0;
        foreach ($rows as $row) {
            $skill = trim((string) ($row['skill'] ?? ''));
            if ($skill === '') {
                continue;
            }
            TeamMemberSkill::query()->create([
                'team_member_id' => $member->id,
                'skill' => $skill,
                'percent' => max(0, min(100, (int) ($row['percent'] ?? 0))),
                'ordering' => $order++,
            ]);
        }
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function syncExperiences(TeamMember $member, array $rows): void
    {
        $member->experiences->each(function (TeamMemberExperience $exp) {
            $exp->items()->delete();
            $exp->delete();
        });

        $order = 0;
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $exp = TeamMemberExperience::query()->create([
                'team_member_id' => $member->id,
                'title' => $title,
                'company' => filled($row['company'] ?? null) ? trim((string) $row['company']) : null,
                'ordering' => $order++,
            ]);
            foreach ($this->linesToArray($row['items'] ?? null) as $line) {
                $exp->items()->create(['content' => $line]);
            }
        }
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function syncDegrees(TeamMember $member, array $rows): void
    {
        $member->degrees->each(function (TeamMemberDegree $degree) {
            $degree->items()->delete();
            $degree->delete();
        });

        $order = 0;
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $degree = TeamMemberDegree::query()->create([
                'team_member_id' => $member->id,
                'title' => $title,
                'school' => filled($row['school'] ?? null) ? trim((string) $row['school']) : null,
                'ordering' => $order++,
            ]);
            foreach ($this->linesToArray($row['items'] ?? null) as $line) {
                $degree->items()->create(['content' => $line]);
            }
        }
    }

    /** @param  array<int, mixed>  $mediaIds */
    private function syncActivityImages(TeamMember $member, array $mediaIds): void
    {
        $member->activityImages()->delete();
        $order = 0;
        foreach ($mediaIds as $mediaId) {
            $id = (int) $mediaId;
            if ($id <= 0) {
                continue;
            }
            TeamMemberActivityImage::query()->create([
                'team_member_id' => $member->id,
                'media_id' => $id,
                'ordering' => $order++,
            ]);
        }
    }

    /** @return list<string> */
    private function linesToArray(mixed $raw): array
    {
        if (is_array($raw)) {
            return collect($raw)->map(fn ($l) => trim((string) $l))->filter()->values()->all();
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();
    }
}
