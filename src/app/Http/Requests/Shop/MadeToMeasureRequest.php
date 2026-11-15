<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A sur-mesure enquiry: the client picked a model and tells the atelier which
 * fabric they want — SARAKI's own or the fabric they will bring.
 */
class MadeToMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $this->merge([
            'name' => trim((string) $this->input('name')) ?: $user?->name,
            'email' => mb_strtolower(trim((string) $this->input('email'))) ?: $user?->email,
            'phone' => trim((string) $this->input('phone')) ?: $user?->phone,
            'fabric' => (string) $this->input('fabric'),
            'message' => trim((string) $this->input('message')) ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'fabric' => ['required', 'in:saraki,own'],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('shop.tailoring.fields.name'),
            'email' => __('shop.tailoring.fields.email'),
            'phone' => __('shop.tailoring.fields.phone'),
            'fabric' => __('shop.tailoring.fields.fabric'),
            'message' => __('shop.tailoring.fields.message'),
        ];
    }

    public function isSpam(): bool
    {
        return filled($this->input('website'));
    }
}
