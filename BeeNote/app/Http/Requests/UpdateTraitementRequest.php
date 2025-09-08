<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTraitementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->current_team_id !== null;
    }

    public function rules(): array
    {
        return [
            'date_traitement' => 'required|date|before_or_equal:today',
            'type_traitement' => 'required|in:varroa,nosema,loque,preventif,frelon_asiatique,autre',
            'produit_utilise' => 'required|string|max:100',
            'dosage' => 'nullable|string|max:50',
            'methode_application' => 'nullable|in:vaporisation,goutte_a_goutte,bandes,poudrage,autre',
            'temperature_application' => 'nullable|numeric|between:-20,50',
            'efficacite' => 'nullable|in:non_evaluee,excellente,bonne,moyenne,faible',
            'effets_secondaires' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'date_traitement.required' => 'La date du traitement est obligatoire.',
            'date_traitement.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'type_traitement.required' => 'Le type de traitement est obligatoire.',
            'type_traitement.in' => 'Le type de traitement sélectionné n\'est pas valide.',
            'produit_utilise.required' => 'Le produit utilisé est obligatoire.',
            'produit_utilise.max' => 'Le nom du produit ne peut pas dépasser 100 caractères.',
            'dosage.max' => 'Le dosage ne peut pas dépasser 50 caractères.',
            'methode_application.in' => 'La méthode d\'application sélectionnée n\'est pas valide.',
            'temperature_application.between' => 'La température doit être comprise entre -20°C et 50°C.',
            'efficacite.in' => 'L\'efficacité sélectionnée n\'est pas valide.',
            'effets_secondaires.max' => 'Les effets secondaires ne peuvent pas dépasser 500 caractères.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.',
        ];
    }
}
