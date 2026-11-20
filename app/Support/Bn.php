<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Bn
{
    private const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    private const MONTHS = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

    public static function digits(string|int|float|null $value): string
    {
        return str_replace(range(0, 9), self::DIGITS, (string) $value);
    }

    /** ৳১,২৯৯ (drops .00, keeps paisa when present). */
    public static function money(string|int|float|null $amount, bool $bangla = true): string
    {
        $amount = (float) $amount;
        $formatted = fmod($amount, 1.0) == 0.0 ? number_format($amount) : number_format($amount, 2);

        return '৳'.($bangla ? self::digits($formatted) : $formatted);
    }

    public static function date(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        return self::digits($date->day).' '.self::MONTHS[$date->month - 1].' '.self::digits($date->year);
    }
}
