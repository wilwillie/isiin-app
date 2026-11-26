<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_login_always_redirects_to_the_vendor_dashboard(): void
    {
        $user = User::create([
            'name' => 'Vendor Isiin',
            'email' => 'vendor@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor Isiin',
            'is_verified' => false,
        ]);

        $this->withSession(['url.intended' => route('packages.index')])
            ->post(route('login'), [
                'email' => 'vendor@example.test',
                'password' => 'password',
            ])
            ->assertRedirect(route('vendor.dashboard'))
            ->assertSessionMissing('url.intended');
    }

    public function test_vendor_navigation_separates_business_profile_from_account_and_hides_vendor_search(): void
    {
        $user = User::create([
            'name' => 'Vendor Isiin',
            'email' => 'vendor-menu@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor Isiin',
            'is_verified' => false,
        ]);

        $this->actingAs($user)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Profil Bisnis')
            ->assertSee('Akun Saya')
            ->assertDontSee('Cari Vendor');
    }

    public function test_guest_homepage_ctas_open_registration_with_the_selected_role(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jadi Vendor')
            ->assertSee('Daftar sebagai Vendor')
            ->assertSee('Mulai sebagai Customer')
            ->assertSee(route('register', ['role' => 'vendor']), false)
            ->assertSee(route('register', ['role' => 'customer']), false);
    }

    public function test_vendor_homepage_ctas_lead_to_dashboard_or_package_catalog(): void
    {
        $user = User::create([
            'name' => 'Vendor Isiin',
            'email' => 'vendor-home@example.test',
            'password' => 'password',
            'role' => 'vendor',
        ]);
        Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor Isiin',
            'is_verified' => false,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard Vendor')
            ->assertSee(route('vendor.dashboard'), false)
            ->assertSee(route('packages.index'), false)
            ->assertDontSee(route('register', ['role' => 'vendor']), false)
            ->assertDontSee('Mulai sebagai Customer');
    }

    public function test_customer_homepage_does_not_show_vendor_registration_ctas(): void
    {
        $customer = User::create([
            'name' => 'Customer Isiin',
            'email' => 'customer-home@example.test',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Jelajahi Paket')
            ->assertSee(route('packages.index'), false)
            ->assertDontSee('Daftar sebagai Vendor')
            ->assertDontSee('Jadi Vendor');
    }

    public function test_unknown_page_uses_the_custom_not_found_view(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }
}
