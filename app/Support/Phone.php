<?php

namespace App\Support;

class Phone
{
    public const BD_MOBILE = '/^01[3-9]\d{8}$/';

    /** "+880 1712-345678" / "০১৭১২৩৪৫৬৭৮" → "01712345678" */
    public static function normalize(?string $phone): string
    {
        $p = strtr((string) $phone, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $p = preg_replace('/\D/', '', $p) ?? '';
        if (str_starts_with($p, '880')) {
            $p = substr($p, 2);
        } elseif (str_starts_with($p, '88') && strlen($p) === 13) {
            $p = substr($p, 2);
        }

        return $p;
    }

    public static function isValid(?string $phone): bool
    {
        return (bool) preg_match(self::BD_MOBILE, self::normalize($phone));
    }
}
