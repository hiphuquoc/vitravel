<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Gateway AI dùng chung (OpenAI-compatible: OpenAI, Gemini OpenAI-mode, DeepSeek).
 * Fallback theo config ai.fallback_providers.
 */
final class AiGateway
{
    /** @var list<string> */
    private const PROVIDERS = ['openai', 'google', 'deepseek'];

    public function isConfigured(): bool
    {
        foreach (self::PROVIDERS as $provider) {
            if ($this->hasKey($provider)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, array{configured: bool, model: string|null, base_url: string|null}>
     */
    public function status(): array
    {
        $out = [];
        foreach (self::PROVIDERS as $provider) {
            $out[$provider] = [
                'configured' => $this->hasKey($provider),
                'model' => $this->resolveModel($provider),
                'base_url' => $this->baseUrl($provider),
            ];
        }

        return $out;
    }

    /**
     * @return array{content: string, parsed: array<string, mixed>|null, provider: string, model: string, latency_ms: int}
     */
    public function chat(
        string $system,
        string $user,
        bool $json = true,
        ?string $provider = null,
        ?int $maxTokens = null,
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Chưa cấu hình API key AI (AI_OPENAI_API_KEY / GEMINI_API_KEY / DEEPSEEK_API_KEY).');
        }

        $order = $this->providerOrder($provider);
        $errors = [];

        foreach ($order as $name) {
            if (! $this->hasKey($name)) {
                continue;
            }
            try {
                return $this->chatWithProvider($name, $system, $user, $json, $maxTokens);
            } catch (\Throwable $e) {
                $errors[] = "{$name}: ".$e->getMessage();
                Log::warning('AI provider failed', [
                    'provider' => $name,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        throw new RuntimeException(
            'Tất cả provider AI đều thất bại. '.implode(' | ', $errors)
        );
    }

    /**
     * @return list<string>
     */
    private function providerOrder(?string $preferred): array
    {
        $fallback = config('ai.fallback_providers', ['openai', 'google', 'deepseek']);
        if (! is_array($fallback)) {
            $fallback = ['openai', 'google', 'deepseek'];
        }
        $fallback = array_values(array_filter($fallback, fn ($p) => is_string($p) && $p !== ''));

        $preferred = $preferred ?: (string) config('ai.default_provider', 'openai');
        if ($preferred === 'gemini') {
            $preferred = 'google';
        }

        $order = [$preferred];
        foreach ($fallback as $p) {
            $p = $p === 'gemini' ? 'google' : $p;
            if (! in_array($p, $order, true)) {
                $order[] = $p;
            }
        }

        return $order;
    }

    private function hasKey(string $provider): bool
    {
        $key = config("services.{$provider}.key");

        return is_string($key) && trim($key) !== '';
    }

    private function baseUrl(string $provider): ?string
    {
        $url = config("services.{$provider}.base_url");

        return is_string($url) && $url !== '' ? rtrim($url, '/') : null;
    }

    private function resolveModel(string $provider): ?string
    {
        $override = config("ai.models.{$provider}");
        if (is_string($override) && $override !== '') {
            return $override;
        }
        $model = config("services.{$provider}.model");

        return is_string($model) && $model !== '' ? $model : null;
    }

    /**
     * @return array{content: string, parsed: array<string, mixed>|null, provider: string, model: string, latency_ms: int}
     */
    private function chatWithProvider(
        string $provider,
        string $system,
        string $user,
        bool $json,
        ?int $maxTokens,
    ): array {
        $apiKey = (string) config("services.{$provider}.key");
        $baseUrl = $this->baseUrl($provider);
        if ($baseUrl === null) {
            throw new RuntimeException("Thiếu base_url cho provider «{$provider}».");
        }

        $model = $this->resolveModel($provider) ?? 'gpt-4o-mini';
        $timeout = (int) config("services.{$provider}.timeout", config('ai.timeout', 180));
        $tokens = $maxTokens ?? (int) config('ai.max_tokens', 8192);
        $startedAt = microtime(true);

        $userContent = $user;
        if ($json) {
            $userContent .= "\n\nHãy CHỈ trả về JSON hợp lệ, không thêm text giải thích.";
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $userContent],
            ],
        ];

        if (preg_match('/^(gpt-5|o[0-9])/i', $model)) {
            $payload['max_completion_tokens'] = $tokens;
        } else {
            $payload['max_tokens'] = $tokens;
        }

        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post($baseUrl.'/chat/completions', $payload);

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new RuntimeException(
                "API «{$provider}» lỗi HTTP {$response->status()}: {$error}"
            );
        }

        $data = $response->json();
        $message = data_get($data, 'choices.0.message', []);
        $rawOutput = $this->extractMessageContent(is_array($message) ? $message : []);

        if ($rawOutput === '') {
            throw new RuntimeException("API «{$provider}» không trả về nội dung.");
        }

        $parsed = null;
        if ($json) {
            $parsed = JsonResponseParser::parse($rawOutput);
            if ($parsed === null) {
                Log::warning('AI JSON parse failed', [
                    'provider' => $provider,
                    'raw_preview' => Str::limit($rawOutput, 1200),
                ]);
                throw new RuntimeException('API trả về JSON không hợp lệ.');
            }
        }

        return [
            'content' => $rawOutput,
            'parsed' => $parsed,
            'provider' => $provider,
            'model' => (string) data_get($data, 'model', $model),
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function extractMessageContent(array $message): string
    {
        $content = $message['content'] ?? null;

        if (is_string($content)) {
            return trim($content);
        }

        if (is_array($content)) {
            $parts = [];
            foreach ($content as $part) {
                if (! is_array($part)) {
                    continue;
                }
                $text = $part['text'] ?? $part['content'] ?? null;
                if (is_string($text) && $text !== '') {
                    $parts[] = $text;
                }
            }

            return trim(implode('', $parts));
        }

        return '';
    }
}
