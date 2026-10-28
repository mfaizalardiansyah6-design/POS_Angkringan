@extends('layouts.app')

@section('title', 'Tambah produk')

@section('content')
    <x-page-header eyebrow="Produk" title="Tambah produk." description="Isi harga jual, modal, dan pengaturan stok.">
        <x-slot:actions><a href="{{ route('products.index') }}" class="btn btn--outline"><x-icon name="chevron-left" /> Kembali</a></x-slot:actions>
    </x-page-header>

    <div class="panel" style="margin-top: 16px; max-width: 880px;">
        <div class="panel__head"><h2 class="panel__title">Form produk</h2></div>
        <form method="POST" action="{{ route('products.store') }}" class="form" enctype="multipart/form-data">
            @csrf
            <div class="grid grid--two">
                <div class="field">
                    <label for="name" class="label">Nama produk *</label>
                    <input id="name" name="name" class="input" value="{{ old('name') }}" required>
                    <x-field-error name="name" />
                </div>
                <div class="field">
                    <label for="sku" class="label">SKU</label>
                    <input id="sku" name="sku" class="input" value="{{ old('sku') }}">
                    <x-field-error name="sku" />
                </div>
            </div>
            <div class="grid grid--two">
                <div class="field">
                    <label for="category_id" class="label">Kategori *</label>
                    <select id="category_id" name="category_id" class="select" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <x-field-error name="category_id" />
                </div>
                <div class="field">
                    <label for="unit" class="label">Satuan *</label>
                    <input id="unit" name="unit" class="input" value="{{ old('unit', 'pcs') }}" required>
                    <x-field-error name="unit" />
                </div>
            </div>
            <div class="grid grid--two">
                <div class="field">
                    <label for="selling_price" class="label">Harga jual *</label>
                    <input id="selling_price" name="selling_price" class="input" value="{{ old('selling_price') }}" required>
                    <x-field-error name="selling_price" />
                </div>
                <div class="field">
                    <label for="cost_price" class="label">Harga modal *</label>
                    <input id="cost_price" name="cost_price" class="input" value="{{ old('cost_price') }}" required>
                    <x-field-error name="cost_price" />
                </div>
            </div>
            <div class="grid grid--three">
                <div class="field">
                    <label class="label">Lacak stok</label>
                    <label class="toggle">
                        <input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', true))>
                        <span class="toggle__track"></span>
                        <span class="toggle__label">Ya</span>
                    </label>
                </div>
                <div class="field">
                    <label for="stock_qty" class="label">Stok awal</label>
                    <input id="stock_qty" type="number" name="stock_qty" class="input" value="{{ old('stock_qty', 0) }}" min="0">
                    <x-field-error name="stock_qty" />
                </div>
                <div class="field">
                    <label for="minimum_stock" class="label">Stok minimum</label>
                    <input id="minimum_stock" type="number" name="minimum_stock" class="input" value="{{ old('minimum_stock', 0) }}" min="0">
                    <x-field-error name="minimum_stock" />
                </div>
            </div>
            <div class="field">
                <label for="image" class="label">Gambar produk</label>
                <input id="image" type="file" name="image" class="input">
                <x-field-error name="image" />
            </div>
            <div class="field">
                <label class="label">Status</label>
                <label class="toggle">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    <span class="toggle__track"></span>
                    <span class="toggle__label">Aktif</span>
                </label>
            </div>
            <div class="form__actions">
                <a href="{{ route('products.index') }}" class="btn btn--ghost">Batal</a>
                <button type="submit" class="btn btn--primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
