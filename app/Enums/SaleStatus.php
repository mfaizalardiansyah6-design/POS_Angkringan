<?php

namespace App\Enums;

enum SaleStatus: string
{
    case PendingPayment = 'pending_payment';
    case Completed = 'completed';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu bayar',
            self::Completed => 'Selesai',
            self::Voided => 'Dibatalkan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::PendingPayment => 'warning',
            self::Completed => 'success',
            self::Voided => 'danger',
        };
    }
}
