<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedService extends Model
{
    use BelongsToProject;

    /** @var list<string> */
    public const TRANSPORT_CLUSTERS = [
        Service::CLUSTER_FERRY,
        Service::CLUSTER_TRAIN,
    ];

    /** @var list<string> */
    public const SUPPORT_CLUSTERS = [
        Service::CLUSTER_STAY,
        Service::CLUSTER_EXPERIENCE,
        Service::CLUSTER_OTHER,
    ];

    protected $fillable = [
        'project_id',
        'service_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @param  list<string>  $clusters
     * @return array<int, string>
     */
    public static function serviceOptions(array $clusters, ?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $langId = Language::idByCode($locale);

        if ($clusters === []) {
            return [];
        }

        return Service::query()
            ->whereIn('cluster', $clusters)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->with(['translations', 'category'])
            ->get()
            ->mapWithKeys(function (Service $service) use ($langId) {
                $title = $service->translations->firstWhere('language_id', $langId)?->title
                    ?? $service->translations->first()?->title
                    ?? $service->code;
                $cluster = $service->cluster;
                $status = $service->status !== 'published' ? ' · nháp' : '';
                $label = trim(($service->code ?: '#'.$service->id).' — '.$title.' ['.$cluster.']'.$status);

                return [$service->id => $label];
            })
            ->all();
    }
}
