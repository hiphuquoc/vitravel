<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Trích JSON từ output AI (markdown fence, text thừa).
 * Giữ envelope key «fields» (không unwrap) để khớp prompt translate_page.
 */
final class JsonResponseParser
{
    /** @var list<string> */
    private const PRESERVE_ROOT_KEYS = ['fields'];

    /**
     * @return array<string, mixed>|null
     */
    public static function parse(string $raw): ?array
    {
        $text = trim($raw);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text) ?? $text;

        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $text, $matches)) {
            $text = trim($matches[1]);
        }

        $decoded = self::decode($text);
        if ($decoded !== null) {
            return self::normalizeRoot($decoded);
        }

        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $decoded = self::decode($matches[0]);
            if ($decoded !== null) {
                return self::normalizeRoot($decoded);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decode(string $text): ?array
    {
        foreach (self::candidates($text) as $candidate) {
            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function candidates(string $text): array
    {
        $text = trim($text);
        $strippedControls = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text) ?? $text;
        $noTrailingCommas = preg_replace('/,\s*([}\]])/', '$1', $strippedControls) ?? $strippedControls;

        return array_values(array_unique(array_filter([
            $text,
            $strippedControls,
            $noTrailingCommas,
        ], fn ($v) => is_string($v) && $v !== '')));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function normalizeRoot(array $data): array
    {
        foreach (self::PRESERVE_ROOT_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                return $data;
            }
        }

        if (count($data) === 1) {
            $only = reset($data);
            if (is_array($only) && ! array_is_list($only)) {
                return $only;
            }
        }

        return $data;
    }
}
