<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalisesNumbers;
use Illuminate\Foundation\Http\FormRequest;

class VariantGenerationRequest extends FormRequest
{
    use NormalisesNumbers;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'stock_qty' => (int) $this->input('stock_qty', 0),
            'price_delta' => $this->decimal('price_delta') ?? '0',
        ]);
    }

    /**
     * Sizes and colours are typed as comma-separated lists so a whole matrix can be
     * produced in one go (S,M,L,XL × Or,Ivoire → eight variants).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sizes' => ['required', 'string', 'max:255'],
            'colors' => ['required', 'string', 'max:255'],
            'stock_qty' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price_delta' => ['required', 'numeric', 'min:-9999999', 'max:9999999'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function sizes(): array
    {
        return $this->list('sizes');
    }

    /**
     * @return array<int, string>
     */
    public function colors(): array
    {
        return $this->list('colors');
    }

    /**
     * @return array<int, string>
     */
    private function list(string $key): array
    {
        return collect(preg_split('/[,;]+/', (string) $this->input($key)) ?: [])
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
