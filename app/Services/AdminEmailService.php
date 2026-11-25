<?php

namespace App\Services;

use App\Models\EmailMessage;

class AdminEmailService
{
    public function __construct(private readonly AdminClientService $clients) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function emails(): array
    {
        return EmailMessage::query()
            ->with('client')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (EmailMessage $email): array => $this->present($email))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(EmailMessage $email): array
    {
        return [
            'id' => $email->id,
            'uuid' => $email->uuid,
            'to' => $email->client?->name ?? 'Unknown',
            'email' => $email->client?->email ?? '',
            'subject' => $email->subject,
            'body' => $email->body,
            'sent' => $email->sent_at?->format('d M Y · H:i') ?? '—',
            'status' => ucfirst($email->status->value),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, email: string}>
     */
    public function clients(): array
    {
        return $this->clients->options();
    }

    /**
     * @return array<int, array{value: string, label: string, subject: string, body: string}>
     */
    public function templates(): array
    {
        return [
            [
                'value' => 'blank',
                'label' => 'Blank',
                'subject' => '',
                'body' => '',
            ],
            [
                'value' => 'gallery_ready',
                'label' => 'Gallery ready',
                'subject' => 'Your gallery is ready',
                'body' => "Hi {{ name }},\n\nYour photos are ready to view. Please use the private link we sent you to open the gallery.\n\nThank you for choosing Lumen Studio.",
            ],
            [
                'value' => 'booking_confirmation',
                'label' => 'Booking confirmation',
                'subject' => 'Your booking is confirmed',
                'body' => "Hi {{ name }},\n\nWe are looking forward to seeing you. Your session is confirmed — please reply if you need to change anything.\n\nSee you soon,\nLumen Studio",
            ],
            [
                'value' => 'session_availability',
                'label' => 'Session availability',
                'subject' => 'Session availability',
                'body' => "Hi {{ name }},\n\nThank you for reaching out. Here are the dates we have open at the studio. Let us know what works best for you.\n\nWarm regards,\nLumen Studio",
            ],
        ];
    }
}
