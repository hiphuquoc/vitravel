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
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class TeamMemberController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(): View
    {
        $members = TeamMember::query()
            ->with(['translations', 'avatar'])
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
            ? TeamMember::query()->with(['translations', 'avatar'])->findOrFail($id)
            : null;

        $languages = $this->activeLanguages();
        $translation = $member?->translation($locale);
        $title = $member ? 'Chỉnh sửa thành viên' : 'Thêm thành viên mới';
        $uploadMaxKb = $this->effectiveUploadMaxKb();
        $uploadMaxLabel = ini_get('upload_max_filesize') ?: round($uploadMaxKb / 1024, 1).'MB';

        return view('admin.team-member.view', compact(
            'member', 'locale', 'languages', 'translation', 'title',
            'uploadMaxKb', 'uploadMaxLabel',
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
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'short_bio' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb,
            'remove_image' => 'nullable|boolean',
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

                $member->fill([
                    'department' => $validated['department'] ?? null,
                    'sort' => $validated['sort'] ?? 0,
                    'is_active' => $request->boolean('is_active'),
                    'show_on_home' => $request->boolean('show_on_home'),
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

                return $member->fresh(['avatar', 'translations']);
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
}
