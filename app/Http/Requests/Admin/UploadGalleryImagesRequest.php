<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadGalleryImagesRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1', 'max:50'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ];
    }
}
