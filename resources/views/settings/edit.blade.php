@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
    <x-page-header eyebrow="Sistem" title="Pengaturan." description="Konfigurasi toko." />
    <div class="panel" style="margin-top: 16px; max-width: 720px;">
        <div class="panel__head"><h2 class="panel__title">Pengaturan toko</h2></div>
        <form method="POST" action="{{ route('settings.update') }}" class="form">
            @csrf
            @method('PUT')
            <div class="field"><label for="store_name" class="label">Nama toko *</label><input id="store_name" name="store_name" class="input" value="{{ old('store_name', $settings['store_name'] ?? '') }}" required><x-field-error name="store_name" /></div>
            <div class="field"><label for="store_address" class="label">Alamat</label><textarea id="store_address" name="store_address" class="textarea">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea></div>
            <div class="field"><label for="store_phone" class="label">Telepon</label><input id="store_phone" name="store_phone" class="input" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}"></div>
            <div class="form__actions"><button type="submit" class="btn btn--primary">Simpan</button></div>
        </form>
    </div>
@endsection
