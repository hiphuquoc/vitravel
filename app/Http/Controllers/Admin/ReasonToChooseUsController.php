<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\ReasonToChooseUs;
use App\Models\ReasonToChooseUsTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReasonToChooseUsController extends Controller
{
    use ManagesTranslations;

    public function list(): View
    {
        $reasons = ReasonToChooseUs::query()
            ->with('translations')
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.reason.list', [
            'reasons' => $reasons,
            'title' => 'Lý do chọn ViTravel',
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $id = $request->integer('id');
        $reason = $id > 0
            ? ReasonToChooseUs::query()->with('translations')->findOrFail($id)
            : null;

        return view('admin.reason.view', [
            'reason' => $reason,
            'locale' => $locale,
            'languages' => $this->activeLanguages(),
            'translation' => $reason?->translation($locale),
            'title' => $reason ? 'Chỉnh sửa lý do' : 'Thêm lý do chọn',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:reasons_to_choose_us,id',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $reason = DB::transaction(function () use ($request, $validated, $locale) {
            $reason = isset($validated['id'])
                ? ReasonToChooseUs::query()->findOrFail($validated['id'])
                : new ReasonToChooseUs;

            $reason->fill([
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active'),
            ]);
            $reason->save();

            $this->saveModelTranslation(
                $reason,
                ReasonToChooseUsTranslation::class,
                'reason_to_choose_us_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                ],
                ['title', 'description'],
            );

            return $reason;
        });

        return redirect()
            ->route('admin.reasons.view', ['id' => $reason->id, 'language' => $locale])
            ->with('success', 'Đã lưu lý do chọn.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $row = ReasonToChooseUs::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return redirect()->route('admin.reasons.list')->with('success', 'Đã xóa lý do.');
    }
}
