<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helper chuyển đổi và định dạng cự ly khoảng cách (m <-> km) cho Chỗ nghỉ & Địa danh.
 */
final class StayDistance
{
    /**
     * Parse chuỗi cự ly ("1,6 km", "1.6 km", "500 m", "500m", "10 km"...) về số nguyên Mét.
     */
    public static function parseMeters(string|int|float|null $input): int
    {
        if ($input === null || $input === '') {
            return 0;
        }

        if (is_numeric($input)) {
            $num = (float) $input;
            return $num < 100 ? (int) round($num * 1000) : (int) round($num);
        }

        $str = trim(mb_strtolower((string) $input));
        $str = str_replace([',', ' '], ['.', ''], $str); // "1,6 km" -> "1.6km"

        if (preg_match('/^([0-9.]+)(km|kilo|m|met|meter)?$/i', $str, $matches)) {
            $value = (float) ($matches[1] ?? 0);
            $unit = $matches[2] ?? '';

            if ($unit === 'km' || $unit === 'kilo') {
                return (int) round($value * 1000);
            }

            if ($unit === 'm' || $unit === 'met' || $unit === 'meter') {
                return (int) round($value);
            }

            // Nếu không có đơn vị, nếu < 50 coi là km, >= 50 coi là m
            return $value < 50 ? (int) round($value * 1000) : (int) round($value);
        }

        return 0;
    }

    /**
     * Định dạng khoảng cách theo mét ra chuỗi hiển thị chuẩn UX/UI (ví dụ: "500 m" hoặc "1,6 km").
     */
    public static function format(int|float|string|null $meters, string $locale = 'vi'): string
    {
        $m = is_int($meters) ? $meters : self::parseMeters($meters);
        if ($m <= 0) {
            return '';
        }

        if ($m < 1000) {
            return $m . ' m';
        }

        $km = $m / 1000;
        // Nếu là số nguyên km (vd 7.0) -> "7 km"
        // Nếu có số thập phân -> "1,6 km" (tiếng Việt) hoặc "1.6 km" (tiếng Anh)
        if (floor($km) == $km) {
            return (int) $km . ' km';
        }

        $formatted = number_format($km, 1, $locale === 'vi' ? ',' : '.', '');
        // Bỏ đuôi ,0 nếu có
        $formatted = rtrim(rtrim($formatted, '0'), $locale === 'vi' ? ',' : '.');

        return $formatted . ' km';
    }
}
