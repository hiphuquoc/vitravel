<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMemberDegreeItem extends Model
{
    protected $fillable = ['team_member_degree_id', 'content'];

    public function degree(): BelongsTo
    {
        return $this->belongsTo(TeamMemberDegree::class, 'team_member_degree_id');
    }
}
