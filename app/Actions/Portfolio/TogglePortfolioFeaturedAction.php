<?php

namespace App\Actions\Portfolio;

use App\Models\PortfolioImage;
use App\Support\Result;

class TogglePortfolioFeaturedAction
{
    public function execute(PortfolioImage $image): Result
    {
        $image->update(['featured' => ! $image->featured]);

        return Result::ok(
            $image->refresh(),
            $image->featured ? 'Image featured.' : 'Image unfeatured.',
        );
    }
}
