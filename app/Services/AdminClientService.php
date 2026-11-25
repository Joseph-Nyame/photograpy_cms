<?php

namespace App\Services;

use App\Models\Client;

class AdminClientService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function clients(): array
    {
        return Client::query()
            ->withCount('bookings')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'uuid' => $client->uuid,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'source' => ucfirst($client->source->value),
                'source_value' => $client->source->value,
                'bookings' => $client->bookings_count,
                'last' => $client->updated_at->diffForHumans(),
                'subscribed' => $client->subscribed,
            ])
            ->all();
    }

    /**
     * Lightweight client list for select inputs.
     *
     * @return array<int, array{id: int, name: string, email: string}>
     */
    public function options(): array
    {
        return Client::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ])
            ->all();
    }
}
