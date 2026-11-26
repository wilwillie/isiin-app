<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(array_keys(Order::STATUS_LABELS))],
        ]);
        $status = $validated['status'] ?? null;
        $orders = $vendor->orders()->with([
            'package',
            'customer',
            'disputes.openedBy',
            'disputes.resolvedBy',
            'disputes.messages.sender',
        ])->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();
        $statuses = Order::STATUS_LABELS;

        return view('vendor.orders.index', compact('orders', 'status', 'statuses'));
    }

    public function accept(Order $order): RedirectResponse
    {
        return $this->transition(
            $order,
            ['pending'],
            'confirmed',
            'Pesanan diterima. Customer diminta melakukan pembayaran.',
            [],
            'vendor.orders.index',
        );
    }

    public function reject(Order $order): RedirectResponse
    {
        return $this->transition($order, ['pending'], 'rejected', 'Pesanan ditolak.');
    }

    public function requestInfo(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->vendor_id === Auth::user()->vendor?->id, 403);

        $validator = Validator::make($request->all(), [
            'vendor_request_note' => ['required', 'string', 'max:2000'],
        ]);
        if ($validator->fails()) {
            return redirect()->route('vendor.orders.index')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $validator->validated();

        if ($order->status !== 'pending') {
            return redirect()->route('vendor.orders.index')
                ->with('error', 'Permintaan informasi hanya dapat dikirim untuk pesanan yang menunggu konfirmasi.');
        }

        $order->update([
            'status' => 'needs_info',
            'vendor_request_note' => $data['vendor_request_note'],
            'customer_response' => null,
        ]);

        return redirect()->route('vendor.orders.index')
            ->with('success', 'Permintaan informasi atau survei dikirim ke pemilik properti.');
    }

    public function installed(Order $order): RedirectResponse
    {
        return $this->transition($order, ['paid'], 'installed', 'Furniture ditandai sudah terpasang.');
    }

    public function complete(Order $order): RedirectResponse
    {
        abort_unless($order->vendor_id === Auth::user()->vendor?->id, 403);
        if ($order->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', 'Pesanan tidak dapat diselesaikan selama komplain masih ditinjau admin.');
        }

        return $this->transition(
            $order,
            ['pickup_pending'],
            'completed',
            'Pengambilan selesai. Pesanan ditandai selesai.',
            ['completed_at' => now()],
            'vendor.orders.index',
        );
    }

    private function transition(
        Order $order,
        array $from,
        string $to,
        string $message,
        array $attributes = [],
        ?string $redirectRoute = null,
    ): RedirectResponse
    {
        abort_unless($order->vendor_id === Auth::user()->vendor?->id, 403);

        if ($order->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', 'Status pesanan tidak dapat diubah selama komplain masih ditinjau admin.');
        }

        if (! in_array($order->status, $from, true)) {
            return back()->with('error', 'Status pesanan tidak bisa diubah ke tahap ini.');
        }

        $order->update(array_merge(['status' => $to], $attributes));

        $redirect = $redirectRoute
            ? redirect()->route($redirectRoute)
            : back();

        return $redirect->with('success', $message);
    }
}
