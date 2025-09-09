<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->current_team_id !== null;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:100',
            'localisation' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du rucher est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 100 caractères.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ];
    }
}
