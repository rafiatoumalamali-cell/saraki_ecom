<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlockedDateRequest extends FormRequest
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
            'blocked_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'unique:blocked_dates,blocked_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
