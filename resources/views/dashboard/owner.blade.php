@extends('layouts.app')

@section('title', 'Ringkasan')

@section('content')
    <x-page-header
        eyebrow="Pemilik · {{ tanggal_id(now()) }}"
        title="Angka malam ini."
        description="Pantau arus kas, stok, dan transaksi yang perlu perhatian sebelum menutup warung."
    >
        <x-slot:actions>
            <a href="{{ route('reports.index') }}" class="btn btn--outline">
                <x-icon name="chart" />
                Laporan
            </a>
            <a href="{{ route('pos.index') }}" class="btn btn--primary">
                <x-icon name="pos" />
                Buka kasir
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-7">
            <section class="ledger-hero reveal">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                    <div class="ledger-hero__label">Pendapatan selesai hari ini</div>
                    @if ($pendingQris > 0)
                        <a href="{{ route('sales.index', ['status' => 'pending_payment']) }}" class="badge badge--dark">
                            {{ $pendingQris }} QRIS menunggu
                        </a>
                    @endif
                </div>

                <div class="ledger-hero__amount">{{ rupiah($todayRevenue) }}</div>
                <div class="ledger-hero__note">
                    {{ $todayCount }} transaksi selesai
                    @if ($todayCount > 0)
                        · rata-rata {{ rupiah((float) $todayRevenue / $todayCount) }}
                    @endif
                    @if ((float) $refundsToday > 0)
                        · refund {{ rupiah($refundsToday) }}
                    @endif
                </div>

                <div class="ledger-hero__grid">
                    <div class="ledger-hero__item">
                        <div class="ledger-hero__item-label">Tunai</div>
                        <div class="ledger-hero__item-value">{{ rupiah($todayCash) }}</div>
                    </div>
                    <div class="ledger-hero__item">
                        <div class="ledger-hero__item-label">QRIS</div>
                        <div class="ledger-hero__item-value">{{ rupiah($todayQris) }}</div>
                    </div>
                </div>
            </section>
        </div>

        <div class="xl:col-span-5">
            <div class="panel" style="height: 100%;">
                <div class="panel__head">
                    <h2 class="panel__title">Perlu dicek</h2>
                    <span class="panel__meta">{{ now()->format('H:i') }} WIB</span>
                </div>
                <div class="panel__body">
                    <a href="{{ route('sales.index', ['status' => 'pending_payment']) }}" class="list__item" style="padding-top: 0;">
                        <span class="empty__icon" style="width: 36px; height: 36px; margin: 0; border-radius: 10px;">
                            <x-icon name="qr" />
                        </span>
                        <span class="list__body">
                            <span class="list__title">QRIS menunggu konfirmasi</span>
                            <span class="list__meta">Pastikan dana masuk sebelum menyelesaikan transaksi.</span>
                        </span>
                        <span class="list__aside">{{ $pendingQris }}</span>
                    </a>

                    <a href="{{ route('products.index', ['low' => 1]) }}" class="list__item">
                        <span class="empty__icon" style="width: 36px; height: 36px; margin: 0; border-radius: 10px;">
                            <x-icon name="alert" />
                        </span>
                        <span class="list__body">
                            <span class="list__title">Stok di batas minimum</span>
                            <span class="list__meta">
                                {{ $lowStock->isEmpty() ? 'Semua stok aman.' : $lowStock->pluck('name')->take(3)->join(', ') }}
                            </span>
                        </span>
                        <span class="list__aside">{{ $lowStock->count() }}</span>
                    </a>

                    <a href="{{ route('shifts.index') }}" class="list__item">
                        <span class="empty__icon" style="width: 36px; height: 36px; margin: 0; border-radius: 10px;">
                            <x-icon name="clock" />
                        </span>
                        <span class="list__body">
                            <span class="list__title">Shift terakhir</span>
                            <span class="list__meta">
                                @if ($recentShifts->first())
                                    {{ $recentShifts->first()->user?->name }} ·
                                    {{ $recentShifts->first()->isOpen() ? 'masih berjalan' : 'ditutup '.$recentShifts->first()->closed_at?->format('H:i') }}
                                @else
                                    Belum ada shift yang dibuka.
                                @endif
                            </span>
                        </span>
                        <x-icon name="chevron-right" style="width: 14px; height: 14px; color: #9c8c77;" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-12" style="margin-top: 16px;">
        <div class="xl:col-span-7">
            <div class="panel">
                <div class="panel__head">
                    <div>
                        <h2 class="panel__title">Tujuh malam terakhir</h2>
                        <p class="panel__meta" style="margin-top: 3px;">Penjualan selesai per hari</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="panel-link">
                        Rincian
                        <x-icon name="arrow-up-right" />
                    </a>
                </div>
                <div class="panel__body">
                    <div class="bar-chart" role="img" aria-label="Grafik penjualan tujuh hari terakhir">
                        @foreach ($trend as $day)
                            @php($height = $day['value'] > 0 ? max(4, round(($day['value'] / $trendMax) * 100)) : 2)
                            <div class="bar-chart__col" title="{{ $day['date'] }}: {{ rupiah($day['value']) }}">
                                <div class="bar-chart__track">
                                    <div class="bar-chart__bar" style="height: {{ $height }}%;"></div>
                                </div>
                                <div class="bar-chart__label">{{ $day['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-5">
            <div class="panel" style="height: 100%;">
                <div class="panel__head">
                    <h2 class="panel__title">Menu paling dicari</h2>
                    <span class="panel__meta">Hari ini</span>
                </div>
                <div class="panel__body">
                    @if ($topProduct)
                        <div class="metric-label">Jumlah terjual</div>
                        <div class="metric-value" style="font-size: clamp(2.1rem, 4vw, 3rem);">
                            {{ $topProduct->product_name }}
                        </div>
                        <div class="metric-note">
                            {{ (int) $topProduct->qty }} porsi / item terjual hari ini.
                        </div>
                        <div class="notice" style="margin-top: 18px;">
                            <x-icon name="info" />
                            <span>Gunakan angka ini untuk menyiapkan bahan sebelum jam ramai berikutnya.</span>
                        </div>
                    @else
                        <x-empty-state icon="bowl" title="Belum ada penjualan" description="Menu terlaris akan muncul setelah transaksi pertama hari ini selesai." />
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-12" style="margin-top: 16px;">
        <div class="xl:col-span-8">
            <div class="panel">
                <div class="panel__head">
                    <h2 class="panel__title">Transaksi terakhir</h2>
                    <a href="{{ route('sales.index') }}" class="panel-link">
                        Semua transaksi
                        <x-icon name="arrow-right" />
                    </a>
                </div>

                @if ($recentSales->isEmpty())
                    <x-empty-state icon="receipt" title="Belum ada transaksi" description="Transaksi akan tampil di sini begitu kasir menyelesaikan pembayaran.">
                        <a href="{{ route('pos.index') }}" class="btn btn--primary btn--sm">Buka kasir</a>
                    </x-empty-state>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Kasir</th>
                                    <th>Metode</th>
                                    <th class="num">Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('sales.show', $sale) }}" class="table__primary">{{ $sale->sale_number }}</a>
                                            <div class="table__sub">{{ tanggal_id($sale->created_at, true) }}</div>
                                        </td>
                                        <td>{{ $sale->cashier?->name }}</td>
                                        <td>{{ $sale->payment_method->label() }}</td>
                                        <td class="num">{{ rupiah($sale->total_amount) }}</td>
                                        <td><x-badge :tone="$sale->status->tone()">{{ $sale->status->label() }}</x-badge></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="xl:col-span-4">
            <div class="panel" style="height: 100%;">
                <div class="panel__head">
                    <h2 class="panel__title">Stok menipis</h2>
                    <a href="{{ route('products.index', ['low' => 1]) }}" class="panel-link">Kelola</a>
                </div>

                @if ($lowStock->isEmpty())
                    <div class="panel__body">
                        <div class="notice">
                            <x-icon name="check" />
                            <span>Semua produk berstok di atas batas minimum.</span>
                        </div>
                    </div>
                @else
                    <div class="panel__body" style="padding-top: 12px; padding-bottom: 12px;">
                        <ul class="list">
                            @foreach ($lowStock as $product)
                                <li class="list__item">
                                    <span class="product-thumb">
                                        @if ($product->imageUrl())
                                            <img src="{{ $product->imageUrl() }}" alt="">
                                        @else
                                            {{ initials($product->name) }}
                                        @endif
                                    </span>
                                    <div class="list__body">
                                        <div class="list__title">{{ $product->name }}</div>
                                        <div class="list__meta">{{ $product->category?->name ?? 'Tanpa kategori' }}</div>
                                    </div>
                                    <div class="list__aside" style="color: {{ $product->stock_qty === 0 ? 'var(--color-danger)' : 'var(--color-warn)' }};">
                                        {{ $product->stock_qty }} {{ $product->unit }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
