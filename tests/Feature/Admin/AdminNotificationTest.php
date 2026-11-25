<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\ClientSource;
use App\Enums\GalleryStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'source' => ClientSource::Manual,
        ]);
    }

    public function test_a_new_public_booking_notifies_admins_with_fresh_counts(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $this->seed();

        $this->post('/booking', [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'date' => now()->addDays(10)->toDateString(),
            'location' => 'Accra, Ghana',
        ])->assertSessionHas('success', true);

        Notification::assertSentTo($admin, AdminNotification::class, function (AdminNotification $notification): bool {
            return $notification->title === 'New booking request'
                && $notification->url === route('admin.bookings')
                && $notification->counts['pendingBookings'] === 1
                && $notification->counts['unreadMessages'] === 0;
        });
    }

    public function test_a_new_contact_message_notifies_admins(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $this->post('/contact', [
            'name' => 'Kojo Mensah',
            'email' => 'kojo@example.com',
            'message' => 'Do you cover football matches?',
        ])->assertSessionHas('success', true);

        Notification::assertSentTo($admin, AdminNotification::class, function (AdminNotification $notification): bool {
            return $notification->title === 'New contact message'
                && $notification->tone === 'warning'
                && $notification->url === route('admin.messages')
                && $notification->counts['unreadMessages'] === 1;
        });
    }

    public function test_notifications_are_persisted_and_broadcast(): void
    {
        $admin = $this->admin();

        $this->post('/contact', [
            'name' => 'Kojo Mensah',
            'email' => 'kojo@example.com',
            'message' => 'Do you cover football matches?',
        ])->assertSessionHas('success', true);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'notifiable_type' => $admin->getMorphClass(),
            'type' => AdminNotification::class,
        ]);
    }

    public function test_sidebar_counts_are_loaded_from_the_database(): void
    {
        $client = $this->client();

        Booking::create([
            'client_id' => $client->id,
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => BookingStatus::Pending,
        ]);

        ContactMessage::create([
            'client_id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'message' => 'Please call me.',
        ]);

        ClientGallery::create([
            'client_id' => $client->id,
            'title' => 'Ama · Wedding',
            'token' => 'token1234567890ab',
            'status' => GalleryStatus::Active,
            'expires_at' => now()->addWeek(),
        ]);

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.pendingBookings', 1)
                ->where('navigation.unreadMessages', 1)
                ->where('navigation.activeGalleries', 1));
    }

    public function test_only_the_admin_can_authorize_their_broadcast_channel(): void
    {
        config()->set('broadcasting.default', 'reverb');
        require base_path('routes/channels.php');

        $admin = $this->admin();
        $other = $this->admin();

        $payload = [
            'socket_id' => '123.456',
            'channel_name' => 'private-App.Models.User.'.$admin->id,
        ];

        $this->actingAs($admin)
            ->post('/broadcasting/auth', $payload)
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this->actingAs($other)
            ->post('/broadcasting/auth', $payload)
            ->assertForbidden();
    }
}
