<?php

namespace App\Services;

use App\Enums\GalleryStatus;
use App\Models\ClientGallery;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\Storage;

class AdminGalleryService
{
    public function __construct(private readonly AdminClientService $clients) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function galleries(): array
    {
        return ClientGallery::query()
            ->with('client')
            ->withCount('images')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ClientGallery $gallery): array => $this->present($gallery))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(ClientGallery $gallery): array
    {
        return [
            'id' => $gallery->id,
            'uuid' => $gallery->uuid,
            'title' => $gallery->title,
            'client' => $gallery->client?->name ?? 'Unknown',
            'client_id' => $gallery->client_id,
            'token' => $gallery->token,
            'images' => $gallery->images_count ?? $gallery->images()->count(),
            'expires' => $gallery->expires_at?->format('d M Y') ?? '—',
            'expires_iso' => $gallery->expires_at?->toDateString(),
            'download' => (bool) $gallery->allow_download,
            'has_passcode' => $gallery->passcode_hash !== null,
            'status' => $this->status($gallery),
            'url' => url('/g/'.$gallery->token),
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
     * @return array<int, array{id: int, url: string, sort: int}>
     */
    public function images(ClientGallery $gallery): array
    {
        return $gallery->images()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (GalleryImage $image): array => [
                'id' => $image->id,
                'uuid' => $image->uuid,
                'url' => Storage::disk('public')->url($image->original_path),
                'sort' => $image->sort,
            ])
            ->all();
    }

    private function status(ClientGallery $gallery): string
    {
        if ($gallery->status === GalleryStatus::Expired || ($gallery->expires_at && $gallery->expires_at->isPast())) {
            return 'Expired';
        }

        if ($gallery->expires_at && $gallery->expires_at->lessThanOrEqualTo(now()->addWeek())) {
            return 'Expiring';
        }

        return 'Active';
    }
}
