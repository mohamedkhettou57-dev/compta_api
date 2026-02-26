<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id ?? $this->route('student');

        return [
            'user_id' => ['sometimes','required','integer','exists:users,id'],
            'cne' => ['sometimes','required','string','max:50', Rule::unique('students','cne')->ignore($studentId)],
            'filiere' => ['sometimes','required','string','max:255'],
            'niveau' => ['sometimes','required','string','max:50'],
            'annee_universitaire' => ['sometimes','required','string','max:20'],
        ];
    }
}