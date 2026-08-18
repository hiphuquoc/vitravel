<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRate extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id', 'period_id', 'variant_id', 'guest_type_id',
        'amount', 'compare_at_amount', 'min_qty', 'max_qty',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'compare_at_amount' => 'decimal:2',
            'min_qty' => 'integer',
            'max_qty' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PricePeriod::class, 'period_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(PriceVariant::class, 'variant_id');
    }

    public function guestType(): BelongsTo
    {
        return $this->belongsTo(PriceGuestType::class, 'guest_type_id');
    }
}
