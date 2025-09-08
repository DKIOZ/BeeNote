<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVisiteRequest extends FormRequest
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

            // État colonie - NULLABLE au lieu de required
            'humeur_colonie' => 'nullable|in:non_observe,calme,agitee,agressive,tres_agressive',
            'activite_entree' => 'nullable|in:non_observe,normale,faible,intense',
            'estimation_population' => 'nullable|in:non_observe,normale,faible,forte',
            'bruit_colonie' => 'nullable|in:non_observe,normal,anormal',

            // Reine - NULLABLE
            'reine_vue' => 'nullable|in:non_observe,ok,non_ok',
            'reine_marquee' => 'nullable|in:non_observe,ok,non_ok',
            'ponte_observee' => 'nullable|in:non_observe,ok,non_ok',
            'qualite_ponte' => 'nullable|in:non_observe,ok,non_ok',
            'cellules_royales' => 'nullable|in:non_observe,ok,non_ok',
            'nombre_cellules_royales' => 'nullable|integer|min:0|max:20',

            // Couvain - NULLABLE
            'oeufs_vus' => 'nullable|in:non_observe,ok,non_ok',
            'larves_vues' => 'nullable|in:non_observe,ok,non_ok',
            'couvain_opercule' => 'nullable|in:non_observe,ok,non_ok',
            'surface_couvain' => 'nullable|in:non_observe,normale,faible,importante',
            'regularite_couvain' => 'nullable|in:non_observe,ok,non_ok',

            // Réserves - NULLABLE
            'reserves_miel' => 'nullable|in:non_observe,suffisantes,insuffisantes',
            'reserves_pollen' => 'nullable|in:non_observe,suffisantes,insuffisantes',
            'cadres_miel_opercule' => 'nullable|integer|min:0|max:20',

            // Santé - NULLABLE
            'varroas_observes' => 'nullable|in:non_observe,ok,non_ok',
            'nombre_varroas_estimes' => 'nullable|integer|min:0',
            'fausse_teigne' => 'nullable|in:non_observe,ok,non_ok',
            'mortalite' => 'nullable|in:non_observe,ok,non_ok',
            'abeilles_mortes_quantite' => 'nullable|in:quelques_unes,beaucoup,enorme',

            // Structure - NULLABLE
            'cadres_etat' => 'nullable|in:non_observe,ok,non_ok',
            'nombre_cadres_total' => 'nullable|integer|min:1|max:20',
            'nombre_cadres_occupes' => 'nullable|integer|min:0|max:20',
            'cire_construite' => 'nullable|in:non_observe,ok,non_ok',

            // Actions - NULLABLE (sauf boolean qui ont des valeurs par défaut)
            'nourrissement_effectue' => 'nullable|boolean',
            'type_nourrissement' => 'nullable|in:sirop,candi,pate',
            'quantite_nourrissement' => 'nullable|numeric|min:0|max:50',
            'traitement_applique' => 'nullable|boolean',
            'hausse_ajoutee' => 'nullable|boolean',
            'hausse_retiree' => 'nullable|boolean',

            // Notes - NULLABLE
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
            'actions_prevues.max' => 'Les actions prévues ne peuvent pas dépasser 1000 caractères.',
            'prochaine_visite_prevue.after' => 'La prochaine visite doit être postérieure à la date de cette visite.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = $this->all();

        // 1. Convertir les chaînes vides en null
        $fieldsToNullify = [
            'heure_debut',
            'heure_fin',
            'meteo',
            'temperature',
            'humidite',
            'force_vent',
            'nombre_cellules_royales',
            'cadres_miel_opercule',
            'nombre_varroas_estimes',
            'abeilles_mortes_quantite',
            'nombre_cadres_total',
            'nombre_cadres_occupes',
            'type_nourrissement',
            'quantite_nourrissement',
            'notes_generales',
            'actions_prevues',
            'prochaine_visite_prevue'
        ];

        foreach ($fieldsToNullify as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // 2. Nettoyer les heures (retirer les secondes si présentes)
        if (!empty($data['heure_debut'])) {
            $data['heure_debut'] = substr($data['heure_debut'], 0, 5); // "10:00:00" -> "10:00"
        }
        if (!empty($data['heure_fin'])) {
            $data['heure_fin'] = substr($data['heure_fin'], 0, 5);
        }

        // 3. Convertir température et humidité en nombres
        if (!empty($data['temperature'])) {
            $data['temperature'] = (float) $data['temperature'];
        }
        if (!empty($data['humidite'])) {
            $data['humidite'] = (int) $data['humidite'];
        }

        // 4. Gérer les valeurs par défaut pour les champs d'observation
        $observationDefaults = [
            'humeur_colonie' => 'non_observe',
            'activite_entree' => 'non_observe',
            'estimation_population' => 'non_observe',
            'bruit_colonie' => 'non_observe',
            'reine_vue' => 'non_observe',
            'reine_marquee' => 'non_observe',
            'ponte_observee' => 'non_observe',
            'qualite_ponte' => 'non_observe',
            'cellules_royales' => 'non_observe',
            'oeufs_vus' => 'non_observe',
            'larves_vues' => 'non_observe',
            'couvain_opercule' => 'non_observe',
            'surface_couvain' => 'non_observe',
            'regularite_couvain' => 'non_observe',
            'reserves_miel' => 'non_observe',
            'reserves_pollen' => 'non_observe',
            'varroas_observes' => 'non_observe',
            'fausse_teigne' => 'non_observe',
            'mortalite' => 'non_observe',
            'cadres_etat' => 'non_observe',
            'cire_construite' => 'non_observe',
        ];

        foreach ($observationDefaults as $field => $defaultValue) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $data[$field] = $defaultValue;
            }
        }

        // 5. Gérer les boolean
        $booleanFields = ['nourrissement_effectue', 'traitement_applique', 'hausse_ajoutee', 'hausse_retiree'];
        foreach ($booleanFields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = false;
            }
        }

        // 6. Si pas de nourrissement, vider les champs liés
        if (!$data['nourrissement_effectue']) {
            $data['type_nourrissement'] = null;
            $data['quantite_nourrissement'] = null;
        }

        $this->replace($data);
    }
}
