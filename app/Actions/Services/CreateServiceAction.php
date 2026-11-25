<?php

namespace App\Actions\Services;

use App\DTOs\Services\SaveServiceDTO;
use App\Models\Service;
use App\Support\Result;
use App\Support\ServiceSlug;

class CreateServiceAction
{
    public function __construct(
        private readonly SyncServicePackagesAction $packages,
        private readonly ServiceSlug $slug,
    ) {}

    public function execute(SaveServiceDTO $dto): Result
    {
        $service = Service::create([
            'name' => $dto->name,
            'slug' => $this->slug->unique($dto->name),
            'description' => $dto->desc,
            'price_from' => $dto->price,
            'currency' => $dto->currency,
            'duration' => $dto->duration,
            'active' => true,
            'sort' => $dto->sort,
        ]);

        $this->packages->execute($service, $dto->packages);

        return Result::ok($service, 'Service created.');
    }
}
