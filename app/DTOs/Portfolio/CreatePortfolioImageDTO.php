<?php

namespace App\DTOs\Portfolio;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class CreatePortfolioImageDTO extends BaseDTO
{
    public function __construct(
        public string $alt,
        public int $categoryId,
        public UploadedFile $image,
        public bool $featured,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{alt: string, category_id: int|string, image: UploadedFile, featured?: bool} $data */
        $data = $request->validated();

        return new self(
            alt: $data['alt'],
            categoryId: (int) $data['category_id'],
            image: $data['image'],
            featured: (bool) ($data['featured'] ?? false),
        );
    }
}
