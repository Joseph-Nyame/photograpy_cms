<?php

namespace App\Actions\Services;

use App\DTOs\Services\SaveServiceDTO;
use App\Models\Service;
use App\Services\AdminCatalogService;
use App\Support\Result;

class UpdateServiceAction
{
    public function __construct(
        private readonly AdminCatalogService $catalog,
        private readonly SyncServicePackagesAction $packages,
    ) {}

    public function execute(Service $service, SaveServiceDTO $dto): Result
    {
        $includes = $this->catalog->includes($service->description);
        $description = $dto->desc;

        if ($includes !== '' && $description !== null) {
            $description .= "\n".$includes;
        }

        $service->update([
            'name' => $dto->name,
            'description' => $description,
            'price_from' => $dto->price,
            'currency' => $dto->currency,
            'duration' => $dto->duration,
            'sort' => $dto->sort,
        ]);

        $this->packages->execute($service, $dto->packages);

        return Result::ok($service->refresh(), 'Service updated.');
    }
}
