<?php

namespace App\DTOs\Clients;

use App\DTOs\BaseDTO;
use App\Enums\ClientSource;
use Illuminate\Http\Request;

final readonly class UpsertClientDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public ClientSource $source,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return self::forSource($request, ClientSource::Manual);
    }

    public static function forSource(Request $request, ClientSource $source): static
    {
        /** @var array{name: string, email: string, phone?: string|null} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            source: $source,
        );
    }
}
