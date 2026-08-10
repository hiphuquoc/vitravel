<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/** Guard khi migration featured chưa chạy — tránh 500 làm trống trang / admin. */
final class HomeFeaturedSchema
{
    public static function has(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public static function hasServices(): bool
    {
        return self::has('home_featured_services');
    }

    public static function hasTeamMembers(): bool
    {
        return self::has('home_featured_team_members');
    }

    public static function hasReviews(): bool
    {
        return self::has('home_featured_reviews');
    }

    public static function hasVideos(): bool
    {
        return self::has('home_featured_videos');
    }
}
