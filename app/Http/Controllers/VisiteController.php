<?php

namespace App\Http\Controllers;

use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Visite;
use App\Services\WeatherService;
use App\Support\VisiteFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VisiteController extends Controller
{
    /**
     * Saisie d'une visite après coup, depuis le PC ou le téléphone.
     */
    public function create(Request $request)
    {
        return Inertia::render('Visites/Create', [
            'ruchers' => $this->ruchesParRucher($request),
            'rucheId' => $request->integer('ruche') ?: null,
            'aujourdhui' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $ruche = Ruche::findOrFail($data['ruche_id']);
        $this->authorizeRuche($ruche, 'update');

        $visite = new Visite(VisiteFields::clean($data));
        $visite->ruche_id = $ruche->id;
        $visite->user_id = $request->user()->id;
        $visite->team_id = $request->user()->current_team_id;
        $visite->save();

        dispatch(fn () => WeatherService::completeVisite($visite))->afterResponse();

        return redirect()->route('ruches.show', $ruche)->with('message', 'Visite enregistrée.');
    }

    public function show(Request $request, Visite $visite)
    {
        $this->authorizeVisite($visite);
        $visite->load('ruche.rucher:id,nom,team_id');

        return Inertia::render('Visites/Show', [
            'visite' => $visite,
            'ruchers' => $this->ruchesParRucher($request),
            'modifiee' => $visite->updated_at->gt($visite->created_at->addMinute()) ? $visite->updated_at->toDateString() : null,
            'peutSupprimer' => $request->user()->can('delete', $visite->ruche->rucher),
        ]);
    }

    public function update(Request $request, Visite $visite)
    {
        $this->authorizeVisite($visite, 'update');

        $data = $request->validate($this->rules());
        $ruche = Ruche::findOrFail($data['ruche_id']);
        $this->authorizeRuche($ruche, 'update');

        $visite->fill(VisiteFields::clean($data));
        $visite->ruche_id = $ruche->id;
        $visite->save();

        return redirect()->route('ruches.show', $ruche)->with('message', 'Visite corrigée.');
    }

    public function destroy(Visite $visite)
    {
        $this->authorizeVisite($visite, 'delete');
        $rucheId = $visite->ruche_id;
        $visite->delete();

        return redirect()->route('ruches.show', $rucheId)->with('message', 'Visite mise à la corbeille.');
    }

    public function note(Visite $visite)
    {
        $this->authorizeVisite($visite);
        abort_unless($visite->note_audio_path && Storage::disk('local')->exists($visite->note_audio_path), 404);

        return response()->file(Storage::disk('local')->path($visite->note_audio_path), [
            'Content-Disposition' => 'inline; filename="note.audio"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    // Au PC, la date est celle du jour ou d'avant : la tolérance d'un jour ne vaut que pour le téléphone.
    private function rules(): array
    {
        return [
            ...VisiteFields::rules(),
            'date_visite' => ['required', 'date', 'before_or_equal:today', 'after:2000-01-01'],
            'ruche_id' => ['required', 'integer'],
        ];
    }

    private function authorizeVisite(Visite $visite, string $ability = 'view'): void
    {
        abort_unless($visite->ruche, 404);
        abort_unless($visite->team_id === request()->user()->current_team_id, 404);
        $this->authorizeRuche($visite->ruche, $ability);
    }

    private function ruchesParRucher(Request $request)
    {
        return Rucher::where('team_id', $request->user()->current_team_id)
            ->with(['ruches' => fn ($q) => $q->orderBy('id')->select('id', 'nom', 'rucher_id')])
            ->orderBy('nom')
            ->get(['id', 'nom']);
    }
}
