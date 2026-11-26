<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_vendor_can_filter_orders_by_status(): void
    {
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'filter-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'filter-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor Filter Test',
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Filter Test',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
        ]);
        $pendingOrder = $this->createOrder($customer, $vendor, $package, 'pending');
        $confirmedOrder = $this->createOrder($customer, $vendor, $package, 'confirmed');

        $this->actingAs($customer)
            ->get(route('orders.index', ['status' => 'confirmed']))
            ->assertOk()
            ->assertSee($confirmedOrder->code)
            ->assertDontSee($pendingOrder->code);

        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pendingOrder->code)
            ->assertDontSee($confirmedOrder->code);
    }

    private function createOrder(User $customer, Vendor $vendor, Package $package, string $status): Order
    {
        return Order::create([
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
            'status' => $status,
        ]);
    }
}
