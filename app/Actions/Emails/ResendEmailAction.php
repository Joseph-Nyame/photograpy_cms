<?php

namespace App\Actions\Emails;

use App\Enums\EmailMessageStatus;
use App\Models\EmailMessage;
use App\Services\AdminEmailService;
use App\Services\EmailDeliveryService;
use App\Support\Result;

class ResendEmailAction
{
    public function __construct(
        private readonly AdminEmailService $emails,
        private readonly EmailDeliveryService $delivery,
    ) {}

    public function execute(EmailMessage $email): Result
    {
        $email->load('client');

        $this->delivery->send($email->client->email, $email->subject, $email->body, $email->client->name);

        $email->update([
            'sent_at' => now(),
            'status' => EmailMessageStatus::Sent,
        ]);

        return Result::ok($this->emails->present($email), 'Email re-sent.');
    }
}
