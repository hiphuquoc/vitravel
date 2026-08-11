<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'prompt_key',
        'feature',
        'entity_type',
        'project_id',
        'user_id',
        'provider',
        'model',
        'latency_ms',
        'success',
        'error_code',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'latency_ms' => 'integer',
            'success' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
