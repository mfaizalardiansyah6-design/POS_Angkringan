<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#191512">
    <title>Masuk · {{ config('app.name', 'Angkringan POS') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-shell">
        <aside class="login-aside">
            <div class="login-brand">
                <span class="login-brand__dot"></span>
                Angkringan POS
            </div>

            <div class="login-editorial">
                <div class="login-wordmark">
                    Warung
                    <em>malam.</em>
                </div>
                <p class="login-tagline">
                    Satu tempat untuk mencatat pesanan, menjaga stok, dan menutup shift
                    dengan angka yang bisa dipercaya.
                </p>
            </div>

            <div class="login-foot">
                <span>Sejak 2026 · Yogyakarta</span>
                <span>{{ tanggal_id(now()) }}</span>
            </div>
        </aside>

        <main class="login-main">
            <div class="login-card">
                <div class="login-card__eyebrow">Meja kasir</div>
                <h1 class="login-title">Masuk, lalu<br>buka warung.</h1>
                <p class="login-sub">
                    Gunakan akun yang diberikan pemilik. Setiap transaksi dan perubahan
                    akan tercatat atas nama Anda.
                </p>

                <form method="POST" action="{{ route('login.store') }}" class="login-form">
                    @csrf

                    @if ($errors->any())
                        <div class="form-errors" role="alert">
                            <strong>Masuk belum berhasil</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="field">
                        <label for="username" class="label">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            class="input @error('username') input--error @enderror"
                            placeholder="contoh: kasir"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            autofocus
                            required
                        >
                        <x-field-error name="username" />
                    </div>

                    <div class="field">
                        <label for="password" class="label">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="input @error('password') input--error @enderror"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                        <x-field-error name="password" />
                    </div>

                    <div class="login-remember">
                        <label class="check">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            <span>Ingat perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn--ember login-submit">
                        Masuk ke kasir
                        <x-icon name="arrow-right" />
                    </button>
                </form>

                <div class="demo-box">
                    <div class="demo-box__title">Akun demo lokal</div>
                    <div class="demo-box__grid">
                        <div class="demo-box__item">
                            <strong>Pemilik</strong>
                            <code>owner</code> · <code>password</code>
                        </div>
                        <div class="demo-box__item">
                            <strong>Kasir</strong>
                            <code>kasir</code> · <code>password</code>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
