<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['nullable', 'string', 'max:120'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'location' => [
                'required',
                Rule::exists('booking_locations', 'name')->where('active', true),
            ],
            'message' => ['nullable', 'string', 'max:2000'],
            'booking_type' => ['nullable', Rule::in(['standard', 'custom'])],
            'custom_details' => [Rule::requiredIf($this->input('booking_type') === 'custom'), 'nullable', 'string', 'max:5000'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
