<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Language;
use App\Models\Package;
use App\Models\PackageCabinType;
use App\Models\PriceGuestType;
use App\Models\PricePeriod;
use App\Models\PriceRate;
use App\Models\PriceTable;
use App\Models\PriceVariant;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Support\ProjectContext;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PriceTableService
{
    /**
     * @return array<string, mixed>
     */
    public static function validationRules(bool $requireTable = false): array
    {
        $prefix = $requireTable ? 'required' : 'nullable';

        return [
            'price_table' => $prefix.'|array',
            'price_table.currency' => 'nullable|string|size:3',
            'price_table.unit' => 'nullable|string|in:'.implode(',', array_keys(config('pricing.units', []))),
            'price_table.notes' => 'nullable|string|max:5000',
            'price_table.variants' => 'nullable|array',
            'price_table.variants.*.id' => 'nullable|integer',
            'price_table.variants.*.code' => 'nullable|string|max:64',
            'price_table.variants.*.name' => 'nullable|string|max:255',
            'price_table.variants.*.description' => 'nullable|string|max:1000',
            'price_table.variants.*.source' => 'nullable|string|in:custom,cabin,service_option',
            'price_table.variants.*.source_id' => 'nullable|integer',
            'price_table.variants.*.sort' => 'nullable|integer|min:0',
            'price_table.variants.*.is_active' => 'nullable|boolean',
            'price_table.periods' => 'nullable|array',
            'price_table.periods.*.id' => 'nullable|integer',
            'price_table.periods.*.kind' => 'required_with:price_table.periods|string|in:date,range,year',
            'price_table.periods.*.starts_on' => 'nullable|date',
            'price_table.periods.*.ends_on' => 'nullable|date',
            'price_table.periods.*.year' => 'nullable|integer|min:2000|max:2100',
            'price_table.periods.*.label' => 'nullable|string|max:255',
            'price_table.periods.*.is_promo' => 'nullable|boolean',
            'price_table.periods.*.priority' => 'nullable|integer|min:0|max:999',
            'price_table.periods.*.sort' => 'nullable|integer|min:0',
            'price_table.periods.*.is_active' => 'nullable|boolean',
            'price_table.periods.*.rates' => 'nullable|array',
            'price_table.periods.*.rates.*.variant_id' => 'nullable|integer',
            'price_table.periods.*.rates.*.variant_code' => 'nullable|string|max:64',
            'price_table.periods.*.rates.*.guest_type_id' => 'nullable|integer',
            'price_table.periods.*.rates.*.guest_type_code' => 'nullable|string|max:64',
            'price_table.periods.*.rates.*.amount' => 'required_with:price_table.periods.*.rates|numeric|min:0',
            'price_table.periods.*.rates.*.compare_at_amount' => 'nullable|numeric|min:0',
            'price_table.periods.*.rates.*.min_qty' => 'nullable|integer|min:1',
            'price_table.periods.*.rates.*.max_qty' => 'nullable|integer|min:1',
        ];
    }

    /** @return array<string, mixed> */
    public function adminPayload(Model $priceable, string $locale = 'vi'): array
    {
        $table = $this->loadTable($priceable);
        $currency = $table?->currency
            ?? (string) ($priceable->getAttribute('currency') ?? config('currency.default', 'VND'));

        return [
            'id' => $table?->id,
            'currency' => strtoupper($currency),
            'unit' => $table?->unit ?? PriceTable::UNIT_PERSON,
            'notes' => $table?->notes,
            'guest_types' => $this->guestTypesPayload($locale),
            'units' => config('pricing.units', []),
            'period_kinds' => config('pricing.period_kinds', []),
            'variants' => $table
                ? $table->variants->map(fn (PriceVariant $v) => $this->serializeVariant($v, $locale))->values()->all()
                : [],
            'suggested_variants' => $this->suggestedVariants($priceable, $table, $locale),
            'periods' => $table
                ? $table->periods->map(fn (PricePeriod $p) => $this->serializePeriodAdmin($p))->values()->all()
                : [],
        ];
    }

    /** @return array<string, mixed>|null */
    public function publicPayload(Model $priceable, ?string $locale = null): ?array
    {
        $locale = $locale ?: app()->getLocale();
        $table = $this->loadTable($priceable);
        if (! $table) {
            return null;
        }

        $today = Carbon::today();
        $periods = $table->periods
            ->filter(fn (PricePeriod $p) => $p->is_active && $p->ends_on->gte($today) && $p->rates->isNotEmpty())
            ->values();

        if ($periods->isEmpty()) {
            return null;
        }

        $guestTypes = PriceGuestType::query()->active()->with('translations')->get()->keyBy('id');
        $currency = strtoupper((string) ($table->currency ?: $priceable->getAttribute('currency') ?: 'VND'));
        $unit = $table->unit ?: PriceTable::UNIT_PERSON;

        $periodPayload = [];
        $usedGuestIds = [];
        $usedVariantIds = [];

        foreach ($periods as $period) {
            $rows = [];
            $byVariant = $period->rates->groupBy('variant_id');
            foreach ($table->variants->where('is_active', true) as $variant) {
                $rates = $byVariant->get($variant->id);
                if (! $rates || $rates->isEmpty()) {
                    continue;
                }
                $usedVariantIds[$variant->id] = true;
                $cells = [];
                foreach ($rates as $rate) {
                    $usedGuestIds[$rate->guest_type_id] = true;
                    $amount = (float) $rate->amount;
                    $compare = $rate->compare_at_amount !== null ? (float) $rate->compare_at_amount : null;
                    $cells[(string) $rate->guest_type_id] = [
                        'amount' => $amount,
                        'formatted' => $this->formatMoney($amount, $currency),
                        'compareAt' => $compare,
                        'compareAtFormatted' => $compare !== null && $compare > $amount
                            ? $this->formatMoney($compare, $currency)
                            : null,
                        'minQty' => $rate->min_qty,
                        'maxQty' => $rate->max_qty,
                    ];
                }
                $rows[] = [
                    'variantId' => $variant->id,
                    'code' => $variant->code,
                    'name' => $variant->translation($locale)?->name ?? $variant->code,
                    'description' => $variant->translation($locale)?->description,
                    'cells' => $cells,
                ];
            }

            if ($rows === []) {
                continue;
            }

            $periodPayload[] = [
                'id' => $period->id,
                'kind' => $period->kind,
                'label' => $period->label ?: $this->periodDateLabel($period),
                'dateLabel' => $this->periodDateLabel($period),
                'startsOn' => $period->starts_on->toDateString(),
                'endsOn' => $period->ends_on->toDateString(),
                'year' => $period->year,
                'isPromo' => (bool) $period->is_promo,
                'rows' => $rows,
            ];
        }

        if ($periodPayload === []) {
            return null;
        }

        $guestCols = $guestTypes
            ->filter(fn (PriceGuestType $g) => isset($usedGuestIds[$g->id]))
            ->map(fn (PriceGuestType $g) => [
                'id' => $g->id,
                'code' => $g->code,
                'name' => $g->translation($locale)?->name ?? $g->code,
                'ageLabel' => $this->guestAgeLabel($g),
            ])
            ->values()
            ->all();

        return [
            'currency' => $currency,
            'unit' => $unit,
            'unitLabel' => config('pricing.units.'.$unit, $unit),
            'notes' => detail_price_table_notes($table->notes),
            'guestTypes' => $guestCols,
            'periods' => $periodPayload,
        ];
    }

    /**
     * Quote sẵn sàng booking: chọn period khớp ngày (ưu đãi đè giá gốc).
     *
     * @param  list<array{guest_type_id: int, qty: int}>  $guests
     * @return array<string, mixed>|null
     */
    public function quote(Model $priceable, CarbonInterface $date, int $variantId, array $guests): ?array
    {
        $table = $this->loadTable($priceable);
        if (! $table) {
            return null;
        }

        $matching = $table->periods
            ->filter(fn (PricePeriod $p) => $p->is_active && $p->coversDate($date))
            ->sortByDesc(fn (PricePeriod $p) => sprintf('%d-%03d-%d', $p->is_promo ? 1 : 0, $p->priority, $p->id))
            ->values();

        if ($matching->isEmpty()) {
            return null;
        }

        $promo = $matching->first(fn (PricePeriod $p) => $p->is_promo);
        $base = $matching->first(fn (PricePeriod $p) => ! $p->is_promo) ?? $matching->first();
        $currency = strtoupper((string) ($table->currency ?: 'VND'));
        $lines = [];
        $total = 0.0;

        foreach ($guests as $guest) {
            $guestTypeId = (int) ($guest['guest_type_id'] ?? 0);
            $qty = max(1, (int) ($guest['qty'] ?? 1));
            if ($guestTypeId < 1) {
                continue;
            }

            $rate = $this->rateFor($promo, $variantId, $guestTypeId)
                ?? $this->rateFor($base, $variantId, $guestTypeId);
            if (! $rate) {
                return null;
            }

            $amount = (float) $rate->amount;
            $lineTotal = $amount * $qty;
            $total += $lineTotal;
            $lines[] = [
                'guest_type_id' => $guestTypeId,
                'qty' => $qty,
                'unit_amount' => $amount,
                'line_total' => $lineTotal,
                'period_id' => $rate->period_id,
                'is_promo' => (bool) ($rate->period?->is_promo ?? $promo?->is_promo),
            ];
        }

        if ($lines === []) {
            return null;
        }

        return [
            'date' => $date->toDateString(),
            'variant_id' => $variantId,
            'currency' => $currency,
            'unit' => $table->unit,
            'lines' => $lines,
            'total' => $total,
            'total_formatted' => $this->formatMoney($total, $currency),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function sync(Model $priceable, array $payload, string $locale = 'vi'): PriceTable
    {
        $langId = Language::idByCode($locale) ?? Language::idByCode('vi');
        $projectId = ProjectContext::id() ?? $priceable->getAttribute('project_id');

        $table = PriceTable::query()->firstOrNew([
            'priceable_type' => $priceable->getMorphClass(),
            'priceable_id' => $priceable->getKey(),
        ]);
        $table->fill([
            'project_id' => $projectId,
            'currency' => strtoupper((string) ($payload['currency'] ?? $priceable->getAttribute('currency') ?? 'VND')),
            'unit' => $payload['unit'] ?? PriceTable::UNIT_PERSON,
            'notes' => $payload['notes'] ?? null,
        ]);
        $table->save();

        $variantIdMap = $this->syncVariants($table, $payload['variants'] ?? [], $langId, $projectId, $locale);
        $this->syncPeriods($table, $payload['periods'] ?? [], $variantIdMap, $projectId);

        $priceable->unsetRelation('priceTable');
        $this->maybeFillPriceFrom($priceable, $table);

        return $this->loadTable($priceable) ?? $table;
    }

    public function minAmount(Model $priceable): ?float
    {
        $table = $this->loadTable($priceable);
        if (! $table) {
            return null;
        }

        $today = Carbon::today();
        $min = null;
        foreach ($table->periods as $period) {
            if (! $period->is_active || $period->ends_on->lt($today)) {
                continue;
            }
            foreach ($period->rates as $rate) {
                $amount = (float) $rate->amount;
                $min = $min === null ? $amount : min($min, $amount);
            }
        }

        return $min;
    }

    public function loadTable(Model $priceable): ?PriceTable
    {
        if (! method_exists($priceable, 'priceTable')) {
            return null;
        }

        if ($priceable->relationLoaded('priceTable') && $priceable->priceTable) {
            $table = $priceable->priceTable;
            if (! $table->relationLoaded('variants')) {
                $table->load(['variants.translations', 'periods.rates']);
            }

            return $table;
        }

        return PriceTable::query()
            ->where('priceable_type', $priceable->getMorphClass())
            ->where('priceable_id', $priceable->getKey())
            ->with(['variants.translations', 'periods.rates'])
            ->first();
    }

    /** @return list<array<string, mixed>> */
    public function guestTypesPayload(string $locale = 'vi'): array
    {
        return PriceGuestType::query()
            ->active()
            ->with('translations')
            ->get()
            ->map(fn (PriceGuestType $g) => [
                'id' => $g->id,
                'code' => $g->code,
                'name' => $g->translation($locale)?->name ?? $g->code,
                'description' => $g->translation($locale)?->description,
                'age_min' => $g->age_min,
                'age_max' => $g->age_max,
                'sort' => $g->sort,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function suggestedVariants(Model $priceable, ?PriceTable $table, string $locale): array
    {
        $existing = [];
        foreach ($table?->variants ?? [] as $variant) {
            $existing[$variant->source.':'.(int) $variant->source_id] = true;
            $existing['code:'.$variant->code] = true;
        }

        $out = [];
        if ($priceable instanceof Package) {
            $cabins = $priceable->relationLoaded('cabinTypes')
                ? $priceable->cabinTypes
                : $priceable->cabinTypes()->with('translations')->get();
            foreach ($cabins as $cabin) {
                /** @var PackageCabinType $cabin */
                $key = PriceVariant::SOURCE_CABIN.':'.$cabin->id;
                if (isset($existing[$key])) {
                    continue;
                }
                $name = (string) ($cabin->translation($locale)?->name ?? 'Cabin #'.$cabin->id);
                $out[] = [
                    'code' => Str::slug($name) ?: 'cabin-'.$cabin->id,
                    'name' => $name,
                    'description' => $cabin->translation($locale)?->description,
                    'source' => PriceVariant::SOURCE_CABIN,
                    'source_id' => $cabin->id,
                ];
            }
        }

        if ($priceable instanceof Service) {
            $options = $priceable->relationLoaded('options')
                ? $priceable->options
                : $priceable->options()->with('translations')->get();
            foreach ($options as $option) {
                /** @var ServiceOption $option */
                $key = PriceVariant::SOURCE_SERVICE_OPTION.':'.$option->id;
                if (isset($existing[$key])) {
                    continue;
                }
                $name = (string) ($option->name ?: ($option->code ?: 'Option #'.$option->id));
                $code = $option->code ?: (Str::slug($name) ?: 'option-'.$option->id);
                if (isset($existing['code:'.$code])) {
                    continue;
                }
                $out[] = [
                    'code' => $code,
                    'name' => $name,
                    'description' => $option->description,
                    'source' => PriceVariant::SOURCE_SERVICE_OPTION,
                    'source_id' => $option->id,
                ];
            }
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, int> code => id
     */
    protected function syncVariants(PriceTable $table, array $rows, ?int $langId, ?int $projectId, string $locale): array
    {
        $keep = [];
        $map = [];
        $sort = 0;

        if ($rows === []) {
            $rows = $this->suggestedVariants($table->priceable, $table, $locale);
            if ($rows === []) {
                $rows = [[
                    'code' => 'standard',
                    'name' => 'Tiêu chuẩn',
                    'source' => PriceVariant::SOURCE_CUSTOM,
                    'sort' => 0,
                    'is_active' => true,
                ]];
            }
        }

        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                $code = Str::slug((string) ($row['name'] ?? '')) ?: 'option-'.($sort + 1);
            }

            $variant = null;
            if (! empty($row['id'])) {
                $variant = $table->variants->firstWhere('id', (int) $row['id']);
            }
            if (! $variant) {
                $variant = $table->variants->firstWhere('code', $code)
                    ?: new PriceVariant(['price_table_id' => $table->id]);
            }

            $variant->fill([
                'project_id' => $projectId,
                'price_table_id' => $table->id,
                'code' => $code,
                'source' => $row['source'] ?? PriceVariant::SOURCE_CUSTOM,
                'source_id' => $row['source_id'] ?? null,
                'sort' => $row['sort'] ?? $sort,
                'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
            ]);
            $variant->save();

            if ($langId && filled($row['name'] ?? null)) {
                $variant->translations()->updateOrCreate(
                    ['language_id' => $langId],
                    [
                        'project_id' => $projectId,
                        'name' => $row['name'],
                        'description' => $row['description'] ?? null,
                    ],
                );
            }

            $keep[] = $variant->id;
            $map[$code] = $variant->id;
            $map['id:'.$variant->id] = $variant->id;
            $sort++;
        }

        $table->variants()->whereNotIn('id', $keep)->each(function (PriceVariant $variant) {
            $variant->rates()->delete();
            $variant->delete();
        });

        $table->unsetRelation('variants');
        $table->load('variants.translations');

        return $map;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, int>  $variantIdMap
     */
    protected function syncPeriods(PriceTable $table, array $rows, array $variantIdMap, ?int $projectId): void
    {
        $keep = [];
        $sort = 0;

        foreach ($rows as $row) {
            [$starts, $ends, $year] = $this->normalizePeriodDates($row);

            $period = null;
            if (! empty($row['id'])) {
                $period = $table->periods->firstWhere('id', (int) $row['id']);
            }
            $period = $period ?: new PricePeriod(['price_table_id' => $table->id]);

            $period->fill([
                'project_id' => $projectId,
                'price_table_id' => $table->id,
                'kind' => $row['kind'],
                'starts_on' => $starts,
                'ends_on' => $ends,
                'year' => $year,
                'label' => $row['label'] ?? null,
                'is_promo' => (bool) ($row['is_promo'] ?? false),
                'priority' => (int) ($row['priority'] ?? 0),
                'sort' => $row['sort'] ?? $sort,
                'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
            ]);
            $period->save();

            $this->syncRates($period, $row['rates'] ?? [], $variantIdMap, $projectId);
            $keep[] = $period->id;
            $sort++;
        }

        $table->periods()->whereNotIn('id', $keep)->each(function (PricePeriod $period) {
            $period->rates()->delete();
            $period->delete();
        });
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, int>  $variantIdMap
     */
    protected function syncRates(PricePeriod $period, array $rows, array $variantIdMap, ?int $projectId): void
    {
        $keep = [];
        $guestTypes = PriceGuestType::query()->get()->keyBy('id');
        $guestByCode = PriceGuestType::query()->get()->keyBy('code');

        foreach ($rows as $row) {
            $variantId = $this->resolveVariantId($row, $variantIdMap);
            $guestTypeId = $this->resolveGuestTypeId($row, $guestTypes, $guestByCode);
            if (! $variantId || ! $guestTypeId) {
                continue;
            }

            $rate = PriceRate::query()->updateOrCreate(
                [
                    'period_id' => $period->id,
                    'variant_id' => $variantId,
                    'guest_type_id' => $guestTypeId,
                ],
                [
                    'project_id' => $projectId,
                    'amount' => $row['amount'],
                    'compare_at_amount' => $row['compare_at_amount'] ?? null,
                    'min_qty' => $row['min_qty'] ?? null,
                    'max_qty' => $row['max_qty'] ?? null,
                ],
            );
            $keep[] = $rate->id;
        }

        $period->rates()->whereNotIn('id', $keep)->delete();
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $variantIdMap
     */
    protected function resolveVariantId(array $row, array $variantIdMap): ?int
    {
        if (! empty($row['variant_id'])) {
            $id = (int) $row['variant_id'];

            return $variantIdMap['id:'.$id] ?? (in_array($id, $variantIdMap, true) ? $id : null);
        }
        $code = trim((string) ($row['variant_code'] ?? ''));
        if ($code !== '' && isset($variantIdMap[$code])) {
            return $variantIdMap[$code];
        }

        return count($variantIdMap) === 1 ? (int) reset($variantIdMap) : null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  \Illuminate\Support\Collection<int, PriceGuestType>  $guestTypes
     * @param  \Illuminate\Support\Collection<string, PriceGuestType>  $guestByCode
     */
    protected function resolveGuestTypeId(array $row, $guestTypes, $guestByCode): ?int
    {
        if (! empty($row['guest_type_id']) && $guestTypes->has((int) $row['guest_type_id'])) {
            return (int) $row['guest_type_id'];
        }
        $code = trim((string) ($row['guest_type_code'] ?? ''));
        if ($code !== '' && $guestByCode->has($code)) {
            return (int) $guestByCode->get($code)->id;
        }
        if ($guestTypes->count() === 1) {
            return (int) $guestTypes->first()->id;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0: string, 1: string, 2: ?int}
     */
    protected function normalizePeriodDates(array $row): array
    {
        $kind = (string) ($row['kind'] ?? PricePeriod::KIND_RANGE);

        if ($kind === PricePeriod::KIND_YEAR) {
            $year = (int) ($row['year'] ?? Carbon::parse((string) ($row['starts_on'] ?? 'now'))->year);
            if ($year < 2000) {
                throw new InvalidArgumentException('Năm giá không hợp lệ.');
            }

            return [$year.'-01-01', $year.'-12-31', $year];
        }

        $starts = (string) ($row['starts_on'] ?? '');
        $ends = (string) ($row['ends_on'] ?? $starts);
        if ($starts === '') {
            throw new InvalidArgumentException('Thiếu ngày bắt đầu của giai đoạn giá.');
        }
        if ($kind === PricePeriod::KIND_DATE) {
            $ends = $starts;
        }
        if ($ends < $starts) {
            throw new InvalidArgumentException('Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.');
        }

        return [$starts, $ends, null];
    }

    protected function maybeFillPriceFrom(Model $priceable, PriceTable $table): void
    {
        if ($priceable->getAttribute('price_from') !== null) {
            return;
        }

        $min = $this->minAmount($priceable);
        if ($min === null) {
            $table->load(['periods.rates']);
            $min = $this->minAmount($priceable);
        }
        if ($min === null) {
            return;
        }

        $priceable->forceFill([
            'price_from' => $min,
            'currency' => $table->currency,
        ])->save();
    }

    protected function rateFor(?PricePeriod $period, int $variantId, int $guestTypeId): ?PriceRate
    {
        if (! $period) {
            return null;
        }

        return $period->rates->first(
            fn (PriceRate $r) => (int) $r->variant_id === $variantId && (int) $r->guest_type_id === $guestTypeId
        );
    }

    /** @return array<string, mixed> */
    protected function serializeVariant(PriceVariant $variant, string $locale): array
    {
        return [
            'id' => $variant->id,
            'code' => $variant->code,
            'name' => $variant->translation($locale)?->name ?? $variant->code,
            'description' => $variant->translation($locale)?->description,
            'source' => $variant->source,
            'source_id' => $variant->source_id,
            'sort' => $variant->sort,
            'is_active' => $variant->is_active,
        ];
    }

    /** @return array<string, mixed> */
    protected function serializePeriodAdmin(PricePeriod $period): array
    {
        return [
            'id' => $period->id,
            'kind' => $period->kind,
            'starts_on' => $period->starts_on->toDateString(),
            'ends_on' => $period->ends_on->toDateString(),
            'year' => $period->year,
            'label' => $period->label,
            'is_promo' => $period->is_promo,
            'priority' => $period->priority,
            'sort' => $period->sort,
            'is_active' => $period->is_active,
            'rates' => $period->rates->map(fn (PriceRate $r) => [
                'id' => $r->id,
                'variant_id' => $r->variant_id,
                'guest_type_id' => $r->guest_type_id,
                'amount' => (float) $r->amount,
                'compare_at_amount' => $r->compare_at_amount !== null ? (float) $r->compare_at_amount : null,
                'min_qty' => $r->min_qty,
                'max_qty' => $r->max_qty,
            ])->values()->all(),
        ];
    }

    public function guestAgeLabel(PriceGuestType $guest): ?string
    {
        $min = $guest->age_min;
        $max = $guest->age_max;
        if ($min !== null && $max !== null) {
            return $min.'–'.$max.' tuổi';
        }
        if ($min !== null) {
            return 'Từ '.$min.' tuổi';
        }
        if ($max !== null) {
            return 'Đến '.$max.' tuổi';
        }

        return null;
    }

    public function periodDateLabel(PricePeriod $period): string
    {
        if ($period->kind === PricePeriod::KIND_YEAR && $period->year) {
            return 'Năm '.$period->year;
        }
        $from = $period->starts_on->format('d/m/Y');
        if ($period->kind === PricePeriod::KIND_DATE || $period->starts_on->equalTo($period->ends_on)) {
            return $from;
        }

        return $from.' – '.$period->ends_on->format('d/m/Y');
    }

    public function formatMoney(float $amount, string $currency = 'VND'): string
    {
        $code = strtoupper(trim($currency));
        if ($code === '' || $code === 'VND') {
            return function_exists('format_price_plain')
                ? format_price_plain($amount)
                : number_format($amount, 0, ',', '.').' ₫';
        }

        if (function_exists('format_price_plain') && function_exists('currency_manager')) {
            return currency_manager()->format($amount, $code, false);
        }

        return number_format($amount, 2).' '.$code;
    }
}
