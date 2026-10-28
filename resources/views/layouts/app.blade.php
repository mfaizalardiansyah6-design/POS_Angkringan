@php
    $navUser = auth()->user();
    $navShift = $navUser?->openShift()->first();
    $isPos = request()->routeIs('pos.index');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a1613">

    <title>@yield('title', 'Kasir') · {{ config('app.name', 'Angkringan POS') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="app-body">
    <div class="app-shell">
        <aside class="sidebar" data-sidebar aria-label="Navigasi utama">
            <a href="{{ route('dashboard') }}" class="sidebar__brand">
                <span class="brand-word">angkringan</span>
                <span class="brand-row">
                    <span class="brand-mark"></span>
                    <span class="brand-tag">POS · Warung malam</span>
                </span>
                <span class="brand-note">Catat setiap pesanan, jaga setiap rupiah.</span>
            </a>

            <nav class="sidebar__nav">
                <div class="nav-group">
                    <div class="nav-label">Hari ini</div>
                    <a href="{{ route('dashboard') }}" @class(['nav-link', 'is-active' => request()->routeIs('dashboard')])>
                        <x-icon name="grid" />
                        <span>Ringkasan</span>
                    </a>
                    <a href="{{ route('pos.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('pos.*')])>
                        <x-icon name="pos" />
                        <span>Kasir</span>
                        @if ($navShift)
                            <span class="nav-link__aside"><span class="status-dot status-dot--live"></span></span>
                        @endif
                    </a>
                    <a href="{{ route('sales.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('sales.*')])>
                        <x-icon name="receipt" />
                        <span>Transaksi</span>
                    </a>
                    <a href="{{ route('shifts.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('shifts.*')])>
                        <x-icon name="clock" />
                        <span>Shift kasir</span>
                    </a>
                </div>

                @if ($navUser?->isOwner())
                    <div class="nav-group">
                        <div class="nav-label">Kelola warung</div>
                        <a href="{{ route('products.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('products.*')])>
                            <x-icon name="package" />
                            <span>Produk</span>
                        </a>
                        <a href="{{ route('categories.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('categories.*')])>
                            <x-icon name="tag" />
                            <span>Kategori</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('inventory.*')])>
                            <x-icon name="archive" />
                            <span>Inventaris</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('expenses.*')])>
                            <x-icon name="wallet" />
                            <span>Pengeluaran</span>
                        </a>
                        <a href="{{ route('reports.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('reports.*')])>
                            <x-icon name="chart" />
                            <span>Laporan</span>
                        </a>
                    </div>

                    <div class="nav-group">
                        <div class="nav-label">Sistem</div>
                        <a href="{{ route('users.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('users.*')])>
                            <x-icon name="users" />
                            <span>Pengguna</span>
                        </a>
                        <a href="{{ route('activity.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('activity.*')])>
                            <x-icon name="history" />
                            <span>Jejak aktivitas</span>
                        </a>
                        <a href="{{ route('settings.edit') }}" @class(['nav-link', 'is-active' => request()->routeIs('settings.*')])>
                            <x-icon name="settings" />
                            <span>Pengaturan</span>
                        </a>
                    </div>
                @endif
            </nav>

            <div class="sidebar__bottom">
                <a href="{{ route('shifts.index') }}" class="shift-card">
                    <span class="shift-card__top">
                        <span @class(['status-dot', 'status-dot--live' => $navShift])></span>
                        {{ $navShift ? 'Shift berjalan' : 'Shift belum dibuka' }}
                    </span>
                    <span class="shift-card__value">
                        {{ $navShift ? 'Sejak '.$navShift->opened_at->format('H:i') : 'Buka shift untuk mulai' }}
                    </span>
                    <span class="shift-card__hint">
                        {{ $navShift ? 'Kas awal '.rupiah($navShift->opening_cash) : 'Kasir & pemilik tetap bisa transaksi' }}
                    </span>
                </a>

                <div class="sidebar__user">
                    <span class="avatar">{{ initials($navUser?->name ?? 'A') }}</span>
                    <div style="min-width: 0;">
                        <div class="sidebar__user-name">{{ $navUser?->name }}</div>
                        <div class="sidebar__user-role">{{ $navUser?->role->label() }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" data-confirm="Keluar dari akun ini sekarang?" data-confirm-title="Keluar dari Angkringan POS" data-confirm-danger="1">
                        @csrf
                        <button type="submit" class="sidebar__logout" aria-label="Keluar">
                            <x-icon name="logout" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="sidebar-backdrop" data-sidebar-backdrop data-sidebar-close></div>

        <div class="app-main">
            <header class="topbar">
                <button type="button" class="topbar__menu" data-sidebar-toggle aria-label="Buka navigasi" aria-expanded="false">
                    <x-icon name="menu" />
                </button>

                <div class="topbar__date">
                    <strong>{{ tanggal_id(now()) }}</strong> · {{ now()->format('H:i') }} WIB
                </div>

                <div class="topbar__actions">
                    <a href="{{ route('shifts.index') }}" class="topbar__shift">
                        <span @class(['status-dot', 'status-dot--live' => $navShift])></span>
                        {{ $navShift ? 'Shift aktif' : 'Buka shift' }}
                    </a>

                    @unless ($isPos)
                        <a href="{{ route('pos.index') }}" class="btn btn--primary btn--sm">
                            <x-icon name="pos" />
                            Kasir
                        </a>
                    @endunless

                    <a href="{{ route('sales.index') }}" class="icon-btn hide-mobile" aria-label="Daftar transaksi">
                        <x-icon name="receipt" />
                    </a>
                </div>
            </header>

            <main class="{{ $isPos ? 'page page--pos' : 'page' }}">
                @yield('content')
            </main>

            @unless ($isPos)
                <footer class="app-footer">
                    <span class="app-footer__mark">{{ config('app.name', 'Angkringan POS') }}</span>
                    <span>Catatan kecil, keputusan besar.</span>
                </footer>
            @endunless
        </div>
    </div>

    @if (session('success') || session('error'))
        <div class="toast-region" aria-live="polite" aria-atomic="true">
            @if (session('success'))
                <div class="flash flash--success" data-toast role="status">
                    <x-icon name="check" />
                    <span>{{ session('success') }}</span>
                    <button type="button" class="flash__close" data-toast-close aria-label="Tutup">
                        <x-icon name="x" />
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="flash flash--error" data-toast role="alert">
                    <x-icon name="alert" />
                    <span>{{ session('error') }}</span>
                    <button type="button" class="flash__close" data-toast-close aria-label="Tutup">
                        <x-icon name="x" />
                    </button>
                </div>
            @endif
        </div>
    @endif

    <dialog id="app-confirm" class="dialog" aria-labelledby="app-confirm-title">
        <div class="dialog__head">
            <div>
                <h2 class="dialog__title" id="app-confirm-title" data-confirm-title>Konfirmasi tindakan</h2>
                <p class="dialog__text" data-confirm-text>Lanjutkan tindakan ini?</p>
            </div>
            <button type="button" class="dialog__close" data-dialog-close aria-label="Tutup">
                <x-icon name="x" />
            </button>
        </div>
        <div class="dialog__foot">
            <button type="button" class="btn btn--outline" data-confirm-cancel>Batal</button>
            <button type="button" class="btn btn--primary" data-confirm-accept>Ya, lanjutkan</button>
        </div>
    </dialog>

    @stack('modals')
</body>
</html>
