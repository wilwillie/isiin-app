<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use App\Models\Vendor;
use App\Services\AccountDeleter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Profil vendor: SEMUA perubahan hanya berupa permintaan. Data baru berlaku setelah admin menyetujui.
 */
class AccountController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $vendor = $this->vendor();

        $pending = $user->changeRequests()->where('status', 'pending')->get()->keyBy('type');

        $form = $this->currentValues();
        if ($pending->has('profile')) {
            $form = array_merge($form, $pending->get('profile')->payload['changes'] ?? []);
        }

        $history = $user->changeRequests()->latest()->take(10)->get();

        return view('vendor.account', compact('user', 'vendor', 'pending', 'form', 'history'));
    }

    public function requestProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'business_name' => ['required', 'string', 'max:120'],
            'city'          => ['nullable', 'string', 'max:80'],
            'email'         => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'         => ['nullable', 'string', 'max:30'],
        ]);

        $changes = [];
        foreach ($this->currentValues() as $field => $value) {
            $new = $data[$field] ?? null;
            if ((string) $new !== (string) $value) {
                $changes[$field] = $new;
            }
        }

        if (! $changes) {
            return back()->with('error', 'Tidak ada data yang berubah dari data saat ini.');
        }

        ProfileChangeRequest::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'profile', 'status' => 'pending'],
            ['payload' => ['changes' => $changes]]
        );

        return back()->with('success', 'Permintaan perubahan profil dikirim. Data baru berlaku setelah admin menyetujui.');
    }

    public function requestPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        // Yang disimpan hanya hash, bukan password asli.
        ProfileChangeRequest::updateOrCreate(
            ['user_id' => Auth::id(), 'type' => 'password', 'status' => 'pending'],
            ['payload' => ['password_hash' => Hash::make($request->input('password'))]]
        );

        return back()->with('success', 'Permintaan ganti password dikirim. Password lama tetap berlaku sampai admin menyetujui.');
    }

    public function requestDelete(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'reason'           => ['nullable', 'string', 'max:300'],
        ]);

        if (AccountDeleter::hasActiveOrders($user)) {
            return back()->with('error', 'Akun belum bisa diajukan untuk dihapus karena masih ada pesanan yang berjalan.');
        }

        ProfileChangeRequest::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'delete', 'status' => 'pending'],
            ['payload' => ['reason' => $request->input('reason')]]
        );

        return back()->with('success', 'Permintaan hapus akun dikirim. Akun baru dihapus setelah admin menyetujui.');
    }

    public function cancel(ProfileChangeRequest $changeRequest): RedirectResponse
    {
        abort_unless($changeRequest->user_id === Auth::id(), 403);

        if ($changeRequest->status === 'pending') {
            $changeRequest->update(['status' => 'cancelled']);
        }

        return back()->with('success', 'Permintaan dibatalkan.');
    }

    private function vendor(): Vendor
    {
        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        return $vendor;
    }

    private function currentValues(): array
    {
        $user = Auth::user();
        $vendor = $this->vendor();

        return [
            'name'          => $user->name,
            'business_name' => $vendor->business_name,
            'city'          => $vendor->city,
            'email'         => $user->email,
            'phone'         => $user->phone,
        ];
    }
}
