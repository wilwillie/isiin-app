<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommissionDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_completed_commissions_daily_trend_and_transaction_history(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'commission-admin@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
        [$customer, $vendor, $package] = $this->makeTransactionParties();

        $this->createOrder($customer, $vendor, $package, 'ISN-20261120-COM1', 'completed', now()->subDays(2), 1000000);
        $this->createOrder($customer, $vendor, $package, 'ISN-20261011-COM2', 'completed', now()->subDays(40), 500000);
        $this->createOrder($customer, $vendor, $package, 'ISN-20261120-COM3', 'paid', null, 300000);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Komisi')
            ->assertSee('Tren Komisi Harian')
            ->assertSee('<svg', false)
            ->assertSee('Rp 150.000')
            ->assertSee('Rp 100.000')
            ->assertSee('ISN-20261120-COM1')
            ->assertSee('ISN-20261011-COM2')
            ->assertDontSee('ISN-20261120-COM3')
            ->assertSee('data-commission="100000"', false);
    }

    public function test_only_admins_can_open_the_commission_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'commission-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    private function makeTransactionParties(): array
    {
        $customer = User::create([
            'name' => 'Pemilik Properti',
            'email' => 'commission-customer-data@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'commission-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
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

        return [$customer, $vendor, $package];
    }

    private function createOrder(
        User $customer,
        Vendor $vendor,
        Package $package,
        string $code,
        string $status,
        $completedAt,
        int $rentalFee,
    ): Order {
        return Order::create([
            'code' => $code,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subMonth()->addDays(7)->toDateString(),
            'property_address' => 'Jakarta',
            'rental_fee' => $rentalFee,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => $rentalFee,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);
    }
}
