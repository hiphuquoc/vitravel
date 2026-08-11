<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSystemPrompt extends Model
{
    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
        'version',
        'system',
        'user',
        'output_format',
        'variables',
        'entity_types',
        'is_active',
        'is_customized',
        'seeded_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'variables' => 'array',
            'entity_types' => 'array',
            'is_active' => 'boolean',
            'is_customized' => 'boolean',
            'seeded_at' => 'datetime',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return array{key: string, name: string, system: string, user: string, output_format: string}
     */
    public function toPromptPayload(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'system' => $this->system,
            'user' => $this->user,
            'output_format' => $this->output_format ?: 'json',
        ];
    }
}
