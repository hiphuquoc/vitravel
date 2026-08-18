<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PriceTable extends Model
{
    use BelongsToProject;

    public const UNIT_PERSON = 'per_person';

    public const UNIT_ROOM = 'per_room';

    public const UNIT_VEHICLE = 'per_vehicle';

    public const UNIT_GROUP = 'per_group';

    public const UNIT_UNIT = 'per_unit';

    protected $fillable = [
        'project_id', 'priceable_type', 'priceable_id', 'currency', 'unit', 'notes',
    ];

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PriceVariant::class)->orderBy('sort')->orderBy('id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(PricePeriod::class)->orderBy('sort')->orderBy('starts_on');
    }
}
