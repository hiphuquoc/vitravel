<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMemberAchievement extends Model
{
    protected $fillable = ['team_member_id', 'content', 'ordering'];

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
}
