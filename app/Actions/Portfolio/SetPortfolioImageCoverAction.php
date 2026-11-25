<?php

namespace App\Actions\Portfolio;

use App\Models\PortfolioImage;
use App\Support\Result;

class SetPortfolioImageCoverAction
{
    public function execute(PortfolioImage $image): Result
    {
        $category = $image->category;

        if ($category === null) {
            return Result::fail('Image has no category.');
        }

        $category->update(['cover_image_id' => $image->id]);

        return Result::ok(['id' => $image->id], 'Gallery card cover updated.');
    }
}
