<?php

declare(strict_types=1);

namespace App\Services\AI\Concerns;

/**
 * Gỡ citation / markdown link do web_search trong output AI.
 */
trait StripsAiCitations
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stripWebSearchCitations(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $out[$key] = $this->stripWebSearchCitations($value);
                continue;
            }
            if (! is_string($value) || $value === '') {
                $out[$key] = $value;
                continue;
            }
            $out[$key] = $this->stripCitationsFromString($value);
        }

        return $out;
    }

    protected function stripCitationsFromString(string $text): string
    {
        $s = $text;

        $s = (string) preg_replace('/\s*\(\[[^\]]*]\(\s*https?:\/\/[^)]+\)\s*\)/iu', '', $s);
        $s = (string) preg_replace('/\s*\[[^\]]*]\(\s*https?:\/\/[^)]+\)/iu', '', $s);
        $s = (string) preg_replace('/\s*\(\s*https?:\/\/[^)]*(?:utm_source=openai|chatgpt\.com)[^)]*\)/iu', '', $s);
        $s = (string) preg_replace('/\s*https?:\/\/[^\s)<]+(?:utm_source=openai|chatgpt\.com)[^\s)<]*/iu', '', $s);
        $s = (string) preg_replace('/[ \t]{2,}/u', ' ', $s);
        $s = (string) preg_replace('/\s+([.,;:!?])/u', '$1', $s);

        return trim($s);
    }
}
