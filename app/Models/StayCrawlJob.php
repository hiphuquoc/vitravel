<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StayCrawlJob extends Model
{
    use BelongsToProject;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CRAWLING = 'crawling';

    public const STATUS_READY = 'ready';

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'project_id', 'source_id', 'list_url', 'canonical_url', 'service_category_id',
        'status', 'pages_crawled', 'items_found', 'error', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'pages_crawled' => 'integer',
            'items_found' => 'integer',
            'meta' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(StayCrawlSource::class, 'source_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StayCrawlItem::class, 'job_id');
    }
}
