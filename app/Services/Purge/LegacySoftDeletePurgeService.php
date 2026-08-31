<?php

declare(strict_types=1);

namespace App\Services\Purge;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\ContentTypeTag;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Destination;
use App\Models\ExperienceAlbum;
use App\Models\ExperienceVideo;
use App\Models\HomeSlide;
use App\Models\KeywordTag;
use App\Models\Media;
use App\Models\Office;
use App\Models\Package;
use App\Models\ReferencePerson;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaticPage;
use App\Models\TeamMember;
use App\Models\TourCategory;
use App\Models\TravelStyle;
use App\Models\Project;
use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;
use App\Support\ProjectContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Dọn row legacy còn deleted_at (soft-delete cũ) trước khi migration drop cột.
 */
final class LegacySoftDeletePurgeService
{
    /**
     * Thứ tự xóa: entity con / nặng trước, taxonomy & geography sau.
     *
     * @var list<array{table: string, model: class-string<Model>, label: string}>
     */
    private const TABLE_SPECS = [
        ['table' => 'services', 'model' => Service::class, 'label' => 'Dịch vụ'],
        ['table' => 'packages', 'model' => Package::class, 'label' => 'Tour / du thuyền'],
        ['table' => 'articles', 'model' => Article::class, 'label' => 'Bài viết'],
        ['table' => 'reviews', 'model' => Review::class, 'label' => 'Review'],
        ['table' => 'team_members', 'model' => TeamMember::class, 'label' => 'Thành viên'],
        ['table' => 'experience_albums', 'model' => ExperienceAlbum::class, 'label' => 'Album trải nghiệm'],
        ['table' => 'experience_videos', 'model' => ExperienceVideo::class, 'label' => 'Video trải nghiệm'],
        ['table' => 'home_slides', 'model' => HomeSlide::class, 'label' => 'Slide trang chủ'],
        ['table' => 'static_pages', 'model' => StaticPage::class, 'label' => 'Trang tĩnh'],
        ['table' => 'destinations', 'model' => Destination::class, 'label' => 'Điểm đến'],
        ['table' => 'blog_categories', 'model' => BlogCategory::class, 'label' => 'Danh mục blog'],
        ['table' => 'tour_categories', 'model' => TourCategory::class, 'label' => 'Danh mục tour'],
        ['table' => 'service_categories', 'model' => ServiceCategory::class, 'label' => 'Danh mục dịch vụ'],
        ['table' => 'cruise_types', 'model' => CruiseType::class, 'label' => 'Loại du thuyền'],
        ['table' => 'reference_persons', 'model' => ReferencePerson::class, 'label' => 'Người liên hệ'],
        ['table' => 'offices', 'model' => Office::class, 'label' => 'Văn phòng'],
        ['table' => 'travel_styles', 'model' => TravelStyle::class, 'label' => 'Phong cách du lịch'],
        ['table' => 'content_type_tags', 'model' => ContentTypeTag::class, 'label' => 'Tag loại nội dung'],
        ['table' => 'keyword_tags', 'model' => KeywordTag::class, 'label' => 'Tag từ khóa'],
        ['table' => 'countries', 'model' => Country::class, 'label' => 'Quốc gia'],
        ['table' => 'media', 'model' => Media::class, 'label' => 'Media'],
    ];

    public function __construct(private readonly EntityPurgeService $purger) {}

