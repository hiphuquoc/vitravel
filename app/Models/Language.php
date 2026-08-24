<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    protected static array $memoIds = [];
    protected static ?self $memoDefault = null;
    protected static ?int $memoDefaultId = null;
    protected static ?string $memoDefaultCode = null;
    protected static ?Collection $memoActive = null;
    protected static ?Collection $memoAll = null;
    protected $fillable = [
        'code', 'name', 'name_native', 'flag', 'og_locale', 'hreflang',
        'dir', 'is_active', 'is_default', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * Cache dạng array (Laravel 13: serializable_classes=false không cho serialize Model).
     */
    private static function rememberRows(string $key, callable $query): array
    {
        return Cache::remember($key, 86400, function () use ($query) {
            $result = $query();

            if ($result instanceof Collection) {
                return $result->map->toArray()->all();
            }

            return $result ? $result->toArray() : [];
        });
    }

    private static function hydrate(?array $row): ?self
    {
        if (empty($row)) {
            return null;
        }

        return (new static)->forceFill($row)->syncOriginal();
    }

    /** @return Collection<int, self> */
    private static function hydrateMany(array $rows): Collection
    {
        return (new Collection($rows))->map(fn (array $row) => self::hydrate($row));
    }

    public static function idByCode(string $code): ?int
    {
        if (array_key_exists($code, static::$memoIds)) {
            return static::$memoIds[$code];
        }

        return static::$memoIds[$code] = Cache::remember("language:id:{$code}", 3600, function () use ($code) {
            return static::query()->where('code', $code)->value('id');
        });
    }

    /** Hỗ trợ hyphen codes (zh-cn, zh-tw). */
    public static function byCode(?string $code): ?self
    {
        if (empty($code)) {
            return null;
        }

        return self::active()->firstWhere('code', $code)
            ?? static::query()->where('code', $code)->first();
    }

    public static function default(): ?self
    {
        if (static::$memoDefault !== null) {
            return static::$memoDefault;
        }

        $row = self::rememberRows('languages:default:v2', function () {
            return self::where('is_default', 1)->first()
                ?? self::where('code', config('language.default_code', 'vi'))->first();
        });

        return static::$memoDefault = self::hydrate($row ?: null);
    }

    public static function defaultId(): ?int
    {
        if (static::$memoDefaultId !== null) {
            return static::$memoDefaultId;
        }

        return static::$memoDefaultId = Cache::remember('language:default_id', 3600, function () {
            return static::default()?->id
                ?? static::query()->where('code', 'vi')->value('id');
        });
    }

    public static function defaultCode(): string
    {
        if (static::$memoDefaultCode !== null) {
            return static::$memoDefaultCode;
        }

        return static::$memoDefaultCode = Cache::remember('language:default_code', 3600, function () {
            return static::default()?->code
                ?? (string) config('language.default_code', 'vi');
        });
    }

    /** Ưu tiên lấy tạm nội dung EN khi locale chưa được seed. */
    public static function contentFallbackCode(): string
    {
        return (string) config('language.content_fallback_code', 'en');
    }

    /**
     * Options cho admin console language switcher.
     *
     * @return list<array{code: string, name: string, name_native: string, is_default: bool}>
     */
    public static function adminOptions(): array
    {
        return collect(config('language.list', []))
            ->filter(fn ($l) => is_array($l) && ($l['is_active'] ?? true))
            ->sortBy(fn ($l) => $l['sort'] ?? 99)
            ->values()
            ->map(fn (array $l) => [
                'code' => (string) ($l['code'] ?? ''),
                'name' => (string) ($l['name'] ?? ($l['name_native'] ?? '')),
                'name_native' => (string) ($l['name_native'] ?? ($l['name'] ?? '')),
                'is_default' => (bool) ($l['is_default'] ?? false),
            ])
            ->filter(fn (array $l) => $l['code'] !== '')
            ->values()
            ->all();
    }

    /**
     * Chuỗi locale để resolve nội dung/SEO: current → content fallback (en) → default (vi).
     *
     * @return list<string>
     */
    public static function contentLocaleChain(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $chain = [$locale, self::contentFallbackCode(), self::defaultCode()];

        return array_values(array_unique(array_filter($chain)));
    }

    /**
     * @return list<int>
     */
    public static function contentLanguageIdChain(?string $locale = null): array
    {
        $ids = [];
        foreach (self::contentLocaleChain($locale) as $code) {
            $id = self::idByCode($code);
            if ($id) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /** @return Collection<int, self> */
    public static function active(): Collection
    {
        if (static::$memoActive !== null) {
            return static::$memoActive;
        }

        return static::$memoActive = self::hydrateMany(
            self::rememberRows('languages:active:v2', fn () => self::where('is_active', 1)->orderBy('sort')->get())
        );
    }

    public static function listAll(): Collection
    {
        return self::hydrateMany(
            self::rememberRows('languages:all:v2', fn () => self::orderBy('sort')->get())
        );
    }

    public static function clearCache(): void
    {
        static::$memoIds = [];
        static::$memoDefault = null;
        static::$memoDefaultId = null;
        static::$memoDefaultCode = null;
        static::$memoActive = null;
        static::$memoAll = null;
        self::flushCache();
        Cache::forget('language:default_id');
        Cache::forget('language:default_code');
        try {
            static::query()->pluck('code')->each(fn ($code) => Cache::forget("language:id:{$code}"));
        } catch (\Throwable $e) {
            // DB may be unavailable during early boot
        }
    }

    public static function flushCache(): void
    {
        foreach ([
            'languages:active', 'languages:all', 'languages:default',
            'languages:active:v2', 'languages:all:v2', 'languages:default:v2',
        ] as $key) {
            Cache::forget($key);
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
