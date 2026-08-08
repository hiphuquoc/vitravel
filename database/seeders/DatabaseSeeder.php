<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LanguageSeeder::class);

        $dir = trim((string) config('project.seed_dir', 'project'), '/\\');
        $pattern = base_path($dir.'/seed_*.php');
        $files = glob($pattern) ?: [];
        sort($files);

        $seeded = [];
        foreach ($files as $file) {
            $base = basename($file);
            if (! preg_match('/^seed_(.+)\.php$/i', $base, $m)) {
                continue;
            }
            $name = $m[1];
            $this->command?->info("=== Seeding profile: {$name} ===");

            $exit = Artisan::call('project:seed', ['profile' => $name]);
            $this->command?->getOutput()?->write(Artisan::output());

            if ($exit !== 0) {
                throw new RuntimeException("project:seed {$name} failed (exit {$exit}).");
            }

            $seeded[] = $name;
        }

        if ($seeded === []) {
            throw new RuntimeException(
                "Không tìm thấy seed_*.php trong {$dir}/. "
                .'Thêm file seed rồi chạy lại migrate --seed.'
            );
        }

        $this->command?->info('Seeded profiles: '.implode(', ', $seeded));
    }
}
