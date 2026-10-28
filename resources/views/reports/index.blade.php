@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <x-page-header
        eyebrow="Analitik"
        title="Laporan."
        description="Ringkasan penjualan, laba, dan operasional berdasarkan periode."
    />

    <form method="GET" action="{{ route('reports.index') }}" class="filter-bar reveal" style="margin-top: 16px;">
        <div class="field" style="max-width: 160px;">
            <label for="from" class="label">Dari tanggal</label>
            <input
                id="from"
                type="date"
                name="from"
                value="{{ $from->format('Y-m-d') }}"
                class="input"
            >
        </div>

        <div class="field" style="max-width: 160px;">
            <label for="to" class="label">Sampai tanggal</label>
            <input
                id="to"
                type="date"
                name="to"
                value="{{ $to->format('Y-m-d') }}"
                class="input"
            >
        </div>

        <button type="submit" class="btn btn--primary btn--sm" style="height: 44px;">
            <x-icon name="filter" />
            Terapkan
        </button>

        <a href="{{ route('reports.index') }}" class="btn btn--ghost btn--sm" style="height: 44px;">Reset</a>
    </form>

    <div class="notice reveal-delay" style="margin-top: 16px;">
        <x-icon name="info" />
        <span>
            Periode: <strong>{{ tanggal_id($from) }}</strong> s/d <strong>{{ tanggal_id($to) }}</strong> ·
            Laba kotor = omzet − modal produk terjual ·
            Laba bersih = laba kotor − pengeluaran tercatat
        </span>
    </div>

    {{-- Hero metrics --}}
    <div class="ledger-hero reveal-delay" style="margin-top: 20px;">
        <div class="ledger-hero__label">Total Omzet Penjualan</div>
        <div class="ledger-hero__amount">{{ rupiah($revenue) }}</div>
        <div class="ledger-hero__note">
            Dari {{ $transactionCount }} transaksi selesai · Rata-rata {{ rupiah($avgTransaction) }} per transaksi
        </div>

        <div class="ledger-hero__grid">
            <div class="ledger-hero__item">
                <div class="ledger-hero__item-label">Laba kotor</div>
                <div class="ledger-hero__item-value">{{ rupiah($grossProfit) }}</div>
            </div>
            <div class="ledger-hero__item">
                <div class="ledger-hero__item-label">Laba bersih estimasi</div>
                <div class="ledger-hero__item-value">{{ rupiah($netProfit) }}</div>
            </div>
        </div>
    </div>

    {{-- Payment breakdown --}}
    <div class="stat-strip reveal-delay-2" style="margin-top: 16px;">
        <div class="stat-strip__item">
            <div class="stat-strip__label">Pembayaran tunai</div>
            <div class="stat-strip__value">{{ rupiah($cash) }}</div>
        </div>
        <div class="stat-strip__item">
            <div class="stat-strip__label">Pembayaran QRIS</div>
            <div class="stat-strip__value">{{ rupiah($qris) }}</div>
        </div>
        <div class="stat-strip__item">
            <div class="stat-strip__label">Pengeluaran</div>
            <div class="stat-strip__value">{{ rupiah($expenses) }}</div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2" style="margin-top: 16px;">
        {{-- Trend chart --}}
        @if (count($trend) > 0)
            <div class="panel reveal">
                <div class="panel__head">
                    <h2 class="panel__title">Trend penjualan harian</h2>
                    <span class="panel__meta">{{ count($trend) }} hari</span>
                </div>
                <div class="panel__body">
                    <div class="bar-chart bar-chart--scroll">
                        @foreach ($trend as $day)
                            <div class="bar-chart__col">
                                <div class="bar-chart__track">
                                    <div
                                        class="bar-chart__bar"
                                        style="height: {{ $trendMax > 0 ? ($day['value'] / $trendMax * 100) : 0 }}%;"
                                        title="{{ $day['label'] }}: {{ rupiah($day['value']) }}"
                                    ></div>
                                </div>
                                <div class="bar-chart__label">{{ $day['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Top products --}}
        <div class="panel reveal">
            <div class="panel__head">
                <h2 class="panel__title">Produk terlaris</h2>
                <span class="panel__meta">Top {{ $topProducts->count() }}</span>
            </div>

            @if ($topProducts->isEmpty())
                <x-empty-state
                    icon="package"
                    title="Belum ada penjualan"
                    description="Data produk terlaris akan muncul setelah ada transaksi selesai."
                />
            @else
                <div class="panel__body">
                    <ul class="list">
                        @foreach ($topProducts as $product)
                            <li class="list__item">
                                <div class="list__body">
                                    <div class="list__title">{{ $product->product_name }}</div>
                                    <div class="list__meta">{{ $product->quantity }} item terjual</div>
                                </div>
                                <div class="list__aside">{{ rupiah($product->revenue) }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2" style="margin-top: 16px;">
        {{-- Shifts --}}
        <div class="panel reveal">
            <div class="panel__head">
                <h2 class="panel__title">Shift dalam periode</h2>
                <span class="panel__meta">{{ $shifts->count() }} shift</span>
            </div>

            @if ($shifts->isEmpty())
                <x-empty-state
                    icon="clock"
                    title="Tidak ada shift"
                    description="Belum ada shift yang dibuka dalam periode ini."
                />
            @else
                <div class="panel__body">
                    <ul class="list">
                        @foreach ($shifts as $shift)
                            <li class="list__item">
                                <div class="list__body">
                                    <div class="list__title">{{ $shift->user?->name ?? 'Kasir' }}</div>
                                    <div class="list__meta">
                                        {{ $shift->opened_at->format('d/m/Y H:i') }}
                                        @if ($shift->status === \App\Enums\ShiftStatus::Closed)
                                            · ditutup {{ $shift->closed_at?->format('H:i') ?? '—' }}
                                        @else
                                            · <x-badge tone="success">Berjalan</x-badge>
                                        @endif
                                    </div>
                                </div>
                                <div class="list__aside">
                                    @if ($shift->status === \App\Enums\ShiftStatus::Closed && $shift->difference_cash != 0)
                                        <span class="tiny muted">Selisih</span><br>
                                        <span style="color: {{ (float) $shift->difference_cash < 0 ? 'var(--color-danger)' : 'var(--color-moss)' }};">
                                            {{ rupiah($shift->difference_cash) }}
                                        </span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($shifts->count() > 0)
                    <div class="panel__foot">
                        <a href="{{ route('shifts.index') }}" class="panel-link">
                            Lihat semua shift
                            <x-icon name="arrow-right" />
                        </a>
                    </div>
                @endif
            @endif
        </div>

        {{-- Low stock --}}
        <div class="panel reveal">
            <div class="panel__head">
                <h2 class="panel__title">Stok menipis</h2>
                <span class="panel__meta">{{ $lowStock->count() }} produk</span>
            </div>

            @if ($lowStock->isEmpty())
                <x-empty-state
                    icon="package"
                    title="Stok aman"
                    description="Tidak ada produk dengan stok di bawah atau sama dengan minimum."
                />
            @else
                <div class="panel__body">
                    <ul class="list">
                        @foreach ($lowStock as $product)
                            <li class="list__item">
                                <div class="product-thumb">
                                    @if ($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($product->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="list__body">
                                    <div class="list__title">{{ $product->name }}</div>
                                    <div class="list__meta">Minimum: {{ $product->minimum_stock }} {{ $product->unit }}</div>
                                </div>
                                <div class="list__aside">
                                    <span class="tiny muted">Tersisa</span><br>
                                    <span style="color: {{ $product->stock_qty <= 0 ? 'var(--color-danger)' : 'var(--color-warn)' }}; font-weight: 600;">
                                        {{ $product->stock_qty }} {{ $product->unit }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="panel__foot">
                    <a href="{{ route('inventory.index') }}" class="panel-link">
                        Kelola inventaris
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if ((float) $refunds > 0)
        <div class="notice notice--warning reveal" style="margin-top: 16px;">
            <x-icon name="alert" />
            <span>
                Total refund dalam periode ini: <strong>{{ rupiah($refunds) }}</strong> dari transaksi yang dibatalkan.
                Jumlah ini sudah dikurangkan dari perhitungan laba.
            </span>
        </div>
    @endif
@endsection
