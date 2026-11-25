<?php

namespace App\Enums;

enum BookingActivityAction: string
{
    case Created = 'created';
    case StatusChanged = 'status_changed';
    case PaymentUpdated = 'payment_updated';
    case NoteAdded = 'note_added';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Request received',
            self::StatusChanged => 'Status changed',
            self::PaymentUpdated => 'Payment updated',
            self::NoteAdded => 'Note added',
        };
    }
}
