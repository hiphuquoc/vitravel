<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMemberDegree extends Model
{
    protected $fillable = ['team_member_id', 'title', 'school', 'ordering'];

    protected function casts(): array
    {
        return [
            'ordering' => 'integer',
        ];
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TeamMemberDegreeItem::class)->orderBy('id');
    }
}
