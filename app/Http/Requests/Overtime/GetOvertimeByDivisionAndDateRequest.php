<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class GetOvertimeByDivisionAndDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
