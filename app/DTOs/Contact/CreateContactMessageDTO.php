<?php

namespace App\DTOs\Contact;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CreateContactMessageDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public string $message,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{name: string, email: string, phone?: string|null, message: string} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            message: $data['message'],
        );
    }
}
