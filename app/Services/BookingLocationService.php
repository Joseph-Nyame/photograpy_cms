<?php

namespace App\Services;

use App\Models\BookingLocation;
use Illuminate\Database\Eloquent\Builder;

class BookingLocationService
{
    /**
     * @return array<int, array{name: string, currency: string}>
     */
    public function activeOptions(): array
    {
        return $this->options(true);
    }

    /**
     * @return array<int, array{name: string, currency: string}>
     */
    public function options(bool $activeOnly = false): array
    {
        return $this->query($activeOnly)
            ->get()
            ->map(fn (BookingLocation $location): array => [
                'name' => $location->name,
                'currency' => $location->currency,
            ])
            ->all();
    }

    /**
     * @return array<int, array{uuid: string, name: string, currency: string, active: bool, sort: int}>
     */
    public function locations(): array
    {
        return BookingLocation::query()
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(fn (BookingLocation $location): array => [
                'uuid' => $location->uuid,
                'name' => $location->name,
                'currency' => $location->currency,
                'active' => $location->active,
                'sort' => $location->sort,
            ])
            ->all();
    }

    public function currencyFor(?string $name): string
    {
        if ($name === null || $name === '') {
            return 'GHS';
        }

        return BookingLocation::query()
            ->where('name', $name)
            ->value('currency') ?? 'GHS';
    }

    private function query(bool $activeOnly): Builder
    {
        $query = BookingLocation::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        return $query->orderBy('sort')->orderBy('name');
    }
}
