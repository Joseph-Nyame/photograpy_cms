<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\ClientSource;
use App\Enums\GalleryStatus;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_sees_dashboard_with_live_data(): void
    {
        $client = Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'source' => ClientSource::Booking,
        ]);

        Booking::create([
            'client_id' => $client->id,
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'status' => BookingStatus::Pending,
        ]);

        ClientGallery::create([
            'client_id' => $client->id,
            'title' => 'Ama · Wedding',
            'token' => 'galtoken123',
            'expires_at' => now()->addDays(3),
            'status' => GalleryStatus::Active,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminDashboard')
                ->has('stats', 4)
                ->where('stats.0.value', 1)
                ->has('bookings', 1)
                ->where('bookings.0.client', 'Ama Boateng')
                ->has('galleries', 1)
                ->where('galleries.0.status', 'Expiring')
                ->has('attention', 4)
                ->has('month.cells'));
    }

    public function test_month_grid_marks_blocked_dates(): void
    {
        $this->travelTo(now()->startOfMonth()->addDay());
        $blocked = now()->addDays(3);

        BlockedDate::create([
            'date' => $blocked->toDateString(),
            'reason' => 'Studio closed',
        ]);

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($blocked): void {
                $page->component('AdminDashboard');
                $cells = collect($page->toArray()['props']['month']['cells']);
                $target = $cells->firstWhere('day', $blocked->day);
                $this->assertSame('blocked', $target['status']);
            });
    }
}
