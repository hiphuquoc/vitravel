<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CruiseType;
use Illuminate\Validation\Rule;

/**
 * Chuẩn hóa / kiểm tra packages.cruise_type ↔ cruise_types.slug (theo project).
 */
final class CruiseTypeSlug
{
    public const MAX_LENGTH = 64;

    /**
     * Map slug / tên loại → slug hợp lệ trong project hiện tại, hoặc null nếu không khớp.
     */
    public static function resolve(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $lower = mb_strtolower($value);

        $match = CruiseType::query()
            ->get(['id', 'slug', 'name'])
            ->first(function (CruiseType $type) use ($value, $lower) {
                return $type->slug === $value
                    || mb_strtolower($type->slug) === $lower
                    || mb_strtolower(trim((string) $type->name)) === $lower;
            });

        return $match?->slug;
    }

    /**
     * Rule validate cruise_type cho package cruise.
     *
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function packageRules(bool $required): array
    {
        $rules = ['string', 'max:'.self::MAX_LENGTH];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        $rules[] = Rule::exists(CruiseType::class, 'slug');

        return $rules;
    }
}
