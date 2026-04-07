<?php

namespace App\Http\Requests\JobSubmission;

use Illuminate\Foundation\Http\FormRequest;

class GetJobSubmissionByDivisionAndDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'division' => [
                'required',
                'string',
                'in:Sektor 1,Sektor 2,Sektor 3,Sektor 4,Sektor 5,Sektor 6,Sektor 7,Sektor 8,Sektor 9,Sektor 10,Management'
            ],
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d'
            ]
        ];
    }
}
