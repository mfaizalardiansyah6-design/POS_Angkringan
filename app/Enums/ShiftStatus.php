<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Berjalan',
            self::Closed => 'Ditutup',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'neutral',
        };
    }
}
