<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Project;

/**
 * Runtime project (tenant) context — set by host middleware / admin header.
 * When null, BelongsToProject does not filter (artisan / migrate / legacy).
 */
final class ProjectContext
{
    private static ?Project $project = null;

    public static function set(?Project $project): void
    {
        self::$project = $project;
    }

    public static function get(): ?Project
    {
        return self::$project;
    }

    public static function id(): ?int
    {
        return self::$project?->id;
    }

    public static function code(): ?string
    {
        $code = self::$project?->code;

        return filled($code) ? (string) $code : null;
    }

    public static function clear(): void
    {
        self::$project = null;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function run(Project $project, callable $callback): mixed
    {
        $previous = self::$project;
        self::set($project);

        try {
            return $callback();
        } finally {
            self::set($previous);
        }
    }
}
