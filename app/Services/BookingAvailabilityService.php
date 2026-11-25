<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\CarbonImmutable;

class BookingAvailabilityService
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Upcoming dates that still have room, shaped for the public booking page.
     *
     * @return array<int, array{date: string, day: string, month: string, note: string, status: string, tone: string}>
     */
    public function openDates(int $limit = 4, int $withinDays = 90): array
    {
        $booking = $this->settings->public()['booking'];
        $maxPerDay = max(1, (int) $booking['max_bookings_per_day']);

        $blocked = BlockedDate::query()
            ->whereDate('date', '>=', now()->toDateString())
            ->pluck('date')
            ->map(fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->flip()
            ->all();

        $taken = Booking::query()
            ->where('event_date', '>=', now()->toDateString())
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->selectRaw('event_date, count(*) as total')
            ->groupBy('event_date')
            ->get()
            ->mapWithKeys(fn ($row): array => [
                CarbonImmutable::parse($row->event_date)->toDateString() => (int) $row->total,
            ])
            ->all();

        $dates = [];
        $cursor = CarbonImmutable::today()->addDay();
        $deadline = CarbonImmutable::today()->addDays($withinDays);

        while ($cursor->lessThanOrEqualTo($deadline) && count($dates) < $limit) {
            $key = $cursor->toDateString();

            if (! isset($blocked[$key])) {
                $remaining = $maxPerDay - (int) ($taken[$key] ?? 0);

                if ($remaining > 0) {
                    $dates[] = [
                        'date' => $key,
                        'day' => $cursor->format('d'),
                        'month' => $cursor->format('M Y'),
                        'note' => 'Open · Accra',
                        'status' => $remaining > 1 ? 'Open' : '1 left',
                        'tone' => $remaining > 1 ? 'ok' : 'warn',
                    ];
                }
            }

            $cursor = $cursor->addDay();
        }

        return $dates;
    }

    public function publicMonth(?int $year = null, ?int $month = null, int $withinDays = 90): array
    {
        $calendar = $this->month($year, $month);
        $today = CarbonImmutable::today();
        $deadline = $today->addDays($withinDays);

        $calendar['cells'] = array_map(function (array $cell) use ($today, $deadline): array {
            if ($cell['blank'] ?? false) {
                return $cell;
            }

            $date = CarbonImmutable::parse($cell['date']);
            $status = $date->lessThan($today) || $date->greaterThan($deadline)
                ? 'unavailable'
                : $cell['status'];

            $cell['status'] = $status;
            $cell['today'] = $date->isToday();
            $cell['selectable'] = in_array($status, ['open', 'limited'], true);

            return $cell;
        }, $calendar['cells']);

        $anchor = CarbonImmutable::create($calendar['year'], $calendar['month'], 1);
        $calendar['canPrev'] = $anchor->startOfMonth()->greaterThan($today->startOfMonth());
        $calendar['canNext'] = $anchor->startOfMonth()->lessThan($deadline->startOfMonth());

        return $calendar;
    }

    /**
     * Month grid of availability for the admin calendar and dashboard.
     *
     * @return array{label: string, year: int, month: int, prev: array{year: int, month: int}, next: array{year: int, month: int}, cells: array<int, array<string, mixed>>}
     */
    public function month(?int $year = null, ?int $month = null): array
    {
        $anchor = CarbonImmutable::create($year ?? now()->year, $month ?? now()->month, 1);
        $maxPerDay = max(1, (int) $this->settings->public()['booking']['max_bookings_per_day']);

        $blocked = BlockedDate::query()
            ->whereBetween('date', [$anchor->startOfMonth(), $anchor->endOfMonth()])
            ->get()
            ->mapWithKeys(fn (BlockedDate $row): array => [
                CarbonImmutable::parse($row->date)->toDateString() => $row->reason ?: 'Studio closed',
            ])
            ->all();

        $taken = Booking::query()
            ->whereBetween('event_date', [$anchor->startOfMonth(), $anchor->endOfMonth()])
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->selectRaw('event_date, count(*) as total')
            ->groupBy('event_date')
            ->get()
            ->mapWithKeys(fn ($row): array => [
                CarbonImmutable::parse($row->event_date)->toDateString() => (int) $row->total,
            ])
            ->all();

        $cells = [];
        $leading = (int) $anchor->dayOfWeek;

        for ($i = 0; $i < $leading; $i++) {
            $cells[] = ['blank' => true, 'key' => 'b'.$i];
        }

        $days = (int) $anchor->daysInMonth;

        for ($day = 1; $day <= $days; $day++) {
            $date = $anchor->day($day);
            $key = $date->toDateString();
            $takenCount = (int) ($taken[$key] ?? 0);

            $status = match (true) {
                isset($blocked[$key]) => 'blocked',
                $takenCount >= $maxPerDay => 'booked',
                $takenCount > 0 => 'limited',
                default => 'open',
            };

            $cells[] = [
                'day' => $day,
                'status' => $status,
                'key' => 'd'.$day,
                'date' => $key,
                'reason' => $blocked[$key] ?? null,
            ];
        }

        $prev = $anchor->subMonth();
        $next = $anchor->addMonth();

        return [
            'label' => $anchor->format('F Y'),
            'year' => $anchor->year,
            'month' => $anchor->month,
            'prev' => ['year' => $prev->year, 'month' => $prev->month],
            'next' => ['year' => $next->year, 'month' => $next->month],
            'cells' => $cells,
        ];
    }
}
