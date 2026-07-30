<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMemberActivityImage extends Model
{
    protected $fillable = ['team_member_id', 'media_id', 'ordering'];

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

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function imageUrl(?string $variant = 'lg'): ?string
    {
        return app(MediaService::class)->publicUrl($this->media, $variant);
    }

    public function thumbUrl(): ?string
    {
        return app(MediaService::class)->publicUrl($this->media, 'thumb');
    }
}
