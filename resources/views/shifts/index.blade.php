@extends('layouts.app')

@section('title', 'Shift')

@section('content')
    <x-page-header
        eyebrow="{{ auth()->user()->isOwner() ? 'Operasional' : 'Kasir' }}"
        title="Shift & kas."
        description="Buka shift dengan kas awal, lalu tutup dengan menghitung uang fisik di laci."
    >
        <x-slot:actions>
            @if ($openShift)
                <a href="{{ route('pos.index') }}" class="btn btn--primary">
                    <x-icon name="pos" />
                    Kembali ke kasir
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($openShift && $summary)
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <section class="ledger-hero reveal">
                    <div class="ledger-hero__label">Kas seharusnya di laci</div>
                    <div class="ledger-hero__amount">{{ rupiah($summary['expected_cash']) }}</div>
                    <div class="ledger-hero__note">
                        Dibuka {{ tanggal_id($openShift->opened_at, true) }} · {{ $openShift->user?->name }}
                    </div>

                    <div class="ledger-hero__grid">
                        <div class="ledger-hero__item">
                            <div class="ledger-hero__item-label">Kas awal</div>
                            <div class="ledger-hero__item-value">{{ rupiah($summary['opening_cash']) }}</div>
                        </div>
                        <div class="ledger-hero__item">
                            <div class="ledger-hero__item-label">Penjualan tunai</div>
                            <div class="ledger-hero__item-value">{{ rupiah($summary['cash_sales']) }}</div>
                        </div>
                        <div class="ledger-hero__item">
                            <div class="ledger-hero__item-label">Pengeluaran tunai</div>
                            <div class="ledger-hero__item-value">{{ rupiah($summary['cash_expenses']) }}</div>
                        </div>
                        <div class="ledger-hero__item">
                            <div class="ledger-hero__item-label">QRIS (bukan kas laci)</div>
                            <div class="ledger-hero__item-value">{{ rupiah($summary['qris_sales']) }}</div>
                        </div>
                    </div>
                </section>

                <div class="panel" style="margin-top: 16px;">
                    <div class="panel__head">
                        <h2 class="panel__title">Rincian perhitungan</h2>
                        <span class="panel__meta">Otomatis</span>
                    </div>
                    <div class="panel__body">
                        <div class="detail-grid">
                            <div>
                                <div class="detail-label">Kas awal</div>
                                <div class="detail-value">{{ rupiah($summary['opening_cash']) }}</div>
                            </div>
                            <div>
                                <div class="detail-label">Penjualan tunai selesai</div>
                                <div class="detail-value">{{ rupiah($summary['cash_sales']) }}</div>
                            </div>
                            <div>
                                <div class="detail-label">Pengeluaran tunai</div>
                                <div class="detail-value">− {{ rupiah($summary['cash_expenses']) }}</div>
                            </div>
                            <div>
                                <div class="detail-label">Refund tunai</div>
                                <div class="detail-value">− {{ rupiah($summary['cash_refunds']) }}</div>
                            </div>
                        </div>

                        <div class="notice" style="margin-top: 16px;">
                            <x-icon name="info" />
                            <span>
                                Expected cash = kas awal + penjualan tunai selesai − pengeluaran tunai − refund tunai.
                                QRIS tidak dihitung sebagai uang di laci.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-5">
                <div class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">Tutup shift</h2>
                        <x-badge tone="success">Berjalan</x-badge>
                    </div>
                    <div class="panel__body">
                        <p class="page-sub" style="margin: 0 0 16px;">
                            Hitung uang fisik di laci, lalu masukkan nominalnya. Sistem akan menampilkan selisih
                            terhadap perhitungan di sebelah kiri.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('shifts.close', $openShift) }}"
                            data-confirm="Pastikan kas fisik sudah dihitung. Shift yang ditutup tidak dapat dibuka kembali."
                            data-confirm-title="Tutup shift sekarang?"
                            data-confirm-danger="1"
                        >
                            @csrf

                            <div class="field">
                                <label for="closing_cash" class="label">Kas fisik di laci</label>
                                <input
                                    id="closing_cash"
                                    name="closing_cash"
                                    type="number"
                                    min="0"
                                    step="500"
                                    value="{{ old('closing_cash', $summary['expected_cash']) }}"
                                    class="input @error('closing_cash') input--error @enderror"
                                    inputmode="numeric"
                                    required
                                >
                                <x-field-error name="closing_cash" />
                            </div>

                            <div class="field">
                                <label for="closing_note" class="label">Catatan (opsional)</label>
                                <textarea
                                    id="closing_note"
                                    name="closing_note"
                                    rows="3"
                                    maxlength="500"
                                    class="input @error('closing_note') input--error @enderror"
                                    placeholder="Contoh: uang kembalian kurang, ada tip."
                                >{{ old('closing_note') }}</textarea>
                                <x-field-error name="closing_note" />
                            </div>

                            <button type="submit" class="btn btn--danger btn--block">
                                <x-icon name="lock" />
                                Tutup shift & hitung selisih
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <div class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">Buka shift</h2>
                        <x-badge tone="warning">Belum aktif</x-badge>
                    </div>
                    <div class="panel__body">
                        <div class="page-title" style="font-size: clamp(2rem, 4vw, 2.8rem);">
                            Belum ada shift berjalan.
                        </div>
                        <p class="page-sub">
                            Catat kas awal sebelum transaksi pertama. Pastikan nominalnya sesuai uang yang
                            benar-benar Anda taruh di laci.
                        </p>

                        <form method="POST" action="{{ route('shifts.open') }}" style="margin-top: 20px; max-width: 380px;">
                            @csrf
                            <div class="field">
                                <label for="opening_cash" class="label">Kas awal</label>
                                <input
                                    id="opening_cash"
                                    name="opening_cash"
                                    type="number"
                                    min="0"
                                    step="500"
                                    value="{{ old('opening_cash', 100000) }}"
                                    class="input @error('opening_cash') input--error @enderror"
                                    inputmode="numeric"
                                    required
                                >
                                <x-field-error name="opening_cash" />
                            </div>

                            <button type="submit" class="btn btn--ember">
                                <x-icon name="check" />
                                Buka shift
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-5">
                <div class="panel" style="height: 100%;">
                    <div class="panel__head">
                        <h2 class="panel__title">Sebelum membuka</h2>
                    </div>
                    <div class="panel__body">
                        <ul class="list">
                            <li class="list__item">
                                <span class="empty__icon" style="width: 34px; height: 34px; margin: 0; border-radius: 9px;">
                                    <x-icon name="cash" />
                                </span>
                                <div class="list__body">
                                    <div class="list__title">Hitung uang laci</div>
                                    <div class="list__meta">Termasuk uang kembalian yang disiapkan.</div>
                                </div>
                            </li>
                            <li class="list__item">
                                <span class="empty__icon" style="width: 34px; height: 34px; margin: 0; border-radius: 9px;">
                                    <x-icon name="receipt" />
                                </span>
                                <div class="list__body">
                                    <div class="list__title">Satu shift per kasir</div>
                                    <div class="list__meta">Selesaikan shift lama sebelum membuka yang baru.</div>
                                </div>
                            </li>
                            <li class="list__item">
                                <span class="empty__icon" style="width: 34px; height: 34px; margin: 0; border-radius: 9px;">
                                    <x-icon name="lock" />
                                </span>
                                <div class="list__body">
                                    <div class="list__title">Shift terkunci saat ditutup</div>
                                    <div class="list__meta">Selisih kas tercatat untuk audit pemilik.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="panel" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Riwayat shift</h2>
            <span class="panel__meta">{{ auth()->user()->isOwner() ? 'Semua kasir' : 'Shift Anda' }}</span>
        </div>

        @if ($history->isEmpty())
            <x-empty-state icon="clock" title="Belum ada riwayat shift" description="Riwayat akan muncul setelah shift pertama dibuka dan ditutup." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shift</th>
                            @if (auth()->user()->isOwner())
                                <th>Kasir</th>
                            @endif
                            <th class="num">Kas awal</th>
                            <th class="num">Kas seharusnya</th>
                            <th class="num">Kas fisik</th>
                            <th class="num">Selisih</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $item)
                            <tr>
                                <td>
                                    <div class="table__primary">{{ tanggal_id($item->opened_at, true) }}</div>
                                    <div class="table__sub">
                                        {{ $item->closed_at ? 'Ditutup '.$item->closed_at->format('H:i') : 'Masih berjalan' }}
                                    </div>
                                </td>
                                @if (auth()->user()->isOwner())
                                    <td>{{ $item->user?->name }}</td>
                                @endif
                                <td class="num">{{ rupiah($item->opening_cash) }}</td>
                                <td class="num">{{ $item->closed_at ? rupiah($item->expected_cash) : '—' }}</td>
                                <td class="num">{{ $item->closed_at ? rupiah($item->closing_cash) : '—' }}</td>
                                <td class="num">
                                    @if ($item->closed_at)
                                        <span style="color: {{ (float) $item->difference_cash === 0.0 ? 'var(--color-ok)' : 'var(--color-danger)' }};">
                                            {{ (float) $item->difference_cash > 0 ? '+' : '' }}{{ rupiah($item->difference_cash) }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><x-badge :tone="$item->status->tone()">{{ $item->status->label() }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 12px 16px; border-top: 1px solid var(--color-line);">
                {{ $history->links() }}
            </div>
        @endif
    </div>
@endsection
