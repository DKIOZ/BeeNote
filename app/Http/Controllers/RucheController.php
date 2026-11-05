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
            'suggestion' => 'Ruche '.(collect($rucher->ruches()->pluck('nom'))->map(fn ($nom) => RucherOverview::numeroDuNom($nom) ?? 0)->push($rucher->ruches()->count())->max() + 1),
        ]);
    }

    public function store(Request $request, Rucher $rucher)
    {
        $this->authorize('update', $rucher);
        $ruche = $rucher->ruches()->create($request->validate($this->rules()));

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
        $ruche->update($request->validate($this->rules()));

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

        return redirect()->route('ruches.show', $ruche);
    }

    private function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:50'],
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
