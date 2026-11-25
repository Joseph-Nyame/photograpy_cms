<?php

namespace App\Console\Commands;

use App\Enums\EmailMessageStatus;
use App\Enums\GalleryStatus;
use App\Models\ClientGallery;
use App\Models\EmailMessage;
use App\Services\EmailDeliveryService;
use Illuminate\Console\Command;

class SendGalleryExpiryReminders extends Command
{
    protected $signature = 'galleries:remind';

    protected $description = 'Email clients whose galleries expire in the next three days';

    public function __construct(private readonly EmailDeliveryService $delivery)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $galleries = ClientGallery::query()
            ->with('client')
            ->where('status', GalleryStatus::Active)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->get();

        $sent = 0;

        foreach ($galleries as $gallery) {
            $client = $gallery->client;

            if (! $client) {
                continue;
            }

            $alreadySent = EmailMessage::query()
                ->where('client_id', $client->id)
                ->where('subject', 'Your gallery expires soon')
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $this->delivery->send(
                $client->email,
                'Your gallery expires soon',
                "Hi {$client->name},\n\nYour Lumen Studio gallery ({$gallery->title}) expires on {$gallery->expires_at->format('d M Y')}. Open your private link to save your favourites and request originals before then.\n\nWarm regards,\nLumen Studio",
                $client->name,
            );

            EmailMessage::create([
                'client_id' => $client->id,
                'subject' => 'Your gallery expires soon',
                'body' => "Your gallery {$gallery->title} expires on {$gallery->expires_at->format('d M Y')}.",
                'sent_at' => now(),
                'status' => EmailMessageStatus::Sent,
            ]);

            $sent++;
        }

        $this->info("Sent {$sent} expiry reminder(s).");

        return self::SUCCESS;
    }
}
