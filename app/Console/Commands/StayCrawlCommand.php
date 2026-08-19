<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlFetcher;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use App\Support\StayBookingUrl;
use Illuminate\Console\Command;

/**
 * Pipeline crawler lưu trú Booking.com.
 *
 *   php artisan stay:crawl ingest {hotelUrl} --project=phuquoc --category=12
 *   php artisan stay:crawl list {searchUrl} --project=phuquoc
 *   php artisan stay:crawl detail --item=3 --file=/tmp/hotel.html
 *   php artisan stay:crawl ai --job=1
 *   php artisan stay:crawl import --job=1 --dry-run
 */
class StayCrawlCommand extends Command
{
    protected $signature = 'stay:crawl
        {action : list|detail|map|ai|import|ingest}
        {url? : URL list hoặc URL khách sạn Booking.com}
        {--project= : Mã project (bắt buộc trên CLI)}
        {--job= : ID stay_crawl_jobs}
        {--item= : ID stay_crawl_items}
        {--file= : File HTML đã lưu (khi fetch bị chặn)}
        {--category= : service_categories.id}
        {--locale=vi}
        {--limit=8 : Số item xử lý mỗi lần}
        {--max-pages=1}
        {--dry-run : Import không ghi DB}
        {--keep-html : Lưu raw_html}
        {--ignore-robots : Bỏ qua robots.txt (mặc định crawler Chrome cũng bỏ)}
        {--proxy : Fetch qua proxy (STAY_CRAWL_PROXY_*)}
        {--rerun= : improve|replace khi URL đã cào}
        {--instructions= : Hướng dẫn thêm cho AI}';

    protected $description = 'Crawler lưu trú Booking.com: list → detail → map HTML → draft service';

    public function handle(StayCrawlService $crawl, StayCrawlFetcher $fetcher): int
    {
        $action = strtolower((string) $this->argument('action'));
        if (! in_array($action, ['list', 'detail', 'map', 'ai', 'import', 'ingest'], true)) {
            $this->error('action: list | detail | map | ai | import | ingest');

            return self::FAILURE;
        }

        if (! $this->bindProject()) {
            return self::FAILURE;
        }

        $respectRobots = ! $this->option('ignore-robots');
        $useProxy = (bool) $this->option('proxy');
        $fileHtml = $this->loadFile($fetcher);

        return match ($action) {
            'list' => $this->runList($crawl, $fileHtml, $respectRobots, $useProxy),
            'detail' => $this->runDetail($crawl, $fileHtml, $respectRobots, $useProxy),
            'map' => $this->runMap($crawl),
            'ai' => $this->runAi($crawl),
            'import' => $this->runImport($crawl),
            'ingest' => $this->runIngest($crawl, $fileHtml, $respectRobots, $useProxy),
            default => self::FAILURE,
        };
    }

    private function runList(StayCrawlService $crawl, ?string $html, bool $respectRobots, bool $useProxy): int
    {
        $url = (string) $this->argument('url');
        $jobId = $this->option('job');
        if ($jobId) {
            $job = StayCrawlJob::query()->findOrFail((int) $jobId);
        } else {
            if ($url === '') {
                $this->error('Thiếu URL danh mục Booking.com.');

                return self::FAILURE;
            }
            $job = $crawl->enqueueList($url, $this->categoryId(), $useProxy);
        }
        $this->info("Job #{$job->id} — {$job->list_url}");
        $result = $crawl->crawlList($job, $html, $respectRobots, (int) $this->option('max-pages'), $useProxy);
        $this->info('Tìm thấy '.$result['job']->items_found.' URL chỗ nghỉ (đã lưu source_url).');
        foreach (array_slice($result['urls'], 0, 20) as $u) {
            $this->line('  '.$u);
        }

        return self::SUCCESS;
    }

    private function runDetail(StayCrawlService $crawl, ?string $html, bool $respectRobots, bool $useProxy): int
    {
        $items = $this->resolveItems($crawl, queuedOrRetry: true);
        if ($items === []) {
            $this->warn('Không có item queued/blocked để crawl detail.');

            return self::SUCCESS;
        }
        $keep = (bool) $this->option('keep-html') || $html !== null;
        foreach ($items as $item) {
            $this->line("Detail #{$item->id} {$item->source_url}");
            $updated = $crawl->crawlDetail($item, $html, $respectRobots, $keep, $useProxy);
            $this->info("  → {$updated->status}".($updated->blocked_reason ? " ({$updated->blocked_reason})" : ''));
            $html = null;
        }

        return self::SUCCESS;
    }

    private function runMap(StayCrawlService $crawl): int
    {
        $q = StayCrawlItem::query()->where('status', StayCrawlItem::STATUS_EXTRACTED);
        if ($id = $this->option('item')) {
            $q = StayCrawlItem::query()->whereKey((int) $id);
        } elseif ($job = $this->option('job')) {
            $q->where('job_id', (int) $job);
        }
        $items = $q->limit((int) $this->option('limit'))->get();
        if ($items->isEmpty()) {
            $this->warn('Không có item status=extracted.');

            return self::SUCCESS;
        }
        foreach ($items as $item) {
            $this->line("Map #{$item->id}");
            $crawl->mapProcess($item);
            $this->info('  → mapped');
        }

        return self::SUCCESS;
    }

