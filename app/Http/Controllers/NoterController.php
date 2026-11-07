<?php

namespace App\Http\Controllers;

use App\Models\Recolte;
use App\Models\RucheEmplacement;
use App\Models\Rucher;
use App\Models\Traitement;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Saisies du registre : traitement, récolte, déplacement, pour une ou plusieurs ruches.
 * Modifie aussi une saisie existante, depuis sa fiche.
 */
class NoterController extends Controller
{
    private const MODELES = [
        'traitement' => Traitement::class,
        'recolte' => Recolte::class,
        'deplacement' => RucheEmplacement::class,
    ];

    public function create(Request $request)
    {
        // « Compléter le traitement » part de la visite : sa date est celle du traitement.
        $visite = $request->integer('visite')
            ? Visite::where('team_id', $request->user()->current_team_id)->with('ruche:id,nom')->find($request->integer('visite'))
            : null;

        return Inertia::render('Noter', [
            'visite' => $visite ? ['id' => $visite->id, 'date' => $visite->date_visite->toDateString(), 'ruche' => $visite->ruche?->nom] : null,
            'type' => in_array($request->query('type'), array_keys(self::MODELES)) ? $request->query('type') : 'traitement',
            'rucheId' => $request->integer('ruche') ?: null,
            'visiteId' => $request->integer('visite') ?: null,
            'ruchers' => Rucher::where('team_id', $request->user()->current_team_id)
                ->with(['ruches' => fn ($q) => $q->orderBy('numero')->orderBy('id')->select('id', 'nom', 'rucher_id', 'statut')])
                ->orderBy('nom')
                ->get(['id', 'nom']),
            'aujourdhui' => now()->toDateString(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        abort_unless(isset(self::MODELES[$type]), 404);

        $data = $request->validate([
            'ruche_ids' => ['required', 'array', 'min:1', 'max:100'],
            'ruche_ids.*' => ['integer'],
            'visite_id' => ['nullable', 'integer'],
            ...$this->rules($type),
        ]);

        $ruches = $this->teamRuches($data['ruche_ids']);
        abort_unless($ruches->count() === count(array_unique($data['ruche_ids'])), 404);
        $ruches->each(fn ($ruche) => $this->authorizeRuche($ruche, 'update'));

        if ($type === 'deplacement') {
            $ruches = $ruches->reject(fn ($ruche) => $ruche->rucher_id === (int) $data['vers_rucher_id']);

            if ($ruches->isEmpty()) {
                throw ValidationException::withMessages(['vers_rucher_id' => 'Déjà dans ce rucher.']);
            }

            // Un déplacement part du rucher actuel : il ne peut pas être daté avant le précédent.
            foreach ($ruches as $ruche) {
                $dernier = RucheEmplacement::where('ruche_id', $ruche->id)->orderByDesc('id')->first();
                if ($dernier && $dernier->date_deplacement->toDateString() > $data['date_deplacement']) {
                    throw ValidationException::withMessages(['date_deplacement' => "{$ruche->nom} a déjà été déplacée le {$dernier->date_deplacement->format('d/m/Y')}. Choisissez une date à partir de ce jour."]);
                }
            }
        }

        DB::transaction(function () use ($ruches, $type, $data, $request) {
            foreach ($ruches as $ruche) {
                $this->enregistrer($type, $ruche, $data, $request);

                if ($type === 'deplacement') {
                    $ruche->recalculerRegistre();
                }
            }
        });

        $cible = $ruches->count() === 1 ? route('ruches.show', $ruches->first()) : route('registre.index');

        return redirect($cible)->with('message', 'Enregistré dans le registre.');
    }

    public function show(Request $request, string $type, int $id)
    {
        $fiche = $this->fiche($type, $id);
        $peutSupprimer = $request->user()->can('delete', $fiche->ruche->rucher);

        return Inertia::render('Fiche', [
            'type' => $type,
            'fiche' => $fiche->load($type === 'deplacement' ? ['ruche:id,nom', 'depuis:id,nom', 'vers:id,nom'] : ['ruche:id,nom']),
            'modifiee' => $fiche->updated_at?->gt($fiche->created_at?->addMinute()) ? $fiche->updated_at->toDateString() : null,
            'peutSupprimer' => $peutSupprimer,
        ]);
    }

    public function update(Request $request, string $type, int $id)
    {
        $fiche = $this->fiche($type, $id, 'update');
        $data = $request->validate($this->rules($type, true));

        if ($type === 'deplacement') {
            $this->verifierChronologie($fiche, $data['date_deplacement']);
        }

        $fiche->update($data);

        if ($type === 'deplacement') {
            $fiche->ruche->recalculerRegistre();
        }

        return redirect()->route('ruches.show', $fiche->ruche_id)->with('message', 'Fiche corrigée.');
    }

    public function destroy(string $type, int $id)
    {
        $fiche = $this->fiche($type, $id, 'delete');
        $rucheId = $fiche->ruche_id;

        if ($type === 'deplacement') {
            // Le dernier enregistré, quelle que soit sa date : c'est lui qui a placé la ruche où elle est.
            $dernier = RucheEmplacement::where('ruche_id', $rucheId)->orderByDesc('id')->first();

            if (! $dernier->is($fiche)) {
                return back()->with('erreur', 'Mettez d\'abord à la corbeille le dernier déplacement enregistré pour cette ruche.');
            }

            if (! $fiche->depuis_rucher_id || ! Rucher::whereKey($fiche->depuis_rucher_id)->exists()) {
                return back()->with('erreur', 'Le rucher de départ est à la corbeille : remettez-le d\'abord en place.');
            }

            // Annuler le dernier déplacement remet la ruche là où il l'avait prise.
            DB::transaction(function () use ($fiche) {
                $fiche->ruche->placerDans($fiche->depuis_rucher_id);
                $fiche->delete();
                $fiche->ruche->recalculerRegistre();
            });

            return redirect()->route('ruches.show', $rucheId)->with('message', 'Déplacement annulé, la ruche est revenue à sa place.');
        }

        $fiche->delete();

        return redirect()->route('ruches.show', $rucheId)->with('message', 'Mis à la corbeille.');
    }

    /**
     * Une date corrigée reste entre le déplacement d'avant et celui d'après.
     */
    private function verifierChronologie(RucheEmplacement $fiche, string $date): void
    {
        $avant = RucheEmplacement::where('ruche_id', $fiche->ruche_id)->where('id', '<', $fiche->id)->orderByDesc('id')->first();
        $apres = RucheEmplacement::where('ruche_id', $fiche->ruche_id)->where('id', '>', $fiche->id)->orderBy('id')->first();

        if ($avant && $avant->date_deplacement->toDateString() > $date) {
            throw ValidationException::withMessages(['date_deplacement' => "Le déplacement précédent date du {$avant->date_deplacement->format('d/m/Y')}."]);
        }

        if ($apres && $apres->date_deplacement->toDateString() < $date) {
            throw ValidationException::withMessages(['date_deplacement' => "Le déplacement suivant date du {$apres->date_deplacement->format('d/m/Y')}."]);
        }
    }

    private function fiche(string $type, int $id, string $ability = 'view')
    {
        abort_unless(isset(self::MODELES[$type]), 404);

        $fiche = self::MODELES[$type]::where('team_id', request()->user()->current_team_id)->findOrFail($id);
        abort_unless($fiche->ruche, 404);
        $this->authorizeRuche($fiche->ruche, $ability);

        return $fiche;
    }

    private function enregistrer(string $type, $ruche, array $data, Request $request): void
    {
        $attributs = ['team_id' => $request->user()->current_team_id, 'user_id' => $request->user()->id, 'ruche_id' => $ruche->id];

        if ($type === 'deplacement') {
            $destination = Rucher::where('team_id', $attributs['team_id'])->findOrFail($data['vers_rucher_id']);

            $emplacement = new RucheEmplacement($data);
            $emplacement->forceFill([...$attributs, 'depuis_rucher_id' => $ruche->rucher_id, 'vers_rucher_id' => $destination->id])->save();
            $ruche->placerDans($destination->id);

            return;
        }

        $modele = self::MODELES[$type];
        $saisie = new $modele($data);

        // Le lien vers une visite n'est gardé que si elle est bien celle de cette ruche.
        if ($type === 'traitement' && ! empty($data['visite_id'])) {
            $saisie->visite_id = Visite::where('team_id', $attributs['team_id'])->where('ruche_id', $ruche->id)->whereKey($data['visite_id'])->value('id');
        }

        $saisie->forceFill($attributs)->save();
    }

    private function rules(string $type, bool $edition = false): array
    {
        $date = ['required', 'date', 'before_or_equal:today'];

        return match ($type) {
            'traitement' => [
                'date_traitement' => $date,
                'type_traitement' => ['required', Rule::in(['varroa', 'nosema', 'loque', 'preventif', 'frelon_asiatique', 'autre'])],
                'produit_utilise' => ['required', 'string', 'max:100'],
                'dosage' => ['nullable', 'string', 'max:50'],
                'numero_lot' => ['nullable', 'string', 'max:60'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            'recolte' => [
                'date_recolte' => $date,
                'type_miel' => ['required', Rule::in(['acacia', 'tilleul', 'chataignier', 'toutes_fleurs', 'lavande', 'autre'])],
                'quantite_kg' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            ],
            'deplacement' => [
                'date_deplacement' => $date,
                'notes' => ['nullable', 'string', 'max:1000'],
                ...($edition ? [] : ['vers_rucher_id' => ['required', 'integer']]),
            ],
        };
    }
}
