<?php

namespace App\Http\Requests\Admin;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
            'client' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', Rule::exists('booking_locations', 'name')],
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
