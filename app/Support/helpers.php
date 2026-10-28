<?php

use App\Support\Money;
use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    function rupiah(mixed $amount): string
    {
        return Money::format($amount);
    }
}

if (! function_exists('tanggal_id')) {
    function tanggal_id(mixed $date, bool $withTime = false): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $value = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        $formatted = $value->format('d').' '.$months[(int) $value->format('n')].' '.$value->format('Y');

        return $withTime ? $formatted.' · '.$value->format('H:i') : $formatted;
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_substr($part, 0, 1);
        }

        return mb_strtoupper($letters ?: 'A');
    }
}
