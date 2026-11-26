<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Isiin') · Isiin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    {{-- Tailwind via CDN: praktis untuk pengembangan. Ganti dengan Vite + Tailwind saat produksi. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { ink: '#0f1720', sand: '#f6f0e6', gold: '#d6b88a' },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'], serif: ['"Playfair Display"', 'serif'] }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @@layer components {
            .btn { @@apply inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold transition duration-200 hover:-translate-y-0.5; }
            .btn-primary { @@apply bg-ink text-white shadow-sm hover:bg-black hover:shadow-md; }
            .btn-gold { @@apply bg-gold text-ink shadow-sm hover:brightness-95 hover:shadow-md; }
            .btn-outline { @@apply border border-ink/15 bg-white text-ink hover:border-gold hover:bg-amber-50; }
            .btn-danger { @@apply bg-rose-600 text-white shadow-sm hover:bg-rose-700; }
            .input { @@apply w-full rounded-xl border border-ink/15 bg-white px-3.5 py-2.5 text-sm transition focus:outline-none focus:ring-2 focus:ring-gold/70; }
            .label { @@apply block text-sm font-medium mb-1; }
            .card { @@apply rounded-2xl bg-white shadow-sm border border-ink/5; }
            .badge { @@apply inline-block rounded-full px-2.5 py-0.5 text-xs font-medium; }
            .page-heading { @@apply font-serif text-3xl leading-tight tracking-tight sm:text-4xl; }
            .page-subtitle { @@apply mt-2 max-w-2xl text-sm leading-6 text-ink/60 sm:text-base; }
            .section-heading { @@apply font-serif text-xl leading-tight sm:text-2xl; }
        }
    </style>
    <style>
        body {
            background-image: radial-gradient(ellipse at 12% 8%, rgba(214, 184, 138, .14), transparent 28rem),
                radial-gradient(ellipse at 88% 42%, rgba(214, 184, 138, .1), transparent 30rem);
            background-attachment: fixed;
        }

        a:focus-visible, button:focus-visible {
            outline: 2px solid #d6b88a;
            outline-offset: 3px;
        }

        summary::-webkit-details-marker {
            display: none;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            white-space: nowrap;
            font-weight: 600;
        }

        td, th {
            vertical-align: top;
        }

        @media (max-width: 639px) {
            main {
                padding-top: 1.5rem;
                padding-bottom: 2rem;
            }
        }
    </style>
