<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfileTranslation extends Model
{
    protected $fillable = [
        'company_profile_id', 'language_id', 'greeting_title', 'intro_text',
        'mission_title', 'mission_text', 'vision_title', 'vision_text',
        'sales_policy_title', 'sales_policy_content',
        'about_page_title', 'about_page_subtitle', 'about_seo_title', 'about_seo_description',
        'values_section_title', 'values_hub_label',
        'reasons_section_title', 'reasons_cta_label', 'reasons_cta_url',
        'sales_policy_cta_label', 'sales_policy_cta_url',
        'reference_section_title', 'reference_section_subtitle',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
