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
        'license_number', 'intro_image_id', 'mission_image_id',
        'vision_image_id', 'policy_image_id',
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
}
