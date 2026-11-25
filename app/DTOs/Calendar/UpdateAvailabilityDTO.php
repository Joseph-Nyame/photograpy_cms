<?php

namespace App\DTOs\Calendar;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class UpdateAvailabilityDTO extends BaseDTO
{
    public function __construct(
        public string $open,
        public string $close,
        public int $maxBookings,
        public int $bufferMinutes,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(
            open: $d['open'],
            close: $d['close'],
            maxBookings: (int) $d['maxBookings'],
            bufferMinutes: (int) $d['bufferMinutes'],
        );
    }
}
