<?php

declare(strict_types=1);

namespace App\Support\StayCatalog;

/**
 * Tách địa chỉ Booking thành phần geo (không hardcode một đảo).
 *
 * @phpstan-type Geo array{
 *   raw: string,
 *   country: ?string,
 *   city: ?string,
 *   district: ?string,
 *   parts: list<string>
 * }
 */
final class StayGeoParser
{
    /**
     * @return Geo
     */
    public static function parseAddress(?string $address): array
    {
        $raw = StayText::collapse((string) $address);
        $empty = [
            'raw' => $raw,
            'country' => null,
            'city' => null,
            'district' => null,
            'parts' => [],
        ];
        if ($raw === '') {
            return $empty;
        }

        $parts = array_values(array_filter(array_map(
            static fn (string $p) => StayText::collapse($p),
            explode(',', $raw),
        ), static fn (string $p) => $p !== ''));

        $country = null;
        $filtered = [];
        foreach ($parts as $part) {
            if (preg_match('/^(việt nam|vietnam|viet nam)$/iu', $part)) {
                $country = 'VN';
                continue;
            }
            $filtered[] = $part;
        }

        $city = $filtered !== [] ? $filtered[count($filtered) - 1] : null;
        $district = count($filtered) >= 2 ? $filtered[count($filtered) - 2] : null;

        return [
            'raw' => $raw,
            'country' => $country,
            'city' => $city,
            'district' => $district,
            'parts' => $filtered,
        ];
    }

    public static function shortLabel(?string $address, string $title = ''): ?string
    {
        $geo = self::parseAddress($address);
        if ($geo['parts'] !== []) {
            return implode(', ', array_slice($geo['parts'], -3));
        }
        $title = StayText::collapse($title);

        return $title !== '' ? $title : null;
    }
}
