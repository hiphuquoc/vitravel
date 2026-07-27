<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
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

    public static function idByCode(string $code): ?int
    {
        return Cache::remember("language:id:{$code}", 3600, function () use ($code) {
            return static::query()->where('code', $code)->value('id');
        });
    }

    public static function byCode(string $code): ?self
    {
        return static::query()->where('code', $code)->where('is_active', true)->first();
    }

    public static function defaultId(): ?int
    {
        return Cache::remember('language:default_id', 3600, function () {
            return static::query()->where('is_default', true)->value('id')
                ?? static::query()->where('code', 'vi')->value('id');
        });
    }

    public static function defaultCode(): string
    {
        return Cache::remember('language:default_code', 3600, function () {
            return static::query()->where('is_default', true)->value('code')
                ?? static::query()->where('code', 'vi')->value('code')
                ?? 'vi';
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('language:default_id');
        Cache::forget('language:default_code');
        static::query()->pluck('code')->each(fn ($code) => Cache::forget("language:id:{$code}"));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    /** Collection các ngôn ngữ đang active. */
    public static function active(): \Illuminate\Support\Collection
    {
        return static::query()->active()->get();
    }
}
