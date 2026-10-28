@extends('layouts.app')

@section('title', 'Meja kasir')

@section('content')
    <x-page-header
        eyebrow="Kasir · {{ tanggal_id(now()) }}"
        title="Meja kasir."
        description="Buka shift, catat pesanan, dan pastikan laci kas sesuai sebelum pulang."
    >
        <x-slot:actions>
            <a href="{{ route('shifts.index') }}" class="btn btn--outline">
                <x-icon name="clock" />
                Shift saya
            </a>
            <a href="{{ route('pos.index') }}" class="btn btn--primary">
                <x-icon name="pos" />
                Mulai jualan
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($pendingQris > 0)
        <div class="notice notice--warning reveal" style="margin-bottom: 16px;">
            <x-icon name="qr" />
            <span>
                Ada <strong>{{ $pendingQris }} transaksi QRIS</strong> yang menunggu konfirmasi.
                Buka transaksi, pastikan dana masuk, lalu konfirmasi.
                <a href="{{ route('sales.index', ['status' => 'pending_payment']) }}" style="text-decoration: underline; text-underline-offset: 3px;">Lihat transaksi</a>.
            </span>
        </div>
    @endif

    @if ($shift)
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <section class="ledger-hero reveal">
                    <div class="ledger-hero__label">Perkiraan kas di laci</div>
                    <div class="ledger-hero__amount">{{ rupiah($summary['expected_cash']) }}</div>
                    <div class="ledger-hero__note">
                        Kas awal {{ rupiah($summary['opening_cash']) }} · shift dibuka {{ $shift->opened_at->format('H:i') }}
                    </div>

                    <div class="ledger-hero__grid">
                        <div class="ledger-hero__item">
                            <div class="ledger-hero__item-label">Penjualan tunai</div>
                            <div class="ledger-hero__item-value">{{ rupiah($summary['cash_sales']) }}</div>
                        </div>
                        <div class="ledger-hero__item">
                            <div class="ledger-hero__item-label">Pengeluaran tunai</div>
                            <div class="ledger-hero__item-value">{{ rupiah($summary['cash_expenses']) }}</div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="xl:col-span-5">
                <div class="panel" style="height: 100%;">
                    <div class="panel__head">
                        <h2 class="panel__title">Ringkasan Anda</h2>
                        <span class="panel__meta">Hari ini</span>
                    </div>
                    <div class="panel__body">
                        <div class="summary-grid">
                            <div class="summary-grid__item">
                                <div class="summary-grid__label">Transaksi selesai</div>
                                <div class="summary-grid__value">{{ $todayCount }}</div>
                            </div>
                            <div class="summary-grid__item">
                                <div class="summary-grid__label">Omzet selesai</div>
                                <div class="summary-grid__value">{{ rupiah($todayTotal) }}</div>
                            </div>
                            <div class="summary-grid__item">
                                <div class="summary-grid__label">QRIS shift</div>
                                <div class="summary-grid__value">{{ rupiah($summary['qris_sales']) }}</div>
                            </div>
                        </div>

                        <div class="notice" style="margin-top: 14px;">
                            <x-icon name="info" />
                            <span>QRIS tidak masuk ke laci kas. Angka laci hanya menghitung tunai.</span>
                        </div>

                        <div style="display: grid; gap: 8px; margin-top: 16px;">
                            <a href="{{ route('pos.index') }}" class="btn btn--ember btn--block">
                                <x-icon name="pos" />
                                Lanjut jualan
                            </a>
                            <a href="{{ route('shifts.index') }}" class="btn btn--outline btn--block">
                                <x-icon name="clock" />
                                Tutup shift & hitung kas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <div class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">Shift belum dibuka</h2>
                        <span class="badge badge--warning">Perlu dibuka</span>
                    </div>
                    <div class="panel__body">
                        <div class="page-title" style="font-size: clamp(2.1rem, 4vw, 3rem);">
                            Mulai dari kas awal.
                        </div>
                        <p class="page-sub">
                            Masukkan uang tunai yang Anda taruh di laci sebelum transaksi pertama.
                            Angka ini menjadi dasar rekonsiliasi saat shift ditutup.
                        </p>

                        <form method="POST" action="{{ route('shifts.open') }}" style="margin-top: 20px; max-width: 360px;">
                            @csrf
                            <div class="field">
                                <label for="opening_cash" class="label">Kas awal</label>
                                <input
                                    id="opening_cash"
                                    name="opening_cash"
                                    type="number"
                                    min="0"
                                    step="500"
                                    value="{{ old('opening_cash', 100000) }}"
                                    class="input @error('opening_cash') input--error @enderror"
                                    inputmode="numeric"
                                    required
                                >
                                <x-field-error name="opening_cash" />
                            </div>

                            <button type="submit" class="btn btn--ember" style="margin-top: 14px;">
                                <x-icon name="check" />
                                Buka shift sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-5">
                <div class="panel" style="height: 100%;">
                    <div class="panel__head">
                        <h2 class="panel__title">Alur singkat</h2>
                    </div>
                    <div class="panel__body">
                        <ul class="list">
                            <li class="list__item">
                                <span class="empty__icon" style="width: 34px; height: 34px; margin: 0; border-radius: 9px;">1</span>
                                <div class="list__body">
                                    <div class="list__title">Buka shift</div>
                                    <div class="list__meta">Catat kas awal di laci.</div>
                                </div>
                            </li>
                            <li class="list__item">
                                <span class="empty__icon" style="width: 34px; height: 34px; margin: 0; border-radius: 9px;">2</span>
                                <div class="list__body">
                                    <div class="list__title">Catat pesanan</div>
                                    <div class="list__meta">Pilih menu, metode bayar, lalu selesaikan.</div>
                                </div>
                            </li>
                            <li class="list__item">
                                <span class="empty__icon" style="width: 34px; height: 34px; margin: 0; border-radius: 9px;">3</span>
                                <div class="list__body">
                                    <div class="list__title">Tutup shift</div>
                                    <div class="list__meta">Hitung kas fisik dan cocokkan selisihnya.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Transaksi Anda</h2>
            <a href="{{ route('sales.index') }}" class="panel-link">
                Semua
                <x-icon name="arrow-right" />
            </a>
        </div>

        @if ($recentSales->isEmpty())
            <x-empty-state icon="receipt" title="Meja masih bersih" description="Transaksi yang Anda buat hari ini akan tampil di sini.">
                <a href="{{ route('pos.index') }}" class="btn btn--primary btn--sm">Buka kasir</a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaksi</th>
                            <th>Metode</th>
                            <th class="num">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="table__primary">{{ $sale->sale_number }}</a>
                                    <div class="table__sub">{{ tanggal_id($sale->created_at, true) }} · {{ $sale->order_type->label() }}</div>
                                </td>
                                <td>{{ $sale->payment_method->label() }}</td>
                                <td class="num">{{ rupiah($sale->total_amount) }}</td>
                                <td><x-badge :tone="$sale->status->tone()">{{ $sale->status->label() }}</x-badge></td>
                                <td class="num">
                                    <a href="{{ route('sales.show', $sale) }}" class="icon-btn" aria-label="Lihat transaksi">
                                        <x-icon name="chevron-right" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
