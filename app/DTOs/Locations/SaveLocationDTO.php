<?php

namespace App\DTOs\Locations;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class SaveLocationDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $currency,
        public int $sort,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{name: string, currency: string, sort?: int|null} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            currency: $data['currency'],
            sort: (int) ($data['sort'] ?? 0),
        );
    }
}
