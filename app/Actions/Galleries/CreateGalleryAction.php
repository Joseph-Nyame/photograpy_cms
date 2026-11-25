<?php

namespace App\Actions\Galleries;

use App\DTOs\Galleries\CreateGalleryDTO;
use App\Enums\GalleryStatus;
use App\Models\ClientGallery;
use App\Services\AdminGalleryService;
use App\Support\Result;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateGalleryAction
{
    public function __construct(private readonly AdminGalleryService $galleries) {}

    public function execute(CreateGalleryDTO $dto): Result
    {
        $gallery = ClientGallery::create([
            'client_id' => $dto->clientId,
            'title' => $dto->title,
            'token' => $this->token(),
            'passcode_hash' => $dto->passcode ? Hash::make($dto->passcode) : null,
            'expires_at' => $dto->expiresAt,
            'allow_download' => $dto->allowDownload,
            'status' => GalleryStatus::Active,
        ]);

        $gallery->load('client')->loadCount('images');

        return Result::ok($this->galleries->present($gallery), 'Gallery created.');
    }

    private function token(): string
    {
        do {
            $token = Str::lower(Str::random(16));
        } while (ClientGallery::query()->where('token', $token)->exists());

        return $token;
    }
}
