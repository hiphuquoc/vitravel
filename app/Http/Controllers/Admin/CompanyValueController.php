<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\CompanyValue;
use App\Models\CompanyValueTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyValueController extends Controller
{
    use ManagesTranslations;

    public function list(): View
    {
        $values = CompanyValue::query()
            ->with('translations')
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.company-value.list', [
            'values' => $values,
            'title' => 'Giá trị cốt lõi',
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $id = $request->integer('id');
        $value = $id > 0
            ? CompanyValue::query()->with('translations')->findOrFail($id)
            : null;

        return view('admin.company-value.view', [
            'value' => $value,
            'locale' => $locale,
            'languages' => $this->activeLanguages(),
            'translation' => $value?->translation($locale),
            'title' => $value ? 'Chỉnh sửa giá trị' : 'Thêm giá trị cốt lõi',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:company_values,id',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
        ]);

        $value = DB::transaction(function () use ($request, $validated, $locale) {
            $value = isset($validated['id'])
                ? CompanyValue::query()->findOrFail($validated['id'])
                : new CompanyValue;

            $value->fill([
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active'),
            ]);
            $value->save();

            $this->saveModelTranslation(
                $value,
                CompanyValueTranslation::class,
                'company_value_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                ],
                ['name', 'description'],
            );

            return $value;
        });

        return redirect()
            ->route('admin.values.view', ['id' => $value->id, 'language' => $locale])
            ->with('success', 'Đã lưu giá trị cốt lõi.');
    }

    public function delete(Request $request): RedirectResponse
    {
        CompanyValue::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.values.list')->with('success', 'Đã xóa giá trị.');
    }
}
