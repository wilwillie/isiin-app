<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class MidtransSnapService
{
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'))
            && filled(config('midtrans.signature_key'));
    }

    public function createTransaction(Order $order, string $providerOrderId): array
    {
        $serverKey = config('midtrans.server_key');
        $baseUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $response = Http::acceptJson()
            ->asJson()
            ->withBasicAuth($serverKey, '')
            ->timeout(15)
            ->post($baseUrl . '/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $providerOrderId,
                    'gross_amount' => $order->total,
                ],
                'customer_details' => [
                    'first_name' => $order->customer->name,
                    'email' => $order->customer->email,
                ],
                'callbacks' => [
                    'finish' => route('orders.show', $order),
                ],
            ])
            ->throw()
            ->json();

        if (! is_array($response)
            || ! is_string($response['token'] ?? null)
            || ! is_string($response['redirect_url'] ?? null)) {
            throw new UnexpectedValueException('Midtrans returned an invalid Snap transaction response.');
        }

        return [
            'token' => $response['token'],
            'redirect_url' => $response['redirect_url'],
        ];
    }

    public function getTransactionStatus(string $providerOrderId): array
    {
        $serverKey = config('midtrans.server_key');
        $baseUrl = config('midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $response = Http::acceptJson()
            ->withBasicAuth($serverKey, '')
            ->timeout(15)
            ->get($baseUrl . '/v2/' . rawurlencode($providerOrderId) . '/status')
            ->throw()
            ->json();

        if (! is_array($response)
            || ($response['order_id'] ?? null) !== $providerOrderId
            || ! is_string($response['transaction_status'] ?? null)
            || ! is_string($response['gross_amount'] ?? null)) {
            throw new UnexpectedValueException('Midtrans returned an invalid transaction status response.');
        }

        return $response;
    }

    public function hasValidSignature(array $notification): bool
    {
        $signatureKey = config('midtrans.signature_key');
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $signature = $notification['signature_key'] ?? null;

        if (! is_string($signatureKey) || $signatureKey === ''
            || ! is_string($orderId)
            || ! is_string($statusCode)
            || ! is_string($grossAmount)
            || ! is_string($signature)) {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $signatureKey);

        return hash_equals($expected, $signature);
    }
}
