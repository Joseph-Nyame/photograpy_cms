<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_an_admin_can_sign_out_and_the_session_is_cleared(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', true)
            ->assertSessionHas('status', 'You have been signed out.')
            ->assertSessionHas('message', 'You have been signed out.');

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/login');
    }
}
