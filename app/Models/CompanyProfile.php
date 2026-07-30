<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    use HasTranslations;

    /** @var list<string> */
    protected array $translatable = [
        'greeting_title', 'intro_text', 'mission_title', 'mission_text',
        'vision_title', 'vision_text', 'sales_policy_title', 'sales_policy_content',
        'about_page_title', 'about_page_subtitle', 'about_seo_title', 'about_seo_description',
        'values_section_title', 'values_hub_label',
        'reasons_section_title', 'reasons_cta_label', 'reasons_cta_url',
        'sales_policy_cta_label', 'sales_policy_cta_url',
        'reference_section_title', 'reference_section_subtitle',
    ];

    protected $fillable = [
        'license_number', 'contact_email', 'contact_phone', 'contact_whatsapp', 'slogan',
        'intro_image_id', 'mission_image_id', 'vision_image_id', 'policy_image_id',
        'reasons_image_id', 'about_banner_media_id',
    ];

    protected function translationClass(): string
    {
        return CompanyProfileTranslation::class;
    }

    public function introImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'intro_image_id');
    }

    public function missionImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'mission_image_id');
    }

    public function visionImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'vision_image_id');
    }

    public function policyImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'policy_image_id');
    }

    public function reasonsImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'reasons_image_id');
    }

    public function aboutBanner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'about_banner_media_id');
    }

    public function mediaUrl(string $relation, ?string $variant = 'lg'): ?string
    {
        $media = $this->{$relation};

        return app(MediaService::class)->publicUrl($media instanceof Media ? $media : null, $variant);
    }

    public function mediaSrcset(string $relation): ?string
    {
        $media = $this->{$relation};

        return app(MediaService::class)->srcset($media instanceof Media ? $media : null);
    }

    public static function current(): ?self
    {
        return static::query()->with([
            'translations',
            'introImage',
            'missionImage',
            'visionImage',
            'policyImage',
            'reasonsImage',
            'aboutBanner',
        ])->first();
    }

    /**
     * Liên hệ / branding cho public — config/company.php làm mặc định,
     * field admin (Company Profile) ghi đè khi đã nhập.
     *
     * @return array{
     *   name: string,
     *   legal_name: string,
     *   tagline: string,
     *   slogan: string,
     *   email: string,
     *   phone: string,
     *   whatsapp: string,
     *   zalo: string,
     *   hotline_label: string,
     *   license: ?string,
     *   address: array<string, mixed>,
     *   social: list<array{key: string, label: string, icon: string, url: string}>,
     *   same_as: list<string>,
     *   footer_copyright: string,
     *   show_dmca_badge: bool,
     *   schema: array<string, mixed>
     * }
     */
    public static function contact(): array
    {
        $cfg = config('company', []);
        $contactCfg = is_array($cfg['contact'] ?? null) ? $cfg['contact'] : [];
        $profile = static::current();

        $email = filled($profile?->contact_email)
            ? (string) $profile->contact_email
            : (string) ($contactCfg['email'] ?? 'hello@vitravel.vn');
        $phone = filled($profile?->contact_phone)
            ? (string) $profile->contact_phone
            : (string) ($contactCfg['phone'] ?? '+84 24 3999 8888');
        $whatsapp = filled($profile?->contact_whatsapp)
            ? (string) $profile->contact_whatsapp
            : (string) ($contactCfg['whatsapp'] ?? $phone);
        $zalo = filled($contactCfg['zalo'] ?? null)
            ? (string) $contactCfg['zalo']
            : $phone;
        $slogan = filled($profile?->slogan)
            ? (string) $profile->slogan
            : (string) ($cfg['slogan'] ?? '');
        $license = filled($profile?->license_number)
            ? (string) $profile->license_number
            : (string) ($cfg['license_number'] ?? '');

        $social = [];
        $sameAs = [];
        foreach ((array) ($cfg['social'] ?? []) as $key => $row) {
            if (! is_array($row)) {
                continue;
            }
            $url = trim((string) ($row['url'] ?? ''));
            if ($url === '' || $url === '#') {
                continue;
            }
            $social[] = [
                'key' => (string) $key,
                'label' => (string) ($row['label'] ?? $key),
                'icon' => (string) ($row['icon'] ?? 'share'),
                'url' => $url,
            ];
            $sameAs[] = $url;
        }

        $copyrightTpl = (string) ($cfg['footer']['copyright'] ?? '© :year ViTravel.');
        $copyright = str_replace(
            [':year', ':license', ':name'],
            [(string) date('Y'), $license !== '' ? $license : '—', (string) ($cfg['name'] ?? 'ViTravel')],
            $copyrightTpl
        );

        return [
            'name' => (string) ($cfg['name'] ?? 'ViTravel'),
            'legal_name' => (string) ($cfg['legal_name'] ?? ($cfg['name'] ?? 'ViTravel')),
            'tagline' => (string) ($cfg['tagline'] ?? ''),
            'slogan' => $slogan,
            'email' => $email,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'zalo' => $zalo,
            'hotline_label' => (string) ($contactCfg['hotline_label'] ?? 'Hotline'),
            'license' => $license !== '' ? $license : null,
            'address' => is_array($cfg['address'] ?? null) ? $cfg['address'] : [],
            'social' => $social,
            'same_as' => $sameAs,
            'footer_copyright' => $copyright,
            'show_dmca_badge' => (bool) ($cfg['footer']['show_dmca_badge'] ?? true),
            'schema' => is_array($cfg['schema'] ?? null) ? $cfg['schema'] : [],
        ];
    }
}
