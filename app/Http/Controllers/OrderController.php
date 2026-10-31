<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()->with(['package', 'vendor'])->latest()->get();

        return view('orders.index', compact('orders'));
    }

    public function create(Package $package)
    {
        $this->ensureBookable($package);

        return view('orders.create', compact('package'));
    }

    public function store(Request $request, Package $package): RedirectResponse
    {
        $this->ensureBookable($package);

        $data = $request->validate([
            'start_date'       => ['required', 'date', 'after_or_equal:today'],
            'end_date'         => ['required', 'date', 'after:start_date'],
            'property_address' => ['required', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.after'            => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();

        // Biaya sewa dihitung per periode (mis. 7 hari), dibulatkan ke atas.
        $days = max(1, (int) abs($start->diffInDays($end)));
        $periods = (int) ceil($days / max(1, $package->duration_days));
        $rental = $package->price * $periods;

        $order = Order::create([
            'code'             => Order::generateCode(),
            'customer_id'      => Auth::id(),
            'package_id'       => $package->id,
            'vendor_id'        => $package->vendor_id,
            'start_date'       => $start->toDateString(),
            'end_date'         => $end->toDateString(),
            'property_address' => $data['property_address'],
            'notes'            => $data['notes'] ?? null,
            'rental_fee'       => $rental,
            'delivery_fee'     => $package->delivery_fee,
            'installation_fee' => $package->installation_fee,
            'deposit'          => $package->deposit,
            'total'            => $rental + $package->delivery_fee + $package->installation_fee + $package->deposit,
            'status'           => 'pending',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan dibuat. Menunggu konfirmasi vendor.');
    }

    public function show(Order $order)
    {
        $this->ensureOwner($order);
        $order->load(['package.items', 'vendor', 'review']);

        return view('orders.show', compact('order'));
    }

    /** Simulasi pembayaran. Ganti dengan integrasi Midtrans/Xendit saat siap. */
    public function pay(Order $order): RedirectResponse
    {
        $this->ensureOwner($order);

        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Pesanan belum bisa dibayar.');
        }

        $order->update(['status' => 'paid', 'paid_at' => now()]);

        return back()->with('success', 'Pembayaran berhasil (simulasi).');
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->ensureOwner($order);

        if (! in_array($order->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Pesanan tidak bisa dibatalkan pada tahap ini.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan dibatalkan.');
    }

    private function ensureOwner(Order $order): void
    {
        abort_unless($order->customer_id === Auth::id(), 403);
    }

    private function ensureBookable(Package $package): void
    {
        abort_unless($package->is_active && $package->vendor->is_verified, 404);
    }
}
