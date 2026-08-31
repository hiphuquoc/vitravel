<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TeamMember extends Model
{
    use BelongsToProject, HasSeo, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['name', 'role', 'short_bio', 'bio_html'];

    protected $fillable = [
        'project_id', 'department', 'avatar_media_id', 'sort', 'is_active', 'show_on_home',
        'phone', 'email', 'area', 'years_experience', 'languages',
        'stat_clients', 'stat_tours', 'stat_awards', 'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_home' => 'boolean',
            'is_verified' => 'boolean',
            'sort' => 'integer',
            'years_experience' => 'integer',
            'stat_clients' => 'integer',
            'stat_tours' => 'integer',
            'stat_awards' => 'integer',
            'languages' => 'array',
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

    public function achievements(): HasMany
    {
        return $this->hasMany(TeamMemberAchievement::class)->orderBy('ordering');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(TeamMemberSkill::class)->orderBy('ordering');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(TeamMemberExperience::class)->orderBy('ordering');
    }

    public function degrees(): HasMany
    {
        return $this->hasMany(TeamMemberDegree::class)->orderBy('ordering');
    }

    public function activityImages(): HasMany
    {
        return $this->hasMany(TeamMemberActivityImage::class)->orderBy('ordering');
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
