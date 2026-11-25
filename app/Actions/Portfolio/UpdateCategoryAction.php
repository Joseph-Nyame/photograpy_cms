<?php

namespace App\Actions\Portfolio;

use App\DTOs\Portfolio\SaveCategoryDTO;
use App\Models\Category;
use App\Support\Result;

class UpdateCategoryAction
{
    public function execute(Category $category, SaveCategoryDTO $dto): Result
    {
        $category->update([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'sort' => $dto->sort,
        ]);

        return Result::ok($category->refresh(), 'Category updated.');
    }
}
