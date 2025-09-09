<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecolteRequest extends FormRequest
{
   public function authorize(): bool
   {
       return Auth::check() && Auth::user()->current_team_id !== null;
   }

   public function rules(): array
   {
       return [
           'date_recolte' => 'required|date|before_or_equal:today',
           'type_miel' => 'required|in:acacia,tilleul,chataignier,toutes_fleurs,lavande,autre',
           'quantite_kg' => 'required|numeric|min:0.01|max:9999.99',
           'nombre_cadres' => 'required|integer|min:1|max:50',
           'qualite' => 'required|in:excellente,bonne,moyenne,mediocre',
           'humidite_miel' => 'nullable|numeric|min:0|max:999.9',
           'notes_qualite' => 'nullable|string|max:1000',
           'notes_degustation' => 'nullable|string|max:1000',
       ];
   }

   public function messages(): array
   {
       return [
           'date_recolte.required' => 'La date de récolte est obligatoire.',
           'date_recolte.before_or_equal' => 'La date de récolte ne peut pas être dans le futur.',
           'type_miel.required' => 'Le type de miel est obligatoire.',
           'type_miel.in' => 'Le type de miel sélectionné n\'est pas valide.',
           'quantite_kg.required' => 'La quantité récoltée est obligatoire.',
           'quantite_kg.min' => 'La quantité doit être supérieure à 0.',
           'quantite_kg.max' => 'La quantité ne peut pas dépasser 9999.99 kg.',
           'nombre_cadres.required' => 'Le nombre de cadres est obligatoire.',
           'nombre_cadres.min' => 'Le nombre de cadres doit être d\'au moins 1.',
           'nombre_cadres.max' => 'Le nombre de cadres ne peut pas dépasser 50.',
           'qualite.required' => 'La qualité du miel est obligatoire.',
           'qualite.in' => 'La qualité sélectionnée n\'est pas valide.',
           'humidite_miel.max' => 'L\'humidité ne peut pas dépasser 999.9%.',
           'notes_qualite.max' => 'Les notes de qualité ne peuvent pas dépasser 1000 caractères.',
           'notes_degustation.max' => 'Les notes de dégustation ne peuvent pas dépasser 1000 caractères.',
       ];
   }
}