<?php

namespace App\Actions\Calendar;

use App\DTOs\Calendar\UpdateAvailabilityDTO;
use App\Services\SettingsService;
use App\Support\Result;

class UpdateAvailabilityAction
{
    public function __construct(private readonly SettingsService $settings) {}

    public function execute(UpdateAvailabilityDTO $dto): Result
    {
        $this->settings->store([
            'booking.open_time' => $dto->open,
            'booking.close_time' => $dto->close,
            'booking.max_bookings_per_day' => $dto->maxBookings,
            'booking.buffer_minutes' => $dto->bufferMinutes,
        ]);

        return Result::ok(null, 'Availability saved.');
    }
}
