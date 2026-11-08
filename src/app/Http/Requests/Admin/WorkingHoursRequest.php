<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class WorkingHoursRequest extends FormRequest
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
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.is_closed' => ['sometimes', 'boolean'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * An open day needs both ends of its range, and closing time must follow
     * opening time; the database enforces the same rule.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ($this->input('hours', []) as $index => $row) {
                    if (! is_array($row) || ! empty($row['is_closed'])) {
                        continue;
                    }

                    $open = $row['open_time'] ?? null;
                    $close = $row['close_time'] ?? null;

                    if (blank($open) || blank($close)) {
                        $validator->errors()->add("hours.{$index}.open_time", __('admin.schedule.hours_required'));

                        continue;
                    }

                    if ($close <= $open) {
                        $validator->errors()->add("hours.{$index}.close_time", __('admin.schedule.hours_invalid'));
                    }
                }
            },
        ];
    }
}
