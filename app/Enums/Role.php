<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pemilik',
            self::Cashier => 'Kasir',
        };
    }
}
