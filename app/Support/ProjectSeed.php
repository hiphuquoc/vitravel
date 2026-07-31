<?php

namespace App\Support;

use RuntimeException;

/**
 * Loader cấu hình seed dự án — single source: project/seed.php
 *
 * Clone dự án mới: sửa project/seed.php → php artisan migrate --seed
 */
final class ProjectSeed
{
    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    public static function path(): string
    {
        return base_path('project/seed.php');
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (self::$data === null) {
            $path = self::path();

            if (! is_file($path)) {
                throw new RuntimeException(
                    "Thiếu file cấu hình dự án: {$path}. Tạo/sửa project/seed.php rồi chạy lại seed."
                );
            }

            /** @var mixed $loaded */
            $loaded = require $path;
            if (! is_array($loaded)) {
                throw new RuntimeException('project/seed.php phải return array.');
            }

            self::$data = $loaded;
        }

        return self::$data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * @return array<string, mixed>
     */
    public static function meta(): array
    {
        $meta = self::get('meta', []);

        return is_array($meta) ? $meta : [];
    }

    /**
     * @return array<string, string>
     */
    public static function countryCodes(): array
    {
        $codes = self::meta()['country_codes'] ?? [];

        return is_array($codes) ? $codes : [];
    }

    public static function flush(): void
    {
        self::$data = null;
    }

    /** @deprecated Dùng flush() */
    public static function forgetCached(): void
    {
        self::flush();
    }
}
