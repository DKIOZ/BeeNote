<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\RucherService;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Dashboard', [
            'stats' => DashboardService::getStats($request->user()),
            'ruchersRecents' => DashboardService::getRecentRuchers($request->user()),
            'ruchesRecentes' => DashboardService::getRecentRuches($request->user()),
        ]);
    }
}
