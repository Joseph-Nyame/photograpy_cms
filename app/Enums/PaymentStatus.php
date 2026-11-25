<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case PartPaid = 'part_paid';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PartPaid => 'Part payment',
            default => ucfirst($this->value),
        };
    }
}
