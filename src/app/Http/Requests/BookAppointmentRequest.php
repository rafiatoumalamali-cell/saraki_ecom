<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookAppointmentRequest extends FormRequest
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
        $horizon = today()->addDays((int) config('saraki.appointment_horizon_days'))->toDateString();

        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('appointment_services', 'id')->where('is_active', true),
            ],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.$horizon],
            'start_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
