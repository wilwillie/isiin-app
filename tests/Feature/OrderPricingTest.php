<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OrderPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_order_total_contains_rent_delivery_and_installation_only(): void
    {
        $customer = User::create([
            'name' => 'Pemilik Properti',
            'email' => 'pricing-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'pricing-vendor@example.test',
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
            'delivery_fee' => 150000,
            'installation_fee' => 200000,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('orders.store', $package), [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(8)->toDateString(),
                'property_address' => 'Jakarta',
                'room_length' => '5.5',
                'room_width' => '4',
                'room_photos' => [UploadedFile::fake()->createWithContent(
                    'ruangan.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true),
                )],
            ])
            ->assertRedirect();

        $order = Order::query()->sole();
        $this->assertSame(1350000, $order->total);
        $this->assertSame(1000000, $order->rental_fee);
        $this->assertSame(150000, $order->delivery_fee);
        $this->assertSame(200000, $order->installation_fee);
        $this->assertSame(5.5, (float) $order->room_length);
        $this->assertSame(4.0, (float) $order->room_width);
        $this->assertCount(1, $order->room_photos);
        $this->assertArrayNotHasKey('deposit', $order->getAttributes());
    }
}
