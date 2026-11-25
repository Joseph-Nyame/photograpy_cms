<?php

namespace App\Services;

use App\Enums\GalleryAccessAction;
use App\Models\ClientGallery;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\URL;

class ClientGalleryService
{
    /**
     * @return array<string, mixed>
     */
    public function present(ClientGallery $gallery, bool $unlocked): array
    {
        return [
            'id' => $gallery->id,
            'uuid' => $gallery->uuid,
            'title' => $gallery->title,
            'token' => $gallery->token,
            'client' => $gallery->client?->name ?? 'Client',
            'expires' => $gallery->expires_at?->diffForHumans() ?? 'No expiry set',
            'has_passcode' => $gallery->passcode_hash !== null,
            'allow_download' => (bool) $gallery->allow_download,
            'unlocked' => $unlocked,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function images(ClientGallery $gallery, bool $unlocked): array
    {
        if (! $unlocked) {
            return [];
        }

        $favourites = $this->favourites($gallery);

        return $gallery->images()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(function (GalleryImage $image) use ($gallery, $favourites): array {
                $params = ['gallery' => $gallery->token, 'image' => $image->uuid];

                return [
                    'id' => $image->id,
                    'uuid' => $image->uuid,
                    'label' => 'Frame '.str_pad((string) ($image->sort ?: $image->id), 2, '0', STR_PAD_LEFT),
                    'thumb_url' => route('client-gallery.preview', $params),
                    'preview_url' => route('client-gallery.preview', $params),
                    'download_url' => $gallery->allow_download
                        ? URL::signedRoute('client-gallery.download', $params)
                        : null,
                    'favourite' => in_array($image->id, $favourites, true),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function favourites(ClientGallery $gallery): array
    {
        $favourites = session()->get("gallery.favourites.{$gallery->id}", []);

        return is_array($favourites) ? array_values(array_map('intval', $favourites)) : [];
    }

    /**
     * @param  array<int, int>  $favourites
     */
    public function setFavourites(ClientGallery $gallery, array $favourites): void
    {
        session()->put("gallery.favourites.{$gallery->id}", array_values($favourites));
    }

    public function unlocked(ClientGallery $gallery): bool
    {
        if (! $gallery->passcode_hash) {
            return true;
        }

        return (bool) session()->get("gallery.unlocked.{$gallery->id}", false);
    }

    public function unlock(ClientGallery $gallery): void
    {
        session()->put("gallery.unlocked.{$gallery->id}", true);
    }

    public function lock(ClientGallery $gallery): void
    {
        session()->forget("gallery.unlocked.{$gallery->id}");
        session()->forget("gallery.favourites.{$gallery->id}");
        session()->forget("gallery.viewed.{$gallery->id}");
    }

    public function markViewed(ClientGallery $gallery): bool
    {
        if (session()->get("gallery.viewed.{$gallery->id}", false)) {
            return false;
        }

        session()->put("gallery.viewed.{$gallery->id}", true);
        $this->log($gallery, GalleryAccessAction::View);

        return true;
    }

    public function log(ClientGallery $gallery, GalleryAccessAction $action): void
    {
        $gallery->accessLogs()->create([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => $action,
        ]);
    }
}
