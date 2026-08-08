<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourCategory extends Model
{
    use BelongsToProject, HasFaqs, HasMediaAttachments, HasSeo, HasTranslations, SoftDeletes;

    public const TYPE_DURATION = 'duration';

    public const TYPE_REGION = 'region';

    public const TYPE_THEME = 'theme';

    public const TYPE_DAY_TRIP = 'day-trip';

    public const TYPE_PACKAGE = 'package';

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_DURATION => 'Theo thời lượng',
            self::TYPE_REGION => 'Theo vùng',
            self::TYPE_THEME => 'Theo chủ đề',
            self::TYPE_DAY_TRIP => 'Tour trong ngày',
            self::TYPE_PACKAGE => 'Gói combo',
        ];
    }

    /** @var list<string> */
    protected array $translatable = ['name', 'slug', 'description', 'seo_intro'];

    protected $fillable = ['project_id', 'country_id', 'type', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return TourCategoryTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_tour_category');
    }
}
