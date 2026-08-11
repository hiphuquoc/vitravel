<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiSystemPrompt;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

/**
 * Registry prompt hệ thống:
 * 1) DB `ai_system_prompts` (đã seed / admin cập nhật) — nguồn runtime ưu tiên
 * 2) File PHP trong resources/ai/prompts + map config/ai.php — seed & fallback
 *
 * Biến template: {{name}}
 */
final class PromptRepository
{
    /**
     * @return array{key: string, name: string, system: string, user: string, output_format: string, source?: string, version?: int}
     */
    public function get(string $promptKey): array
    {
        if ($this->dbReady()) {
            $row = AiSystemPrompt::query()
                ->where('key', $promptKey)
                ->where('is_active', true)
                ->first();
            if ($row) {
                $payload = $row->toPromptPayload();
                $payload['source'] = $row->is_customized ? 'db_custom' : 'db';
                $payload['version'] = $row->version;

                return $payload;
            }
        }

        return $this->loadFromFile($promptKey);
    }

    /**
     * @param  array<string, scalar|null>  $variables
     */
    public function render(string $template, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/',
            static function (array $m) use ($variables): string {
                $name = $m[1];
                if (! array_key_exists($name, $variables)) {
                    return $m[0];
                }
                $value = $variables[$name];

                return $value === null ? '' : (string) $value;
            },
            $template
        );
    }

    /**
     * @param  array<string, scalar|null>  $variables
     * @return array{system: string, user: string, output_format: string, key: string, name: string, source?: string, version?: int}
     */
    public function renderPrompt(string $promptKey, array $variables): array
    {
        $prompt = $this->get($promptKey);

        return [
            'key' => $prompt['key'],
            'name' => $prompt['name'],
            'system' => $this->render($prompt['system'], $variables),
            'user' => $this->render($prompt['user'], $variables),
            'output_format' => $prompt['output_format'] ?? 'json',
            'source' => $prompt['source'] ?? 'file',
            'version' => $prompt['version'] ?? null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listCatalog(bool $includeInactive = false): array
    {
        if ($this->dbReady() && AiSystemPrompt::query()->exists()) {
            $q = AiSystemPrompt::query()->orderBy('category')->orderBy('key');
            if (! $includeInactive) {
                $q->where('is_active', true);
            }

            return $q->get()->map(fn (AiSystemPrompt $row) => $this->serializeRow($row))->all();
        }

        $out = [];
        foreach ($this->registeredFileKeys() as $key) {
            try {
                $file = $this->loadFromFile($key);
                $meta = $this->fileMeta($key);
                $out[] = array_merge($meta, [
                    'key' => $file['key'],
                    'name' => $file['name'],
                    'system' => $file['system'],
                    'user' => $file['user'],
                    'output_format' => $file['output_format'],
                    'source' => 'file',
                    'is_active' => true,
                    'is_customized' => false,
                ]);
            } catch (\Throwable) {
                // skip broken file
            }
        }

        return $out;
    }

    /**
     * Sync mọi file đã đăng ký trong config/ai.php → DB.
     *
     * @return array{synced: list<string>, skipped_custom: list<string>, created: list<string>, updated: list<string>}
     */
    public function syncFromFiles(bool $force = false): array
    {
        $created = [];
        $updated = [];
        $skipped = [];

        foreach ($this->registeredFileKeys() as $key) {
            $file = $this->loadFromFile($key);
            $meta = $this->fileMeta($key);
            $version = (int) ($meta['version'] ?? 1);

            $row = AiSystemPrompt::query()->where('key', $key)->first();
            if ($row && $row->is_customized && ! $force) {
                $skipped[] = $key;
                continue;
            }

            $payload = [
                'name' => $file['name'],
                'category' => (string) ($meta['category'] ?? 'general'),
                'description' => $meta['description'] ?? null,
                'version' => $version,
                'system' => $file['system'],
                'user' => $file['user'],
                'output_format' => $file['output_format'] ?? 'json',
                'variables' => $meta['variables'] ?? [],
                'entity_types' => $meta['entity_types'] ?? [],
                'is_active' => true,
                'seeded_at' => now(),
            ];

            if (! $row) {
                AiSystemPrompt::query()->create(array_merge($payload, [
                    'key' => $key,
                    'is_customized' => false,
                ]));
                $created[] = $key;
                continue;
            }

            if ($force) {
                $payload['is_customized'] = false;
            }

            $row->fill($payload);
            $row->save();
            $updated[] = $key;
        }

        return [
            'synced' => array_values(array_merge($created, $updated)),
            'created' => $created,
            'updated' => $updated,
            'skipped_custom' => $skipped,
        ];
    }

    /**
     * @return list<string>
     */
    public function registeredFileKeys(): array
    {
        $map = config('ai.prompts', []);
        if (! is_array($map)) {
            return [];
        }

        return array_keys($map);
    }

    /**
     * @return array{key: string, name: string, system: string, user: string, output_format: string, source: string}
     */
    private function loadFromFile(string $promptKey): array
    {
        $map = config('ai.prompts', []);
        if (! is_array($map) || ! isset($map[$promptKey])) {
            throw new InvalidArgumentException("Prompt «{$promptKey}» chưa đăng ký trong config/ai.php.");
        }

        $file = (string) $map[$promptKey];
        $base = rtrim((string) config('ai.prompts_path', resource_path('ai/prompts')), DIRECTORY_SEPARATOR);
        $path = $base.DIRECTORY_SEPARATOR.ltrim($file, DIRECTORY_SEPARATOR);

        if (! is_file($path)) {
            throw new RuntimeException("Không tìm thấy file prompt: {$path}");
        }

        /** @var mixed $data */
        $data = require $path;
        if (! is_array($data) || ! isset($data['system'], $data['user'])) {
            throw new RuntimeException("Prompt «{$promptKey}» phải trả về array có system + user.");
        }

        return [
            'key' => (string) ($data['key'] ?? $promptKey),
            'name' => (string) ($data['name'] ?? $promptKey),
            'system' => (string) $data['system'],
            'user' => (string) $data['user'],
            'output_format' => (string) ($data['output_format'] ?? 'json'),
            'source' => 'file',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fileMeta(string $promptKey): array
    {
        $map = config('ai.prompts', []);
        $file = (string) ($map[$promptKey] ?? '');
        $base = rtrim((string) config('ai.prompts_path', resource_path('ai/prompts')), DIRECTORY_SEPARATOR);
        $path = $base.DIRECTORY_SEPARATOR.ltrim($file, DIRECTORY_SEPARATOR);
        if (! is_file($path)) {
            return [];
        }

        /** @var mixed $data */
        $data = require $path;
        if (! is_array($data)) {
            return [];
        }

        return [
            'category' => (string) ($data['category'] ?? 'general'),
            'description' => isset($data['description']) ? (string) $data['description'] : null,
            'version' => (int) ($data['version'] ?? 1),
            'variables' => is_array($data['variables'] ?? null) ? $data['variables'] : [],
            'entity_types' => is_array($data['entity_types'] ?? null) ? $data['entity_types'] : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRow(AiSystemPrompt $row): array
    {
        return [
            'id' => $row->id,
            'key' => $row->key,
            'name' => $row->name,
            'category' => $row->category,
            'description' => $row->description,
            'version' => $row->version,
            'system' => $row->system,
            'user' => $row->user,
            'output_format' => $row->output_format,
            'variables' => $row->variables ?? [],
            'entity_types' => $row->entity_types ?? [],
            'is_active' => $row->is_active,
            'is_customized' => $row->is_customized,
            'seeded_at' => $row->seeded_at?->toIso8601String(),
            'updated_at' => $row->updated_at?->toIso8601String(),
            'updated_by' => $row->updated_by,
            'source' => $row->is_customized ? 'db_custom' : 'db',
        ];
    }

    private function dbReady(): bool
    {
        try {
            return Schema::hasTable('ai_system_prompts');
        } catch (\Throwable) {
            return false;
        }
    }
}
