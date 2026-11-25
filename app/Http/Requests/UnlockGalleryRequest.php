<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnlockGalleryRequest extends FormRequest
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
            'passcode' => ['required', 'string', 'max:60'],
        ];
    }
}
