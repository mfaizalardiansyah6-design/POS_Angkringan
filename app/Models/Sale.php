<?php

namespace App\Models;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sale_number', 'idempotency_key', 'cashier_shift_id', 'cashier_id',
    'order_type', 'status', 'payment_method', 'payment_status', 'subtotal',
    'discount_amount', 'total_amount', 'amount_paid', 'change_amount',
    'payment_reference', 'paid_at', 'cancelled_by', 'cancelled_at',
    'cancellation_reason', 'refund_amount', 'refund_method',
])]
class Sale extends Model
{
    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'status' => SaleStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'refund_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class, 'cashier_shift_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', SaleStatus::Completed->value);
    }

    public function scopePendingPayment(Builder $query): void
    {
        $query->where('status', SaleStatus::PendingPayment->value);
    }

    public function isPendingQris(): bool
    {
        return $this->status === SaleStatus::PendingPayment
            && $this->payment_method === PaymentMethod::Qris;
    }

    public function canBeVoided(): bool
    {
        return $this->status === SaleStatus::Completed && $this->shift?->isOpen();
    }
}
