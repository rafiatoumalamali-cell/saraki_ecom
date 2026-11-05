<?php

namespace App\Http\Requests;

use App\Enums\FitPreference;
use App\Models\CustomerMeasurement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeasurementUpdateRequest extends FormRequest
{
    /**
     * Every field is optional: customers fill in what they know and the tailor
     * completes the sheet at the measurement appointment.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $measurements = [];

        foreach (CustomerMeasurement::BODY_FIELDS as $field => $meta) {
            $measurements[$field] = [
                'nullable', 'numeric', 'decimal:0,2',
                'min:'.$meta['min'], 'max:'.$meta['max'],
            ];
        }

        return $measurements + [
            'fit_preference' => ['nullable', Rule::enum(FitPreference::class)],
            'taken_at' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $labels = [];

        foreach (array_keys(CustomerMeasurement::BODY_FIELDS) as $field) {
            $labels[$field] = CustomerMeasurement::label($field);
        }

        return $labels + [
            'fit_preference' => __('measurements.labels.fit_preference'),
            'taken_at' => __('measurements.labels.taken_at'),
            'notes' => __('measurements.labels.notes'),
        ];
    }
}
