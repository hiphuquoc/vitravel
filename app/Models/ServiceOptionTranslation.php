<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOptionTranslation extends Model
{
    protected $fillable = [
        'service_option_id', 'language_id', 'name', 'description', 'amenities',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
        ];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ServiceOption::class, 'service_option_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
