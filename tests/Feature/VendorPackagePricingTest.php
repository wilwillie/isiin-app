<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PortfolioProject;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorPackagePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_form_shows_vendor_income_after_commission(): void
    {
        $user = User::create([
            'name' => 'Vendor',
            'email' => 'package-pricing-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor Contoh',
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
        ]);

        $this->actingAs($user)
            ->get(route('vendor.packages.edit', $package))
            ->assertOk()
            ->assertSee('Tarif sewa (Rp)')
            ->assertSee('Durasi sewa per tarif (hari)')
            ->assertDontSee('Panjang 1 periode')
            ->assertSee('Estimasi pendapatan vendor untuk 1 tarif sewa')
            ->assertSee('Komisi Isiin (10% dari total biaya layanan)')
            ->assertSee('Rp 135.000')
            ->assertSee('Rp 1.215.000');
    }

    public function test_replacing_a_package_image_keeps_a_file_still_used_by_a_portfolio_project(): void
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Vendor',
            'email' => 'shared-image-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor Contoh',
        ]);
        $sharedImagePath = 'packages/shared-demo.jpg';
        Storage::disk('public')->put($sharedImagePath, 'shared image');
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket dengan foto bersama',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
            'image_path' => $sharedImagePath,
        ]);
        PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'title' => 'Proyek dengan foto bersama',
            'room_type' => 'ruang_tamu',
            'location' => 'Jakarta',
            'completion_year' => 2025,
            'description' => 'Foto yang sama dipakai paket dan proyek.',
            'before_image_path' => $sharedImagePath,
        ]);
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);
        $this->assertNotFalse($image);

        $this->actingAs($user)
            ->put(route('vendor.packages.update', $package), [
                'name' => 'Paket dengan foto baru',
                'room_type' => 'ruang_tamu',
                'style' => 'Modern Minimalist',
                'price' => 1000000,
                'duration_days' => 7,
                'delivery_fee' => 0,
                'installation_fee' => 0,
                'minimum_room_length' => '4.5',
                'minimum_room_width' => '3.25',
                'is_active' => '1',
                'image' => UploadedFile::fake()->createWithContent('replacement.png', $image),
            ])
            ->assertRedirect(route('vendor.packages.index'));

        Storage::disk('public')->assertExists($sharedImagePath);
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'minimum_room_length' => 4.5,
            'minimum_room_width' => 3.25,
        ]);
        $this->assertDatabaseHas('portfolio_projects', [
            'vendor_id' => $vendor->id,
            'before_image_path' => $sharedImagePath,
        ]);
    }
}
