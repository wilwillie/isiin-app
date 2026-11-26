<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prototype Isiin</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172329;
            --muted: #738078;
            --sand: #f5f0e7;
            --gold: #d6b88a;
            --green: #315746;
            --line: #e9e5dc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: radial-gradient(circle at 15% 10%, #e8dfd1, transparent 30rem), #eae8e2;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .stage { display: grid; min-height: calc(100vh - 48px); place-items: center; }
        .phone {
            display: flex;
            width: min(100%, 410px);
            height: min(860px, calc(100vh - 48px));
            min-height: 650px;
            flex-direction: column;
            overflow: hidden;
            border: 8px solid #fff;
            border-radius: 34px;
            background: #fff;
            box-shadow: 0 24px 80px #1723292b;
        }
        .topbar { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px 12px; }
        .brand { font-family: Georgia, serif; font-size: 24px; font-weight: 700; letter-spacing: -.04em; }
        .brand span { color: #b98f54; }
        .demo-pill { border-radius: 999px; background: #f5f0e7; color: #786240; font-size: 10px; font-weight: 700; padding: 6px 9px; }
        .screen { flex: 1; overflow-y: auto; padding: 8px 18px 22px; }
        .screen::-webkit-scrollbar { width: 4px; }
        .screen::-webkit-scrollbar-thumb { border-radius: 4px; background: #ddd7cc; }
        .eyebrow { color: #a77a3f; font-size: 10px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        h1, h2, h3, p { margin-top: 0; }
        h1 { margin-bottom: 9px; font-family: Georgia, serif; font-size: 29px; line-height: 1.12; }
        h2 { margin-bottom: 0; font-family: Georgia, serif; font-size: 21px; }
        h3 { margin-bottom: 5px; font-size: 15px; }
        p { color: var(--muted); font-size: 13px; line-height: 1.55; }
        .hero { margin: 7px 0 20px; border-radius: 23px; background: var(--ink); padding: 20px; color: #fff; }
        .hero p { margin-bottom: 15px; color: #ffffffb8; }
        .hero h1 { max-width: 290px; color: #fff; }
        .search { display: flex; gap: 8px; margin-top: 16px; }
        .search input, .field { width: 100%; min-width: 0; border: 1px solid var(--line); border-radius: 12px; background: #fff; padding: 11px 12px; color: var(--ink); font-size: 12px; outline: none; }
        .search button, .button { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 12px; background: var(--gold); color: var(--ink); cursor: pointer; font-size: 12px; font-weight: 800; padding: 11px 14px; }
        .button { width: 100%; margin-top: 12px; }
        .button.dark { background: var(--ink); color: #fff; }
        .section-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 19px 0 10px; }
        .section-head a { color: #926b35; font-size: 11px; font-weight: 800; }
        .chips { display: flex; gap: 8px; overflow-x: auto; padding: 2px 0 4px; }
        .chip { flex: 0 0 auto; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: #49564f; font-size: 11px; padding: 8px 12px; }
        .package-list { display: grid; gap: 11px; }
        .package-card { display: grid; grid-template-columns: 90px 1fr; gap: 12px; align-items: center; border: 1px solid var(--line); border-radius: 17px; background: #fff; padding: 9px; }
        .package-art { display: grid; min-height: 90px; place-items: center; border-radius: 12px; color: #fff; font-family: Georgia, serif; font-size: 22px; font-weight: 700; text-shadow: 0 1px 5px #0005; }
        .package-card p { margin-bottom: 5px; font-size: 11px; }
        .price { color: var(--ink); font-size: 13px; font-weight: 800; }
        .muted-small { color: var(--muted); font-size: 10px; }
        .panel { margin-top: 13px; border: 1px solid var(--line); border-radius: 16px; background: #fff; padding: 14px; }
        .detail-art { display: grid; min-height: 180px; place-items: center; margin: 4px 0 17px; border-radius: 20px; color: #fff; font-family: Georgia, serif; font-size: 44px; font-weight: 700; text-shadow: 0 2px 8px #0005; }
        .item-row, .order-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); padding: 10px 0; font-size: 12px; }
        .item-row:last-child, .order-row:last-child { border-bottom: 0; }
        .price-line { display: flex; align-items: center; justify-content: space-between; padding-top: 11px; font-size: 12px; }
        .price-line strong { font-size: 16px; }
        .field-label { display: block; margin: 13px 0 5px; color: #526057; font-size: 11px; font-weight: 700; }
        .notice { margin: 12px 0; border-radius: 12px; background: #f5f0e7; padding: 11px; color: #6c5735; font-size: 11px; line-height: 1.5; }
        .success-mark { display: grid; width: 62px; height: 62px; place-items: center; margin: 28px auto 16px; border-radius: 50%; background: #e2efe5; color: #2d7950; font-size: 30px; }
        .timeline { margin-top: 18px; border-left: 2px solid #dfe8df; padding-left: 15px; }
        .timeline div { position: relative; margin-bottom: 18px; }
        .timeline div::before { position: absolute; top: 3px; left: -21px; width: 10px; height: 10px; border: 2px solid #fff; border-radius: 50%; background: #49845f; content: ""; box-shadow: 0 0 0 1px #49845f; }
        .timeline b { display: block; font-size: 12px; }
        .timeline span { color: var(--muted); font-size: 10px; }
        .profile-icon { display: grid; width: 58px; height: 58px; place-items: center; margin-bottom: 14px; border-radius: 20px; background: var(--sand); font-size: 24px; }
        .tabbar { display: grid; grid-template-columns: repeat(4, 1fr); border-top: 1px solid var(--line); background: #fff; padding: 8px 5px 5px; }
        .tab { display: grid; justify-items: center; gap: 3px; border-radius: 12px; color: #8a938c; font-size: 9px; padding: 6px 2px; }
        .tab span:first-child { font-size: 17px; }
        .tab.active { color: var(--green); font-weight: 800; }
        .prototype-note { margin: 13px auto 0; color: #626c65; font-size: 11px; text-align: center; }
        @media (max-width: 520px) {
            body { padding: 0; background: #fff; }
            .stage { min-height: 100vh; }
            .phone { width: 100%; height: 100vh; min-height: 600px; border: 0; border-radius: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="stage">
        <div>
            <div class="phone">
                <header class="topbar">
                    <a class="brand" href="{{ route('prototype.app', ['screen' => 'home']) }}">isiin<span>.</span></a>
                    <span class="demo-pill">PROTOTYPE</span>
                </header>

                <main class="screen">
                    @if (session('error'))
                        <div class="notice">{{ session('error') }}</div>
                    @endif

                    @if ($screen === 'home')
                        <section class="hero">
                            <span class="eyebrow">Home staging, lebih mudah</span>
                            <h1>Ruang menarik, properti lebih berkesan.</h1>
                            <p>Pilih furniture sewaan yang pas untuk membuat calon pembeli merasa di rumah.</p>
                            <form class="search" method="GET" action="{{ route('prototype.app') }}">
                                <input type="hidden" name="screen" value="catalog">
                                <input name="q" placeholder="Cari gaya atau ruangan">
                                <button type="submit" aria-label="Cari paket">Cari</button>
                            </form>
                        </section>

                        <div class="chips">
                            @foreach (['Ruang tamu', 'Japandi', 'Scandinavian'] as $term)
                                <a class="chip" href="{{ route('prototype.app', ['screen' => 'catalog', 'q' => $term]) }}">{{ $term }}</a>
                            @endforeach
                        </div>

                        <div class="section-head">
                            <h2>Paket pilihan</h2>
                            <a href="{{ route('prototype.app', ['screen' => 'catalog']) }}">Lihat semua</a>
                        </div>
                        <div class="package-list">
                            @foreach ($featuredPackages as $id => $package)
                                @include('prototype._package-card', ['id' => $id, 'package' => $package])
                            @endforeach
                        </div>
                    @elseif ($screen === 'catalog')
                        <p class="eyebrow">Katalog sewa</p>
                        <h1>Temukan paketmu.</h1>
                        <form class="search" method="GET" action="{{ route('prototype.app') }}">
                            <input type="hidden" name="screen" value="catalog">
                            <input name="q" value="{{ $query }}" placeholder="Cari gaya atau ruangan">
                            <button type="submit">Cari</button>
                        </form>
                        <div class="section-head"><h2>{{ $query ? 'Hasil pencarian' : 'Semua paket' }}</h2><span class="muted-small">{{ $packages->count() }} paket</span></div>
                        <div class="package-list">
                            @forelse ($packages as $id => $package)
                                @include('prototype._package-card', ['id' => $id, 'package' => $package])
                            @empty
                                <div class="panel"><p style="margin:0">Belum ada paket yang cocok. Coba kata kunci lain.</p></div>
                            @endforelse
                        </div>
                    @elseif ($screen === 'detail')
                        <div class="detail-art" style="background:linear-gradient(140deg, {{ $selectedPackage['color'] }}, #344037)">{{ strtoupper(substr($selectedPackage['name'], 0, 1)) }}</div>
                        <p class="eyebrow">{{ $selectedPackage['room'] }} · {{ $selectedPackage['style'] }}</p>
                        <h1>{{ $selectedPackage['name'] }}</h1>
                        <p>Paket pilihan untuk memberi suasana yang lebih hidup pada properti. Semua furniture dikirim dan dipasang oleh vendor.</p>
                        <div class="panel">
                            <h3>Isi paket</h3>
                            @foreach ($selectedPackage['items'] as $item)
                                <div class="item-row"><span>{{ $item }}</span><span class="muted-small">Termasuk</span></div>
                            @endforeach
                            <div class="price-line"><span>Sewa {{ $selectedPackage['duration'] }}</span><strong>Rp {{ number_format($selectedPackage['price'], 0, ',', '.') }}</strong></div>
                        </div>
                        <a class="button dark" href="{{ route('prototype.app', ['screen' => 'booking', 'package' => $packageId]) }}">Pilih tanggal sewa</a>
                    @elseif ($screen === 'booking')
                        <p class="eyebrow">Langkah 1 dari 2</p>
                        <h1>Atur rencana sewamu.</h1>
                        <div class="panel">
                            <h3>{{ $selectedPackage['name'] }}</h3>
                            <p>{{ $selectedPackage['room'] }} · {{ $selectedPackage['duration'] }}</p>
                            <div class="price-line"><span>Harga paket</span><strong>Rp {{ number_format($selectedPackage['price'], 0, ',', '.') }}</strong></div>
                        </div>
                        <form method="GET" action="{{ route('prototype.app') }}">
                            <input type="hidden" name="screen" value="payment">
                            <input type="hidden" name="package" value="{{ $packageId }}">
                            <label class="field-label" for="start-date">Tanggal mulai</label>
                            <input class="field" id="start-date" type="date" required>
                            <label class="field-label" for="end-date">Tanggal selesai</label>
                            <input class="field" id="end-date" type="date" required>
                            <label class="field-label" for="address">Alamat properti</label>
                            <textarea class="field" id="address" rows="2" placeholder="Masukkan alamat pemasangan"></textarea>
                            <button class="button dark" type="submit">Lanjut ke pembayaran</button>
                        </form>
                        <div class="notice">Simulasi prototype: data formulir tidak disimpan dan tidak membuat pesanan sungguhan.</div>
                    @elseif ($screen === 'payment')
                        <p class="eyebrow">Langkah 2 dari 2</p>
                        <h1>Ringkasan pembayaran.</h1>
                        <div class="panel">
                            <div class="item-row"><span>{{ $selectedPackage['name'] }}</span><span>Rp {{ number_format($selectedPackage['price'], 0, ',', '.') }}</span></div>
                            <div class="item-row"><span>Pengiriman & pemasangan</span><span>Rp 250.000</span></div>
                            <div class="price-line"><span>Total simulasi</span><strong>Rp {{ number_format($selectedPackage['price'] + 250000, 0, ',', '.') }}</strong></div>
                        </div>
                        <form method="GET" action="{{ route('prototype.app') }}">
                            <input type="hidden" name="screen" value="success">
                            <input type="hidden" name="package" value="{{ $packageId }}">
                            <label class="field-label">Metode pembayaran</label>
                            <div class="panel">
                                <label class="item-row"><span>Virtual account</span><input type="radio" name="payment" checked></label>
                                <label class="item-row"><span>QRIS</span><input type="radio" name="payment"></label>
                            </div>
                            <button class="button dark" type="submit">Simulasikan pembayaran berhasil</button>
                        </form>
                        <div class="notice">Tidak ada transaksi atau pembayaran sungguhan yang diproses.</div>
                    @elseif ($screen === 'success')
                        <div class="success-mark" aria-hidden="true">✓</div>
                        <p class="eyebrow" style="text-align:center">Pembayaran berhasil</p>
                        <h1 style="text-align:center">Pesananmu tercatat!</h1>
                        <p style="text-align:center">Ini hanya tampilan simulasi. Status pesanan demo akan menunggu konfirmasi vendor.</p>
                        <div class="panel">
                            <div class="item-row"><span>Kode pesanan</span><strong>ISN-DEMO-001</strong></div>
                            <div class="item-row"><span>Paket</span><strong>{{ $selectedPackage['name'] }}</strong></div>
                        </div>
                        <a class="button dark" href="{{ route('prototype.app', ['screen' => 'orders', 'package' => $packageId]) }}">Lihat status pesanan</a>
                    @elseif ($screen === 'orders')
                        <p class="eyebrow">Aktivitas</p>
                        <h1>Pesanan demo.</h1>
                        <div class="panel">
                            <div class="order-row"><span>ISN-DEMO-001</span><span class="demo-pill">Menunggu konfirmasi</span></div>
                            <h3 style="margin-top:12px">{{ $selectedPackage['name'] ?? 'Japandi Living' }}</h3>
                            <p>Periode sewa · 14 hari</p>
                            <div class="timeline">
                                <div><b>Pesanan dibuat</b><span>Permintaan diterima</span></div>
                                <div><b>Konfirmasi vendor</b><span>Menunggu persetujuan</span></div>
                                <div><b>Pembayaran</b><span>Berikutnya</span></div>
                            </div>
                        </div>
                        <a class="button" href="{{ route('prototype.app', ['screen' => 'catalog']) }}">Cari paket lain</a>
                    @else
                        <p class="eyebrow">Akun</p>
                        <h1>Profil customer.</h1>
                        <div class="panel">
                            <div class="profile-icon" aria-hidden="true">⌂</div>
                            <h3>Nama Customer</h3>
                            <p>Kelola informasi dan lihat aktivitas sewamu di satu tempat.</p>
                            <div class="item-row"><span>Pesanan aktif</span><strong>1</strong></div>
                            <div class="item-row"><span>Pesanan selesai</span><strong>3</strong></div>
                        </div>
                        <div class="notice">Informasi akun di layar ini hanya contoh untuk prototype.</div>
                    @endif
                </main>

                <nav class="tabbar" aria-label="Navigasi prototype">
                    <a class="tab {{ $activeTab === 'home' ? 'active' : '' }}" href="{{ route('prototype.app', ['screen' => 'home']) }}"><span aria-hidden="true">⌂</span><span>Beranda</span></a>
                    <a class="tab {{ $activeTab === 'catalog' ? 'active' : '' }}" href="{{ route('prototype.app', ['screen' => 'catalog']) }}"><span aria-hidden="true">⌕</span><span>Cari</span></a>
                    <a class="tab {{ $activeTab === 'orders' ? 'active' : '' }}" href="{{ route('prototype.app', ['screen' => 'orders']) }}"><span aria-hidden="true">▣</span><span>Pesanan</span></a>
                    <a class="tab {{ $activeTab === 'profile' ? 'active' : '' }}" href="{{ route('prototype.app', ['screen' => 'profile']) }}"><span aria-hidden="true">◉</span><span>Akun</span></a>
                </nav>
            </div>
            <p class="prototype-note">Prototype interaktif · belum terhubung ke pemesanan atau pembayaran asli</p>
        </div>
    </div>
</body>
</html>
