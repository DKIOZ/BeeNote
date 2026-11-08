<?php

namespace App\Http\Controllers;

use App\Models\Rucher;
use App\Services\RegistreService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegistreController extends Controller
{
    public const PDF_MAX = 1000;

    public const PDF_MAX_CARACTERES = 200000;

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
        ], [
            'jusqu.after_or_equal' => 'La date de fin doit être après la date de début.',
            'depuis.date_format' => 'Date de début invalide.',
            'jusqu.date_format' => 'Date de fin invalide.',
        ]);

        $teamId = $request->user()->current_team_id;
        $rucherId = null;

        if (! empty($data['rucher_id'])) {
            $rucherId = Rucher::where('team_id', $teamId)->findOrFail($data['rucher_id'])->id;
        }

        $lignes = RegistreService::pourEquipe($teamId, $data['depuis'], $data['jusqu'], $rucherId, limite: null);
        $nom = 'registre-'.$data['depuis'].'-'.$data['jusqu'];

        if ($data['format'] === 'pdf') {
            // Au-delà, la mise en page PDF épuise la mémoire du serveur. Le tableur n'a pas de limite.
            // Les notes longues pèsent autant que des lignes : on compte aussi le texte.
            if ($lignes->count() > self::PDF_MAX || $lignes->sum(fn ($ligne) => mb_strlen($ligne['detail'])) > self::PDF_MAX_CARACTERES) {
                return redirect()->route('registre.export-form')->withErrors(['format' => 'Plus de '.self::PDF_MAX." lignes sur cette période : choisissez le format tableur, ou une période plus courte."]);
            }

            return Pdf::loadView('pdf.registre', [
                // Dans le PDF, une note est coupée : le tableur la donne en entier.
                'lignes' => $lignes->map(fn ($ligne) => [...$ligne, 'detail' => Str::limit($ligne['detail'], 400)]),
                'types' => self::TYPES,
                'depuis' => $data['depuis'],
                'jusqu' => $data['jusqu'],
                'proprietaire' => $request->user()->name,
            ])->download($nom.'.pdf')->header('Cache-Control', 'private, no-store');
        }

        return response()->streamDownload(function () use ($lignes) {
            $colonnes = [];
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF");
            // Une colonne par information saisie : le tableur peut filtrer et additionner.
            $colonnes = ['notes' => 'Notes', 'produit' => 'Produit', 'cible' => 'Cible', 'dose' => 'Dose', 'lot' => 'Lot', 'miel' => 'Miel', 'poids_kg' => 'Poids (kg)', 'depuis' => 'Depuis', 'vers' => 'Vers'];
            fputcsv($sortie, ['Date', 'Type', 'Ruche', 'Rucher', 'Détail', ...array_values($colonnes)], ';', escape: '');

            foreach ($lignes as $ligne) {
                // Seul le poids est un nombre : un lot « B.24.117 » ou une note gardent leurs points.
                $extra = array_map(fn ($cle) => ! isset($ligne['champs'][$cle]) ? '' : ($cle === 'poids_kg' ? str_replace('.', ',', (string) $ligne['champs'][$cle]) : (string) $ligne['champs'][$cle]), array_keys($colonnes));

                fputcsv($sortie, array_map([$this, 'celluleSure'], [
                    $ligne['date'], self::TYPES[$ligne['type']], $ligne['ruche'], $ligne['rucher'], $ligne['detail'], ...$extra,
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
