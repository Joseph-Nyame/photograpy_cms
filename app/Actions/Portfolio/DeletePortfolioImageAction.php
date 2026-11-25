<?php

namespace App\Actions\Portfolio;

use App\Models\PortfolioImage;
use App\Support\Result;

class DeletePortfolioImageAction
{
    public function execute(PortfolioImage $image): Result
    {
        $image->delete();

        return Result::ok(['id' => $image->id], 'Image removed.');
    }
}
