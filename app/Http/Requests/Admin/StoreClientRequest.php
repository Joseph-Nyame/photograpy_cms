<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClientSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:clients,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'source' => ['nullable', Rule::enum(ClientSource::class)],
            'subscribed' => ['boolean'],
        ];
    }
}
