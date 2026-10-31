<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        $orders = $vendor->orders()->with(['package', 'customer'])->latest()->get();

        return view('vendor.orders.index', compact('orders'));
    }

    public function accept(Order $order): RedirectResponse
    {
        return $this->transition($order, ['pending'], 'confirmed', 'Pesanan diterima. Customer diminta melakukan pembayaran.');
    }

    public function reject(Order $order): RedirectResponse
    {
        return $this->transition($order, ['pending'], 'rejected', 'Pesanan ditolak.');
    }

    public function installed(Order $order): RedirectResponse
    {
        return $this->transition($order, ['paid'], 'installed', 'Furniture ditandai sudah terpasang.');
    }

    public function complete(Order $order): RedirectResponse
    {
        return $this->transition($order, ['installed'], 'completed', 'Pengambilan selesai. Pesanan ditandai selesai.');
    }

    private function transition(Order $order, array $from, string $to, string $message): RedirectResponse
    {
        abort_unless($order->vendor_id === Auth::user()->vendor?->id, 403);

        if (! in_array($order->status, $from, true)) {
            return back()->with('error', 'Status pesanan tidak bisa diubah ke tahap ini.');
        }

        $order->update(['status' => $to]);

        return back()->with('success', $message);
    }
}
