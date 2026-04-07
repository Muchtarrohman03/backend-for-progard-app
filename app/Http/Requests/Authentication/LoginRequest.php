<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string'
        ];
    }
}
