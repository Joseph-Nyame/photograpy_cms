<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{email: string, password: string, remember?: bool} $data */
        $data = $request->validated();

        return new self(
            email: $data['email'],
            password: $data['password'],
            remember: (bool) ($data['remember'] ?? false),
        );
    }
}
