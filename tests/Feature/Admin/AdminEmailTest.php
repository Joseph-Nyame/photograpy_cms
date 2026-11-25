<?php

namespace Tests\Feature\Admin;

use App\Enums\ClientSource;
use App\Enums\EmailMessageStatus;
use App\Mail\ClientEmail;
use App\Models\Client;
use App\Models\EmailMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Mail::fake();
    }

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

    private function email(Client $client): EmailMessage
    {
        return EmailMessage::create([
            'client_id' => $client->id,
            'subject' => 'Your gallery is ready',
            'body' => 'Hello Ama, your photos are ready.',
            'sent_at' => now()->subDay(),
            'status' => EmailMessageStatus::Sent,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/emails')->assertRedirect('/login');
    }

    public function test_admin_can_view_emails(): void
    {
        $this->email($this->client());

        $this->actingAs($this->admin())
            ->get('/admin/emails')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminEmails')
                ->has('emails', 1)
                ->where('emails.0.to', 'Ama Boateng')
                ->where('emails.0.subject', 'Your gallery is ready')
                ->has('clients', 1)
                ->has('templates', 4));
    }

    public function test_admin_can_send_an_email(): void
    {
        $client = $this->client();

        $this->actingAs($this->admin())
            ->post('/admin/emails', [
                'client_id' => $client->id,
                'subject' => 'Your gallery is ready',
                'body' => 'Hello Ama, your photos are ready.',
            ])
            ->assertSessionHas('success', true);

        $this->assertDatabaseHas('email_messages', [
            'client_id' => $client->id,
            'subject' => 'Your gallery is ready',
            'status' => EmailMessageStatus::Sent->value,
        ]);
        $this->assertNotNull(EmailMessage::query()->first()->sent_at);

        Mail::assertSent(ClientEmail::class, function (ClientEmail $mail) use ($client): bool {
            return $mail->hasTo($client->email) && $mail->mailSubject === 'Your gallery is ready';
        });
    }

    public function test_sending_an_email_validates_input(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/emails', [])
            ->assertSessionHasErrors(['client_id', 'subject', 'body']);
    }

    public function test_admin_can_resend_an_email(): void
    {
        $email = $this->email($this->client());
        $email->update(['sent_at' => null, 'status' => EmailMessageStatus::Queued]);

        $this->actingAs($this->admin())
            ->post("/admin/emails/{$email->uuid}/resend")
            ->assertSessionHas('success', true);

        $email->refresh();

        $this->assertSame(EmailMessageStatus::Sent, $email->status);
        $this->assertNotNull($email->sent_at);
        Mail::assertSent(ClientEmail::class, 1);
    }
}
