<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\MidtransSnapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransSnapService $midtrans): JsonResponse
    {
        $notification = $request->all();

        abort_unless($midtrans->hasValidSignature($notification), 403, 'Invalid Midtrans signature.');

        $payment = OrderPayment::query()
            ->where('provider_order_id', $notification['order_id'])
            ->with('order')
            ->first();

        abort_unless($payment, 404, 'Payment attempt not found.');

        if (! in_array($notification['currency'] ?? 'IDR', ['IDR'], true)
            || ! $this->amountMatchesOrder($notification['gross_amount'], $payment->order->total)) {
            abort(422, 'Notification amount or currency does not match the order.');
        }

        $transaction = $midtrans->getTransactionStatus($payment->provider_order_id);
        if (($transaction['currency'] ?? null) !== 'IDR'
            || ! $this->amountMatchesOrder($transaction['gross_amount'], $payment->order->total)) {
            abort(422, 'Midtrans transaction amount or currency does not match the order.');
        }

        DB::transaction(function () use ($transaction, $payment): void {
            $payment = OrderPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
            $status = $transaction['transaction_status'];
            $successfulStatuses = ['capture', 'settlement'];

            $alreadySuccessful = in_array($payment->status, $successfulStatuses, true);
            if (! $alreadySuccessful || in_array($status, $successfulStatuses, true)) {
                $payment->update([
                    'status' => $status,
                    'transaction_id' => $transaction['transaction_id'] ?? $payment->transaction_id,
                    'payment_type' => $transaction['payment_type'] ?? $payment->payment_type,
                ]);
            }

            $isPaid = $status === 'settlement'
                || ($status === 'capture' && ($transaction['fraud_status'] ?? null) === 'accept');

            if ($isPaid && in_array($order->status, ['confirmed', 'paid'], true)) {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => $order->paid_at ?? now(),
                ]);
            }
        });

        return response()->json(['message' => 'Notification received.']);
    }

    private function amountMatchesOrder(string $grossAmount, int $orderTotal): bool
    {
        if (! preg_match('/^\d+(?:\.00)?$/D', $grossAmount)) {
            return false;
        }

        $amount = explode('.', $grossAmount, 2)[0];

        return ltrim($amount, '0') === (string) $orderTotal;
    }
}
