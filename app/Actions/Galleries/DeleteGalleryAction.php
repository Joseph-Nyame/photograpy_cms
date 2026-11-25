<?php

namespace App\Actions\Galleries;

use App\Models\ClientGallery;
use App\Support\Result;
use Illuminate\Support\Facades\Storage;

class DeleteGalleryAction
{
    public function execute(ClientGallery $gallery): Result
    {
        Storage::disk('public')->deleteDirectory("galleries/{$gallery->id}");

        $gallery->delete();

        return Result::ok(null, 'Gallery deleted.');
    }
}
