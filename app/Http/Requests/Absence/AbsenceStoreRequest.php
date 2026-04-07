<?php

namespace App\Http\Requests\Absence;

use Illuminate\Foundation\Http\FormRequest;

class AbsenceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user &&
            $user->hasAnyRole(['gardener', 'staff', 'supervisor', 'site_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start' => 'required|date|after_or_equal:today',
            'end' => 'required|date|after_or_equal:start',
            'reason' => 'required|in:sakit,darurat,lainnya',
            'evidence' => 'required|image|max:2048',
            'description' => 'required|string',
        ];
    }
}
