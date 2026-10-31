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
            .btn { @@apply inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition; }
            .btn-primary { @@apply bg-ink text-white hover:bg-black; }
            .btn-gold { @@apply bg-gold text-ink hover:brightness-95; }
            .btn-outline { @@apply border border-ink/20 bg-white text-ink hover:bg-ink/5; }
            .btn-danger { @@apply bg-rose-600 text-white hover:bg-rose-700; }
            .input { @@apply w-full rounded-lg border border-ink/20 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold; }
            .label { @@apply block text-sm font-medium mb-1; }
            .card { @@apply rounded-xl bg-white shadow-sm border border-ink/5; }
            .badge { @@apply inline-block rounded-full px-2.5 py-0.5 text-xs font-medium; }
        }
    </style>
</head>
<body class="bg-sand text-ink font-sans antialiased min-h-screen flex flex-col">
    <header class="bg-ink text-white">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="font-serif text-2xl tracking-wide">Isiin</a>
            <nav class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm">
                <a href="{{ route('packages.index') }}" class="hover:text-gold">Paket Staging</a>
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('orders.index') }}" class="hover:text-gold">Pesanan Saya</a>
                    @elseif (auth()->user()->isVendor())
                        <a href="{{ route('vendor.dashboard') }}" class="hover:text-gold">Dashboard</a>
                        <a href="{{ route('vendor.packages.index') }}" class="hover:text-gold">Paket Saya</a>
                        <a href="{{ route('vendor.orders.index') }}" class="hover:text-gold">Pesanan Masuk</a>
                    @elseif (auth()->user()->isAdmin())
                        <a href="{{ route('admin.vendors.index') }}" class="hover:text-gold">Verifikasi Vendor</a>
                    @endif
                    <span class="hidden sm:inline text-white/60">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-gold">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-gold">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-gold">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-rose-50 text-rose-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-ink text-white/60 text-sm">
        <div class="max-w-6xl mx-auto px-4 py-6">© {{ date('Y') }} Isiin · Marketplace jasa sewa furniture untuk home staging properti</div>
    </footer>
</body>
</html>
