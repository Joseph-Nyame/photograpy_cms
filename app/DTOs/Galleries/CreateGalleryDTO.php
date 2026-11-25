<?php

namespace App\DTOs\Galleries;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CreateGalleryDTO extends BaseDTO
{
    public function __construct(
        public int $clientId,
        public string $title,
        public ?string $expiresAt,
        public bool $allowDownload,
        public ?string $passcode,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(
            clientId: (int) $d['client_id'],
            title: $d['title'],
            expiresAt: $d['expires_at'] ?? null,
            allowDownload: (bool) ($d['allow_download'] ?? false),
            passcode: $d['passcode'] ?? null,
        );
    }
}
