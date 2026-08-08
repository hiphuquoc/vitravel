<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    use BelongsToProject, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['question', 'answer'];

    protected $fillable = ['project_id', 'faqable_type', 'faqable_id', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return FaqTranslation::class;
    }

    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }
}
