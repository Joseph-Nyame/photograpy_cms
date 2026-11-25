<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
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
            'open' => ['required', 'date_format:H:i'],
            'close' => ['required', 'date_format:H:i', 'after:open'],
            'maxBookings' => ['required', 'integer', 'min:1', 'max:50'],
            'bufferMinutes' => ['required', 'integer', 'min:0', 'max:480'],
        ];
    }
}
