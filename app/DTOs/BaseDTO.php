<?php

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

/**
 * @implements Arrayable<string, mixed>
 */
abstract readonly class BaseDTO implements Arrayable
{
    abstract public static function fromRequest(Request $request): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
