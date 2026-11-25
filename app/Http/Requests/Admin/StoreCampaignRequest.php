<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'string', 'in:all,subscribed,with_bookings'],
        ];
    }
}
