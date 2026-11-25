<?php

namespace App\DTOs\Services;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class SaveServiceDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public float $price,
        public string $currency,
        public ?string $duration,
        public int $sort,
        public ?string $desc,
        public array $packages = [],
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array{name: string, price: numeric-string|int|float, currency?: string|null, duration?: string|null, sort?: int|null, desc?: string|null, packages?: array<int, array{name: string, items?: array<int, array{label: string, value?: numeric-string|int|float|null}>}>} $data */
        $data = $request->validated();

        return new self(
            name: $data['name'],
            price: (float) $data['price'],
            currency: $data['currency'] ?? 'GHS',
            duration: $data['duration'] ?? null,
            sort: (int) ($data['sort'] ?? 0),
            desc: $data['desc'] ?? null,
            packages: $data['packages'] ?? [],
        );
    }
}
