<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\PriceGuestType;
use App\Models\Project;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;

/**
 * Seed đối tượng khách mặc định nếu project chưa có mã đó.
 * Không ghi đè name/age admin đã sửa. Có thể override từ seed key `price_guest_types`.
 *
 * Chạy được:
 *   - trong `project:seed {profile}` (đã có ProjectContext)
 *   - độc lập: `php artisan db:seed --class=PriceGuestTypeSeeder` (mọi project)
 */
class PriceGuestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $current = ProjectContext::get();
        if ($current) {
            $this->seedForProject($current);

            return;
        }

        $projects = Project::query()->orderBy('id')->get();
        if ($projects->isEmpty()) {
            $this->command?->warn('Chưa có project — bỏ qua PriceGuestTypeSeeder.');

            return;
        }

        foreach ($projects as $project) {
            ProjectContext::run($project, function () use ($project) {
                $profile = trim((string) ($project->seed_profile ?: $project->code));
                if ($profile !== '') {
                    ProjectSeed::useProfile($profile);
                }

                try {
                    $this->seedForProject($project);
                } finally {
                    ProjectSeed::clearProfile();
                }
            });
        }
    }

    private function seedForProject(Project $project): void
    {
        $rows = $this->guestTypeRows();
        $viId = Language::idByCode('vi');
        $enId = Language::idByCode('en');
        $projectId = $project->id;

        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $type = PriceGuestType::query()->where('code', $code)->first();
            if ($type) {
                continue;
            }

            $type = PriceGuestType::query()->create([
                'project_id' => $projectId,
                'code' => $code,
                'age_min' => $row['age_min'] ?? null,
                'age_max' => $row['age_max'] ?? null,
                'sort' => $row['sort'] ?? 0,
                'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
            ]);

            $names = $row['name'] ?? [];
            if (! is_array($names)) {
                $names = ['vi' => (string) $names];
            }

            if ($viId && filled($names['vi'] ?? null)) {
                $type->translations()->create([
                    'project_id' => $projectId,
                    'language_id' => $viId,
                    'name' => $names['vi'],
                    'description' => is_array($row['description'] ?? null)
                        ? ($row['description']['vi'] ?? null)
                        : ($row['description'] ?? null),
                ]);
            }

            if ($enId && filled($names['en'] ?? null)) {
                $type->translations()->create([
                    'project_id' => $projectId,
                    'language_id' => $enId,
                    'name' => $names['en'],
                    'description' => is_array($row['description'] ?? null)
                        ? ($row['description']['en'] ?? null)
                        : null,
                ]);
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function guestTypeRows(): array
    {
        $fromSeed = [];
        try {
            $fromSeed = ProjectSeed::get('price_guest_types', []);
        } catch (\RuntimeException) {
            $fromSeed = [];
        }

        if (is_array($fromSeed) && $fromSeed !== []) {
            return array_values($fromSeed);
        }

        /** @var list<array<string, mixed>> $defaults */
        $defaults = config('pricing.default_guest_types', []);

        return is_array($defaults) ? $defaults : [];
    }
}
