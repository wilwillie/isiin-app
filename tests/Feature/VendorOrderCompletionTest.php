<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_complete_only_after_pickup_is_pending(): void
    {
        $vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'completion-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'completion-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor Contoh',
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Tes',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
        ]);
        $order = Order::create([
            'code' => 'ISN-20261119-IJKL',
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-08',
            'property_address' => 'Jakarta',
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => 'installed',
        ]);

        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertSee('Pengambilan dapat diminta customer lebih awal')
            ->assertDontSee('Tandai sudah diambil (selesai)');

        $this->actingAs($vendorUser)
            ->post(route('vendor.orders.complete', $order))
            ->assertRedirect();

        $this->assertSame('installed', $order->fresh()->status);
        $this->assertNull($order->fresh()->completed_at);

        $order->update(['status' => 'pickup_pending']);

        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertSee('Konfirmasi furniture sudah benar-benar diambil dari properti?', false);

        $this->actingAs($vendorUser)
            ->post(route('vendor.orders.complete', $order))
            ->assertRedirect(route('vendor.orders.index'));

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
    }
}
