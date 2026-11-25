<?php

namespace App\DTOs\Clients;

use App\DTOs\BaseDTO;
use App\Enums\ClientSource;
use Illuminate\Http\Request;

final readonly class CreateClientDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public ClientSource $source,
        public bool $subscribed,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{name: string, email: string, phone?: string|null, source?: string|null, subscribed?: bool} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            source: ClientSource::from($data['source'] ?? ClientSource::Manual->value),
            subscribed: (bool) ($data['subscribed'] ?? false),
        );
    }
}
