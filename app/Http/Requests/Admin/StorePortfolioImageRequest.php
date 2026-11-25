<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioImageRequest extends FormRequest
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
            'alt' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:20480'],
            'featured' => ['boolean'],
        ];
    }
}
