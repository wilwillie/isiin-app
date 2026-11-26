<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_an_integer_star_rating(): void
    {
        [$customer, $order] = $this->createCompletedOrder();

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('4 ★')
            ->assertDontSee('4,5 ★');

        $this->actingAs($customer)
            ->post(route('reviews.store', $order), ['rating' => '4'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'rating' => 4,
        ]);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('aria-label="4 dari 5 bintang"', false);
    }

    public function test_customer_cannot_submit_a_fractional_rating(): void
    {
        [$customer, $order] = $this->createCompletedOrder();

        $this->actingAs($customer)
            ->from(route('orders.show', $order))
            ->post(route('reviews.store', $order), ['rating' => '4.5'])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    private function createCompletedOrder(): array
    {
        $customer = User::create([
            'name' => 'Customer Ulasan',
            'email' => 'review-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor Ulasan',
            'email' => 'review-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor Ulasan',
            'city' => 'Jakarta',
            'is_verified' => true,
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Ulasan',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'is_active' => true,
        ]);
        $order = Order::create([
            'code' => 'ISN-20261119-RVWA',
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-08',
            'property_address' => 'Alamat demo',
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => 'completed',
        ]);

        return [$customer, $order];
    }
}
