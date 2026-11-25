<?php

namespace App\DTOs\Emails;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class SendEmailDTO extends BaseDTO
{
    public function __construct(
        public int $clientId,
        public string $subject,
        public string $body,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(
            clientId: (int) $d['client_id'],
            subject: $d['subject'],
            body: $d['body'],
        );
    }
}
