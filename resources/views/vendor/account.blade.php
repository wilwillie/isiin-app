@extends('layouts.app')

@section('title', 'Profil Vendor')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Pengaturan akun</p>
        <h1 class="page-heading mt-2">Profil Vendor</h1>
    </div>

    <div class="mb-5 rounded-2xl border border-sky-200/70 bg-sky-50 px-4 py-3.5 text-sm leading-6 text-sky-900 sm:mb-6 sm:px-5">
        Permintaan perubahan profil, password, atau penghapusan akun berlaku setelah disetujui admin.
    </div>

    <div class="mt-4">
        @include('partials.errors')
    </div>

    <div class="mt-2 grid items-start gap-4 sm:gap-6 lg:grid-cols-2">
        {{-- Ubah profil --}}
        <form method="POST" action="{{ route('vendor.account.profile') }}" class="card space-y-4 p-5 sm:p-6">
            @csrf
            <h2 class="section-heading">Data profil</h2>

            @if ($pending->has('profile'))
                @php $pp = $pending->get('profile'); @endphp
                <div class="rounded-xl border border-amber-200/70 bg-amber-50 px-3 py-2.5 text-xs leading-5 text-amber-800">
                    Ada permintaan perubahan yang menunggu admin:
                    @foreach (($pp->payload['changes'] ?? []) as $field => $value)
                        <br>• {{ \App\Models\ProfileChangeRequest::FIELD_LABELS[$field] ?? $field }} → {{ $value ?? '-' }}
                    @endforeach
                    <br>Mengirim ulang akan menggantikan permintaan ini.
                </div>
            @endif

            <div>
                <label class="label" for="name">Nama lengkap</label>
                <input class="input" id="name" name="name" value="{{ old('name', $form['name']) }}" required>
            </div>
            <div>
                <label class="label" for="business_name">Nama usaha</label>
                <input class="input" id="business_name" name="business_name" value="{{ old('business_name', $form['business_name']) }}" required>
            </div>
            <div>
                <label class="label" for="city">Kota</label>
                <input class="input" id="city" name="city" value="{{ old('city', $form['city']) }}">
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email', $form['email']) }}" required>
            </div>
            <div>
                <label class="label" for="phone">Nomor telepon</label>
                <input class="input" id="phone" name="phone" value="{{ old('phone', $form['phone']) }}">
            </div>
            <button type="submit" class="btn btn-primary">Ajukan Perubahan Profil</button>
        </form>

        <div class="space-y-6">
            {{-- Ganti password --}}
            <form method="POST" action="{{ route('vendor.account.password') }}" class="card space-y-4 p-5 sm:p-6">
                @csrf
                <h2 class="section-heading">Ganti password</h2>

                @if ($pending->has('password'))
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50 px-3 py-2.5 text-xs leading-5 text-amber-800">
                        Permintaan ganti password menunggu admin. Password lama tetap berlaku sampai disetujui.
                    </div>
                @endif

                <div>
                    <label class="label" for="pw_current_password">Password saat ini</label>
                    <div class="relative">
                        <input class="input pr-12" id="pw_current_password" type="password" name="current_password" required autocomplete="current-password">
                        @include('partials.password-toggle', ['inputId' => 'pw_current_password'])
                    </div>
                </div>
                <div>
                    <label class="label" for="pw_password">Password baru (minimal 8 karakter)</label>
                    <div class="relative">
                        <input class="input pr-12" id="pw_password" type="password" name="password" required autocomplete="new-password">
                        @include('partials.password-toggle', ['inputId' => 'pw_password'])
                    </div>
                </div>
                <div>
                    <label class="label" for="pw_password_confirmation">Ulangi password baru</label>
                    <div class="relative">
                        <input class="input pr-12" id="pw_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                        @include('partials.password-toggle', ['inputId' => 'pw_password_confirmation'])
                    </div>
                </div>
                <p class="text-xs text-ink/50">Password baru disimpan dalam bentuk hash (bcrypt), tidak pernah sebagai teks asli.</p>
                <button type="submit" class="btn btn-primary">Ajukan Ganti Password</button>
            </form>

            {{-- Hapus akun --}}
            <form method="POST" action="{{ route('vendor.account.delete') }}" class="card space-y-4 p-5 sm:p-6"
                  onsubmit="return confirm('Ajukan penghapusan akun vendor?')">
                @csrf
                <h2 class="section-heading text-rose-700">Hapus akun vendor</h2>

                @if ($pending->has('delete'))
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50 px-3 py-2.5 text-xs leading-5 text-amber-800">
                        Permintaan hapus akun sedang menunggu admin.
                    </div>
                @endif

                <p class="text-sm text-ink/70">Setelah disetujui, akun dan semua paket Anda tidak aktif lagi. Tidak bisa diajukan selama masih ada pesanan yang berjalan.</p>
                <div>
                    <label class="label" for="reason">Alasan (opsional)</label>
                    <textarea class="input" id="reason" name="reason" rows="2">{{ old('reason') }}</textarea>
                </div>
                <div>
                    <label class="label" for="del_current_password">Password saat ini</label>
                    <div class="relative">
                        <input class="input pr-12" id="del_current_password" type="password" name="current_password" required autocomplete="current-password">
                        @include('partials.password-toggle', ['inputId' => 'del_current_password'])
                    </div>
                </div>
                <button type="submit" class="btn btn-danger w-full sm:w-auto">Ajukan Hapus Akun</button>
            </form>
        </div>
    </div>

    {{-- Permintaan yang masih menunggu: bisa dibatalkan --}}
    @if ($pending->isNotEmpty())
        <div class="card mt-6 p-5 sm:p-6">
            <h2 class="section-heading">Permintaan menunggu admin</h2>
            <div class="mt-3 space-y-2">
                @foreach ($pending as $req)
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span>{{ $req->type_label }} · diajukan {{ $req->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</span>
                        <form method="POST" action="{{ route('vendor.account.cancel', $req) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline">Batalkan</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Riwayat --}}
    <div class="card mt-6 overflow-hidden">
        <div class="border-b border-ink/5 px-5 py-4 sm:px-6"><h2 class="section-heading">Riwayat permintaan</h2></div>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[36rem] text-sm">
                <thead class="bg-[#faf8f3] text-left text-ink/60">
                    <tr>
                        <th class="py-2 pr-3">Tanggal</th>
                        <th class="py-2 pr-3">Jenis</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2">Catatan admin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $req)
                        <tr class="border-t border-ink/5">
                            <td class="py-2 pr-3">{{ $req->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</td>
                            <td class="py-2 pr-3">{{ $req->type_label }}</td>
                            <td class="py-2 pr-3"><span class="badge {{ $req->status_class }}">{{ $req->status_label }}</span></td>
                            <td class="py-2">{{ $req->admin_note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-3 text-ink/60">Belum ada permintaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
