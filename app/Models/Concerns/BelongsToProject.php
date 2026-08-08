<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Project;
use App\Support\ProjectContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes queries to the current ProjectContext when set.
 * When context is null (CLI / legacy), no filter is applied.
 *
 * @mixin Model
 */
trait BelongsToProject
{
    public static function bootBelongsToProject(): void
    {
        static::addGlobalScope('project', function (Builder $builder): void {
            $projectId = ProjectContext::id();
            if ($projectId === null) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.project_id', $projectId);
        });

        static::creating(function (Model $model): void {
            if (! empty($model->getAttribute('project_id'))) {
                return;
            }

            $projectId = ProjectContext::id();
            if ($projectId !== null) {
                $model->setAttribute('project_id', $projectId);
            }
        });
    }

    public function initializeBelongsToProject(): void
    {
        if (! in_array('project_id', $this->getFillable(), true)) {
            $this->mergeFillable(['project_id']);
        }
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public static function withoutProjectScope(): Builder
    {
        return static::withoutGlobalScope('project');
    }
}
