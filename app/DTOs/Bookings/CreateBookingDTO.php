<?php

namespace App\DTOs\Bookings;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CreateBookingDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $service,
        public string $date,
        public ?string $location,
        public ?string $message,
        public string $bookingType = 'standard',
        public ?string $customDetails = null,
        public ?float $budget = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{name: string, email: string, phone?: string|null, service?: string|null, date: string, location: string, message?: string|null, booking_type?: string|null, custom_details?: string|null, budget?: numeric-string|int|float|null} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            service: $data['service'] ?? null,
            date: $data['date'],
            location: $data['location'] ?? null,
            message: $data['message'] ?? null,
            bookingType: $data['booking_type'] ?? 'standard',
            customDetails: $data['custom_details'] ?? null,
            budget: isset($data['budget']) ? (float) $data['budget'] : null,
        );
    }
}
