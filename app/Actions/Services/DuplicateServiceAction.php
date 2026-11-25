<?php

namespace App\Actions\Services;

use App\Models\Service;
use App\Services\AdminCatalogService;
use App\Support\Result;
use App\Support\ServiceSlug;

class DuplicateServiceAction
{
    public function __construct(
        private readonly AdminCatalogService $catalog,
        private readonly ServiceSlug $slug,
    ) {}

    public function execute(Service $service): Result
    {
        $name = $service->name.' (copy)';

        $copy = Service::create([
            'name' => $name,
            'slug' => $this->slug->unique($name),
            'description' => $service->description,
            'price_from' => $service->price_from,
            'currency' => $service->currency,
            'duration' => $service->duration,
            'active' => false,
            'sort' => ((int) Service::query()->max('sort')) + 1,
        ]);

        $service->loadMissing('packages.items');

        foreach ($service->packages as $package) {
            $packageCopy = $copy->packages()->create([
                'name' => $package->name,
                'sort' => $package->sort,
            ]);

            foreach ($package->items as $item) {
                $packageCopy->items()->create([
                    'label' => $item->label,
                    'value' => $item->value,
                    'sort' => $item->sort,
                ]);
            }
        }

        $copy->load('packages.items');

        return Result::ok($this->catalog->present($copy), 'Service duplicated as a paused copy. Edit it below.');
    }
}
