<?php

namespace App\Actions\Services;

use App\Models\Service;
use App\Support\Result;

class ToggleServiceActiveAction
{
    public function execute(Service $service): Result
    {
        $service->update(['active' => ! $service->active]);

        return Result::ok(
            $service->refresh(),
            $service->active ? 'Service activated.' : 'Service paused.',
        );
    }
}
