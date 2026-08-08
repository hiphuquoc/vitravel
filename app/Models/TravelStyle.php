<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelStyle extends Model
{
    use BelongsToProject, HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['name', 'slug', 'description'];

    protected $fillable = ['project_id', 'code', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return TravelStyleTranslation::class;
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_travel_style');
    }
}
