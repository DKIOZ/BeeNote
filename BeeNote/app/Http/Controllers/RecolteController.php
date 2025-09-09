<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Recolte;
use Illuminate\Http\Request;
use App\Services\RecolteService;
use App\Http\Requests\StoreRecolteRequest;
use App\Http\Requests\UpdateRecolteRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecolteController extends Controller
{
     use AuthorizesRequests;

    public function index(Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient au rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Récupérer les récoltes via le service
        $recoltes = RecolteService::getRecoltesForRuche($ruche, request()->user());

        return Inertia::render('Recoltes/Index', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'recoltes' => $recoltes
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
        $formData = RecolteService::prepareFormData($ruche, request()->user());

        return Inertia::render('Recoltes/Create', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'formData' => $formData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecolteRequest $request, Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Créer la récolte via le service
        $recolte = RecolteService::createForRuche(
            $request->validated(),
            $ruche,
            $request->user()
        );

        return redirect()->route('ruchers.ruches.recoltes.show', [$rucher->id, $ruche->id, $recolte->id])
            ->with('message', 'Recolte enregistrée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rucher $rucher, Ruche $ruche, Recolte $recolte)
    {
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($recolte->ruche_id !== $ruche->id) {
            abort(404, 'Récolte non trouvée pour cette ruche');
        }

        if ($recolte->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette récolte');
        }

        return Inertia::render('Recoltes/Show', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'recolte' => $recolte
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rucher $rucher, Ruche $ruche, Recolte $recolte)
    {
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($recolte->ruche_id !== $ruche->id) {
            abort(404, 'Récolte non trouvée pour cette ruche');
        }

        if ($recolte->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette récolte');
        }

        $formData = RecolteService::prepareEditData($recolte, request()->user());

        return Inertia::render('Recoltes/Edit', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'recolte' => $recolte,
            'formData' => $formData  // ← Ajouter ceci
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecolteRequest $request, Rucher $rucher, Ruche $ruche, Recolte $recolte)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($recolte->ruche_id !== $ruche->id) {
            abort(404, 'Récolte non trouvée pour cette ruche');
        }

        if ($recolte->team_id !== $request->user()->current_team_id) {
            abort(403, 'Accès refusé à cette récolte');
        }

        // Mettre à jour via le service
        $recolte = RecolteService::updateForRuche($recolte, $request->validated(), $request->user());

        return redirect()->route('ruchers.ruches.recoltes.show', [$rucher->id, $ruche->id, $recolte->id])
            ->with('message', 'Récolte modifiée avec succès !');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rucher $rucher, Ruche $ruche, Recolte $recolte)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifications de cohérence
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        if ($recolte->ruche_id !== $ruche->id) {
            abort(404, 'Récolte non trouvé pour cette ruche');
        }

        if ($recolte->team_id !== request()->user()->current_team_id) {
            abort(403, 'Accès refusé à cette récolte');
        }

        // Supprimer via le service
        RecolteService::deleteForRuche($recolte, request()->user());

        return redirect()->route('ruchers.ruches.recoltes.index', [$rucher->id, $ruche->id])
            ->with('message', 'Récolte supprimé avec succès !');
    }
}
