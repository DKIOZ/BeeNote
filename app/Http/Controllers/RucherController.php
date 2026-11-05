<?php

namespace App\Http\Controllers;

use App\Models\Rucher;
use App\Services\RucherOverview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RucherController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Rucher::class);

        return Inertia::render('Ruchers/Index', ['ruchers' => RucherOverview::ruchers($request->user())]);
    }

    public function create()
    {
        $this->authorize('create', Rucher::class);

        return Inertia::render('Ruchers/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Rucher::class);

        $data = $request->validate([
            ...$this->rules(),
            'nombre_ruches' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $rucher = DB::transaction(function () use ($data, $request) {
            $rucher = new Rucher($data);
            $rucher->team_id = $request->user()->current_team_id;
            $rucher->save();

            for ($i = 1; $i <= $data['nombre_ruches']; $i++) {
                $rucher->ruches()->create(['nom' => "Ruche {$i}"]);
            }

            return $rucher;
        });

        return redirect()->route('ruchers.show', $rucher)->with('message', 'Rucher créé.');
    }

    public function show(Request $request, Rucher $rucher)
    {
        $this->authorize('view', $rucher);

        return Inertia::render('Ruchers/Show', [
            'rucher' => RucherOverview::ruchers($request->user())->firstWhere('id', $rucher->id),
            'peutSupprimer' => $request->user()->can('delete', $rucher),
        ]);
    }

    public function edit(Request $request, Rucher $rucher)
    {
        $this->authorize('update', $rucher);

        return Inertia::render('Ruchers/Edit', [
            'rucher' => $rucher->only(['id', 'nom', 'localisation', 'latitude', 'longitude', 'description']),
            'peutSupprimer' => $request->user()->can('delete', $rucher),
        ]);
    }

    public function update(Request $request, Rucher $rucher)
    {
        $this->authorize('update', $rucher);
        $rucher->update($request->validate($this->rules()));

        return redirect()->route('ruchers.show', $rucher)->with('message', 'Rucher enregistré.');
    }

    public function destroy(Rucher $rucher)
    {
        $this->authorize('delete', $rucher);

        DB::transaction(function () use ($rucher) {
            // Ses ruches partent avec lui et reviendront avec lui, à la même heure.
            $rucher->delete();
            $rucher->ruches()->update(['deleted_at' => $rucher->deleted_at]);
        });

        return redirect()->route('ruchers.index')->with('message', 'Rucher mis à la corbeille.');
    }

    private function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
