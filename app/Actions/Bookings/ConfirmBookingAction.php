<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Support\Result;

class ConfirmBookingAction
{
    public function execute(Booking $booking): Result
    {
        $booking->update([
            'status' => BookingStatus::Confirmed,
            'decline_reason' => null,
        ]);

        return Result::ok($booking->refresh(), 'Booking confirmed.');
    }
}