    /**
     * @return list<array{table: string, label: string, count: int}>
     */
    public function scan(?int $projectId = null, ?string $onlyTable = null): array
    {
        $rows = [];

        foreach ($this->specs($onlyTable) as $spec) {
            if (! $this->tableReady($spec['table'])) {
                continue;
            }

            $count = $this->legacyQuery($spec['table'], $projectId)->count();
            if ($count > 0) {
                $rows[] = [
                    'table' => $spec['table'],
                    'label' => $spec['label'],
                    'count' => $count,
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array{purged: int, skipped: int, failed: int, messages: list<string>}
     */
    public function purge(
        bool $dryRun = false,
        ?int $projectId = null,
        ?string $onlyTable = null,
        int $batch = 50,
        bool $cleanupSeo = true,
    ): array {
        $purged = 0;
        $skipped = 0;
        $failed = 0;
        $messages = [];

        $runner = function () use (
            $dryRun,
            $projectId,
            $onlyTable,
            $batch,
            &$purged,
            &$skipped,
            &$failed,
            &$messages,
        ): void {
            foreach ($this->specs($onlyTable) as $spec) {
                if (! $this->tableReady($spec['table'])) {
                    continue;
                }

                /** @var class-string<Model> $modelClass */
                $modelClass = $spec['model'];

                $this->legacyModelQuery($modelClass, $spec['table'], $projectId)
                    ->orderBy('id')
                    ->chunkById($batch, function ($models) use (
                        $dryRun,
                        $spec,
                        &$purged,
                        &$skipped,
                        &$failed,
                        &$messages,
                    ): void {
                        foreach ($models as $model) {
                            $label = $spec['label'].' #'.$model->getKey();

                            if ($dryRun) {
                                $purged++;

                                continue;
                            }

                            try {
                                $this->purger->purge($model);
                                $purged++;
                            } catch (ValidationException $e) {
                                $skipped++;
                                $reason = collect($e->errors())->flatten()->first() ?: $e->getMessage();
                                $messages[] = "Bỏ qua {$label}: {$reason}";
                            } catch (\Throwable $e) {
                                $failed++;
                                $messages[] = "Lỗi {$label}: ".$e->getMessage();
                            }
                        }
                    });
            }
        };

        if ($projectId !== null) {
            $project = Project::query()->find($projectId);
            if (! $project) {
                throw new \InvalidArgumentException("Không tìm thấy project_id={$projectId}.");
            }

            ProjectContext::run($project, $runner);
        } else {
            $runner();
        }

        if (! $dryRun && $cleanupSeo) {
            $orphanSeo = $this->purgeOrphanSeoEntries();
            if ($orphanSeo > 0) {
                $messages[] = "Đã xóa {$orphanSeo} SEO entry mồ côi.";
            }
        }

        return compact('purged', 'skipped', 'failed', 'messages');
    }

    /**
     * @return list<array{table: string, model: class-string<Model>, label: string}>
     */
    private function specs(?string $onlyTable): array
    {
        if ($onlyTable === null || $onlyTable === '') {
            return self::TABLE_SPECS;
        }

        $onlyTable = trim($onlyTable);

        return array_values(array_filter(
            self::TABLE_SPECS,
            fn (array $spec) => $spec['table'] === $onlyTable,
        ));
    }

    private function tableReady(string $table): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at');
    }

    private function legacyQuery(string $table, ?int $projectId): \Illuminate\Database\Query\Builder
    {
        $query = DB::table($table)->whereNotNull('deleted_at');

        if ($projectId !== null && Schema::hasColumn($table, 'project_id')) {
            $query->where('project_id', $projectId);
        }

        return $query;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function legacyModelQuery(string $modelClass, string $table, ?int $projectId): \Illuminate\Database\Eloquent\Builder
    {
        $query = $modelClass::withoutGlobalScopes()
            ->whereNotNull('deleted_at');

        if ($projectId !== null && Schema::hasColumn($table, 'project_id')) {
            $query->where('project_id', $projectId);
        }

        return $query;
    }

    public function purgeOrphanSeoEntries(): int
    {
        if (! Schema::hasTable('seo_entries')) {
            return 0;
        }

        $deleted = 0;

        SeoEntry::withoutGlobalScopes()
            ->whereNotNull('reference_type')
            ->whereNotNull('reference_id')
            ->orderBy('id')
            ->chunkById(100, function ($entries) use (&$deleted): void {
                foreach ($entries as $entry) {
                    $ref = $entry->reference()->withoutGlobalScopes()->first();
                    if ($ref !== null) {
                        continue;
                    }

                    SeoEntryTranslation::withoutGlobalScopes()
                        ->where('seo_entry_id', $entry->id)
                        ->delete();
                    $entry->delete();
                    $deleted++;
                }
            });

        return $deleted;
    }

    /**
     * @return list<string>
     */
    public static function tablesWithSoftDeleteColumn(): array
    {
        return array_column(self::TABLE_SPECS, 'table');
    }
}
