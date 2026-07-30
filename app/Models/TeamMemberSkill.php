<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMemberSkill extends Model
{
    protected $fillable = ['team_member_id', 'skill', 'percent', 'ordering'];

    protected function casts(): array
    {
        return [
            'percent' => 'integer',
            'ordering' => 'integer',
        ];
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }
}
