<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileTranslation;
use App\Models\Language;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyProfileApiController extends Controller
{
    use ManagesTranslations;

    public function show(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $profile = CompanyProfile::query()->with([
            'translations', 'missionImage', 'visionImage', 'policyImage', 'reasonsImage', 'aboutBanner',
        ])->first() ?? new CompanyProfile;
        $t = $profile->exists ? $profile->translation($locale) : null;
        $media = app(MediaService::class);

        return ApiResponse::success([
            'id' => $profile->id,
            'exists' => $profile->exists,
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'translated_locales' => $profile->exists ? $this->translatedLocaleCodes($profile, 'about_page_title') : [],
            'license_number' => $profile->license_number,
            'contact_email' => $profile->contact_email,
            'contact_phone' => $profile->contact_phone,
            'contact_whatsapp' => $profile->contact_whatsapp,
            'slogan' => $profile->slogan,
            'about_seo_title' => $t?->about_seo_title,
            'about_seo_description' => $t?->about_seo_description,
            'about_page_title' => $t?->about_page_title,
            'about_page_subtitle' => $t?->about_page_subtitle,
            'mission_title' => $t?->mission_title,
            'mission_text' => $t?->mission_text,
            'vision_title' => $t?->vision_title,
            'vision_text' => $t?->vision_text,
            'sales_policy_title' => $t?->sales_policy_title,
            'sales_policy_content' => $t?->sales_policy_content,
            'sales_policy_cta_label' => $t?->sales_policy_cta_label,
            'sales_policy_cta_url' => $t?->sales_policy_cta_url,
            'values_section_title' => $t?->values_section_title,
            'values_hub_label' => $t?->values_hub_label,
            'reasons_section_title' => $t?->reasons_section_title,
            'reasons_cta_label' => $t?->reasons_cta_label,
            'reasons_cta_url' => $t?->reasons_cta_url,
            'reference_section_title' => $t?->reference_section_title,
            'reference_section_subtitle' => $t?->reference_section_subtitle,
            'about_banner' => $media->adminMediaPayload($profile->aboutBanner, 'lg'),
            'mission_image' => $media->adminMediaPayload($profile->missionImage, 'card'),
            'vision_image' => $media->adminMediaPayload($profile->visionImage, 'card'),
            'policy_image' => $media->adminMediaPayload($profile->policyImage, 'card'),
            'reasons_image' => $media->adminMediaPayload($profile->reasonsImage, 'card'),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
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
                'about_banner_media_id' => 'nullable|integer|exists:media,id',
                'remove_about_banner' => 'nullable|boolean',
                'mission_image_id' => 'nullable|integer|exists:media,id',
                'remove_mission_image' => 'nullable|boolean',
                'vision_image_id' => 'nullable|integer|exists:media,id',
                'remove_vision_image' => 'nullable|boolean',
                'policy_image_id' => 'nullable|integer|exists:media,id',
                'remove_policy_image' => 'nullable|boolean',
                'reasons_image_id' => 'nullable|integer|exists:media,id',
                'remove_reasons_image' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

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

            $media = app(MediaService::class);
            $media->syncDirectMediaId($profile, 'about_banner_media_id', isset($validated['about_banner_media_id']) ? (int) $validated['about_banner_media_id'] : null, $request->boolean('remove_about_banner'));
            $media->syncDirectMediaId($profile, 'mission_image_id', isset($validated['mission_image_id']) ? (int) $validated['mission_image_id'] : null, $request->boolean('remove_mission_image'));
            $media->syncDirectMediaId($profile, 'vision_image_id', isset($validated['vision_image_id']) ? (int) $validated['vision_image_id'] : null, $request->boolean('remove_vision_image'));
            $media->syncDirectMediaId($profile, 'policy_image_id', isset($validated['policy_image_id']) ? (int) $validated['policy_image_id'] : null, $request->boolean('remove_policy_image'));
            $media->syncDirectMediaId($profile, 'reasons_image_id', isset($validated['reasons_image_id']) ? (int) $validated['reasons_image_id'] : null, $request->boolean('remove_reasons_image'));
        });

        return $this->show($request->merge(['locale' => $locale]));
    }
}
