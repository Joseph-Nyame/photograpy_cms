<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class AdminCampaignService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function campaigns(): array
    {
        return Campaign::query()
            ->withCount('recipients')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Campaign $campaign): array => $this->present($campaign))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'uuid' => $campaign->uuid,
            'subject' => $campaign->subject,
            'body' => $campaign->body,
            'status' => ucfirst($campaign->status->value),
            'status_value' => $campaign->status->value,
            'sent' => $campaign->sent_at?->format('d M Y') ?? '—',
            'recipients' => $campaign->recipients_count ?? $campaign->recipients()->count(),
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function audiences(): array
    {
        return [
            ['value' => 'all', 'label' => 'All clients'],
            ['value' => 'subscribed', 'label' => 'Subscribed clients'],
            ['value' => 'with_bookings', 'label' => 'Clients with bookings'],
        ];
    }

    /**
     * @return Collection<int, Client>
     */
    public function recipients(string $audience): Collection
    {
        return Client::query()
            ->when($audience === 'subscribed', fn ($query) => $query->where('subscribed', true))
            ->when($audience === 'with_bookings', fn ($query) => $query->has('bookings'))
            ->orderBy('name')
            ->get();
    }
}
