@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Pengaturan akun</p>
        <h1 class="page-heading mt-2">Profil Saya</h1>
        <p class="page-subtitle">{{ $user->isAdmin() ? 'Administrator' : 'Pemilik properti / agen' }}</p>
    </div>

    <div class="mt-4">
        @include('partials.errors')
    </div>

    <div class="mt-2 grid items-start gap-4 sm:gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('account.profile.update') }}" class="card space-y-4 p-5 sm:p-6">
            @csrf
            @method('PUT')
            <h2 class="section-heading">Data profil</h2>

            <div>
                <label class="label" for="name">Nama</label>
                <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            @if ($user->isCustomer())
                <div>
                    <label class="label" for="phone">Nomor telepon</label>
                    <input class="input" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>
            @endif
            <div>
                <label class="label" for="profile_current_password">Password saat ini</label>
                <div class="relative">
                    <input class="input pr-12" id="profile_current_password" type="password" name="current_password" autocomplete="current-password">
                    @include('partials.password-toggle', ['inputId' => 'profile_current_password'])
                </div>
                <p class="text-xs text-ink/50 mt-1">Hanya wajib diisi jika Anda mengganti email.</p>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Profil</button>
        </form>

        <form method="POST" action="{{ route('account.password.update') }}" class="card space-y-4 p-5 sm:p-6">
            @csrf
            @method('PUT')
            <h2 class="section-heading">Ganti password</h2>

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
            <p class="text-xs text-ink/50">Password disimpan dalam bentuk hash (bcrypt), sehingga tidak bisa dibaca kembali, bahkan oleh admin.</p>
            <button type="submit" class="btn btn-primary">Ganti Password</button>
        </form>

        @if ($user->isCustomer())
            <form method="POST" action="{{ route('account.destroy') }}" class="card space-y-4 border-rose-200 p-5 sm:p-6 lg:col-span-2"
                  onsubmit="return confirm('Hapus akun secara permanen? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <h2 class="section-heading text-rose-700">Hapus akun saya</h2>
                <p class="text-sm text-ink/70">Akun akan dihapus dan data pribadi Anda dianonimkan. Akun tidak bisa dihapus selama masih ada pesanan yang berjalan.</p>
                <div class="max-w-sm">
                    <label class="label" for="del_current_password">Password saat ini</label>
                    <div class="relative">
                        <input class="input pr-12" id="del_current_password" type="password" name="current_password" required autocomplete="current-password">
                        @include('partials.password-toggle', ['inputId' => 'del_current_password'])
                    </div>
                </div>
                <button type="submit" class="btn btn-danger w-full sm:w-auto">Hapus Akun Saya</button>
            </form>
        @endif
    </div>
@endsection
