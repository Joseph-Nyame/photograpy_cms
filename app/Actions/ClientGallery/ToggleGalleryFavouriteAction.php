<?php

namespace App\Actions\ClientGallery;

use App\Support\Result;

class ToggleGalleryFavouriteAction
{
    /**
     * @param  array<int, int>  $favourites
     */
    public function execute(int $imageId, array $favourites): Result
    {
        $favourited = ! in_array($imageId, $favourites, true);

        $favourites = $favourited
            ? [...$favourites, $imageId]
            : array_values(array_filter($favourites, fn (int $id): bool => $id !== $imageId));

        return Result::ok(
            ['favourites' => $favourites, 'favourited' => $favourited],
            $favourited ? 'Saved to favourites.' : 'Removed from favourites.',
        );
    }
}
