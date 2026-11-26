<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RentalPickupWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $vendorUser;

    private Vendor $vendor;

    private Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-10 12:00:00', 'Asia/Jakarta'));

        $this->customer = User::create([
            'name' => 'Customer',
            'email' => 'pickup-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $this->vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'pickup-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Vendor Contoh',
        ]);
        $this->package = Package::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Paket Tes',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
        ]);
    }

    public function test_customer_can_confirm_an_early_pickup_request_during_the_rental(): void
    {
        $order = $this->createOrder('ISN-20261010-EARL', '2026-10-20');

        $this->actingAs($this->customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Minta Pengambilan Lebih Awal')
            ->assertSee('Konfirmasi permintaan pengambilan furniture lebih awal?', false);

        $this->actingAs($this->customer)
            ->post(route('orders.pickup.request', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertSame('pickup_pending', $order->fresh()->status);
    }

    public function test_expired_rental_moves_to_pickup_pending_automatically(): void
    {
        $expired = $this->createOrder('ISN-20261010-EXPR', '2026-10-09');
        $endingToday = $this->createOrder('ISN-20261010-TDAY', '2026-10-10');
        $future = $this->createOrder('ISN-20261010-FUTR', '2026-10-11');

        $this->artisan('orders:mark-expired-pickups')
            ->expectsOutput('1 pesanan dipindahkan ke tahap menunggu pengambilan.')
            ->assertExitCode(0);

        $this->assertSame('pickup_pending', $expired->fresh()->status);
        $this->assertSame('installed', $endingToday->fresh()->status);
        $this->assertSame('installed', $future->fresh()->status);
    }

    public function test_customer_cannot_request_early_pickup_after_rental_expiry(): void
    {
        $order = $this->createOrder('ISN-20261010-LATE', '2026-10-09');

        $this->actingAs($this->customer)
            ->post(route('orders.pickup.request', $order))
            ->assertRedirect()
            ->assertSessionHas('error', 'Masa sewa sudah berakhir. Tahap pengambilan akan diaktifkan otomatis.');

        $this->assertSame('installed', $order->fresh()->status);
    }

    private function createOrder(string $code, string $endDate): Order
    {
        return Order::create([
            'code' => $code,
            'customer_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'vendor_id' => $this->vendor->id,
            'start_date' => '2026-10-01',
            'end_date' => $endDate,
            'property_address' => 'Jakarta',
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => 'installed',
        ]);
    }
}
