<?php

namespace App\Actions\ClientGallery;

use App\DTOs\ClientGallery\UnlockGalleryDTO;
use App\Models\ClientGallery;
use App\Support\Result;
use Illuminate\Support\Facades\Hash;

class VerifyGalleryPasscodeAction
{
    public function execute(ClientGallery $gallery, UnlockGalleryDTO $dto): Result
    {
        if (! Hash::check($dto->passcode, (string) $gallery->passcode_hash)) {
            return Result::fail('That passcode is not correct.');
        }

        return Result::ok(null, 'Gallery unlocked.');
    }
}
