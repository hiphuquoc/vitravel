<?php

namespace App\Support;

use RuntimeException;

/**
 * Loader dữ liệu seed theo PROJECT_SEED (.env) → project/seed_{name}.php
 */
final class ProjectSeed
{
    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /** @var string|null */
    private static ?string $resolvedPath = null;

    /**
     * Profile từ config (vd: vitravel, seed_bali.php).
     */
    public static function profile(): string
    {
        $raw = (string) config('project.seed', 'vitravel');

        return trim($raw) !== '' ? trim($raw) : 'vitravel';
    }

    /**
     * Đường dẫn tuyệt đối tới file seed đang active.
     */
    public static function path(): string
    {
        if (self::$resolvedPath !== null) {
            return self::$resolvedPath;
        }

        $dir = trim((string) config('project.seed_dir', 'project'), '/\\');
        $profile = self::profile();

        // Cho phép path tương đối base (ít dùng): project/custom/foo.php
        if (str_contains($profile, '/') || str_contains($profile, '\\')) {
            $normalized = str_replace('\\', '/', $profile);
            if (str_ends_with(strtolower($normalized), '.php')) {
                $relative = $normalized;
            } else {
                // island/phu-quoc → seed_island_phu-quoc.php trong seed_dir
                $slug = str_replace('/', '_', trim($normalized, '/'));
                $relative = $dir.'/seed_'.$slug.'.php';
            }

            return self::$resolvedPath = base_path($relative);
        }

        $file = str_ends_with(strtolower($profile), '.php')
            ? $profile
            : 'seed_'.$profile.'.php';

        // An toàn: chỉ tên file, không path traversal
        $file = basename($file);
        $path = base_path($dir.'/'.$file);

        // Tương thích: PROJECT_SEED=vitravel vẫn đọc project/seed.php nếu chưa rename
        if (! is_file($path) && $file === 'seed_vitravel.php') {
            $legacy = base_path($dir.'/seed.php');
            if (is_file($legacy)) {
                $path = $legacy;
            }
        }

        return self::$resolvedPath = $path;
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
                    "Thiếu file seed dự án: {$path}\n"
                    .'Đặt PROJECT_SEED trong .env (vd: vitravel → project/seed_vitravel.php) '
                    .'hoặc tạo file tương ứng. Xem project/README.md.'
                );
            }

            /** @var mixed $loaded */
            $loaded = require $path;
            if (! is_array($loaded)) {
                throw new RuntimeException(self::path().' phải return array.');
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
        self::$resolvedPath = null;
    }

    /** @deprecated Dùng flush() */
    public static function forgetCached(): void
    {
        self::flush();
    }
}
