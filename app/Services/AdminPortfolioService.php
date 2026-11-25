<?php

namespace App\Services;

use App\Models\Category;
use App\Models\PortfolioImage;
use Illuminate\Support\Str;

class AdminPortfolioService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function images(): array
    {
        return PortfolioImage::query()
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (PortfolioImage $image): array => $this->present($image))
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, cover_image_id: int|null, sort: int, count: int}>
     */
    public function categoryOptions(): array
    {
        return Category::query()
            ->withCount('portfolioImages')
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'cover_image_id' => $category->cover_image_id,
                'sort' => $category->sort,
                'count' => $category->portfolio_images_count,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(PortfolioImage $image): array
    {
        return [
            'id' => $image->id,
            'uuid' => $image->uuid,
            'title' => Str::headline(pathinfo($image->path, PATHINFO_FILENAME)),
            'alt' => $image->alt,
            'category' => $image->category?->name ?? 'Uncategorised',
            'category_id' => $image->category_id,
            'featured' => $image->featured,
            'sort' => $image->sort,
            'cover' => $image->category?->cover_image_id === $image->id,
            'path' => $image->path,
        ];
    }
}
