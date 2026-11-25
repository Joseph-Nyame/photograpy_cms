<?php

namespace App\Services;

use App\Models\Service;

class AdminCatalogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function services(): array
    {
        return Service::query()
            ->with('packages.items')
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service): array => $this->present($service))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Service $service): array
    {
        return [
            'id' => $service->id,
            'uuid' => $service->uuid,
            'name' => $service->name,
            'slug' => $service->slug,
            'price' => $service->price_from !== null ? (float) $service->price_from : 0,
            'currency' => $service->currency,
            'duration' => $service->duration ?? '',
            'sort' => $service->sort,
            'active' => $service->active,
            'desc' => $this->summary($service->description),
            'description' => $service->description ?? '',
            'packages' => $this->packages($service),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function packages(Service $service): array
    {
        return $service->packages
            ->sortBy('sort')
            ->map(fn ($package): array => [
                'name' => $package->name,
                'items' => $package->items
                    ->sortBy('sort')
                    ->map(fn ($item): array => [
                        'label' => $item->label,
                        'value' => $item->value !== null ? (float) $item->value : null,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    public function summary(?string $description): string
    {
        $lines = preg_split('/\R/', trim((string) $description)) ?: [];

        return trim($lines[0] ?? '');
    }

    public function includes(?string $description): string
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/', trim((string) $description)) ?: []),
            fn (string $line): bool => $line !== '',
        ));

        return implode("\n", array_slice($lines, 1));
    }
}
