<?php

namespace App\Actions\Locations;

use App\DTOs\Locations\SaveLocationDTO;
use App\Models\BookingLocation;
use App\Support\Result;

class UpdateLocationAction
{
    public function execute(BookingLocation $location, SaveLocationDTO $dto): Result
    {
        $location->update([
            'name' => $dto->name,
            'currency' => $dto->currency,
            'sort' => $dto->sort,
        ]);

        return Result::ok($location->refresh(), 'Location updated.');
    }
}
