<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    use HasTranslations;

    /** @var list<string> */
    protected array $translatable = [
        'greeting_title', 'intro_text', 'mission_title', 'mission_text',
        'vision_title', 'vision_text', 'sales_policy_title', 'sales_policy_content',
    ];

    protected $fillable = [
        'license_number', 'contact_email', 'contact_phone', 'contact_whatsapp', 'slogan',
        'intro_image_id', 'mission_image_id', 'vision_image_id', 'policy_image_id',
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

    public static function current(): ?self
    {
        return static::query()->with('translations')->first();
    }

    /** @return array{email: string, phone: string, whatsapp: string, slogan: string, license: ?string} */
    public static function contact(): array
    {
        $profile = static::current();

        return [
            'email' => $profile?->contact_email ?: 'hello@vitravel.example',
            'phone' => $profile?->contact_phone ?: '+84 24 3999 8888',
            'whatsapp' => $profile?->contact_whatsapp ?: '+84 912 345 678',
            'slogan' => $profile?->slogan ?: '“Hài lòng hơn cả mong đợi”',
            'license' => $profile?->license_number,
        ];
    }
}
