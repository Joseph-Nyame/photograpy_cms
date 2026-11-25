<?php

namespace App\DTOs\Portfolio;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class SaveCategoryDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public int $sort,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{name: string, slug: string, sort?: int|null} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            slug: $data['slug'],
            sort: (int) ($data['sort'] ?? 0),
        );
    }
}
