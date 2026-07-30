<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ReferencePerson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ReferencePersonController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(): View
    {
        $persons = ReferencePerson::query()
            ->with(['country.translations', 'photo'])
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.reference-person.list', [
            'persons' => $persons,
            'title' => 'Đại diện nước ngoài',
        ]);
    }

    public function view(Request $request): View
    {
        $id = $request->integer('id');
        $person = $id > 0
            ? ReferencePerson::query()->with(['country', 'photo'])->findOrFail($id)
            : null;

        $uploadMaxKb = $this->effectiveUploadMaxKb();
        $uploadMaxLabel = ini_get('upload_max_filesize') ?: round($uploadMaxKb / 1024, 1).'MB';

        return view('admin.reference-person.view', [
            'person' => $person,
            'countries' => Country::query()->active()->with('translations')->orderBy('sort')->get(),
            'title' => $person ? 'Chỉnh sửa đại diện' : 'Thêm đại diện NN',
            'uploadMaxKb' => $uploadMaxKb,
            'uploadMaxLabel' => $uploadMaxLabel,
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $this->assertUploadedFileOk($request, 'image');
        $maxKb = $this->effectiveUploadMaxKb();

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:reference_persons,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:120',
            'phone' => 'nullable|string|max:50',
            'skype' => 'nullable|string|max:120',
            'country_id' => 'nullable|integer|exists:countries,id',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb,
            'remove_image' => 'nullable|boolean',
        ], [
            'image.image' => 'File tải lên phải là ảnh hợp lệ.',
            'image.max' => 'Ảnh vượt quá '.round($maxKb / 1024, 1).'MB.',
            'name.required' => 'Vui lòng nhập họ tên.',
        ]);

        try {
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
                    'is_active' => $request->boolean('is_active'),
                ]);
                $person->save();

                $this->syncDirectCover($person, 'photo_media_id', $request, config('media.team'));

                return $person->fresh(['photo']);
            });
        } catch (Throwable $e) {
            Log::error('Reference person save failed', ['message' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['image' => 'Không lưu được: '.$e->getMessage()]);
        }

        return redirect()
            ->route('admin.referencePersons.view', ['id' => $person->id])
            ->with('success', 'Đã lưu đại diện.');
    }

    public function delete(Request $request): RedirectResponse
    {
        ReferencePerson::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.referencePersons.list')->with('success', 'Đã xóa đại diện.');
    }
}