    private function runAi(StayCrawlService $crawl): int
    {
        $q = StayCrawlItem::query()->where('status', StayCrawlItem::STATUS_EXTRACTED);
        if ($id = $this->option('item')) {
            $q = StayCrawlItem::query()->whereKey((int) $id);
        } elseif ($job = $this->option('job')) {
            $q->where('job_id', (int) $job);
        }
        $items = $q->limit((int) $this->option('limit'))->get();
        if ($items->isEmpty()) {
            $this->warn('Không có item status=extracted.');

            return self::SUCCESS;
        }
        foreach ($items as $item) {
            $this->line("AI #{$item->id}");
            $crawl->aiProcess($item, (string) $this->option('locale'), $this->option('instructions') ?: null);
            $this->info('  → ai_done');
        }

        return self::SUCCESS;
    }

    private function runImport(StayCrawlService $crawl): int
    {
        $q = StayCrawlItem::query()->where('status', StayCrawlItem::STATUS_AI_DONE);
        if ($id = $this->option('item')) {
            $q = StayCrawlItem::query()->whereKey((int) $id);
        } elseif ($job = $this->option('job')) {
            $q->where('job_id', (int) $job);
        }
        $items = $q->limit((int) $this->option('limit'))->get();
        if ($items->isEmpty()) {
            $this->warn('Không có item ai_done để import.');

            return self::SUCCESS;
        }
        $dry = (bool) $this->option('dry-run');
        foreach ($items as $item) {
            $service = $crawl->importItem($item, $this->categoryId(), (string) $this->option('locale'), $dry);
            $this->info(($dry ? '[dry-run] ' : '')."Service #{$service->id} {$service->code} ({$service->status})");
        }

        return self::SUCCESS;
    }

    private function runIngest(StayCrawlService $crawl, ?string $html, bool $respectRobots, bool $useProxy): int
    {
        $url = (string) $this->argument('url');
        if ($url === '' && ! $this->option('item')) {
            $this->error('ingest cần URL khách sạn hoặc --item=');

            return self::FAILURE;
        }
        $item = $this->option('item')
            ? StayCrawlItem::query()->findOrFail((int) $this->option('item'))
            : $crawl->queueHotelUrl($url);
        $rerun = (string) $this->option('rerun');
        $already = in_array($item->status, [
            StayCrawlItem::STATUS_IMPORTED,
            StayCrawlItem::STATUS_AI_DONE,
            StayCrawlItem::STATUS_EXTRACTED,
        ], true);
        if ($already && ! in_array($rerun, ['improve', 'replace'], true)) {
            $this->error('URL đã cào (item #'.$item->id.', '.$item->status.'). Dùng --rerun=improve hoặc --rerun=replace.');

            return self::FAILURE;
        }
        if (in_array($rerun, ['improve', 'replace'], true) && $already) {
            $crawl->resetItemForRerun($item, $rerun);
            $item->refresh();
        }
        $this->info("Ingest #{$item->id} {$item->source_url}");
        $item = $crawl->crawlDetail($item, $html, $respectRobots, true, $useProxy);
        if ($item->status === StayCrawlItem::STATUS_BLOCKED) {
            $this->error('Bị chặn: '.$item->error);
            $this->line('Lưu trang Booking (Save as HTML) rồi: php artisan stay:crawl ingest --item='.$item->id.' --file=hotel.html --project=…');

            return self::FAILURE;
        }
        if ($item->status === StayCrawlItem::STATUS_EXTRACTED) {
            $item = $crawl->mapProcess($item);
        }
        $service = $crawl->importItem($item, $this->categoryId(), (string) $this->option('locale'), (bool) $this->option('dry-run'));
        $this->info("Draft service #{$service->id} code={$service->code}");

        return self::SUCCESS;
    }

    /** @return list<StayCrawlItem> */
    private function resolveItems(StayCrawlService $crawl, bool $queuedOrRetry): array
    {
        if ($id = $this->option('item')) {
            return [StayCrawlItem::query()->findOrFail((int) $id)];
        }
        $url = (string) $this->argument('url');
        if ($url !== '' && StayBookingUrl::isHotelPage($url)) {
            return [$crawl->queueHotelUrl($url, $this->option('job') ? StayCrawlJob::query()->find((int) $this->option('job')) : null)];
        }
        $q = StayCrawlItem::query();
        if ($job = $this->option('job')) {
            $q->where('job_id', (int) $job);
        }
        if ($queuedOrRetry) {
            $q->whereIn('status', [
                StayCrawlItem::STATUS_QUEUED,
                StayCrawlItem::STATUS_BLOCKED,
                StayCrawlItem::STATUS_FAILED,
            ]);
        }

        return $q->limit((int) $this->option('limit'))->get()->all();
    }

    private function bindProject(): bool
    {
        $code = (string) $this->option('project');
        if ($code === '') {
            $this->error('Bắt buộc --project= (vd: phuquoc, hicatba).');

            return false;
        }
        $project = Project::query()->where('code', $code)->orWhere('seed_profile', $code)->first();
        if (! $project) {
            $this->error('Không tìm thấy project: '.$code);

            return false;
        }
        ProjectContext::set($project);
        $this->info('Project: '.$project->code.' #'.$project->id);

        return true;
    }

    private function categoryId(): ?int
    {
        $raw = $this->option('category');

        return $raw !== null && $raw !== '' ? (int) $raw : null;
    }

    private function loadFile(StayCrawlFetcher $fetcher): ?string
    {
        $path = (string) $this->option('file');
        if ($path === '') {
            return null;
        }

        return $fetcher->readFile($path);
    }
}
