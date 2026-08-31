<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CruiseType;
use App\Support\CruiseTypeSlug;
use App\Models\Faq;
use App\Models\Language;
use App\Models\Package;
use App\Models\PackageItineraryDay;
use App\Models\PackageTranslation;
use App\Models\TravelStyle;
use App\Services\CurrencyManager;
use App\Services\MediaService;
use App\Services\PriceTableService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PackageApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $type = $request->string('type', Package::TYPE_TOUR)->toString();
        abort_unless(in_array($type, [Package::TYPE_TOUR, Package::TYPE_CRUISE], true), 404);

        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = Package::query()
            ->with([
                'country.translations',
                'translations',
                'travelStyles.translations',
                'seoEntry.translations',
                'cruiseType',
                'mediaAttachments.media',
            ])
            ->where('type', $type);

        if ($request->filled('country_id')) {
            $countryId = $request->integer('country_id');
            $query->where(function ($q) use ($countryId) {
                $q->where('country_id', $countryId)
                    ->orWhereHas('countries', fn ($c) => $c->where('countries.id', $countryId));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $paginator = $query->orderByDesc('id')->paginate(
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (Package $p) => $this->serializeListItem($p, $locale));

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $package = Package::query()
            ->with([
                'translations',
                'country.translations',
                'countries.translations',
                'travelStyles.translations',
                'categories.translations',
                'seoEntry.translations',
                'itineraryDays.translations',
                'faqs.translations',
                'cabinTypes.translations',
                'cruiseType',
                'mediaAttachments.media',
            ])
            ->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($package, $locale));
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);

        return $this->save($request);
    }

    public function destroy(int $id): JsonResponse
    {
        $package = Package::query()->findOrFail($id);
        $package->delete();

        return ApiResponse::success(null, 'Đã xóa gói tour');
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $type = $request->string('type', Package::TYPE_TOUR)->toString();
        abort_unless(in_array($type, [Package::TYPE_TOUR, Package::TYPE_CRUISE], true), 404);

        $countries = Country::query()
            ->with('translations')
            ->orderBy('sort')
            ->get()
            ->map(fn (Country $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->translation($locale)?->name ?? $c->code,
            ]);

        $travelStyles = TravelStyle::query()
            ->with('translations')
            ->where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->map(fn (TravelStyle $s) => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->translation($locale)?->name ?? $s->code,
            ]);

        $cruiseTypes = CruiseType::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (CruiseType $t) => [
                'id' => $t->id,
                'slug' => $t->slug,
                'name' => $t->name,
                'is_active' => $t->is_active,
            ]);

        $currencyManager = app(CurrencyManager::class);
        $currencies = collect($currencyManager->available())
            ->map(fn (array $meta, string $code) => [
                'value' => $code,
                'label' => $code.' — '.($meta['name_local'] ?? $meta['name'] ?? $code),
                'symbol' => $meta['symbol'] ?? null,
            ])
            ->values()
            ->all();

        return ApiResponse::success([
            'countries' => $countries,
            'travel_styles' => $travelStyles,
            'cruise_types' => $cruiseTypes,
            'currencies' => $currencies,
            'default_currency' => (string) config('currency.default', 'VND'),
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'statuses' => [
                ['value' => 'draft', 'label' => 'Nháp'],
                ['value' => 'published', 'label' => 'Xuất bản'],
                ['value' => 'archived', 'label' => 'Lưu trữ'],
            ],
            'discount_badges' => [
                ['value' => '', 'label' => '— Không có —'],
                ['value' => 'Ưu đãi đặc biệt', 'label' => 'Ưu đãi đặc biệt'],
                ['value' => 'Bán chạy nhất', 'label' => 'Bán chạy nhất'],
                ['value' => 'Bán chạy', 'label' => 'Bán chạy'],
                ['value' => 'Mới', 'label' => 'Mới'],
                ['value' => 'Hot deal', 'label' => 'Hot deal'],
            ],
            'seo_parents' => $this->mapSeoParents(
                $type === Package::TYPE_CRUISE
                    ? $this->seoService()->parentOptionsForType('package_cruise')
                    : $this->seoService()->parentOptionsForType('package_tour'),
                $locale,
            ),
        ]);
    }

    private function save(Request $request): JsonResponse
    {
        $type = $request->string('type', Package::TYPE_TOUR)->toString();
        abort_unless(in_array($type, [Package::TYPE_TOUR, Package::TYPE_CRUISE], true), 404);

        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        if ($type === Package::TYPE_CRUISE && $request->filled('cruise_type')) {
            $resolved = CruiseTypeSlug::resolve((string) $request->input('cruise_type'));
            if ($resolved === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cruise_type' => [
                        'Loại du thuyền không hợp lệ. Chọn một mục có trong danh sách «Loại du thuyền» (slug phải tồn tại trong hệ thống).',
                    ],
                ]);
            }
            $request->merge(['cruise_type' => $resolved]);
        }

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:packages,id',
                'country_id' => 'required|integer|exists:countries,id',
                'country_ids' => 'nullable|array',
                'country_ids.*' => 'integer|exists:countries,id',
                'code' => 'nullable|string|max:64',
                'duration_days' => 'required|integer|min:1',
                'duration_nights' => 'nullable|integer|min:0',
                'price_from' => 'nullable|numeric|min:0',
                'currency' => [
                    'nullable',
                    'string',
                    'max:3',
                    function (string $attribute, mixed $value, \Closure $fail) {
                        if ($value === null || $value === '') {
                            return;
                        }
                        if (! app(CurrencyManager::class)->isSupported((string) $value)) {
                            $fail('Tiền tệ không được hỗ trợ.');
                        }
                    },
                ],
                'status' => 'required|string|in:draft,published,archived',
                'discount_badge' => 'nullable|string|max:100',
                'sort' => 'nullable|integer|min:0',
                'is_featured' => 'nullable|boolean',
                'is_hot_deal' => 'nullable|boolean',
                'title' => 'required|string|max:255',
                'start_location' => 'nullable|string|max:255',
                'end_location' => 'nullable|string|max:255',
                'summary' => 'nullable|string',
                'highlights_intro' => 'nullable|string',
                'featured_quote_text' => 'nullable|string|max:255',
                'featured_quote_author' => 'nullable|string|max:255',
                'places_to_visit' => 'nullable|string',
                'highlight_bullets' => 'nullable|string',
                'inclusions' => 'nullable|string',
                'exclusions' => 'nullable|string',
                'notes' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:350',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'travel_style_ids' => 'nullable|array',
                'travel_style_ids.*' => 'integer|exists:travel_styles,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:tour_categories,id',
                'cruise_type' => CruiseTypeSlug::packageRules($type === Package::TYPE_CRUISE),
                'departure_port' => 'nullable|string|max:255',
                'boat_class' => 'nullable|string|max:100',
                'nights_on_board' => 'nullable|integer|min:0',
                'itinerary' => 'nullable|array',
                'itinerary.*.id' => 'nullable|integer|exists:package_itinerary_days,id',
                'itinerary.*.day_number' => 'nullable|integer|min:1',
                'itinerary.*.title' => 'nullable|string|max:255',
                'itinerary.*.meals_included' => 'nullable|string|max:100',
                'itinerary.*.transport_icons' => 'nullable|string|max:255',
                'itinerary.*.overnight_at' => 'nullable|string|max:255',
                'itinerary.*.content' => 'nullable|string',
                'faqs' => 'nullable|array',
                'faqs.*.id' => 'nullable|integer|exists:faqs,id',
                'faqs.*.question' => 'nullable|string|max:500',
                'faqs.*.answer' => 'nullable|string',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
                'gallery_media_ids' => 'nullable|array|max:40',
                'gallery_media_ids.*' => 'integer|exists:media,id',
            ] + PriceTableService::validationRules());
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $countryIds = array_values(array_unique(array_map(
            'intval',
            $validated['country_ids'] ?? [$validated['country_id']]
        )));
        if (! in_array((int) $validated['country_id'], $countryIds, true)) {
            $countryIds[] = (int) $validated['country_id'];
        }

        try {
            $package = DB::transaction(function () use ($request, $validated, $type, $locale, $countryIds) {
            $package = isset($validated['id'])
                ? Package::query()->findOrFail($validated['id'])
                : new Package(['type' => $type]);

            $ratingStar = array_key_exists('rating_aggregate_star', $validated)
                ? $validated['rating_aggregate_star']
                : ($package->rating ?? 0);
            $ratingCount = array_key_exists('rating_aggregate_count', $validated)
                ? $validated['rating_aggregate_count']
                : ($package->review_count ?? 0);

            $package->fill([
                'type' => $type,
                'country_id' => $validated['country_id'],
                'code' => $validated['code'] ?? null,
                'duration_days' => $validated['duration_days'],
                'duration_nights' => $validated['duration_nights'] ?? max(0, $validated['duration_days'] - 1),
                'price_from' => $validated['price_from'] ?? null,
                'currency' => strtoupper((string) ($validated['currency'] ?? config('currency.default', 'VND'))),
                'status' => $validated['status'],
                'discount_badge' => ($validated['discount_badge'] ?? null) ?: null,
                'sort' => $validated['sort'] ?? 0,
                'rating' => $ratingStar ?? 0,
                'review_count' => $ratingCount ?? 0,
                'is_featured' => $request->boolean('is_featured'),
                'is_hot_deal' => $request->boolean('is_hot_deal'),
                'cruise_type' => $validated['cruise_type'] ?? null,
                'departure_port' => $validated['departure_port'] ?? null,
                'boat_class' => $validated['boat_class'] ?? null,
                'nights_on_board' => $validated['nights_on_board'] ?? null,
                'published_at' => $validated['status'] === 'published' ? ($package->published_at ?? now()) : null,
            ]);
            $package->save();

            $this->saveModelTranslation(
                $package,
                PackageTranslation::class,
                'package_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'start_location' => $validated['start_location'] ?? null,
                    'end_location' => $validated['end_location'] ?? null,
                    'summary' => $validated['summary'] ?? null,
                    'highlights_intro' => $validated['highlights_intro'] ?? null,
                    'featured_quote_text' => $validated['featured_quote_text'] ?? null,
                    'featured_quote_author' => $validated['featured_quote_author'] ?? null,
                    'places_to_visit' => $this->linesToArray($validated['places_to_visit'] ?? null),
                    'highlight_bullets' => $this->linesToArray($validated['highlight_bullets'] ?? null),
                    'inclusions' => $this->linesToArray($validated['inclusions'] ?? null),
                    'exclusions' => $this->linesToArray($validated['exclusions'] ?? null),
                    'notes' => $this->linesToArray($validated['notes'] ?? null),
                ],
                [
                    'title', 'start_location', 'end_location', 'summary', 'highlights_intro',
                    'featured_quote_text', 'featured_quote_author',
                    'places_to_visit', 'highlight_bullets', 'inclusions', 'exclusions', 'notes',
                ],
            );

            $package->load([
                'country.translations',
                'country.seoEntry.translations',
                'cruiseType.seoEntry.translations',
            ]);
            $country = $package->country;
            $countryCode = $country?->code ?? 'vn';
            $countrySlug = $country?->translation($locale)?->slug
                ?? Str::slug((string) ($country?->translation($locale)?->name ?? $countryCode));

            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: null;
            $seoType = $this->seoTypeForPackage($type);

            if (! $parentId) {
                if ($type === Package::TYPE_CRUISE) {
                    $cruiseType = $package->cruiseType;
                    if ($cruiseType) {
                        $hubSeo = $this->seoService()->ensureCruisesHub($locale);
                        $ctSeo = $this->seoService()->ensureSeoFor($cruiseType, 'cruise_type', $locale, [
                            'slug' => $cruiseType->slug,
                            'title' => $cruiseType->name,
                            'seo_title' => $cruiseType->name,
                            'status' => 'published',
                            'parent_id' => $hubSeo->id,
                        ]);
                        $parentId = $ctSeo->id;
                    }
                } elseif ($country) {
                    $countrySeo = $this->seoService()->ensureSeoFor($country, 'country', $locale, [
                        'slug' => $countrySlug,
                        'title' => $country->translation($locale)?->name ?? $countrySlug,
                        'seo_title' => $country->translation($locale)?->name ?? $countrySlug,
                        'status' => 'published',
                        'country_slug' => $countrySlug,
                    ]);
                    $parentId = $countrySeo->id;
                }
            }

            $this->saveSeoTranslations(
                $package,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['title'],
                        'title' => $validated['title'],
                        'seo_title' => $validated['seo_title'] ?? $validated['title'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'status' => $validated['status'],
                        'parent_id' => $parentId,
                        'country_slug' => $countrySlug,
                        'country_code' => $countryCode,
                        'cruise_type' => $package->cruise_type ?? null,
                        'rating_aggregate_star' => $ratingStar,
                        'rating_aggregate_count' => $ratingCount,
                    ],
                ],
                $seoType,
                [
                    'rating_aggregate_star' => $ratingStar,
                    'rating_aggregate_count' => $ratingCount,
                ],
            );

            $package->travelStyles()->sync($validated['travel_style_ids'] ?? []);
            $package->categories()->sync($validated['category_ids'] ?? []);

            $syncCountries = [];
            foreach ($countryIds as $sort => $cid) {
                $syncCountries[$cid] = ['sort' => $sort];
            }
            $package->countries()->sync($syncCountries);

            $this->syncItineraryDays($package, $validated['itinerary'] ?? [], $locale);
            $this->syncFaqs($package, $validated['faqs'] ?? [], $locale);

            app(MediaService::class)->syncCoverMediaId(
                $package,
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            if (array_key_exists('gallery_media_ids', $validated)) {
                app(MediaService::class)->syncGalleryMediaIds(
                    $package,
                    is_array($validated['gallery_media_ids']) ? $validated['gallery_media_ids'] : [],
                );
            }

            if (isset($validated['price_table']) && is_array($validated['price_table'])) {
                app(PriceTableService::class)->sync($package, $validated['price_table'], $locale);
            }

            return $package->fresh([
                'translations',
                'country.translations',
                'countries.translations',
                'travelStyles.translations',
                'categories.translations',
                'seoEntry.translations',
                'itineraryDays.translations',
                'faqs.translations',
                'cabinTypes.translations',
                'mediaAttachments.media',
            ]);
        });
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_PRICE_PERIOD', 422);
        }

        return ApiResponse::success(
            $this->serializeDetail($package, $locale),
            isset($validated['id']) ? 'Đã cập nhật gói tour' : 'Đã tạo gói tour',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncItineraryDays(Package $package, array $rows, string $locale): void
    {
        $keepIds = [];

        foreach ($rows as $index => $row) {
            if (empty($row['title'])) {
                continue;
            }

            $day = ! empty($row['id'])
                ? PackageItineraryDay::query()->find($row['id'])
                : new PackageItineraryDay(['package_id' => $package->id]);

            if (! $day) {
                continue;
            }

            $transport = array_values(array_filter(array_map(
                'trim',
                explode(',', (string) ($row['transport_icons'] ?? '')),
            )));

            $dayNumber = (int) ($row['day_number'] ?? ($index + 1));

            $day->fill([
                'package_id' => $package->id,
                'day_number' => $dayNumber,
                'meals_included' => $row['meals_included'] ?? null,
                'transport_icons' => $transport !== [] ? $transport : null,
                'sort' => $dayNumber,
            ]);
            $day->save();
            $keepIds[] = $day->id;

            $languageId = Language::idByCode($locale);
            if ($languageId) {
                $day->translations()->updateOrCreate(
                    ['language_id' => $languageId],
                    [
                        'title' => $row['title'],
                        'content' => $row['content'] ?? null,
                        'overnight_at' => $row['overnight_at'] ?? null,
                    ],
                );
            }
        }

        $package->itineraryDays()->whereNotIn('id', $keepIds)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncFaqs(Package $package, array $rows, string $locale): void
    {
        $keepIds = [];
        $sort = 0;
        $langId = Language::idByCode($locale);

        foreach ($rows as $row) {
            if (empty($row['question'])) {
                continue;
            }

            $faq = ! empty($row['id'])
                ? Faq::query()->find($row['id'])
                : new Faq([
                    'faqable_type' => Package::class,
                    'faqable_id' => $package->id,
                ]);

            if (! $faq) {
                continue;
            }

            $faq->fill([
                'faqable_type' => Package::class,
                'faqable_id' => $package->id,
                'sort' => $sort,
                'is_active' => true,
            ]);
            $faq->save();
            $keepIds[] = $faq->id;

            if ($langId) {
                $faq->translations()->updateOrCreate(
                    ['language_id' => $langId],
                    [
                        'question' => $row['question'],
                        'answer' => $row['answer'] ?? null,
                    ],
                );
            }

            $sort++;
        }

        $package->faqs()->whereNotIn('id', $keepIds)->delete();
    }

    /** @return array<string, mixed> */
    private function serializeListItem(Package $package, string $locale): array
    {
        $t = $package->translation($locale);
        $seo = $package->seoEntry?->translation($locale);

        return [
            'id' => $package->id,
            'type' => $package->type,
            'code' => $package->code,
            'title' => $t?->title,
            'status' => $package->status,
            'duration_days' => $package->duration_days,
            'duration_nights' => $package->duration_nights,
            'price_from' => $package->price_from,
            'currency' => $package->currency,
            'is_featured' => $package->is_featured,
            'is_hot_deal' => $package->is_hot_deal,
            'cruise_type' => $package->cruiseType ? $package->cruise_type : null,
            'cruise_type_invalid' => $package->cruise_type && ! $package->cruiseType
                ? $package->cruise_type
                : null,
            'cruise_type_name' => $package->relationLoaded('cruiseType')
                ? ($package->cruiseType?->name)
                : null,
            'country' => $package->country ? [
                'id' => $package->country->id,
                'name' => $package->country->translation($locale)?->name,
            ] : null,
            'travel_styles' => $package->travelStyles->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->translation($locale)?->name,
            ])->values(),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'cover' => app(MediaService::class)->adminMediaPayload($package->coverMedia(), 'thumb'),
            'updated_at' => $package->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(Package $package, string $locale): array
    {
        $t = $package->translation($locale);
        $seo = $package->seoEntry?->translation($locale);

        return array_merge($this->serializeListItem($package, $locale), [
            'start_location' => $t?->start_location,
            'end_location' => $t?->end_location,
            'summary' => $t?->summary,
            'highlights_intro' => $t?->highlights_intro,
            'featured_quote_text' => $t?->featured_quote_text,
            'featured_quote_author' => $t?->featured_quote_author,
            'places_to_visit' => $this->arrayToLines($t?->places_to_visit),
            'highlight_bullets' => $this->arrayToLines($t?->highlight_bullets),
            'inclusions' => $this->arrayToLines($t?->inclusions),
            'exclusions' => $this->arrayToLines($t?->exclusions),
            'notes' => $this->arrayToLines($t?->notes),
            'sort' => $package->sort,
            'discount_badge' => $package->discount_badge,
            'cruise_type' => $package->cruiseType ? $package->cruise_type : null,
            'cruise_type_invalid' => $package->cruise_type && ! $package->cruiseType
                ? $package->cruise_type
                : null,
            'departure_port' => $package->departure_port,
            'boat_class' => $package->boat_class,
            'nights_on_board' => $package->nights_on_board,
            'country_id' => $package->country_id,
            'country_ids' => $package->countries->pluck('id')->values(),
            'travel_style_ids' => $package->travelStyles->pluck('id')->values(),
            'category_ids' => $package->categories->pluck('id')->values(),
            'itinerary' => $package->itineraryDays->map(function (PackageItineraryDay $day) use ($locale) {
                $dt = $day->translation($locale);

                return [
                    'id' => $day->id,
                    'day_number' => $day->day_number,
                    'meals_included' => $day->meals_included,
                    'transport_icons' => is_array($day->transport_icons)
                        ? implode(', ', $day->transport_icons)
                        : '',
                    'title' => $dt?->title,
                    'content' => $dt?->content,
                    'overnight_at' => $dt?->overnight_at,
                ];
            })->values(),
            'faqs' => $package->faqs->map(function (Faq $faq) use ($locale) {
                $ft = $faq->translation($locale);

                return [
                    'id' => $faq->id,
                    'question' => $ft?->question,
                    'answer' => $ft?->answer,
                ];
            })->values(),
            'translated_locales' => $this->translatedLocaleCodes($package, 'title'),
            'cover' => app(MediaService::class)->adminMediaPayload($package->coverMedia(), 'card'),
            'gallery' => app(MediaService::class)->adminGalleryPayload($package, 'card'),
            'price_table' => app(PriceTableService::class)->adminPayload($package, $locale),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'parent_id' => $package->seoEntry?->parent_id,
                'rating_aggregate_star' => $package->seoEntry?->rating_aggregate_star !== null
                    ? (float) $package->seoEntry->rating_aggregate_star
                    : ($package->rating !== null ? (float) $package->rating : null),
                'rating_aggregate_count' => $package->seoEntry?->rating_aggregate_count !== null
                    ? (int) $package->seoEntry->rating_aggregate_count
                    : ($package->review_count !== null ? (int) $package->review_count : null),
            ],
        ]);
    }
}
