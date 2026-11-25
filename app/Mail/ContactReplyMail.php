<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ContactMessage $contactMessage,
        public readonly string $mailSubject,
        public readonly string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-reply',
            with: [
                'name' => $this->contactMessage->name,
                'body' => $this->body,
            ],
        );
    }
}
