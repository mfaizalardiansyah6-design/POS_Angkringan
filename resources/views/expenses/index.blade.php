@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
    <x-page-header eyebrow="Kelola" title="Pengeluaran." description="Catat pengeluaran kas untuk rekonsiliasi shift." />
    <div class="grid" style="grid-template-columns: 1fr 1.2fr; gap: 16px; margin-top: 16px; align-items: start;">
        <div class="panel">
            <div class="panel__head"><h2 class="panel__title">Tambah pengeluaran</h2></div>
            <form method="POST" action="{{ route('expenses.store') }}" class="form">
                @csrf
                <div class="field">
                    <label for="category" class="label">Kategori *</label>
                    <input id="category" name="category" class="input" value="{{ old('category') }}" required>
                    <x-field-error name="category" />
                </div>
                <div class="field">
                    <label for="description" class="label">Deskripsi *</label>
                    <input id="description" name="description" class="input" value="{{ old('description') }}" required>
                    <x-field-error name="description" />
                </div>
                <div class="field">
                    <label for="amount" class="label">Jumlah *</label>
                    <input id="amount" name="amount" type="number" min="1" step="1" class="input" value="{{ old('amount') }}" required>
                    <x-field-error name="amount" />
                </div>
                <div class="field">
                    <label for="payment_method" class="label">Metode pembayaran *</label>
                    <select id="payment_method" name="payment_method" class="input" required>
                        <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                        <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                    </select>
                    <x-field-error name="payment_method" />
                </div>
                <div class="field">
                    <label for="expense_date" class="label">Tanggal *</label>
                    <input id="expense_date" type="date" name="expense_date" class="input" value="{{ old('expense_date', now()->toDateString()) }}" required>
                    <x-field-error name="expense_date" />
                </div>
                <div class="field">
                    <label for="note" class="label">Catatan</label>
                    <textarea id="note" name="note" class="textarea">{{ old('note') }}</textarea>
                    <x-field-error name="note" />
                </div>
                <button type="submit" class="btn btn--primary">Simpan</button>
            </form>
        </div>
        <div class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Riwayat pengeluaran</h2>
                <span class="panel__meta">{{ $expenses->total() }} entri</span>
            </div>
            @if ($expenses->isEmpty())
                <x-empty-state icon="wallet" title="Belum ada pengeluaran" description="Catat pengeluaran pertama." />
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th class="num">Jumlah</th>
                                <th>Metode</th>
                                <th>Oleh</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $e)
                                <tr>
                                    <td>{{ tanggal_id($e->expense_date) }}</td>
                                    <td>{{ $e->category }}</td>
                                    <td>{{ $e->description }}</td>
                                    <td class="num">{{ rupiah($e->amount) }}</td>
                                    <td>{{ $e->payment_method?->label() ?? $e->payment_method }}</td>
                                    <td>{{ $e->user?->name ?? '-' }}</td>
                                    <td class="table__sub">{{ $e->note ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding: 12px 18px;">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
@endsection
