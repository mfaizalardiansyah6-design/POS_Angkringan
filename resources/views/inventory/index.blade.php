@extends('layouts.app')

@section('title', 'Inventaris')

@section('content')
    <x-page-header eyebrow="Kelola" title="Inventaris." description="Riwayat pergerakan stok." />
    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Pergerakan stok</h2>
            <span class="panel__meta">{{ $movements->total() }} entri</span>
        </div>
        @if ($movements->isEmpty())
            <x-empty-state icon="archive" title="Belum ada pergerakan" description="Pergerakan stok akan muncul di sini." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Produk</th>
                            <th>Tipe</th>
                            <th class="num">Perubahan</th>
                            <th class="num">Stok sebelum</th>
                            <th class="num">Stok sesudah</th>
                            <th>Oleh</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $m)
                            <tr>
                                <td>{{ tanggal_id($m->created_at, true) }}</td>
                                <td>{{ $m->product?->name ?? '-' }}</td>
                                <td>{{ $m->movement_type->label() }}</td>
                                <td class="num">{{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}</td>
                                <td class="num">{{ $m->stock_before }}</td>
                                <td class="num">{{ $m->stock_after }}</td>
                                <td>{{ $m->user?->name ?? '-' }}</td>
                                <td class="table__sub">{{ $m->note ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 12px 18px;">{{ $movements->links() }}</div>
        @endif
    </div>
@endsection
