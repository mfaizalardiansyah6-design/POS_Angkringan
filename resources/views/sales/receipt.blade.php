<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->sale_number }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="receipt-page">
        <div class="receipt-toolbar">
            <a href="{{ route('sales.show', $sale) }}" class="btn btn--outline btn--sm">
                <x-icon name="arrow-left" />
                Kembali
            </a>
            <button type="button" class="btn btn--primary btn--sm" data-print>
                <x-icon name="printer" />
                Cetak
            </button>
        </div>

        <article class="receipt-paper receipt-paper--{{ $paperWidth === '58' ? '58' : '80' }}">
            <header class="receipt-store">
                <div class="receipt-store__name">{{ $storeName }}</div>
                @if ($storeAddress)
                    <div class="receipt-store__line">{{ $storeAddress }}</div>
                @endif
                @if ($storePhone)
                    <div class="receipt-store__line">{{ $storePhone }}</div>
                @endif
            </header>

            <hr class="receipt-rule">

            <div class="receipt-meta">
                <div class="receipt-meta__row">
                    <span>No.</span>
                    <span>{{ $sale->sale_number }}</span>
                </div>
                <div class="receipt-meta__row">
                    <span>Waktu</span>
                    <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="receipt-meta__row">
                    <span>Kasir</span>
                    <span>{{ $sale->cashier?->name ?? '—' }}</span>
                </div>
                <div class="receipt-meta__row">
                    <span>Tipe</span>
                    <span>{{ $sale->order_type->label() }}</span>
                </div>
            </div>

            @if ($sale->status === \App\Enums\SaleStatus::Voided)
                <div class="receipt-status">Dibatalkan</div>
            @elseif ($sale->isPendingQris())
                <div class="receipt-status receipt-status--waiting">Menunggu pembayaran QRIS</div>
            @endif

            <hr class="receipt-rule">

            <div>
                @foreach ($sale->items as $item)
                    <div class="receipt-item">
                        <div class="receipt-item__name">{{ $item->product_name }}</div>
                        <div class="receipt-item__qty">
                            {{ $item->quantity }} × {{ rupiah($item->unit_price) }}
                        </div>
                        <div class="receipt-item__total">{{ rupiah($item->line_total) }}</div>
                    </div>
                @endforeach
            </div>

            <hr class="receipt-rule">

            <div class="receipt-total">
                <div class="receipt-total__row">
                    <span>Subtotal</span>
                    <span>{{ rupiah($sale->subtotal) }}</span>
                </div>
                @if ((float) $sale->discount_amount > 0)
                    <div class="receipt-total__row">
                        <span>Diskon</span>
                        <span>− {{ rupiah($sale->discount_amount) }}</span>
                    </div>
                @endif
                <div class="receipt-total__row receipt-total__row--grand">
                    <span>Total</span>
                    <span>{{ rupiah($sale->total_amount) }}</span>
                </div>
                <div class="receipt-total__row">
                    <span>Metode</span>
                    <span>{{ $sale->payment_method->label() }}</span>
                </div>
                @if ($sale->payment_method === \App\Enums\PaymentMethod::Cash)
                    <div class="receipt-total__row">
                        <span>Tunai</span>
                        <span>{{ rupiah($sale->amount_paid) }}</span>
                    </div>
                    <div class="receipt-total__row">
                        <span>Kembali</span>
                        <span>{{ rupiah($sale->change_amount) }}</span>
                    </div>
                @endif
                @if ($sale->payment_reference)
                    <div class="receipt-total__row">
                        <span>Ref.</span>
                        <span>{{ $sale->payment_reference }}</span>
                    </div>
                @endif
                @if ((float) $sale->refund_amount > 0)
                    <div class="receipt-total__row">
                        <span>Refund</span>
                        <span>{{ rupiah($sale->refund_amount) }}</span>
                    </div>
                @endif
            </div>

            @if ($receiptFooter)
                <hr class="receipt-rule">
                <div class="receipt-note">{{ $receiptFooter }}</div>
            @endif

            <div class="receipt-thanks">Terima kasih.</div>
        </article>
    </main>
</body>
</html>
