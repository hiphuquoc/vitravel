<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\Purge\LegacySoftDeletePurgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PurgeLegacySoftDeletesCommand extends Command
{
    protected $signature = 'purge:legacy-soft-deletes
                            {--dry-run : Chỉ đếm row legacy (deleted_at), không xóa}
                            {--force : Không hỏi xác nhận}
                            {--table= : Chỉ xử lý một bảng (vd: services, packages)}
                            {--project= : Giới hạn project_id (multi-tenant)}
                            {--batch=50 : Số bản ghi mỗi lần xử lý}
                            {--skip-seo-cleanup : Không dọn SEO entry mồ côi sau purge}';

    protected $description = 'Xóa cứng row legacy còn deleted_at (trước migration drop soft-delete columns)';

    public function handle(LegacySoftDeletePurgeService $service): int
    {
        if (! Schema::hasTable('migrations')) {
            $this->error('Chưa có DB. Chạy: php artisan migrate');

            return self::FAILURE;
        }

        $hasLegacyColumn = false;
        foreach (LegacySoftDeletePurgeService::tablesWithSoftDeleteColumn() as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                $hasLegacyColumn = true;
                break;
            }
        }

        if (! $hasLegacyColumn) {
            $this->info('Không còn cột deleted_at — có thể đã chạy migration drop soft-delete.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $onlyTable = $this->option('table') ? (string) $this->option('table') : null;
        $batch = max(1, (int) $this->option('batch'));
        $projectId = $this->option('project') !== null ? (int) $this->option('project') : null;

        if ($onlyTable !== null && $onlyTable !== '') {
            $known = LegacySoftDeletePurgeService::tablesWithSoftDeleteColumn();
            if (! in_array($onlyTable, $known, true)) {
                $this->error("Bảng không hỗ trợ: {$onlyTable}");
                $this->line('Các bảng: '.implode(', ', $known));

                return self::FAILURE;
            }
        }

        if ($projectId !== null && ! Project::query()->whereKey($projectId)->exists()) {
            $this->error("Không tìm thấy project_id={$projectId}.");

            return self::FAILURE;
        }

        $scan = $service->scan($projectId, $onlyTable);
        if ($scan === []) {
            $this->info('Không có row legacy nào (deleted_at IS NOT NULL).');
            $this->line('Bước tiếp: php artisan migrate (drop cột deleted_at).');

            return self::SUCCESS;
        }

        $this->table(['Bảng', 'Mô tả', 'Số row legacy'], array_map(
            fn (array $row) => [$row['table'], $row['label'], $row['count']],
            $scan,
        ));

        $total = array_sum(array_column($scan, 'count'));
        $this->line("Tổng: {$total} row legacy.");

        if ($dryRun) {
            $this->warn('Chế độ dry-run — không xóa gì.');
            $this->line('Chạy lại không có --dry-run để purge, sau đó: php artisan migrate');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Xóa cứng toàn bộ row legacy ở trên?', false)) {
            $this->warn('Đã hủy.');

            return self::SUCCESS;
        }

        $this->info('Đang purge (EntityPurgeService — relations, SEO, media GCS)...');

        $result = $service->purge(
            dryRun: false,
            projectId: $projectId,
            onlyTable: $onlyTable,
            batch: $batch,
            cleanupSeo: ! (bool) $this->option('skip-seo-cleanup'),
        );

        $this->info("Đã purge: {$result['purged']} | Bỏ qua: {$result['skipped']} | Lỗi: {$result['failed']}");

        foreach ($result['messages'] as $message) {
            $this->line('  • '.$message);
        }

        if ($result['failed'] > 0) {
            $this->warn('Còn lỗi — kiểm tra log rồi chạy lại command trước khi migrate.');

            return self::FAILURE;
        }

        $remaining = $service->scan($projectId, $onlyTable);
        if ($remaining !== []) {
            $left = array_sum(array_column($remaining, 'count'));
            $this->warn("Vẫn còn {$left} row legacy (có thể bị skip do ràng buộc). Xem lại trước khi migrate.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Purge hoàn tất. Chạy migration drop cột deleted_at:');
        $this->line('  php artisan migrate');

        return self::SUCCESS;
    }
}
