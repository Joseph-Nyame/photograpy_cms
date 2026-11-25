<?php

namespace App\Actions\Emails;

use App\DTOs\Emails\SendEmailDTO;
use App\Enums\EmailMessageStatus;
use App\Models\Client;
use App\Models\EmailMessage;
use App\Services\AdminEmailService;
use App\Services\EmailDeliveryService;
use App\Support\Result;

class SendEmailAction
{
    public function __construct(
        private readonly AdminEmailService $emails,
        private readonly EmailDeliveryService $delivery,
    ) {}

    public function execute(SendEmailDTO $dto): Result
    {
        $client = Client::findOrFail($dto->clientId);

        $this->delivery->send($client->email, $dto->subject, $dto->body, $client->name);

        $email = EmailMessage::create([
            'client_id' => $client->id,
            'subject' => $dto->subject,
            'body' => $dto->body,
            'sent_at' => now(),
            'status' => EmailMessageStatus::Sent,
        ]);
        $email->load('client');

        return Result::ok($this->emails->present($email), 'Email sent.');
    }
}
