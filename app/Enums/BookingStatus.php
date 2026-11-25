<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
