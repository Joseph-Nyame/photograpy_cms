<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Service;
use App\Support\Pricing;

class PublicCatalogService
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function services(): array
    {
        $depositRate = (float) $this->settings->public()['booking']['deposit_rate'];

        return Service::query()
            ->with('packages.items')
            ->where('active', true)
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(function (Service $service) use ($depositRate): array {
                $price = $this->startingPrice($service);

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'summary' => $this->summary($service->description),
                    'includes' => $this->includes($service->description),
                    'duration' => $service->duration,
                    'price' => $price,
                    'deposit' => Pricing::deposit($price, $depositRate),
                    'currency' => $service->currency,
                    'sort' => $service->sort,
                    'packages' => $this->presentPackages($service),
                ];
            })
            ->all();
    }

    public function startingPrice(Service $service): ?float
    {
        $values = $service->packages
            ->flatMap(fn ($package) => $package->items)
            ->pluck('value')
            ->filter(fn ($value): bool => $value !== null)
            ->map(fn ($value): float => (float) $value);

        if ($values->isNotEmpty()) {
            return $values->min();
        }

        return $service->price_from !== null ? (float) $service->price_from : null;
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function presentPackages(Service $service): array
    {
        $packages = $this->packages($service);
        $hasItems = collect($packages)->sum(fn (array $package): int => count($package['items'])) > 0;

        return $hasItems ? $packages : $this->fallbackPackages($service);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackPackages(Service $service): array
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/', trim((string) $service->description)) ?: []),
            fn (string $line): bool => $line !== '',
        ));

        array_shift($lines);

        $items = [[
            'label' => 'Session',
            'value' => $service->price_from !== null ? (float) $service->price_from : null,
        ]];

        foreach (array_slice($lines, 0, 5) as $line) {
            $items[] = ['label' => $line, 'value' => null];
        }

        return [[
            'name' => 'Standard',
            'items' => $items,
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categories(): array
    {
        return Category::query()
            ->withCount('portfolioImages')
            ->with('coverImage')
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => $this->categoryData($category))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function category(Category $category): array
    {
        $category->loadCount('portfolioImages')->load('coverImage');

        return $this->categoryData($category);
    }

    /**
     * @return array<string, mixed>
     */
    public function portfolioImages(Category $category, int $page = 1, int $perPage = 6): array
    {
        $page = max(1, $page);
        $query = $category->portfolioImages()->orderBy('sort')->orderBy('id');
        $total = (int) $query->count();
        $images = $query->forPage($page, $perPage)
            ->get()
            ->map(fn ($image): array => [
                'id' => $image->id,
                'path' => $image->path,
                'alt' => $image->alt,
                'featured' => $image->featured,
                'sort' => $image->sort,
            ])
            ->all();
        $hasMore = $page * $perPage < $total;

        return [
            'images' => $images,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'hasMore' => $hasMore,
                'nextPage' => $hasMore ? $page + 1 : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryData(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'count' => $category->portfolio_images_count,
            'sort' => $category->sort,
            'cover' => $this->coverData($category),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function coverData(Category $category): ?array
    {
        $cover = $category->coverImage;

        if ($cover === null) {
            return null;
        }

        return [
            'id' => $cover->id,
            'path' => $cover->path,
            'alt' => $cover->alt,
        ];
    }

    private function summary(?string $description): string
    {
        return $this->lines($description)[0] ?? '';
    }

    /**
     * @return array<int, string>
     */
    private function includes(?string $description): array
    {
        return array_slice($this->lines($description), 1);
    }

    /**
     * @return array<int, string>
     */
    private function lines(?string $description): array
    {
        $lines = preg_split('/\R/', trim((string) $description)) ?: [];

        return array_values(array_filter(
            array_map('trim', $lines),
            fn (string $line): bool => $line !== '',
        ));
    }
}
