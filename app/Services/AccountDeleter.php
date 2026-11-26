<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Penghapusan akun dengan soft delete + anonimisasi, supaya riwayat pesanan tetap utuh
 * dan email lama bisa dipakai mendaftar lagi.
 */
class AccountDeleter
{
    public static function hasActiveOrders(User $user): bool
    {
        $query = Order::whereIn('status', Order::ACTIVE_STATUSES);

        if ($user->isVendor()) {
            $vendor = $user->vendor;

            return $vendor ? $query->where('vendor_id', $vendor->id)->exists() : false;
        }

        return $query->where('customer_id', $user->id)->exists();
    }

    public static function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            if ($user->isVendor() && $vendor = $user->vendor) {
                // Paket vendor tidak tampil lagi di katalog.
                $vendor->packages()->update(['is_active' => false]);
                $vendor->delete();
            }

            $user->forceFill([
                'name'           => 'Akun dihapus',
                'email'          => 'deleted-' . $user->id . '-' . time() . '@deleted.invalid',
                'phone'          => null,
                'password'       => Str::random(40),
                'remember_token' => null,
            ])->save();

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            $user->delete();
        });
    }
}
