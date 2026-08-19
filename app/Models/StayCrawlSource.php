<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StayCrawlSource extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id', 'host', 'user_agent', 'delay_ms', 'respect_robots', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'delay_ms' => 'integer',
            'respect_robots' => 'boolean',
        ];
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(StayCrawlJob::class, 'source_id');
    }
}
