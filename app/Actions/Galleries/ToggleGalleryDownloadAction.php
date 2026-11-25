<?php

namespace App\Actions\Galleries;

use App\Models\ClientGallery;
use App\Support\Result;

class ToggleGalleryDownloadAction
{
    public function execute(ClientGallery $gallery): Result
    {
        $gallery->update(['allow_download' => ! $gallery->allow_download]);

        return Result::ok(
            ['download' => $gallery->allow_download],
            $gallery->allow_download ? 'Downloads enabled.' : 'Downloads disabled.',
        );
    }
}
