<?php

namespace Tests\Feature;

use App\Mail\ClientEmail;
use App\Services\EmailDeliveryService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class EmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_sends_via_ahasend_when_enabled_in_settings(): void
    {
        Http::fake(['api.ahasend.com/*' => Http::response(['message_id' => 'msg-1'], 202)]);
        $this->configureAhaSend();

        app(EmailDeliveryService::class)->send(
            'client@example.com',
            'Your gallery is ready',
            'Hello Ama, your photos are ready.',
            'Ama Boateng',
        );

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.ahasend.com/v2/accounts/acc-42/messages'
                && $request->header('Authorization')[0] === 'Bearer AGT-1234567890xyz'
                && $request['from']['email'] === 'studio@lumen.studio'
                && $request['recipients'][0]['email'] === 'client@example.com'
                && $request['recipients'][0]['name'] === 'Ama Boateng'
                && $request['subject'] === 'Your gallery is ready'
                && $request['text_content'] === 'Hello Ama, your photos are ready.';
        });
        Mail::assertNothingSent();
    }

    public function test_falls_back_to_laravel_mail_when_provider_is_smtp(): void
    {
        app(SettingsService::class)->store(['email.provider' => 'smtp']);

        app(EmailDeliveryService::class)->send('client@example.com', 'Subject', 'Body', 'Ama');

        Mail::assertSent(ClientEmail::class, fn (ClientEmail $mail): bool => $mail->hasTo('client@example.com'));
    }

    public function test_ahasend_without_credentials_fails_loudly(): void
    {
        app(SettingsService::class)->store([
            'email.provider' => 'ahasend',
            'email.ahasend_active' => '1',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AhaSend is enabled but its API key or account ID is missing.');

        app(EmailDeliveryService::class)->send('client@example.com', 'Subject', 'Body');
    }

    public function test_ahasend_rejection_raises_an_error(): void
    {
        Http::fake(['api.ahasend.com/*' => Http::response(['error' => 'invalid api key'], 401)]);
        $this->configureAhaSend();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AhaSend rejected the email (HTTP 401)');

        app(EmailDeliveryService::class)->send('client@example.com', 'Subject', 'Body');
    }

    private function configureAhaSend(): void
    {
        app(SettingsService::class)->store([
            'email.provider' => 'ahasend',
            'email.ahasend_active' => '1',
            'email.ahasend_account_id' => 'acc-42',
            'email.ahasend_from' => 'studio@lumen.studio',
            'email.ahasend_api_key_encrypted' => Crypt::encryptString('AGT-1234567890xyz'),
            'studio.name' => 'Lumen Studio',
        ]);
    }
}
