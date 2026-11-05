<?php

namespace Database\Seeders;

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Tache;
use App\Models\Traitement;
use App\Models\User;
use App\Models\Visite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Données d'essai pour le développement : ne jamais lancer en production.
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        abort_if(app()->isProduction(), 403, 'Données de démonstration interdites en production.');

        $user = User::firstWhere('email', 'demo@beenote.test') ?? User::factory()->withPersonalTeam()->create([
            'name' => 'Apiculteur Démo',
            'email' => 'demo@beenote.test',
            // Mot de passe aléatoire, ou celui fourni par DEMO_PASSWORD pour se connecter.
            'password' => Hash::make(env('DEMO_PASSWORD', Str::random(32))),
            'email_verified_at' => now(),
        ]);

        $team = $user->currentTeam;
        $verger = Rucher::where('team_id', $team->id)->firstWhere('nom', 'Rucher du Verger');

        if ($verger) {
            return;
        }

        $verger = new Rucher(['nom' => 'Rucher du Verger', 'latitude' => 48.8566, 'longitude' => 2.3522]);
        $verger->team_id = $team->id;
        $verger->save();

        $genets = new Rucher(['nom' => 'Rucher des Genêts']);
        $genets->team_id = $team->id;
        $genets->save();

        $couleurs = ['#E8A400', '#2F6DB5', '#6DB58A'];
        $ruches = collect(range(1, 3))->map(fn ($i) => $verger->ruches()->create(['nom' => "Ruche {$i}", 'couleur' => $couleurs[$i - 1], 'annee_reine' => 2025, 'couleur_marquage_reine' => 'bleu']));
        collect(range(4, 6))->each(fn ($i) => $genets->ruches()->create(['nom' => "Ruche {$i}"]));

        $visite = fn (Ruche $ruche, int $jours, array $champs) => tap(new Visite([
            'date_visite' => now()->subDays($jours)->toDateString(),
            'humeur_colonie' => 'calme',
            ...$champs,
        ]), fn (Visite $v) => $v->forceFill(['ruche_id' => $ruche->id, 'user_id' => $user->id, 'team_id' => $team->id])->save());

        $visite($ruches[0], 0, ['reine_vue' => 'ok', 'hausse_ajoutee' => true, 'prochaine_visite_prevue' => now()->addDays(14)->toDateString()]);
        $visite($ruches[1], 14, ['reine_vue' => 'non_ok', 'reserves_miel' => 'suffisantes', 'prochaine_visite_prevue' => now()->subDays(7)->toDateString()]);
        $visite($ruches[2], 14, ['humeur_colonie' => 'agitee', 'nourrissement_effectue' => true, 'traitement_applique' => true, 'prochaine_visite_prevue' => now()->toDateString()]);

        $tache = new Tache(['titre' => 'Contrôler la reine', 'type' => 'autre', 'date_prevue' => now()->subDays(7)->toDateString()]);
        $tache->forceFill(['user_id' => $user->id, 'team_id' => $team->id, 'ruche_id' => $ruches[1]->id, 'rucher_id' => $verger->id])->save();

        $traitement = new Traitement(['date_traitement' => now()->subMonths(4)->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Acide oxalique', 'dosage' => '5 ml']);
        $traitement->forceFill(['user_id' => $user->id, 'team_id' => $team->id, 'ruche_id' => $ruches[1]->id])->save();

        $recolte = new Recolte(['date_recolte' => now()->subMonths(3)->toDateString(), 'type_miel' => 'toutes_fleurs', 'quantite_kg' => 14]);
        $recolte->forceFill(['user_id' => $user->id, 'team_id' => $team->id, 'ruche_id' => $ruches[1]->id])->save();
    }
}
