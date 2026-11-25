<?php

namespace Tests\Feature\Admin;

use App\Enums\ClientSource;
use App\Enums\GalleryStatus;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/search?q=boateng')->assertRedirect('/login');
    }

    public function test_search_requires_at_least_two_characters(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/admin/search?q=a')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_search_returns_grouped_clickable_results(): void
    {
        $client = Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'phone' => '+233 24 111 2200',
            'source' => ClientSource::Booking,
            'subscribed' => true,
        ]);

        $gallery = ClientGallery::create([
            'client_id' => $client->id,
            'title' => 'Boateng Wedding',
            'token' => 'boateng123',
            'expires_at' => now()->addDays(7),
            'status' => GalleryStatus::Active,
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/admin/search?q=boateng')
            ->assertOk()
            ->assertJsonFragment([
                'label' => 'Ama Boateng',
                'sub' => 'ama@example.com',
                'href' => '/admin/clients?q=boateng',
            ])
            ->assertJsonFragment([
                'label' => 'Boateng Wedding',
                'href' => '/admin/galleries/'.$gallery->uuid,
            ]);
    }

    public function test_clients_page_receives_the_search_query(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/clients?q=boateng')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminClients')
                ->where('initialQuery', 'boateng'));
    }
}
