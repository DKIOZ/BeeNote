<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\RucheController;
use App\Http\Controllers\RucherController;
use App\Http\Controllers\DashboardController;

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

    // ✅ Ajouter cette ligne :
    Route::resource('ruchers', RucherController::class);

    // ✅ Nouvelle ligne : routes imbriquées
    Route::resource('ruchers.ruches', RucheController::class)
        ->parameters(['ruchers' => 'rucher', 'ruches' => 'ruche']);
    // ✅ Nouvelle route pour transhumance
    Route::patch('ruchers/{rucher}/ruches/{ruche}/move', [RucheController::class, 'move'])
        ->name('ruchers.ruches.move');
});
