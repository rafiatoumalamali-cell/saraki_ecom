<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Only a payment of this very order can be refunded from its screen.
            'payment_id' => [
                'required',
                'integer',
                Rule::exists('payments', 'id')->where('order_id', $this->route('order')?->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_id.exists' => __('admin.refunds.payment_not_found'),
        ];
    }
}
