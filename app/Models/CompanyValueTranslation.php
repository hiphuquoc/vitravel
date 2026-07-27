<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyValueTranslation extends Model
{
    protected $fillable = ['company_value_id', 'language_id', 'name', 'description'];

    public function companyValue(): BelongsTo
    {
        return $this->belongsTo(CompanyValue::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
