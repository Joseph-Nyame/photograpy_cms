<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\CreateBookingAction;
use App\DTOs\Bookings\CreateBookingDTO;
use App\Http\Requests\Bookings\BookingCalendarRequest;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Services\BookingAvailabilityService;
use App\Services\BookingLocationService;
use App\Services\PublicCatalogService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function create(
        BookingCalendarRequest $request,
        PublicCatalogService $catalog,
        BookingAvailabilityService $availability,
        BookingLocationService $locations,
    ): Response {
        return Inertia::render('Booking', [
            'services' => $catalog->services(),
            'locations' => $locations->activeOptions(),
            'calendar' => $availability->publicMonth(
                $request->integer('year') ?: null,
                $request->integer('month') ?: null,
            ),
        ]);
    }

    public function store(CreateBookingRequest $request, CreateBookingAction $action): RedirectResponse
    {
        $result = $action->execute(CreateBookingDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
