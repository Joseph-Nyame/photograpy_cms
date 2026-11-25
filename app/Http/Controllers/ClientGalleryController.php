<?php

namespace App\Http\Controllers;

use App\Actions\ClientGallery\ToggleGalleryFavouriteAction;
use App\Actions\ClientGallery\VerifyGalleryPasscodeAction;
use App\DTOs\ClientGallery\UnlockGalleryDTO;
use App\Enums\GalleryAccessAction;
use App\Http\Requests\UnlockGalleryRequest;
use App\Models\ClientGallery;
use App\Models\GalleryImage;
use App\Services\ClientGalleryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientGalleryController extends Controller
{
    public function show(ClientGallery $gallery, ClientGalleryService $service): Response
    {
        $unlocked = $service->unlocked($gallery);

        if ($unlocked) {
            $service->markViewed($gallery);
        }

        return Inertia::render('ClientGallery', [
            'gallery' => $service->present($gallery, $unlocked),
            'images' => $service->images($gallery, $unlocked),
            'favourites' => $service->favourites($gallery),
            'unlocked' => $unlocked,
        ]);
    }

    public function unlock(
        UnlockGalleryRequest $request,
        ClientGallery $gallery,
        VerifyGalleryPasscodeAction $action,
        ClientGalleryService $service,
    ): RedirectResponse {
        $result = $action->execute($gallery, UnlockGalleryDTO::fromRequest($request));

        if (! $result->success) {
            return back()
                ->withErrors(['passcode' => $result->message])
                ->with('success', false)
                ->with('message', $result->message);
        }

        $service->unlock($gallery);
        $service->markViewed($gallery);

        return redirect()->route('client-gallery', $gallery->token);
    }

    public function lock(ClientGallery $gallery, ClientGalleryService $service): RedirectResponse
    {
        $service->lock($gallery);

        return redirect()->route('client-gallery', $gallery->token);
    }

    public function toggleFavourite(
        ClientGallery $gallery,
        GalleryImage $image,
        ToggleGalleryFavouriteAction $action,
        ClientGalleryService $service,
    ): RedirectResponse {
        $this->authorizeGallery($gallery, $service);
        $this->assertImageBelongs($gallery, $image);

        $result = $action->execute($image->id, $service->favourites($gallery));
        $service->setFavourites($gallery, $result->data['favourites']);
        $service->log($gallery, GalleryAccessAction::Favourite);

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function preview(ClientGallery $gallery, GalleryImage $image, ClientGalleryService $service): StreamedResponse
    {
        $this->authorizeGallery($gallery, $service);
        $this->assertImageBelongs($gallery, $image);

        $path = $image->preview_path ?: $image->original_path;

        return Storage::disk('public')->response(
            $path,
            null,
            ['Cache-Control' => 'private, no-store'],
            'inline',
        );
    }

    public function download(ClientGallery $gallery, GalleryImage $image, ClientGalleryService $service): StreamedResponse
    {
        $this->authorizeGallery($gallery, $service);
        $this->assertImageBelongs($gallery, $image);

        if (! $gallery->allow_download) {
            abort(403, 'Downloads are not enabled for this gallery.');
        }

        $service->log($gallery, GalleryAccessAction::Download);

        return Storage::disk('public')->download($image->original_path, basename($image->original_path));
    }

    private function authorizeGallery(ClientGallery $gallery, ClientGalleryService $service): void
    {
        abort_unless($service->unlocked($gallery), 403, 'Unlock the gallery first.');
    }

    private function assertImageBelongs(ClientGallery $gallery, GalleryImage $image): void
    {
        abort_unless($image->gallery_id === $gallery->id, 404);
    }
}
