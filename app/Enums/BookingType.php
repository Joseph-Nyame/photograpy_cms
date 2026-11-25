<?php

namespace App\Enums;

enum BookingType: string
{
    case Standard = 'standard';
    case Custom = 'custom';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
