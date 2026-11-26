<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::query()
            ->with(['order.customer', 'order.vendor', 'openedBy'])
            ->withCount('messages')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15);

        $openCount = Dispute::query()->where('status', 'open')->count();

        return view('admin.disputes.index', compact('disputes', 'openCount'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'order.customer',
            'order.vendor',
            'order.package',
            'openedBy',
            'resolvedBy',
            'messages.sender',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        if ($dispute->status !== 'open') {
            return back()->with('error', 'Komplain ini sudah diselesaikan.');
        }

        $data = $request->validate([
            'resolution_outcome' => ['required', 'string', 'in:' . implode(',', array_keys(Dispute::OUTCOMES))],
            'resolution_note' => ['required', 'string', 'max:3000'],
        ]);

        $dispute->update([
            'status' => 'resolved',
            'resolution_outcome' => $data['resolution_outcome'],
            'resolution_note' => $data['resolution_note'],
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        return redirect()->route('admin.disputes.show', $dispute)
            ->with('success', 'Keputusan komplain berhasil dicatat. Tidak ada refund otomatis pada versi ini.');
    }
}
