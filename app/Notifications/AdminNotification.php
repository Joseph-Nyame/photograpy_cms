<?php

namespace App\Notifications;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use InteractsWithSockets;

    /**
     * @param  array{pendingBookings: int, unreadMessages: int, activeGalleries: int}  $counts
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $url,
        public readonly array $counts = [],
        public readonly string $tone = 'info',
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    public function broadcastAs(): string
    {
        return 'admin.notification';
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'tone' => $this->tone,
            'counts' => $this->counts,
        ];
    }
}
