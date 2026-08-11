<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\AI\PromptRepository;
use Illuminate\Database\Seeder;

/**
 * Sync file prompts → bảng ai_system_prompts.
 * Không ghi đè prompt đã admin customize (trừ khi gọi artisan --force).
 */
class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        $result = app(PromptRepository::class)->syncFromFiles(force: false);

        $this->command?->info('AI prompts created: '.implode(', ', $result['created'] ?: ['(none)']));
        $this->command?->info('AI prompts updated: '.implode(', ', $result['updated'] ?: ['(none)']));
        if ($result['skipped_custom'] !== []) {
            $this->command?->warn('Skipped customized: '.implode(', ', $result['skipped_custom']));
        }
    }
}
