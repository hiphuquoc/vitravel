<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class CompanyValue extends Model
{
    use BelongsToProject, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['name', 'description'];

    protected $fillable = ['project_id', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return CompanyValueTranslation::class;
    }
}
