@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <x-page-header
        eyebrow="Kelola"
        title="Produk."
        description="Atur harga jual, biaya, stok, dan status produk."
    >
        <x-slot:actions>
            <a href="{{ route('products.create') }}" class="btn btn--primary">
                <x-icon name="plus" />
                Tambah produk
            </a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('products.index') }}" class="filter-bar">
        <div class="search-input">
            <x-icon name="search" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="Cari nama atau SKU...">
        </div>
        <div class="field" style="max-width: 200px;">
            <label for="category" class="label">Kategori</label>
            <select id="category" name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(($filters['category'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="max-width: 150px;">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="select">
                <option value="">Semua</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <label class="toggle" style="align-self: flex-end; margin-bottom: 4px;">
            <input type="checkbox" name="low" value="1" @checked(request()->boolean('low'))>
            <span class="toggle__track"></span>
            <span class="toggle__label">Stok menipis</span>
        </label>
        <button type="submit" class="btn btn--primary btn--sm" style="height: 44px;"><x-icon name="filter" /> Terapkan</button>
        <a href="{{ route('products.index') }}" class="btn btn--ghost btn--sm" style="height: 44px;">Reset</a>
    </form>

    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Daftar produk</h2>
            <span class="panel__meta">{{ $products->total() }} produk</span>
        </div>
        @if ($products->isEmpty())
            <x-empty-state icon="package" title="Tidak ada produk" description="Tambahkan produk pertama atau ubah filter.">
                <a href="{{ route('products.create') }}" class="btn btn--primary btn--sm"><x-icon name="plus" /> Tambah produk</a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga jual</th>
                            <th>Harga modal</th>
                            <th class="num">Stok</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <span class="table__primary">{{ $product->name }}</span>
                                    @if ($product->sku)<div class="table__sub">SKU: {{ $product->sku }}</div>@endif
                                </td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td>{{ rupiah($product->selling_price) }}</td>
                                <td>{{ rupiah($product->cost_price) }}</td>
                                <td class="num">{{ $product->track_stock ? $product->stock_qty : 'N/A' }}</td>
                                <td><x-badge :tone="$product->is_active ? 'success' : 'neutral'">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                <td class="num">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="{{ route('products.edit', $product) }}" class="icon-btn"><x-icon name="edit" /></a>
                                        <form method="POST" action="{{ route('products.toggle', $product) }}">@csrf @method('PATCH')<button class="icon-btn"><x-icon name="refresh" /></button></form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 12px 18px;">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
