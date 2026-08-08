<?php

namespace App\Support;

use RuntimeException;

/**
 * Loader dữ liệu seed theo profile → project/seed_{name}.php
 *
 * Profile được chọn bởi:
 *   1. ProjectSeed::useProfile($name) — CLI `project:seed` / `project:ensure`
 *   2. ProjectContext (seed_profile hoặc code) — runtime khi đã resolve project
 *
 * Không còn config('project.seed') / PROJECT_SEED trong .env.
 * Chuẩn hoá alias dự án (vd. Cát Bà: zones/zoneSlug → countries/countrySlug)
 * để seeder/CMS dùng chung một shape.
 */
final class ProjectSeed
{
    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /** @var string|null */
    private static ?string $resolvedPath = null;

    private static ?string $forcedProfile = null;

    /**
     * Ép profile seed (CLI). Gọi clearProfile() khi xong.
     */
    public static function useProfile(string $profile): void
    {
        $profile = trim($profile);
        if ($profile === '') {
            throw new RuntimeException('Profile seed rỗng.');
        }

        self::$forcedProfile = $profile;
        self::flush();
    }

    /**
     * Bỏ ép profile + flush cache dữ liệu.
     */
    public static function clearProfile(): void
    {
        self::$forcedProfile = null;
        self::flush();
    }

    /**
     * Profile đang active: forced → ProjectContext → exception.
     */
    public static function profile(): string
    {
        if (self::$forcedProfile !== null && self::$forcedProfile !== '') {
            return self::$forcedProfile;
        }

        $ctx = ProjectContext::get();
        if ($ctx) {
            $fromCtx = filled($ctx->seed_profile)
                ? (string) $ctx->seed_profile
                : (string) ($ctx->code ?? '');
            $fromCtx = trim($fromCtx);
            if ($fromCtx !== '') {
                return $fromCtx;
            }
        }

        throw new RuntimeException(
            "Project seed profile chưa được chọn.\n"
            .'Chạy `php artisan project:seed {profile}` (vd: vitravel, hicatba), '
            .'hoặc ProjectSeed::useProfile() / set ProjectContext trước khi đọc seed. '
            .'Không dùng PROJECT_SEED trong .env — xem project/README.md.'
        );
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

        // Tương thích: seed_vitravel.php thiếu → project/seed.php nếu còn
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
                    .'Tạo project/seed_{name}.php rồi chạy `php artisan project:seed {name}`. '
                    .'Xem project/README.md.'
                );
            }

            /** @var mixed $loaded */
            $loaded = require $path;
            if (! is_array($loaded)) {
                throw new RuntimeException(self::path().' phải return array.');
            }

            self::$data = self::normalize($loaded);
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

    /**
     * Alias dự án → shape chuẩn CMS (countries / countrySlug / …).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalize(array $data): array
    {
        $countries = $data['countries'] ?? null;
        $zones = $data['zones'] ?? null;

        if ((! is_array($countries) || $countries === []) && is_array($zones) && $zones !== []) {
            $data['countries'] = $zones;
        }

        $countryTranslations = $data['country_translations'] ?? null;
        $zoneTranslations = $data['zone_translations'] ?? null;
        if ((! is_array($countryTranslations) || $countryTranslations === [])
            && is_array($zoneTranslations) && $zoneTranslations !== []) {
            $data['country_translations'] = $zoneTranslations;
        }

        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
        $codes = is_array($meta['country_codes'] ?? null) ? $meta['country_codes'] : [];
        if ($codes === [] && is_array($data['countries'] ?? null)) {
            $used = [];
            foreach ($data['countries'] as $row) {
                if (! is_array($row) || empty($row['slug'])) {
                    continue;
                }
                $slug = (string) $row['slug'];
                $code = self::synthesizeCountryCode($slug, $used);
                $codes[$slug] = $code;
                $used[$code] = true;
            }
            $meta['country_codes'] = $codes;
            $data['meta'] = $meta;
        }

        foreach (['tours', 'cruises', 'articles', 'blog_categories', 'tour_categories'] as $listKey) {
            if (! is_array($data[$listKey] ?? null)) {
                continue;
            }
            foreach ($data[$listKey] as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $data[$listKey][$i] = self::normalizeDestinationRefs($row);
            }
        }

        if (is_array($data['services'] ?? null)) {
            foreach ($data['services'] as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (! isset($row['country_slug']) && isset($row['zone_slug'])) {
                    $row['country_slug'] = $row['zone_slug'];
                }
                $data['services'][$i] = $row;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected static function normalizeDestinationRefs(array $row): array
    {
        if (! isset($row['countrySlug']) && isset($row['zoneSlug'])) {
            $row['countrySlug'] = $row['zoneSlug'];
        }

        if (! isset($row['countrySlugs']) && isset($row['zoneSlugs']) && is_array($row['zoneSlugs'])) {
            $row['countrySlugs'] = $row['zoneSlugs'];
        }

        if (! isset($row['country']) && isset($row['zone'])) {
            $row['country'] = $row['zone'];
        }

        return $row;
    }

    /**
     * @param  array<string, true>  $used
     */
    protected static function synthesizeCountryCode(string $slug, array $used): string
    {
        $base = strtoupper(preg_replace('/[^a-z0-9]/i', '', $slug) ?? '');
        if ($base === '') {
            $base = 'ZN';
        }
        $base = substr($base, 0, 10);
        $code = $base;
        $n = 2;
        while (isset($used[$code])) {
            $suffix = (string) $n++;
            $code = substr($base, 0, max(1, 10 - strlen($suffix))).$suffix;
        }

        return $code;
    }
}
