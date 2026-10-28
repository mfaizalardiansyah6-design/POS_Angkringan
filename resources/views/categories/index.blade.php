@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <x-page-header
        eyebrow="Kelola"
        title="Kategori."
        description="Kelompokkan produk agar kasir lebih cepat mencari."
    >
        <x-slot:actions>
            <a href="{{ route('categories.create') }}" class="btn btn--primary">
                <x-icon name="plus" />
                Tambah kategori
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Daftar kategori</h2>
            <span class="panel__meta">{{ $categories->count() }} kategori</span>
        </div>

        @if ($categories->isEmpty())
            <x-empty-state icon="tag" title="Belum ada kategori" description="Buat kategori pertama untuk mengelompokkan produk.">
                <a href="{{ route('categories.create') }}" class="btn btn--primary btn--sm">
                    <x-icon name="plus" />
                    Tambah kategori
                </a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Urutan</th>
                            <th>Produk</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <span class="table__primary">{{ $category->name }}</span>
                                </td>
                                <td class="table__sub">{{ $category->description ?? '-' }}</td>
                                <td>{{ $category->sort_order }}</td>
                                <td>{{ $category->products_count }}</td>
                                <td><x-badge :tone="$category->is_active ? 'success' : 'neutral'">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                <td class="num">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="{{ route('categories.edit', $category) }}" class="icon-btn" aria-label="Edit {{ $category->name }}">
                                            <x-icon name="edit" />
                                        </a>
                                        <form method="POST" action="{{ route('categories.toggle', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="icon-btn" aria-label="Ubah status {{ $category->name }}">
                                                <x-icon name="refresh" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
