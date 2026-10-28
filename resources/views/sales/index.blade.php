@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    <x-page-header
        eyebrow="Riwayat"
        title="Transaksi."
        description="Cari transaksi berdasarkan nomor, status, metode bayar, atau tanggal."
    >
        <x-slot:actions>
            <a href="{{ route('pos.index') }}" class="btn btn--primary">
                <x-icon name="pos" />
                Buka kasir
            </a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('sales.index') }}" class="filter-bar">
        <div class="search-input">
            <x-icon name="search" />
            <input
                type="search"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                class="input"
                placeholder="Cari nomor transaksi…"
                autocomplete="off"
            >
        </div>

        <div class="field" style="max-width: 175px;">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="select">
                <option value="">Semua status</option>
                @foreach (\App\Enums\SaleStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field" style="max-width: 155px;">
            <label for="method" class="label">Metode</label>
            <select id="method" name="method" class="select">
                <option value="">Semua metode</option>
                @foreach (\App\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}" @selected(($filters['method'] ?? '') === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field" style="max-width: 155px;">
            <label for="from" class="label">Dari tanggal</label>
            <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input">
        </div>

        <div class="field" style="max-width: 155px;">
            <label for="to" class="label">Sampai</label>
            <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input">
        </div>

        <button type="submit" class="btn btn--primary btn--sm" style="height: 44px;">
            <x-icon name="filter" />
            Terapkan
        </button>

        <a href="{{ route('sales.index') }}" class="btn btn--ghost btn--sm" style="height: 44px;">Reset</a>
    </form>

    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Daftar transaksi</h2>
            <span class="panel__meta">{{ $sales->total() }} transaksi</span>
        </div>

        @if ($sales->isEmpty())
            <x-empty-state icon="receipt" title="Tidak ada transaksi" description="Ubah filter atau buat transaksi baru dari meja kasir.">
                <a href="{{ route('sales.index') }}" class="btn btn--outline btn--sm">Hapus filter</a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaksi</th>
                            @if (auth()->user()->isOwner())
                                <th>Kasir</th>
                            @endif
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th class="num">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="table__primary">{{ $sale->sale_number }}</a>
                                    <div class="table__sub">{{ tanggal_id($sale->created_at, true) }}</div>
                                </td>
                                @if (auth()->user()->isOwner())
                                    <td>{{ $sale->cashier?->name }}</td>
                                @endif
                                <td>{{ $sale->order_type->label() }}</td>
                                <td>{{ $sale->payment_method->label() }}</td>
                                <td class="num">{{ rupiah($sale->total_amount) }}</td>
                                <td><x-badge :tone="$sale->status->tone()">{{ $sale->status->label() }}</x-badge></td>
                                <td class="num">
                                    <a href="{{ route('sales.show', $sale) }}" class="icon-btn" aria-label="Buka {{ $sale->sale_number }}">
                                        <x-icon name="chevron-right" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 12px 16px; border-top: 1px solid var(--color-line);">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
@endsection
