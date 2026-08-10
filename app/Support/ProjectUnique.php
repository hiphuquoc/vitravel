<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Rule::unique theo project hiện tại — tránh đụng unique global khi nhiều dự án
 * cùng code/slug (vd. zone `ran-san-ho` → code RANSANHO ở culaocham + phuquy).
 */
final class ProjectUnique
{
    public static function rule(string $table, string $column): Unique
    {
        $rule = Rule::unique($table, $column);

        $projectId = ProjectContext::id();
        if ($projectId !== null && self::tableHasColumn($table, 'project_id')) {
            $rule = $rule->where('project_id', $projectId);
        }

        return $rule;
    }

    public static function softDeleting(string $table, string $column): Unique
    {
        $rule = self::rule($table, $column);
        if (self::tableHasColumn($table, 'deleted_at')) {
            $rule = $rule->whereNull('deleted_at');
        }

        return $rule;
    }

    private static function tableHasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
