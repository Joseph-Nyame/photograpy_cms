<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Calendar\BlockDateAction;
use App\Actions\Calendar\UnblockDateAction;
use App\Actions\Calendar\UpdateAvailabilityAction;
use App\DTOs\Calendar\BlockDateDTO;
use App\DTOs\Calendar\UpdateAvailabilityDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockDateRequest;
use App\Http\Requests\Admin\UpdateAvailabilityRequest;
use App\Models\BlockedDate;
use App\Services\AdminCalendarService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(Request $request, AdminCalendarService $calendar, SettingsService $settings): Response
    {
        $month = min(12, max(1, (int) $request->integer('month', now()->month)));
        $year = min(2100, max(2000, (int) $request->integer('year', now()->year)));

        $grid = $calendar->month($year, $month);
        $booking = $settings->public()['booking'];

        return Inertia::render('AdminCalendar', [
            'month' => $grid,
            'bookingsByDay' => $calendar->bookingsByDay($grid['year'], $grid['month']),
            'blockedDates' => $calendar->blockedDates(),
            'availability' => [
                'open' => $booking['open_time'],
                'close' => $booking['close_time'],
                'maxBookings' => $booking['max_bookings_per_day'],
                'bufferMinutes' => $booking['buffer_minutes'],
            ],
        ]);
    }

    public function block(BlockDateRequest $request, BlockDateAction $action): RedirectResponse
    {
        $result = $action->execute(BlockDateDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function unblock(BlockedDate $blockedDate, UnblockDateAction $action): RedirectResponse
    {
        $result = $action->execute($blockedDate);

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function availability(UpdateAvailabilityRequest $request, UpdateAvailabilityAction $action): RedirectResponse
    {
        $result = $action->execute(UpdateAvailabilityDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
