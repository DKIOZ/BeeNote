<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Champs d'une visite que le client peut envoyer, partagés par la saisie et la synchronisation.
 * Toute valeur absente vaut « non observé » : rien n'est obligatoire hormis la date.
 */
class VisiteFields
{
    public const OBSERVATIONS = [
        'humeur_colonie' => ['non_observe', 'calme', 'agitee', 'agressive', 'tres_agressive'],
        'estimation_population' => ['non_observe', 'faible', 'normale', 'forte'],
        'reine_vue' => ['non_observe', 'ok', 'non_ok'],
        'ponte_observee' => ['non_observe', 'ok', 'non_ok'],
        'cellules_royales' => ['non_observe', 'ok', 'non_ok'],
        'reserves_miel' => ['non_observe', 'suffisantes', 'insuffisantes'],
        'varroas_observes' => ['non_observe', 'ok', 'non_ok'],
        'surface_couvain' => ['non_observe', 'faible', 'normale', 'importante'],
        'frelons_asiatiques' => ['non_observe', 'absents', 'quelques_uns', 'nombreux'],
        'fausse_teigne' => ['non_observe', 'ok', 'non_ok'],
    ];

    public const ACTIONS = ['nourrissement_effectue', 'traitement_applique', 'hausse_ajoutee', 'hausse_retiree'];

    public static function rules(): array
    {
        $rules = [
            // Demain est toléré : le téléphone peut être sur un autre fuseau que le serveur.
            'date_visite' => ['required', 'date', 'before_or_equal:tomorrow', 'after:2000-01-01'],
            'heure_debut' => ['nullable', 'date_format:H:i'],
            'type_nourrissement' => ['nullable', Rule::in(['sirop', 'candi', 'pate'])],
            'notes_generales' => ['nullable', 'string', 'max:2000'],
            'actions_prevues' => ['nullable', 'string', 'max:1000'],
            'prochaine_visite_prevue' => ['nullable', 'date', 'after_or_equal:date_visite'],
        ];

        foreach (self::OBSERVATIONS as $field => $values) {
            $rules[$field] = ['nullable', Rule::in($values)];
        }

        foreach (self::ACTIONS as $field) {
            $rules[$field] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    /**
     * Ne garde que les champs connus et remplace les absents par leur valeur par défaut.
     */
    public static function clean(array $data): array
    {
        $clean = array_intersect_key($data, self::rules());

        foreach (array_keys(self::OBSERVATIONS) as $field) {
            $clean[$field] = $clean[$field] ?? 'non_observe';
        }

        foreach (self::ACTIONS as $field) {
            $clean[$field] = (bool) ($clean[$field] ?? false);
        }

        return $clean;
    }
}
