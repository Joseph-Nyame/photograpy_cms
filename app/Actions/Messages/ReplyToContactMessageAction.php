<?php

namespace App\Actions\Messages;

use App\Enums\EmailMessageStatus;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use App\Models\EmailMessage;
use App\Services\AdminMessageService;
use App\Services\EmailDeliveryService;
use App\Support\Result;

class ReplyToContactMessageAction
{
    public function __construct(
        private readonly AdminMessageService $messages,
        private readonly EmailDeliveryService $delivery,
    ) {}

    public function execute(ContactMessage $message, string $body): Result
    {
        $subject = 'Re: '.$this->messages->subject($message->message);

        $this->delivery->send(
            $message->email,
            $subject,
            $body,
            $message->name,
            new ContactReplyMail($message, $subject, $body),
        );

        if ($message->client_id !== null) {
            EmailMessage::create([
                'client_id' => $message->client_id,
                'subject' => $subject,
                'body' => $body,
                'sent_at' => now(),
                'status' => EmailMessageStatus::Sent,
            ]);
        }

        if ($message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        return Result::ok($message->refresh(), 'Reply sent.');
    }
}
