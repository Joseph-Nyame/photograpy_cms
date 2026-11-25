<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ClientSource;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Client;
use App\Services\BookingAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_open_dates_skip_blocked_and_full_days(): void
    {
        $blocked = now()->addDay()->toDateString();
        BlockedDate::create(['date' => $blocked]);

        $full = now()->addDays(2)->toDateString();
        $client = Client::create([
            'name' => 'Existing Client',
            'email' => 'existing@example.com',
            'source' => ClientSource::Manual,
        ]);

        Booking::create([
            'client_id' => $client->id,
            'event_date' => $full,
            'status' => BookingStatus::Pending,
        ]);
        Booking::create([
            'client_id' => $client->id,
            'event_date' => $full,
            'status' => BookingStatus::Confirmed,
        ]);

        $dates = app(BookingAvailabilityService::class)->openDates(limit: 5);
        $keys = array_column($dates, 'date');

        $this->assertNotEmpty($dates);
        $this->assertNotContains($blocked, $keys);
        $this->assertNotContains($full, $keys);
        $this->assertSame(now()->addDays(3)->toDateString(), $keys[0]);
    }

    public function test_public_calendar_marks_past_and_booked_dates_as_unselectable(): void
    {
        $booked = now()->addDays(2)->toDateString();
        $client = Client::create([
            'name' => 'Existing Client',
            'email' => 'existing@example.com',
            'source' => ClientSource::Manual,
        ]);

        Booking::create([
            'client_id' => $client->id,
            'event_date' => $booked,
            'status' => BookingStatus::Confirmed,
        ]);
        Booking::create([
            'client_id' => $client->id,
            'event_date' => $booked,
            'status' => BookingStatus::Pending,
        ]);

        $calendar = app(BookingAvailabilityService::class)->publicMonth();
        $cells = collect($calendar['cells'])->keyBy('date');

        $this->assertSame('booked', $cells->get($booked)['status']);
        $this->assertFalse($cells->get($booked)['selectable']);
        $this->assertTrue($cells->get(now()->addDay()->toDateString())['selectable']);

        $pastMonth = now()->subMonth();
        $pastCalendar = app(BookingAvailabilityService::class)->publicMonth($pastMonth->year, $pastMonth->month);
        $pastCells = collect($pastCalendar['cells'])->keyBy('date');
        $pastDate = $pastMonth->day(15)->toDateString();

        $this->assertSame('unavailable', $pastCells->get($pastDate)['status']);
        $this->assertFalse($pastCells->get($pastDate)['selectable']);
    }
}
