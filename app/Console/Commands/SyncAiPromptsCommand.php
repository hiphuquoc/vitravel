<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\PromptRepository;
use Illuminate\Console\Command;

class SyncAiPromptsCommand extends Command
{
    protected $signature = 'ai:sync-prompts
                            {--force : Ghi đè cả prompt đã customize trên DB}';

    protected $description = 'Sync resources/ai/prompts/*.php (config/ai.php) → bảng ai_system_prompts';

    public function handle(PromptRepository $prompts): int
    {
        $force = (bool) $this->option('force');
        $result = $prompts->syncFromFiles($force);

        $this->info('Created: '.implode(', ', $result['created'] ?: ['—']));
        $this->info('Updated: '.implode(', ', $result['updated'] ?: ['—']));
        $this->info('Skipped (customized): '.implode(', ', $result['skipped_custom'] ?: ['—']));

        if ($force) {
            $this->warn('Đã --force: mọi prompt lấy lại từ file seed.');
        }

        return self::SUCCESS;
    }
}
