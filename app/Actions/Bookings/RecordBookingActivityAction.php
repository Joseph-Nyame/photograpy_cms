<?php

namespace App\Actions\Bookings;

use App\Enums\BookingActivityAction;
use App\Models\Booking;
use App\Models\BookingActivity;
use Illuminate\Support\Facades\Auth;

class RecordBookingActivityAction
{
    public function execute(
        Booking $booking,
        BookingActivityAction $action,
        ?string $from = null,
        ?string $to = null,
        ?string $note = null,
    ): BookingActivity {
        return $booking->activities()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'from_value' => $from,
            'to_value' => $to,
            'note' => $note,
        ]);
    }
}
