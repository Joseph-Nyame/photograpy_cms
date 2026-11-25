<?php

namespace App\Support;

final class Pricing
{
    public static function deposit(?float $total, float $ratePercent): ?float
    {
        if ($total === null) {
            return null;
        }

        return round($total * ($ratePercent / 100), 2);
    }
}
