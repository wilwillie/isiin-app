<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Order;
use App\Support\DisputeEvidenceStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisputeController extends Controller
{
    public function __construct(private DisputeEvidenceStorage $evidenceStorage) {}

    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOrderParticipant($order);
        $categories = Dispute::categoriesForOrder($order);
        if ($categories === []) {
            return back()->with('error', 'Laporan hanya dapat dibuat setelah vendor mengonfirmasi pesanan.');
        }

        if ($order->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', 'Selesaikan laporan yang masih ditinjau sebelum membuat laporan baru.');
        }

        $data = $request->validate([
            'category' => ['required', 'string', 'in:' . implode(',', array_keys($categories))],
            'description' => ['required', 'string', 'max:3000'],
            'evidence_photos' => ['nullable', 'array', 'max:4'],
            'evidence_photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photos = $request->file('evidence_photos', []);
        $storedPhotos = [];
        try {
            $dispute = DB::transaction(function () use ($order, $data, $photos, &$storedPhotos) {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                if (! array_key_exists($data['category'], Dispute::categoriesForOrder($lockedOrder))) {
                    throw ValidationException::withMessages([
                        'category' => 'Kategori laporan tidak sesuai dengan tahap pesanan saat ini.',
                    ]);
                }
                if ($lockedOrder->disputes()->where('status', 'open')->exists()) {
                    throw ValidationException::withMessages([
                        'category' => 'Selesaikan laporan yang masih ditinjau sebelum membuat laporan baru.',
                    ]);
                }

                $dispute = $lockedOrder->disputes()->create([
                    'opened_by' => Auth::id(),
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'status' => 'open',
                ]);
                $storedPhotos = $this->evidenceStorage->store($photos, $dispute->id);
                $dispute->update(['evidence_photos' => $storedPhotos]);

                return $dispute;
            });
        } catch (\Throwable $exception) {
            $this->evidenceStorage->delete($storedPhotos);

            throw $exception;
        }

        return back()->with('success', 'Laporan komplain dibuat. Admin akan meninjau laporan dan bukti yang dikirim.');
    }

    public function message(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->ensureDisputeParticipant($dispute);
        if ($dispute->status !== 'open') {
            return back()->with('error', 'Diskusi pada komplain yang sudah selesai telah ditutup.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'evidence_photos' => ['nullable', 'array', 'max:4'],
            'evidence_photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photos = $request->file('evidence_photos', []);
        $storedPhotos = [];
        try {
            DB::transaction(function () use ($dispute, $data, $photos, &$storedPhotos): void {
                $storedPhotos = $this->evidenceStorage->store($photos, $dispute->id);
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'sender_id' => Auth::id(),
                    'body' => $data['body'],
                    'evidence_photos' => $storedPhotos,
                ]);
            });
        } catch (\Throwable $exception) {
            $this->evidenceStorage->delete($storedPhotos);

            throw $exception;
        }

        return back()->with('success', 'Tanggapan berhasil dikirim.');
    }

    public function evidence(Request $request, Dispute $dispute, int $evidenceIndex)
    {
        $this->ensureDisputeParticipant($dispute);

        $messageId = $request->query('message');
        if ($messageId !== null) {
            abort_unless(is_numeric($messageId), 404);
            $message = $dispute->messages()->findOrFail((int) $messageId);
            $path = $message->evidence_photos[$evidenceIndex] ?? null;
        } else {
            $path = $dispute->evidence_photos[$evidenceIndex] ?? null;
        }

        abort_unless(is_string($path), 404);

        return $this->evidenceStorage->response($path);
    }

    private function ensureOrderParticipant(Order $order): void
    {
        $user = Auth::user();
        $isCustomer = $order->customer_id === $user->id;
        $isVendor = $order->vendor_id === $user->vendor?->id;

        abort_unless($isCustomer || $isVendor, 403);
    }

    private function ensureDisputeParticipant(Dispute $dispute): void
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isCustomer = $dispute->order->customer_id === $user->id;
        $isVendor = $dispute->order->vendor_id === $user->vendor?->id;

        abort_unless($isAdmin || $isCustomer || $isVendor, 403);
    }
}
