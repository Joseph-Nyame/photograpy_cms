<?php

namespace App\Http\Requests\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('status') && ! $this->filled('payment_status') && ! filled($this->input('note'))) {
                $validator->errors()->add('note', 'Add a note or choose a status/payment update.');
            }
        });
    }
}
