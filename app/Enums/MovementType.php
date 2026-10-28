<?php

namespace App\Enums;

enum MovementType: string
{
    case Initial = 'initial';
    case Sale = 'sale';
    case StockIn = 'stock_in';
    case Adjustment = 'adjustment';
    case Waste = 'waste';
    case VoidReturn = 'void_return';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Stok awal',
            self::Sale => 'Penjualan',
            self::StockIn => 'Stok masuk',
            self::Adjustment => 'Koreksi stok',
            self::Waste => 'Produk rusak',
            self::VoidReturn => 'Pembatalan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Initial, self::StockIn, self::VoidReturn => 'success',
            self::Sale => 'neutral',
            self::Adjustment => 'warning',
            self::Waste => 'danger',
        };
    }
}
