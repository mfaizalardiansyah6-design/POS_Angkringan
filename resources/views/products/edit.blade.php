@extends('layouts.app')

@section('title', 'Edit produk')

@section('content')
    <x-page-header eyebrow="Produk" title="Edit produk." description="Perbarui informasi, harga, dan pengaturan stok.">
        <x-slot:actions><a href="{{ route('products.index') }}" class="btn btn--outline"><x-icon name="chevron-left" /> Kembali</a></x-slot:actions>
    </x-page-header>

    <div class="panel" style="margin-top: 16px; max-width: 880px;">
        <div class="panel__head"><h2 class="panel__title">{{ $product->name }}</h2></div>
        <form method="POST" action="{{ route('products.update', $product) }}" class="form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid--two">
                <div class="field">
                    <label for="name" class="label">Nama produk *</label>
                    <input id="name" name="name" class="input" value="{{ old('name', $product->name) }}" required>
                    <x-field-error name="name" />
                </div>
                <div class="field">
                    <label for="sku" class="label">SKU</label>
                    <input id="sku" name="sku" class="input" value="{{ old('sku', $product->sku) }}">
                    <x-field-error name="sku" />
                </div>
            </div>
            <div class="grid grid--two">
                <div class="field">
                    <label for="category_id" class="label">Kategori *</label>
                    <select id="category_id" name="category_id" class="select" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <x-field-error name="category_id" />
                </div>
                <div class="field">
                    <label for="unit" class="label">Satuan *</label>
                    <input id="unit" name="unit" class="input" value="{{ old('unit', $product->unit) }}" required>
                    <x-field-error name="unit" />
                </div>
            </div>
            <div class="grid grid--two">
                <div class="field">
                    <label for="selling_price" class="label">Harga jual *</label>
                    <input id="selling_price" name="selling_price" class="input" value="{{ old('selling_price', $product->selling_price) }}" required>
                    <x-field-error name="selling_price" />
                </div>
                <div class="field">
                    <label for="cost_price" class="label">Harga modal *</label>
                    <input id="cost_price" name="cost_price" class="input" value="{{ old('cost_price', $product->cost_price) }}" required>
                    <x-field-error name="cost_price" />
                </div>
            </div>
            <div class="grid grid--three">
                <div class="field">
                    <label class="label">Lacak stok</label>
                    <label class="toggle">
                        <input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', $product->track_stock))>
                        <span class="toggle__track"></span>
                        <span class="toggle__label">Ya</span>
                    </label>
                </div>
                <div class="field">
                    <label for="minimum_stock" class="label">Stok minimum</label>
                    <input id="minimum_stock" type="number" name="minimum_stock" class="input" value="{{ old('minimum_stock', $product->minimum_stock) }}" min="0">
                    <x-field-error name="minimum_stock" />
                </div>
                <div class="field">
                    <label class="label">Status</label>
                    <label class="toggle">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                        <span class="toggle__track"></span>
                        <span class="toggle__label">Aktif</span>
                    </label>
                </div>
            </div>
            <div class="field">
                <label for="image" class="label">Gambar produk</label>
                <input id="image" type="file" name="image" class="input">
                @if ($product->image_path)
                    <label class="toggle" style="margin-top: 8px;">
                        <input type="checkbox" name="remove_image" value="1">
                        <span class="toggle__track"></span>
                        <span class="toggle__label">Hapus gambar saat ini</span>
                    </label>
                @endif
                <x-field-error name="image" />
            </div>
            <div class="form__actions">
                <a href="{{ route('products.index') }}" class="btn btn--ghost">Batal</a>
                <button type="submit" class="btn btn--primary">Simpan perubahan</button>
            </div>
        </form>
    </div>
@endsection
