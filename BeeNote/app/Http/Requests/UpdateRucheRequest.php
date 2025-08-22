<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRucheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->current_team_id !== null;
    }

    public function rules(): array
    {
        $currentYear = date('Y');

        return [
            'nom' => 'required|string|max:50',
            'couleur' => 'nullable|string|max:30',
            'type' => 'nullable|in:dadant,warre,langstroth,ktbh,autre',
            'statut' => 'required|in:active,inactive,morte',
            'annee_reine' => 'nullable|integer|min:' . ($currentYear - 5) . '|max:' . $currentYear,
            'couleur_marquage_reine' => 'nullable|in:blanc,jaune,rouge,vert,bleu',
            'date_installation' => 'nullable|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la ruche est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 50 caractères.',
            'type.in' => 'Le type de ruche sélectionné n\'est pas valide.',
            'statut.required' => 'Le statut de la ruche est obligatoire.',
            'statut.in' => 'Le statut sélectionné n\'est pas valide.',
            'annee_reine.min' => 'Une reine ne peut pas avoir plus de 5 ans.',
            'annee_reine.max' => 'L\'année de la reine ne peut pas être dans le futur.',
            'couleur_marquage_reine.in' => 'La couleur de marquage sélectionnée n\'est pas valide.',
            'date_installation.before_or_equal' => 'La date d\'installation ne peut pas être dans le futur.',
        ];
    }
}
