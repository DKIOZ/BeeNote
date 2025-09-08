<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->current_team_id !== null;
    }

    public function rules(): array
    {
        return [
            // Informations temporelles
            'date_visite' => 'required|date|before_or_equal:today',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i|after:heure_debut',

            // Météo
            'meteo' => 'nullable|in:ensoleille,nuageux,pluvieux,orageux',
            'temperature' => 'nullable|numeric|between:-50,50',
            'humidite' => 'nullable|integer|between:0,100',
            'force_vent' => 'nullable|in:nul,faible,modere,fort',

            // État colonie
            'humeur_colonie' => 'required|in:non_observe,calme,agitee,agressive,tres_agressive',
            'activite_entree' => 'required|in:non_observe,normale,faible,intense',
            'estimation_population' => 'required|in:non_observe,normale,faible,forte',
            'bruit_colonie' => 'required|in:non_observe,normal,anormal',

            // Reine
            'reine_vue' => 'required|in:non_observe,ok,non_ok',
            'reine_marquee' => 'required|in:non_observe,ok,non_ok',
            'ponte_observee' => 'required|in:non_observe,ok,non_ok',
            'qualite_ponte' => 'required|in:non_observe,ok,non_ok',
            'cellules_royales' => 'required|in:non_observe,ok,non_ok',
            'nombre_cellules_royales' => 'nullable|integer|min:0|max:20',

            // Couvain
            'oeufs_vus' => 'required|in:non_observe,ok,non_ok',
            'larves_vues' => 'required|in:non_observe,ok,non_ok',
            'couvain_opercule' => 'required|in:non_observe,ok,non_ok',
            'surface_couvain' => 'required|in:non_observe,normale,faible,importante',
            'regularite_couvain' => 'required|in:non_observe,ok,non_ok',

            // Réserves
            'reserves_miel' => 'required|in:non_observe,suffisantes,insuffisantes',
            'reserves_pollen' => 'required|in:non_observe,suffisantes,insuffisantes',
            'cadres_miel_opercule' => 'nullable|integer|min:0|max:20',

            // Santé
            'varroas_observes' => 'required|in:non_observe,ok,non_ok',
            'nombre_varroas_estimes' => 'nullable|integer|min:0',
            'fausse_teigne' => 'required|in:non_observe,ok,non_ok',
            'mortalite' => 'required|in:non_observe,ok,non_ok',
            'abeilles_mortes_quantite' => 'nullable|in:quelques_unes,beaucoup,enorme',

            // Structure
            'cadres_etat' => 'required|in:non_observe,ok,non_ok',
            'nombre_cadres_total' => 'nullable|integer|min:1|max:20',
            'nombre_cadres_occupes' => 'nullable|integer|min:0|max:20',
            'cire_construite' => 'required|in:non_observe,ok,non_ok',

            // Actions
            'nourrissement_effectue' => 'required|boolean',
            'type_nourrissement' => 'nullable|in:sirop,candi,pate',
            'quantite_nourrissement' => 'nullable|numeric|min:0|max:50',
            'traitement_applique' => 'required|boolean',
            'hausse_ajoutee' => 'required|boolean',
            'hausse_retiree' => 'required|boolean',

            // Notes
            'notes_generales' => 'nullable|string|max:2000',
            'actions_prevues' => 'nullable|string|max:1000',
            'prochaine_visite_prevue' => 'nullable|date|after:date_visite',
        ];
    }

    public function messages(): array
    {
        return [
            'date_visite.required' => 'La date de visite est obligatoire.',
            'date_visite.before_or_equal' => 'La date de visite ne peut pas être dans le futur.',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'temperature.between' => 'La température doit être comprise entre -50°C et 50°C.',
            'humidite.between' => 'L\'humidité doit être comprise entre 0% et 100%.',
            'nombre_cadres_occupes.max' => 'Le nombre de cadres occupés ne peut pas dépasser 20.',
            'quantite_nourrissement.max' => 'La quantité de nourrissement ne peut pas dépasser 50kg.',
            'notes_generales.max' => 'Les notes générales ne peuvent pas dépasser 2000 caractères.',
        ];
    }

    protected function prepareForValidation()
    {
        // Si type_nourrissement n'est pas fourni mais nourrissement_effectue = true,
        // on peut forcer une valeur par défaut ou lever une erreur
        if ($this->nourrissement_effectue && !$this->type_nourrissement) {
            $this->merge([
                'type_nourrissement' => 'sirop' // valeur par défaut
            ]);
        }
    }
}
