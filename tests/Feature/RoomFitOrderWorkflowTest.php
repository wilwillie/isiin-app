<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use App\Support\OrderRoomPhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoomFitOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private array $photoPaths = [];

    public function test_vendor_can_request_more_room_information_before_acceptance_and_payment(): void
    {
        config([
            'midtrans.server_key' => 'SB-Mid-server-test-key',
            'midtrans.signature_key' => 'SB-Mid-signature-test-key',
            'midtrans.is_production' => false,
        ]);
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            if ($request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions') {
                return Http::response([
                    'token' => 'snap-token-room-fit',
                    'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/room-fit',
                ], 201);
            }

            $pathSegments = explode('/', trim((string) parse_url($request->url(), PHP_URL_PATH), '/'));
            $providerOrderId = $pathSegments[count($pathSegments) - 2];

            return Http::response([
                'order_id' => $providerOrderId,
                'gross_amount' => '1000000.00',
                'currency' => 'IDR',
                'transaction_status' => 'settlement',
                'transaction_id' => 'midtrans-room-fit-test',
                'payment_type' => 'bank_transfer',
                'fraud_status' => 'accept',
            ]);
        });
        [$customer, $vendorUser, $package] = $this->makeBookingParties();
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);

        $this->actingAs($customer)
            ->post(route('orders.store', $package), [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
                'property_address' => 'Jakarta',
                'room_length' => '5',
                'room_width' => '4',
                'room_photos' => [UploadedFile::fake()->createWithContent('ruangan.png', $image)],
            ])
            ->assertRedirect();

        $order = Order::query()->sole();
        $this->photoPaths = $order->room_photos;
        $this->assertSame('pending', $order->status);
        $this->assertFileExists(storage_path('app/private/' . $this->photoPaths[0]));
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Foto ruangan');

        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertSee('Minta Info / Survei')
            ->assertSee('Foto ruangan');
        $this->actingAs($vendorUser)
            ->get(route('orders.photos.show', [$order, 0]))
            ->assertOk();
        $otherCustomer = User::create([
            'name' => 'Customer Lain',
            'email' => 'room-fit-other@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $this->actingAs($otherCustomer)
            ->get(route('orders.photos.show', [$order, 0]))
            ->assertForbidden();

        $this->actingAs($vendorUser)
            ->withHeader('Referer', route('orders.photos.show', [$order, 0]))
            ->post(route('vendor.orders.request-info', $order), [
                'vendor_request_note' => '',
            ])
            ->assertRedirect(route('vendor.orders.index'))
            ->assertSessionHasErrors('vendor_request_note');

        $this->actingAs($vendorUser)
            ->withHeader('Referer', route('orders.photos.show', [$order, 0]))
            ->post(route('vendor.orders.request-info', $order), [
                'vendor_request_note' => 'Mohon foto area pintu dan konfirmasi lebar akses.',
            ])
            ->assertRedirect(route('vendor.orders.index'));
        $order->refresh();
        $this->assertSame('needs_info', $order->status);
        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertDontSee('Menunggu pemilik properti mengirim informasi tambahan.');
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Mohon foto area pintu dan konfirmasi lebar akses.')
            ->assertSee('Kirim informasi ke vendor');

        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertSessionHas('error');
        $this->assertSame('needs_info', $order->fresh()->status);

        $this->actingAs($customer)
            ->post(route('orders.respond', $order), [
                'customer_response' => 'Lebar pintu 90 cm.',
                'room_length' => '5',
                'room_width' => '4',
            ])
            ->assertRedirect();
        $this->assertSame('pending', $order->fresh()->status);

        $this->actingAs($vendorUser)
            ->from(route('orders.photos.show', [$order, 0]))
            ->post(route('vendor.orders.accept', $order))
            ->assertRedirect(route('vendor.orders.index'));
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertSee('Dikonfirmasi, menunggu pembayaran')
            ->assertSee('Foto ruangan');
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Dikonfirmasi, menunggu pembayaran')
            ->assertSee('Foto ruangan');

        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/room-fit');
        $this->assertSame('confirmed', $order->fresh()->status);

        $payment = $order->latestPayment()->firstOrFail();
        $notification = [
            'order_id' => $payment->provider_order_id,
            'status_code' => '200',
            'gross_amount' => number_format($order->total, 2, '.', ''),
            'currency' => 'IDR',
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-room-fit-test',
            'fraud_status' => 'accept',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'] . $notification['status_code'] . $notification['gross_amount'] . 'SB-Mid-signature-test-key',
        );
        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_order_requires_room_dimensions_and_at_least_one_photo(): void
    {
        [$customer, , $package] = $this->makeBookingParties();

        $this->actingAs($customer)
            ->post(route('orders.store', $package), [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
                'property_address' => 'Jakarta',
            ])
            ->assertSessionHasErrors(['room_length', 'room_width', 'room_photos']);

        $this->assertDatabaseCount('orders', 0);
    }

    private function makeBookingParties(): array
    {
        $customer = User::create([
            'name' => 'Pemilik Properti',
            'email' => 'room-fit-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'room-fit-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor Contoh',
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

        return [$customer, $vendorUser, $package];
    }

    protected function tearDown(): void
    {
        if ($this->photoPaths !== []) {
            app(OrderRoomPhotoStorage::class)->delete($this->photoPaths);
        }

        parent::tearDown();
    }
}
