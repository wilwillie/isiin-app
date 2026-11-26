<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Package;
use App\Models\PortfolioProject;
use App\Models\ProfileChangeRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = env('DEMO_SEED_PASSWORD');
        if (app()->environment('production') && (! is_string($demoPassword) || strlen($demoPassword) < 16)) {
            throw new RuntimeException('Set DEMO_SEED_PASSWORD to at least 16 characters before seeding production.');
        }
        $demoPassword = is_string($demoPassword) && $demoPassword !== '' ? $demoPassword : 'password';

        $admin = $this->seedUser([
            'name' => 'Admin Isiin',
            'email' => 'admin@isiin.test',
            'password' => $demoPassword,
            'role' => 'admin',
        ]);

        $vendorUsers = [
            'furnistyle' => $this->seedUser([
                'name' => 'FurniStyle',
                'email' => 'vendor@isiin.test',
                'phone' => '081234567890',
                'password' => $demoPassword,
                'role' => 'vendor',
            ]),
            'ruangrapi' => $this->seedUser([
                'name' => 'Nadia Putri',
                'email' => 'vendor2@isiin.test',
                'phone' => '081298765432',
                'password' => $demoPassword,
                'role' => 'vendor',
            ]),
            'belumverifikasi' => $this->seedUser([
                'name' => 'Dimas Pratama',
                'email' => 'vendor.pending@isiin.test',
                'phone' => '081377788899',
                'password' => $demoPassword,
                'role' => 'vendor',
            ]),
        ];

        $vendors = [
            'furnistyle' => $this->seedVendor($vendorUsers['furnistyle'], [
                'business_name' => 'FurniStyle',
                'city' => 'Jakarta Pusat',
                'description' => 'Menyewakan furniture dan dekorasi untuk home staging dengan pilihan gaya yang beragam.',
                'is_verified' => true,
            ]),
            'ruangrapi' => $this->seedVendor($vendorUsers['ruangrapi'], [
                'business_name' => 'Ruang Rapi Studio',
                'city' => 'Bandung',
                'description' => 'Studio home staging untuk apartemen dan hunian keluarga. Fokus pada ruang yang nyaman dan fungsional.',
                'is_verified' => true,
            ]),
            'belumverifikasi' => $this->seedVendor($vendorUsers['belumverifikasi'], [
                'business_name' => 'Dekorasi Kita',
                'city' => 'Depok',
                'description' => 'Vendor demo yang belum diverifikasi untuk mencoba alur verifikasi admin.',
                'is_verified' => false,
            ]),
        ];

        $customers = [
            'alex' => $this->seedUser([
                'name' => 'Alex Tan',
                'email' => 'customer@isiin.test',
                'phone' => '081200001111',
                'password' => $demoPassword,
                'role' => 'customer',
            ]),
            'maya' => $this->seedUser([
                'name' => 'Maya Putri',
                'email' => 'maya@isiin.test',
                'phone' => '081200002222',
                'password' => $demoPassword,
                'role' => 'customer',
            ]),
            'budi' => $this->seedUser([
                'name' => 'Budi Santoso',
                'email' => 'budi@isiin.test',
                'phone' => '081200003333',
                'password' => $demoPassword,
                'role' => 'customer',
            ]),
        ];

        $packages = [
            'furnistyle' => $this->seedPackages($vendors['furnistyle'], [
                'modern' => [
                    'name' => 'Modern Minimalist',
                    'room_type' => 'ruang_tamu',
                    'style' => 'Modern Minimalist',
                    'description' => 'Paket staging bergaya minimalis modern dengan desain bersih dan elegan untuk menarik calon pembeli.',
                    'price' => 1500000,
                    'duration_days' => 7,
                    'delivery_fee' => 300000,
                    'installation_fee' => 300000,
                    'image_path' => 'packages/seed-japandi-home.jpg',
                    'items' => [['Sofa 3 dudukan', 1], ['Meja kopi', 1], ['Karpet', 1], ['Lampu lantai', 1], ['Dekorasi dan tanaman', 1]],
                ],
                'scandinavian_bedroom' => [
                    'name' => 'Scandinavian',
                    'room_type' => 'kamar_utama',
                    'style' => 'Scandinavian',
                    'description' => 'Nuansa hangat dan terang khas Skandinavia untuk kamar utama.',
                    'price' => 1300000,
                    'duration_days' => 14,
                    'delivery_fee' => 250000,
                    'installation_fee' => 250000,
                    'image_path' => 'packages/seed-scandinavian-bedroom.jpg',
                    'items' => [['Tempat tidur queen', 1], ['Nakas', 2], ['Lemari pakaian', 1], ['Lampu tidur', 2], ['Karpet', 1]],
                ],
                'japandi_home' => [
                    'name' => 'Japandi',
                    'room_type' => 'rumah_penuh',
                    'style' => 'Japandi',
                    'description' => 'Perpaduan gaya Jepang dan Skandinavia untuk satu rumah: ruang tamu, kamar, dan dapur.',
                    'price' => 1700000,
                    'duration_days' => 30,
                    'delivery_fee' => 400000,
                    'installation_fee' => 400000,
                    'image_path' => 'packages/seed-japandi-home.jpg',
                    'items' => [['Sofa', 1], ['Meja makan 4 kursi', 1], ['Tempat tidur', 2], ['Rak TV', 1], ['Dekorasi', 6]],
                ],
                'industrial' => [
                    'name' => 'Industrial Loft',
                    'room_type' => 'ruang_tamu',
                    'style' => 'Industrial',
                    'description' => 'Sentuhan industrial dengan material berkarakter untuk membuat ruang tamu tampil tegas dan modern.',
                    'price' => 1100000,
                    'duration_days' => 3,
                    'delivery_fee' => 250000,
                    'installation_fee' => 200000,
                    'image_path' => 'packages/seed-industrial-residence.jpg',
                    'items' => [['Sofa kulit 2 dudukan', 1], ['Meja kopi metal', 1], ['Rak besi', 1], ['Lampu gantung', 2], ['Dekorasi dinding', 2]],
                ],
                'family_room' => [
                    'name' => 'Scandinavian Family Room',
                    'room_type' => 'ruang_tamu',
                    'style' => 'Scandinavian',
                    'description' => 'Ruang berkumpul keluarga yang terasa terang dan nyaman dengan furnitur fungsional.',
                    'price' => 1450000,
                    'duration_days' => 10,
                    'delivery_fee' => 300000,
                    'installation_fee' => 250000,
                    'image_path' => 'packages/seed-japandi-compact.jpg',
                    'items' => [['Sofa sudut', 1], ['Meja kopi kayu', 1], ['Karpet', 1], ['Rak buku', 1], ['Tanaman dekoratif', 3]],
                ],
                'compact_home' => [
                    'name' => 'Japandi Compact Home',
                    'room_type' => 'rumah_penuh',
                    'style' => 'Japandi',
                    'description' => 'Paket Japandi untuk hunian kompak dengan pilihan furnitur sederhana dan hemat ruang.',
                    'price' => 1900000,
                    'duration_days' => 21,
                    'delivery_fee' => 400000,
                    'installation_fee' => 350000,
                    'image_path' => 'packages/seed-japandi-compact.jpg',
                    'items' => [['Sofa 2 dudukan', 1], ['Meja makan lipat', 1], ['Tempat tidur queen', 1], ['Meja samping', 2], ['Rak serbaguna', 1]],
                ],
                'industrial_home' => [
                    'name' => 'Industrial Full Residence',
                    'room_type' => 'rumah_penuh',
                    'style' => 'Industrial',
                    'description' => 'Staging rumah penuh dengan aksen industrial yang konsisten dari ruang keluarga hingga kamar.',
                    'price' => 2500000,
                    'duration_days' => 30,
                    'delivery_fee' => 500000,
                    'installation_fee' => 500000,
                    'image_path' => 'packages/seed-industrial-residence.jpg',
                    'items' => [['Sofa 3 dudukan', 1], ['Meja makan 4 kursi', 1], ['Tempat tidur queen', 2], ['Rak besi', 2], ['Lampu industrial', 4]],
                ],
            ]),
            'ruangrapi' => $this->seedPackages($vendors['ruangrapi'], [
                'scandinavian_living' => [
                    'name' => 'Ruang Keluarga Terang',
                    'room_type' => 'ruang_tamu',
                    'style' => 'Scandinavian',
                    'description' => 'Furniture berwarna terang untuk menciptakan ruang keluarga yang hangat dan lapang.',
                    'price' => 1250000,
                    'duration_days' => 7,
                    'delivery_fee' => 200000,
                    'installation_fee' => 250000,
                    'image_path' => 'packages/seed-japandi-compact.jpg',
                    'items' => [['Sofa 2 dudukan', 1], ['Meja kopi', 1], ['Karpet', 1], ['Lampu lantai', 1]],
                ],
                'japandi_apartment' => [
                    'name' => 'Japandi Apartment',
                    'room_type' => 'rumah_penuh',
                    'style' => 'Japandi',
                    'description' => 'Paket ringkas untuk menata beberapa area apartemen dengan palet natural.',
                    'price' => 1650000,
                    'duration_days' => 14,
                    'delivery_fee' => 275000,
                    'installation_fee' => 275000,
                    'image_path' => 'packages/seed-japandi-compact.jpg',
                    'items' => [['Sofa kompak', 1], ['Meja makan 2 kursi', 1], ['Tempat tidur queen', 1], ['Rak serbaguna', 1]],
                ],
                'industrial_home' => [
                    'name' => 'Industrial Family Home',
                    'room_type' => 'rumah_penuh',
                    'style' => 'Industrial',
                    'description' => 'Paket rumah penuh dengan perpaduan furnitur kayu dan aksen metal.',
                    'price' => 2350000,
                    'duration_days' => 30,
                    'delivery_fee' => 450000,
                    'installation_fee' => 400000,
                    'image_path' => 'packages/seed-industrial-residence.jpg',
                    'items' => [['Sofa 3 dudukan', 1], ['Meja makan', 1], ['Tempat tidur queen', 2], ['Rak metal', 2]],
                ],
                'inactive_preview' => [
                    'name' => 'Paket Preview Nonaktif',
                    'room_type' => 'kamar_utama',
                    'style' => 'Modern Minimalist',
                    'description' => 'Paket nonaktif untuk mencoba tampilan pengelolaan paket vendor.',
                    'price' => 900000,
                    'duration_days' => 5,
                    'delivery_fee' => 150000,
                    'installation_fee' => 150000,
                    'image_path' => 'packages/seed-scandinavian-bedroom.jpg',
                    'is_active' => false,
                    'items' => [['Tempat tidur', 1], ['Nakas', 2]],
                ],
            ]),
            'belumverifikasi' => $this->seedPackages($vendors['belumverifikasi'], [
                'pending_package' => [
                    'name' => 'Paket Menunggu Verifikasi',
                    'room_type' => 'ruang_tamu',
                    'style' => 'Modern Minimalist',
                    'description' => 'Paket contoh untuk mencoba bahwa paket vendor belum terverifikasi tidak tampil ke customer.',
                    'price' => 1000000,
                    'duration_days' => 7,
                    'delivery_fee' => 200000,
                    'installation_fee' => 200000,
                    'image_path' => 'packages/seed-japandi-home.jpg',
                    'items' => [['Sofa', 1], ['Meja kopi', 1]],
                ],
            ]),
        ];

        $orders = [
            'alex_completed_modern' => $this->seedOrder(
                'ISN-DEMO-0001',
                $customers['alex'],
                $packages['furnistyle']['modern'],
                'completed',
                now()->subDays(45),
                now()->subDays(38),
                'Alamat demo customer, Jakarta'
            ),
            'alex_completed_scandinavian' => $this->seedOrder(
                'ISN-DEMO-0002',
                $customers['alex'],
                $packages['furnistyle']['scandinavian_bedroom'],
                'completed',
                now()->subDays(60),
                now()->subDays(46),
                'Alamat demo customer, Jakarta'
            ),
            'alex_pending' => $this->seedOrder(
                'ISN-DEMO-0003',
                $customers['alex'],
                $packages['furnistyle']['industrial'],
                'pending',
                now()->addDays(5),
                now()->addDays(8),
                'Alamat demo customer, Jakarta',
                'Menunggu konfirmasi vendor.'
            ),
            'alex_cancelled' => $this->seedOrder(
                'ISN-DEMO-0004',
                $customers['alex'],
                $packages['ruangrapi']['japandi_apartment'],
                'cancelled',
                now()->addDays(18),
                now()->addDays(32),
                'Alamat demo customer, Bandung',
                'Pesanan dibatalkan customer.'
            ),
            'maya_confirmed' => $this->seedOrder(
                'ISN-DEMO-0005',
                $customers['maya'],
                $packages['ruangrapi']['scandinavian_living'],
                'confirmed',
                now()->addDays(8),
                now()->addDays(15),
                'Alamat demo customer, Bandung',
                'Menunggu pembayaran customer.'
            ),
            'maya_paid' => $this->seedOrder(
                'ISN-DEMO-0006',
                $customers['maya'],
                $packages['ruangrapi']['japandi_apartment'],
                'paid',
                now()->addDays(1),
                now()->addDays(15),
                'Alamat demo customer, Bandung',
                'Pembayaran simulasi sudah dilakukan.'
            ),
            'maya_installed' => $this->seedOrder(
                'ISN-DEMO-0007',
                $customers['maya'],
                $packages['furnistyle']['family_room'],
                'installed',
                now()->subDay(),
                now()->addDays(9),
                'Alamat demo customer, Jakarta',
                'Furniture sudah terpasang, masa sewa berjalan.'
            ),
            'maya_completed_japandi' => $this->seedOrder(
                'ISN-DEMO-0008',
                $customers['maya'],
                $packages['ruangrapi']['japandi_apartment'],
                'completed',
                now()->subDays(40),
                now()->subDays(26),
                'Alamat demo customer, Bandung'
            ),
            'budi_rejected' => $this->seedOrder(
                'ISN-DEMO-0009',
                $customers['budi'],
                $packages['furnistyle']['industrial_home'],
                'rejected',
                now()->addDays(10),
                now()->addDays(40),
                'Alamat demo customer, Jakarta',
                'Contoh pesanan yang ditolak vendor.'
            ),
            'budi_completed' => $this->seedOrder(
                'ISN-DEMO-0010',
                $customers['budi'],
                $packages['ruangrapi']['industrial_home'],
                'completed',
                now()->subDays(48),
                now()->subDays(18),
                'Alamat demo customer, Bandung'
            ),
            'alex_completed_unreviewed' => $this->seedOrder(
                'ISN-DEMO-0011',
                $customers['alex'],
                $packages['furnistyle']['industrial'],
                'completed',
                now()->subDays(12),
                now()->subDays(9),
                'Alamat demo customer, Jakarta',
                'Pesanan selesai yang masih bisa diberi ulasan.'
            ),
        ];

        $reviews = [
            'alex_completed_modern' => [5, 'Pelayanan cepat dan hasil penataan ruangnya rapi.'],
            'alex_completed_scandinavian' => [4, 'Furniture sesuai foto dan ruang terasa lebih nyaman.'],
            'maya_completed_japandi' => [5, 'Koordinasi vendor baik dan hasilnya sesuai kebutuhan.'],
            'budi_completed' => [4, 'Paketnya bagus, pemasangan berjalan lancar.'],
        ];

        foreach ($reviews as $key => [$rating, $comment]) {
            $order = $orders[$key];
            Review::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'package_id' => $order->package_id,
                    'customer_id' => $order->customer_id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]
            );
        }

        $this->seedDispute(
            $orders['maya_installed'],
            $customers['maya'],
            $admin,
            'furniture_damage',
            'Permukaan meja kopi terlihat tergores saat furniture selesai dipasang.',
            'open',
            now()->subHours(8),
            [
                [$vendorUsers['ruangrapi'], 'Mohon kirim foto bagian meja yang tergores agar kami bisa meninjau dan menawarkan solusi.', now()->subHours(6)],
                [$customers['maya'], 'Saya sudah mengirimkan detailnya. Mohon dibantu sebelum masa sewa berakhir.', now()->subHours(3)],
            ]
        );
        $this->seedDispute(
            $orders['budi_completed'],
            $vendorUsers['ruangrapi'],
            $admin,
            'schedule',
            'Pemilik properti meminta perubahan jadwal pengambilan furniture di luar jadwal awal.',
            'resolved',
            now()->subDays(20),
            [
                [$customers['budi'], 'Saya meminta perubahan jadwal karena akses properti belum tersedia.', now()->subDays(19)],
                [$vendorUsers['ruangrapi'], 'Jadwal baru sudah disepakati tanpa biaya tambahan.', now()->subDays(19)->addHours(3)],
            ],
            'mutual_agreement',
            'Kedua pihak sepakat tidak ada biaya tambahan dan pesanan dapat ditutup.'
        );

        $this->seedPortfolioProject(
            $vendors['furnistyle'],
            'Demo foto sesudah: Ruang Tamu',
            'ruang_tamu',
            'Jakarta Pusat',
            now()->year,
            'Foto sesudah contoh untuk mencoba tampilan proyek dengan satu gambar. Bukan dokumentasi properti customer sebenarnya.',
            null,
            'packages/seed-japandi-home.jpg'
        );
        $this->seedPortfolioProject(
            $vendors['ruangrapi'],
            'Demo before-after: Rumah Japandi',
            'rumah_penuh',
            'Bandung',
            now()->year,
            'Foto contoh seed untuk mencoba galeri before-after. Ganti dengan dokumentasi pesanan setelah mendapat izin customer.',
            'packages/seed-scandinavian-bedroom.jpg',
            'packages/seed-japandi-home.jpg'
        );

        PortfolioProject::query()
            ->where('vendor_id', $vendors['furnistyle']->id)
            ->where('title', 'Apartemen Senopati')
            ->where('image_path', 'packages/seed-scandinavian-family.jpg')
            ->where('description', 'Contoh proyek portofolio terstruktur untuk menampilkan pengalaman vendor.')
            ->delete();

        ProfileChangeRequest::updateOrCreate(
            ['user_id' => $vendorUsers['ruangrapi']->id, 'type' => 'profile', 'status' => 'pending'],
            ['payload' => ['changes' => ['city' => 'Cimahi', 'business_name' => 'Ruang Rapi Studio Bandung']]]
        );
        ProfileChangeRequest::updateOrCreate(
            ['user_id' => $vendorUsers['furnistyle']->id, 'type' => 'profile', 'status' => 'rejected'],
            [
                'payload' => ['changes' => ['city' => 'Bandung']],
                'admin_note' => 'Data lokasi belum sesuai dokumen usaha.',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDays(2),
            ]
        );
    }

    private function seedUser(array $attributes): User
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $attributes['email']],
            $attributes
        );

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }

    private function seedVendor(User $user, array $attributes): Vendor
    {
        $vendor = Vendor::withTrashed()->updateOrCreate(
            ['user_id' => $user->id],
            $attributes
        );

        if ($vendor->trashed()) {
            $vendor->restore();
        }

        return $vendor;
    }

    private function seedPackages(Vendor $vendor, array $definitions): array
    {
        $packages = [];

        foreach ($definitions as $key => $data) {
            $items = $data['items'];
            unset($data['items']);

            $package = Package::updateOrCreate(
                ['vendor_id' => $vendor->id, 'name' => $data['name']],
                $data + ['is_active' => true]
            );

            foreach ($items as [$name, $quantity]) {
                $package->items()->updateOrCreate(
                    ['name' => $name],
                    ['quantity' => $quantity]
                );
            }

            $packages[$key] = $package;
        }

        return $packages;
    }

    private function seedOrder(
        string $code,
        User $customer,
        Package $package,
        string $status,
        Carbon $startDate,
        Carbon $endDate,
        string $propertyAddress,
        ?string $notes = null
    ): Order {
        $days = max(1, (int) $startDate->diffInDays($endDate));
        $periods = (int) ceil($days / max(1, $package->duration_days));
        $rentalFee = $package->price * $periods;
        $paidAt = in_array($status, ['paid', 'installed', 'completed'], true)
            ? now()->subDay()
            : null;

        return Order::updateOrCreate(
            ['code' => $code],
            [
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'vendor_id' => $package->vendor_id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'property_address' => $propertyAddress,
                'notes' => $notes,
                'room_length' => $package->room_type === 'rumah_penuh' ? 8.5 : 5.2,
                'room_width' => $package->room_type === 'rumah_penuh' ? 6.0 : 4.0,
                'ceiling_height' => 2.8,
                'room_photos' => [],
                'rental_fee' => $rentalFee,
                'delivery_fee' => $package->delivery_fee,
                'installation_fee' => $package->installation_fee,
                'total' => $rentalFee + $package->delivery_fee + $package->installation_fee,
                'status' => $status,
                'paid_at' => $paidAt,
                'completed_at' => $status === 'completed' ? $endDate->copy()->setTime(15, 0) : null,
            ]
        );
    }

    private function seedDispute(
        Order $order,
        User $openedBy,
        User $admin,
        string $category,
        string $description,
        string $status,
        Carbon $createdAt,
        array $messages,
        ?string $resolutionOutcome = null,
        ?string $resolutionNote = null
    ): void {
        $resolvedAt = $status === 'resolved' ? $createdAt->copy()->addDay() : null;
        $dispute = Dispute::updateOrCreate(
            ['order_id' => $order->id],
            [
                'opened_by' => $openedBy->id,
                'category' => $category,
                'description' => $description,
                'evidence_photos' => [],
                'status' => $status,
                'resolution_outcome' => $resolutionOutcome,
                'resolution_note' => $resolutionNote,
                'resolved_by' => $resolvedAt ? $admin->id : null,
                'resolved_at' => $resolvedAt,
                'created_at' => $createdAt,
                'updated_at' => $resolvedAt ?? $createdAt,
            ]
        );

        foreach ($messages as [$sender, $body, $sentAt]) {
            DisputeMessage::updateOrCreate(
                [
                    'dispute_id' => $dispute->id,
                    'sender_id' => $sender->id,
                    'body' => $body,
                ],
                [
                    'evidence_photos' => [],
                    'created_at' => $sentAt,
                    'updated_at' => $sentAt,
                ]
            );
        }
    }

    private function seedPortfolioProject(
        Vendor $vendor,
        string $title,
        string $roomType,
        string $location,
        int $completionYear,
        string $description,
        ?string $beforeImage,
        string $afterImage
    ): void {
        PortfolioProject::updateOrCreate(
            ['vendor_id' => $vendor->id, 'title' => $title],
            [
                'room_type' => $roomType,
                'location' => $location,
                'completion_year' => $completionYear,
                'description' => $description,
                'image_path' => null,
                'before_image_path' => $beforeImage,
                'after_image_path' => $afterImage,
            ]
        );
    }
}
