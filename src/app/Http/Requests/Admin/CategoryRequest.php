<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
            'slug' => filled($this->input('slug')) ? $this->input('slug') : null,
            'sort_order' => filled($this->input('sort_order')) ? (int) $this->input('sort_order') : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        // A category cannot be its own parent, nor move under one of its own children.
        $forbidden = $category
            ? [$category->id, ...$category->descendantIds()]
            : [];

        return [
            'name_fr' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                ...($forbidden === [] ? [] : [Rule::notIn($forbidden)]),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_id.not_in' => __('admin.categories.parent_cycle'),
        ];
    }
}
