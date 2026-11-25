<?php

namespace App\Services;

use App\Mail\ClientEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class EmailDeliveryService
{
    public function __construct(private readonly SettingsService $settings) {}

    public function send(string $to, string $subject, string $body, string $recipientName = '', ?Mailable $mailable = null): void
    {
        $config = $this->settings->emailConfig();

        if ($config['provider'] === 'ahasend' && $config['active']) {
            if (! $config['configured']) {
                throw new RuntimeException('AhaSend is enabled but its API key or account ID is missing. Update it in Settings.');
            }

            $this->sendViaAhaSend($config, $to, $subject, $body, $recipientName);

            return;
        }

        Mail::to($to)->send($mailable ?? new ClientEmail(
            mailSubject: $subject,
            body: $body,
            recipientName: $recipientName,
        ));
    }

    /**
     * @param  array{provider: string, apiKey: string|null, hint: string|null, accountId: string|null, from: string|null, fromName: string, active: bool, configured: bool}  $config
     */
    private function sendViaAhaSend(array $config, string $to, string $subject, string $body, string $recipientName): void
    {
        $response = Http::withToken($config['apiKey'])
            ->acceptJson()
            ->timeout(30)
            ->post("https://api.ahasend.com/v2/accounts/{$config['accountId']}/messages", [
                'from' => [
                    'email' => $config['from'],
                    'name' => $config['fromName'],
                ],
                'recipients' => [[
                    'email' => $to,
                    'name' => $recipientName !== '' ? $recipientName : explode('@', $to)[0],
                ]],
                'subject' => $subject,
                'text_content' => $body,
            ]);

        if ($response->status() !== 202) {
            throw new RuntimeException(
                "AhaSend rejected the email (HTTP {$response->status()}): ".$response->body()
            );
        }
    }
}
