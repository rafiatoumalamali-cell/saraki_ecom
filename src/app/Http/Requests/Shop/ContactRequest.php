<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Anyone may write to the atelier, signed in or not.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')) ?: null,
            'subject' => trim((string) $this->input('subject')) ?: null,
        ]);
    }

    /**
     * `website` is the honeypot: it is invisible to people and filled by scripts.
     * It is validated as free text so a bot gets a normal-looking success, and the
     * controller drops the message without storing it.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('shop.contact.fields.name'),
            'email' => __('shop.contact.fields.email'),
            'phone' => __('shop.contact.fields.phone'),
            'subject' => __('shop.contact.fields.subject'),
            'message' => __('shop.contact.fields.message'),
        ];
    }

    /**
     * A bot filled the hidden field: the submission is silently discarded.
     */
    public function isSpam(): bool
    {
        return filled($this->input('website'));
    }
}
