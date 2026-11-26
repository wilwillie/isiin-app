<?php

namespace Tests\Feature;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use App\Support\DisputeEvidenceStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisputeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private array $evidencePaths = [];

    public function test_customer_vendor_and_admin_can_review_and_resolve_a_dispute(): void
    {
        [$customer, $vendorUser, $admin, $order] = $this->makeOrder('installed');
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Laporkan masalah pada pesanan');

        $this->actingAs($customer)
            ->post(route('orders.disputes.store', $order), [
                'category' => 'service_quality',
                'description' => 'Furniture tiba dalam kondisi rusak.',
                'evidence_photos' => [UploadedFile::fake()->createWithContent('bukti.png', $image)],
            ])
            ->assertRedirect();

        $dispute = Dispute::query()->sole();
        $this->evidencePaths = $dispute->evidence_photos;
        $this->assertSame('open', $dispute->status);
        $this->assertFileExists(storage_path('app/private/' . $this->evidencePaths[0]));
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Furniture tiba dalam kondisi rusak.');

        $outsider = User::create([
            'name' => 'Pengguna Lain',
            'email' => 'dispute-outsider@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $this->actingAs($outsider)
            ->get(route('disputes.evidence', [$dispute, 0]))
            ->assertForbidden();

        $this->actingAs($vendorUser)
            ->get(route('disputes.evidence', [$dispute, 0]))
            ->assertOk();
        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.index'))
            ->assertOk()
            ->assertSee('Furniture tiba dalam kondisi rusak.')
            ->assertSee('Komplain / Sengketa');
        $this->actingAs($vendorUser)
            ->post(route('disputes.messages.store', $dispute), [
                'body' => 'Kami akan meninjau foto dan menghubungi customer.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.disputes.index'))
            ->assertOk()
            ->assertSee('1 kasus terbuka');
        $this->actingAs($admin)
            ->get(route('admin.disputes.show', $dispute))
            ->assertOk()
            ->assertSee('Catat keputusan')
            ->assertSee('Kami akan meninjau foto dan menghubungi customer.');
        $this->actingAs($admin)
            ->get(route('disputes.evidence', [$dispute, 0]))
            ->assertOk();
        $this->actingAs($vendorUser)
            ->post(route('vendor.orders.complete', $order))
            ->assertSessionHas('error');
        $this->assertSame('installed', $order->fresh()->status);
        $this->actingAs($admin)
            ->post(route('disputes.messages.store', $dispute), [
                'body' => 'Admin meminta kedua pihak menyepakati solusi.',
            ])
            ->assertRedirect();
        $this->actingAs($admin)
            ->post(route('admin.disputes.resolve', $dispute), [
                'resolution_outcome' => 'mutual_agreement',
                'resolution_note' => 'Vendor mengganti furniture yang rusak.',
            ])
            ->assertRedirect(route('admin.disputes.show', $dispute));

        $this->assertSame('resolved', $dispute->fresh()->status);
        $this->assertDatabaseCount('dispute_messages', 2);

        $this->actingAs($vendorUser)
            ->post(route('vendor.orders.complete', $order))
            ->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
    }

    public function test_open_dispute_blocks_payment_and_cannot_be_submitted_twice(): void
    {
        [$customer, $vendorUser, , $order] = $this->makeOrder('confirmed');

        $this->actingAs($vendorUser)
            ->post(route('vendor.orders.disputes.store', $order), [
                'category' => 'payment',
                'description' => 'Saya perlu klarifikasi rincian biaya.',
            ])
            ->assertRedirect();

        $dispute = Dispute::query()->sole();
        $this->actingAs($customer)
            ->post(route('orders.pay', $order))
            ->assertSessionHas('error');
        $this->assertSame('confirmed', $order->fresh()->status);

        $this->actingAs($vendorUser)
            ->post(route('vendor.orders.disputes.store', $order), [
                'category' => 'other',
                'description' => 'Laporan duplikat.',
            ])
            ->assertSessionHas('error');
        $this->assertSame(1, $order->disputes()->count());
        $this->assertSame('open', $dispute->fresh()->status);
    }

    public function test_service_quality_report_is_not_allowed_before_installation(): void
    {
        [$customer, , , $order] = $this->makeOrder('confirmed');

        $this->actingAs($customer)
            ->post(route('orders.disputes.store', $order), [
                'category' => 'service_quality',
                'description' => 'Pemasangan belum dilakukan.',
            ])
            ->assertSessionHasErrors('category');

        $this->assertDatabaseCount('disputes', 0);
    }

    public function test_customer_can_open_a_new_report_after_previous_case_is_resolved(): void
    {
        [$customer, , $admin, $order] = $this->makeOrder('completed');
        $firstDispute = $order->disputes()->create([
            'opened_by' => $customer->id,
            'category' => 'furniture_damage',
            'description' => 'Ada bagian furniture yang rusak.',
            'status' => 'open',
        ]);
        $firstDispute->created_at = Carbon::parse('2026-11-20 16:35:00', 'UTC');
        $firstDispute->save();

        $this->actingAs($admin)
            ->post(route('admin.disputes.resolve', $firstDispute), [
                'resolution_outcome' => 'mutual_agreement',
                'resolution_note' => 'Masalah pertama sudah diselesaikan.',
            ])
            ->assertRedirect();

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Komplain setelah pengambilan dapat dilaporkan kapan saja.')
            ->assertSee('Buat laporan baru');

        $this->actingAs($customer)
            ->post(route('orders.disputes.store', $order), [
                'category' => 'furniture_damage',
                'description' => 'Saat pengambilan, satu bagian kecil furniture ternyata tertinggal.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, $order->disputes()->count());
        $this->assertSame(1, $order->disputes()->where('status', 'open')->count());
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Masalah pertama sudah diselesaikan.')
            ->assertSee('20 Nov 2026 23:35')
            ->assertSee('Saat pengambilan, satu bagian kecil furniture ternyata tertinggal.');
    }

    public function test_admin_sees_order_and_payment_status_when_reviewing_a_case(): void
    {
        [$customer, , $admin, $order] = $this->makeOrder('confirmed');
        $dispute = $order->disputes()->create([
            'opened_by' => $customer->id,
            'category' => 'payment',
            'description' => 'Mohon konfirmasi rincian pembayaran.',
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.disputes.show', $dispute))
            ->assertOk()
            ->assertSee('Dikonfirmasi, menunggu pembayaran')
            ->assertSee('Belum tercatat dibayar');
    }

    private function makeOrder(string $status): array
    {
        $customer = User::create([
            'name' => 'Pemilik Properti',
            'email' => 'dispute-customer@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $vendorUser = User::create([
            'name' => 'Vendor',
            'email' => 'dispute-vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'dispute-admin@example.test',
            'password' => 'password',
            'role' => 'admin',
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
            'code' => 'ISN-20261120-' . strtoupper(substr($status, 0, 4)),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'vendor_id' => $vendor->id,
            'start_date' => now()->subDays(8)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'property_address' => 'Jakarta',
            'rental_fee' => 1000000,
            'delivery_fee' => 0,
            'installation_fee' => 0,
            'total' => 1000000,
            'status' => $status,
        ]);

        return [$customer, $vendorUser, $admin, $order];
    }

    protected function tearDown(): void
    {
        if ($this->evidencePaths !== []) {
            app(DisputeEvidenceStorage::class)->delete($this->evidencePaths);
        }

        parent::tearDown();
    }
}
