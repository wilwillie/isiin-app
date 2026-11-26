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

        $vendorOrders = $vendor->orders();
        $completedOrders = (clone $vendorOrders)->where('status', 'completed');
        $stats = [
            'packages' => $vendor->packages()->count(),
            'pending'  => $vendor->orders()->whereIn('status', ['pending', 'needs_info'])->count(),
            'active'   => $vendor->orders()->whereIn('status', ['confirmed', 'paid', 'installed', 'pickup_pending'])->count(),
            'pickup'   => $vendor->orders()->where('status', 'pickup_pending')->count(),
            'payout'   => (clone $completedOrders)->get()
                ->sum(fn ($order) => $order->vendor_payout),
        ];
        $chartStart = now()->startOfMonth()->subMonths(5);
        $completedInPeriod = (clone $completedOrders)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $chartStart)
            ->get(['completed_at', 'rental_fee', 'delivery_fee', 'installation_fee']);
        $monthlyPayout = collect(range(5, 0))->mapWithKeys(function (int $monthsAgo) use ($completedInPeriod): array {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $payout = $completedInPeriod
                ->filter(fn ($order): bool => $order->completed_at->format('Y-m') === $month->format('Y-m'))
                ->sum(fn ($order): int => $order->vendor_payout);

            return [$month->format('M') => $payout];
        });
        $maxMonthlyPayout = max(1, $monthlyPayout->max());
        $recentOrders = $vendor->orders()
            ->with(['package', 'customer'])
            ->latest()
            ->limit(5)
            ->get();

        return view('vendor.dashboard', compact(
            'vendor',
            'stats',
            'monthlyPayout',
            'maxMonthlyPayout',
            'recentOrders',
        ));
    }
}
