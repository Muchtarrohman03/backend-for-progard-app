<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'category_id' => 'required|exists:job_categories,id',
            'description' => 'nullable|string',
            'before' => 'required|image|max:2048',
            'after' => 'required|image|max:2048',
        ];
    }
}
