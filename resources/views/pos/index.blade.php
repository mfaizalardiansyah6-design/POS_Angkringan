@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
    <div class="pos-page" data-pos>
        <div class="pos-head">
            <div class="pos-head__left">
                <div class="pos-head__title">Kasir</div>
                <div class="pos-head__meta">
                    <span class="status-dot status-dot--live"></span>
                    Shift aktif · {{ $shift->opened_at->format('H:i') }}
                    <span style="color: #c9b9a4;">/</span>
                    {{ auth()->user()->name }}
                </div>
            </div>
            <div class="pos-head__actions">
                <a href="{{ route('sales.index') }}" class="btn btn--outline btn--sm">
                    <x-icon name="receipt" />
                    Transaksi
                </a>
                <a href="{{ route('shifts.index') }}" class="btn btn--outline btn--sm">
                    <x-icon name="clock" />
                    Shift
                </a>
            </div>
        </div>

        <div class="pos-shell">
            <section class="pos-catalog" aria-label="Katalog menu">
                <div class="pos-catalog__top">
                    <div class="pos-search-row">
                        <div class="pos-search">
                            <div class="search-input">
                                <x-icon name="search" />
                                <input
                                    type="search"
                                    class="input"
                                    placeholder="Cari menu atau kategori…"
                                    data-product-search
                                    autocomplete="off"
                                    aria-label="Cari menu"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="category-chips" role="group" aria-label="Filter kategori">
                        <button type="button" class="category-chip is-active" data-category-chip="all">
                            Semua
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" class="category-chip" data-category-chip="{{ $category->id }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($products->isEmpty())
                    <div class="pos-catalog__body">
                        <x-empty-state icon="bowl" title="Belum ada menu aktif" description="Tambahkan produk aktif terlebih dahulu agar bisa dijual di kasir.">
                            @if (auth()->user()->isOwner())
                                <a href="{{ route('products.create') }}" class="btn btn--primary btn--sm">Tambah produk</a>
                            @endif
                        </x-empty-state>
                    </div>
                @else
                    <div class="pos-catalog__body">
                        <div class="product-grid" data-product-grid>
                            @foreach ($products as $product)
                                @php($isOut = $product['track_stock'] && $product['stock'] <= 0)
                                <button
                                    type="button"
                                    class="product-tile"
                                    data-product-card
                                    data-product-id="{{ $product['id'] }}"
                                    data-category="{{ $product['category_id'] }}"
                                    data-search="{{ mb_strtolower($product['name'].' '.($product['category'] ?? '')) }}"
                                    data-stock="{{ $product['stock'] }}"
                                    data-track-stock="{{ $product['track_stock'] ? '1' : '0' }}"
                                    aria-label="Tambahkan {{ $product['name'] }} ke pesanan"
                                    @disabled($isOut)
                                >
                                    <span class="product-tile__media">
                                        @if ($product['image'])
                                            <img src="{{ $product['image'] }}" alt="" loading="lazy">
                                        @else
                                            {{ $product['initial'] }}
                                        @endif
                                    </span>

                                    <span class="product-tile__body">
                                        <span class="product-tile__name">{{ $product['name'] }}</span>
                                        <span class="product-tile__meta">{{ $product['category'] ?? 'Tanpa kategori' }}</span>
                                    </span>

                                    <span class="product-tile__foot">
                                        <span class="product-tile__price">{{ rupiah($product['price']) }}</span>
                                        @if ($product['track_stock'])
                                            <span @class([
                                                'product-tile__stock',
                                                'product-tile__stock--low' => $product['stock'] > 0 && $product['stock'] <= $product['minimum_stock'],
                                                'product-tile__stock--empty' => $product['stock'] <= 0,
                                            ])>
                                                {{ $product['stock'] <= 0 ? 'Habis' : 'Sisa '.$product['stock'] }}
                                            </span>
                                        @else
                                            <span class="product-tile__stock">Tersedia</span>
                                        @endif
                                    </span>

                                    <span class="product-tile__add" aria-hidden="true">
                                        <x-icon name="plus" />
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <div data-product-empty hidden>
                            <x-empty-state icon="search" title="Menu tidak ditemukan" description="Coba kata kunci lain atau pilih kategori berbeda." />
                        </div>
                    </div>
                @endif
            </section>

            <form
                method="POST"
                action="{{ route('pos.checkout') }}"
                class="pos-cart"
                data-pos-form
                data-cart-panel
                aria-label="Pesanan berjalan"
            >
                @csrf

                <div class="pos-cart__head">
                    <div>
                        <div class="pos-cart__title-row">
                            <span class="pos-cart__title">Pesanan</span>
                            <span class="pos-cart__count" data-cart-count>0</span>
                        </div>
                        <div class="pos-cart__meta">Pilih menu, lalu atur jumlahnya.</div>
                    </div>

                    <div class="pos-cart__actions">
                        <button type="button" class="cart-clear" data-cart-clear>Kosongkan</button>
                        <button type="button" class="pos-cart__close" data-cart-close aria-label="Tutup pesanan">
                            <x-icon name="x" />
                        </button>
                    </div>
                </div>

                <div class="pos-cart__top">
                    <div class="segmented segmented--dark">
                        <label>
                            <input type="radio" name="order_type" value="{{ \App\Enums\OrderType::DineIn->value }}" @checked($oldOrderType === \App\Enums\OrderType::DineIn->value)>
                            <span>Makan di sini</span>
                        </label>
                        <label>
                            <input type="radio" name="order_type" value="{{ \App\Enums\OrderType::Takeaway->value }}" @checked($oldOrderType === \App\Enums\OrderType::Takeaway->value)>
                            <span>Bungkus</span>
                        </label>
                    </div>
                </div>

                <div class="pos-cart__lines" data-cart-lines>
                    <div class="pos-cart__empty" data-cart-empty>
                        <div class="pos-cart__empty-icon">
                            <x-icon name="bowl" />
                        </div>
                        <div class="pos-cart__empty-title">Belum ada pesanan</div>
                        <div class="pos-cart__empty-text">Pilih menu di sebelah kiri untuk mulai mencatat.</div>
                    </div>
                </div>

                <div class="notice notice--danger" data-pos-error hidden style="margin: 0 20px 10px; border-color: rgba(255, 143, 122, 0.35); background: rgba(115, 43, 34, 0.25); color: #ffb9aa;"></div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Item</span>
                        <span><span data-cart-count>0</span> item</span>
                    </div>
                    <div class="summary-row summary-row--total">
                        <span>Total</span>
                        <span data-subtotal-value>Rp 0</span>
                    </div>

                    <div class="payment-methods">
                        <label class="payment-pill">
                            <input type="radio" name="payment_method" value="{{ \App\Enums\PaymentMethod::Cash->value }}" data-payment-method @checked($oldPaymentMethod === \App\Enums\PaymentMethod::Cash->value)>
                            <span>
                                <x-icon name="cash" />
                                Tunai
                            </span>
                        </label>
                        <label class="payment-pill">
                            <input type="radio" name="payment_method" value="{{ \App\Enums\PaymentMethod::Qris->value }}" data-payment-method @checked($oldPaymentMethod === \App\Enums\PaymentMethod::Qris->value)>
                            <span>
                                <x-icon name="qr" />
                                QRIS
                            </span>
                        </label>
                    </div>

                    <div data-cash-panel @if ($oldPaymentMethod === \App\Enums\PaymentMethod::Qris->value) hidden @endif>
                        <label for="cash-input" class="label" style="color: #9e8e79;">Uang diterima</label>
                        <div class="cash-field">
                            <span class="cash-field__prefix">Rp</span>
                            <input
                                id="cash-input"
                                type="text"
                                class="input"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="0"
                                data-cash-input
                                aria-describedby="cash-change"
                            >
                        </div>

                        <div class="cash-quick">
                            @foreach ([5000, 10000, 20000, 50000, 100000] as $nominal)
                                <button type="button" data-cash-quick="{{ $nominal }}">
                                    {{ number_format($nominal / 1000, 0, ',', '.') }}rb
                                </button>
                            @endforeach
                            <button type="button" data-cash-quick="exact">Pas</button>
                        </div>

                        <div class="change-row" id="cash-change" data-change-row>
                            <span>Kembalian</span>
                            <strong data-change-value>Rp 0</strong>
                        </div>
                    </div>

                    <div data-qris-panel @if ($oldPaymentMethod !== \App\Enums\PaymentMethod::Qris->value) hidden @endif>
                        <div class="notice" style="margin: 2px 0 10px; font-size: 10.5px;">
                            <x-icon name="qr" />
                            <span>
                                Transaksi QRIS akan berstatus <strong>menunggu</strong>. Stok dan pendapatan
                                dihitung setelah pembayaran dikonfirmasi.
                            </span>
                        </div>

                        @if ($qrisImage)
                            <button type="button" class="qris-preview" data-dialog-open="qris-dialog">
                                <img src="{{ $qrisImage }}" alt="Kode QRIS toko">
                                <span>Perbesar QRIS</span>
                            </button>
                        @endif

                        @if ($qrisInfo)
                            <div class="qris-info">{{ $qrisInfo }}</div>
                        @endif

                        <div class="field" style="margin-top: 10px;">
                            <label for="payment_reference" class="label" style="color: #9e8e79;">Referensi QRIS (opsional)</label>
                            <input
                                id="payment_reference"
                                name="payment_reference"
                                type="text"
                                value="{{ old('payment_reference') }}"
                                maxlength="100"
                                class="input"
                                style="height: 38px; border-color: rgba(255, 245, 229, 0.16); background: rgba(255, 245, 229, 0.06); color: #f3e9da; font-size: 11px;"
                                placeholder="Nomor referensi / berita transfer"
                            >
                            <x-field-error name="payment_reference" />
                        </div>
                    </div>

                    <input type="hidden" name="items" value="{{ old('items', '[]') }}" data-items-input>
                    <input type="hidden" name="amount_paid" value="{{ old('amount_paid') }}" data-cash-value>
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}" data-idempotency>

                    @error('items')
                        <div class="field-error" style="margin-bottom: 8px;">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn--ember btn--block btn--lg" data-pay-button>
                        <span data-pay-label>Bayar tunai</span>
                        <span data-pay-total>Rp 0</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="pos-overlay" data-cart-overlay></div>

        <button type="button" class="cart-fab" data-cart-fab aria-label="Buka pesanan">
            <span style="display: inline-flex; align-items: center; gap: 7px;">
                <x-icon name="receipt" />
                Pesanan
                <span class="cart-fab__count" data-cart-count>0</span>
            </span>
            <strong data-fab-value>Rp 0</strong>
        </button>

        <script type="application/json" id="pos-data">
            {!! json_encode([
                'products' => $products,
                'oldItems' => $oldItems,
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}
        </script>
    </div>
@endsection

@push('modals')
    @if ($qrisImage)
        <dialog id="qris-dialog" class="dialog" aria-label="Kode QRIS toko">
            <div class="dialog__head">
                <div>
                    <h2 class="dialog__title">QRIS Angkringan</h2>
                    <p class="dialog__text">Pindai dari aplikasi pembayaran pelanggan.</p>
                </div>
                <button type="button" class="dialog__close" data-dialog-close aria-label="Tutup">
                    <x-icon name="x" />
                </button>
            </div>
            <div class="dialog__body">
                <img src="{{ $qrisImage }}" alt="Kode QRIS Angkringan" style="width: 100%; border-radius: 12px;">
                @if ($qrisInfo)
                    <p class="dialog__text" style="margin-top: 12px;">{{ $qrisInfo }}</p>
                @endif
            </div>
            <div class="dialog__foot">
                <button type="button" class="btn btn--outline" data-dialog-close>Tutup</button>
            </div>
        </dialog>
    @endif

    <dialog id="cart-clear-confirm" class="dialog" aria-label="Kosongkan pesanan">
        <div class="dialog__head">
            <div>
                <h2 class="dialog__title">Kosongkan pesanan?</h2>
                <p class="dialog__text">Semua item yang sudah dipilih akan dihapus dari keranjang.</p>
            </div>
            <button type="button" class="dialog__close" data-dialog-close aria-label="Tutup">
                <x-icon name="x" />
            </button>
        </div>
        <div class="dialog__foot">
            <button type="button" class="btn btn--outline" data-dialog-close>Batal</button>
            <button type="button" class="btn btn--danger" data-cart-clear-confirm>Kosongkan</button>
        </div>
    </dialog>

    <dialog id="checkout-confirm" class="dialog" aria-label="Konfirmasi pembayaran">
        <div class="dialog__head">
            <div>
                <h2 class="dialog__title">Konfirmasi pembayaran</h2>
                <p class="dialog__text">Periksa pesanan sebelum transaksi disimpan.</p>
            </div>
            <button type="button" class="dialog__close" data-dialog-close aria-label="Tutup">
                <x-icon name="x" />
            </button>
        </div>
        <div class="dialog__body">
            <div class="detail-grid">
                <div>
                    <div class="detail-label">Metode</div>
                    <div class="detail-value" data-confirm-method>—</div>
                </div>
                <div>
                    <div class="detail-label">Total</div>
                    <div class="detail-value" data-confirm-total>—</div>
                </div>
            </div>

            <div class="detail-grid" data-confirm-cash-row style="margin-top: 14px;">
                <div>
                    <div class="detail-label">Uang diterima</div>
                    <div class="detail-value" data-confirm-cash>—</div>
                </div>
            </div>

            <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--color-line);">
                <div class="detail-label" style="margin-bottom: 8px;">Rincian</div>
                <div data-confirm-items style="color: var(--color-ink-soft); font-size: 12px; line-height: 1.7;"></div>
            </div>

            <div class="notice" style="margin-top: 14px;">
                <x-icon name="info" />
                <span>Untuk QRIS, transaksi tetap menunggu sampai pembayaran dikonfirmasi di halaman transaksi.</span>
            </div>
        </div>
        <div class="dialog__foot">
            <button type="button" class="btn btn--outline" data-dialog-close>Periksa lagi</button>
            <button type="button" class="btn btn--ember" data-confirm-pay>
                <x-icon name="check" />
                Konfirmasi &amp; simpan
            </button>
        </div>
    </dialog>
@endpush
