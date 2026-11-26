<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppPrototypeTest extends TestCase
{
    public function test_homepage_store_badges_open_the_clickable_prototype(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('prototype.app'), false)
            ->assertSee('Google Play')
            ->assertSee('App Store')
            ->assertSee('prototype interaktif');
    }

    public function test_demo_customer_can_follow_the_booking_and_payment_flow(): void
    {
        $this->get(route('prototype.app', ['screen' => 'catalog', 'q' => 'Japandi']))
            ->assertOk()
            ->assertSee('Japandi Living')
            ->assertDontSee('Kamar Hangat');

        $this->get(route('prototype.app', ['screen' => 'detail', 'package' => 'japandi']))
            ->assertOk()
            ->assertSee('Isi paket')
            ->assertSee(e(route('prototype.app', ['screen' => 'booking', 'package' => 'japandi'])), false);

        $this->get(route('prototype.app', ['screen' => 'booking', 'package' => 'japandi']))
            ->assertOk()
            ->assertSee('Lanjut ke pembayaran');

        $this->get(route('prototype.app', ['screen' => 'payment', 'package' => 'japandi']))
            ->assertOk()
            ->assertSee('Simulasikan pembayaran berhasil')
            ->assertSee('Tidak ada transaksi atau pembayaran sungguhan');

        $this->get(route('prototype.app', ['screen' => 'success', 'package' => 'japandi']))
            ->assertOk()
            ->assertSee('ISN-DEMO-001')
            ->assertSee(e(route('prototype.app', ['screen' => 'orders', 'package' => 'japandi'])), false);

        $this->get(route('prototype.app', ['screen' => 'orders', 'package' => 'japandi']))
            ->assertOk()
            ->assertSee('Menunggu persetujuan');
    }
}
