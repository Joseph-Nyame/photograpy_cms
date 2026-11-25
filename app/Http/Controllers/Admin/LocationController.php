<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Locations\CreateLocationAction;
use App\Actions\Locations\ToggleLocationActiveAction;
use App\Actions\Locations\UpdateLocationAction;
use App\DTOs\Locations\SaveLocationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Models\BookingLocation;
use App\Services\BookingLocationService;
use App\Support\Currencies;
use App\Support\Result;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(BookingLocationService $locations): Response
    {
        return Inertia::render('AdminLocations', [
            'locations' => $locations->locations(),
            'currencies' => Currencies::options(),
        ]);
    }

    public function store(LocationRequest $request, CreateLocationAction $action): RedirectResponse
    {
        return $this->flash($action->execute(SaveLocationDTO::fromRequest($request)));
    }

    public function update(LocationRequest $request, BookingLocation $location, UpdateLocationAction $action): RedirectResponse
    {
        return $this->flash($action->execute($location, SaveLocationDTO::fromRequest($request)));
    }

    public function toggle(BookingLocation $location, ToggleLocationActiveAction $action): RedirectResponse
    {
        return $this->flash($action->execute($location));
    }

    private function flash(Result $result): RedirectResponse
    {
        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
