<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Support\Str;

class AdminMessageService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(): array
    {
        return ContactMessage::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ContactMessage $message): array => $this->present($message))
            ->all();
    }

    public function unreadCount(): int
    {
        return ContactMessage::query()->whereNull('read_at')->count();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(ContactMessage $message): array
    {
        return [
            'id' => $message->id,
            'uuid' => $message->uuid,
            'name' => $message->name,
            'email' => $message->email,
            'phone' => $message->phone,
            'subject' => $this->subject($message->message),
            'body' => $message->message,
            'time' => $this->time($message),
            'read' => $message->read_at !== null,
        ];
    }

    public function subject(string $body): string
    {
        $line = trim((string) strtok($body, "\n"));

        return $line === '' ? 'New message' : Str::limit($line, 60);
    }

    private function time(ContactMessage $message): string
    {
        $created = $message->created_at;

        if ($created->isToday()) {
            return $created->format('H:i');
        }

        if ($created->isCurrentYear()) {
            return $created->format('d M');
        }

        return $created->format('d M Y');
    }
}
