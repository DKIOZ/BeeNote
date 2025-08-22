<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Ruche;
use App\Models\Rucher;
use Illuminate\Http\Request;
use App\Services\RucheService;
use App\Services\RucherService;
use App\Http\Requests\MoveRucheRequest;
use App\Http\Requests\StoreRucheRequest;
use App\Http\Requests\UpdateRucheRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RucheController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Rucher $rucher)
    {
        $this->authorize('view', $rucher);

        // Récupérer les ruches de ce rucher
        $ruches = $rucher->ruches;

        return Inertia::render('Ruches/Index', [
            'rucher' => $rucher,
            'ruches' => $ruches
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Rucher $rucher)
    {
        // Vérifier que le rucher appartient à la team
        $this->authorize('view', $rucher);

        return Inertia::render('Ruches/Create', [
            'rucher' => $rucher
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRucheRequest $request, Rucher $rucher)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Créer la ruche via le service
        $ruche = RucheService::createForRucher(
            $request->validated(),
            $rucher,
            $request->user()
        );

        return redirect()->route('ruchers.ruches.index', $rucher)
            ->with('message', 'Ruche créée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rucher $rucher, Ruche $ruche)
    {
        $this->authorize('view', $rucher);

        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // ✅ Récupérer tous les ruchers de la team pour le déplacement
        $availableRuchers = RucherService::getRuchersForTeam(request()->user())
            ->where('id', '!=', $rucher->id)  // Exclure le rucher actuel
            ->values();  // Réindexer le tableau

        return Inertia::render('Ruches/Show', [
            'rucher' => $rucher,
            'ruche' => $ruche,
            'availableRuchers' => $availableRuchers  // ✅ Passer les ruchers
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rucher $rucher, Ruche $ruche)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient bien à ce rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        return Inertia::render('Ruches/Edit', [
            'rucher' => $rucher,
            'ruche' => $ruche
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRucheRequest $request, Rucher $rucher, Ruche $ruche)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient bien à ce rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Mettre à jour la ruche via le service
        $ruche = RucheService::updateForRucher($ruche, $request->validated(), $request->user());

        return redirect()->route('ruchers.ruches.show', [$rucher->id, $ruche->id])
            ->with('message', 'Ruche modifiée avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rucher $rucher, Ruche $ruche)
    {
        // Vérifier l'accès au rucher
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient bien à ce rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Supprimer la ruche via le service
        RucheService::deleteForRucher($ruche, request()->user());

        return redirect()->route('ruchers.ruches.index', $rucher)
            ->with('message', "Ruche {$ruche->nom} supprimée avec succès !");
    }


    public function move(MoveRucheRequest $request, Rucher $rucher, Ruche $ruche)
    {
        // Vérifier l'accès au rucher actuel
        $this->authorize('view', $rucher);

        // Vérifier que la ruche appartient bien à ce rucher
        if ($ruche->rucher_id !== $rucher->id) {
            abort(404, 'Ruche non trouvée dans ce rucher');
        }

        // Récupérer le rucher de destination
        $newRucher = Rucher::findOrFail($request->rucher_id);

        // Vérifier l'accès au rucher de destination
        $this->authorize('view', $newRucher);

        // Déplacer la ruche
        $ruche = RucheService::moveToRucher($ruche, $newRucher, $request->user());

        return redirect()->route('ruchers.ruches.show', [$newRucher->id, $ruche->id])
            ->with('message', "Ruche {$ruche->nom} déplacée vers {$newRucher->nom} avec succès !");
    }
}
