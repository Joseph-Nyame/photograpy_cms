<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Support\Result;

class DeclineBookingAction
{
    public function execute(Booking $booking, ?string $reason = null): Result
    {
        $booking->update([
            'status' => BookingStatus::Declined,
            'decline_reason' => $reason,
        ]);

        return Result::ok($booking->refresh(), 'Booking declined.');
    }
}
