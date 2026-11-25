<?php

namespace App\Http\Requests\Admin;

use App\Support\Currencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', Rule::in(Currencies::codes())],
            'duration' => ['nullable', 'string', 'max:60'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'desc' => ['nullable', 'string', 'max:500'],
            'packages' => ['nullable', 'array', 'max:20'],
            'packages.*.name' => ['required', 'string', 'max:120'],
            'packages.*.items' => ['nullable', 'array', 'max:30'],
            'packages.*.items.*.label' => ['required', 'string', 'max:120'],
            'packages.*.items.*.value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
