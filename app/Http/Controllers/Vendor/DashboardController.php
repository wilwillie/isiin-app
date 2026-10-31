<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        $stats = [
            'packages' => $vendor->packages()->count(),
            'pending'  => $vendor->orders()->where('status', 'pending')->count(),
            'active'   => $vendor->orders()->whereIn('status', ['confirmed', 'paid', 'installed'])->count(),
            'payout'   => $vendor->orders()->where('status', 'completed')->get()
                ->sum(fn ($order) => $order->vendor_payout),
        ];

        return view('vendor.dashboard', compact('vendor', 'stats'));
    }
}
