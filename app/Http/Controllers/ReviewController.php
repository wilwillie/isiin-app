<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        if ($order->status !== 'completed' || $order->review()->exists()) {
            return back()->with('error', 'Ulasan tidak bisa dikirim untuk pesanan ini.');
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::create([
            'order_id'    => $order->id,
            'package_id'  => $order->package_id,
            'customer_id' => Auth::id(),
            'rating'      => $data['rating'],
            'comment'     => $data['comment'] ?? null,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda.');
    }
}
