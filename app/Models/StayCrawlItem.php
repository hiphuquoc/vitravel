<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayCrawlItem extends Model
{
    use BelongsToProject;

    public const STATUS_QUEUED = 'queued';

    public const STATUS_FETCHED = 'fetched';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_EXTRACTED = 'extracted';

    public const STATUS_AI_DONE = 'ai_done';

    public const STATUS_IMPORTED = 'imported';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'project_id', 'job_id', 'source_url', 'canonical_url', 'list_url',
        'status', 'http_status', 'blocked_reason', 'extractor_version',
        'raw_html', 'extracted_html', 'raw_json', 'ai_json', 'service_id',
        'error', 'crawled_at', 'ai_at', 'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'extractor_version' => 'integer',
            'raw_json' => 'array',
            'ai_json' => 'array',
            'crawled_at' => 'datetime',
            'ai_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(StayCrawlJob::class, 'job_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
