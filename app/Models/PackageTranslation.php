<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageTranslation extends Model
{
    protected $fillable = [
        'package_id', 'language_id', 'title', 'start_location', 'end_location',
        'places_to_visit', 'featured_quote_text', 'featured_quote_author',
        'highlights_intro', 'highlight_bullets', 'inclusions', 'exclusions', 'notes', 'summary',
    ];

    protected function casts(): array
    {
        return [
            'places_to_visit' => 'array',
            'highlight_bullets' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'notes' => 'array',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
