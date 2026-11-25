<?php

namespace App\Actions\Bookings;

use App\Actions\Clients\UpsertClientAction;
use App\Actions\Notifications\NotifyAdminsAction;
use App\DTOs\Bookings\CreateBookingDTO;
use App\DTOs\Clients\UpsertClientDTO;
use App\Enums\BookingActivityAction;
use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\ClientSource;
use App\Models\Service;
use App\Services\BookingLocationService;
use App\Services\BookingPricingService;
use App\Support\Result;

class CreateBookingAction
{
    public function __construct(
        private readonly UpsertClientAction $upsertClient,
        private readonly NotifyAdminsAction $notifyAdmins,
        private readonly RecordBookingActivityAction $activities,
        private readonly BookingPricingService $pricing,
        private readonly BookingLocationService $locations,
    ) {}

    public function execute(CreateBookingDTO $dto): Result
    {
        $message = trim(implode("\n\n", array_filter([
            $dto->customDetails,
            $dto->message,
        ])));

        $service = null;

        if ($dto->bookingType !== BookingType::Custom->value && $dto->service !== null && $dto->service !== '' && $dto->service !== 'Not sure yet') {
            $service = Service::query()->where('name', $dto->service)->first();
        }

        $quote = $this->pricing->quote($service, $dto->budget, $dto->bookingType);

        $client = $this->upsertClient->execute(new UpsertClientDTO(
            name: $dto->name,
            email: $dto->email,
            phone: $dto->phone,
            source: ClientSource::Booking,
        ));

        $booking = $client->bookings()->create([
            'service_id' => $service?->id,
            'event_date' => $dto->date,
            'location' => $dto->location,
            'message' => $message !== '' ? $message : null,
            'status' => BookingStatus::Pending,
            'booking_type' => $dto->bookingType,
            'currency' => $this->locations->currencyFor($dto->location),
            'budget' => $dto->budget,
            'total_cost' => $quote['total'],
            'deposit_amount' => $quote['deposit'],
        ]);

        $this->activities->execute(
            $booking,
            BookingActivityAction::Created,
            note: $dto->bookingType === BookingType::Custom->value
                ? 'Custom request submitted: '.$dto->customDetails
                : 'Booking request received.',
        );

        $this->notifyAdmins->execute(
            title: 'New booking request',
            body: "{$client->name} requested ".($booking->event_date?->format('d M Y') ?? 'a session').'.',
            url: route('admin.bookings'),
        );

        return Result::ok($booking, 'Request received. We will confirm availability within one working day.');
    }
}
