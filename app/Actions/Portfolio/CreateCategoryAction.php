<?php

namespace App\Actions\Portfolio;

use App\DTOs\Portfolio\SaveCategoryDTO;
use App\Models\Category;
use App\Support\Result;

class CreateCategoryAction
{
    public function execute(SaveCategoryDTO $dto): Result
    {
        $sort = $dto->sort > 0 ? $dto->sort : (int) Category::query()->max('sort') + 1;

        $category = Category::create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'sort' => $sort,
        ]);

        return Result::ok($category, 'Category added.');
    }
}
