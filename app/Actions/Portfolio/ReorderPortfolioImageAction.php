<?php

namespace App\Actions\Portfolio;

use App\Models\PortfolioImage;
use App\Support\Result;

class ReorderPortfolioImageAction
{
    public function execute(PortfolioImage $image, int $position): Result
    {
        $images = PortfolioImage::query()
            ->where('category_id', $image->category_id)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->all();

        $current = array_search($image->id, array_map(fn (PortfolioImage $item): int => $item->id, $images), true);

        if ($current === false) {
            return Result::fail('Image does not belong to this category.');
        }

        $target = max(0, min(count($images) - 1, $position - 1));
        $moved = array_splice($images, $current, 1)[0];
        array_splice($images, $target, 0, [$moved]);

        foreach ($images as $index => $item) {
            $item->update(['sort' => $index + 1]);
        }

        return Result::ok(['position' => $target + 1], 'Image order updated.');
    }
}
