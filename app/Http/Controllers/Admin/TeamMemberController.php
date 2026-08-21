<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\TeamMemberAchievement;
use App\Models\TeamMemberDegree;
use App\Models\TeamMemberExperience;
use App\Models\TeamMemberSkill;
use App\Models\TeamMemberTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class TeamMemberController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(): View
    {
        $members = TeamMember::query()
            ->with(['translations', 'avatar', 'seoEntry.translations'])
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate(20);

        $title = 'Đội ngũ';

        return view('admin.team-member.list', compact('members', 'title'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $id = $request->integer('id');

        $member = $id > 0
            ? TeamMember::query()
                ->with([
                    'translations',
                    'avatar',
                    'seoEntry.translations',
                    'achievements',
                    'skills',
                    'experiences.items',
                    'degrees.items',
                ])
                ->findOrFail($id)
            : null;

        $hubSeo = $this->seoService()->ensureHub('team_hub', $locale);
        $parents = $this->seoService()->parentOptions('team_hub');
        $defaultParentId = $member ? $member->seoEntry?->parent_id : $hubSeo->id;
        $seoTranslation = $member?->seoEntry?->translation($locale);

        $languages = $this->activeLanguages();
        $translation = $member?->translation($locale);
        $title = $member ? 'Chỉnh sửa thành viên' : 'Thêm thành viên mới';
        $uploadMaxKb = $this->effectiveUploadMaxKb();
        $uploadMaxLabel = ini_get('upload_max_filesize') ?: round($uploadMaxKb / 1024, 1).'MB';

        return view('admin.team-member.view', compact(
            'member', 'locale', 'languages', 'translation', 'title',
            'uploadMaxKb', 'uploadMaxLabel', 'parents', 'defaultParentId', 'seoTranslation', 'hubSeo',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $this->assertUploadedFileOk($request, 'image');

        $maxKb = $this->effectiveUploadMaxKb();

        $validated = $request->validate([
            'id' => 'nullable|integer|min:1|exists:team_members,id',
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
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb,
            'remove_image' => 'nullable|boolean',
            'seo_slug' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
            'rating_aggregate_count' => 'nullable|integer|min:0',
            'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
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
        ], [
            'image.image' => 'File tải lên phải là ảnh hợp lệ (JPG, PNG, WebP, GIF).',
            'image.mimes' => 'Chỉ chấp nhận JPG, PNG, WebP hoặc GIF.',
            'image.max' => 'Ảnh vượt quá '.round($maxKb / 1024, 1).'MB (giới hạn máy chủ). Vui lòng chọn ảnh nhỏ hơn.',
            'name.required' => 'Vui lòng nhập họ tên thành viên.',
        ]);

        try {
            $member = DB::transaction(function () use ($request, $validated, $locale) {
                $member = isset($validated['id'])
                    ? TeamMember::query()->findOrFail($validated['id'])
                    : new TeamMember;

                $languages = $this->linesToArray($validated['languages'] ?? null);

                $member->fill([
                    'department' => $validated['department'] ?? null,
                    'sort' => $validated['sort'] ?? 0,
                    'is_active' => $request->boolean('is_active'),
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

                $this->syncDirectCover($member, 'avatar_media_id', $request, config('media.team'));

                $hubSeo = $this->seoService()->ensureHub('team_hub', $locale);
                $parentId = $request->has('seo_parent_id') ? ((int) $request->input('seo_parent_id') ?: null) : $hubSeo->id;
                $slug = filled($validated['seo_slug'] ?? null)
                    ? Str::slug((string) $validated['seo_slug'])
                    : Str::slug((string) $validated['name']);

                $this->saveSeoTranslations(
                    $member,
                    [
                        $locale => [
                            'slug' => $slug,
                            'title' => $validated['name'],
                            'seo_title' => $validated['seo_title'] ?? $validated['name'],
                            'seo_description' => $validated['seo_description']
                                ?? Str::limit(strip_tags((string) ($validated['short_bio'] ?? '')), 160),
                            'keywords' => $validated['seo_keywords'] ?? null,
                            'status' => 'published',
                            'parent_id' => $parentId,
                        ],
                    ],
                    'team_member',
                    [
                        'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                        'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                    ],
                );

                $this->syncAchievements($member, $request->input('achievements', []));
                $this->syncSkills($member, $request->input('skills', []));
                $this->syncExperiences($member, $request->input('experiences', []));
                $this->syncDegrees($member, $request->input('degrees', []));

                return $member->fresh(['avatar', 'translations', 'seoEntry.translations']);
            });
        } catch (Throwable $e) {
            Log::error('Team member save/upload failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'image' => 'Không lưu được ảnh/thành viên: '.$e->getMessage(),
                ]);
        }

        return redirect()
            ->route('admin.team.view', ['id' => $member->id, 'language' => $locale])
            ->with('success', 'Đã lưu thành viên thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        TeamMember::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.team.list')->with('success', 'Đã xóa thành viên thành công.');
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
            foreach ($this->linesToArray($row['items'] ?? null) ?? [] as $line) {
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
            foreach ($this->linesToArray($row['items'] ?? null) ?? [] as $line) {
                $degree->items()->create(['content' => $line]);
            }
        }
    }
}
