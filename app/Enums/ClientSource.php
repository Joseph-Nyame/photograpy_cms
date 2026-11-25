<?php

namespace App\Enums;

enum ClientSource: string
{
    case Booking = 'booking';
    case Contact = 'contact';
    case Manual = 'manual';
}
