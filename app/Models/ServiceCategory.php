<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasSeo;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ServiceCategory extends Model
{
    use BelongsToProject, HasSeo;

    protected $fillable = [
        'project_id', 'cluster', 'slug', 'name', 'intro', 'seo_body', 'banner_media_id', 'cover_media_id', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function bannerUrl(?string $variant = 'card'): ?string
    {
        return app(MediaService::class)->publicUrl($this->banner, $variant);
    }

    public function coverUrl(?string $variant = 'card'): ?string
    {
        return app(MediaService::class)->publicUrl($this->cover, $variant);
    }

    public function bannerSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->banner);
    }

    public function coverSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->cover);
    }

    /** @return BelongsToMany<Service> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_category_service')
            ->withPivot(['sort'])
            ->orderByPivot('sort');
    }

    public function directServices(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    public function scopeForCluster(Builder $query, string $cluster): Builder
    {
        return $query->where('cluster', $cluster);
    }

    public function hubKey(): ?string
    {
        return config("services_catalog.clusters.{$this->cluster}.hub_key");
    }
}
