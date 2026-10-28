<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final class Money
{
    public static function of(mixed $value): BigDecimal
    {
        if ($value instanceof BigDecimal) {
            return $value->toScale(2, RoundingMode::HalfUp);
        }

        return BigDecimal::of((string) ($value ?? '0'))->toScale(2, RoundingMode::HalfUp);
    }

    public static function format(mixed $value): string
    {
        $amount = (float) (string) self::of($value);
        $formatted = number_format(abs($amount), 0, ',', '.');

        return ($amount < 0 ? '-Rp ' : 'Rp ').$formatted;
    }
}
