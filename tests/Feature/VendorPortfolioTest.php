<?php

namespace Tests\Feature;

use App\Models\PortfolioProject;
use App\Models\Order;
use App\Models\Package;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_add_a_structured_portfolio_project(): void
    {
        [$user, $vendor] = $this->createVendor();

        $response = $this->actingAs($user)->post(route('vendor.portfolio.projects.store'), [
            'project' => [
                'title' => 'Staging Rumah Menteng',
                'room_type' => 'rumah_penuh',
                'location' => 'Jakarta Pusat',
                'completion_year' => now()->year,
                'description' => 'Penataan ruang keluarga dan kamar utama.',
            ],
        ]);

        $response->assertRedirect(route('vendor.portfolio.manage'));
        $this->assertDatabaseHas('portfolio_projects', [
            'vendor_id' => $vendor->id,
            'title' => 'Staging Rumah Menteng',
            'room_type' => 'rumah_penuh',
            'location' => 'Jakarta Pusat',
        ]);
    }

    public function test_customer_can_view_projects_from_a_verified_vendor(): void
    {
        [, $vendor] = $this->createVendor();
        PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'title' => 'Apartemen Senopati',
            'room_type' => 'ruang_tamu',
            'location' => 'Jakarta Selatan',
            'completion_year' => 2025,
            'description' => 'Staging ruang tamu bergaya minimalis.',
        ]);
        $customer = User::create([
            'name' => 'Pemilik Properti',
            'email' => 'customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('vendors.portfolio.show', $vendor))
            ->assertOk()
            ->assertSee('Apartemen Senopati')
            ->assertSee('Jakarta Selatan');
    }

    public function test_customer_cannot_view_an_unverified_vendor_portfolio(): void
    {
        [, $vendor] = $this->createVendor(false);

        $this->get(route('vendors.portfolio.show', $vendor))->assertNotFound();
    }

    public function test_customer_can_see_completed_order_owner_names_without_property_addresses(): void
    {
        [, $vendor] = $this->createVendor();
        $customer = User::create([
            'name' => 'Nama Rahasia Pelanggan',
            'email' => 'private-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Ruang Tamu',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'is_active' => true,
        ]);
        Order::create([
            'code' => 'ISN-20261119-ABCD',
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-08',
            'property_address' => 'Alamat Rahasia, Jakarta',
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => 'completed',
        ]);

        $this->get(route('vendors.portfolio.show', $vendor))
            ->assertOk()
            ->assertSee('Pesanan selesai')
            ->assertSee('Paket Ruang Tamu')
            ->assertSee('Nama Rahasia Pelanggan')
            ->assertDontSee('Alamat Rahasia, Jakarta');
    }

    public function test_vendor_can_add_a_portfolio_project_with_before_and_after_photos(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->createVendor();

        $this->actingAs($user)
            ->get(route('vendor.portfolio.manage'))
            ->assertOk()
            ->assertSee('project[before_image]', false)
            ->assertSee('project[after_image]', false)
            ->assertDontSee('Hubungkan dengan pesanan selesai');

        $this->actingAs($user)
            ->post(route('vendor.portfolio.projects.store'), [
                'project' => [
                    'title' => 'Penyegaran Apartemen',
                    'room_type' => 'ruang_tamu',
                    'location' => 'Jakarta Selatan',
                    'completion_year' => now()->year,
                    'description' => 'Ruang tampil lebih terang dan nyaman.',
                    'before_image' => $this->fakeImage('before.png'),
                    'after_image' => $this->fakeImage('after.png'),
                ],
            ])
            ->assertRedirect(route('vendor.portfolio.manage'));

        $project = PortfolioProject::where('title', 'Penyegaran Apartemen')->firstOrFail();
        $this->assertSame($vendor->id, $project->vendor_id);
        $this->assertSame('ruang_tamu', $project->room_type);
        Storage::disk('public')->assertExists($project->before_image_path);
        Storage::disk('public')->assertExists($project->after_image_path);

        $this->get(route('vendors.profile.show', $vendor))
            ->assertOk()
            ->assertSee('Penyegaran Apartemen')
            ->assertSee('Sebelum')
            ->assertSee('Sesudah')
            ->assertSee('Ruang tampil lebih terang dan nyaman.');
    }

    public function test_vendor_can_add_a_portfolio_project_with_only_an_after_photo(): void
    {
        Storage::fake('public');
        [$user] = $this->createVendor();

        $this->actingAs($user)
            ->post(route('vendor.portfolio.projects.store'), [
                'project' => [
                    'title' => 'Proyek Foto Sesudah',
                    'room_type' => 'ruang_tamu',
                    'location' => 'Jakarta',
                    'completion_year' => now()->year,
                    'description' => 'Hasil staging setelah ditata.',
                    'after_image' => $this->fakeImage('after-only.png'),
                ],
            ])
            ->assertRedirect(route('vendor.portfolio.manage'));

        $project = PortfolioProject::where('title', 'Proyek Foto Sesudah')->firstOrFail();
        $this->assertNull($project->before_image_path);
        Storage::disk('public')->assertExists($project->after_image_path);

        $this->get(route('vendors.profile.show', $project->vendor))
            ->assertOk()
            ->assertSee('Sesudah: Proyek Foto Sesudah', false)
            ->assertDontSee('Sebelum: Proyek Foto Sesudah', false);
    }

    public function test_customer_can_see_reviews_for_completed_orders_from_this_vendor_only(): void
    {
        [, $vendor] = $this->createVendor();
        [, $otherVendor] = $this->createVendor(true, 'other-vendor@example.test');
        $customer = User::create([
            'name' => 'Pelanggan Pemberi Ulasan',
            'email' => 'reviewer@example.test',
            'password' => 'password',
            'role' => 'customer',
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
        $completedOrder = $this->createOrder($vendor, $package, $customer, 'ISN-20261119-REVA', 'completed');
        Review::create([
            'order_id' => $completedOrder->id,
            'package_id' => $package->id,
            'customer_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Pelayanan sangat memuaskan.',
        ]);
        $anotherCompletedOrder = $this->createOrder($vendor, $package, $customer, 'ISN-20261119-REVD', 'completed');
        Review::create([
            'order_id' => $anotherCompletedOrder->id,
            'package_id' => $package->id,
            'customer_id' => $customer->id,
            'rating' => 4,
            'comment' => 'Ulasan pesanan selesai lainnya.',
        ]);
        foreach ([
            ['ISN-20261119-REVE', 5, 'Ulasan pesanan selesai ketiga.'],
            ['ISN-20261119-REVF', 5, 'Ulasan pesanan selesai keempat.'],
        ] as [$code, $rating, $comment]) {
            $order = $this->createOrder($vendor, $package, $customer, $code, 'completed');
            Review::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'customer_id' => $customer->id,
                'rating' => $rating,
                'comment' => $comment,
            ]);
        }
        $pendingOrder = $this->createOrder($vendor, $package, $customer, 'ISN-20261119-REVB', 'pending');
        Review::create([
            'order_id' => $pendingOrder->id,
            'package_id' => $package->id,
            'customer_id' => $customer->id,
            'rating' => 1,
            'comment' => 'Ulasan pesanan belum selesai.',
        ]);
        $otherVendorPackage = Package::create([
            'vendor_id' => $otherVendor->id,
            'name' => 'Paket Vendor Lain',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'is_active' => true,
        ]);
        $otherVendorOrder = $this->createOrder($otherVendor, $otherVendorPackage, $customer, 'ISN-20261119-REVC', 'completed');
        Review::create([
            'order_id' => $otherVendorOrder->id,
            'package_id' => $otherVendorPackage->id,
            'customer_id' => $customer->id,
            'rating' => 2,
            'comment' => 'Ulasan vendor lain.',
        ]);

        $this->get(route('vendors.portfolio.show', $vendor))
            ->assertOk()
            ->assertSee('Ulasan vendor')
            ->assertSee('Pelayanan sangat memuaskan.')
            ->assertSee('Pelanggan Pemberi Ulasan')
            ->assertSee('★ 4.7')
            ->assertSee('4 ulasan')
            ->assertSee('Ulasan pesanan selesai lainnya.')
            ->assertSee('Ulasan pesanan selesai ketiga.')
            ->assertSee('Ulasan pesanan selesai keempat.')
            ->assertDontSee('Ulasan pesanan belum selesai.')
            ->assertDontSee('Ulasan vendor lain.');
    }

    public function test_vendor_can_update_and_delete_their_portfolio_project(): void
    {
        [$user, $vendor] = $this->createVendor();
        $project = PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'title' => 'Proyek Awal',
            'room_type' => 'ruang_tamu',
            'location' => 'Bandung',
            'completion_year' => 2024,
            'description' => 'Deskripsi lama.',
        ]);

        $this->actingAs($user)
            ->put(route('vendor.portfolio.projects.update', $project), [
                'projects' => [
                    $project->id => [
                        'title' => 'Proyek Diperbarui',
                        'room_type' => 'rumah_penuh',
                        'location' => 'Jakarta',
                        'completion_year' => 2025,
                        'description' => 'Deskripsi baru.',
                    ],
                ],
            ])
            ->assertRedirect(route('vendor.portfolio.manage'));

        $this->assertDatabaseHas('portfolio_projects', [
            'id' => $project->id,
            'title' => 'Proyek Diperbarui',
            'room_type' => 'rumah_penuh',
            'location' => 'Jakarta',
        ]);

        $this->delete(route('vendor.portfolio.projects.destroy', $project))
            ->assertRedirect(route('vendor.portfolio.manage'));
        $this->assertDatabaseMissing('portfolio_projects', ['id' => $project->id]);
    }

    public function test_vendor_can_remove_before_and_after_photos_individually_when_editing(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->createVendor();
        $beforeImagePath = 'portfolio/before.jpg';
        $afterImagePath = 'portfolio/after.jpg';
        Storage::disk('public')->put($beforeImagePath, 'before image');
        Storage::disk('public')->put($afterImagePath, 'after image');
        $project = PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'title' => 'Proyek Before After',
            'room_type' => 'ruang_tamu',
            'location' => 'Bandung',
            'completion_year' => 2025,
            'description' => 'Proyek dengan dua foto.',
            'before_image_path' => $beforeImagePath,
            'after_image_path' => $afterImagePath,
        ]);
        $this->actingAs($user)
            ->get(route('vendor.portfolio.manage'))
            ->assertOk()
            ->assertSee('remove_before_image', false)
            ->assertSee('remove_after_image', false);

        $this->actingAs($user)
            ->put(route('vendor.portfolio.projects.update', $project), [
                'projects' => [
                    $project->id => [
                        'title' => $project->title,
                        'room_type' => $project->room_type,
                        'location' => $project->location,
                        'completion_year' => $project->completion_year,
                        'description' => $project->description,
                        'remove_before_image' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('vendor.portfolio.manage'));

        $project->refresh();
        $this->assertNull($project->before_image_path);
        $this->assertSame($afterImagePath, $project->after_image_path);
        Storage::disk('public')->assertMissing($beforeImagePath);
        Storage::disk('public')->assertExists($afterImagePath);

        $this->put(route('vendor.portfolio.projects.update', $project), [
            'projects' => [
                $project->id => [
                    'title' => $project->title,
                    'room_type' => $project->room_type,
                    'location' => $project->location,
                    'completion_year' => $project->completion_year,
                    'description' => $project->description,
                    'remove_after_image' => '1',
                ],
            ],
        ])->assertRedirect(route('vendor.portfolio.manage'));

        $project->refresh();
        $this->assertNull($project->after_image_path);
        Storage::disk('public')->assertMissing($afterImagePath);

        $this->get(route('vendors.profile.show', $vendor))
            ->assertOk()
            ->assertDontSee('Sebelum: Proyek Before After', false)
            ->assertDontSee('Sesudah: Proyek Before After', false);
    }

    public function test_deleting_a_project_keeps_an_image_still_used_by_a_package(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->createVendor();
        $imagePath = 'packages/shared-demo.jpg';
        Storage::disk('public')->put($imagePath, 'shared image');
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket dengan foto bersama',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'image_path' => $imagePath,
            'is_active' => true,
        ]);
        $project = PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'title' => 'Proyek dengan foto bersama',
            'room_type' => 'ruang_tamu',
            'location' => 'Bandung',
            'completion_year' => 2025,
            'description' => 'Foto yang sama dipakai paket dan proyek.',
            'after_image_path' => $imagePath,
        ]);

        $this->actingAs($user)
            ->delete(route('vendor.portfolio.projects.destroy', $project))
            ->assertRedirect(route('vendor.portfolio.manage'));

        Storage::disk('public')->assertExists($imagePath);
        $this->assertDatabaseHas('packages', ['id' => $package->id, 'image_path' => $imagePath]);
    }

    public function test_vendor_cannot_edit_another_vendors_portfolio_project(): void
    {
        [$owner] = $this->createVendor(true, 'owner@example.test');
        [, $otherVendor] = $this->createVendor(true, 'other@example.test');
        $project = PortfolioProject::create([
            'vendor_id' => $otherVendor->id,
            'title' => 'Proyek Pribadi',
            'room_type' => 'ruang_tamu',
            'location' => 'Bandung',
            'completion_year' => 2025,
            'description' => 'Deskripsi proyek.',
        ]);

        $this->actingAs($owner)
            ->put(route('vendor.portfolio.projects.update', $project), [
                'projects' => [
                    $project->id => [
                        'title' => 'Diubah',
                        'room_type' => 'ruang_tamu',
                        'location' => 'Jakarta',
                        'completion_year' => 2025,
                        'description' => 'Tidak boleh diubah.',
                    ],
                ],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('portfolio_projects', [
            'id' => $project->id,
            'title' => 'Proyek Pribadi',
        ]);
    }

    public function test_vendor_order_list_shows_delivery_and_installation_in_the_payout_breakdown(): void
    {
        [$vendorUser, $vendor] = $this->createVendor();
        $customer = User::create([
            'name' => 'Customer Pesanan',
            'email' => 'order-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Biaya Lengkap',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'is_active' => true,
        ]);
        Order::create([
            'code' => 'ISN-20261119-EFGH',
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-08',
            'property_address' => 'Jakarta',
            'rental_fee' => 1000000,
            'delivery_fee' => 150000,
            'installation_fee' => 200000,
            'total' => 1350000,
            'status' => 'pending',
        ]);

        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertSee('Biaya pengiriman')
            ->assertSee('Rp 150.000')
            ->assertSee('Biaya pemasangan')
            ->assertSee('Rp 200.000')
            ->assertSee('Komisi Isiin (10% dari total biaya layanan)')
            ->assertSee('Rp 135.000')
            ->assertSee('Rp 1.215.000')
            ->assertDontSee('Deposit');
    }

    private function createVendor(bool $verified = true, string $email = 'vendor@example.test'): array
    {
        $user = User::create([
            'name' => 'Vendor Portofolio',
            'email' => $email,
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor Contoh',
            'city' => 'Jakarta',
            'description' => 'Jasa staging properti.',
            'is_verified' => $verified,
        ]);

        return [$user, $vendor];
    }

    private function fakeImage(string $filename): UploadedFile
    {
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($filename, $image);
    }

    private function createOrder(
        Vendor $vendor,
        Package $package,
        User $customer,
        string $code,
        string $status,
        string $address = 'Jakarta'
    ): Order
    {
        return Order::create([
            'code' => $code,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-08',
            'property_address' => $address,
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => $status,
        ]);
    }
}
