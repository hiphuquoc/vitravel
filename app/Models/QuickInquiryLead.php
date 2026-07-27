<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickInquiryLead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'message', 'source_page_url',
        'related_package_id', 'locale', 'status', 'ip_address', 'user_agent',
        'utm', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'utm' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    public function relatedPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'related_package_id');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }
}
