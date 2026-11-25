<?php

namespace App\Http\Requests\Admin;

use App\Models\BookingLocation;
use App\Support\Currencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
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
        /** @var BookingLocation|null $location */
        $location = $this->route('location');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('booking_locations', 'name')->ignore($location?->id),
            ],
            'currency' => ['required', Rule::in(Currencies::codes())],
            'sort' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
