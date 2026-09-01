<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationItemTranslation extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id',
        'navigation_item_id',
        'language_id',
        'label',
        'lead_label',
        'meta',
    ];

    public function navigationItem(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
