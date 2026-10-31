<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo (password semuanya: password)
        User::create(['name' => 'Admin Isiin', 'email' => 'admin@isiin.test', 'password' => 'password', 'role' => 'admin']);

        $vendorUser = User::create([
            'name' => 'FurniStyle', 'email' => 'vendor@isiin.test', 'phone' => '081234567890',
            'password' => 'password', 'role' => 'vendor',
        ]);

        $vendor = Vendor::create([
            'user_id'       => $vendorUser->id,
            'business_name' => 'FurniStyle',
            'city'          => 'Jakarta Pusat',
            'description'   => 'Menyewakan furniture dan dekorasi untuk home staging, kantor, dan acara.',
            'is_verified'   => true,
        ]);

        User::create(['name' => 'Alex Tan', 'email' => 'customer@isiin.test', 'password' => 'password', 'role' => 'customer']);

        $packages = [
            [
                'name' => 'Modern Minimalist', 'room_type' => 'ruang_tamu', 'style' => 'Modern Minimalist',
                'description' => 'Paket staging bergaya minimalis modern dengan desain bersih dan elegan untuk menarik calon pembeli.',
                'price' => 1500000, 'duration_days' => 7, 'delivery_fee' => 300000, 'installation_fee' => 300000, 'deposit' => 500000,
                'items' => [['Sofa 3 dudukan', 1], ['Meja kopi', 1], ['Karpet', 1], ['Lampu lantai', 1], ['Dekorasi dan tanaman', 1]],
            ],
            [
                'name' => 'Scandinavian', 'room_type' => 'kamar_utama', 'style' => 'Scandinavian',
                'description' => 'Nuansa hangat dan terang khas Skandinavia untuk kamar utama.',
                'price' => 1300000, 'duration_days' => 7, 'delivery_fee' => 250000, 'installation_fee' => 250000, 'deposit' => 400000,
                'items' => [['Tempat tidur queen', 1], ['Nakas', 2], ['Lemari pakaian', 1], ['Lampu tidur', 2], ['Karpet', 1]],
            ],
            [
                'name' => 'Japandi', 'room_type' => 'rumah_penuh', 'style' => 'Japandi',
                'description' => 'Perpaduan gaya Jepang dan Skandinavia untuk satu rumah: ruang tamu, kamar, dan dapur.',
                'price' => 1700000, 'duration_days' => 7, 'delivery_fee' => 400000, 'installation_fee' => 400000, 'deposit' => 700000,
                'items' => [['Sofa', 1], ['Meja makan 4 kursi', 1], ['Tempat tidur', 2], ['Rak TV', 1], ['Dekorasi', 6]],
            ],
        ];

        foreach ($packages as $data) {
            $items = $data['items'];
            unset($data['items']);

            $package = Package::create($data + ['vendor_id' => $vendor->id]);

            foreach ($items as [$name, $qty]) {
                $package->items()->create(['name' => $name, 'quantity' => $qty]);
            }
        }
    }
}
