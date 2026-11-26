<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\OrderPayment;
use App\Services\MidtransSnapService;
use App\Support\OrderRoomPhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use UnexpectedValueException;

class OrderController extends Controller
{
    public function __construct(
        private OrderRoomPhotoStorage $roomPhotoStorage,
        private MidtransSnapService $midtrans,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(array_keys(Order::STATUS_LABELS))],
        ]);
        $status = $validated['status'] ?? null;
        $customerOrders = Auth::user()->orders();
        $orders = Auth::user()->orders()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['package', 'vendor', 'disputes'])
            ->latest()
            ->get();
        $statuses = Order::STATUS_LABELS;
        $stats = [
            'total' => (clone $customerOrders)->count(),
            'active' => (clone $customerOrders)->whereIn('status', [
                'pending', 'needs_info', 'confirmed', 'paid', 'installed', 'pickup_pending',
            ])->count(),
            'awaiting_payment' => (clone $customerOrders)->where('status', 'confirmed')->count(),
            'completed' => (clone $customerOrders)->where('status', 'completed')->count(),
            'spent' => (clone $customerOrders)->whereNotNull('paid_at')->sum('total'),
        ];

        $chartStart = now()->startOfMonth()->subMonths(5);
        $paidOrders = (clone $customerOrders)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $chartStart)
            ->get(['paid_at', 'total']);
        $monthlySpending = collect(range(5, 0))->mapWithKeys(function (int $monthsAgo) use ($paidOrders): array {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $spending = $paidOrders
                ->filter(fn (Order $order): bool => $order->paid_at->format('Y-m') === $month->format('Y-m'))
                ->sum('total');

            return [$month->format('M') => $spending];
        });
        $maxMonthlySpending = max(1, $monthlySpending->max());

        return view('orders.index', compact(
            'orders',
            'status',
            'statuses',
            'stats',
            'monthlySpending',
            'maxMonthlySpending',
        ));
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
            'room_length'      => ['required', 'numeric', 'between:0.1,1000', 'decimal:0,2'],
            'room_width'       => ['required', 'numeric', 'between:0.1,1000', 'decimal:0,2'],
            'ceiling_height'   => ['nullable', 'numeric', 'between:0.1,100', 'decimal:0,2'],
            'room_photos'      => ['required', 'array', 'min:1', 'max:4'],
            'room_photos.*'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.after'            => 'Tanggal selesai harus setelah tanggal mulai.',
            'room_photos.required'      => 'Unggah setidaknya satu foto ruangan.',
            'room_photos.min'           => 'Unggah setidaknya satu foto ruangan.',
            'room_photos.max'           => 'Maksimal empat foto ruangan.',
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();

        // Biaya sewa dihitung per periode (mis. 7 hari), dibulatkan ke atas.
        $days = max(1, (int) abs($start->diffInDays($end)));
        $periods = (int) ceil($days / max(1, $package->duration_days));
        $rental = $package->price * $periods;

        $photos = $request->file('room_photos');
        $storedPhotos = [];
        try {
            $order = DB::transaction(function () use ($data, $package, $start, $end, $rental, $photos, &$storedPhotos) {
                $order = Order::create([
                    'code'             => Order::generateCode(),
                    'customer_id'      => Auth::id(),
                    'package_id'       => $package->id,
                    'vendor_id'        => $package->vendor_id,
                    'start_date'       => $start->toDateString(),
                    'end_date'         => $end->toDateString(),
                    'property_address' => $data['property_address'],
                    'notes'            => $data['notes'] ?? null,
                    'room_length'      => $data['room_length'],
                    'room_width'       => $data['room_width'],
                    'ceiling_height'   => $data['ceiling_height'] ?? null,
                    'rental_fee'       => $rental,
                    'delivery_fee'     => $package->delivery_fee,
                    'installation_fee' => $package->installation_fee,
                    'total'            => $rental + $package->delivery_fee + $package->installation_fee,
                    'status'           => 'pending',
                ]);
                $storedPhotos = $this->roomPhotoStorage->store($photos, $order->id);
                $order->update(['room_photos' => $storedPhotos]);

                return $order;
            });
        } catch (\Throwable $exception) {
            $this->roomPhotoStorage->delete($storedPhotos);

            throw $exception;
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan dibuat. Menunggu konfirmasi vendor.');
    }

    public function show(Order $order)
    {
        $this->ensureOwner($order);
        $order->load([
            'package.items',
            'vendor',
            'review',
            'disputes.openedBy',
            'disputes.resolvedBy',
            'disputes.messages.sender',
            'latestPayment',
        ]);

        return view('orders.show', compact('order'));
    }

    public function pay(Order $order): RedirectResponse
    {
        $this->ensureOwner($order);

        if ($order->status !== 'confirmed') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Pesanan belum bisa dibayar.');
        }

        if ($order->disputes()->where('status', 'open')->exists()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Pembayaran ditunda sampai komplain pada pesanan ini diselesaikan admin.');
        }

        if (! $this->midtrans->isConfigured()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_SIGNATURE_KEY di file .env.');
        }

        $latestPayment = $order->latestPayment;
        if ($latestPayment && $latestPayment->redirect_url
            && in_array($latestPayment->status, ['initiated', 'pending'], true)) {
            return redirect()->away($latestPayment->redirect_url);
        }

        $payment = $order->latestPayment()->create([
            'provider_order_id' => $order->code . '-' . Str::lower(Str::random(10)),
            'status' => 'initiated',
        ]);

        try {
            $transaction = $this->midtrans->createTransaction($order, $payment->provider_order_id);
        } catch (RequestException $exception) {
            report($exception);

            if ($exception->response->status() === 401) {
                $payment->update(['status' => 'failure']);

                return redirect()->route('orders.show', $order)
                    ->with('error', 'Midtrans menolak Server Key. Pastikan MIDTRANS_SERVER_KEY cocok dengan lingkungan Sandbox atau Production yang dipilih.');
            }

            return redirect()->route('orders.show', $order)
                ->with('error', 'Pembayaran belum bisa dimulai. Coba lagi beberapa saat lagi.');
        } catch (ConnectionException|UnexpectedValueException $exception) {
            report($exception);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Pembayaran belum bisa dimulai. Coba lagi beberapa saat lagi.');
        }

        $payment->update([
            'snap_token' => $transaction['token'],
            'redirect_url' => $transaction['redirect_url'],
        ]);

        return redirect()->away($payment->redirect_url);
    }

    public function respond(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOwner($order);
        abort_unless($order->status === 'needs_info', 404);

        $data = $request->validate([
            'customer_response' => ['required', 'string', 'max:2000'],
            'room_length'       => ['required', 'numeric', 'between:0.1,1000', 'decimal:0,2'],
            'room_width'        => ['required', 'numeric', 'between:0.1,1000', 'decimal:0,2'],
            'ceiling_height'    => ['nullable', 'numeric', 'between:0.1,100', 'decimal:0,2'],
            'room_photos'       => ['nullable', 'array', 'max:4'],
            'room_photos.*'     => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $newPhotos = $request->file('room_photos', []);
        $storedPhotos = [];
        $oldPhotos = $order->room_photos ?? [];
        try {
            DB::transaction(function () use ($order, $data, $newPhotos, &$storedPhotos): void {
                if ($newPhotos !== []) {
                    $storedPhotos = $this->roomPhotoStorage->store($newPhotos, $order->id);
                    $order->room_photos = $storedPhotos;
                }

                $order->fill([
                    'room_length' => $data['room_length'],
                    'room_width' => $data['room_width'],
                    'ceiling_height' => $data['ceiling_height'] ?? null,
                    'customer_response' => $data['customer_response'],
                    'status' => 'pending',
                ])->save();
            });
        } catch (\Throwable $exception) {
            $this->roomPhotoStorage->delete($storedPhotos);

            throw $exception;
        }

        if ($newPhotos !== []) {
            $this->roomPhotoStorage->delete($oldPhotos);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Informasi tambahan berhasil dikirim kembali ke vendor.');
    }

    public function photo(Order $order, int $photoIndex)
    {
        $isCustomer = $order->customer_id === Auth::id();
        $isVendor = $order->vendor_id === Auth::user()?->vendor?->id;
        abort_unless($isCustomer || $isVendor, 403);

        $path = $order->room_photos[$photoIndex] ?? null;
        abort_unless(is_string($path), 404);

        return $this->roomPhotoStorage->response($path);
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->ensureOwner($order);

        if ($order->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan selama komplain masih ditinjau admin.');
        }

        if (! in_array($order->status, ['pending', 'needs_info', 'confirmed'], true)) {
            return back()->with('error', 'Pesanan tidak bisa dibatalkan pada tahap ini.');
        }

        if ($order->latestPayment()
            ->whereIn('status', ['initiated', 'pending'])
            ->whereNotNull('redirect_url')
            ->exists()) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan selama status pembayaran Midtrans masih diproses.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan dibatalkan.');
    }

    public function requestPickup(Order $order): RedirectResponse
    {
        $this->ensureOwner($order);

        if ($order->status !== 'installed') {
            return back()->with('error', 'Pengambilan lebih awal hanya bisa diajukan selama masa sewa.');
        }

        if ($order->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', 'Permintaan pengambilan tidak dapat diproses selama komplain masih ditinjau admin.');
        }

        $timezone = config('isiin.display_timezone', 'Asia/Jakarta');
        if ($order->end_date->toDateString() < Carbon::today($timezone)->toDateString()) {
            return back()->with('error', 'Masa sewa sudah berakhir. Tahap pengambilan akan diaktifkan otomatis.');
        }

        $order->update(['status' => 'pickup_pending']);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Permintaan pengambilan lebih awal dikonfirmasi. Vendor dapat melanjutkan pengambilan furniture.');
    }

    private function ensureOwner(Order $order): void
    {
        abort_unless($order->customer_id === Auth::id(), 403);
    }

    private function ensureBookable(Package $package): void
    {
        abort_unless($package->is_active && $package->vendor?->is_verified, 404);
    }
}
