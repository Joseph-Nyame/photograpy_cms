<?php

namespace App\Services;

use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\CarbonImmutable;

class AdminCalendarService
{
    public function __construct(
        private readonly BookingAvailabilityService $calendar,
        private readonly AdminBookingService $bookings,
    ) {}

    /**
     * @return array{label: string, year: int, month: int, prev: array{year: int, month: int}, next: array{year: int, month: int}, cells: array<int, array<string, mixed>>}
     */
    public function month(?int $year = null, ?int $month = null): array
    {
        return $this->calendar->month($year, $month);
    }

    /**
     * Bookings for a month keyed by day of month.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function bookingsByDay(int $year, int $month): array
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfMonth();

        return Booking::query()
            ->with(['client', 'service'])
            ->whereBetween('event_date', [$start, $start->endOfMonth()])
            ->orderBy('event_date')
            ->get()
            ->groupBy(fn (Booking $booking): int => CarbonImmutable::parse($booking->event_date)->day)
            ->map(fn ($group): array => $group
                ->map(fn (Booking $booking): array => $this->bookings->present($booking))
                ->values()
                ->all())
            ->all();
    }

    /**
     * @return array<int, array{id: int, date: string, iso: string, reason: string}>
     */
    public function blockedDates(): array
    {
        return BlockedDate::query()
            ->orderBy('date')
            ->get()
            ->map(fn (BlockedDate $blocked): array => [
                'id' => $blocked->id,
                'uuid' => $blocked->uuid,
                'date' => $blocked->date->format('d M Y'),
                'iso' => $blocked->date->toDateString(),
                'reason' => $blocked->reason ?: 'Studio closed',
            ])
            ->all();
    }
}
