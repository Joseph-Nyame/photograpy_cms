<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class Result implements Arrayable, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public string $message = '',
        public array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function ok(mixed $data = null, string $message = '', array $meta = []): self
    {
        return new self(true, $data, $message, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function fail(string $message, mixed $data = null, array $meta = []): self
    {
        return new self(false, $data, $message, $meta);
    }

    /**
     * @return array{success: bool, data: mixed, message: string, meta: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'meta' => $this->meta,
        ];
    }

    /**
     * @return array{success: bool, data: mixed, message: string, meta: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
