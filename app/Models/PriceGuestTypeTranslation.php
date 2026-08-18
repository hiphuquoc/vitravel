<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceGuestTypeTranslation extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id', 'price_guest_type_id', 'language_id', 'name', 'description',
    ];

    public function guestType(): BelongsTo
    {
        return $this->belongsTo(PriceGuestType::class, 'price_guest_type_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
