<?php

namespace App\Actions\Bookings;

use App\DTOs\Bookings\UpdateBookingRecordDTO;
use App\Enums\BookingActivityAction;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Support\Result;

class UpdateBookingRecordAction
{
    public function __construct(
        private readonly RecordBookingActivityAction $activities,
    ) {}

    public function execute(Booking $booking, UpdateBookingRecordDTO $dto): Result
    {
        $changes = [];
        $note = filled($dto->note) ? trim($dto->note) : null;
        $note = $note === '' ? null : $note;
        $noteRecorded = false;

        if ($dto->status !== null && $dto->status !== $booking->status->value) {
            $changes['status'] = BookingStatus::from($dto->status);

            $this->activities->execute(
                $booking,
                BookingActivityAction::StatusChanged,
                $booking->status->value,
                $dto->status,
                $note,
            );

            $noteRecorded = true;
        }

        if ($dto->paymentStatus !== null && $dto->paymentStatus !== $booking->payment_status->value) {
            $changes['payment_status'] = PaymentStatus::from($dto->paymentStatus);

            $this->activities->execute(
                $booking,
                BookingActivityAction::PaymentUpdated,
                $booking->payment_status->value,
                $dto->paymentStatus,
                $note,
            );

            $noteRecorded = true;
        }

        if ($note !== null && ! $noteRecorded) {
            $this->activities->execute($booking, BookingActivityAction::NoteAdded, note: $note);
        }

        if ($changes !== []) {
            $booking->update($changes);
        }

        return Result::ok($booking->refresh(), 'Booking record updated.');
    }
}
