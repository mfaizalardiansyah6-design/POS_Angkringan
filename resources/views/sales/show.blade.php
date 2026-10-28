@extends('layouts.app')

@section('title', $sale->sale_number)

@section('content')
    <div style="margin-bottom: 14px;">
        <a href="{{ route('sales.index') }}" class="btn btn--ghost btn--sm">
            <x-icon name="arrow-left" />
            Semua transaksi
        </a>
    </div>

    <x-page-header
        eyebrow="{{ tanggal_id($sale->created_at, true) }} · {{ $sale->order_type->label() }}"
        title="{{ $sale->sale_number }}"
        description="Transaksi oleh {{ $sale->cashier?->name ?? 'kasir' }} pada shift {{ $sale->shift?->opened_at?->format('H:i') ?? '—' }}."
    >
        <x-slot:actions>
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn--outline">
                <x-icon name="printer" />
                Cetak struk
            </a>
            <x-badge :tone="$sale->status->tone()">{{ $sale->status->label() }}</x-badge>
        </x-slot:actions>
    </x-page-header>

    @if ($sale->isPendingQris())
        <div class="notice notice--warning reveal" style="margin-bottom: 16px;">
            <x-icon name="qr" />
            <span>
                Transaksi QRIS ini <strong>belum menambah pendapatan dan belum memotong stok</strong>.
                Konfirmasi hanya setelah dana benar-benar masuk.
            </span>
        </div>
    @endif

    @if ($sale->status === \App\Enums\SaleStatus::Voided)
        <div class="notice notice--danger reveal" style="margin-bottom: 16px;">
            <x-icon name="ban" />
            <span>
                Transaksi dibatalkan {{ $sale->cancelled_at ? tanggal_id($sale->cancelled_at, true) : '' }}
                oleh {{ $sale->cancelledBy?->name ?? 'sistem' }}.
                Stok dan kas sudah dikembalikan sesuai metode pembayaran.
            </span>
        </div>
    @endif

    <div class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-8">
            <div class="panel">
                <div class="panel__head">
                    <h2 class="panel__title">Rincian pesanan</h2>
                    <span class="panel__meta">{{ $sale->items->sum('quantity') }} item</span>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th class="num">Harga</th>
                                <th class="num">Qty</th>
                                <th class="num">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>
                                        <div class="table__primary">{{ $item->product_name }}</div>
                                        @if ($item->product_sku)
                                            <div class="table__sub">{{ $item->product_sku }}</div>
                                        @endif
                                    </td>
                                    <td class="num">{{ rupiah($item->unit_price) }}</td>
                                    <td class="num">{{ $item->quantity }}</td>
                                    <td class="num">{{ rupiah($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="panel__body" style="border-top: 1px solid var(--color-line);">
                    <div style="max-width: 340px; margin-left: auto; display: grid; gap: 8px;">
                        <div class="summary-row" style="color: var(--color-ink-soft);">
                            <span>Subtotal</span>
                            <span>{{ rupiah($sale->subtotal) }}</span>
                        </div>

                        @if ((float) $sale->discount_amount > 0)
                            <div class="summary-row" style="color: var(--color-ink-soft);">
                                <span>Diskon</span>
                                <span>− {{ rupiah($sale->discount_amount) }}</span>
                            </div>
                        @endif

                        <div class="summary-row" style="padding-top: 9px; border-top: 1px solid var(--color-line); font-family: var(--font-serif); font-size: 24px; letter-spacing: -0.02em;">
                            <span>Total</span>
                            <span>{{ rupiah($sale->total_amount) }}</span>
                        </div>

                        @if ($sale->payment_method === \App\Enums\PaymentMethod::Cash)
                            <div class="summary-row" style="color: var(--color-ink-soft);">
                                <span>Diterima</span>
                                <span>{{ rupiah($sale->amount_paid) }}</span>
                            </div>
                            <div class="summary-row" style="color: var(--color-ink-soft);">
                                <span>Kembalian</span>
                                <span>{{ rupiah($sale->change_amount) }}</span>
                            </div>
                        @endif

                        @if ((float) $sale->refund_amount > 0)
                            <div class="summary-row" style="color: var(--color-danger);">
                                <span>Refund</span>
                                <span>{{ rupiah($sale->refund_amount) }} ({{ $sale->refund_method?->label() }})</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="panel" style="margin-top: 16px;">
                <div class="panel__head">
                    <h2 class="panel__title">Pembayaran & jejak</h2>
                </div>
                <div class="panel__body">
                    <div class="detail-grid">
                        <div>
                            <div class="detail-label">Metode</div>
                            <div class="detail-value">{{ $sale->payment_method->label() }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Status bayar</div>
                            <div class="detail-value">
                                <x-badge :tone="$sale->payment_status->tone()">{{ $sale->payment_status->label() }}</x-badge>
                            </div>
                        </div>
                        <div>
                            <div class="detail-label">Dibuat</div>
                            <div class="detail-value">{{ tanggal_id($sale->created_at, true) }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Dibayar</div>
                            <div class="detail-value">{{ $sale->paid_at ? tanggal_id($sale->paid_at, true) : '—' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Kasir</div>
                            <div class="detail-value">{{ $sale->cashier?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Shift</div>
                            <div class="detail-value">
                                @if ($sale->shift)
                                    {{ $sale->shift->opened_at->format('H:i') }} ·
                                    {{ $sale->shift->isOpen() ? 'masih berjalan' : 'ditutup' }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        @if ($sale->payment_reference)
                            <div style="grid-column: span 2;">
                                <div class="detail-label">Referensi pembayaran</div>
                                <div class="detail-value">{{ $sale->payment_reference }}</div>
                            </div>
                        @endif
                        @if ($sale->cancellation_reason)
                            <div style="grid-column: span 2;">
                                <div class="detail-label">Alasan pembatalan</div>
                                <div class="detail-value">{{ $sale->cancellation_reason }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div class="panel">
                <div class="panel__head">
                    <h2 class="panel__title">Tindakan</h2>
                </div>
                <div class="panel__body" style="display: grid; gap: 10px;">
                    @if ($sale->isPendingQris())
                        <form
                            method="POST"
                            action="{{ route('sales.confirm', $sale) }}"
                            data-confirm="Pastikan dana QRIS sudah masuk ke rekening toko. Stok akan dipotong dan transaksi dihitung sebagai pendapatan."
                            data-confirm-title="Konfirmasi pembayaran QRIS?"
                        >
                            @csrf
                            <button type="submit" class="btn btn--ember btn--block">
                                <x-icon name="check" />
                                Konfirmasi QRIS diterima
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn--outline btn--block">
                        <x-icon name="printer" />
                        Buka struk
                    </a>

                    @if ($sale->isPendingQris() || (auth()->user()->isOwner() && $sale->canBeVoided()))
                        <div style="height: 1px; background: var(--color-line); margin: 4px 0;"></div>
                        <form
                            method="POST"
                            action="{{ route('sales.void', $sale) }}"
                            data-confirm="Transaksi akan dibatalkan. Stok dan kas akan dikembalikan satu kali dan tidak dapat diurungkan."
                            data-confirm-title="Batalkan transaksi?"
                            data-confirm-danger="1"
                        >
                            @csrf
                            <div class="field">
                                <label for="reason" class="label">Alasan pembatalan</label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="3"
                                    minlength="5"
                                    maxlength="500"
                                    class="input @error('reason') input--error @enderror"
                                    placeholder="Contoh: pelanggan batal, salah input menu."
                                    required
                                >{{ old('reason') }}</textarea>
                                <x-field-error name="reason" />
                            </div>
                            <button type="submit" class="btn btn--danger btn--block">
                                <x-icon name="ban" />
                                Batalkan transaksi
                            </button>
                        </form>
                    @elseif ($sale->status === \App\Enums\SaleStatus::Completed && auth()->user()->isOwner())
                        <div class="notice" style="font-size: 10.5px;">
                            <x-icon name="lock" />
                            <span>Shift transaksi ini sudah ditutup. Pembatalan tidak lagi tersedia.</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="panel" style="margin-top: 16px;">
                <div class="panel__head">
                    <h2 class="panel__title">Ringkasan</h2>
                </div>
                <div class="panel__body">
                    <div class="stat-strip" style="grid-template-columns: 1fr;">
                        <div class="stat-strip__item">
                            <div class="metric-label">Total transaksi</div>
                            <div class="stat-strip__value">{{ rupiah($sale->total_amount) }}</div>
                        </div>
                        <div class="stat-strip__item">
                            <div class="metric-label">Status</div>
                            <div class="stat-strip__value" style="font-size: 1.35rem; font-family: var(--font-sans); font-weight: 650; letter-spacing: -0.01em;">
                                {{ $sale->status->label() }}
                            </div>
                        </div>
                    </div>

                    <p class="page-sub" style="margin: 14px 0 0; font-size: 11.5px;">
                        Item: {{ $sale->items->sum('quantity') }} ·
                        {{ $sale->order_type->label() }} ·
                        {{ $sale->payment_method->label() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
