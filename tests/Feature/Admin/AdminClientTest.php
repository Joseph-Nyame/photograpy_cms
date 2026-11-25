<?php

namespace Tests\Feature\Admin;

use App\Enums\ClientSource;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminClientTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/clients')->assertRedirect('/login');
    }

    public function test_admin_can_view_the_client_list(): void
    {
        Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'phone' => '+233 24 111 2200',
            'source' => ClientSource::Booking,
            'subscribed' => true,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/clients')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminClients')
                ->has('clients', 1)
                ->where('clients.0.name', 'Ama Boateng')
                ->where('clients.0.source', 'Booking')
                ->where('clients.0.subscribed', true));
    }

    public function test_admin_can_add_a_client(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/clients', [
                'name' => 'Kojo Mensah',
                'email' => 'kojo@example.com',
                'phone' => '+233 20 442 1188',
                'source' => 'manual',
                'subscribed' => true,
            ])
            ->assertSessionHas('success', true);

        $client = Client::query()->where('email', 'kojo@example.com')->first();

        $this->assertNotNull($client);
        $this->assertSame(ClientSource::Manual, $client->source);
        $this->assertTrue($client->subscribed);
    }

    public function test_duplicate_client_email_is_rejected(): void
    {
        Client::create(['name' => 'Ama', 'email' => 'ama@example.com', 'source' => ClientSource::Manual]);

        $this->actingAs($this->admin())
            ->post('/admin/clients', ['name' => 'Ama', 'email' => 'ama@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_toggle_subscription(): void
    {
        $client = Client::create([
            'name' => 'Ama',
            'email' => 'ama@example.com',
            'source' => ClientSource::Booking,
            'subscribed' => true,
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/clients/{$client->uuid}/toggle-subscription")
            ->assertSessionHas('success', true);

        $this->assertFalse($client->refresh()->subscribed);

        $this->actingAs($this->admin())
            ->post("/admin/clients/{$client->uuid}/toggle-subscription");

        $this->assertTrue($client->refresh()->subscribed);
    }
}
