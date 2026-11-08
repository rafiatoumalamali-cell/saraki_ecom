<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalisesNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantRequest extends FormRequest
{
    use NormalisesNumbers;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'size' => filled($this->input('size')) ? trim((string) $this->input('size')) : null,
            'color' => filled($this->input('color')) ? trim((string) $this->input('color')) : null,
            'sku' => filled($this->input('sku')) ? mb_strtoupper(trim((string) $this->input('sku'))) : null,
            'stock_qty' => (int) $this->input('stock_qty', 0),
            'price_delta' => $this->decimal('price_delta') ?? '0',
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $variant = $this->route('variant');

        return [
            'size' => ['nullable', 'string', 'max:40'],
            'color' => ['nullable', 'string', 'max:60'],
            'sku' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('product_variants', 'sku')->ignore($variant?->id),
            ],
            'stock_qty' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price_delta' => ['required', 'numeric', 'min:-9999999', 'max:9999999'],
            'is_active' => ['boolean'],
        ];
    }
}
