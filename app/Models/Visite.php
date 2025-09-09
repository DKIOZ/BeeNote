<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    /** @use HasFactory<\Database\Factories\VisiteFactory> */
    use HasFactory;

    protected $fillable = [
        // Informations temporelles
        'date_visite',
        'heure_debut',
        'heure_fin',
        'duree_visite',

        // Météo & conditions
        'meteo',
        'temperature',
        'humidite',
        'force_vent',

        // État général de la colonie
        'humeur_colonie',
        'activite_entree',
        'estimation_population',
        'bruit_colonie',

        // Observations reine
        'reine_vue',
        'reine_marquee',
        'ponte_observee',
        'qualite_ponte',
        'cellules_royales',
        'nombre_cellules_royales',

        // Couvain
        'oeufs_vus',
        'larves_vues',
        'couvain_opercule',
        'surface_couvain',
        'regularite_couvain',

        // Réserves
        'reserves_miel',
        'reserves_pollen',
        'cadres_miel_opercule',

        // Santé & parasites
        'varroas_observes',
        'nombre_varroas_estimes',
        'fausse_teigne',
        'mortalite',
        'abeilles_mortes_quantite',

        // Structure de la ruche
        'cadres_etat',
        'nombre_cadres_total',
        'nombre_cadres_occupes',
        'cire_construite',

        // Actions réalisées
        'nourrissement_effectue',
        'type_nourrissement',
        'quantite_nourrissement',
        'traitement_applique',
        'hausse_ajoutee',
        'hausse_retiree',

        // Notes & observations
        'notes_generales',
        'actions_prevues',
        'prochaine_visite_prevue',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function ruche()
    {
        return $this->belongsTo(Ruche::class);
    }

    public function photos()
    {
        // Relations polymorphiques 
        return $this->morphMany(Photo::class, 'photoable');
    }

    public function traitements()
    {
        return $this->hasMany(Traitement::class);
    }

    public function taches()
    {
        return $this->hasMany(Tache::class);
    }

    /**
     * Champs considérés comme "stables" pour le pré-remplissage
     */
    public static function getStableFields(): array
    {
        return [
            'nombre_cadres_total',
            'nombre_cadres_occupes',
            'reine_marquee',
            'cellules_royales',
            'nombre_cellules_royales',
            'type_nourrissement',
        ];
    }

    /**
     * Champs à remettre à "non_observe"
     */
    public static function getObservationFields(): array
    {
        return [
            'humeur_colonie',
            'activite_entree',
            'estimation_population',
            'bruit_colonie',
            'reine_vue',
            'ponte_observee',
            'qualite_ponte',
            'oeufs_vus',
            'larves_vues',
            'couvain_opercule',
            'surface_couvain',
            'regularite_couvain',
            'reserves_miel',
            'reserves_pollen',
            'varroas_observes',
            'fausse_teigne',
            'mortalite',
            'cadres_etat',
            'cire_construite',
        ];
    }

    /**
     * Préparer les données pour une nouvelle visite
     */
    public function prepareDataForNewVisit(): array
    {
        $data = [];

        // Copier les champs stables
        foreach (self::getStableFields() as $field) {
            if (!is_null($this->$field)) {
                $data[$field] = $this->$field;
            }
        }

        // Remettre à "non_observe"
        foreach (self::getObservationFields() as $field) {
            $data[$field] = 'non_observe';
        }

        // Réinitialiser le reste (SANS la météo - elle sera récupérée via API)
        $data = array_merge($data, [
            'date_visite' => now()->format('Y-m-d'),
            'heure_debut' => null,
            'heure_fin' => null,
            // Météo sera remplie par WeatherService
            'nourrissement_effectue' => false,
            'traitement_applique' => false,
            'hausse_ajoutee' => false,
            'hausse_retiree' => false,
            'quantite_nourrissement' => null,
            'notes_generales' => null,
            'actions_prevues' => null,
            'prochaine_visite_prevue' => null,
        ]);

        return $data;
    }
}
