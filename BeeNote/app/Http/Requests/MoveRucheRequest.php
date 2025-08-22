<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class MoveRucheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->current_team_id !== null;
    }

    public function rules(): array
    {
        return [
            'rucher_id' => 'required|exists:ruchers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'rucher_id.required' => 'Vous devez sélectionner un rucher de destination.',
            'rucher_id.exists' => 'Le rucher sélectionné n\'existe pas.',
        ];
    }
}
