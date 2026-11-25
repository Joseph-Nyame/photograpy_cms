<?php

namespace App\Actions\Locations;

use App\Models\BookingLocation;
use App\Support\Result;

class ToggleLocationActiveAction
{
    public function execute(BookingLocation $location): Result
    {
        $location->update(['active' => ! $location->active]);

        return Result::ok(
            $location->refresh(),
            $location->active ? 'Location activated.' : 'Location deactivated.',
        );
    }
}
