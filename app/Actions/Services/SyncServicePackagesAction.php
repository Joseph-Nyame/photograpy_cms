<?php

namespace App\Actions\Services;

use App\Models\Service;

class SyncServicePackagesAction
{
    public function execute(Service $service, array $packages): void
    {
        $service->packages()->delete();

        foreach (array_values($packages) as $sort => $package) {
            $model = $service->packages()->create([
                'name' => $package['name'],
                'sort' => $sort,
            ]);

            foreach (array_values($package['items'] ?? []) as $itemSort => $item) {
                $model->items()->create([
                    'label' => $item['label'],
                    'value' => isset($item['value']) && $item['value'] !== '' ? (float) $item['value'] : null,
                    'sort' => $itemSort,
                ]);
            }
        }
    }
}
