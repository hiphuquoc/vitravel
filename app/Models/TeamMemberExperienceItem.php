<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMemberExperienceItem extends Model
{
    protected $fillable = ['team_member_experience_id', 'content'];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(TeamMemberExperience::class, 'team_member_experience_id');
    }
}
