<?php

use App\Http\Controllers\CorbeilleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\NoterController;
use App\Http\Controllers\RegistreController;
use App\Http\Controllers\RucheController;
use App\Http\Controllers\RucherController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\TerrainController;
use App\Http\Controllers\VisiteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Un identifiant non numérique répond 404, jamais une erreur de la base.
Route::patterns(['rucher' => '[0-9]+', 'ruche' => '[0-9]+', 'visite' => '[0-9]+', 'tache' => '[0-9]+']);

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : Inertia::render('Welcome'))->name('home');

// Servi depuis la racine : un service worker ne contrôle que ce qui est sous son adresse.
Route::get('/sw.js', function () {
    $fichier = public_path('build/sw.js');
    abort_unless(is_file($fichier), 404);

    return response()->file($fichier, [
        'Content-Type' => 'text/javascript; charset=utf-8',
        'Cache-Control' => 'no-cache',
        'Service-Worker-Allowed' => '/',
    ]);
})->name('service-worker');

Route::get('/manifest.webmanifest', function () {
    $fichier = public_path('build/manifest.webmanifest');
    abort_unless(is_file($fichier), 404);

    return response()->file($fichier, ['Content-Type' => 'application/manifest+json']);
})->name('web-manifest');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('ruchers', RucherController::class);
    Route::get('ruchers/{rucher}/ruches/create', [RucheController::class, 'create'])->name('ruches.create');
    Route::post('ruchers/{rucher}/ruches', [RucheController::class, 'store'])->name('ruches.store');
    Route::resource('ruches', RucheController::class)
        ->only(['show', 'edit', 'update', 'destroy'])
        ->parameters(['ruches' => 'ruche']);
    Route::get('/scan/{token}', [RucheController::class, 'scan'])
        ->where('token', '[A-Za-z0-9]{32}')
        ->middleware('throttle:qr')
        ->name('qr.scan');

    Route::resource('visites', VisiteController::class)
        ->except(['index', 'edit'])
        ->parameters(['visites' => 'visite']);
    Route::get('visites/{visite}/note', [VisiteController::class, 'note'])->name('visites.note');

    Route::get('noter', [NoterController::class, 'create'])->name('noter.create');
    Route::post('noter/{type}', [NoterController::class, 'store'])->name('noter.store');
    Route::get('fiches/{type}/{id}', [NoterController::class, 'show'])->whereNumber('id')->name('fiches.show');
    Route::put('fiches/{type}/{id}', [NoterController::class, 'update'])->whereNumber('id')->name('fiches.update');
    Route::delete('fiches/{type}/{id}', [NoterController::class, 'destroy'])->whereNumber('id')->name('fiches.destroy');

    Route::get('registre', [RegistreController::class, 'index'])->name('registre.index');
    Route::get('registre/telecharger', [RegistreController::class, 'exportForm'])->name('registre.export-form');
    Route::get('registre/fichier', [RegistreController::class, 'export'])->middleware('throttle:export')->name('registre.export');

    Route::get('corbeille', [CorbeilleController::class, 'index'])->name('corbeille.index');
    Route::post('corbeille/{type}/{id}', [CorbeilleController::class, 'restore'])->whereNumber('id')->name('corbeille.restore');

    Route::get('taches/create', [TacheController::class, 'create'])->name('taches.create');
    Route::post('taches', [TacheController::class, 'store'])->name('taches.store');
    Route::patch('taches/{tache}', [TacheController::class, 'toggle'])->name('taches.toggle');

    Route::get('etiquettes', [EtiquetteController::class, 'index'])->name('etiquettes.index');
    Route::inertia('plus', 'Plus')->name('plus');
    Route::inertia('demarrage', 'Demarrage')->name('demarrage');
    Route::inertia('mots', 'Mots')->name('mots');

    Route::get('terrain', [TerrainController::class, 'show'])->name('terrain');
    Route::get('terrain/donnees', [TerrainController::class, 'donnees'])->middleware('throttle:terrain-donnees')->name('terrain.donnees');
    Route::post('terrain/sync', [TerrainController::class, 'sync'])->middleware('throttle:terrain-envoi')->name('terrain.sync');
    Route::post('terrain/visites/{uuid}/note', [TerrainController::class, 'note'])
        ->whereUuid('uuid')
        ->middleware('throttle:terrain-notes')
        ->name('terrain.note');
});
