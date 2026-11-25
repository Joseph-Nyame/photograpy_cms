<?php

namespace App\Actions\Galleries;

use App\Models\ClientGallery;
use App\Support\Result;
use Illuminate\Http\UploadedFile;

class UploadGalleryImagesAction
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function execute(ClientGallery $gallery, array $files): Result
    {
        $sort = (int) $gallery->images()->max('sort');

        foreach ($files as $file) {
            $path = $file->store("galleries/{$gallery->id}", 'public');

            $gallery->images()->create([
                'original_path' => $path,
                'preview_path' => $path,
                'thumb_path' => $path,
                'sort' => ++$sort,
            ]);
        }

        return Result::ok(null, count($files).' image(s) uploaded.');
    }
}
