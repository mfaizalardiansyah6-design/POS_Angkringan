@extends('layouts.app')

@section('title', 'Edit pengguna')

@section('content')
    <x-page-header eyebrow="Pengguna" title="Edit pengguna." description="Perbarui data, peran, atau status akun.">
        <x-slot:actions><a href="{{ route('users.index') }}" class="btn btn--outline"><x-icon name="chevron-left" /> Kembali</a></x-slot:actions>
    </x-page-header>
    <div class="panel" style="margin-top: 16px; max-width: 720px;">
        <div class="panel__head"><h2 class="panel__title">{{ $user->name }}</h2></div>
        <form method="POST" action="{{ route('users.update', $user) }}" class="form">
            @csrf
            @method('PUT')
            <div class="field"><label for="name" class="label">Nama *</label><input id="name" name="name" class="input" value="{{ old('name', $user->name) }}" required><x-field-error name="name" /></div>
            <div class="field"><label for="username" class="label">Username *</label><input id="username" name="username" class="input" value="{{ old('username', $user->username) }}" required><x-field-error name="username" /></div>
            <div class="grid grid--two">
                <div class="field"><label for="password" class="label">Password baru (kosongkan jika tidak diubah)</label><input id="password" type="password" name="password" class="input"><x-field-error name="password" /></div>
                <div class="field"><label for="password_confirmation" class="label">Konfirmasi password</label><input id="password_confirmation" type="password" name="password_confirmation" class="input"></div>
            </div>
            <div class="grid grid--two">
                <div class="field">
                    <label for="role" class="label">Peran *</label>
                    <select id="role" name="role" class="select" required>
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <x-field-error name="role" />
                </div>
                <div class="field">
                    <label class="label">Status</label>
                    <label class="toggle"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))><span class="toggle__track"></span><span class="toggle__label">Aktif</span></label>
                </div>
            </div>
            <div class="form__actions"><a href="{{ route('users.index') }}" class="btn btn--ghost">Batal</a><button type="submit" class="btn btn--primary">Simpan perubahan</button></div>
        </form>
    </div>
@endsection
