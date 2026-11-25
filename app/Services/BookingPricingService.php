<?php

namespace App\Services;

use App\Enums\BookingType;
use App\Models\Service;
use App\Support\Pricing;

class BookingPricingService
{
    public function __construct(
        private readonly PublicCatalogService $catalog,
        private readonly SettingsService $settings,
    ) {}

    public function quote(?Service $service, ?float $budget, string $bookingType): array
    {
        $total = match ($bookingType) {
            BookingType::Custom->value => $budget,
            default => $service === null ? null : $this->catalog->startingPrice($service->loadMissing('packages.items')),
        };

        $rate = (float) $this->settings->public()['booking']['deposit_rate'];

        return [
            'total' => $total,
            'deposit' => Pricing::deposit($total, $rate),
        ];
    }
}
