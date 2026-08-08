<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectDomain;
use App\Support\ProjectSeed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ProjectsEnsureCommand extends Command
{
    protected $signature = 'project:ensure {profile?} {--domain=*} {--name=}';

    protected $description = 'Create or update a Project (+ domains from seed meta / --domain=… / APP_URL)';

    public function handle(): int
    {
        if (! Schema::hasTable('projects')) {
            $this->error('Table projects chưa có. Chạy migrate trước.');

            return self::FAILURE;
        }

        $profileArg = $this->argument('profile');
        if (is_string($profileArg) && trim($profileArg) !== '') {
            ProjectSeed::useProfile(trim($profileArg));
        }

        try {
            $profile = ProjectSeed::profile();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Ví dụ: php artisan project:ensure hicatba --domain=hicatba.dev --domain=hicatba.com');

            return self::FAILURE;
        }

        $code = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($profile)) ?: 'vitravel';
        $code = trim($code, '-') ?: 'vitravel';

        $meta = ProjectSeed::meta();
        $name = (string) ($this->option('name') ?: ($meta['brand'] ?? $code));

        $domains = $this->resolveDomains($meta);
        $primaryDomain = $domains[0] ?? null;

        $project = Project::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'seed_profile' => $profile,
                'is_active' => true,
                'primary_domain' => $primaryDomain,
                'media_prefix' => 'projects/'.$code,
                'config' => array_filter([
                    'seed' => $profile,
                    'brand' => $name,
                ]),
            ]
        );

        if (Schema::hasTable('project_domains')) {
            foreach ($domains as $i => $domain) {
                ProjectDomain::query()->updateOrCreate(
                    ['domain' => $domain],
                    [
                        'project_id' => $project->id,
                        'is_primary' => $i === 0 || $domain === $primaryDomain,
                    ]
                );
                $this->info("Domain: {$domain}");
            }
        }

        $this->info("OK — project #{$project->id} code={$project->code} name={$project->name}");

        return self::SUCCESS;
    }

    /**
     * Domains: --domain=* (có thể lặp) → meta.domains / primary_domain → APP_URL host nếu còn trống.
     *
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    private function resolveDomains(array $meta): array
    {
        $domains = [];

        foreach ((array) $this->option('domain') as $domainOpt) {
            if (! is_string($domainOpt) || trim($domainOpt) === '') {
                continue;
            }
            $d = strtolower(preg_replace('/:\d+$/', '', trim($domainOpt)) ?: '');
            $d = preg_replace('#^https?://#', '', $d) ?: $d;
            $d = rtrim($d, '/');
            if ($d !== '') {
                $domains[] = $d;
            }
        }

        if (filled($meta['primary_domain'] ?? null)) {
            $d = strtolower(preg_replace('/:\d+$/', '', (string) $meta['primary_domain']) ?: '');
            $d = preg_replace('#^https?://#', '', $d) ?: $d;
            $d = rtrim($d, '/');
            if ($d !== '') {
                $domains[] = $d;
            }
        }

        if (is_array($meta['domains'] ?? null)) {
            foreach ($meta['domains'] as $raw) {
                $d = strtolower(trim((string) $raw));
                $d = preg_replace('#^https?://#', '', $d) ?: $d;
                $d = preg_replace('/:\d+$/', '', $d) ?: $d;
                $d = rtrim($d, '/');
                if ($d !== '') {
                    $domains[] = $d;
                }
            }
        }

        $domains = array_values(array_unique($domains));

        // APP_URL host chỉ khi meta (+ --domain) trống — gắn cho project đang ensure
        if ($domains === []) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (is_string($appHost) && $appHost !== '') {
                $domains[] = strtolower($appHost);
            }
        }

        return $domains;
    }
}
