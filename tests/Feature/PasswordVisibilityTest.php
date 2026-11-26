<?php

namespace Tests\Feature;

use Tests\TestCase;

class PasswordVisibilityTest extends TestCase
{
    public function test_login_password_field_has_an_accessible_visibility_toggle(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('aria-controls="password"', false)
            ->assertSee('aria-label="Tampilkan password"', false);
    }

    public function test_registration_password_fields_have_independent_visibility_toggles(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false)
            ->assertSee('Sembunyikan password');
    }
}
