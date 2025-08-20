<?php

namespace App\Http\Controllers;

use App\Models\Rucher;
use Illuminate\Http\Request;
use App\Services\RucherService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreRucherRequest;
use Inertia\Inertia;  // ✅ Import Inertia

class RucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupère les ruchers de l'équipe active de l'utilisateur connecté, ou une collection vide si problème
        $ruchers = request()->user()->currentTeam->ruchers ?? collect();

        return Inertia::render('Ruchers/Index', [
            'ruchers' => $ruchers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Ruchers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRucherRequest  $request)
    {
        $rucher = RucherService::createForTeam(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('ruchers.index')
            ->with('message', 'Rucher créé avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
