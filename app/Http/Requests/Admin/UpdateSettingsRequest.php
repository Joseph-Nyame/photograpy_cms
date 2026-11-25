<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'whatsapp' => ['nullable', 'string', 'max:190'],
            'address' => ['nullable', 'string', 'max:190'],
            'hours' => ['nullable', 'string', 'max:190'],
            'instagram' => ['nullable', 'string', 'max:190'],
            'map' => ['nullable', 'string', 'max:255'],

            'maxBookings' => ['required', 'integer', 'min:1', 'max:50'],
            'bufferMinutes' => ['required', 'integer', 'min:0', 'max:480'],
            'open' => ['required', 'date_format:H:i'],
            'close' => ['required', 'date_format:H:i', 'after:open'],
            'expiry' => ['required', 'integer', 'min:1', 'max:365'],
            'deposit' => ['boolean'],
            'depositRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'autoConfirm' => ['boolean'],

            'notifyEmail' => ['nullable', 'string', 'email', 'max:190'],
            'notifyBooking' => ['boolean'],
            'notifyContact' => ['boolean'],
            'notifyDownload' => ['boolean'],

            'seoTitle' => ['nullable', 'string', 'max:190'],
            'seoDescription' => ['nullable', 'string', 'max:500'],
            'domain' => ['nullable', 'string', 'max:190'],
            'analytics' => ['nullable', 'string', 'max:60'],

            'mailProvider' => ['nullable', 'string', 'in:smtp,ahasend,resend,postmark'],

            'ahasendApiKey' => ['nullable', 'string', 'max:255'],
            'ahasendAccountId' => ['nullable', 'string', 'max:120'],
            'ahasendFrom' => ['nullable', 'email', 'max:190'],
            'ahasendActive' => ['boolean'],
        ];
    }
}
