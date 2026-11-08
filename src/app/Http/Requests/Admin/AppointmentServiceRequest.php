<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentServiceRequest extends FormRequest
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
            'name_fr' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:480'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
