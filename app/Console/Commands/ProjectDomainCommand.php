<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectDomain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Quản lý domain của một project (multi-domain: local + production).
 *
 * Ví dụ:
 *   php artisan project:domain hicatba --list
 *   php artisan project:domain hicatba --add=hicatba.dev --primary=hicatba.dev
 *   php artisan project:domain hicatba --add=hicatba.com
 *   php artisan project:domain hicatba --add=www.hicatba.com
 *   php artisan project:domain hicatba --primary=hicatba.com
 *   php artisan project:domain hicatba hicatba.com --set-primary   (shorthand)
 *   php artisan project:domain hicatba --remove=hicatba.dev
 */
class ProjectDomainCommand extends Command
{
    protected $signature = 'project:domain
        {code : Mã project (vd: hicatba)}
        {domain? : Domain (shorthand: tự --add; kèm --set-primary)}
        {--list : Liệt kê domain hiện có}
        {--add=* : Thêm một hoặc nhiều domain}
        {--remove=* : Xóa domain}
        {--set-primary : Đặt {domain} argument làm primary}
        {--primary= : Đặt domain này làm primary_domain}';

    protected $description = 'Thêm / xóa / liệt kê domain của project (local + production cùng lúc)';

    public function handle(): int
    {
        if (! Schema::hasTable('projects') || ! Schema::hasTable('project_domains')) {
            $this->error('Thiếu bảng projects / project_domains. Chạy migrate trước.');

            return self::FAILURE;
        }

        $code = trim((string) $this->argument('code'));
        $project = Project::query()->where('code', $code)->first();
        if (! $project) {
            $this->error("Không tìm thấy project code={$code}");

            return self::FAILURE;
        }

        $adds = array_values(array_filter(array_map(
            fn ($d) => $this->normalizeDomain((string) $d),
            (array) $this->option('add')
        )));
        $domainArg = $this->argument('domain');
        if (is_string($domainArg) && trim($domainArg) !== '') {
            $adds[] = $this->normalizeDomain($domainArg);
            $adds = array_values(array_unique($adds));
        }
        $removes = array_values(array_filter(array_map(
            fn ($d) => $this->normalizeDomain((string) $d),
            (array) $this->option('remove')
        )));
        $primaryOpt = $this->option('primary');
        $primary = null;
        if (is_string($primaryOpt) && trim($primaryOpt) !== '') {
            $primary = $this->normalizeDomain($primaryOpt);
        } elseif ($this->option('set-primary') && is_string($domainArg) && trim($domainArg) !== '') {
            $primary = $this->normalizeDomain($domainArg);
        }

        $didMutate = false;

        foreach ($removes as $domain) {
            $n = ProjectDomain::query()
                ->where('project_id', $project->id)
                ->where('domain', $domain)
                ->delete();
            if ($n > 0) {
                $this->warn("Đã xóa domain: {$domain}");
                $didMutate = true;
                if ($project->primary_domain === $domain) {
                    $project->primary_domain = null;
                }
            } else {
                $this->line("Domain không thuộc project (bỏ qua xóa): {$domain}");
            }
        }

        foreach ($adds as $i => $domain) {
            // Domain unique toàn hệ thống — nếu thuộc project khác thì báo lỗi
            $existing = ProjectDomain::query()->where('domain', $domain)->first();
            if ($existing && (int) $existing->project_id !== (int) $project->id) {
                $other = Project::query()->find($existing->project_id);
                $this->error("Domain {$domain} đang gắn project ".($other?->code ?? '#'.$existing->project_id));

                return self::FAILURE;
            }

            $isPrimary = ($primary !== null && $domain === $primary)
                || ($primary === null && $i === 0 && ! $project->primary_domain && $adds !== []);

            ProjectDomain::query()->updateOrCreate(
                ['domain' => $domain],
                [
                    'project_id' => $project->id,
                    'is_primary' => $isPrimary,
                ]
            );
            $this->info("Đã thêm/cập nhật: {$domain}".($isPrimary ? ' (primary)' : ''));
            $didMutate = true;

            if ($isPrimary) {
                $project->primary_domain = $domain;
            }
        }

        if ($primary !== null) {
            $row = ProjectDomain::query()
                ->where('project_id', $project->id)
                ->where('domain', $primary)
                ->first();
            if (! $row) {
                $this->error("Không thể set primary: domain {$primary} chưa thuộc project. Dùng --add={$primary} --primary={$primary}");

                return self::FAILURE;
            }
            ProjectDomain::query()->where('project_id', $project->id)->update(['is_primary' => false]);
            $row->is_primary = true;
            $row->save();
            $project->primary_domain = $primary;
            $didMutate = true;
            $this->info("Primary domain → {$primary}");
        }

        if ($didMutate) {
            if (! $project->primary_domain) {
                $first = ProjectDomain::query()->where('project_id', $project->id)->orderByDesc('is_primary')->orderBy('id')->first();
                if ($first) {
                    $project->primary_domain = $first->domain;
                    $first->is_primary = true;
                    $first->save();
                }
            }
            $project->save();
        }

        if ($this->option('list') || (! $adds && ! $removes && $primary === null)) {
            $this->listDomains($project->fresh(['domains']));
        }

        return self::SUCCESS;
    }

    private function normalizeDomain(string $raw): string
    {
        $d = strtolower(trim($raw));
        $d = preg_replace('#^https?://#', '', $d) ?: $d;
        $d = preg_replace('/:\d+$/', '', $d) ?: $d;
        $d = rtrim($d, '/');

        return $d;
    }

    private function listDomains(Project $project): void
    {
        $this->line("Project #{$project->id} {$project->code} — primary: ".($project->primary_domain ?: '(none)'));
        $rows = $project->domains->map(fn (ProjectDomain $d) => [
            $d->domain,
            $d->is_primary ? 'yes' : '',
        ])->all();
        if ($rows === []) {
            $this->warn('Chưa có domain nào.');

            return;
        }
        $this->table(['domain', 'primary'], $rows);
    }
}
