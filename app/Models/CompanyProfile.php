<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use App\Support\ProjectSeed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    protected static ?self $memoCurrent = null;
    protected static ?array $memoContact = null;
    use BelongsToProject, HasTranslations;

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
        'project_id', 'name', 'legal_name', 'tagline',
        'license_number', 'contact_email', 'contact_phone', 'contact_whatsapp', 'contact_zalo',
        'hotline_label', 'slogan',
        'address', 'social_links', 'schema_settings',
        'footer_copyright', 'show_dmca_badge',
        'intro_image_id', 'mission_image_id', 'vision_image_id', 'policy_image_id',
        'reasons_image_id', 'about_banner_media_id',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'social_links' => 'array',
            'schema_settings' => 'array',
            'show_dmca_badge' => 'boolean',
        ];
    }

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
        if (static::$memoCurrent !== null) {
            return static::$memoCurrent;
        }

        return static::$memoCurrent = static::query()->with([
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
     * Defaults từ ProjectSeed `company` (hoặc config/company.php fallback).
     *
     * @return array<string, mixed>
     */
    public static function seedDefaults(): array
    {
        $fromSeed = ProjectSeed::get('company', []);
        if (is_array($fromSeed) && $fromSeed !== []) {
            return $fromSeed;
        }

        $cfg = config('company', []);

        return is_array($cfg) ? $cfg : [];
    }

    /**
     * Map seed/config shape → cột company_profiles.
     *
     * @param  array<string, mixed>  $src
     * @return array<string, mixed>
     */
    public static function attributesFromSeed(array $src): array
    {
        $contact = is_array($src['contact'] ?? null) ? $src['contact'] : [];
        $footer = is_array($src['footer'] ?? null) ? $src['footer'] : [];

        return [
            'name' => $src['name'] ?? null,
            'legal_name' => $src['legal_name'] ?? null,
            'tagline' => $src['tagline'] ?? null,
            'slogan' => $src['slogan'] ?? null,
            'license_number' => $src['license_number'] ?? null,
            'contact_email' => $contact['email'] ?? null,
            'contact_phone' => $contact['phone'] ?? null,
            'contact_whatsapp' => $contact['whatsapp'] ?? null,
            'contact_zalo' => $contact['zalo'] ?? null,
            'hotline_label' => $contact['hotline_label'] ?? null,
            'address' => is_array($src['address'] ?? null) ? $src['address'] : null,
            'social_links' => is_array($src['social'] ?? null) ? $src['social'] : null,
            'schema_settings' => is_array($src['schema'] ?? null) ? $src['schema'] : null,
            'footer_copyright' => $footer['copyright'] ?? null,
            'show_dmca_badge' => (bool) ($footer['show_dmca_badge'] ?? true),
        ];
    }

    /**
     * Liên hệ / branding public — ưu tiên DB; thiếu thì seed/config.
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
        if (static::$memoContact !== null) {
            return static::$memoContact;
        }

        $defaults = static::seedDefaults();
        $contactCfg = is_array($defaults['contact'] ?? null) ? $defaults['contact'] : [];
        $profile = static::current();

        $pick = static function (?string $db, mixed $fallback): string {
            if (filled($db)) {
                return (string) $db;
            }

            return filled($fallback) ? (string) $fallback : '';
        };

        $name = $pick($profile?->name, $defaults['name'] ?? 'ViTravel');
        $email = $pick($profile?->contact_email, $contactCfg['email'] ?? 'hello@vitravel.vn');
        $phone = $pick($profile?->contact_phone, $contactCfg['phone'] ?? '');
        $whatsapp = $pick($profile?->contact_whatsapp, $contactCfg['whatsapp'] ?? $phone);
        $zalo = $pick($profile?->contact_zalo, $contactCfg['zalo'] ?? $phone);
        $slogan = $pick($profile?->slogan, $defaults['slogan'] ?? '');
        $license = $pick($profile?->license_number, $defaults['license_number'] ?? '');
        $hotlineLabel = $pick($profile?->hotline_label, $contactCfg['hotline_label'] ?? 'Hotline');
        $tagline = $pick($profile?->tagline, $defaults['tagline'] ?? '');
        $legalName = $pick($profile?->legal_name, $defaults['legal_name'] ?? $name);

        $address = is_array($profile?->address) && $profile->address !== []
            ? $profile->address
            : (is_array($defaults['address'] ?? null) ? $defaults['address'] : []);

        $socialSrc = is_array($profile?->social_links) && $profile->social_links !== []
            ? $profile->social_links
            : (is_array($defaults['social'] ?? null) ? $defaults['social'] : []);

        $social = [];
        $sameAs = [];
        foreach ($socialSrc as $key => $row) {
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

        $schema = is_array($profile?->schema_settings) && $profile->schema_settings !== []
            ? $profile->schema_settings
            : (is_array($defaults['schema'] ?? null) ? $defaults['schema'] : []);

        $copyrightTpl = $pick(
            $profile?->footer_copyright,
            $defaults['footer']['copyright'] ?? '© :year '.$name.'.'
        );
        $copyright = str_replace(
            [':year', ':license', ':name'],
            [(string) date('Y'), $license !== '' ? $license : '—', $name],
            $copyrightTpl
        );

        $showDmca = $profile !== null && $profile->exists
            ? (bool) $profile->show_dmca_badge
            : (bool) ($defaults['footer']['show_dmca_badge'] ?? true);

        return [
            'name' => $name,
            'legal_name' => $legalName,
            'tagline' => $tagline,
            'slogan' => $slogan,
            'email' => $email,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'zalo' => $zalo,
            'hotline_label' => $hotlineLabel,
            'license' => $license !== '' ? $license : null,
            'address' => $address,
            'social' => $social,
            'same_as' => $sameAs,
            'footer_copyright' => $copyright,
            'show_dmca_badge' => $showDmca,
            'schema' => $schema,
        ];
    }
}
