<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Package;
use App\Models\PortfolioProject;
use App\Models\ProfileChangeRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_both_sides_and_can_be_run_repeatedly(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            ['cancelled', 'completed', 'confirmed', 'installed', 'paid', 'pending', 'rejected'],
            Order::query()->distinct()->orderBy('status')->pluck('status')->all()
        );
        $this->assertSame(11, Order::count());
        $this->assertSame(4, Review::count());
        $this->assertSame(12, Package::count());
        $this->assertSame(3, Vendor::count());
        $this->assertSame(2, PortfolioProject::count());
        $this->assertSame(2, ProfileChangeRequest::count());
        $this->assertSame(2, Dispute::count());
        $this->assertSame(4, DisputeMessage::count());
        $this->assertSame(['open', 'resolved'], Dispute::query()->orderBy('status')->pluck('status')->all());
        $this->assertSame(5.2, (float) Order::where('code', 'ISN-DEMO-0007')->value('room_length'));
        $this->assertNotNull(Order::where('code', 'ISN-DEMO-0001')->value('completed_at'));

        foreach (Package::query()->whereNotNull('image_path')->pluck('image_path') as $path) {
            $this->assertTrue(Storage::disk('public')->exists($path), "Missing seeded package image: {$path}");
        }

        foreach (PortfolioProject::query()->get() as $project) {
            foreach ([$project->image_path, $project->before_image_path, $project->after_image_path] as $path) {
                if ($path) {
                    $this->assertTrue(Storage::disk('public')->exists($path), "Missing seeded portfolio image: {$path}");
                }
            }
        }

        $counts = [
            User::count(),
            Vendor::count(),
            Package::count(),
            Order::count(),
            Review::count(),
            PortfolioProject::count(),
            ProfileChangeRequest::count(),
            Dispute::count(),
            DisputeMessage::count(),
        ];

        PortfolioProject::create([
            'vendor_id' => Vendor::whereHas('user', fn ($query) => $query->where('email', 'vendor@isiin.test'))->value('id'),
            'title' => 'Apartemen Senopati',
            'room_type' => 'ruang_tamu',
            'location' => 'Jakarta Selatan',
            'completion_year' => now()->year - 1,
            'description' => 'Contoh proyek portofolio terstruktur untuk menampilkan pengalaman vendor.',
            'image_path' => 'packages/seed-scandinavian-family.jpg',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, [
            User::count(),
            Vendor::count(),
            Package::count(),
            Order::count(),
            Review::count(),
            PortfolioProject::count(),
            ProfileChangeRequest::count(),
            Dispute::count(),
            DisputeMessage::count(),
        ]);
        $this->assertDatabaseMissing('portfolio_projects', [
            'title' => 'Apartemen Senopati',
            'image_path' => 'packages/seed-scandinavian-family.jpg',
        ]);
        $this->assertDatabaseHas('orders', [
            'code' => 'ISN-DEMO-0003',
            'customer_id' => User::where('email', 'customer@isiin.test')->value('id'),
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('portfolio_projects', [
            'vendor_id' => Vendor::whereHas('user', fn ($query) => $query->where('email', 'vendor@isiin.test'))->value('id'),
            'title' => 'Demo foto sesudah: Ruang Tamu',
            'before_image_path' => null,
            'after_image_path' => 'packages/seed-japandi-home.jpg',
        ]);
        $this->assertDatabaseHas('portfolio_projects', [
            'vendor_id' => Vendor::whereHas('user', fn ($query) => $query->where('email', 'vendor2@isiin.test'))->value('id'),
            'title' => 'Demo before-after: Rumah Japandi',
            'before_image_path' => 'packages/seed-scandinavian-bedroom.jpg',
            'after_image_path' => 'packages/seed-japandi-home.jpg',
        ]);
        $this->assertDatabaseHas('reviews', [
            'order_id' => Order::where('code', 'ISN-DEMO-0002')->value('id'),
            'rating' => 4,
        ]);
    }
}
