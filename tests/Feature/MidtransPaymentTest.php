<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-test-key';
    private const SIGNATURE_KEY = 'SB-Mid-signature-test-key';

    public function test_customer_is_redirected_to_snap_without_marking_order_paid(): void
    {
        config([
            'midtrans.server_key' => self::SERVER_KEY,
            'midtrans.signature_key' => self::SIGNATURE_KEY,
            'midtrans.is_production' => false,
        ]);
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-test',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-test',
            ], 201),
        ]);
        [$customer, $order] = $this->makeOrder();

        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-test');

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
        $this->assertSame('initiated', $order->latestPayment()->sole()->status);

        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-test');

        Http::assertSentCount(1);
    }

    public function test_checkout_is_blocked_when_signature_key_is_missing(): void
    {
        config([
            'midtrans.server_key' => self::SERVER_KEY,
            'midtrans.signature_key' => null,
            'midtrans.is_production' => false,
        ]);
        Http::fake();
        [$customer, $order] = $this->makeOrder();

        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error', 'Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_SIGNATURE_KEY di file .env.');

        Http::assertNothingSent();
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_unauthorized_server_key_returns_to_order_details_and_marks_attempt_failed(): void
    {
        config([
            'midtrans.server_key' => self::SERVER_KEY,
            'midtrans.signature_key' => self::SIGNATURE_KEY,
            'midtrans.is_production' => false,
        ]);
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'error' => 'Unauthorized',
            ], 401),
        ]);
        [$customer, $order] = $this->makeOrder();

        $this->actingAs($customer)
            ->from(route('orders.photos.show', [$order, 0]))
            ->post(route('orders.pay', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error', 'Midtrans menolak Server Key. Pastikan MIDTRANS_SERVER_KEY cocok dengan lingkungan Sandbox atau Production yang dipilih.');

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame('failure', $order->latestPayment()->sole()->status);
    }

    public function test_valid_settlement_notification_marks_order_paid_idempotently(): void
    {
        [, $order] = $this->makeOrder();
        $payment = $order->latestPayment()->create([
            'provider_order_id' => 'ISN-TEST-PAYMENT-1',
            'status' => 'pending',
        ]);
        $notification = $this->notification($payment->provider_order_id, 'settlement');
        $this->fakeTransactionStatus($payment->provider_order_id, 'settlement');

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();
        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame('settlement', $payment->fresh()->status);
    }

    public function test_invalid_signature_or_amount_cannot_mark_order_paid(): void
    {
        [, $order] = $this->makeOrder();
        $payment = $order->latestPayment()->create([
            'provider_order_id' => 'ISN-TEST-PAYMENT-2',
            'status' => 'pending',
        ]);
        $invalidSignature = $this->notification($payment->provider_order_id, 'settlement');
        $invalidSignature['signature_key'] = 'invalid';

        $this->postJson(route('midtrans.notifications'), $invalidSignature)->assertForbidden();

        $wrongAmount = $this->notification($payment->provider_order_id, 'settlement', '1000001.00');
        $this->postJson(route('midtrans.notifications'), $wrongAmount)->assertUnprocessable();

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_notification_cannot_override_the_status_reported_by_midtrans(): void
    {
        [, $order] = $this->makeOrder();
        $payment = $order->latestPayment()->create([
            'provider_order_id' => 'ISN-TEST-PAYMENT-3',
            'status' => 'pending',
        ]);
        $forgedSuccessNotification = $this->notification($payment->provider_order_id, 'settlement');
        $this->fakeTransactionStatus($payment->provider_order_id, 'pending');

        $this->postJson(route('midtrans.notifications'), $forgedSuccessNotification)->assertOk();

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_expired_payment_can_be_retried_with_a_new_midtrans_order_id(): void
    {
        config([
            'midtrans.server_key' => self::SERVER_KEY,
            'midtrans.signature_key' => self::SIGNATURE_KEY,
            'midtrans.is_production' => false,
        ]);
        $snapCallCount = 0;
        Http::fake(function (\Illuminate\Http\Client\Request $request) use (&$snapCallCount) {
            if ($request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions') {
                $snapCallCount++;

                return Http::response([
                    'token' => $snapCallCount === 1 ? 'snap-token-first' : 'snap-token-second',
                    'redirect_url' => $snapCallCount === 1
                        ? 'https://app.sandbox.midtrans.com/snap/v2/vtweb/first'
                        : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/second',
                ], 201);
            }

            $pathSegments = explode('/', trim((string) parse_url($request->url(), PHP_URL_PATH), '/'));
            $providerOrderId = $pathSegments[count($pathSegments) - 2];

            return Http::response([
                'order_id' => $providerOrderId,
                'gross_amount' => '1000000.00',
                'currency' => 'IDR',
                'transaction_status' => 'expire',
                'transaction_id' => 'midtrans-expired-test',
                'payment_type' => 'bank_transfer',
                'fraud_status' => 'accept',
            ]);
        });
        [$customer, $order] = $this->makeOrder();

        $this->actingAs($customer)->post(route('orders.pay', $order))->assertRedirect();
        $firstPayment = $order->latestPayment()->firstOrFail();
        $expiredNotification = $this->notification($firstPayment->provider_order_id, 'expire');
        $this->postJson(route('midtrans.notifications'), $expiredNotification)->assertOk();

        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/second');

        $this->assertSame(2, OrderPayment::query()->where('order_id', $order->id)->count());
        $this->assertSame(
            1,
            OrderPayment::query()
                ->where('order_id', $order->id)
                ->where('provider_order_id', '!=', $firstPayment->provider_order_id)
                ->count(),
        );
    }

    private function notification(string $providerOrderId, string $status, string $grossAmount = '1000000.00'): array
    {
        config([
            'midtrans.server_key' => self::SERVER_KEY,
            'midtrans.signature_key' => self::SIGNATURE_KEY,
        ]);

        $notification = [
            'order_id' => $providerOrderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'currency' => 'IDR',
            'transaction_status' => $status,
            'transaction_id' => 'midtrans-transaction-test',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'] . $notification['status_code'] . $notification['gross_amount'] . self::SIGNATURE_KEY,
        );

        return $notification;
    }

    private function fakeTransactionStatus(string $providerOrderId, string $status): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/' . $providerOrderId . '/status' => Http::response([
                'order_id' => $providerOrderId,
                'gross_amount' => '1000000.00',
                'currency' => 'IDR',
                'transaction_status' => $status,
                'transaction_id' => 'midtrans-transaction-test',
                'payment_type' => 'bank_transfer',
                'fraud_status' => 'accept',
            ]),
        ]);
    }

    private function makeOrder(): array
    {
        $customer = User::create([
            'name' => 'Customer Test',
            'email' => 'midtrans-customer-' . Str::random(8) . '@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor Test',
            'email' => 'midtrans-vendor-' . Str::random(8) . '@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor Test',
            'is_verified' => true,
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Tes',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'is_active' => true,
        ]);
        $order = Order::create([
            'code' => 'ISN-20261125-' . strtoupper(Str::random(4)),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'property_address' => 'Jakarta',
            'room_length' => 5,
            'room_width' => 4,
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => 'confirmed',
        ]);

        return [$customer, $order];
    }
}
