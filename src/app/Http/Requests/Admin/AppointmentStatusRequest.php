<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'appointment_date' => ['nullable', 'required_if:status,rescheduled', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['nullable', 'required_if:status,rescheduled', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
