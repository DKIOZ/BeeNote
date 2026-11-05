<?php

namespace App\Http\Controllers;

use App\Models\Ruche;
use App\Models\Tache;
use App\Models\Visite;
use App\Services\RucherOverview;
use App\Services\WeatherService;
use App\Support\VisiteFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Mode terrain : page unique gardée sur le téléphone, données en local, envoi au retour du réseau.
 */
class TerrainController extends Controller
{
    public function show()
    {
        return Inertia::render('Terrain');
    }

    public function donnees(Request $request)
    {
        return response()->json([
            'ruchers' => RucherOverview::ruchers($request->user()),
            'aujourdhui' => now()->toDateString(),
        ])->header('Cache-Control', 'private, no-store');
    }

    /**
     * Reçoit un lot de visites. Chaque visite est traitée seule : une erreur n'en bloque pas d'autres,
     * et renvoyer le même lot ne crée jamais de doublon.
     */
    public function sync(Request $request)
    {
        $request->validate(['visites' => ['present', 'array', 'max:100']]);

        $user = $request->user();
        $resultats = [];

        foreach ($request->input('visites', []) as $item) {
            $resultats[] = $this->synchroniser(is_array($item) ? $item : [], $user);
        }

        return response()->json(['resultats' => $resultats]);
    }

    private function synchroniser(array $item, $user): array
    {
        $uuid = $item['client_uuid'] ?? null;

        $validateur = Validator::make($item, [
            'client_uuid' => ['required', 'uuid'],
            'ruche_id' => ['required', 'integer'],
            'taches_faites' => ['nullable', 'array', 'max:100'],
            'taches_faites.*' => ['integer'],
            ...VisiteFields::rules(),
        ]);

        if ($validateur->fails()) {
            return ['client_uuid' => $uuid, 'statut' => 'refusee', 'erreurs' => $validateur->errors()->keys()];
        }

        $ruche = Ruche::whereKey($item['ruche_id'])
            ->whereHas('rucher', fn ($q) => $q->where('team_id', $user->current_team_id))
            ->first();

        if (! $ruche || ! $user->can('update', $ruche->rucher)) {
            return ['client_uuid' => $uuid, 'statut' => 'refusee', 'erreurs' => ['ruche_id']];
        }

        $visite = Visite::withTrashed()->where('team_id', $user->current_team_id)->where('client_uuid', $uuid)->first();

        if ($visite) {
            return ['client_uuid' => $uuid, 'statut' => 'deja_recue', 'id' => $visite->id];
        }

        $donnees = $validateur->validated();
        $visite = new Visite(VisiteFields::clean($donnees));

        // Une tâche n'est soldée que si sa visite est bien acceptée.
        DB::transaction(function () use ($visite, $donnees, $uuid, $ruche, $user) {
            $visite->forceFill([
                'client_uuid' => $uuid,
                'ruche_id' => $ruche->id,
                'user_id' => $user->id,
                'team_id' => $user->current_team_id,
            ])->save();

            Tache::where('team_id', $user->current_team_id)
                ->whereIn('id', $donnees['taches_faites'] ?? [])
                ->update(['statut' => 'terminee', 'date_realisee' => $visite->date_visite->toDateString()]);
        });

        dispatch(fn () => WeatherService::completeVisite($visite))->afterResponse();

        return ['client_uuid' => $uuid, 'statut' => 'recue', 'id' => $visite->id];
    }

    public function note(Request $request, string $uuid)
    {
        $data = $request->validate([
            'note' => ['required', 'file', 'max:15360', 'mimetypes:audio/webm,video/webm,audio/ogg,application/ogg,audio/mp4,video/mp4,audio/x-m4a,audio/m4a,audio/aac,audio/mpeg,audio/wav,audio/x-wav'],
            'duree' => ['nullable', 'integer', 'min:0', 'max:3600'],
        ]);

        $visite = Visite::where('team_id', $request->user()->current_team_id)->where('client_uuid', $uuid)->firstOrFail();
        abort_unless($visite->ruche, 404);
        $this->authorizeRuche($visite->ruche, 'update');

        // Le nom vient du serveur : rien du fichier envoyé n'entre dans le chemin.
        $dossier = 'notes-audio/'.$visite->team_id;
        $this->verifierQuota($dossier, $data['note']->getSize());

        $chemin = $dossier.'/'.Str::random(40);
        Storage::disk('local')->put($chemin, file_get_contents($data['note']->getRealPath()));

        if ($visite->note_audio_path) {
            Storage::disk('local')->delete($visite->note_audio_path);
        }

        $visite->forceFill(['note_audio_path' => $chemin, 'note_audio_duree' => $data['duree'] ?? null])->save();

        return response()->json(['statut' => 'recue']);
    }

    private function verifierQuota(string $dossier, int $taille): void
    {
        $disque = Storage::disk('local');
        $occupe = collect($disque->files($dossier))->sum(fn (string $fichier) => $disque->size($fichier));

        if ($occupe + $taille > config('beenote.quota_audio_mo') * 1024 * 1024) {
            throw ValidationException::withMessages(['note' => "L'espace réservé aux notes vocales est plein."]);
        }
    }
}
