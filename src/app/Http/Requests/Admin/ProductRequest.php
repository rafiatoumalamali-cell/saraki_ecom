<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalisesNumbers;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    use NormalisesNumbers;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => filled($this->input('slug')) ? $this->input('slug') : null,
            'base_price' => $this->decimal('base_price'),
            'fabric_price' => $this->decimal('fabric_price'),
            'confection_price' => $this->decimal('confection_price'),
            'fabric_unit' => $this->input('fabric_unit') ?: null,
            'base_sku' => mb_strtoupper(trim((string) $this->input('base_sku'))),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'type' => $this->input('type') ?: Product::TYPE_READY_TO_WEAR,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name_fr' => ['required', 'string', 'max:180'],
            'name_en' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200'],
            'description_fr' => ['nullable', 'string', 'max:8000'],
            'description_en' => ['nullable', 'string', 'max:8000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'type' => ['required', Rule::in([Product::TYPE_READY_TO_WEAR, Product::TYPE_MADE_TO_MEASURE])],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'fabric_price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'fabric_unit' => ['nullable', Rule::in(['meter', 'pagne'])],
            'confection_price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'base_sku' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9][A-Z0-9\-_]*$/',
                Rule::unique('products', 'base_sku')->ignore($product?->id),
            ],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'base_sku.regex' => __('admin.products.sku_format'),
        ];
    }
}
