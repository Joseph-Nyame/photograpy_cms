<?php

namespace Tests\Feature;

use App\Enums\ClientSource;
use App\Models\Client;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_send_a_message(): void
    {
        $this->post('/contact', [
            'name' => 'Kojo Mensah',
            'email' => 'kojo@example.com',
            'phone' => '+233 24 000 0000',
            'message' => 'Do you cover football matches?',
        ])->assertRedirect()->assertSessionHas('success', true);

        $client = Client::query()->where('email', 'kojo@example.com')->first();

        $this->assertNotNull($client);
        $this->assertSame(ClientSource::Contact, $client->source);

        $message = ContactMessage::query()->first();

        $this->assertNotNull($message);
        $this->assertSame($client->id, $message->client_id);
        $this->assertSame('Do you cover football matches?', $message->message);
    }

    public function test_message_requires_name_email_and_body(): void
    {
        $this->post('/contact', [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
        ])->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
