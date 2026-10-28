@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <x-page-header eyebrow="Sistem" title="Pengguna." description="Kelola akun pemilik dan kasir.">
        <x-slot:actions><a href="{{ route('users.create') }}" class="btn btn--primary"><x-icon name="plus" /> Tambah pengguna</a></x-slot:actions>
    </x-page-header>
    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head"><h2 class="panel__title">Daftar pengguna</h2><span class="panel__meta">{{ $users->count() }} pengguna</span></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><span class="table__primary">{{ $user->name }}</span></td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->role->label() }}</td>
                            <td><x-badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="num">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('users.edit', $user) }}" class="icon-btn"><x-icon name="edit" /></a>
                                    <form method="POST" action="{{ route('users.toggle', $user) }}">@csrf @method('PATCH')<button class="icon-btn"><x-icon name="refresh" /></button></form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
