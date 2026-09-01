<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    use BelongsToProject;

    public const ZONE_MAIN = 'main';

    public const ZONE_MORE = 'more';

    public const ZONE_CTA = 'cta';

    public const KIND_TOURS_MENU = 'tours_menu';

    public const KIND_CRUISE_MENU = 'cruise_menu';

    public const KIND_SERVICE_CLUSTER = 'service_cluster';

    public const KIND_ROUTE_LINK = 'route_link';

    public const KIND_HEADING = 'heading';

    public const KIND_BLOG_MENU = 'blog_menu';

    public const KIND_CTA_LINK = 'cta_link';

    protected $fillable = [
        'project_id',
        'zone',
        'kind',
        'item_key',
        'reference',
        'config',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(NavigationItemTranslation::class);
    }

    public function translation(?string $locale = null): ?NavigationItemTranslation
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return \App\Support\LocaleContent::firstTranslation($this->translations, $locale);
        }

        $ids = Language::contentLanguageIdChain($locale);
        if ($ids === []) {
            return null;
        }

        $rows = $this->translations()->whereIn('language_id', $ids)->get();

        return \App\Support\LocaleContent::firstTranslation($rows, $locale);
    }

    /** @return array<string, string> */
    public static function kindLabels(): array
    {
        return [
            self::KIND_TOURS_MENU => 'Menu Tour (dropdown quốc gia)',
            self::KIND_CRUISE_MENU => 'Menu Du thuyền / Trải nghiệm',
            self::KIND_SERVICE_CLUSTER => 'Cụm dịch vụ (hub + danh mục)',
            self::KIND_ROUTE_LINK => 'Liên kết trang',
            self::KIND_HEADING => 'Tiêu đề nhóm (menu ⋯)',
            self::KIND_BLOG_MENU => 'Nhóm Blog (chuyên mục động)',
            self::KIND_CTA_LINK => 'Nút CTA header',
        ];
    }

    public function kindLabel(): string
    {
        return self::kindLabels()[$this->kind] ?? $this->kind;
    }

    public function showInMainBar(): bool
    {
        if ($this->kind !== self::KIND_SERVICE_CLUSTER) {
            return true;
        }

        return ($this->config['show_in_main_bar'] ?? true) !== false;
    }
}
