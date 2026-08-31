<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Office;
use App\Models\OfficeTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfficeController extends Controller
{
    use ManagesTranslations;

    public function list(): View
    {
        $offices = Office::query()
            ->with(['translations', 'country.translations'])
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.office.list', [
            'offices' => $offices,
            'title' => 'Văn phòng',
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $id = $request->integer('id');
        $office = $id > 0
            ? Office::query()->with(['translations', 'country'])->findOrFail($id)
            : null;

        return view('admin.office.view', [
            'office' => $office,
            'locale' => $locale,
            'languages' => $this->activeLanguages(),
            'translation' => $office?->translation($locale),
            'countries' => Country::query()->active()->with('translations')->orderBy('sort')->get(),
            'title' => $office ? 'Chỉnh sửa văn phòng' : 'Thêm văn phòng',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

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
                'is_active' => $request->boolean('is_active'),
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

            return $office;
        });

        return redirect()
            ->route('admin.offices.view', ['id' => $office->id, 'language' => $locale])
            ->with('success', 'Đã lưu văn phòng.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $row = Office::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return redirect()->route('admin.offices.list')->with('success', 'Đã xóa văn phòng.');
    }
}
