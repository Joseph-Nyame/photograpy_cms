<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
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
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:160'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'allow_download' => ['boolean'],
            'passcode' => ['nullable', 'string', 'min:4', 'max:60'],
        ];
    }
}
