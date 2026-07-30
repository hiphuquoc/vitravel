<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CompanyProfileController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function edit(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $profile = CompanyProfile::query()
            ->with([
                'translations',
                'missionImage',
                'visionImage',
                'policyImage',
                'reasonsImage',
                'aboutBanner',
            ])
            ->first() ?? new CompanyProfile;

        $uploadMaxKb = $this->effectiveUploadMaxKb();
        $uploadMaxLabel = ini_get('upload_max_filesize') ?: round($uploadMaxKb / 1024, 1).'MB';

        return view('admin.company.profile', [
            'profile' => $profile,
            'locale' => $locale,
            'languages' => $this->activeLanguages(),
            'translation' => $profile->exists ? $profile->translation($locale) : null,
            'title' => 'Công ty — trang Về chúng tôi',
            'uploadMaxKb' => $uploadMaxKb,
            'uploadMaxLabel' => $uploadMaxLabel,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

        foreach (['about_banner', 'mission_image', 'vision_image', 'policy_image', 'reasons_image'] as $field) {
            $this->assertUploadedFileOk($request, $field);
        }

        $maxKb = $this->effectiveUploadMaxKb();
        $imageRule = 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb;

        $validated = $request->validate([
            'license_number' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:120',
            'contact_phone' => 'nullable|string|max:40',
            'contact_whatsapp' => 'nullable|string|max:40',
            'slogan' => 'nullable|string|max:255',
            'about_seo_title' => 'nullable|string|max:255',
            'about_seo_description' => 'nullable|string|max:500',
            'about_page_title' => 'nullable|string|max:255',
            'about_page_subtitle' => 'nullable|string|max:500',
            'mission_title' => 'nullable|string|max:255',
            'mission_text' => 'nullable|string',
            'vision_title' => 'nullable|string|max:255',
            'vision_text' => 'nullable|string',
            'sales_policy_title' => 'nullable|string|max:255',
            'sales_policy_content' => 'nullable|string',
            'sales_policy_cta_label' => 'nullable|string|max:120',
            'sales_policy_cta_url' => 'nullable|string|max:500',
            'values_section_title' => 'nullable|string|max:255',
            'values_hub_label' => 'nullable|string|max:255',
            'reasons_section_title' => 'nullable|string|max:255',
            'reasons_cta_label' => 'nullable|string|max:120',
            'reasons_cta_url' => 'nullable|string|max:500',
            'reference_section_title' => 'nullable|string|max:255',
            'reference_section_subtitle' => 'nullable|string|max:500',
            'about_banner' => $imageRule,
            'remove_about_banner' => 'nullable|boolean',
            'mission_image' => $imageRule,
            'remove_mission_image' => 'nullable|boolean',
            'vision_image' => $imageRule,
            'remove_vision_image' => 'nullable|boolean',
            'policy_image' => $imageRule,
            'remove_policy_image' => 'nullable|boolean',
            'reasons_image' => $imageRule,
            'remove_reasons_image' => 'nullable|boolean',
        ], [
            '*.image' => 'File tải lên phải là ảnh hợp lệ (JPG, PNG, WebP, GIF).',
            '*.mimes' => 'Chỉ chấp nhận JPG, PNG, WebP hoặc GIF.',
            '*.max' => 'Ảnh vượt quá '.round($maxKb / 1024, 1).'MB (giới hạn máy chủ).',
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $locale) {
                $profile = CompanyProfile::query()->first() ?? new CompanyProfile;
                $profile->fill([
                    'license_number' => $validated['license_number'] ?? null,
                    'contact_email' => $validated['contact_email'] ?? null,
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'contact_whatsapp' => $validated['contact_whatsapp'] ?? null,
                    'slogan' => $validated['slogan'] ?? null,
                ]);
                $profile->save();

                $this->saveModelTranslation(
                    $profile,
                    CompanyProfileTranslation::class,
                    'company_profile_id',
                    $locale,
                    [
                        'about_seo_title' => $validated['about_seo_title'] ?? null,
                        'about_seo_description' => $validated['about_seo_description'] ?? null,
                        'about_page_title' => $validated['about_page_title'] ?? null,
                        'about_page_subtitle' => $validated['about_page_subtitle'] ?? null,
                        'mission_title' => $validated['mission_title'] ?? null,
                        'mission_text' => $validated['mission_text'] ?? null,
                        'vision_title' => $validated['vision_title'] ?? null,
                        'vision_text' => $validated['vision_text'] ?? null,
                        'sales_policy_title' => $validated['sales_policy_title'] ?? null,
                        'sales_policy_content' => $validated['sales_policy_content'] ?? null,
                        'sales_policy_cta_label' => $validated['sales_policy_cta_label'] ?? null,
                        'sales_policy_cta_url' => $validated['sales_policy_cta_url'] ?? null,
                        'values_section_title' => $validated['values_section_title'] ?? null,
                        'values_hub_label' => $validated['values_hub_label'] ?? null,
                        'reasons_section_title' => $validated['reasons_section_title'] ?? null,
                        'reasons_cta_label' => $validated['reasons_cta_label'] ?? null,
                        'reasons_cta_url' => $validated['reasons_cta_url'] ?? null,
                        'reference_section_title' => $validated['reference_section_title'] ?? null,
                        'reference_section_subtitle' => $validated['reference_section_subtitle'] ?? null,
                    ],
                    [
                        'about_seo_title', 'about_seo_description',
                        'about_page_title', 'about_page_subtitle',
                        'mission_title', 'mission_text',
                        'vision_title', 'vision_text',
                        'sales_policy_title', 'sales_policy_content',
                        'sales_policy_cta_label', 'sales_policy_cta_url',
                        'values_section_title', 'values_hub_label',
                        'reasons_section_title', 'reasons_cta_label', 'reasons_cta_url',
                        'reference_section_title', 'reference_section_subtitle',
                    ],
                );

                $folder = config('media.company');
                $this->syncDirectCover($profile, 'about_banner_media_id', $request, $folder, 'about_banner', 'remove_about_banner');
                $this->syncDirectCover($profile, 'mission_image_id', $request, $folder, 'mission_image', 'remove_mission_image');
                $this->syncDirectCover($profile, 'vision_image_id', $request, $folder, 'vision_image', 'remove_vision_image');
                $this->syncDirectCover($profile, 'policy_image_id', $request, $folder, 'policy_image', 'remove_policy_image');
                $this->syncDirectCover($profile, 'reasons_image_id', $request, $folder, 'reasons_image', 'remove_reasons_image');
            });
        } catch (Throwable $e) {
            Log::error('Company profile save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['about_banner' => 'Không lưu được: '.$e->getMessage()]);
        }

        return redirect()
            ->route('admin.company.profile', ['language' => $locale])
            ->with('success', 'Đã lưu thông tin công ty / trang Về chúng tôi.');
    }
}
