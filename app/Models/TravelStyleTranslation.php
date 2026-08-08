<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelStyleTranslation extends Model
{
    use BelongsToProject;

    protected $fillable = ['project_id', 'travel_style_id', 'language_id', 'name', 'slug', 'description'];

    public function travelStyle(): BelongsTo
    {
        return $this->belongsTo(TravelStyle::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
