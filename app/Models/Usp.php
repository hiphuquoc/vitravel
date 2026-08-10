<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Usp extends Model
{
    use BelongsToProject, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['title', 'description'];

    public static function iconOptions(): array
    {
        return [
            'expert' => 'Chuyên gia (expert)',
            'refund' => 'Hoàn tiền (refund)',
            'value' => 'Giá trị (value)',
            'support' => 'Hỗ trợ (support)',
            'shield' => 'Bảo vệ (shield)',
            'compass' => 'La bàn (compass)',
            'sparkles' => 'Nổi bật (sparkles)',
            'boat' => 'Tàu thuyền (boat)',
        ];
    }

    protected $fillable = ['project_id', 'icon', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return UspTranslation::class;
    }
}
