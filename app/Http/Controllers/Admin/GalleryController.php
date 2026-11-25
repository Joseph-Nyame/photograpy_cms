<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Galleries\CreateGalleryAction;
use App\Actions\Galleries\DeleteGalleryAction;
use App\Actions\Galleries\DeleteGalleryImageAction;
use App\Actions\Galleries\ToggleGalleryDownloadAction;
use App\Actions\Galleries\UploadGalleryImagesAction;
use App\DTOs\Galleries\CreateGalleryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGalleryRequest;
use App\Http\Requests\Admin\UploadGalleryImagesRequest;
use App\Models\ClientGallery;
use App\Models\GalleryImage;
use App\Services\AdminGalleryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function index(Request $request, AdminGalleryService $galleries): Response
    {
        return Inertia::render('AdminGalleries', [
            'galleries' => $galleries->galleries(),
            'clients' => $galleries->clients(),
            'initialQuery' => $request->string('q')->toString(),
        ]);
    }

    public function show(ClientGallery $gallery, AdminGalleryService $galleries): Response
    {
        $gallery->load('client')->loadCount('images');

        return Inertia::render('AdminGalleryDetail', [
            'gallery' => $galleries->present($gallery),
            'images' => $galleries->images($gallery),
        ]);
    }

    public function store(StoreGalleryRequest $request, CreateGalleryAction $action): RedirectResponse
    {
        $result = $action->execute(CreateGalleryDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function upload(UploadGalleryImagesRequest $request, ClientGallery $gallery, UploadGalleryImagesAction $action): RedirectResponse
    {
        $result = $action->execute($gallery, $request->file('images'));

        return back()
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function destroyImage(GalleryImage $image, DeleteGalleryImageAction $action): RedirectResponse
    {
        $result = $action->execute($image);

        return back()
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function toggleDownload(ClientGallery $gallery, ToggleGalleryDownloadAction $action): RedirectResponse
    {
        $result = $action->execute($gallery);

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function destroy(ClientGallery $gallery, DeleteGalleryAction $action): RedirectResponse
    {
        $result = $action->execute($gallery);

        return redirect()
            ->route('admin.galleries')
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
