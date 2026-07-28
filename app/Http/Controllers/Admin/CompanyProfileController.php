<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        $profile = CompanyProfile::query()->first() ?? new CompanyProfile;

        return view('admin.company.profile', [
            'profile' => $profile,
            'title' => 'Thông tin công ty & liên hệ',
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'license_number' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:120',
            'contact_phone' => 'nullable|string|max:40',
            'contact_whatsapp' => 'nullable|string|max:40',
            'slogan' => 'nullable|string|max:255',
        ]);

        $profile = CompanyProfile::query()->first() ?? new CompanyProfile;
        $profile->fill($validated);
        $profile->save();

        return redirect()
            ->route('admin.company.profile')
            ->with('success', 'Đã lưu thông tin liên hệ / footer.');
    }
}
