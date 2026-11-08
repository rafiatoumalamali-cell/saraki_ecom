<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostStatus;
use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class BlogPostRequest extends FormRequest
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
            'excerpt_fr' => filled($this->input('excerpt_fr')) ? $this->input('excerpt_fr') : null,
            'excerpt_en' => filled($this->input('excerpt_en')) ? $this->input('excerpt_en') : null,
            'published_at' => filled($this->input('published_at')) ? $this->input('published_at') : null,
        ]);
    }

    /**
     * `status` is guarded on the model, so the form posts it and the controller
     * writes it with forceFill — the same rule the order status follows.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title_fr' => ['required', 'string', 'max:200'],
            'title_en' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220'],
            'excerpt_fr' => ['nullable', 'string', 'max:500'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'body_fr' => ['required', 'string', 'max:40000'],
            'body_en' => ['required', 'string', 'max:40000'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => ['nullable', 'date'],
            'cover' => ['nullable', ...BlogPost::COVER_RULES],
        ];
    }

    /**
     * Publishing without a date would hide the article from every "latest first"
     * listing, so the moment it goes live is stamped here.
     */
    public function publishingAt(): ?Carbon
    {
        if ($this->input('status') !== PostStatus::Published->value) {
            return null;
        }

        return filled($this->input('published_at'))
            ? Carbon::parse((string) $this->input('published_at'))->startOfDay()
            : now();
    }
}
