<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReasonToChooseUs extends Model
{
    use HasTranslations;

    protected $table = 'reasons_to_choose_us';

    /** @var list<string> */
    protected array $translatable = ['title', 'description'];

    protected $fillable = ['section_image_id', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return ReasonToChooseUsTranslation::class;
    }

    public function sectionImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'section_image_id');
    }
}
