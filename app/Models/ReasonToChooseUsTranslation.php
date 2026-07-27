<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReasonToChooseUsTranslation extends Model
{
    protected $table = 'reason_to_choose_us_translations';

    protected $fillable = ['reason_to_choose_us_id', 'language_id', 'title', 'description'];

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReasonToChooseUs::class, 'reason_to_choose_us_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
