<?php

namespace App\Actions\Galleries;

use App\Models\GalleryImage;
use App\Support\Result;
use Illuminate\Support\Facades\Storage;

class DeleteGalleryImageAction
{
    public function execute(GalleryImage $image): Result
    {
        Storage::disk('public')->delete(array_filter([
            $image->original_path,
            $image->preview_path,
            $image->thumb_path,
        ]));

        $image->delete();

        return Result::ok(null, 'Image removed.');
    }
}
