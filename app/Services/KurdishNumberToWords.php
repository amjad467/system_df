<?php

namespace App\Services;

class KurdishNumberToWords
{
    private static array $ones = [
        0 => '',
        1 => 'یەک',
        2 => 'دوو',
        3 => 'سێ',
        4 => 'چوار',
        5 => 'پێنج',
        6 => 'شەش',
        7 => 'حەوت',
        8 => 'هەشت',
        9 => 'نۆ',
        10 => 'دە',
        11 => 'یانزە',
        12 => 'دوانزە',
        13 => 'سێزدە',
        14 => 'چواردە',
        15 => 'پێنجدە',
        16 => 'شازدە',
        17 => 'حەفدە',
        18 => 'هەژدە',
        19 => 'نۆزدە',
    ];

    private static array $tens = [
        2 => 'بیست',
        3 => 'سی',
        4 => 'چل',
        5 => 'پێنجا',
        6 => 'شەست',
        7 => 'حەفتا',
        8 => 'هەشتا',
        9 => 'نەوەد',
    ];

    private static array $hundreds = [
        1 => 'سەد',
        2 => 'دووسەد',
        3 => 'سێسەد',
        4 => 'چوارسەد',
        5 => 'پێنجسەد',
        6 => 'شەششەد',
        7 => 'حەوتسەد',
        8 => 'هەشتسەد',
        9 => 'نۆسەد',
    ];

    public static function convert(int|float $number): string
    {
        $number = (int)round($number);

        if ($number === 0) {
            return 'سفر دینار';
        }

        $words = [];

        // Millions
        if ($number >= 1000000) {
            $millions = (int)floor($number / 1000000);
            $number %= 1000000;
            if ($millions === 1) {
                $words[] = 'ملیۆنێک';
            } else {
                $words[] = self::convertThreeDigits($millions) . ' ملیۆن';
            }
        }

        // Thousands
        if ($number >= 1000) {
            $thousands = (int)floor($number / 1000);
            $number %= 1000;
            if ($thousands === 1) {
                $words[] = 'هەزار';
            } else {
                $words[] = self::convertThreeDigits($thousands) . ' هەزار';
            }
        }

        // Remaining 1-999
        if ($number > 0) {
            $words[] = self::convertThreeDigits($number);
        }

        return implode(' و ', $words) . ' دینار';
    }

    private static function convertThreeDigits(int $num): string
    {
        $parts = [];

        // Hundreds
        if ($num >= 100) {
            $h = (int)floor($num / 100);
            $parts[] = self::$hundreds[$h] ?? '';
            $num %= 100;
        }

        // Tens & Ones
        if ($num > 0) {
            if ($num < 20) {
                $parts[] = self::$ones[$num];
            } else {
                $t = (int)floor($num / 10);
                $o = $num % 10;
                if ($o > 0) {
                    $parts[] = self::$tens[$t] . ' و ' . self::$ones[$o];
                } else {
                    $parts[] = self::$tens[$t];
                }
            }
        }

        return implode(' و ', array_filter($parts));
    }
}
