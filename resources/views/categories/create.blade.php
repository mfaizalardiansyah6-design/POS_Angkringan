@extends('layouts.app')

@section('title', 'Tambah kategori')

@section('content')
    <x-page-header
        eyebrow="Kategori"
        title="Tambah kategori."
        description="Buat grup produk untuk mempermudah pencarian di kasir."
    >
        <x-slot:actions>
            <a href="{{ route('categories.index') }}" class="btn btn--outline">
                <x-icon name="chevron-left" />
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel" style="margin-top: 16px; max-width: 720px;">
        <div class="panel__head">
            <h2 class="panel__title">Form kategori</h2>
        </div>
        <form method="POST" action="{{ route('categories.store') }}" class="form">
            @csrf
            <div class="field">
                <label for="name" class="label">Nama kategori *</label>
                <input id="name" name="name" class="input" value="{{ old('name') }}" required>
                <x-field-error name="name" />
            </div>
            <div class="field">
                <label for="description" class="label">Deskripsi</label>
                <textarea id="description" name="description" class="textarea">{{ old('description') }}</textarea>
                <x-field-error name="description" />
            </div>
            <div class="grid grid--two">
                <div class="field">
                    <label for="sort_order" class="label">Urutan</label>
                    <input id="sort_order" type="number" name="sort_order" class="input" value="{{ old('sort_order', 0) }}" min="0">
                    <x-field-error name="sort_order" />
                </div>
                <div class="field">
                    <label class="label">Status</label>
                    <label class="toggle">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <span class="toggle__track"></span>
                        <span class="toggle__label">Aktif</span>
                    </label>
                </div>
            </div>
            <div class="form__actions">
                <a href="{{ route('categories.index') }}" class="btn btn--ghost">Batal</a>
                <button type="submit" class="btn btn--primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
