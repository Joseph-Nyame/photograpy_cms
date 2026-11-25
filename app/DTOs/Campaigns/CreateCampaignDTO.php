<?php

namespace App\DTOs\Campaigns;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CreateCampaignDTO extends BaseDTO
{
    public function __construct(
        public string $subject,
        public string $body,
        public string $audience,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(
            subject: $d['subject'],
            body: $d['body'],
            audience: $d['audience'],
        );
    }
}
