<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\TeamMemberTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(): View
    {
        $members = TeamMember::query()
            ->with('translations')
            ->orderBy('sort')
            ->paginate(20);

        return view('admin.team-member.list', compact('members'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $member = $request->filled('id')
            ? TeamMember::query()->with(['translations', 'avatar'])->findOrFail($request->integer('id'))
            : null;

        $languages = $this->activeLanguages();
        $translation = $member?->translation($locale);
        $title = $member ? 'Chỉnh sửa thành viên' : 'Thêm thành viên mới';

        return view('admin.team-member.view', compact('member', 'locale', 'languages', 'translation', 'title'));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:team_members,id',
            'department' => 'nullable|string|max:100',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'show_on_home' => 'nullable|boolean',
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'short_bio' => 'nullable|string',
            ...$this->coverImageRules(),
        ]);

        $member = DB::transaction(function () use ($request, $validated, $locale) {
            $member = isset($validated['id'])
                ? TeamMember::query()->findOrFail($validated['id'])
                : new TeamMember;

            $member->fill([
                'department' => $validated['department'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'show_on_home' => $request->boolean('show_on_home', false),
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
                ],
                ['name', 'role', 'short_bio'],
            );

            $this->syncDirectCover($member, 'avatar_media_id', $request, config('media.team'));

            return $member;
        });

        return redirect()
            ->route('admin.team.view', ['id' => $member->id, 'language' => $locale])
            ->with('success', 'Đã lưu thành viên thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        TeamMember::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.team.list')->with('success', 'Đã xóa thành viên thành công.');
    }
}
