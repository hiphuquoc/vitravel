<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaticPageTranslation extends Model
{
    protected $fillable = ['static_page_id', 'language_id', 'title', 'body'];

    public function staticPage(): BelongsTo
    {
        return $this->belongsTo(StaticPage::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
