<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\User;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        $profile = ProjectSeed::profile();
        $meta = ProjectSeed::meta();
        $brand = (string) ($meta['brand'] ?? $profile);
        $code = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($profile)) ?: 'vitravel';
        $code = trim($code, '-') ?: 'vitravel';

        $primaryDomain = $this->resolvePrimaryDomain($meta);
        $domains = $this->resolveDomains($meta, $primaryDomain);

        $project = Project::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $brand,
                'seed_profile' => $profile,
                'is_active' => true,
                'primary_domain' => $primaryDomain,
                'media_prefix' => 'projects/'.$code,
                'config' => [
                    'seed' => $profile,
                    'brand' => $brand,
                ],
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
            }
        }

        ProjectContext::set($project);
        $this->command?->info("Project context: {$project->code} (#{$project->id})");
    }

    /**
     * Attach admin user to the active project (called after User seed).
     */
    public static function attachAdmin(?User $user = null): void
    {
        if (! Schema::hasTable('project_user')) {
            return;
        }

        $project = ProjectContext::get()
            ?? Project::query()->where('code', ProjectSeed::profile())->first()
            ?? Project::query()->orderBy('id')->first();

        if (! $project) {
            return;
        }

        if (! $user) {
            $adminMeta = ProjectSeed::meta()['admin'] ?? [];
            $user = User::query()
                ->where('email', $adminMeta['email'] ?? 'admin@vitravel.dev')
                ->first();
        }

        if (! $user) {
            return;
        }

        $project->users()->syncWithoutDetaching([
            $user->id => ['role' => 'admin'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function resolvePrimaryDomain(array $meta): ?string
    {
        if (filled($meta['primary_domain'] ?? null)) {
            return strtolower(preg_replace('/:\d+$/', '', (string) $meta['primary_domain']) ?: '');
        }

        $appUrl = (string) config('app.url', '');
        $host = parse_url($appUrl, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    private function resolveDomains(array $meta, ?string $primaryDomain): array
    {
        $domains = [];

        if (is_array($meta['domains'] ?? null)) {
            foreach ($meta['domains'] as $d) {
                $d = strtolower(trim((string) $d));
                $d = preg_replace('/:\d+$/', '', $d) ?: $d;
                if ($d !== '') {
                    $domains[] = $d;
                }
            }
        }

        if ($primaryDomain && ! in_array($primaryDomain, $domains, true)) {
            array_unshift($domains, $primaryDomain);
        }

        return array_values(array_unique($domains));
    }
}
