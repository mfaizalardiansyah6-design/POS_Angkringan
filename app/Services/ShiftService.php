<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\ShiftStatus;
use App\Models\BusinessExpense;
use App\Models\CashierShift;
use App\Models\Sale;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ShiftService
{
    public function open(User $user, mixed $openingCash): CashierShift
    {
        return DB::transaction(function () use ($user, $openingCash) {
            $existing = CashierShift::query()
                ->where('user_id', $user->id)
                ->where('status', ShiftStatus::Open->value)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'opening_cash' => 'Masih ada shift yang terbuka. Tutup shift tersebut terlebih dahulu.',
                ]);
            }

            $shift = CashierShift::create([
                'user_id' => $user->id,
                'opening_cash' => Money::of($openingCash)->__toString(),
                'opened_at' => now(),
                'status' => ShiftStatus::Open,
            ]);

            ActivityLogger::log('shift.opened', $shift, 'Shift dibuka oleh '.$user->name.'.', null, [
                'opening_cash' => (string) $shift->opening_cash,
            ]);

            return $shift;
        });
    }

    public function close(User $user, CashierShift $shift, mixed $closingCash, ?string $note): CashierShift
    {
        return DB::transaction(function () use ($user, $shift, $closingCash, $note) {
            $locked = CashierShift::query()->whereKey($shift->id)->lockForUpdate()->firstOrFail();

            if ($locked->user_id !== $user->id) {
                abort(403, 'Shift ini bukan milik Anda.');
            }

            if (! $locked->isOpen()) {
                throw ValidationException::withMessages([
                    'closing_cash' => 'Shift ini sudah ditutup.',
                ]);
            }

            $summary = $this->summary($locked);
            $expected = Money::of($summary['expected_cash']);
            $closing = Money::of($closingCash);
            $difference = $closing->minus($expected);

            $locked->update([
                'closing_cash' => $closing->__toString(),
                'expected_cash' => $expected->__toString(),
                'difference_cash' => $difference->__toString(),
                'status' => ShiftStatus::Closed,
                'closing_note' => $note,
                'closed_at' => now(),
            ]);

            ActivityLogger::log('shift.closed', $locked, 'Shift ditutup oleh '.$user->name.'.', null, [
                'expected_cash' => $expected->__toString(),
                'closing_cash' => $closing->__toString(),
                'difference_cash' => $difference->__toString(),
            ]);

            return $locked;
        });
    }

    /**
     * @return array<string, string|int>
     */
    public function summary(CashierShift $shift): array
    {
        $cashSales = Sale::query()
            ->where('cashier_shift_id', $shift->id)
            ->where('payment_method', PaymentMethod::Cash->value)
            ->whereNotNull('paid_at')
            ->whereIn('status', [SaleStatus::Completed->value, SaleStatus::Voided->value])
            ->sum('total_amount');

        $cashRefunds = Sale::query()
            ->where('cashier_shift_id', $shift->id)
            ->where('payment_method', PaymentMethod::Cash->value)
            ->where('status', SaleStatus::Voided->value)
            ->sum('refund_amount');

        $cashExpenses = BusinessExpense::query()
            ->where('cashier_shift_id', $shift->id)
            ->where('payment_method', PaymentMethod::Cash->value)
            ->sum('amount');

        $salesCount = Sale::query()
            ->where('cashier_shift_id', $shift->id)
            ->where('status', SaleStatus::Completed->value)
            ->count();

        $qrisSales = Sale::query()
            ->where('cashier_shift_id', $shift->id)
            ->where('payment_method', PaymentMethod::Qris->value)
            ->where('status', SaleStatus::Completed->value)
            ->sum('total_amount');

        $expected = Money::of($shift->opening_cash)
            ->plus(Money::of($cashSales))
            ->minus(Money::of($cashExpenses))
            ->minus(Money::of($cashRefunds));

        return [
            'opening_cash' => Money::of($shift->opening_cash)->__toString(),
            'cash_sales' => Money::of($cashSales)->__toString(),
            'cash_refunds' => Money::of($cashRefunds)->__toString(),
            'cash_expenses' => Money::of($cashExpenses)->__toString(),
            'expected_cash' => $expected->__toString(),
            'sales_count' => $salesCount,
            'qris_sales' => Money::of($qrisSales)->__toString(),
        ];
    }
}
