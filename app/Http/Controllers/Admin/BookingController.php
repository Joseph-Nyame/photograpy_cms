<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Bookings\ConfirmBookingAction;
use App\Actions\Bookings\CreateAdminBookingAction;
use App\Actions\Bookings\DeclineBookingAction;
use App\Actions\Bookings\RecordBookingActivityAction;
use App\Actions\Bookings\ReopenBookingAction;
use App\Actions\Bookings\UpdateBookingRecordAction;
use App\DTOs\Bookings\CreateAdminBookingDTO;
use App\DTOs\Bookings\UpdateBookingRecordDTO;
use App\Enums\BookingActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeclineBookingRequest;
use App\Http\Requests\Admin\StoreBookingRequest;
use App\Http\Requests\Admin\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\AdminBookingService;
use App\Services\BookingLocationService;
use App\Support\Result;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request, AdminBookingService $bookings, BookingLocationService $locations): Response
    {
        return Inertia::render('AdminBookings', [
            'bookings' => $bookings->bookings(),
            'services' => $bookings->serviceOptions(),
            'locations' => $locations->options(),
            'initialQuery' => $request->string('q')->toString(),
        ]);
    }

    public function store(StoreBookingRequest $request, CreateAdminBookingAction $action): RedirectResponse
    {
        $result = $action->execute(CreateAdminBookingDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function update(Booking $booking, UpdateBookingRequest $request, UpdateBookingRecordAction $action): RedirectResponse
    {
        return $this->flash($action->execute($booking, UpdateBookingRecordDTO::fromRequest($request)));
    }

    public function confirm(Booking $booking, ConfirmBookingAction $action, RecordBookingActivityAction $activities): RedirectResponse
    {
        $from = $booking->status->value;
        $result = $action->execute($booking);

        $activities->execute($booking, BookingActivityAction::StatusChanged, $from, $booking->status->value, 'Booking confirmed.');

        return $this->flash($result);
    }

    public function decline(Booking $booking, DeclineBookingRequest $request, DeclineBookingAction $action, RecordBookingActivityAction $activities): RedirectResponse
    {
        $from = $booking->status->value;
        $reason = $request->validated('reason');
        $result = $action->execute($booking, $reason);

        $activities->execute($booking, BookingActivityAction::StatusChanged, $from, $booking->status->value, $reason);

        return $this->flash($result);
    }

    public function reopen(Booking $booking, ReopenBookingAction $action, RecordBookingActivityAction $activities): RedirectResponse
    {
        $from = $booking->status->value;
        $result = $action->execute($booking);

        $activities->execute($booking, BookingActivityAction::StatusChanged, $from, $booking->status->value, 'Booking reopened.');

        return $this->flash($result);
    }

    private function flash(Result $result): RedirectResponse
    {
        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
