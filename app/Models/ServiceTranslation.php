<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTranslation extends Model
{
    protected $fillable = [
        'service_id', 'language_id', 'title', 'location_label', 'summary',
        'featured_quote_text', 'featured_quote_author',
        'highlights', 'inclusions', 'exclusions', 'notes', 'content',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'notes' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
