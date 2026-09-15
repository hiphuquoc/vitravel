<?php

declare(strict_types=1);

namespace App\Support\StayCatalog;

/**
 * Chuẩn hoá chuỗi để gộp alias tiện ích / POI / địa danh.
 */
final class StayText
{
    public static function fold(string $value): string
    {
        $value = trim(mb_strtolower($value));
        if ($value === '') {
            return '';
        }
        $value = str_replace(['đ', 'Đ'], ['d', 'd'], $value);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (! is_string($ascii) || $ascii === '') {
            $ascii = $value;
        }
        $ascii = strtolower($ascii);
        $ascii = preg_replace('/[^a-z0-9]+/', '', $ascii) ?? $ascii;

        return $ascii;
    }

    /**
     * Fold tiện ích: gộp “Wi-Fi” / “Wifi miễn phí” / “Free WiFi”.
     * Không dùng cho POI / địa danh (giữ số, không cắt hậu tố).
     */
    public static function foldAmenity(string $value): string
    {
        $fold = self::fold($value);
        if ($fold === '') {
            return '';
        }
        $fold = preg_replace('/^(free|mienphi|complimentary|gratuit)/', '', $fold) ?? $fold;
        $fold = preg_replace('/(mienphi|free|complimentary|gratuit|included)$/', '', $fold) ?? $fold;

        return $fold;
    }

    public static function collapse(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        return $value;
    }
}
