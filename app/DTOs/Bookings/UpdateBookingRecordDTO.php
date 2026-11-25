<?php

namespace App\DTOs\Bookings;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class UpdateBookingRecordDTO extends BaseDTO
{
    public function __construct(
        public ?string $status,
        public ?string $paymentStatus,
        public ?string $note,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{status?: string|null, payment_status?: string|null, note?: string|null} $data */
        $data = $request->validated();

        return new self(
            status: $data['status'] ?? null,
            paymentStatus: $data['payment_status'] ?? null,
            note: $data['note'] ?? null,
        );
    }
}
