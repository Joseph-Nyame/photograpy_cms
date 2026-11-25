<?php

namespace App\Actions\Portfolio;

use App\DTOs\Portfolio\CreatePortfolioImageDTO;
use App\Models\PortfolioImage;
use App\Support\Result;

class CreatePortfolioImageAction
{
    public function execute(CreatePortfolioImageDTO $dto): Result
    {
        $image = PortfolioImage::create([
            'category_id' => $dto->categoryId,
            'path' => $dto->image->store("portfolio/{$dto->categoryId}", 'public'),
            'alt' => $dto->alt,
            'featured' => $dto->featured,
            'sort' => (int) PortfolioImage::query()->where('category_id', $dto->categoryId)->max('sort') + 1,
        ]);

        return Result::ok($image, 'Image added to the portfolio.');
    }
}
