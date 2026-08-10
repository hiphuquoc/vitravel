<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedTeamMember extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id',
        'team_member_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    /** @return array<int, string> */
    public static function memberOptions(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $langId = Language::idByCode($locale);

        return TeamMember::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->with('translations')
            ->get()
            ->mapWithKeys(function (TeamMember $member) use ($langId) {
                $name = $member->translations->firstWhere('language_id', $langId)?->name
                    ?? $member->translations->first()?->name
                    ?? ('#'.$member->id);
                $role = $member->translations->firstWhere('language_id', $langId)?->role
                    ?? $member->translations->first()?->role
                    ?? '';
                $label = trim($name.($role ? " — {$role}" : ''));

                return [$member->id => $label];
            })
            ->all();
    }
}
