<?php

namespace App\Http\Controllers;

use App\Models\Ruche;
use App\Models\Rucher;
use App\Services\RucherOverview;
use App\Services\RegistreService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RucheController extends Controller
{
    public function create(Rucher $rucher)
    {
        $this->authorize('update', $rucher);

        return Inertia::render('Ruches/Form', [
            'rucher' => $rucher->only(['id', 'nom']),
            'ruche' => null,
            'suggestion' => 'Ruche '.RucherOverview::prochainNumero($rucher->team_id),
        ]);
    }

    public function store(Request $request, Rucher $rucher)
    {
        $this->authorize('update', $rucher);
        $ruche = $rucher->ruches()->create($request->validate($this->rules($rucher->team_id), $this->messages()));

        return redirect()->route('ruchers.show', $rucher)->with('message', "{$ruche->nom} ajoutée.");
    }

    public function show(Request $request, Ruche $ruche)
    {
        $this->authorizeRuche($ruche);

        $apercu = RucherOverview::ruchers($request->user())
            ->firstWhere('id', $ruche->rucher_id)['ruches']
            ->firstWhere('id', $ruche->id);

        return Inertia::render('Ruches/Show', [
            'ruche' => [
                ...$ruche->only(['id', 'nom', 'couleur', 'type', 'statut', 'annee_reine', 'couleur_marquage_reine', 'date_installation', 'notes', 'qr_token']),
                'rucher' => $ruche->rucher->only(['id', 'nom']),
                'numero' => $apercu['numero'],
                'etat' => $apercu['etat'],
                'taches' => $apercu['taches'],
                'prochaine_visite' => $apercu['prochaine_visite'],
                'rappel' => $apercu['rappel'],
                'alertes' => $apercu['alertes'],
            ],
            'historique' => RegistreService::pourRuche($ruche),
            'peutModifier' => $request->user()->can('update', $ruche->rucher),
        ]);
    }

    public function edit(Request $request, Ruche $ruche)
    {
        $this->authorizeRuche($ruche, 'update');

        return Inertia::render('Ruches/Form', [
            'rucher' => $ruche->rucher->only(['id', 'nom']),
            'ruche' => $ruche->only(['id', 'nom', 'couleur', 'type', 'statut', 'annee_reine', 'couleur_marquage_reine', 'date_installation', 'notes']),
            'peutSupprimer' => $request->user()->can('delete', $ruche->rucher),
        ]);
    }

    public function update(Request $request, Ruche $ruche)
    {
        $this->authorizeRuche($ruche, 'update');
        $ruche->update($request->validate($this->rules($ruche->rucher->team_id, $ruche), $this->messages()));

        return redirect()->route('ruches.show', $ruche)->with('message', 'Ruche enregistrée.');
    }

    public function destroy(Ruche $ruche)
    {
        $this->authorizeRuche($ruche, 'delete');
        $rucherId = $ruche->rucher_id;
        $ruche->delete();

        return redirect()->route('ruchers.show', $rucherId)->with('message', 'Ruche mise à la corbeille.');
    }

    /**
     * Un QR code d'une autre équipe répond 404 : il ne révèle pas l'existence de la ruche.
     */
    public function scan(Request $request, string $token)
    {
        $ruche = Ruche::where('qr_token', $token)
            ->whereHas('rucher', fn ($q) => $q->where('team_id', $request->user()->current_team_id))
            ->firstOrFail();

        // Comme sans réseau : scanner une ruche, c'est commencer sa visite.
        return redirect()->route('terrain', ['ruche' => $ruche->id]);
    }

    private function messages(): array
    {
        return ['nom.unique' => 'Une autre ruche porte déjà ce nom, peut-être à la corbeille. Choisissez-en un autre, par exemple avec un autre numéro.'];
    }

    private function rules(int $teamId, ?Ruche $ruche = null): array
    {
        // Le nom sert au registre, aux étiquettes et à la voix : il est unique sur tout le compte.
        // Une ruche à la corbeille garde son nom : ses traitements ne doivent pas passer à une autre.
        $nomPris = Rule::unique('ruches', 'nom')
            ->where(fn ($q) => $q->whereIn('rucher_id', Rucher::withTrashed()->where('team_id', $teamId)->select('id')))
            ->ignore($ruche?->id);

        // Le numéro porté par le nom ne doit pas être celui d'une autre ruche du compte.
        $numeroPris = function (string $attribut, $valeur, $echec) use ($teamId, $ruche) {
            $numero = RucherOverview::numeroDuNom((string) $valeur);
            $autre = $numero ? Ruche::withTrashed()->where('numero', $numero)
                ->whereHas('rucher', fn ($q) => $q->withTrashed()->where('team_id', $teamId))
                ->when($ruche, fn ($q) => $q->whereKeyNot($ruche->id))
                ->value('nom') : null;

            if ($autre) {
                $echec("Le numéro {$numero} est déjà celui de « {$autre} ». Choisissez un autre numéro.");
            }
        };

        return [
            'nom' => ['required', 'string', 'max:50', $nomPris, $numeroPris],
            'couleur' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type' => ['sometimes', 'filled', Rule::in(['dadant', 'warre', 'langstroth', 'ktbh', 'autre'])],
            'statut' => ['sometimes', 'filled', Rule::in(['active', 'inactive', 'morte'])],
            'annee_reine' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'couleur_marquage_reine' => ['nullable', Rule::in(['blanc', 'jaune', 'rouge', 'vert', 'bleu'])],
            'date_installation' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
