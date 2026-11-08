<?php

namespace App\Models;

use App\Models\Concerns\GardeLeRucher;
use App\Support\VisiteFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visite extends Model
{
    /** @use HasFactory<\Database\Factories\VisiteFactory> */
    use GardeLeRucher, HasFactory, SoftDeletes;

    protected $guarded = ['id', 'team_id', 'user_id', 'ruche_id', 'rucher_id', 'client_uuid', 'note_audio_path', 'note_audio_duree'];

    // Le chemin du fichier audio ne quitte jamais le serveur.
    protected $hidden = ['note_audio_path', 'client_uuid', 'uuids_fusionnes', 'notes_audio_ajoutees'];

    protected $appends = ['a_note_audio', 'notes_audio'];

    protected function casts(): array
    {
        return [
            'date_visite' => 'date:Y-m-d',
            'prochaine_visite_prevue' => 'date:Y-m-d',
            'nourrissement_effectue' => 'boolean',
            'traitement_applique' => 'boolean',
            'hausse_ajoutee' => 'boolean',
            'hausse_retiree' => 'boolean',
            'notes_audio_ajoutees' => 'array',
        ];
    }

    public function getANoteAudioAttribute(): bool
    {
        return $this->note_audio_path !== null;
    }

    /**
     * Les notes vocales de la visite, sans leur chemin : la première, puis celles ajoutées dans la journée.
     */
    public function getNotesAudioAttribute(): array
    {
        $notes = $this->note_audio_path ? [['numero' => 0, 'duree' => $this->note_audio_duree]] : [];

        foreach ($this->notes_audio_ajoutees ?? [] as $index => $note) {
            $notes[] = ['numero' => $index + 1, 'duree' => $note['duree'] ?? null];
        }

        return $notes;
    }

    /**
     * Chemins de toutes les notes, pour les servir ou les effacer.
     */
    public function cheminsAudio(): array
    {
        return array_values(array_filter([$this->note_audio_path, ...array_column($this->notes_audio_ajoutees ?? [], 'chemin')]));
    }

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

    public function traitements()
    {
        return $this->hasMany(Traitement::class);
    }

    public function taches()
    {
        return $this->hasMany(Tache::class);
    }

    /**
     * Alertes d'après plusieurs visites, de la plus récente à la plus ancienne : pour chaque point,
     * c'est la dernière visite qui l'a vraiment observé qui compte.
     */
    public static function alertesDe(iterable $visites): array
    {
        $combinee = new self;

        foreach (array_keys(VisiteFields::OBSERVATIONS) as $champ) {
            foreach ($visites as $visite) {
                if (($visite->{$champ} ?? 'non_observe') !== 'non_observe') {
                    $combinee->{$champ} = $visite->{$champ};
                    break;
                }
            }
        }

        return $combinee->alertes();
    }

    /**
     * Ce que l'apiculteur doit garder à l'œil après cette visite.
     */
    public function alertes(): array
    {
        $signes = [
            'estimation_population' => ['faible' => 'colonie faible'],
            'reine_vue' => ['non_ok' => 'reine pas vue'],
            'ponte_observee' => ['non_ok' => 'pas de ponte'],
            'cellules_royales' => ['ok' => 'cellules royales'],
            'reserves_miel' => ['insuffisantes' => 'réserves faibles'],
            'varroas_observes' => ['ok' => 'varroas'],
            'humeur_colonie' => ['agressive' => 'agressive', 'tres_agressive' => 'très agressive'],
            'frelons_asiatiques' => ['nombreux' => 'beaucoup de frelons'],
            'fausse_teigne' => ['ok' => 'teigne'],
            'surface_couvain' => ['faible' => 'couvain faible'],
        ];

        // Des œufs prouvent que la reine est là : ne pas la voir n'est alors pas inquiétant.
        if ($this->ponte_observee === 'ok') {
            unset($signes['reine_vue']);
        }

        $alertes = [];
        foreach ($signes as $champ => $valeurs) {
            if (isset($valeurs[$this->getAttribute($champ)])) {
                $alertes[] = $valeurs[$this->getAttribute($champ)];
            }
        }

        return $alertes;
    }

    /**
     * Résumé lisible d'une visite, aussi repris dans le registre.
     */
    public function resume(): string
    {
        $labels = [
            'humeur_colonie' => ['calme' => 'Calme', 'agitee' => 'Agitée', 'agressive' => 'Agressive', 'tres_agressive' => 'Très agressive'],
            'estimation_population' => ['faible' => 'Colonie faible', 'normale' => 'Colonie normale', 'forte' => 'Colonie forte'],
            'reine_vue' => ['ok' => 'Reine vue', 'non_ok' => 'Reine pas vue'],
            'ponte_observee' => ['ok' => 'Ponte vue', 'non_ok' => 'Pas de ponte'],
            'cellules_royales' => ['ok' => 'Cellules royales', 'non_ok' => 'Pas de cellules'],
            'reserves_miel' => ['suffisantes' => 'Réserves bonnes', 'insuffisantes' => 'Réserves faibles'],
            // Au registre, on écrit ce qui a été observé : l'absence vue ne prouve pas l'absence.
            'varroas_observes' => ['ok' => 'Varroas présents', 'non_ok' => 'Pas vu de varroas'],
            'surface_couvain' => ['faible' => 'Couvain faible', 'normale' => 'Couvain normal', 'importante' => 'Couvain fort'],
            'frelons_asiatiques' => ['absents' => 'Pas de frelons', 'quelques_uns' => 'Quelques frelons', 'nombreux' => 'Beaucoup de frelons'],
            'fausse_teigne' => ['ok' => 'Teigne présente', 'non_ok' => 'Pas de teigne'],
        ];

        $parts = [];
        foreach ($labels as $field => $values) {
            $value = $this->getAttribute($field);
            if (isset($values[$value])) {
                $parts[] = $values[$value];
            }
        }

        // « Nourri (sirop) » plutôt que deux mots qui disent la même chose.
        $nourriture = ['sirop' => 'sirop', 'candi' => 'candi', 'pate' => 'pâte'][$this->type_nourrissement] ?? null;
        if ($nourriture && ! $this->nourrissement_effectue) {
            $parts[] = 'Nourri ('.$nourriture.')';
        }

        foreach ([
            'nourrissement_effectue' => $nourriture ? 'Nourri ('.$nourriture.')' : 'Nourri',
            'traitement_applique' => 'Traité',
            'hausse_ajoutee' => 'Hausse posée',
            'hausse_retiree' => 'Hausse enlevée',
        ] as $field => $label) {
            if ($this->getAttribute($field)) {
                $parts[] = $label;
            }
        }

        return $parts === [] ? 'Rien à signaler' : implode(', ', $parts);
    }
}
