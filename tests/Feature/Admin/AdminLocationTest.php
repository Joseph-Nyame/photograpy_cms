<?php

namespace Tests\Feature\Admin;

use App\Models\BookingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/locations')->assertRedirect('/login');
    }

    public function test_admin_can_view_locations_and_currencies(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/locations')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminLocations')
                ->has('locations', 6)
                ->where('locations.0.name', 'Accra, Ghana')
                ->where('locations.0.currency', 'GHS')
                ->where('locations.0.active', true)
                ->where('locations.3.active', false)
                ->has('currencies', 9)
                ->where('currencies.0.code', 'GHS'));
    }

    public function test_admin_can_add_a_location(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/locations', [
                'name' => 'Dubai, UAE',
                'currency' => 'USD',
                'sort' => 7,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', true)
            ->assertSessionHas('message', 'Location added.');

        $this->assertDatabaseHas('booking_locations', [
            'name' => 'Dubai, UAE',
            'currency' => 'USD',
            'active' => true,
            'sort' => 7,
        ]);
    }

    public function test_location_name_must_be_unique(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/locations', [
                'name' => 'Accra, Ghana',
                'currency' => 'GHS',
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_location_currency_must_be_supported(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/locations', [
                'name' => 'Cairo, Egypt',
                'currency' => 'EGP',
            ])
            ->assertSessionHasErrors(['currency']);
    }

    public function test_admin_can_update_a_location(): void
    {
        $location = BookingLocation::query()->where('name', 'Accra, Ghana')->firstOrFail();

        $this->actingAs($this->admin())
            ->put("/admin/locations/{$location->uuid}", [
                'name' => 'Greater Accra, Ghana',
                'currency' => 'GHS',
                'sort' => 9,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', true)
            ->assertSessionHas('message', 'Location updated.');

        $location->refresh();

        $this->assertSame('Greater Accra, Ghana', $location->name);
        $this->assertSame(9, $location->sort);
    }

    public function test_admin_can_deactivate_and_reactivate_a_location(): void
    {
        $location = BookingLocation::query()->where('name', 'Kumasi, Ghana')->firstOrFail();

        $this->actingAs($this->admin())
            ->post("/admin/locations/{$location->uuid}/toggle")
            ->assertRedirect()
            ->assertSessionHas('message', 'Location deactivated.');

        $this->assertFalse($location->refresh()->active);

        $this->actingAs($this->admin())
            ->post("/admin/locations/{$location->uuid}/toggle")
            ->assertRedirect()
            ->assertSessionHas('message', 'Location activated.');

        $this->assertTrue($location->refresh()->active);
    }
}