</head>
<body class="bg-[#f8f5ef] text-ink font-sans antialiased min-h-screen flex flex-col">
    @php
        $pendingRequests = auth()->check() && auth()->user()->isAdmin()
            ? \App\Models\ProfileChangeRequest::where('status', 'pending')->count()
            : 0;
        $openDisputes = auth()->check() && auth()->user()->isAdmin()
            ? \App\Models\Dispute::where('status', 'open')->count()
            : 0;
    @endphp
    <header class="sticky top-0 z-20 bg-gradient-to-r from-[#111b22] via-ink to-[#28332f] text-white shadow-lg shadow-ink/10">
        <div class="pointer-events-none absolute right-0 top-0 h-full w-64 overflow-hidden">
            <div class="absolute -right-16 -top-32 h-64 w-64 rounded-full border border-gold/15"></div>
            <div class="absolute -right-5 -top-20 h-48 w-48 rounded-full border border-gold/10"></div>
        </div>
        <div class="relative mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3 lg:min-h-20 lg:flex-nowrap lg:gap-4">
            <a href="{{ route('home') }}" class="group inline-flex shrink-0 items-center gap-2.5">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-gold text-lg text-ink shadow-md transition group-hover:rotate-6" aria-hidden="true">⌂</span>
                <span class="font-serif text-2xl tracking-wide">isiin<span class="text-gold">.</span></span>
            </a>
            <details class="group relative z-10 lg:hidden">
                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/10">
                    Menu
                    <span class="text-xs text-gold transition group-open:rotate-180" aria-hidden="true">⌄</span>
                </summary>
                <nav class="absolute right-0 top-14 z-30 w-[min(19rem,calc(100vw-2rem))] rounded-2xl border border-white/10 bg-[#172127] p-2 text-sm shadow-2xl shadow-black/30">
                    <a href="{{ route('packages.index') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Paket Staging</a>
                    @if (! auth()->check() || ! auth()->user()->isVendor())
                        <a href="{{ route('vendors.index') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Cari Vendor</a>
                    @endif
                    @auth
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('orders.index') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Pesanan Saya</a>
                        @elseif (auth()->user()->isVendor())
                            <a href="{{ route('vendor.dashboard') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Dashboard</a>
                            <a href="{{ route('vendor.portfolio.manage') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Profil Bisnis</a>
                            <a href="{{ route('vendor.packages.index') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Paket Saya</a>
                            <a href="{{ route('vendor.orders.index') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Pesanan Masuk</a>
                        @elseif (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Dashboard Komisi</a>
                            <a href="{{ route('admin.disputes.index') }}" class="flex items-center justify-between rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">
                                Sengketa
                                @if ($openDisputes > 0)
                                    <span class="badge bg-gold text-ink">{{ $openDisputes }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.vendors.index') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Verifikasi Vendor</a>
                            <a href="{{ route('admin.requests.index') }}" class="flex items-center justify-between rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">
                                Permintaan Perubahan
                                @if ($pendingRequests > 0)
                                    <span class="badge bg-gold text-ink">{{ $pendingRequests }}</span>
                                @endif
                            </a>
                        @endif
                        <a href="{{ auth()->user()->isVendor() ? route('vendor.account.show') : route('account.show') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">{{ auth()->user()->isVendor() ? 'Akun Saya' : 'Profil' }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-2">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-4 py-3 text-left text-white/85 transition hover:bg-white/10 hover:text-gold">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-xl px-4 py-3 text-white/85 transition hover:bg-white/10 hover:text-gold">Masuk</a>
                        <a href="{{ route('register') }}" class="mt-1 flex items-center justify-center rounded-xl bg-gold px-4 py-3 font-semibold text-ink transition hover:brightness-95">Daftar</a>
                    @endauth
                </nav>
            </details>
            <nav class="hidden flex-wrap items-center justify-end gap-1.5 text-sm lg:flex">
                <a href="{{ route('packages.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Paket Staging</a>
                @if (! auth()->check() || ! auth()->user()->isVendor())
                    <a href="{{ route('vendors.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Cari Vendor</a>
                @endif
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('orders.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Pesanan Saya</a>
                    @elseif (auth()->user()->isVendor())
                        <a href="{{ route('vendor.dashboard') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Dashboard</a>
                        <a href="{{ route('vendor.portfolio.manage') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Profil Bisnis</a>
                        <a href="{{ route('vendor.packages.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Paket Saya</a>
                        <a href="{{ route('vendor.orders.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Pesanan Masuk</a>
                    @elseif (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Dashboard</a>
                        <a href="{{ route('admin.disputes.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">
                            Sengketa
                            @if ($openDisputes > 0)
                                <span class="badge bg-gold text-ink ml-1">{{ $openDisputes }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.vendors.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Verifikasi Vendor</a>
                        <a href="{{ route('admin.requests.index') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">
                            Permintaan Perubahan
                            @if ($pendingRequests > 0)
                                <span class="badge bg-gold text-ink ml-1">{{ $pendingRequests }}</span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ auth()->user()->isVendor() ? route('vendor.account.show') : route('account.show') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">{{ auth()->user()->isVendor() ? 'Akun Saya' : 'Profil' }}</a>
                    <span class="hidden sm:inline text-white/60">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-3 py-2 text-white/85 transition hover:bg-white/10 hover:text-gold">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-gold">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-6 sm:px-5 sm:py-8 lg:px-6">
        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200/70 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800 shadow-sm sm:mb-6 sm:px-5">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-2xl border border-rose-200/70 bg-rose-50 px-4 py-3.5 text-sm text-rose-800 shadow-sm sm:mb-6 sm:px-5">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-8 bg-gradient-to-br from-[#111b22] via-ink to-[#28332f] text-white/65 text-sm">
        <div class="mx-auto max-w-6xl px-5 pb-5 pt-9 sm:px-6 sm:pt-10">
            <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:items-start sm:justify-between sm:text-left">
                <div class="max-w-sm">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-gold text-lg text-ink" aria-hidden="true">⌂</span>
                        <span class="font-serif text-xl text-white">isiin<span class="text-gold">.</span></span>
                    </a>
                    <p class="mt-3 leading-6">Ruang yang terasa seperti rumah. Bantu properti tampil lebih menarik dengan staging yang praktis.</p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
                    <a href="{{ route('packages.index') }}" class="rounded-full border border-white/15 px-4 py-2.5 text-white/85 transition hover:border-gold/60 hover:bg-white/5 hover:text-gold">Jelajahi Paket</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-gold px-4 py-2.5 font-semibold text-ink transition hover:brightness-95">Mulai Sekarang <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="mt-7 border-t border-white/10 pt-4 text-center text-xs text-white/45 sm:mt-8 sm:text-left">
                © {{ date('Y') }} Isiin <span class="mx-1 text-gold/70">·</span> Marketplace sewa furniture untuk home staging properti
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-password-toggle]');
            if (!button) return;

            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const isVisible = input.type === 'password';
            input.type = isVisible ? 'text' : 'password';
            const label = isVisible ? 'Sembunyikan password' : 'Tampilkan password';
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            button.setAttribute('aria-pressed', String(isVisible));
            button.querySelector('[data-eye-open]').classList.toggle('hidden', isVisible);
            button.querySelector('[data-eye-closed]').classList.toggle('hidden', !isVisible);
        });
    </script>
</body>
</html>
