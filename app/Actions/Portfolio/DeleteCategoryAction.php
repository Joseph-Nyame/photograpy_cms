<?php

namespace App\Actions\Portfolio;

use App\Models\Category;
use App\Support\Result;

class DeleteCategoryAction
{
    public function execute(Category $category): Result
    {
        $category->delete();

        return Result::ok(null, 'Category deleted.');
    }
}
