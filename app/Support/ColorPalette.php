<?php

namespace App\Support;

class ColorPalette
{
    public static function shades(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            $hex = 'dc2626';
        }

        [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));

        $black = [0, 0, 0];

        return [
            50  => self::blend($r, $g, $b, 255, 255, 255, 0.90),
            100 => self::blend($r, $g, $b, 255, 255, 255, 0.80),
            200 => self::blend($r, $g, $b, 255, 255, 255, 0.65),
            300 => self::blend($r, $g, $b, 255, 255, 255, 0.48),
            400 => self::blend($r, $g, $b, 255, 255, 255, 0.30),
            500 => self::blend($r, $g, $b, 255, 255, 255, 0.12),
            600 => self::hex($r, $g, $b),
            700 => self::blend($r, $g, $b, $black[0], $black[1], $black[2], 0.18),
            800 => self::blend($r, $g, $b, $black[0], $black[1], $black[2], 0.40),
            900 => self::blend($r, $g, $b, $black[0], $black[1], $black[2], 0.62),
            950 => self::blend($r, $g, $b, $black[0], $black[1], $black[2], 0.80),
        ];
    }

    private static function blend(int $r, int $g, int $b, int $tr, int $tg, int $tb, float $amount): string
    {
        $r = (int) round($r + (($tr - $r) * $amount));
        $g = (int) round($g + (($tg - $g) * $amount));
        $b = (int) round($b + (($tb - $b) * $amount));

        return self::hex($r, $g, $b);
    }

    private static function hex(int $r, int $g, int $b): string
    {
        return '#'.sprintf('%02x%02x%02x', $r, $g, $b);
    }
}