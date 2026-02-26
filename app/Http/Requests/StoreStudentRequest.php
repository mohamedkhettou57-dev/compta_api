<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required','integer','exists:users,id'],
            'cne' => ['required','string','max:50','unique:students,cne'],
            'filiere' => ['required','string','max:255'],
            'niveau' => ['required','string','max:50'],
            'annee_universitaire' => ['required','string','max:20'],
        ];
    }
}