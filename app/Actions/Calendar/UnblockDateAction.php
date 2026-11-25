<?php

namespace App\Actions\Calendar;

use App\Models\BlockedDate;
use App\Support\Result;

class UnblockDateAction
{
    public function execute(BlockedDate $blockedDate): Result
    {
        $blockedDate->delete();

        return Result::ok(null, 'Date re-opened.');
    }
}
