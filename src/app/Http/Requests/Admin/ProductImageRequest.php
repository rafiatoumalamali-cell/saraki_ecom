<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductImage;
use Illuminate\Foundation\Http\FormRequest;

class ProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:12'],
            'images.*' => ProductImage::UPLOAD_RULES,
            'alt_fr' => ['nullable', 'string', 'max:255'],
            'alt_en' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:120'],
        ];
    }
}
