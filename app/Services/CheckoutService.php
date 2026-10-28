<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CheckoutService
{
    /**
     * @param  array{items: array<int, array{product_id: int|string, quantity: int|string}>, order_type: string, payment_method: string, amount_paid?: mixed, payment_reference?: ?string, idempotency_key: string}  $data
     * @return array{0: Sale, 1: bool}
     */
    public function checkout(User $cashier, CashierShift $shift, array $data): array
    {
        $idempotencyKey = (string) $data['idempotency_key'];
        $existing = Sale::query()->where('idempotency_key', $idempotencyKey)->first();

        if ($existing) {
            return $this->existingSale($existing, $cashier);
        }

        try {
            return DB::transaction(function () use ($cashier, $shift, $data, $idempotencyKey) {
                $lockedShift = CashierShift::query()->whereKey($shift->id)->lockForUpdate()->firstOrFail();

                if (! $lockedShift->isOpen() || $lockedShift->user_id !== $cashier->id) {
                    throw ValidationException::withMessages([
                        'shift' => 'Shift tidak aktif. Buka shift terlebih dahulu.',
                    ]);
                }

                $existing = Sale::query()->where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();

                if ($existing) {
                    return $this->existingSale($existing, $cashier);
                }

                $items = $this->normalizeItems($data['items']);
                $products = Product::query()
                    ->whereIn('id', array_keys($items))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $method = PaymentMethod::tryFrom((string) ($data['payment_method'] ?? ''));
                $orderType = OrderType::tryFrom((string) ($data['order_type'] ?? ''));

                if (! $method || ! $orderType) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'Metode pembayaran atau tipe pesanan tidak valid.',
                    ]);
                }

                $subtotal = Money::of(0);
                $lines = [];

                foreach ($items as $productId => $quantity) {
                    $product = $products->get($productId);

                    if (! $product || ! $product->is_active) {
                        throw ValidationException::withMessages([
                            'items' => 'Ada produk yang tidak tersedia. Muat ulang katalog.',
                        ]);
                    }

                    if ($product->track_stock && $product->stock_qty < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => 'Stok '.$product->name.' tidak mencukupi. Tersisa '.$product->stock_qty.'.',
                        ]);
                    }

                    $lineTotal = Money::of($product->selling_price)->multipliedBy($quantity);
                    $subtotal = $subtotal->plus($lineTotal);

                    $lines[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => Money::of($product->selling_price)->__toString(),
                        'unit_cost' => Money::of($product->cost_price)->__toString(),
                        'line_total' => $lineTotal->__toString(),
                    ];
                }

                $total = $subtotal;
                $isCash = $method === PaymentMethod::Cash;
                $amountPaid = $isCash ? Money::of($data['amount_paid'] ?? 0) : Money::of(0);

                if ($isCash && $amountPaid->isLessThan($total)) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'Uang diterima kurang dari total pembayaran.',
                    ]);
                }

                $change = $isCash ? $amountPaid->minus($total) : Money::of(0);
                $now = now();

                $sale = Sale::create([
                    'sale_number' => $this->saleNumber(),
                    'idempotency_key' => $idempotencyKey,
                    'cashier_shift_id' => $lockedShift->id,
                    'cashier_id' => $cashier->id,
                    'order_type' => $orderType,
                    'status' => $isCash ? SaleStatus::Completed : SaleStatus::PendingPayment,
                    'payment_method' => $method,
                    'payment_status' => $isCash ? PaymentStatus::Paid : PaymentStatus::Pending,
                    'subtotal' => $subtotal->__toString(),
                    'discount_amount' => Money::of(0)->__toString(),
                    'total_amount' => $total->__toString(),
                    'amount_paid' => $isCash ? $amountPaid->__toString() : Money::of(0)->__toString(),
                    'change_amount' => $change->__toString(),
                    'payment_reference' => $method === PaymentMethod::Qris ? ($data['payment_reference'] ?? null) : null,
                    'paid_at' => $isCash ? $now : null,
                ]);

                foreach ($lines as $line) {
                    $sale->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'unit' => $line['product']->unit,
                        'unit_price' => $line['unit_price'],
                        'unit_cost' => $line['unit_cost'],
                        'quantity' => $line['quantity'],
                        'discount_amount' => Money::of(0)->__toString(),
                        'line_total' => $line['line_total'],
                    ]);
                }

                if ($isCash) {
                    foreach ($lines as $line) {
                        $this->decreaseStock($line['product'], $line['quantity'], $sale, $cashier, MovementType::Sale);
                    }
                }

                return [$sale->load('items', 'cashier', 'shift'), true];
            });
        } catch (UniqueConstraintViolationException $exception) {
            $existing = Sale::query()->where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $this->existingSale($existing, $cashier);
            }

            throw $exception;
        }
    }

    public function confirmQris(Sale $sale, User $user): Sale
    {
        return DB::transaction(function () use ($sale, $user) {
            $locked = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === SaleStatus::Completed) {
                return $locked->load('items', 'cashier', 'shift');
            }

            if (! $user->isOwner() && $locked->cashier_id !== $user->id) {
                abort(403, 'Anda tidak berwenang mengonfirmasi transaksi ini.');
            }

            if (! $locked->isPendingQris()) {
                throw ValidationException::withMessages([
                    'sale' => 'Transaksi ini tidak sedang menunggu pembayaran QRIS.',
                ]);
            }

            $shift = CashierShift::query()->whereKey($locked->cashier_shift_id)->lockForUpdate()->firstOrFail();

            if (! $shift->isOpen()) {
                throw ValidationException::withMessages([
                    'sale' => 'Shift transaksi ini sudah ditutup. Konfirmasi tidak dapat dilakukan.',
                ]);
            }

            $items = $locked->items()->get();
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'sale' => 'Produk pada transaksi ini sudah tidak tersedia.',
                    ]);
                }

                if ($product->track_stock && $product->stock_qty < $item->quantity) {
                    throw ValidationException::withMessages([
                        'sale' => 'Stok '.$product->name.' tidak mencukupi untuk menyelesaikan QRIS. Tersisa '.$product->stock_qty.'.',
                    ]);
                }
            }

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                $this->decreaseStock($product, $item->quantity, $locked, $user, MovementType::Sale);
            }

            $locked->update([
                'status' => SaleStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'amount_paid' => $locked->total_amount,
                'change_amount' => Money::of(0)->__toString(),
                'paid_at' => now(),
            ]);

            ActivityLogger::log('sale.qris_confirmed', $locked, 'Pembayaran QRIS dikonfirmasi untuk '.$locked->sale_number.'.', null, [
                'total_amount' => (string) $locked->total_amount,
            ]);

            return $locked->load('items', 'cashier', 'shift');
        });
    }

    public function void(Sale $sale, User $user, string $reason): Sale
    {
        return DB::transaction(function () use ($sale, $user, $reason) {
            $locked = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === SaleStatus::Voided) {
                throw ValidationException::withMessages([
                    'reason' => 'Transaksi ini sudah dibatalkan.',
                ]);
            }

            if ($locked->status === SaleStatus::PendingPayment) {
                if (! $user->isOwner() && $locked->cashier_id !== $user->id) {
                    abort(403, 'Anda tidak berwenang membatalkan transaksi ini.');
                }

                $locked->update([
                    'status' => SaleStatus::Voided,
                    'payment_status' => PaymentStatus::Failed,
                    'cancelled_by' => $user->id,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                    'refund_amount' => Money::of(0)->__toString(),
                ]);

                ActivityLogger::log('sale.cancelled_pending', $locked, 'Transaksi pending dibatalkan: '.$locked->sale_number.'.', null, [
                    'reason' => $reason,
                ]);

                return $locked->load('items', 'cashier', 'shift');
            }

            if (! $user->isOwner()) {
                abort(403, 'Hanya pemilik yang dapat membatalkan transaksi selesai.');
            }

            $shift = CashierShift::query()->whereKey($locked->cashier_shift_id)->lockForUpdate()->firstOrFail();

            if (! $shift->isOpen()) {
                throw ValidationException::withMessages([
                    'reason' => 'Shift transaksi ini sudah ditutup. Pembatalan tidak dapat dilakukan.',
                ]);
            }

            $items = $locked->items()->get();
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->track_stock) {
                    continue;
                }

                $before = $product->stock_qty;
                $after = $before + $item->quantity;
                $product->update(['stock_qty' => $after]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'sale_id' => $locked->id,
                    'movement_type' => MovementType::VoidReturn,
                    'quantity_change' => $item->quantity,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'source_key' => 'void:'.$locked->id.':product:'.$product->id,
                    'note' => 'Pembatalan '.$locked->sale_number.'.',
                ]);
            }

            $locked->update([
                'status' => SaleStatus::Voided,
                'payment_status' => PaymentStatus::Refunded,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'refund_amount' => $locked->total_amount,
                'refund_method' => $locked->payment_method,
            ]);

            ActivityLogger::log('sale.voided', $locked, 'Transaksi dibatalkan: '.$locked->sale_number.'.', null, [
                'reason' => $reason,
                'refund_amount' => (string) $locked->total_amount,
            ]);

            return $locked->load('items', 'cashier', 'shift');
        });
    }

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     * @return array<int, int>
     */
    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Keranjang masih kosong.',
            ]);
        }

        $normalized = [];

        foreach ($items as $item) {
            $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);

            if (! $productId || ! $quantity || $quantity < 1 || $quantity > 999) {
                throw ValidationException::withMessages([
                    'items' => 'Isi keranjang tidak valid.',
                ]);
            }

            $normalized[$productId] = ($normalized[$productId] ?? 0) + $quantity;
        }

        return $normalized;
    }

    private function decreaseStock(Product $product, int $quantity, Sale $sale, User $user, MovementType $type): void
    {
        if (! $product->track_stock) {
            return;
        }

        $before = $product->stock_qty;
        $after = $before - $quantity;

        if ($after < 0) {
            throw ValidationException::withMessages([
                'items' => 'Stok '.$product->name.' tidak mencukupi.',
            ]);
        }

        $product->update(['stock_qty' => $after]);

        InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'sale_id' => $sale->id,
            'movement_type' => $type,
            'quantity_change' => -$quantity,
            'stock_before' => $before,
            'stock_after' => $after,
            'source_key' => 'sale:'.$sale->id.':product:'.$product->id,
            'note' => 'Penjualan '.$sale->sale_number.'.',
        ]);
    }

    private function saleNumber(): string
    {
        do {
            $number = 'ANG-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (Sale::query()->where('sale_number', $number)->exists());

        return $number;
    }

    /**
     * @return array{0: Sale, 1: bool}
     */
    private function existingSale(Sale $sale, User $cashier): array
    {
        if ($sale->cashier_id !== $cashier->id) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Kunci transaksi sudah dipakai oleh pengguna lain.',
            ]);
        }

        return [$sale->load('items', 'cashier', 'shift'), false];
    }
}
