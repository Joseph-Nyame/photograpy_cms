<?php

namespace Tests\Feature\Admin;

use App\Enums\ClientSource;
use App\Mail\ContactReplyMail;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\EmailMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminMessageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function message(bool $read = false): ContactMessage
    {
        $client = Client::firstOrCreate(
            ['email' => 'esi@example.com'],
            ['name' => 'Esi Owusu', 'source' => ClientSource::Contact],
        );

        return ContactMessage::create([
            'client_id' => $client->id,
            'name' => 'Esi Owusu',
            'email' => 'esi@example.com',
            'message' => 'Portrait session in December',
            'read_at' => $read ? now() : null,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/messages')->assertRedirect('/login');
    }

    public function test_admin_can_view_the_inbox(): void
    {
        $this->message();

        $this->actingAs($this->admin())
            ->get('/admin/messages')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminMessages')
                ->has('messages', 1)
                ->where('messages.0.name', 'Esi Owusu')
                ->where('messages.0.read', false)
                ->where('unread', 1));
    }

    public function test_admin_can_mark_a_message_read_and_unread(): void
    {
        $message = $this->message();

        $this->actingAs($this->admin())
            ->post("/admin/messages/{$message->uuid}/read")
            ->assertSessionHas('success', true);

        $this->assertNotNull($message->refresh()->read_at);

        $this->actingAs($this->admin())
            ->post("/admin/messages/{$message->uuid}/unread")
            ->assertSessionHas('success', true);

        $this->assertNull($message->refresh()->read_at);
    }

    public function test_admin_can_mark_all_messages_read(): void
    {
        $this->message();
        $this->message();

        $this->actingAs($this->admin())
            ->post('/admin/messages/read-all')
            ->assertSessionHas('success', true);

        $this->assertSame(0, ContactMessage::query()->whereNull('read_at')->count());
    }

    public function test_admin_can_reply_to_a_message(): void
    {
        Mail::fake();

        $message = $this->message();

        $this->actingAs($this->admin())
            ->post("/admin/messages/{$message->uuid}/reply", ['body' => 'We have the 20th open.'])
            ->assertSessionHas('success', true);

        Mail::assertSent(ContactReplyMail::class, fn (ContactReplyMail $mail): bool => $mail->hasTo('esi@example.com'));

        $this->assertDatabaseHas('email_messages', [
            'client_id' => $message->client_id,
            'status' => 'sent',
        ]);
        $this->assertSame(1, EmailMessage::query()->count());
        $this->assertNotNull($message->refresh()->read_at);
    }

    public function test_reply_requires_a_body(): void
    {
        $message = $this->message();

        $this->actingAs($this->admin())
            ->post("/admin/messages/{$message->uuid}/reply", ['body' => ''])
            ->assertSessionHasErrors('body');
    }
}
