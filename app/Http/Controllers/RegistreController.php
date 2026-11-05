<?php

namespace App\Http\Controllers;

use App\Models\Rucher;
use App\Services\RegistreService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegistreController extends Controller
{
    private const TYPES = ['visite' => 'Visite', 'traitement' => 'Traitement', 'recolte' => 'Récolte', 'deplacement' => 'Déplacement'];

    public function index(Request $request)
    {
        $lignes = RegistreService::pourEquipe($request->user()->current_team_id, now()->subYear()->toDateString());

        return Inertia::render('Registre/Index', [
            'lignes' => $lignes,
            'aUnRucher' => Rucher::where('team_id', $request->user()->current_team_id)->exists(),
            'limite' => $lignes->count() >= RegistreService::LIMITE ? RegistreService::LIMITE : null,
        ]);
    }

    public function exportForm(Request $request)
    {
        return Inertia::render('Registre/Export', [
            'ruchers' => Rucher::where('team_id', $request->user()->current_team_id)->orderBy('nom')->get(['id', 'nom']),
            'annee' => now()->year,
        ]);
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'format' => ['required', 'in:pdf,csv'],
            'depuis' => ['required', 'date_format:Y-m-d'],
            'jusqu' => ['required', 'date_format:Y-m-d', 'after_or_equal:depuis'],
            'rucher_id' => ['nullable', 'integer'],
        ]);

        $teamId = $request->user()->current_team_id;
        $rucherId = null;

        if (! empty($data['rucher_id'])) {
            $rucherId = Rucher::where('team_id', $teamId)->findOrFail($data['rucher_id'])->id;
        }

        $lignes = RegistreService::pourEquipe($teamId, $data['depuis'], $data['jusqu'], $rucherId, limite: null);
        $nom = 'registre-'.$data['depuis'].'-'.$data['jusqu'];

        if ($data['format'] === 'pdf') {
            return Pdf::loadView('pdf.registre', [
                'lignes' => $lignes,
                'types' => self::TYPES,
                'depuis' => $data['depuis'],
                'jusqu' => $data['jusqu'],
                'proprietaire' => $request->user()->name,
            ])->download($nom.'.pdf')->header('Cache-Control', 'private, no-store');
        }

        return response()->streamDownload(function () use ($lignes) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF");
            fputcsv($sortie, ['Date', 'Type', 'Ruche', 'Rucher', 'Détail'], ';', escape: '');

            foreach ($lignes as $ligne) {
                fputcsv($sortie, array_map([$this, 'celluleSure'], [
                    $ligne['date'], self::TYPES[$ligne['type']], $ligne['ruche'], $ligne['rucher'], $ligne['detail'],
                ]), ';', escape: '');
            }

            fclose($sortie);
        }, $nom.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    /**
     * Un tableur exécute une cellule commençant par =, +, - ou @ : on la neutralise.
     */
    private function celluleSure(?string $valeur): string
    {
        $valeur = (string) $valeur;

        return preg_match('/^[=+\-@\t\r]/', $valeur) ? "'".$valeur : $valeur;
    }
}
