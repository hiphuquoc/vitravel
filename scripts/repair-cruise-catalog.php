<?php

declare(strict_types=1);

use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use App\Models\Package;
use App\Models\PackageCabinType;
use App\Models\PackageCabinTypeTranslation;
use App\Models\PackageItineraryDay;
use App\Models\PackageItineraryDayTranslation;
use App\Models\PackageTranslation;
use App\Services\SeoService;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$profiles = array_values(array_filter(array_slice($argv, 1), fn ($arg) => ! str_starts_with((string) $arg, '-')));
if ($profiles === []) {
    $profiles = ['phuquoc', 'phuquy'];
}

$seo = app(SeoService::class);
$viId = Language::idByCode('vi');
$enId = Language::idByCode('en');

foreach ($profiles as $profile) {
    repairCruisesForProfile((string) $profile, $seo, $viId, $enId);
}

echo "Xong.\n";

/**
 * Chỉ sửa nhóm cruise/thuyền:
 * - khôi phục hoặc tạo thiếu `cruise_types`
 * - khôi phục hoặc tạo thiếu `packages` loại cruise
 * - không ghi đè package đã có nội dung/chỉnh tay
 */
function repairCruisesForProfile(string $profile, SeoService $seo, ?int $viId, ?int $enId): void
{
    ProjectSeed::useProfile($profile);

    try {
        $project = App\Models\Project::query()->where('code', $profile)->first();
        if (! $project) {
            echo "[{$profile}] Bỏ qua: chưa có project.\n";

            return;
        }

        ProjectContext::set($project);
        echo "\n=== Repair cruise catalog: {$profile} (#{$project->id}) ===\n";

        $countryIds = Country::query()
            ->with('translations')
            ->get()
            ->mapWithKeys(function (Country $country) {
                $slug = $country->translation('vi')?->slug ?? $country->slug ?? null;

                return $slug ? [$slug => $country->id] : [];
            })
            ->all();

        $typeMap = [];
        $typeCreated = 0;
        $typeMatched = 0;

        foreach (ProjectSeed::get('cruise_types', []) as $sort => $row) {
            $slug = (string) ($row['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $type = resolveCruiseType($slug, (string) ($row['name'] ?? $slug));
            $isNew = ! $type->exists;

            if ($isNew) {
                $type->slug = $slug;
                $type->fill([
                    'name' => $row['name'] ?? $slug,
                    'intro' => $row['intro'] ?? $row['subtitle'] ?? null,
                    'seo_body' => $row['seo_body'] ?? null,
                    'sort' => $row['sort'] ?? $sort,
                    'is_active' => true,
                ]);
                $type->save();
                $typeCreated++;
                echo "  + cruise_type {$slug}\n";
            } else {
                if ($type->trashed()) {
                    $type->restore();
                }
                if (! $type->is_active) {
                    $type->forceFill(['is_active' => true])->save();
                }
                $typeMatched++;
            }

            ensureCruiseTypeSeo($seo, $type, $enId);
            $typeMap[$slug] = $type->slug;
        }

        $pkgCreated = 0;
        $pkgRestored = 0;
        $pkgLinked = 0;
        $pkgKept = 0;

        foreach (ProjectSeed::get('cruises', []) as $sort => $row) {
            $code = (string) ($row['tourCode'] ?? ($row['slug'] ?? "CRUISE-{$sort}"));
            $slug = (string) ($row['slug'] ?? $code);
            $typeSlug = (string) ($row['typeSlug'] ?? '');
            if ($typeSlug === '') {
                continue;
            }

            $countrySlug = (string) ($row['countrySlug'] ?? $row['zoneSlug'] ?? '');
            $countryId = $countrySlug !== '' ? ($countryIds[$countrySlug] ?? null) : null;
            if (! $countryId && $countryIds !== []) {
                $countryId = reset($countryIds) ?: null;
            }
            if (! $countryId) {
                continue;
            }

            $package = findCruisePackage($code, $slug);
            $isNew = ! $package->exists;

            if ($isNew) {
                $package->fill([
                    'type' => Package::TYPE_CRUISE,
                    'code' => $code,
                    'country_id' => $countryId,
                    'duration_days' => resolveDays($row),
                    'duration_nights' => resolveNights($row),
                    'price_from' => $row['priceFrom'] ?? null,
                    'currency' => $row['currency'] ?? 'VND',
                    'rating' => $row['rating'] ?? 0,
                    'review_count' => $row['reviewCount'] ?? 0,
                    'is_featured' => (bool) ($row['featured'] ?? false),
                    'discount_badge' => $row['badge'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort' => $sort,
                    'cruise_type' => $typeMap[$typeSlug] ?? $typeSlug,
                    'departure_port' => $row['departurePort'] ?? null,
                    'boat_class' => $row['boatClass'] ?? null,
                    'nights_on_board' => $row['nightsOnBoard'] ?? null,
                ]);
                $package->save();

                seedCruisePackageContent($package, $row, $viId, $enId);
                $pkgCreated++;
                echo "  + package {$code} ({$slug})\n";
            } else {
                $changed = false;
                if ($package->trashed()) {
                    $package->restore();
                    $pkgRestored++;
                    $changed = true;
                    echo "  ↺ restored package {$code}\n";
                }
                if ($package->type !== Package::TYPE_CRUISE) {
                    $package->type = Package::TYPE_CRUISE;
                    $changed = true;
                }
                if (($typeMap[$typeSlug] ?? $typeSlug) !== (string) $package->cruise_type) {
                    $package->cruise_type = $typeMap[$typeSlug] ?? $typeSlug;
                    $pkgLinked++;
                    $changed = true;
                }
                if ($package->status !== 'published') {
                    $package->status = 'published';
                    $package->published_at = $package->published_at ?? now();
                    $changed = true;
                }
                if (! $package->country_id) {
                    $package->country_id = $countryId;
                    $changed = true;
                }
                if ($changed) {
                    $package->save();
                } else {
                    $pkgKept++;
                }
            }

            ensureCruisePackageSeo($seo, $package, $row, $enId);
        }

        $publishedCruises = Package::query()->published()->cruises()->count();
        $expectedCruises = count(ProjectSeed::get('cruises', []));

        echo "  cruise_types: +{$typeCreated} matched={$typeMatched}\n";
        echo "  packages: +{$pkgCreated} restored={$pkgRestored} relinked={$pkgLinked} kept={$pkgKept}\n";
        echo "  published cruises: {$publishedCruises}/{$expectedCruises}\n";
    } finally {
        ProjectSeed::clearProfile();
        ProjectContext::clear();
    }
}

function resolveCruiseType(string $seedSlug, string $name): CruiseType
{
    $type = CruiseType::query()->withTrashed()->where('slug', $seedSlug)->first();
    if ($type) {
        return $type;
    }

    $normalized = normalizeCruiseLabel($name);
    foreach (CruiseType::query()->withTrashed()->get() as $existing) {
        if (normalizeCruiseLabel((string) $existing->name) === $normalized) {
            return $existing;
        }
    }

    return new CruiseType;
}

function findCruisePackage(string $code, string $slug): Package
{
    $package = Package::query()->withTrashed()->where('code', $code)->first();
    if ($package) {
        return $package;
    }

    $translation = PackageTranslation::query()
        ->whereHas('package', fn ($q) => $q->withTrashed()->where('type', Package::TYPE_CRUISE))
        ->whereHas('package.seoEntry.translations', fn ($q) => $q->where('slug', $slug))
        ->first();

    return $translation?->package ?? new Package;
}

function ensureCruiseTypeSeo(SeoService $seo, CruiseType $type, ?int $enId): void
{
    $hub = $seo->ensureCruisesHub('vi');
    $seo->ensureSeoFor($type, 'cruise_type', 'vi', [
        'slug' => $type->slug,
        'title' => $type->name,
        'seo_title' => $type->name,
        'description' => $type->intro,
        'seo_description' => $type->intro,
        'status' => 'published',
        'parent_id' => $hub->id,
        'reclaim_slug_full' => true,
    ]);

    if ($enId) {
        $hubEn = $seo->ensureCruisesHub('en');
        $seo->ensureSeoFor($type, 'cruise_type', 'en', [
            'slug' => $type->slug,
            'title' => $type->name,
            'seo_title' => $type->name,
            'status' => 'published',
            'parent_id' => $hubEn->id,
            'reclaim_slug_full' => true,
        ]);
    }
}

/**
 * Chỉ seed full content cho package mới tạo, không đụng package cũ đã chỉnh tay.
 *
 * @param array<string, mixed> $row
 */
function seedCruisePackageContent(Package $package, array $row, ?int $viId, ?int $enId): void
{
    if ($viId) {
        PackageTranslation::query()->create([
            'package_id' => $package->id,
            'language_id' => $viId,
            'title' => $row['title'],
            'start_location' => $row['start'] ?? null,
            'end_location' => $row['end'] ?? null,
            'places_to_visit' => $row['places'] ?? [],
            'featured_quote_text' => $row['quote']['text'] ?? null,
            'featured_quote_author' => $row['quote']['author'] ?? null,
            'highlights_intro' => $row['highlightsIntro'] ?? null,
            'highlight_bullets' => $row['highlights'] ?? [],
            'inclusions' => $row['inclusions'] ?? [],
            'exclusions' => $row['exclusions'] ?? [],
            'notes' => $row['notes'] ?? [],
        ]);
    }

    $en = $row['en'] ?? null;
    if ($enId && is_array($en)) {
        PackageTranslation::query()->create([
            'package_id' => $package->id,
            'language_id' => $enId,
            'title' => $en['title'] ?? $row['title'],
            'start_location' => $en['start'] ?? ($row['start'] ?? null),
            'end_location' => $en['end'] ?? ($row['end'] ?? null),
            'places_to_visit' => $en['places'] ?? ($row['places'] ?? []),
            'featured_quote_text' => $en['quote']['text'] ?? ($row['quote']['text'] ?? null),
            'featured_quote_author' => $en['quote']['author'] ?? ($row['quote']['author'] ?? null),
            'highlights_intro' => $en['highlightsIntro'] ?? ($row['highlightsIntro'] ?? null),
            'highlight_bullets' => $en['highlights'] ?? ($row['highlights'] ?? []),
            'inclusions' => $en['inclusions'] ?? ($row['inclusions'] ?? []),
            'exclusions' => $en['exclusions'] ?? ($row['exclusions'] ?? []),
            'notes' => $en['notes'] ?? ($row['notes'] ?? []),
        ]);
    }

    foreach ($row['itinerary'] ?? [] as $index => $dayRow) {
        $day = PackageItineraryDay::query()->create([
            'package_id' => $package->id,
            'day_number' => $dayRow['day'] ?? ($index + 1),
            'meals_included' => $dayRow['meals'] ?? null,
            'transport_icons' => $dayRow['transport'] ?? [],
            'sort' => $dayRow['day'] ?? ($index + 1),
        ]);

        if ($viId) {
            PackageItineraryDayTranslation::query()->create([
                'package_itinerary_day_id' => $day->id,
                'language_id' => $viId,
                'title' => $dayRow['title'] ?? ('Ngày '.($index + 1)),
                'content' => $dayRow['content'] ?? '',
                'overnight_at' => $dayRow['overnight'] ?? null,
            ]);
        }

        $enDay = is_array($en) ? ($en['itinerary'][$index] ?? null) : null;
        if ($enId && is_array($enDay)) {
            PackageItineraryDayTranslation::query()->create([
                'package_itinerary_day_id' => $day->id,
                'language_id' => $enId,
                'title' => $enDay['title'] ?? ($dayRow['title'] ?? ('Day '.($index + 1))),
                'content' => $enDay['content'] ?? ($dayRow['content'] ?? ''),
                'overnight_at' => $enDay['overnight'] ?? ($dayRow['overnight'] ?? null),
            ]);
        }
    }

    foreach ($row['cabinTypes'] ?? [] as $cabinSort => $cabin) {
        $cabinModel = PackageCabinType::query()->create([
            'package_id' => $package->id,
            'capacity' => $cabin['capacity'] ?? 2,
            'sort' => $cabinSort,
        ]);

        if ($viId) {
            PackageCabinTypeTranslation::query()->create([
                'package_cabin_type_id' => $cabinModel->id,
                'language_id' => $viId,
                'name' => $cabin['name'],
                'description' => $cabin['note'] ?? null,
            ]);
        }
    }

    syncCruiseFaqs($package, $row['faqs'] ?? [], is_array($en) ? ($en['faqs'] ?? []) : [], $viId, $enId);
}

/**
 * @param array<string, mixed> $row
 */
function ensureCruisePackageSeo(SeoService $seo, Package $package, array $row, ?int $enId): void
{
    $typeSlug = (string) ($package->cruise_type ?: ($row['typeSlug'] ?? ''));
    $type = CruiseType::query()->where('slug', $typeSlug)->first();
    if (! $type) {
        return;
    }

    ensureCruiseTypeSeo($seo, $type, $enId);
    $parentId = $type->seoEntry?->id;
    $slug = (string) ($row['slug'] ?? $package->code ?? '');

    $seo->syncSeo($package, 'vi', [
        'slug' => $slug,
        'title' => $row['title'] ?? $slug,
        'description' => $row['highlightsIntro'] ?? ($row['title'] ?? $slug),
        'rating_aggregate_star' => $row['rating'] ?? null,
        'rating_aggregate_count' => $row['reviewCount'] ?? null,
        'status' => 'published',
        'parent_id' => $parentId,
        'reclaim_slug_full' => true,
    ], 'package_cruise');

    $en = $row['en'] ?? null;
    if ($enId && is_array($en)) {
        $seo->syncSeo($package, 'en', [
            'slug' => $en['slug'] ?? $slug,
            'title' => $en['title'] ?? ($row['title'] ?? $slug),
            'description' => $en['highlightsIntro'] ?? ($row['highlightsIntro'] ?? ($row['title'] ?? $slug)),
            'rating_aggregate_star' => $row['rating'] ?? null,
            'rating_aggregate_count' => $row['reviewCount'] ?? null,
            'status' => 'published',
            'parent_id' => $parentId,
            'reclaim_slug_full' => true,
        ], 'package_cruise');
    }
}

/**
 * @param list<array{q?: string, a?: string}> $faqs
 * @param list<array{q?: string, a?: string}> $faqsEn
 */
function syncCruiseFaqs(Package $package, array $faqs, array $faqsEn, ?int $viId, ?int $enId): void
{
    foreach ($faqs as $i => $faq) {
        $q = $faq['q'] ?? null;
        $a = $faq['a'] ?? null;
        if (! filled($q) || ! filled($a)) {
            continue;
        }

        $faqModel = Faq::query()->create([
            'faqable_type' => $package->getMorphClass(),
            'faqable_id' => $package->id,
            'sort' => $i,
        ]);

        if ($viId) {
            FaqTranslation::query()->create([
                'faq_id' => $faqModel->id,
                'language_id' => $viId,
                'question' => $q,
                'answer' => $a,
            ]);
        }

        $enFaq = $faqsEn[$i] ?? null;
        if ($enId && is_array($enFaq)) {
            FaqTranslation::query()->create([
                'faq_id' => $faqModel->id,
                'language_id' => $enId,
                'question' => $enFaq['q'] ?? $q,
                'answer' => $enFaq['a'] ?? $a,
            ]);
        }
    }
}

/**
 * @param array<string, mixed> $row
 */
function resolveDays(array $row): int
{
    if (isset($row['days'])) {
        return max(1, (int) $row['days']);
    }

    preg_match('/(\d+)\s*ngày/u', (string) ($row['duration'] ?? ''), $matches);

    return max(1, (int) ($matches[1] ?? 1));
}

/**
 * @param array<string, mixed> $row
 */
function resolveNights(array $row): int
{
    preg_match('/(\d+)\s*đêm/u', (string) ($row['duration'] ?? ''), $matches);
    if (isset($matches[1])) {
        return max(0, (int) $matches[1]);
    }

    return max(0, resolveDays($row) - 1);
}

function normalizeCruiseLabel(string $name): string
{
    $n = mb_strtolower(trim($name));
    $n = str_replace(['—', '–'], '-', $n);
    $n = preg_replace('/\([^)]*\)/u', ' ', $n);
    $n = preg_replace('/\b(phu quoc|phú quốc|phu quy|phú quý)\b/u', ' ', $n);
    $n = preg_replace('/[^a-z0-9\x{00C0}-\x{1EF9}\s]/u', ' ', $n);

    return preg_replace('/\s+/u', ' ', trim($n)) ?: $name;
}
