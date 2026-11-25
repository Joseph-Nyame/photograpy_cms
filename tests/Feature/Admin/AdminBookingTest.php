<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\ClientSource;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminBookingTest extends TestCase
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

    private function booking(BookingStatus $status = BookingStatus::Pending): Booking
    {
        $client = Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'phone' => '+233 24 111 2200',
            'source' => ClientSource::Booking,
        ]);

        return Booking::create([
            'client_id' => $client->id,
            'event_date' => now()->addDays(10)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'location' => 'East Legon',
            'status' => $status,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/bookings')->assertRedirect('/login');
    }

    public function test_admin_can_view_the_bookings_table(): void
    {
        $this->booking();

        $this->actingAs($this->admin())
            ->get('/admin/bookings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminBookings')
                ->has('bookings', 1)
                ->where('bookings.0.client', 'Ama Boateng')
                ->where('bookings.0.status', 'Pending')
                ->where('bookings.0.time', '09:00 – 12:00')
                ->where('bookings.0.currency', 'GHS')
                ->where('bookings.0.payment_status_value', 'unpaid')
                ->where('bookings.0.booking_type', 'standard')
                ->has('bookings.0.history', 0)
                ->has('services', 4)
                ->has('locations', 6)
                ->where('locations.0.name', 'Accra, Ghana')
                ->where('locations.0.currency', 'GHS'));
    }

    public function test_admin_can_confirm_a_booking(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->admin())
            ->post("/admin/bookings/{$booking->uuid}/confirm")
            ->assertRedirect()
            ->assertSessionHas('success', true);

        $this->assertSame(BookingStatus::Confirmed, $booking->refresh()->status);
    }

    public function test_admin_can_decline_a_booking_with_a_reason(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->admin())
            ->post("/admin/bookings/{$booking->uuid}/decline", ['reason' => 'Date already blocked.'])
            ->assertSessionHas('success', true);

        $booking->refresh();

        $this->assertSame(BookingStatus::Declined, $booking->status);
        $this->assertSame('Date already blocked.', $booking->decline_reason);
    }

    public function test_admin_can_reopen_a_booking(): void
    {
        $booking = $this->booking(BookingStatus::Declined);

        $this->actingAs($this->admin())
            ->post("/admin/bookings/{$booking->uuid}/reopen")
            ->assertSessionHas('success', true);

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_admin_can_update_status_payment_and_record_history(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->admin())
            ->put("/admin/bookings/{$booking->uuid}", [
                'status' => 'confirmed',
                'payment_status' => 'part_paid',
                'note' => 'Deposit received via bank transfer.',
            ])
            ->assertSessionHas('success', true);

        $booking->refresh();

        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(PaymentStatus::PartPaid, $booking->payment_status);
        $this->assertDatabaseHas('booking_activities', [
            'booking_id' => $booking->id,
            'action' => 'status_changed',
            'from_value' => 'pending',
            'to_value' => 'confirmed',
            'note' => 'Deposit received via bank transfer.',
        ]);
        $this->assertDatabaseHas('booking_activities', [
            'booking_id' => $booking->id,
            'action' => 'payment_updated',
            'from_value' => 'unpaid',
            'to_value' => 'part_paid',
        ]);
        $this->assertSame(2, $booking->activities()->count());
    }

    public function test_admin_can_record_a_note_without_changing_status(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->admin())
            ->put("/admin/bookings/{$booking->uuid}", [
                'note' => 'Client called. Moving the shoot to the following weekend.',
            ])
            ->assertSessionHas('success', true);

        $booking->refresh();

        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(PaymentStatus::Unpaid, $booking->payment_status);
        $this->assertDatabaseHas('booking_activities', [
            'booking_id' => $booking->id,
            'action' => 'note_added',
            'note' => 'Client called. Moving the shoot to the following weekend.',
        ]);
    }

    public function test_booking_update_requires_a_change(): void
    {
        $booking = $this->booking();

        $this->actingAs($this->admin())
            ->put("/admin/bookings/{$booking->uuid}", [])
            ->assertSessionHasErrors(['note']);
    }

    public function test_admin_can_create_a_booking_manually(): void
    {
        $service = Service::query()->first();

        $this->actingAs($this->admin())
            ->post('/admin/bookings', [
                'client' => 'Kojo Mensah',
                'email' => 'kojo@example.com',
                'phone' => '+233 20 442 1188',
                'service_id' => $service->id,
                'date' => now()->addDays(20)->toDateString(),
                'start_time' => '15:00',
                'end_time' => '18:00',
                'location' => 'New York, United States',
                'status' => 'confirmed',
                'note' => 'Team access approved.',
            ])
            ->assertSessionHas('success', true);

        $booking = Booking::query()->first();

        $this->assertNotNull($booking);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame($service->id, $booking->service_id);
        $this->assertSame('Kojo Mensah', $booking->client->name);
        $this->assertSame(ClientSource::Manual, $booking->client->source);
        $this->assertSame(BookingType::Standard, $booking->booking_type);
        $this->assertSame('USD', $booking->currency);
    }

    public function test_manual_booking_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/bookings', [])
            ->assertSessionHasErrors(['client', 'email', 'date']);
    }

    public function test_manual_booking_requires_a_configured_location(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/bookings', [
                'client' => 'Kojo Mensah',
                'email' => 'kojo@example.com',
                'date' => now()->addDays(20)->toDateString(),
                'location' => 'Baba Yara Stadium',
            ])
            ->assertSessionHasErrors(['location']);

        $this->assertDatabaseCount('bookings', 0);
    }
}
