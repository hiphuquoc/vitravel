<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['name', 'role', 'short_bio'];

    protected $fillable = [
        'department', 'avatar_media_id', 'sort', 'is_active', 'show_on_home',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_home' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return TeamMemberTranslation::class;
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }

    public function avatarUrl(?string $variant = 'thumb'): ?string
    {
        return app(MediaService::class)->publicUrl($this->avatar, $variant);
    }

    public function avatarSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->avatar);
    }
}
