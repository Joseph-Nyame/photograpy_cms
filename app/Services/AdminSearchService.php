<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\ContactMessage;
use App\Models\EmailMessage;
use App\Models\PortfolioImage;
use App\Models\Service as StudioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AdminSearchService
{
    private const LIMIT = 5;

    public function search(string $term): array
    {
        $term = trim($term);
        $needle = $this->needle($term);
        $query = '/admin/clients?q='.rawurlencode($term);

        return array_values(array_filter([
            $this->group('Clients', Client::query()
                ->where('name', 'like', $needle)
                ->orWhere('email', 'like', $needle)
                ->orWhere('phone', 'like', $needle)
                ->orWhere('notes', 'like', $needle)
                ->orderBy('name')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (Client $client): array => [
                    'label' => $client->name,
                    'sub' => $client->email,
                    'href' => $query,
                ])
                ->all()),

            $this->group('Bookings', Booking::query()
                ->with('client')
                ->where('location', 'like', $needle)
                ->orWhere('message', 'like', $needle)
                ->orWhere('status', 'like', $needle)
                ->orWhereHas('client', fn (Builder $q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('email', 'like', $needle))
                ->orderByDesc('event_date')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (Booking $booking): array => [
                    'label' => $booking->client?->name ?? 'Unknown client',
                    'sub' => trim(($booking->event_date?->format('d M Y') ?? 'No date').' · '.($booking->location ?? '')),
                    'href' => '/admin/bookings?q='.rawurlencode($term),
                ])
                ->all()),

            $this->group('Galleries', ClientGallery::query()
                ->with('client')
                ->where('title', 'like', $needle)
                ->orWhere('token', 'like', $needle)
                ->orWhereHas('client', fn (Builder $q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('email', 'like', $needle))
                ->orderByDesc('created_at')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (ClientGallery $gallery): array => [
                    'label' => $gallery->title,
                    'sub' => trim(($gallery->client?->name ?? 'Unknown client').' · '.$gallery->token),
                    'href' => '/admin/galleries/'.$gallery->uuid,
                ])
                ->all()),

            $this->group('Messages', ContactMessage::query()
                ->where('name', 'like', $needle)
                ->orWhere('email', 'like', $needle)
                ->orWhere('message', 'like', $needle)
                ->orderByDesc('created_at')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (ContactMessage $message): array => [
                    'label' => $message->name,
                    'sub' => $message->email.' · '.Str::limit(str_replace("\n", ' ', $message->message), 40),
                    'href' => '/admin/messages',
                ])
                ->all()),

            $this->group('Services', StudioService::query()
                ->where('name', 'like', $needle)
                ->orWhere('description', 'like', $needle)
                ->orderBy('name')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (StudioService $service): array => [
                    'label' => $service->name,
                    'sub' => Str::limit($service->description ?? '', 50) ?: 'Studio service',
                    'href' => '/admin/services',
                ])
                ->all()),

            $this->group('Campaigns', Campaign::query()
                ->where('subject', 'like', $needle)
                ->orWhere('body', 'like', $needle)
                ->orderByDesc('created_at')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (Campaign $campaign): array => [
                    'label' => $campaign->subject,
                    'sub' => 'Campaign · '.ucfirst((string) ($campaign->status?->value ?? 'draft')),
                    'href' => '/admin/campaigns',
                ])
                ->all()),

            $this->group('Emails', EmailMessage::query()
                ->with('client')
                ->where('subject', 'like', $needle)
                ->orWhere('body', 'like', $needle)
                ->orWhereHas('client', fn (Builder $q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('email', 'like', $needle))
                ->orderByDesc('sent_at')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (EmailMessage $email): array => [
                    'label' => $email->subject,
                    'sub' => $email->client?->email ?? 'Studio email',
                    'href' => '/admin/emails',
                ])
                ->all()),

            $this->group('Portfolio', PortfolioImage::query()
                ->with('category')
                ->where('alt', 'like', $needle)
                ->orWhere('path', 'like', $needle)
                ->orWhereHas('category', fn (Builder $q) => $q->where('name', 'like', $needle))
                ->orderBy('sort')
                ->limit(self::LIMIT)
                ->get()
                ->map(fn (PortfolioImage $image): array => [
                    'label' => $image->alt ?: 'Portfolio image',
                    'sub' => $image->category?->name ?? 'Portfolio',
                    'href' => '/admin/portfolio',
                ])
                ->all()),
        ]));
    }

    private function group(string $label, array $items): ?array
    {
        return $items === [] ? null : ['label' => $label, 'items' => $items];
    }

    private function needle(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }
}
