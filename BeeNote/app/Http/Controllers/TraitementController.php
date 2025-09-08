<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Traitement;
use Illuminate\Http\Request;
use App\Services\TraitementService;
use App\Http\Requests\StoreTraitementRequest;
use App\Http\Requests\UpdateTraitementRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TraitementController extends Controller
{
    use AuthorizesRequests;

    public function index(Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient au rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Récupérer les Traitements via le service
        $traitements = TraitementService::getTraitementsForRuche($ruche, request()->user());

        return Inertia::render('Traitements/Index', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'traitements' => $traitements
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
        $formData = TraitementService::prepareFormData($ruche, request()->user());

        return Inertia::render('Traitements/Create', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'formData' => $formData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTraitementRequest $request, Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Créer le traitement via le service
        $traitement = TraitementService::createForRuche(
            $request->validated(),
            $ruche,
            $request->user()
        );

        return redirect()->route('ruchers.ruches.traitements.show', [$rucher->id, $ruche->id, $traitement->id])
            ->with('message', 'Traitement enregistré avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rucher $rucher, Ruche $ruche, Traitement $traitement)
    {
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($traitement->ruche_id !== $ruche->id) {
            abort(404, 'Traitement non trouvé pour cette ruche');
        }

        if ($traitement->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette traitement');
        }

        return Inertia::render('Traitements/Show', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'traitement' => $traitement
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rucher $rucher, Ruche $ruche, Traitement $traitement)
    {
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($traitement->ruche_id !== $ruche->id) {
            abort(404, 'Traitement non trouvée pour cette ruche');
        }

        if ($traitement->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette traitement');
        }

        $formData = TraitementService::prepareEditData($traitement, request()->user());

        return Inertia::render('Traitements/Edit', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'traitement' => $traitement,
            'formData' => $formData  // ← Ajouter ceci
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTraitementRequest $request, Rucher $rucher, Ruche $ruche, Traitement $traitement)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($traitement->ruche_id !== $ruche->id) {
            abort(404, 'Traitement non trouvé pour cette ruche');
        }

        if ($traitement->team_id !== $request->user()->current_team_id) {
            abort(403, 'Accès refusé à cette traitement');
        }

        // Mettre à jour via le service
        $traitement = TraitementService::updateForRuche($traitement, $request->validated(), $request->user());

        return redirect()->route('ruchers.ruches.traitements.show', [$rucher->id, $ruche->id, $traitement->id])
            ->with('message', 'Traitement modifié avec succès !');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rucher $rucher, Ruche $ruche, Traitement $traitement)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($traitement->ruche_id !== $ruche->id) {
            abort(404, 'Traitement non trouvé pour cette ruche');
        }

        if ($traitement->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette traitement');
        }

        // Supprimer via le service
        TraitementService::deleteForRuche($traitement, request()->user());

        return redirect()->route('ruchers.ruches.traitements.index', [$rucher->id, $ruche->id])
            ->with('message', 'Traitement supprimé avec succès !');
    }
}
