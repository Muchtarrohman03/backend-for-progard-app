<?php

namespace App\Http\Requests\Absence;

use Illuminate\Foundation\Http\FormRequest;

class AbsenceFilterByDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user &&
            $user->hasAnyRole(['gardener', 'staff', 'supervisor', 'site_manager']);
    }


    public function rules(): array
    {
        return [
            'date' => 'required|date',
        ];
    }
}
