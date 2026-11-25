<?php

namespace App\DTOs\ClientGallery;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class UnlockGalleryDTO extends BaseDTO
{
    public function __construct(
        public string $passcode,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(passcode: $d['passcode']);
    }
}
