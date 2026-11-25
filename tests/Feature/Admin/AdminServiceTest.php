<?php

namespace Tests\Feature\Admin;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/services')->assertRedirect('/login');
    }

    public function test_admin_can_view_services(): void
    {
        Service::create([
            'name' => 'Portrait session',
            'slug' => 'portrait-session',
            'description' => "One hour, one location.\n60-minute shoot",
            'price_from' => 800,
            'duration' => '1 hour',
            'active' => true,
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/services')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminServices')
                ->has('services', 1)
                ->where('services.0.name', 'Portrait session')
                ->where('services.0.desc', 'One hour, one location.')
                ->where('services.0.active', true));
    }

    public function test_admin_can_create_a_service(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/services', [
                'name' => 'Football matchday',
                'price' => 1800,
                'currency' => 'USD',
                'duration' => '3 hours',
                'sort' => 4,
                'desc' => 'Match action and touchline reactions.',
            ])
            ->assertSessionHas('success', true);

        $service = Service::query()->where('name', 'Football matchday')->first();

        $this->assertNotNull($service);
        $this->assertSame('football-matchday', $service->slug);
        $this->assertTrue($service->active);
        $this->assertSame('1800.00', $service->price_from);
        $this->assertSame('USD', $service->currency);
    }

    public function test_admin_can_create_a_service_with_pricing_packages(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/services', [
                'name' => 'Wedding film',
                'price' => 2400,
                'packages' => [
                    [
                        'name' => 'Essential',
                        'items' => [
                            ['label' => '2 hours', 'value' => '1200'],
                            ['label' => '20 edited images', 'value' => null],
                        ],
                    ],
                    [
                        'name' => 'Signature',
                        'items' => [
                            ['label' => 'Full day', 'value' => '2400'],
                        ],
                    ],
                ],
            ])
            ->assertSessionHas('success', true);

        $service = Service::query()->where('name', 'Wedding film')->firstOrFail();

        $this->assertSame(2, $service->packages()->count());
        $this->assertDatabaseHas('service_packages', [
            'service_id' => $service->id,
            'name' => 'Essential',
            'sort' => 0,
        ]);
        $this->assertDatabaseHas('service_package_items', [
            'label' => '2 hours',
            'value' => '1200.00',
        ]);
        $this->assertSame(2, $service->packages()->where('name', 'Essential')->firstOrFail()->items()->count());
    }

    public function test_admin_can_update_a_service_and_keeps_includes(): void
    {
        $service = Service::create([
            'name' => 'Portrait session',
            'slug' => 'portrait-session',
            'description' => "One hour, one location.\n60-minute shoot\n20 edited images",
            'price_from' => 800,
            'duration' => '1 hour',
            'active' => true,
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->put("/admin/services/{$service->uuid}", [
                'name' => 'Portrait session',
                'price' => 900,
                'duration' => '90 minutes',
                'sort' => 1,
                'desc' => 'Ninety minutes, one location.',
            ])
            ->assertSessionHas('success', true);

        $service->refresh();

        $this->assertSame('900.00', $service->price_from);
        $this->assertStringContainsString('Ninety minutes, one location.', $service->description);
        $this->assertStringContainsString('60-minute shoot', $service->description);
        $this->assertStringContainsString('20 edited images', $service->description);
    }

    public function test_updating_a_service_replaces_pricing_packages(): void
    {
        $service = Service::create([
            'name' => 'Portrait session',
            'slug' => 'portrait-session',
            'price_from' => 800,
            'active' => true,
            'sort' => 1,
        ]);

        $service->packages()->create(['name' => 'Old package']);

        $this->actingAs($this->admin())
            ->put("/admin/services/{$service->uuid}", [
                'name' => 'Portrait session',
                'price' => 900,
                'packages' => [
                    ['name' => 'New package', 'items' => [['label' => '90 minutes', 'value' => '900']]],
                ],
            ])
            ->assertSessionHas('success', true);

        $this->assertSame(1, $service->packages()->count());
        $this->assertSame('New package', $service->packages()->firstOrFail()->name);
        $this->assertSame(0, $service->packages()->where('name', 'Old package')->count());
    }

    public function test_guest_cannot_duplicate_a_service(): void
    {
        $service = Service::create([
            'name' => 'Portrait session',
            'slug' => 'portrait-session',
            'price_from' => 800,
            'active' => true,
            'sort' => 1,
        ]);

        $this->post("/admin/services/{$service->uuid}/duplicate")
            ->assertRedirect('/login');

        $this->assertSame(1, Service::count());
    }

    public function test_admin_can_duplicate_a_service_with_its_packages(): void
    {
        $service = Service::create([
            'name' => 'Portrait session',
            'slug' => 'portrait-session',
            'description' => "One hour, one location.\n60-minute shoot",
            'price_from' => 800,
            'currency' => 'USD',
            'duration' => '1 hour',
            'active' => true,
            'sort' => 2,
        ]);

        $package = $service->packages()->create(['name' => 'Essential', 'sort' => 0]);
        $package->items()->create(['label' => '2 hours', 'value' => 800, 'sort' => 0]);
        $package->items()->create(['label' => '20 edited images', 'sort' => 1]);

        $this->actingAs($this->admin())
            ->post("/admin/services/{$service->uuid}/duplicate")
            ->assertSessionHas('success', true)
            ->assertSessionHas('message', 'Service duplicated as a paused copy. Edit it below.');

        $copy = Service::query()->where('name', 'Portrait session (copy)')->firstOrFail();

        $this->assertSame('portrait-session-copy', $copy->slug);
        $this->assertSame("One hour, one location.\n60-minute shoot", $copy->description);
        $this->assertSame('800.00', $copy->price_from);
        $this->assertSame('USD', $copy->currency);
        $this->assertSame('1 hour', $copy->duration);
        $this->assertFalse($copy->active);
        $this->assertSame(3, $copy->sort);

        $this->assertSame(1, $copy->packages()->count());
        $packageCopy = $copy->packages()->firstOrFail();
        $this->assertSame('Essential', $packageCopy->name);
        $this->assertSame(0, $packageCopy->sort);
        $this->assertSame(['2 hours', '20 edited images'], $packageCopy->items->pluck('label')->all());
        $this->assertSame('800.00', $packageCopy->items->first()->value);
        $this->assertNull($packageCopy->items->last()->value);

        $this->assertTrue($service->refresh()->active);
        $this->assertSame(1, $service->packages()->count());

        $this->assertSame($copy->uuid, session('data.uuid'));
        $this->assertSame("One hour, one location.\n60-minute shoot", session('data.description'));
        $this->assertSame('Essential', session('data.packages.0.name'));
    }

    public function test_duplicating_a_copy_gives_it_a_unique_slug(): void
    {
        $service = Service::create([
            'name' => 'Wedding film',
            'slug' => 'wedding-film',
            'price_from' => 2400,
            'active' => true,
            'sort' => 1,
        ]);

        $admin = $this->actingAs($this->admin());
        $admin->post("/admin/services/{$service->uuid}/duplicate")->assertSessionHas('success', true);

        $firstCopy = Service::query()->where('name', 'Wedding film (copy)')->firstOrFail();
        $admin->post("/admin/services/{$service->uuid}/duplicate")->assertSessionHas('success', true);

        $this->assertSame('wedding-film-copy', $firstCopy->slug);
        $this->assertSame(2, Service::query()->where('name', 'Wedding film (copy)')->count());
        $this->assertSame(
            1,
            Service::query()->where('slug', 'wedding-film-copy-2')->count(),
        );
    }

    public function test_admin_can_toggle_a_service(): void
    {
        $service = Service::create([
            'name' => 'Event coverage',
            'slug' => 'event-coverage',
            'price_from' => 2500,
            'active' => true,
            'sort' => 2,
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/services/{$service->uuid}/toggle")
            ->assertSessionHas('success', true);

        $this->assertFalse($service->refresh()->active);
    }

    public function test_service_requires_name_and_price(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/services', [])
            ->assertSessionHasErrors(['name', 'price']);
    }
}
