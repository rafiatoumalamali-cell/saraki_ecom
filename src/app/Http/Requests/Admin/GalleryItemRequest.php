<?php

namespace App\Http\Requests\Admin;

use App\Enums\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GalleryItemRequest extends FormRequest
{
    /**
     * Reached only through the `auth` + `admin` middleware group.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_order' => filled($this->input('sort_order')) ? (int) $this->input('sort_order') : 0,
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title_fr' => ['required', 'string', 'max:180'],
            'title_en' => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::enum(GalleryCategory::class)],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
            'is_published' => ['boolean'],
            // The photo is the content of the record, so it is required when the
            // item is created and optional when it is only being edited.
            'image' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                ...GalleryItem::UPLOAD_RULES,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.enum' => __('admin.gallery.category_invalid'),
        ];
    }
}
