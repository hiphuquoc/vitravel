<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id', 'name', 'email', 'phone', 'address', 'message', 'locale',
        'status', 'ip_address', 'user_agent', 'utm', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'utm' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }
}
