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

    /** @var list<string> */
    private const SOCIAL_KEYS = ['facebook', 'youtube', 'instagram', 'tiktok'];

    public function show(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $profile = CompanyProfile::query()->with([
            'translations', 'missionImage', 'visionImage', 'policyImage', 'reasonsImage', 'aboutBanner',
        ])->first() ?? new CompanyProfile;
        $t = $profile->exists ? $profile->translation($locale) : null;
        $media = app(MediaService::class);
        $address = is_array($profile->address) ? $profile->address : [];
        $social = is_array($profile->social_links) ? $profile->social_links : [];
        $schema = is_array($profile->schema_settings) ? $profile->schema_settings : [];

        return ApiResponse::success(array_merge([
            'id' => $profile->id,
            'exists' => $profile->exists,
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'translated_locales' => $profile->exists ? $this->translatedLocaleCodes($profile, 'about_page_title') : [],
            // Identity / contact (cấu trúc — khóa ở bản dịch)
            'name' => $profile->name,
            'legal_name' => $profile->legal_name,
            'tagline' => $profile->tagline,
            'license_number' => $profile->license_number,
            'contact_email' => $profile->contact_email,
            'contact_phone' => $profile->contact_phone,
            'contact_whatsapp' => $profile->contact_whatsapp,
            'contact_zalo' => $profile->contact_zalo,
            'hotline_label' => $profile->hotline_label,
            'slogan' => $profile->slogan,
            'address_street' => $address['street'] ?? null,
            'address_locality' => $address['locality'] ?? null,
            'address_region' => $address['region'] ?? null,
            'address_postal' => $address['postal'] ?? null,
            'address_country' => $address['country'] ?? null,
            'footer_copyright' => $profile->footer_copyright,
            'show_dmca_badge' => (bool) ($profile->show_dmca_badge ?? true),
            'schema_logo' => $schema['logo'] ?? null,
            'schema_contact_type' => $schema['contact_type'] ?? null,
            'schema_available_language' => is_array($schema['available_language'] ?? null)
                ? implode(', ', $schema['available_language'])
                : (string) ($schema['available_language'] ?? ''),
            // About CMS (dịch được)
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
        ], $this->flattenSocial($social)));
    }

    public function update(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        try {
            $rules = [
                'name' => 'nullable|string|max:120',
                'legal_name' => 'nullable|string|max:255',
                'tagline' => 'nullable|string|max:255',
                'license_number' => 'nullable|string|max:120',
                'contact_email' => 'nullable|email|max:120',
                'contact_phone' => 'nullable|string|max:40',
                'contact_whatsapp' => 'nullable|string|max:40',
                'contact_zalo' => 'nullable|string|max:40',
                'hotline_label' => 'nullable|string|max:80',
                'slogan' => 'nullable|string|max:255',
                'address_street' => 'nullable|string|max:255',
                'address_locality' => 'nullable|string|max:120',
                'address_region' => 'nullable|string|max:120',
                'address_postal' => 'nullable|string|max:40',
                'address_country' => 'nullable|string|max:8',
                'footer_copyright' => 'nullable|string|max:500',
                'show_dmca_badge' => 'nullable|boolean',
                'schema_logo' => 'nullable|string|max:500',
                'schema_contact_type' => 'nullable|string|max:120',
                'schema_available_language' => 'nullable|string|max:255',
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
            ];
            foreach (self::SOCIAL_KEYS as $key) {
                $rules["social_{$key}_url"] = 'nullable|string|max:500';
                $rules["social_{$key}_label"] = 'nullable|string|max:80';
            }
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        DB::transaction(function () use ($request, $validated, $locale) {
            $profile = CompanyProfile::query()->first() ?? new CompanyProfile;

            $defaultLocale = Language::defaultCode();
            $isDefault = strtolower($locale) === strtolower($defaultLocale);
            $touchesIdentity = $request->hasAny([
                'name', 'legal_name', 'tagline', 'license_number',
                'contact_email', 'contact_phone', 'contact_whatsapp', 'contact_zalo',
                'hotline_label', 'slogan',
                'address_street', 'address_locality', 'address_region', 'address_postal', 'address_country',
                'footer_copyright', 'show_dmca_badge',
                'schema_logo', 'schema_contact_type', 'schema_available_language',
                'social_facebook_url', 'social_youtube_url', 'social_instagram_url', 'social_tiktok_url',
                'social_facebook_label', 'social_youtube_label', 'social_instagram_label', 'social_tiktok_label',
            ]);

            if (($isDefault || ! $profile->exists) && $touchesIdentity) {
                $profile->fill([
                    'name' => array_key_exists('name', $validated) ? ($validated['name'] ?? null) : $profile->name,
                    'legal_name' => array_key_exists('legal_name', $validated) ? ($validated['legal_name'] ?? null) : $profile->legal_name,
                    'tagline' => array_key_exists('tagline', $validated) ? ($validated['tagline'] ?? null) : $profile->tagline,
                    'license_number' => array_key_exists('license_number', $validated) ? ($validated['license_number'] ?? null) : $profile->license_number,
                    'contact_email' => array_key_exists('contact_email', $validated) ? ($validated['contact_email'] ?? null) : $profile->contact_email,
                    'contact_phone' => array_key_exists('contact_phone', $validated) ? ($validated['contact_phone'] ?? null) : $profile->contact_phone,
                    'contact_whatsapp' => array_key_exists('contact_whatsapp', $validated) ? ($validated['contact_whatsapp'] ?? null) : $profile->contact_whatsapp,
                    'contact_zalo' => array_key_exists('contact_zalo', $validated) ? ($validated['contact_zalo'] ?? null) : $profile->contact_zalo,
                    'hotline_label' => array_key_exists('hotline_label', $validated) ? ($validated['hotline_label'] ?? null) : $profile->hotline_label,
                    'slogan' => array_key_exists('slogan', $validated) ? ($validated['slogan'] ?? null) : $profile->slogan,
                    'address' => [
                        'street' => $validated['address_street'] ?? ($profile->address['street'] ?? null),
                        'locality' => $validated['address_locality'] ?? ($profile->address['locality'] ?? null),
                        'region' => $validated['address_region'] ?? ($profile->address['region'] ?? null),
                        'postal' => $validated['address_postal'] ?? ($profile->address['postal'] ?? null),
                        'country' => $validated['address_country'] ?? ($profile->address['country'] ?? null),
                    ],
                    'social_links' => $this->buildSocialFromRequest($validated, $profile->social_links),
                    'schema_settings' => [
                        'logo' => array_key_exists('schema_logo', $validated)
                            ? ($validated['schema_logo'] ?? null)
                            : ($profile->schema_settings['logo'] ?? null),
                        'contact_type' => array_key_exists('schema_contact_type', $validated)
                            ? ($validated['schema_contact_type'] ?? 'customer service')
                            : ($profile->schema_settings['contact_type'] ?? 'customer service'),
                        'available_language' => array_key_exists('schema_available_language', $validated)
                            ? $this->parseLanguageList($validated['schema_available_language'] ?? null)
                            : ($profile->schema_settings['available_language'] ?? ['Vietnamese', 'English']),
                    ],
                    'footer_copyright' => array_key_exists('footer_copyright', $validated)
                        ? ($validated['footer_copyright'] ?? null)
                        : $profile->footer_copyright,
                    'show_dmca_badge' => array_key_exists('show_dmca_badge', $validated)
                        ? (bool) $validated['show_dmca_badge']
                        : ($profile->show_dmca_badge ?? true),
                ]);
            }

            if (! $profile->exists || $profile->isDirty()) {
                $profile->save();
            }

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

    /**
     * @param  array<string, mixed>  $social
     * @return array<string, string>
     */
    private function flattenSocial(array $social): array
    {
        $out = [];
        $defaults = [
            'facebook' => ['label' => 'Facebook', 'icon' => 'facebook'],
            'youtube' => ['label' => 'YouTube', 'icon' => 'play'],
            'instagram' => ['label' => 'Instagram', 'icon' => 'photo'],
            'tiktok' => ['label' => 'TikTok', 'icon' => 'share'],
        ];
        foreach (self::SOCIAL_KEYS as $key) {
            $row = is_array($social[$key] ?? null) ? $social[$key] : [];
            $out["social_{$key}_url"] = (string) ($row['url'] ?? '');
            $out["social_{$key}_label"] = (string) ($row['label'] ?? $defaults[$key]['label']);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>|null  $existing
     * @return array<string, array{label: string, icon: string, url: string}>
     */
    private function buildSocialFromRequest(array $validated, ?array $existing): array
    {
        $defaults = [
            'facebook' => ['label' => 'Facebook', 'icon' => 'facebook'],
            'youtube' => ['label' => 'YouTube', 'icon' => 'play'],
            'instagram' => ['label' => 'Instagram', 'icon' => 'photo'],
            'tiktok' => ['label' => 'TikTok', 'icon' => 'share'],
        ];
        $out = [];
        foreach (self::SOCIAL_KEYS as $key) {
            $prev = is_array($existing[$key] ?? null) ? $existing[$key] : [];
            $out[$key] = [
                'label' => (string) ($validated["social_{$key}_label"] ?? $prev['label'] ?? $defaults[$key]['label']),
                'icon' => (string) ($prev['icon'] ?? $defaults[$key]['icon']),
                'url' => trim((string) ($validated["social_{$key}_url"] ?? $prev['url'] ?? '')),
            ];
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    private function parseLanguageList(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return ['Vietnamese', 'English'];
        }

        return array_values(array_filter(array_map(
            static fn (string $s) => trim($s),
            explode(',', $raw)
        )));
    }
}
