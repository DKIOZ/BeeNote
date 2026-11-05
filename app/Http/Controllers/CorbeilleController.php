<?php

namespace App\Http\Controllers;

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Rucher;
use App\Models\Traitement;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CorbeilleController extends Controller
{
    private const MODELES = [
        'visite' => Visite::class,
        'traitement' => Traitement::class,
        'recolte' => Recolte::class,
        'deplacement' => RucheEmplacement::class,
    ];

    public function index(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $items = collect();

        foreach (Rucher::onlyTrashed()->where('team_id', $teamId)->get() as $rucher) {
            $items->push($this->item('rucher', $rucher, 'Rucher', $rucher->nom));
        }

        // Une ruche partie avec son rucher reviendra avec lui : elle n'apparaît pas séparément.
        $ruches = Ruche::onlyTrashed()->whereHas('rucher', fn ($q) => $q->where('team_id', $teamId))->with('rucher:id,nom')->get();
        foreach ($ruches as $ruche) {
            $items->push($this->item('ruche', $ruche, 'Ruche', $ruche->nom.' · '.$ruche->rucher->nom));
        }

        foreach (self::MODELES as $type => $modele) {
            $lignes = $modele::onlyTrashed()->where('team_id', $teamId)->whereHas('ruche')->with('ruche:id,nom')->get();
            foreach ($lignes as $ligne) {
                $items->push($this->item($type, $ligne, ucfirst($type === 'recolte' ? 'récolte' : ($type === 'deplacement' ? 'déplacement' : $type)), $ligne->ruche->nom));
            }
        }

        return Inertia::render('Corbeille', [
            'items' => $items->sortByDesc('supprime_le')->values(),
            'jours' => config('beenote.corbeille_jours'),
        ]);
    }

    public function restore(Request $request, string $type, int $id)
    {
        $teamId = $request->user()->current_team_id;

        if ($type === 'rucher') {
            $rucher = Rucher::onlyTrashed()->where('team_id', $teamId)->findOrFail($id);
            $this->authorizeRestauration($request, $rucher);

            DB::transaction(function () use ($rucher) {
                $supprimeLe = $rucher->deleted_at;
                $rucher->restore();
                Ruche::onlyTrashed()->where('rucher_id', $rucher->id)->where('deleted_at', $supprimeLe)->restore();
            });
        } elseif ($type === 'ruche') {
            $ruche = Ruche::onlyTrashed()->whereHas('rucher', fn ($q) => $q->where('team_id', $teamId))->findOrFail($id);
            $this->authorizeRestauration($request, $ruche->rucher);
            $ruche->restore();
        } else {
            abort_unless(isset(self::MODELES[$type]), 404);
            $ligne = self::MODELES[$type]::onlyTrashed()->where('team_id', $teamId)->whereHas('ruche')->findOrFail($id);
            $this->authorizeRestauration($request, $ligne->ruche->rucher);

            DB::transaction(function () use ($ligne, $type, $teamId) {
                $ligne->restore();

                if ($type === 'deplacement' && $ligne->vers_rucher_id && Rucher::where('team_id', $teamId)->whereKey($ligne->vers_rucher_id)->exists()) {
                    $dernier = RucheEmplacement::where('ruche_id', $ligne->ruche_id)->orderByDesc('id')->first();

                    if ($dernier->is($ligne)) {
                        $ligne->ruche->placerDans($ligne->vers_rucher_id);
                    }
                }
            });
        }

        return back()->with('message', 'Remis en place.');
    }

    private function authorizeRestauration(Request $request, Rucher $rucher): void
    {
        abort_unless($request->user()->hasTeamPermission($rucher->team, 'delete'), 403);
    }

    private function item(string $type, $modele, string $titre, string $detail): array
    {
        return [
            'type' => $type,
            'id' => $modele->id,
            'titre' => $titre,
            'detail' => $detail,
            'supprime_le' => $modele->deleted_at->toDateString(),
        ];
    }
}
