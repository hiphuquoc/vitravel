<?php

use App\Models\Language;
use App\Models\SeoEntry;
use App\Services\SeoService;
use Illuminate\Database\Migrations\Migration;

/**
 * Backfill 301: default hub paths → current slug_full tree
 * (vd. /cruises → /du-thuyen, /cruises/{type} → /du-thuyen/{type}, …).
 */
return new class extends Migration
{
    public function up(): void
    {
        $seo = app(SeoService::class);
        $locale = (string) config('language.default_code', 'vi');
        $languageId = Language::idByCode($locale);
        if (! $languageId) {
            return;
        }

        foreach (config('seo.hubs', []) as $hubKey => $cfg) {
            $defaultSlug = (string) ($cfg['default_slug'] ?? '');
            if ($defaultSlug === '') {
                continue;
            }

            try {
                $hub = $seo->ensureHub($hubKey, $locale);
            } catch (\Throwable $e) {
                continue;
            }

            $current = $hub->translation($locale)?->slug_full;
            if (! filled($current)) {
                continue;
            }

            $oldHub = '/'.ltrim($defaultSlug, '/');
            $newHub = '/'.ltrim((string) $current, '/');

            if ($oldHub !== $newHub) {
                $seo->createRedirect301($oldHub, $newHub, $languageId);
            }

            $this->backfillChildren($seo, $hub, $oldHub, $locale, $languageId);
        }
    }

    private function backfillChildren(
        SeoService $seo,
        SeoEntry $parent,
        string $oldParentFull,
        string $locale,
        int $languageId,
    ): void {
        $parent->loadMissing(['children.translations']);

        foreach ($parent->children as $child) {
            $trans = $child->translation($locale);
            if (! $trans || ! filled($trans->slug) || ! filled($trans->slug_full)) {
                continue;
            }

            $oldFull = rtrim($oldParentFull, '/').'/'.ltrim((string) $trans->slug, '/');
            $newFull = '/'.ltrim((string) $trans->slug_full, '/');

            if ($oldFull !== $newFull) {
                $seo->createRedirect301($oldFull, $newFull, $languageId);
            }

            $this->backfillChildren($seo, $child, $oldFull, $locale, $languageId);
        }
    }

    public function down(): void
    {
        // Keep redirects
    }
};
