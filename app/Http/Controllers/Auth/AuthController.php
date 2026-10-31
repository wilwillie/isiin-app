<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()));
    }

    public function showRegister(Request $request)
    {
        $role = $request->query('role') === 'vendor' ? 'vendor' : 'customer';

        return view('auth.register', compact('role'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role'          => ['required', 'in:customer,vendor'],
            'name'          => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['required_if:role,vendor', 'nullable', 'string', 'max:120'],
            'city'          => ['nullable', 'string', 'max:80'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => $data['password'],
                'role'     => $data['role'],
            ]);

            if ($data['role'] === 'vendor') {
                Vendor::create([
                    'user_id'       => $user->id,
                    'business_name' => $data['business_name'],
                    'city'          => $data['city'] ?? null,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $message = $user->isVendor()
            ? 'Akun vendor dibuat. Paket Anda akan tampil di katalog setelah diverifikasi admin.'
            : 'Akun berhasil dibuat. Selamat datang di Isiin!';

        return redirect($this->homeFor($user))->with('success', $message);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function homeFor(User $user): string
    {
        return match (true) {
            $user->isAdmin()  => route('admin.vendors.index'),
            $user->isVendor() => route('vendor.dashboard'),
            default           => route('packages.index'),
        };
    }
}
