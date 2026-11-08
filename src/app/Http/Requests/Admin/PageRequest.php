<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Blank means "build it from the French title", like products and posts.
            'slug' => ['nullable', 'string', 'max:160'],
            'title_fr' => ['required', 'string', 'max:180'],
            'title_en' => ['required', 'string', 'max:180'],
            'body_fr' => ['required', 'string', 'max:40000'],
            'body_en' => ['required', 'string', 'max:40000'],
            'is_published' => ['boolean'],
        ];
    }
}
