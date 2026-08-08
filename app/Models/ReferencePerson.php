<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferencePerson extends Model
{
    use BelongsToProject, SoftDeletes;

    protected $table = 'reference_persons';

    protected $fillable = [
        'project_id', 'country_id', 'photo_media_id', 'name', 'email', 'phone', 'skype', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    public function photoUrl(?string $variant = 'thumb'): ?string
    {
        return app(MediaService::class)->publicUrl($this->photo, $variant);
    }

    public function photoSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->photo);
    }
}
