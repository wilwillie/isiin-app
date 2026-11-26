<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use App\Services\AccountDeleter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChangeRequestController extends Controller
{
    public function index()
    {
        $pending = ProfileChangeRequest::with('user.vendor')->where('status', 'pending')->oldest()->get();
        $history = ProfileChangeRequest::with(['user', 'reviewer'])->where('status', '!=', 'pending')->latest()->take(20)->get();

        return view('admin.requests', compact('pending', 'history'));
    }

    public function approve(Request $request, ProfileChangeRequest $changeRequest): RedirectResponse
    {
        $request->validate(['admin_note' => ['nullable', 'string', 'max:300']]);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $user = $changeRequest->user;
        if (! $user || $user->trashed()) {
            return back()->with('error', 'Akun pemohon sudah tidak aktif.');
        }

        $error = match ($changeRequest->type) {
            'profile'  => $this->applyProfile($changeRequest, $user),
            'password' => $this->applyPassword($changeRequest, $user),
            'delete'   => $this->applyDelete($user),
            default    => 'Jenis permintaan tidak dikenal.',
        };

        if ($error) {
            return back()->with('error', $error);
        }

        $changeRequest->update([
            'status'      => 'approved',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan disetujui dan perubahan sudah diterapkan.');
    }

    public function reject(Request $request, ProfileChangeRequest $changeRequest): RedirectResponse
    {
        $request->validate(
            ['admin_note' => ['required', 'string', 'max:300']],
            ['admin_note.required' => 'Catatan wajib diisi saat menolak permintaan.']
        );

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $changeRequest->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan ditolak.');
    }

    /** Mengembalikan pesan error (string), atau null jika berhasil. */
    private function applyProfile(ProfileChangeRequest $changeRequest, User $user): ?string
    {
        $changes = $changeRequest->payload['changes'] ?? [];

        if (isset($changes['email'])
            && User::where('email', $changes['email'])->where('id', '!=', $user->id)->exists()) {
            return 'Email baru sudah dipakai akun lain. Tolak permintaan ini.';
        }

        DB::transaction(function () use ($user, $changes) {
            $userData = Arr::only($changes, ['name', 'email', 'phone']);
            if ($userData) {
                $user->update($userData);
            }

            $vendorData = Arr::only($changes, ['business_name', 'city']);
            if ($vendorData && $user->vendor) {
                $user->vendor->update($vendorData);
            }
        });

        return null;
    }

    private function applyPassword(ProfileChangeRequest $changeRequest, User $user): ?string
    {
        $hash = $changeRequest->payload['password_hash'] ?? null;
        if (! $hash) {
            return 'Data password pada permintaan ini tidak ditemukan.';
        }

        // Hash sudah jadi dari saat permintaan dibuat; ditulis langsung agar tidak di-hash ulang.
        DB::table('users')->where('id', $user->id)->update(['password' => $hash, 'remember_token' => null]);

        return null;
    }

    private function applyDelete(User $user): ?string
    {
        if (AccountDeleter::hasActiveOrders($user)) {
            return 'Akun belum bisa dihapus karena masih punya pesanan yang berjalan.';
        }

        AccountDeleter::delete($user);

        return null;
    }
}
