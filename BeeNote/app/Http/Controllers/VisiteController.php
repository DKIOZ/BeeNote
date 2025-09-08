<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Visite;
use Illuminate\Http\Request;
use App\Services\VisiteService;
use App\Http\Requests\StoreVisiteRequest;
use App\Http\Requests\UpdateVisiteRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VisiteController extends Controller
{
    use AuthorizesRequests;

    public function index(Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient au rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Récupérer les visites via le service
        $visites = VisiteService::getVisitesForRuche($ruche, request()->user());

        return Inertia::render('Visites/Index', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'visites' => $visites
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Préparer les données du formulaire (pré-remplissage intelligent)
        $formData = VisiteService::prepareFormData($ruche, request()->user());

        return Inertia::render('Visites/Create', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'formData' => $formData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVisiteRequest $request, Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Créer la visite via le service
        $visite = VisiteService::createForRuche(
            $request->validated(),
            $ruche,
            $request->user()
        );

        return redirect()->route('ruchers.ruches.visites.show', [$rucher->id, $ruche->id, $visite->id])
            ->with('message', 'Visite enregistrée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rucher $rucher, Ruche $ruche, Visite $visite)
    {
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($visite->ruche_id !== $ruche->id) {
            abort(404, 'Visite non trouvée pour cette ruche');
        }

        if ($visite->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette visite');
        }

        return Inertia::render('Visites/Show', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'visite' => $visite
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rucher $rucher, Ruche $ruche, Visite $visite)
    {
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($visite->ruche_id !== $ruche->id) {
            abort(404, 'Visite non trouvée pour cette ruche');
        }

        if ($visite->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette visite');
        }

        $formData = VisiteService::prepareEditData($visite, request()->user());

        return Inertia::render('Visites/Edit', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'visite' => $visite,
            'formData' => $formData  // ← Ajouter ceci
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVisiteRequest $request, Rucher $rucher, Ruche $ruche, Visite $visite)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($visite->ruche_id !== $ruche->id) {
            abort(404, 'Visite non trouvée pour cette ruche');
        }

        if ($visite->team_id !== $request->user()->current_team_id) {
            abort(403, 'Accès refusé à cette visite');
        }

        // Mettre à jour via le service
        $visite = VisiteService::updateForRuche($visite, $request->validated(), $request->user());

        return redirect()->route('ruchers.ruches.visites.show', [$rucher->id, $ruche->id, $visite->id])
            ->with('message', 'Visite modifiée avec succès !');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rucher $rucher, Ruche $ruche, Visite $visite)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($visite->ruche_id !== $ruche->id) {
            abort(404, 'Visite non trouvée pour cette ruche');
        }

        if ($visite->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette visite');
        }

        // Supprimer via le service
        VisiteService::deleteForRuche($visite, request()->user());

        return redirect()->route('ruchers.ruches.visites.index', [$rucher->id, $ruche->id])
            ->with('message', 'Visite supprimée avec succès !');
    }
}
