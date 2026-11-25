<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\ClientSource;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCalendarTest extends TestCase
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
        $this->get('/admin/calendar')->assertRedirect('/login');
    }

    public function test_admin_can_view_the_calendar(): void
    {
        $client = Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'source' => ClientSource::Booking,
        ]);

        Booking::create([
            'client_id' => $client->id,
            'event_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/calendar')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminCalendar')
                ->has('month.cells')
                ->where('month.year', now()->year)
                ->where('month.month', now()->month)
                ->has('bookingsByDay')
                ->has('blockedDates')
                ->where('availability.maxBookings', 2)
                ->where('availability.open', '08:00'));
    }

    public function test_admin_can_block_and_reopen_a_date(): void
    {
        $date = now()->addDays(10)->toDateString();

        $this->actingAs($this->admin())
            ->post('/admin/calendar/block', ['date' => $date, 'reason' => 'Studio closed'])
            ->assertSessionHas('success', true);

        $blocked = BlockedDate::query()->whereDate('date', $date)->first();
        $this->assertNotNull($blocked);

        $this->actingAs($this->admin())
            ->delete("/admin/calendar/blocked/{$blocked->uuid}")
            ->assertSessionHas('success', true);

        $this->assertDatabaseMissing('blocked_dates', ['id' => $blocked->id]);
    }

    public function test_blocking_validates_required_date(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/calendar/block', [])
            ->assertSessionHasErrors('date');
    }

    public function test_admin_can_update_availability(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/calendar/availability', [
                'open' => '09:30',
                'close' => '17:30',
                'maxBookings' => 4,
                'bufferMinutes' => 60,
            ])
            ->assertSessionHas('success', true);

        $booking = app(SettingsService::class)->public()['booking'];

        $this->assertSame('09:30', $booking['open_time']);
        $this->assertSame('17:30', $booking['close_time']);
        $this->assertSame(4, $booking['max_bookings_per_day']);
        $this->assertSame(60, $booking['buffer_minutes']);
    }
}
