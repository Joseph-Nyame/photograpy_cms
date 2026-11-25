<?php

namespace App\Actions\Locations;

use App\DTOs\Locations\SaveLocationDTO;
use App\Models\BookingLocation;
use App\Support\Result;

class CreateLocationAction
{
    public function execute(SaveLocationDTO $dto): Result
    {
        $location = BookingLocation::create([
            'name' => $dto->name,
            'currency' => $dto->currency,
            'active' => true,
            'sort' => $dto->sort,
        ]);

        return Result::ok($location, 'Location added.');
    }
}
