<?php

namespace Tests\Feature;

use App\Enums\ClientSource;
use App\Enums\GalleryStatus;
use App\Mail\ClientEmail;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\EmailMessage;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GallerySchedulerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function gallery(Carbon $expiresAt, GalleryStatus $status = GalleryStatus::Active): ClientGallery
    {
        $client = Client::create([
            'name' => 'Ama Boateng',
            'email' => uniqid('client', true).'@example.com',
            'source' => ClientSource::Manual,
        ]);

        return ClientGallery::create([
            'client_id' => $client->id,
            'title' => 'Ama · Wedding',
            'token' => uniqid('token', true),
            'expires_at' => $expiresAt,
            'status' => $status,
        ]);
    }

    public function test_expire_command_marks_past_galleries_as_expired(): void
    {
        $past = $this->gallery(now()->subDay());
        $future = $this->gallery(now()->addWeek());

        $this->artisan('galleries:expire')->assertSuccessful();

        $this->assertSame(GalleryStatus::Expired, $past->fresh()->status);
        $this->assertSame(GalleryStatus::Active, $future->fresh()->status);
    }

    public function test_reminder_command_emails_clients_with_galleries_expiring_soon(): void
    {
        $soon = $this->gallery(now()->addDays(2));
        $far = $this->gallery(now()->addMonth());
        $this->gallery(now()->subDay(), GalleryStatus::Expired);

        $this->artisan('galleries:remind')->assertSuccessful();

        Mail::assertSent(ClientEmail::class, 1);
        Mail::assertSent(ClientEmail::class, fn (ClientEmail $mail): bool => $mail->mailSubject === 'Your gallery expires soon'
            && $mail->hasTo($soon->client->email));
        $this->assertDatabaseHas('email_messages', [
            'client_id' => $soon->client_id,
            'subject' => 'Your gallery expires soon',
        ]);
        $this->assertDatabaseMissing('email_messages', ['client_id' => $far->client_id]);
    }

    public function test_reminders_are_not_resent_the_same_day(): void
    {
        $this->gallery(now()->addDays(2));

        $this->artisan('galleries:remind')->assertSuccessful();
        $this->artisan('galleries:remind')->assertSuccessful();

        Mail::assertSent(ClientEmail::class, 1);
        $this->assertSame(1, EmailMessage::query()->count());
    }

    public function test_gallery_commands_are_scheduled(): void
    {
        $commands = collect($this->app->make(Schedule::class)->events())
            ->map(fn ($event): string => $event->command)
            ->implode(' ');

        $this->assertStringContainsString('galleries:expire', $commands);
        $this->assertStringContainsString('galleries:remind', $commands);
    }
}
