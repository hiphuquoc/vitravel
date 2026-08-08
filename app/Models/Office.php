<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use BelongsToProject, HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['city_label', 'address_line'];

    protected $fillable = [
        'project_id', 'country_id', 'phone', 'whatsapp', 'email', 'map_embed_url',
        'latitude', 'longitude', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return OfficeTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
