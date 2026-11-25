<?php

namespace App\Actions\Bookings;

use App\Actions\Clients\UpsertClientAction;
use App\DTOs\Bookings\CreateAdminBookingDTO;
use App\DTOs\Clients\UpsertClientDTO;
use App\Enums\BookingActivityAction;
use App\Enums\ClientSource;
use App\Services\BookingLocationService;
use App\Support\Result;

class CreateAdminBookingAction
{
    public function __construct(
        private readonly UpsertClientAction $upsertClient,
        private readonly RecordBookingActivityAction $activities,
        private readonly BookingLocationService $locations,
    ) {}

    public function execute(CreateAdminBookingDTO $dto): Result
    {
        $client = $this->upsertClient->execute(new UpsertClientDTO(
            name: $dto->client,
            email: $dto->email,
            phone: $dto->phone,
            source: ClientSource::Manual,
        ));

        $booking = $client->bookings()->create([
            'service_id' => $dto->serviceId,
            'event_date' => $dto->date,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
            'location' => $dto->location,
            'currency' => $this->locations->currencyFor($dto->location),
            'message' => $dto->note,
            'status' => $dto->status,
        ]);

        $this->activities->execute($booking, BookingActivityAction::Created, note: 'Booking created by admin.');

        return Result::ok($booking, 'Booking created.');
    }
}
