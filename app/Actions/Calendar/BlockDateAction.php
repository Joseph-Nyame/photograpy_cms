<?php

namespace App\Actions\Calendar;

use App\DTOs\Calendar\BlockDateDTO;
use App\Models\BlockedDate;
use App\Support\Result;

class BlockDateAction
{
    public function execute(BlockDateDTO $dto): Result
    {
        BlockedDate::query()->updateOrCreate(
            ['date' => $dto->date],
            ['reason' => $dto->reason],
        );

        return Result::ok(null, 'Date blocked.');
    }
}
