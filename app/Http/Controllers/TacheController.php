<?php

namespace App\Http\Controllers;

use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Tache;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TacheController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('Taches/Create', [
            'ruchers' => Rucher::where('team_id', $request->user()->current_team_id)
                ->with(['ruches' => fn ($q) => $q->orderBy('id')->select('id', 'nom', 'rucher_id')])
                ->orderBy('nom')
                ->get(['id', 'nom']),
            'rucheId' => $request->integer('ruche') ?: null,
            'aujourdhui' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:200'],
            'date_prevue' => ['required', 'date'],
            'ruche_id' => ['nullable', 'integer', 'required_without:rucher_id'],
            'rucher_id' => ['nullable', 'integer'],
        ]);

        $tache = new Tache(['titre' => $data['titre'], 'date_prevue' => $data['date_prevue'], 'type' => 'autre']);

        if (! empty($data['ruche_id'])) {
            $ruche = Ruche::findOrFail($data['ruche_id']);
            $this->authorizeRuche($ruche, 'update');
            $tache->ruche_id = $ruche->id;
            $tache->rucher_id = $ruche->rucher_id;
        } else {
            $rucher = Rucher::findOrFail($data['rucher_id']);
            $this->authorize('update', $rucher);
            $tache->rucher_id = $rucher->id;
        }

        $tache->user_id = $request->user()->id;
        $tache->team_id = $request->user()->current_team_id;
        $tache->save();

        return redirect()->route('dashboard')->with('message', 'Tâche ajoutée.');
    }

    public function toggle(Request $request, Tache $tache)
    {
        abort_unless($tache->team_id === $request->user()->current_team_id, 403);
        abort_unless($tache->rucher, 404);
        $this->authorize('update', $tache->rucher);

        $faite = $tache->statut !== 'terminee';
        $tache->forceFill([
            'statut' => $faite ? 'terminee' : 'a_faire',
            'date_realisee' => $faite ? now()->toDateString() : null,
        ])->save();

        return back();
    }
}
