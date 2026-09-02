<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceVariant extends Model
{
    use BelongsToProject, HasTranslations;

    public const SOURCE_CUSTOM = 'custom';

    public const SOURCE_CABIN = 'cabin';

    public const SOURCE_SERVICE_OPTION = 'service_option';

    /** Khớp migration `price_variants.code` string(64). */
    public const CODE_MAX_LENGTH = 64;

    /**
     * Chuẩn hoá code variant ≤ 64 ký tự, vẫn unique khi rút gọn.
     * Ưu tiên giữ slug gốc; nếu dài → prefix + hash ngắn.
     */
    public static function normalizeCode(string $code, ?int $sourceId = null): string
    {
        $code = trim($code);
        if ($code === '') {
            $code = $sourceId ? 'opt-'.$sourceId : 'standard';
        }

        if (strlen($code) <= self::CODE_MAX_LENGTH) {
            return $code;
        }

        $suffix = $sourceId
            ? '-'.$sourceId
            : '-'.substr(md5($code), 0, 8);
        $keep = self::CODE_MAX_LENGTH - strlen($suffix);
        if ($keep < 8) {
            return substr(md5($code.$suffix), 0, self::CODE_MAX_LENGTH);
        }

        return rtrim(substr($code, 0, $keep), '-').$suffix;
    }

    /** @var list<string> */
    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'project_id', 'price_table_id', 'code', 'source', 'source_id', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'source_id' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return PriceVariantTranslation::class;
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class, 'price_table_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(PriceRate::class, 'variant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
