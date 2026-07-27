<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomTourRequest extends Model
{
    protected $fillable = [
        'adults_count', 'children_count', 'infants_count', 'duration_text',
        'arrival_date', 'countries_to_visit', 'accommodation_preference',
        'budget_amount', 'budget_currency', 'budget_unit', 'gender',
        'first_name', 'last_name', 'email', 'phone', 'nationality', 'city',
        'additional_notes', 'locale', 'status', 'ip_address', 'user_agent',
        'utm', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'adults_count' => 'integer',
            'children_count' => 'integer',
            'infants_count' => 'integer',
            'arrival_date' => 'date',
            'countries_to_visit' => 'array',
            'accommodation_preference' => 'array',
            'budget_amount' => 'decimal:2',
            'utm' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
