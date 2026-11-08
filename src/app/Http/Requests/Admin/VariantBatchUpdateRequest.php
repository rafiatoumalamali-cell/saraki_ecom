<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalisesNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VariantBatchUpdateRequest extends FormRequest
{
    use NormalisesNumbers;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The variant grid posts every row at once, so each row is normalised here
     * rather than inside the controller.
     */
    protected function prepareForValidation(): void
    {
        $variants = (array) $this->input('variants', []);

        foreach (array_keys($variants) as $id) {
            $variants[$id] = [
                'size' => filled($this->input("variants.{$id}.size")) ? trim((string) $this->input("variants.{$id}.size")) : null,
                'color' => filled($this->input("variants.{$id}.color")) ? trim((string) $this->input("variants.{$id}.color")) : null,
                'sku' => mb_strtoupper(trim((string) $this->input("variants.{$id}.sku"))),
                'stock_qty' => (int) $this->input("variants.{$id}.stock_qty", 0),
                'price_delta' => $this->decimal("variants.{$id}.price_delta") ?? '0',
                'is_active' => filter_var($this->input("variants.{$id}.is_active", false), FILTER_VALIDATE_BOOLEAN),
            ];
        }

        $this->merge([
            'variants' => $variants,
            'removed' => array_map('intval', (array) $this->input('removed', [])),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.size' => ['nullable', 'string', 'max:40'],
            'variants.*.color' => ['nullable', 'string', 'max:60'],
            'variants.*.stock_qty' => ['required', 'integer', 'min:0', 'max:1000000'],
            'variants.*.price_delta' => ['required', 'numeric', 'min:-9999999', 'max:9999999'],
            'variants.*.is_active' => ['boolean'],
            'removed' => ['nullable', 'array'],
            'removed.*' => ['integer'],
        ];

        foreach (array_keys((array) $this->input('variants', [])) as $id) {
            $rules["variants.{$id}.sku"] = [
                'required',
                'string',
                'max:64',
                Rule::unique('product_variants', 'sku')->ignore((int) $id),
            ];
        }

        return $rules;
    }
}
