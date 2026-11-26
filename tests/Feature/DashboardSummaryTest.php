<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_page_shows_payment_summary_and_monthly_chart(): void
    {
        $this->travelTo(Carbon::parse('2026-11-25 12:00:00', 'Asia/Jakarta'));
        [$customer, $vendor, $package] = $this->createAccountsAndPackage();

        $paidOrder = $this->createOrder($customer, $vendor, $package, 'paid', 1200000);
        $paidOrder->update(['paid_at' => now()]);
        $completedOrder = $this->createOrder($customer, $vendor, $package, 'completed', 800000);
        $completedOrder->update(['paid_at' => now()->subMonth(), 'completed_at' => now()]);
        $this->createOrder($customer, $vendor, $package, 'confirmed', 900000);

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Total pesanan')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Pesanan selesai')
            ->assertSee('Ringkasan pembayaran')
            ->assertSee('Rp 2.000.000')
            ->assertSee('1,2 jt')
            ->assertSee('0,8 jt');
    }

    public function test_vendor_dashboard_shows_monthly_payout_and_recent_orders(): void
    {
        $this->travelTo(Carbon::parse('2026-11-25 12:00:00', 'Asia/Jakarta'));
        [$customer, $vendor, $package] = $this->createAccountsAndPackage();
        $vendorUser = $vendor->user;

        $completedOrder = $this->createOrder($customer, $vendor, $package, 'completed', 1000000);
        $completedOrder->update([
            'completed_at' => now(),
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
        ]);
        $this->createOrder($customer, $vendor, $package, 'pickup_pending', 1000000);

        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu pengambilan')
            ->assertSee('Pendapatan per bulan')
            ->assertSee('Pesanan terbaru')
            ->assertSee('900.000')
            ->assertSee(route('vendor.orders.index', ['status' => 'completed']), false)
            ->assertSee(route('vendor.orders.index', ['status' => 'pickup_pending']), false);
    }

    /**
     * @return array{User, Vendor, Package}
     */
    private function createAccountsAndPackage(): array
    {
        $customer = User::create([
            'name' => 'Customer Ringkasan',
            'email' => 'dashboard-customer-'.Str::random(6).'@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor Ringkasan',
            'email' => 'dashboard-vendor-'.Str::random(6).'@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor Ringkasan',
        ]);
        $package = Package::create([
            'vendor_id' => $vendor->id,
            'name' => 'Paket Ringkasan',
            'room_type' => 'ruang_tamu',
            'style' => 'Modern Minimalist',
            'price' => 1000000,
            'duration_days' => 7,
        ]);

        return [$customer, $vendor, $package];
    }

    private function createOrder(User $customer, Vendor $vendor, Package $package, string $status, int $total): Order
    {
        return Order::create([
            'code' => 'ISN-20261125-'.strtoupper(Str::random(4)),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => '2026-11-26',
            'end_date' => '2026-12-03',
            'property_address' => 'Jakarta',
            'rental_fee' => $total,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => $total,
            'status' => $status,
        ]);
    }
}
