<?php

namespace App\Http\Controllers;

use App\Models\Rucher;
use Illuminate\Http\Request;
use App\Services\RucherService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreRucherRequest;
use Inertia\Inertia;  // ✅ Import Inertia
use App\Http\Requests\UpdateRucherRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RucherController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Rucher::class);

        $ruchers = RucherService::getRuchersForTeam(request()->user())
            ->map(function ($rucher) {
                $rucher->ruches_count = $rucher->ruches->count();
                $rucher->ruches_actives_count = $rucher->ruches->where('statut', 'active')->count();
                $rucher->visites_count = $rucher->ruches->sum(fn($ruche) => $ruche->visites->count());
                $rucher->derniere_visite = $rucher->ruches->flatMap->visites->max('date_visite');

                return $rucher;
            });

        return Inertia::render('Ruchers/Index', [
            'ruchers' => $ruchers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Rucher::class);

        return Inertia::render('Ruchers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRucherRequest  $request)
    {
        $this->authorize('create', Rucher::class);

        RucherService::createForTeam(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('ruchers.index')
            ->with('message', 'Rucher créé avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rucher $rucher)
    {
        $this->authorize('view', $rucher);

        // Charger les ruches du rucher
        $ruches = $rucher->ruches;

        return Inertia::render('Ruchers/Show', [
            'rucher' => $rucher,
            'ruches' => $ruches  // ✅ Passer les ruches
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rucher $rucher)
    {
        $this->authorize('update', $rucher);

        return Inertia::render('Ruchers/Edit', [
            'rucher' => $rucher
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRucherRequest $request, Rucher $rucher)
    {
        $this->authorize('update', $rucher);

        $rucher = RucherService::updateForTeam($rucher, $request->validated(), $request->user());

        return redirect()->route('ruchers.show', $rucher)
            ->with('message', 'Rucher modifié avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rucher $rucher)
    {
        $this->authorize('delete', $rucher);

        RucherService::deleteForTeam($rucher, request()->user());

        return redirect()->route('ruchers.index')
            ->with('message', 'Rucher supprimé avec succès !');
    }
}
