<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\ClientSource;
use App\Models\Booking;
use App\Models\BookingLocation;
use App\Models\Client;
use App\Models\Service;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_guest_can_request_a_booking(): void
    {
        $date = now()->addDays(10)->toDateString();

        $this->post('/booking', [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'phone' => '+233 20 000 0000',
            'service' => 'Portrait session',
            'date' => $date,
            'location' => 'Accra, Ghana',
            'message' => 'Golden hour please.',
        ])->assertRedirect()->assertSessionHas('success', true);

        $client = Client::query()->where('email', 'ama@example.com')->first();

        $this->assertNotNull($client);
        $this->assertSame(ClientSource::Booking, $client->source);

        $booking = Booking::query()->first();

        $this->assertNotNull($booking);
        $this->assertSame($client->id, $booking->client_id);
        $this->assertSame($date, $booking->event_date->toDateString());
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame('GHS', $booking->currency);
        $this->assertSame('800.00', $booking->total_cost);
        $this->assertSame('240.00', $booking->deposit_amount);
        $this->assertSame(
            Service::query()->where('name', 'Portrait session')->value('id'),
            $booking->service_id,
        );
    }

    public function test_guest_can_submit_a_custom_request(): void
    {
        $date = now()->addDays(12)->toDateString();
        BookingLocation::query()->where('name', 'New York, United States')->update(['active' => true]);

        $this->post('/booking', [
            'name' => 'Kwame Osei',
            'email' => 'kwame@example.com',
            'booking_type' => 'custom',
            'service' => 'Something else / custom request',
            'custom_details' => 'A two-hour documentary film with drone coverage.',
            'budget' => '2500',
            'date' => $date,
            'location' => 'New York, United States',
        ])->assertRedirect()->assertSessionHas('success', true);

        $booking = Booking::query()->first();

        $this->assertNotNull($booking);
        $this->assertSame(BookingType::Custom, $booking->booking_type);
        $this->assertNull($booking->service_id);
        $this->assertSame('USD', $booking->currency);
        $this->assertSame('2500.00', $booking->budget);
        $this->assertSame('2500.00', $booking->total_cost);
        $this->assertSame('750.00', $booking->deposit_amount);
        $this->assertStringContainsString('two-hour documentary film', $booking->message);
        $this->assertDatabaseHas('booking_activities', [
            'booking_id' => $booking->id,
            'action' => 'created',
        ]);
    }

    public function test_deposit_uses_the_configured_settings_rate(): void
    {
        Setting::updateOrCreate(['key' => 'booking.deposit_rate'], ['value' => '10']);
        app(SettingsService::class)->forget();

        $this->post('/booking', [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'service' => 'Portrait session',
            'date' => now()->addDays(10)->toDateString(),
            'location' => 'Accra, Ghana',
        ])->assertRedirect()->assertSessionHas('success', true);

        $booking = Booking::query()->firstOrFail();

        $this->assertSame('800.00', $booking->total_cost);
        $this->assertSame('80.00', $booking->deposit_amount);
    }

    public function test_booking_requires_name_email_and_future_date(): void
    {
        $this->post('/booking', [
            'name' => '',
            'email' => 'not-an-email',
            'date' => now()->subDay()->toDateString(),
        ])->assertSessionHasErrors(['name', 'email', 'date']);

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_custom_request_requires_details(): void
    {
        $this->post('/booking', [
            'name' => 'Kwame Osei',
            'email' => 'kwame@example.com',
            'booking_type' => 'custom',
            'date' => now()->addDays(8)->toDateString(),
        ])->assertSessionHasErrors(['custom_details']);
    }

    public function test_repeat_booking_reuses_the_client_record(): void
    {
        $payload = [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'date' => now()->addDays(5)->toDateString(),
            'location' => 'Accra, Ghana',
        ];

        $this->post('/booking', $payload)->assertSessionHas('success', true);
        $this->post('/booking', $payload)->assertSessionHas('success', true);

        $this->assertSame(1, Client::query()->where('email', 'ama@example.com')->count());
        $this->assertSame(2, Booking::query()->count());
    }

    public function test_booking_requires_a_configured_location(): void
    {
        $this->post('/booking', [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'date' => now()->addDays(5)->toDateString(),
            'location' => 'East Legon',
        ])->assertSessionHasErrors(['location']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_public_cannot_book_at_an_inactive_location(): void
    {
        $this->post('/booking', [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'date' => now()->addDays(5)->toDateString(),
            'location' => 'London, United Kingdom',
        ])->assertSessionHasErrors(['location']);

        $this->assertDatabaseCount('bookings', 0);
    }
}
