<?php

namespace App\DTOs\Calendar;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class BlockDateDTO extends BaseDTO
{
    public function __construct(
        public string $date,
        public ?string $reason,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(
            date: $d['date'],
            reason: $d['reason'] ?? null,
        );
    }
}
