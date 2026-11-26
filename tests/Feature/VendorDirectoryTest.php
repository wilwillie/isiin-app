<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_filter_verified_vendors_by_name_city_and_completed_order_rating(): void
    {
        $customer = User::create([
            'name' => 'Pemilik Properti',
            'email' => 'customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $studioJakarta = $this->createVendor('Studio Jakarta', 'studio-jakarta@example.test', 'Jakarta');
        $studioBandung = $this->createVendor('Studio Bandung', 'studio-bandung@example.test', 'Bandung');
        $unverifiedStudio = $this->createVendor('Studio Belum Terverifikasi', 'unverified@example.test', 'Jakarta', false);

        $jakartaPackage = $this->createPackage($studioJakarta, 'Paket Jakarta');
        $this->createReview($studioJakarta, $jakartaPackage, $customer, 'ISN-20261119-DIRA', 'completed', 5);
        $this->createReview($studioJakarta, $jakartaPackage, $customer, 'ISN-20261119-DIRB', 'completed', 5);
        $this->createReview($studioJakarta, $jakartaPackage, $customer, 'ISN-20261119-DIRC', 'pending', 1);
        $this->createReview($studioJakarta, $jakartaPackage, $customer, 'ISN-20261119-DIRF', 'completed', 4);
        $this->createReview($studioJakarta, $jakartaPackage, $customer, 'ISN-20261119-DIRG', 'completed', 5);

        $bandungPackage = $this->createPackage($studioBandung, 'Paket Bandung');
        $this->createReview($studioBandung, $bandungPackage, $customer, 'ISN-20261119-DIRD', 'completed', 5);

        $unverifiedPackage = $this->createPackage($unverifiedStudio, 'Paket Belum Terverifikasi');
        $this->createReview($unverifiedStudio, $unverifiedPackage, $customer, 'ISN-20261119-DIRE', 'completed', 5);
        $this->get(route('vendors.index', ['q' => 'Studio', 'city' => 'Jakarta']))
            ->assertOk()
            ->assertSee('Studio Jakarta');

        $this->get(route('vendors.index', [
            'q' => 'Studio',
            'city' => 'Jakarta',
            'min_rating' => 4.5,
            'sort' => 'rating',
        ]))
            ->assertOk()
            ->assertSee('Studio Jakarta')
            ->assertSee('★ 4.7')
            ->assertSee('4 ulasan pesanan selesai')
            ->assertSee(route('vendors.profile.show', $studioJakarta), false)
            ->assertDontSee('Studio Bandung')
            ->assertDontSee('Studio Belum Terverifikasi');
    }

    private function createVendor(string $name, string $email, string $city, bool $verified = true): Vendor
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => 'vendor',
        ]);

        return Vendor::create([
            'user_id' => $user->id,
            'business_name' => $name,
            'city' => $city,
            'is_verified' => $verified,
        ]);
    }

    private function createPackage(Vendor $vendor, string $name): Package
    {
        return Package::create([
            'vendor_id' => $vendor->id,
            'name' => $name,
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'is_active' => true,
        ]);
    }

    private function createReview(
        Vendor $vendor,
        Package $package,
        User $customer,
        string $code,
        string $status,
        int $rating
    ): void {
        $order = Order::create([
            'code' => $code,
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
            'status' => $status,
        ]);

        Review::create([
            'order_id' => $order->id,
            'package_id' => $package->id,
            'customer_id' => $customer->id,
            'rating' => $rating,
            'comment' => 'Ulasan pesanan.',
        ]);
    }
}
