<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Support\ProjectHostResolver;
use App\Services\Sitemap\SitemapGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class VerifySitemapCommand extends Command
{
    protected $signature = 'sitemap:verify
                            {--host= : Host giả lập (vd: vitravel.dev)}
                            {--project= : Kiểm tra project code cụ thể}';

    protected $description = 'Chẩn đoán sitemap: project resolve, đường dẫn file, route';

    public function handle(SitemapGenerator $generator): int
    {
        $diskName = (string) config('sitemap.disk', 'local');
        $disk = Storage::disk($diskName);

        $this->info('Disk: '.$diskName);
        try {
            $root = $disk->path('');
            $this->line('Root: '.$root);
            $writable = is_writable($root) || (is_dir($root) && is_writable($root));
            if (! is_dir($root)) {
                $this->warn('  Thư mục chưa tồn tại — sẽ tạo khi generate.');
            } elseif (! $writable) {
                $this->error('  Không ghi được! chown/chmod storage/app/sitemaps');
            }
        } catch (\Throwable $e) {
            $this->line('Root: (cloud disk)');
        }

        $host = $this->option('host') ? (string) $this->option('host') : null;
        if ($host) {
            $project = ProjectHostResolver::resolve($host);
            $this->newLine();
            $this->info("Host: {$host}");
            if ($project) {
                $this->line("→ Project #{$project->id} code={$project->code} primary={$project->primary_domain}");
                $this->checkProject($generator, $disk, $project);
            } else {
                $this->error('→ Không resolve được project từ host.');
            }
        }

        $code = $this->option('project') ? trim((string) $this->option('project')) : null;
        $query = Project::query()->active()->orderBy('id');
        if ($code) {
            $query->where('code', $code);
        }

        $this->newLine();
        $this->info('Projects:');
        foreach ($query->get() as $project) {
            $domains = $project->domains()->pluck('domain')->implode(', ');
            $canonical = $generator->resolveBaseUrl($project);
            $this->line("  {$project->code} (#{$project->id}) primary={$project->primary_domain} domains=[{$domains}]");
            $this->line("    canonical base URL → {$canonical}");
            if (! app()->environment('local') && str_contains($canonical, '.dev')) {
                $this->warn('    ⚠ Production đang dùng domain .dev — thêm domain .net/.com vào project_domains hoặc set SITEMAP_CANONICAL_BASE_URL');
            }
            $this->checkProject($generator, $disk, $project);
        }

        $this->newLine();
        $this->comment('Nếu route OK nhưng browser 404 → nginx/apache chưa pass /sitemap/* vào index.php.');
        $this->comment('Nginx: location ~ ^/sitemap/ { try_files $uri /index.php?$query_string; }');
        $this->comment('Lưu ý www/non-www: regenerate với --base-url=https://domain-khong-www');

        $this->newLine();
        $this->info('Routes (sitemap):');
        foreach (['sitemap', 'robots', 'sitemap.language', 'sitemap.language.file'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->line('  '.($route ? $route->uri() : $name.' (MISSING)'));
        }

        return self::SUCCESS;
    }

    private function checkProject(SitemapGenerator $generator, $disk, Project $project): void
    {
        $rel = $generator->projectRoot($project).'/sitemap.xml';
        $exists = $disk->exists($rel);
        $status = $exists ? '<fg=green>OK</>' : '<fg=red>MISSING</>';
        $this->line("  [{$status}] {$project->code} → {$rel}");
        if ($exists) {
            $size = $disk->size($rel);
            $this->line("       size={$size} bytes");
        } else {
            $this->line('       fix: php artisan sitemap:generate --project='.$project->code);
        }
    }
}
