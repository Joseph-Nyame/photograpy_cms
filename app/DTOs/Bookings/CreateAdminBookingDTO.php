<?php

namespace App\DTOs\Bookings;

use App\DTOs\BaseDTO;
use App\Enums\BookingStatus;
use Illuminate\Http\Request;

final readonly class CreateAdminBookingDTO extends BaseDTO
{
    public function __construct(
        public string $client,
        public string $email,
        public ?string $phone,
        public ?int $serviceId,
        public string $date,
        public ?string $startTime,
        public ?string $endTime,
        public ?string $location,
        public BookingStatus $status,
        public ?string $note,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{client: string, email: string, phone?: string|null, service_id?: int|null, date: string, start_time?: string|null, end_time?: string|null, location?: string|null, status?: string|null, note?: string|null} $data */
        $data = $request->validated();

        return new self(
            client: $data['client'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            serviceId: isset($data['service_id']) ? (int) $data['service_id'] : null,
            date: $data['date'],
            startTime: $data['start_time'] ?? null,
            endTime: $data['end_time'] ?? null,
            location: $data['location'] ?? null,
            status: BookingStatus::from($data['status'] ?? BookingStatus::Pending->value),
            note: $data['note'] ?? null,
        );
    }
}
