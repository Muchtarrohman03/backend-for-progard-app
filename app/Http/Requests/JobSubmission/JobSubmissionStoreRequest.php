<?php

namespace App\Http\Requests\JobSubmission;

use Illuminate\Foundation\Http\FormRequest;

class JobSubmissionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // bisa tambahkan logic role jika perlu
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:job_categories,id',
            'status' => 'nullable|string',
            'before' => 'required|image|max:2048',
            'after' => 'required|image|max:2048',
        ];
    }
}
