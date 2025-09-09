<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\RucheController;
use App\Http\Controllers\RucherController;
use App\Http\Controllers\VisiteController;
use App\Http\Controllers\RecolteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TraitementController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes pour les ruchers
    Route::resource('ruchers', RucherController::class);

    // routes imbriquées pour les ruches
    Route::resource('ruchers.ruches', RucheController::class)
        ->parameters(['ruchers' => 'rucher', 'ruches' => 'ruche']);
    // route pour transhumance
    Route::patch('ruchers/{rucher}/ruches/{ruche}/move', [RucheController::class, 'move'])
        ->name('ruchers.ruches.move');

    // routes visites
    Route::resource('ruchers.ruches.visites', VisiteController::class)
        ->parameters(['ruchers' => 'rucher', 'ruches' => 'ruche', 'visites' => 'visite']);

    // routes traitement
    Route::resource('ruchers.ruches.traitements', TraitementController::class)
        ->parameters(['ruchers' => 'rucher', 'ruches' => 'ruche', 'traitements' => 'traitement']);

    // routes récoltes
    Route::resource('ruchers.ruches.recoltes', RecolteController::class)
        ->parameters(['ruchers' => 'rucher', 'ruches' => 'ruche', 'recoltes' => 'recolte']);
});
