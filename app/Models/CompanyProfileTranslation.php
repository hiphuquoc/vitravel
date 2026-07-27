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
