<?php

namespace App\Http\Controllers;

use App\Services\AccountDeleter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Profil untuk customer (pemilik properti/agen) dan admin.
 * Perubahan langsung berlaku. Vendor memakai Vendor\AccountController (perlu persetujuan admin).
 */
class AccountController extends Controller
{
    public function show()
    {
        return view('account.show', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ];
        if ($user->isCustomer()) {
            $rules['phone'] = ['nullable', 'string', 'max:30'];
        }

        $data = $request->validate($rules);

        // Mengganti email butuh konfirmasi password saat ini.
        if (strcasecmp($data['email'], $user->email) !== 0) {
            $request->validate(['current_password' => ['required', 'current_password']]);
        }

        $user->update(Arr::only($data, ['name', 'email', 'phone']));

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        // Cast "hashed" pada model User menyimpan password dalam bentuk hash (bcrypt).
        Auth::user()->update(['password' => $request->input('password')]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isCustomer(), 403);

        $request->validate(['current_password' => ['required', 'current_password']]);

        if (AccountDeleter::hasActiveOrders($user)) {
            return back()->with('error', 'Akun belum bisa dihapus karena masih ada pesanan yang berjalan. Selesaikan atau batalkan dulu.');
        }

        Auth::logout();
        AccountDeleter::delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Akun Anda telah dihapus.');
    }
}
