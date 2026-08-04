<?php

declare(strict_types=1);

namespace App\Services\AI;

use InvalidArgumentException;
use RuntimeException;

/**
 * Load prompt PHP từ resources/ai/prompts (map trong config/ai.php).
 * Biến: {{name}} — thay bằng giá trị string.
 */
final class PromptRepository
{
    /**
     * @return array{key: string, name: string, system: string, user: string, output_format?: string}
     */
    public function get(string $promptKey): array
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
        ];
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
     * @return array{system: string, user: string, output_format: string, key: string, name: string}
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
        ];
    }
}
