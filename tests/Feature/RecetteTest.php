<?php

use App\Actions\Jetstream\DeleteUser;
use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Rucher;
use App\Models\Tache;
use App\Models\User;
use App\Models\Visite;
use App\Services\RegistreService;
use App\Services\RucherOverview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function compte(): User
{
    return User::factory()->withPersonalTeam()->create();
}

function rucherAvec(User $user, array $noms = ['Ruche 1'], string $nom = 'Le verger'): Rucher
{
    $rucher = Rucher::factory()->create(['team_id' => $user->currentTeam->id, 'nom' => $nom]);

    foreach ($noms as $ruche) {
        Ruche::factory()->create(['rucher_id' => $rucher->id, 'nom' => $ruche]);
    }

    return $rucher->load('ruches');
}

function visiteDe(Ruche $ruche, User $user, array $attributs = []): Visite
{
    $visite = new Visite(['date_visite' => now()->toDateString(), ...$attributs]);
    $visite->forceFill(['ruche_id' => $ruche->id, 'user_id' => $user->id, 'team_id' => $user->current_team_id])->save();

    return $visite;
}

function tacheDe(User $user, Ruche $ruche, array $attributs = []): Tache
{
    $tache = new Tache(['titre' => 'Poser une hausse', 'date_prevue' => now()->toDateString(), 'type' => 'autre', ...$attributs]);
    $tache->forceFill(['team_id' => $user->current_team_id, 'user_id' => $user->id, 'ruche_id' => $ruche->id, 'rucher_id' => $ruche->rucher_id])->save();

    return $tache;
}

// Un vrai fichier : le contrôle du format lit son contenu, pas son nom.
function fichierSon(string $contenu): UploadedFile
{
    $chemin = tempnam(sys_get_temp_dir(), 'son');
    file_put_contents($chemin, $contenu);

    return new UploadedFile($chemin, 'note', null, null, true);
}

beforeEach(function () {
    Http::fake();
    Storage::fake('local');
});

describe('sécurité', function () {
    it('efface les notes vocales à la suppression du compte', function () {
        $user = compte();
        $chemin = 'notes-audio/'.$user->currentTeam->id.'/son';
        Storage::disk('local')->put($chemin, 'son');

        app(DeleteUser::class)->delete($user->fresh());

        Storage::disk('local')->assertMissing($chemin);
    });

    it('efface les notes des ruches purgées de la corbeille', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $visite = visiteDe($ruche, $user);
        $chemin = 'notes-audio/'.$user->current_team_id.'/son';
        Storage::disk('local')->put($chemin, 'son');
        $visite->forceFill(['note_audio_path' => $chemin])->save();

        $ruche->delete();
        Ruche::onlyTrashed()->update(['deleted_at' => now()->subDays(40)]);

        $this->artisan('beenote:vider-corbeille')->assertOk();

        Storage::disk('local')->assertMissing($chemin);
    });

    it('refuse de créer une équipe ou d\'ajouter un membre', function () {
        $user = compte();
        $autre = compte();

        $this->actingAs($user)->post('/teams', ['name' => 'Autre'])->assertForbidden();
        $this->actingAs($user)->post('/teams/'.$user->current_team_id.'/members', ['email' => $autre->email, 'role' => 'admin'])->assertForbidden();

        expect($user->currentTeam->fresh()->users)->toHaveCount(0);
    });

    it('répond pareil au mot de passe oublié, que le compte existe ou non', function () {
        Notification::fake();
        $user = compte();

        $existe = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $inconnu = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'personne@exemple.fr']);

        $existe->assertSessionHasNoErrors();
        $inconnu->assertSessionHasNoErrors();
        expect($inconnu->getSession()->get('status'))->toBe($existe->getSession()->get('status'));
    });

    it('limite les essais répétés à l\'inscription', function () {
        foreach (range(1, 10) as $essai) {
            $this->post('/register', ['email' => 'pas-une-adresse'])->assertStatus(302);
        }

        $this->post('/register', ['email' => 'pas-une-adresse'])->assertStatus(429);
    });

    it('ne garde pas les pages du compte dans le cache du navigateur', function () {
        $this->actingAs(compte())->get('/dashboard')->assertHeader('Cache-Control', 'no-store, private');
    });

    it('refuse une date mal formée à l\'export sans erreur serveur', function () {
        $this->actingAs(compte())
            ->from('/registre/telecharger')
            ->get('/registre/fichier?format=csv&depuis=01/01/2026&jusqu=2026-12-31')
            ->assertSessionHasErrors('depuis');
    });

    it('refuse une note quand l\'espace de l\'équipe est plein', function () {
        config(['beenote.quota_audio_mo' => 1]);
        $user = compte();
        $visite = visiteDe(rucherAvec($user)->ruches->first(), $user);
        $uuid = (string) Str::uuid();
        $visite->forceFill(['client_uuid' => $uuid])->save();
        Storage::disk('local')->put('notes-audio/'.$user->current_team_id.'/plein', str_repeat('a', 1024 * 1024));

        $this->actingAs($user)
            ->postJson(route('terrain.note', $uuid), ['note' => UploadedFile::fake()->createWithContent('note', "\x1A\x45\xDF\xA3webm")])
            ->assertStatus(422);
    });

    it('un membre en lecture seule ne peut pas écrire', function () {
        $proprietaire = compte();
        $ruche = rucherAvec($proprietaire)->ruches->first();
        $lecteur = compte();
        $proprietaire->currentTeam->users()->attach($lecteur, ['role' => 'lecteur']);
        $lecteur->forceFill(['current_team_id' => $proprietaire->current_team_id])->save();

        $this->actingAs($lecteur->fresh())
            ->post(route('visites.store'), ['ruche_id' => $ruche->id, 'date_visite' => now()->toDateString()])
            ->assertForbidden();

        $reponse = $this->actingAs($lecteur->fresh())->postJson(route('terrain.sync'), ['visites' => [[
            'client_uuid' => (string) Str::uuid(),
            'ruche_id' => $ruche->id,
            'date_visite' => now()->toDateString(),
        ]]]);

        expect($reponse->json('resultats.0.statut'))->toBe('refusee');
        expect(Visite::count())->toBe(0);
    });
});

describe('envoi depuis le terrain', function () {
    it('accepte une note au format de Safari', function () {
        $user = compte();
        $visite = visiteDe(rucherAvec($user)->ruches->first(), $user);
        $uuid = (string) Str::uuid();
        $visite->forceFill(['client_uuid' => $uuid])->save();
        $m4a = "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00M4A mp42isom".str_repeat("\x00", 64);

        $this->actingAs($user)
            ->postJson(route('terrain.note', $uuid), ['note' => fichierSon($m4a)])
            ->assertOk();

        expect($visite->fresh()->note_audio_path)->not->toBeNull();
    });

    it('ne solde les tâches que si la visite est acceptée', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $acceptee = tacheDe($user, $ruche);
        $refusee = tacheDe($user, $ruche);
        $date = now()->toDateString();

        $this->actingAs($user)->postJson(route('terrain.sync'), ['visites' => [
            ['client_uuid' => (string) Str::uuid(), 'ruche_id' => $ruche->id, 'date_visite' => $date, 'taches_faites' => [$acceptee->id]],
            ['client_uuid' => (string) Str::uuid(), 'ruche_id' => 999999, 'date_visite' => $date, 'taches_faites' => [$refusee->id]],
        ]])->assertOk();

        expect($acceptee->fresh()->statut)->toBe('terminee');
        expect($refusee->fresh()->statut)->toBe('a_faire');
    });

    it('ne solde pas la tâche d\'une autre équipe', function () {
        $alice = compte();
        $bob = compte();
        $tacheBob = tacheDe($bob, rucherAvec($bob)->ruches->first());
        $ruche = rucherAvec($alice)->ruches->first();

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => [
            ['client_uuid' => (string) Str::uuid(), 'ruche_id' => $ruche->id, 'date_visite' => now()->toDateString(), 'taches_faites' => [$tacheBob->id]],
        ]])->assertOk();

        expect($tacheBob->fresh()->statut)->toBe('a_faire');
    });

    it('accepte taches_faites nul sans erreur serveur', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();

        $this->actingAs($user)->postJson(route('terrain.sync'), ['visites' => [
            ['client_uuid' => (string) Str::uuid(), 'ruche_id' => $ruche->id, 'date_visite' => now()->toDateString(), 'taches_faites' => null],
        ]])->assertOk()->assertJsonPath('resultats.0.statut', 'recue');
    });
});

describe('état des ruches', function () {
    it('donne à une ruche le numéro de son nom, et un numéro libre du compte sinon', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 3', 'La bleue']);

        expect(RucherOverview::ruchers($user)->first()['ruches']->pluck('numero', 'nom')->all())
            ->toEqual(['Ruche 1' => 1, 'Ruche 3' => 3, 'La bleue' => 4]);
    });

    it('compte une ruche jamais visitée comme à visiter, pas une ruche morte', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2']);
        $rucher->ruches[1]->update(['statut' => 'morte']);
        tacheDe($user, $rucher->ruches[1], ['date_prevue' => now()->subWeek()->toDateString()]);

        $resultat = RucherOverview::ruchers($user)->first();

        expect($resultat['ruches'][0]['etat'])->toBe('todo');
        expect($resultat['a_visiter'])->toBe(1);
    });

    it('propose un nom de ruche libre', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 3']);

        $this->actingAs($user)->get(route('ruches.create', $rucher))
            ->assertInertia(fn ($page) => $page->where('suggestion', 'Ruche 4'));
    });

    it('n\'affiche pas la tâche d\'une ruche à la corbeille', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        tacheDe($user, $ruche);
        $ruche->delete();

        $this->actingAs($user)->get('/dashboard')->assertInertia(fn ($page) => $page->has('taches', 0));
    });

    it('refuse un type vide sans erreur serveur', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();

        $this->actingAs($user)
            ->put(route('ruches.update', $ruche), ['nom' => 'Ruche 1', 'type' => null])
            ->assertSessionHasErrors('type');
    });
});

describe('panel : logique claire', function () {
    it('sort une ruche morte ou inactive de la tournée et des comptes', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2', 'Ruche 3']);
        $rucher->ruches[0]->update(['statut' => 'morte']);
        $rucher->ruches[1]->update(['statut' => 'inactive']);

        $resultat = RucherOverview::ruchers($user)->first();

        expect($resultat['ruches']->pluck('etat')->all())->toBe(['hors', 'hors', 'todo']);
        expect($resultat['a_visiter'])->toBe(1);
    });

    it('rappelle une ruche visitée sans date prévue au bout de trois semaines', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2']);
        visiteDe($rucher->ruches[0], $user, ['date_visite' => now()->subDays(30)->toDateString()]);
        visiteDe($rucher->ruches[1], $user, ['date_visite' => now()->subDays(5)->toDateString()]);

        expect(RucherOverview::ruchers($user)->first()['ruches']->pluck('etat')->all())->toBe(['todo', 'next']);
    });

    it('écrit « RAS » pour une visite où rien n\'a été noté', function () {
        $user = compte();
        expect(visiteDe(rucherAvec($user)->ruches->first(), $user)->resume())->toBe('Rien à signaler');
    });

    it('donne une colonne par information dans le CSV', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar', 'numero_lot' => 'AV1']);

        $lignes = array_map(fn ($l) => str_getcsv($l, ';', '"', ''), explode("\n", trim($this->get('/registre/fichier?format=csv&depuis=2000-01-01&jusqu=2100-01-01')->streamedContent())));
        $entete = array_map(fn ($c) => trim($c, "\xEF\xBB\xBF"), $lignes[0]);
        $traitement = array_combine($entete, $lignes[1]);

        expect($traitement['Produit'])->toBe('Apivar');
        expect($traitement['Lot'])->toBe('AV1');
        expect($traitement['Cible'])->toBe('varroa');
    });
});

describe('panel : registre juste', function () {
    it('écrit le type de miel, et annonce le total d\'une récolte sur plusieurs ruches', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2']);

        $this->actingAs($user)->post(route('noter.store', 'recolte'), [
            'ruche_ids' => $rucher->ruches->pluck('id')->all(),
            'date_recolte' => now()->toDateString(),
            'type_miel' => 'acacia',
            'quantite_kg' => 12,
        ]);

        expect(RegistreService::pourEquipe($user->current_team_id)->pluck('detail')->all())->toBe(['Acacia, 12 kg', 'Acacia, 12 kg']);
    });

    it('écrit produit, cible, dose et lot du traitement', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar', 'dosage' => '2 lanières', 'numero_lot' => 'AV2611B']);

        expect(RegistreService::pourEquipe($user->current_team_id)->first()['detail'])->toBe('Apivar (varroa), 2 lanières, lot AV2611B');
    });

    it('dit « Traité (à compléter) » tant qu\'aucun traitement n\'est noté, et éteint le rappel pour chaque ruche traitée', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2']);
        foreach ($rucher->ruches as $ruche) {
            visiteDe($ruche, $user, ['traitement_applique' => true]);
        }

        expect(RucherOverview::aCompleter($user))->toHaveCount(2);
        expect(RegistreService::pourEquipe($user->current_team_id)->pluck('detail')->all())->each->toContain('Traité (à compléter)');

        $this->actingAs($user)->post(route('noter.store', 'traitement'), ['ruche_ids' => $rucher->ruches->pluck('id')->all(), 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar']);

        expect(RucherOverview::aCompleter($user))->toHaveCount(0);
        expect(RegistreService::pourEquipe($user->current_team_id)->where('type', 'visite')->pluck('detail')->all())->each->not->toContain('à compléter');
    });

    it('recalcule le rucher des saisies quand un déplacement est noté après coup', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        $visite = visiteDe($ruche, $user, ['date_visite' => now()->subDays(2)->toDateString()]);
        $this->actingAs($user)->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->subDays(5)->toDateString()]);

        expect($visite->fresh()->rucher_id)->toBe($b->id);

        $this->delete(route('fiches.destroy', ['deplacement', RucheEmplacement::first()->id]));

        expect($visite->fresh()->rucher_id)->toBe($a->id);
    });

    it('range les lignes d\'une journée dans l\'ordre de saisie', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->toDateString()]);
        $this->travel(1)->minutes();
        visiteDe($ruche->fresh(), $user);

        expect(RegistreService::pourEquipe($user->current_team_id)->pluck('type')->all())->toBe(['visite', 'deplacement']);
    });

    it('garde au registre les traitements d\'une ruche purgée de la corbeille', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        visiteDe($ruche, $user);
        $this->actingAs($user)->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar']);
        $ruche->delete();

        expect(RegistreService::pourEquipe($user->current_team_id)->pluck('type')->all())->toBe(['traitement']);

        Ruche::onlyTrashed()->update(['deleted_at' => now()->subDays(40)]);
        $this->artisan('beenote:vider-corbeille')->assertOk();

        expect(Visite::withTrashed()->count())->toBe(0);
        expect(RegistreService::pourEquipe($user->current_team_id)->pluck('ruche')->all())->toBe(['Ruche 1']);
    });
});

describe('panel : numéros de ruche', function () {
    it('poursuit la numérotation d\'un rucher à l\'autre', function () {
        $user = compte();
        rucherAvec($user, ['Ruche 1', 'Ruche 2']);

        $this->actingAs($user)->post(route('ruchers.store'), ['nom' => 'Colza', 'nombre_ruches' => 2]);

        expect(Rucher::where('nom', 'Colza')->first()->ruches->pluck('nom')->all())->toBe(['Ruche 3', 'Ruche 4']);
    });

    it('refuse un nom déjà pris dans un autre rucher', function () {
        $user = compte();
        rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');

        $this->actingAs($user)->post(route('ruches.store', $b), ['nom' => 'Ruche 1'])->assertSessionHasErrors('nom');
        $this->actingAs($user)->post(route('ruches.store', $b), ['nom' => 'Ruche 2'])->assertSessionHasNoErrors();
    });

    it('n\'empêche pas deux comptes d\'avoir leur « Ruche 1 »', function () {
        rucherAvec(compte(), ['Ruche 1']);
        $autre = compte();
        $rucher = rucherAvec($autre, []);

        $this->actingAs($autre)->post(route('ruches.store', $rucher), ['nom' => 'Ruche 1'])->assertSessionHasNoErrors();
    });

    it('trie les ruches par numéro', function () {
        $user = compte();
        rucherAvec($user, ['Ruche 10', 'Ruche 2']);

        expect(RucherOverview::ruchers($user)->first()['ruches']->pluck('nom')->all())->toBe(['Ruche 2', 'Ruche 10']);
    });
});

describe('contre-recette', function () {
    it('range une saisie après coup dans le rucher de sa date', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->subDays(2)->toDateString()]);

        $ancienne = visiteDe($ruche->fresh(), $user, ['date_visite' => now()->subDays(10)->toDateString()]);
        $recente = visiteDe($ruche->fresh(), $user);

        expect($ancienne->rucher_id)->toBe($a->id);
        expect($recente->rucher_id)->toBe($b->id);
    });

    it('refuse un déplacement daté avant le précédent, et n\'annule que le dernier', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $c = rucherAvec($user, [], 'C');
        $ruche = $a->ruches->first();
        $this->actingAs($user);
        $this->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->subDay()->toDateString()]);
        $premier = RucheEmplacement::orderBy('id')->first();

        $this->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $c->id, 'date_deplacement' => now()->subDays(5)->toDateString()])
            ->assertSessionHasErrors('date_deplacement');
        $this->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $c->id, 'date_deplacement' => now()->toDateString()])
            ->assertSessionHasNoErrors();

        $this->delete(route('fiches.destroy', ['deplacement', $premier->id]))->assertSessionHas('erreur');

        expect($ruche->fresh()->rucher_id)->toBe($c->id);
        expect($premier->fresh()->deleted_at)->toBeNull();
    });

    it('refuse d\'annuler un déplacement dont le rucher de départ est à la corbeille', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->toDateString()]);
        $a->delete();

        $this->delete(route('fiches.destroy', ['deplacement', RucheEmplacement::first()->id]))->assertSessionHas('erreur');

        expect($ruche->fresh()->rucher_id)->toBe($b->id);
    });

    it('emmène les tâches avec la ruche, et garde le nom du rucher après la purge', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        $tache = tacheDe($user, $ruche);
        visiteDe($ruche, $user, ['date_visite' => now()->subDays(3)->toDateString()]);
        $this->actingAs($user)->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->toDateString()]);

        expect($tache->fresh()->rucher_id)->toBe($b->id);

        $a->delete();
        Rucher::onlyTrashed()->update(['deleted_at' => now()->subDays(40)]);
        $this->artisan('beenote:vider-corbeille')->assertOk();

        $lignes = RegistreService::pourEquipe($user->current_team_id);
        expect($lignes->firstWhere('type', 'visite')['rucher'])->toBe('A');
        expect($lignes->firstWhere('type', 'deplacement')['detail'])->toBe('A vers B');
    });

    it('purge un rucher et ses sons quand plus rien ne le cite', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $visite = visiteDe($a->ruches->first(), $user);
        $chemin = 'notes-audio/'.$user->current_team_id.'/son';
        Storage::disk('local')->put($chemin, 'son');
        $visite->forceFill(['note_audio_path' => $chemin])->save();
        $a->delete();
        Rucher::onlyTrashed()->update(['deleted_at' => now()->subDays(40)]);

        $this->artisan('beenote:vider-corbeille')->assertOk();

        expect(Rucher::withTrashed()->count())->toBe(0);
        Storage::disk('local')->assertMissing($chemin);
    });

    it('sépare les limites : lire ses données ne bloque pas l\'export', function () {
        $user = compte();
        rucherAvec($user);
        $this->actingAs($user);

        foreach (range(1, 25) as $n) {
            $this->getJson(route('terrain.donnees'))->assertOk();
        }

        $this->get('/registre/fichier?format=csv&depuis=2026-01-01&jusqu=2026-12-31')->assertOk();
    });

    it('croit le proxy déclaré, et lui seul', function () {
        config(['beenote.trusted_proxies' => '10.0.0.1']);
        (new App\Providers\AppServiceProvider(app()))->boot();
        Illuminate\Support\Facades\Route::get('/ip-essai', fn (Illuminate\Http\Request $request) => $request->ip());

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->withHeader('X-Forwarded-For', '203.0.113.9')->get('/ip-essai')->assertSee('203.0.113.9');
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->withHeader('X-Forwarded-For', '203.0.113.9')->get('/ip-essai')->assertSee('10.0.0.2');

        Illuminate\Http\Middleware\TrustProxies::flushState();
    });

    it('écrit un CSV sans cellule qu\'un tableur exécuterait', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'x\\";=1+1;"']);

        $reponse = $this->get('/registre/fichier?format=csv&depuis=2000-01-01&jusqu=2100-01-01');
        $reponse->assertHeader('Cache-Control', 'no-store, private');

        foreach (explode("\n", trim($reponse->streamedContent())) as $ligne) {
            foreach (str_getcsv($ligne, ';', '"', '') as $cellule) {
                expect($cellule)->not->toStartWith('=');
            }
        }
    });

    it('réserve à qui peut écrire le coche des tâches et l\'ajout de notes', function () {
        $proprietaire = compte();
        $ruche = rucherAvec($proprietaire)->ruches->first();
        $tache = tacheDe($proprietaire, $ruche);
        $uuid = (string) Str::uuid();
        visiteDe($ruche, $proprietaire)->forceFill(['client_uuid' => $uuid])->save();
        $lecteur = compte();
        $proprietaire->currentTeam->users()->attach($lecteur, ['role' => 'lecteur']);
        $lecteur->forceFill(['current_team_id' => $proprietaire->current_team_id])->save();
        $this->actingAs($lecteur->fresh());

        $this->patch(route('taches.toggle', $tache))->assertForbidden();
        $this->postJson(route('terrain.note', $uuid), ['note' => fichierSon("\x1A\x45\xDF\xA3\x9F\x42\x86\x81\x01\x42\xF7\x81\x01\x42\xF2\x81\x04\x42\xF3\x81\x08\x42\x82\x84webm")])->assertForbidden();
    });

    it('accepte une visite datée de demain sur le serveur, pas au-delà', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $envoi = fn ($date) => $this->actingAs($user)->postJson(route('terrain.sync'), ['visites' => [['client_uuid' => (string) Str::uuid(), 'ruche_id' => $ruche->id, 'date_visite' => $date]]])->json('resultats.0.statut');

        expect($envoi(now()->addDay()->toDateString()))->toBe('recue');
        expect($envoi(now()->addDays(2)->toDateString()))->toBe('refusee');
    });

    it('répond 404 à une adresse non numérique', function () {
        $this->actingAs(compte());

        $this->get('/ruches/abc')->assertNotFound();
        $this->get('/ruchers/abc')->assertNotFound();
        $this->get('/visites/abc')->assertNotFound();
    });
});

describe('droits affichés', function () {
    it('propose Modifier et Corbeille au propriétaire sur chaque fiche', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $visite = visiteDe($ruche, $user);
        $this->actingAs($user);
        $this->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar']);
        $traitement = \App\Models\Traitement::first();

        $this->get(route('ruches.show', $ruche))->assertInertia(fn ($page) => $page->where('peutModifier', true)->missing('ruche.rucher.team_id'));
        $this->get(route('ruches.edit', $ruche))->assertInertia(fn ($page) => $page->where('peutSupprimer', true));
        $this->get(route('visites.show', $visite))->assertInertia(fn ($page) => $page->where('peutSupprimer', true));
        $this->get(route('fiches.show', ['traitement', $traitement->id]))->assertInertia(fn ($page) => $page->where('peutSupprimer', true));
    });

    it('compte faite une ruche vue aujourd\'hui, et garde sa tâche en retard à côté', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        visiteDe($ruche, $user);
        tacheDe($user, $ruche, ['date_prevue' => now()->subWeek()->toDateString()]);

        $resultat = RucherOverview::ruchers($user)->first();

        expect($resultat['ruches'][0]['etat'])->toBe('done');
        expect($resultat['ruches'][0]['taches'][0]['retard'])->toBeTrue();
        expect($resultat['a_visiter'])->toBe(0);
    });
});

describe('registre', function () {
    it('résume aussi les détails de la visite', function () {
        $user = compte();
        $visite = visiteDe(rucherAvec($user)->ruches->first(), $user, [
            'frelons_asiatiques' => 'nombreux',
            'surface_couvain' => 'faible',
            'type_nourrissement' => 'sirop',
        ]);

        expect($visite->resume())->toContain('Beaucoup de frelons', 'Couvain faible', 'Nourri (sirop)');
    });

    it('garde le rucher de l\'époque après un déplacement', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        visiteDe($ruche, $user);

        $this->actingAs($user)->post(route('noter.store', 'deplacement'), [
            'ruche_ids' => [$ruche->id],
            'vers_rucher_id' => $b->id,
            'date_deplacement' => now()->toDateString(),
        ])->assertRedirect();

        $visites = RegistreService::pourEquipe($user->current_team_id)->where('type', 'visite');
        expect($visites->first()['rucher'])->toBe('A');
        expect(RegistreService::pourEquipe($user->current_team_id, rucherId: $b->id)->where('type', 'visite'))->toHaveCount(0);
        expect(RegistreService::pourEquipe($user->current_team_id, rucherId: $a->id)->where('type', 'visite'))->toHaveCount(1);
    });

    it('signale proprement une ruche déjà dans le rucher', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');

        $this->actingAs($user)->post(route('noter.store', 'deplacement'), [
            'ruche_ids' => [$a->ruches->first()->id],
            'vers_rucher_id' => $a->id,
            'date_deplacement' => now()->toDateString(),
        ])->assertSessionHasErrors('vers_rucher_id');
    });

    it('ignore dans un lot les ruches déjà sur place', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, ['Ruche 2'], 'B');

        $this->actingAs($user)->post(route('noter.store', 'deplacement'), [
            'ruche_ids' => [$a->ruches->first()->id, $b->ruches->first()->id],
            'vers_rucher_id' => $b->id,
            'date_deplacement' => now()->toDateString(),
        ])->assertRedirect();

        expect(RucheEmplacement::count())->toBe(1);
        expect(RucheEmplacement::first()->ruche_id)->toBe($a->ruches->first()->id);
    });

    it('annule un déplacement mis à la corbeille, et le rejoue à la restauration', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();

        $this->actingAs($user)->post(route('noter.store', 'deplacement'), [
            'ruche_ids' => [$ruche->id],
            'vers_rucher_id' => $b->id,
            'date_deplacement' => now()->toDateString(),
        ]);
        $deplacement = RucheEmplacement::first();

        $this->actingAs($user)->delete(route('fiches.destroy', ['deplacement', $deplacement->id]));
        expect($ruche->fresh()->rucher_id)->toBe($a->id);

        $this->actingAs($user)->post(route('corbeille.restore', ['deplacement', $deplacement->id]));
        expect($ruche->fresh()->rucher_id)->toBe($b->id);
    });

    it('écrit correctement les récoltes rondes', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();

        foreach ([100, 20, 12.5] as $kilos) {
            $recolte = new Recolte(['date_recolte' => now()->toDateString(), 'type_miel' => 'acacia', 'quantite_kg' => $kilos]);
            $recolte->forceFill(['team_id' => $user->current_team_id, 'user_id' => $user->id, 'ruche_id' => $ruche->id])->save();
        }

        expect(RegistreService::pourEquipe($user->current_team_id)->pluck('detail')->sort()->values()->all())
            ->toBe(['Acacia, 100 kg', 'Acacia, 12,5 kg', 'Acacia, 20 kg']);
    });

    it('n\'applique pas de limite au fichier téléchargé', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();

        foreach (range(1, 3) as $n) {
            visiteDe($ruche, $user);
        }

        expect(RegistreService::pourEquipe($user->current_team_id, limite: 2))->toHaveCount(2);
        expect(RegistreService::pourEquipe($user->current_team_id, limite: null))->toHaveCount(3);
    });

    it('répond 404 à un identifiant de fiche non numérique', function () {
        $this->actingAs(compte())->get('/fiches/traitement/abc')->assertNotFound();
    });
});

describe('panel 2 : tour 1', function () {
    $envoi = fn (Ruche $ruche, array $valeurs = []) => ['client_uuid' => (string) Str::uuid(), 'ruche_id' => $ruche->id, 'date_visite' => now()->toDateString(), ...$valeurs];

    it('complète la visite du jour au lieu d\'en créer une seconde, sans effacer la prochaine visite', function () use ($envoi) {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $premier = $envoi($ruche, ['humeur_colonie' => 'calme', 'prochaine_visite_prevue' => now()->addWeek()->toDateString()]);
        $second = $envoi($ruche, ['hausse_ajoutee' => true]);

        $this->actingAs($user)->postJson(route('terrain.sync'), ['visites' => [$premier]])->assertOk();
        $this->postJson(route('terrain.sync'), ['visites' => [$second]])->assertJsonPath('resultats.0.statut', 'completee');
        $this->postJson(route('terrain.sync'), ['visites' => [$second]])->assertJsonPath('resultats.0.statut', 'deja_recue');

        expect(Visite::count())->toBe(1);
        $visite = Visite::first();
        expect($visite->humeur_colonie)->toBe('calme');
        expect($visite->hausse_ajoutee)->toBeTrue();
        expect($visite->prochaine_visite_prevue->toDateString())->toBe(now()->addWeek()->toDateString());

        $this->postJson(route('terrain.note', $second['client_uuid']), ['note' => fichierSon("\x1A\x45\xDF\xA3\x9F\x42\x86\x81\x01\x42\xF7\x81\x01\x42\xF2\x81\x04\x42\xF3\x81\x08\x42\x82\x84webm")])->assertOk();
        expect($visite->fresh()->note_audio_path)->not->toBeNull();
    });

    it('ne solde que les tâches de la ruche visitée ou de son rucher', function () use ($envoi) {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2']);
        $deLaRuche = tacheDe($user, $rucher->ruches[0]);
        $duRucher = tacheDe($user, $rucher->ruches[0]);
        $duRucher->forceFill(['ruche_id' => null])->save();
        $autre = tacheDe($user, $rucher->ruches[1]);

        $this->actingAs($user)->postJson(route('terrain.sync'), ['visites' => [$envoi($rucher->ruches[0], ['taches_faites' => [$deLaRuche->id, $duRucher->id, $autre->id]])]]);

        expect($deLaRuche->fresh()->statut)->toBe('terminee');
        expect($duRucher->fresh()->statut)->toBe('terminee');
        expect($autre->fresh()->statut)->toBe('a_faire');
    });

    it('garde le numéro d\'une ruche quand une autre arrive dans le rucher', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['La grosse']);
        $numero = $rucher->ruches[0]->fresh()->numero;

        // Le numéro 1 est celui de « La grosse » : il n'est pas donné à une autre.
        $this->actingAs($user)->post(route('ruches.store', $rucher), ['nom' => 'Ruche 1'])->assertSessionHasErrors('nom');
        $this->post(route('ruches.store', $rucher), ['nom' => 'Ruche 2'])->assertSessionHasNoErrors();

        expect($rucher->ruches[0]->fresh()->numero)->toBe($numero);
        expect(RucherOverview::ruchers($user)->first()['ruches']->pluck('numero')->unique())->toHaveCount(2);
    });

    it('refuse un nom dont le numéro est déjà pris', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 3'], 'A');
        $b = rucherAvec($user, [], 'B');

        $this->actingAs($user)->post(route('ruches.store', $b), ['nom' => 'Colonie 3'])->assertSessionHasErrors('nom');
    });

    it('garde le nom d\'une ruche à la corbeille, qui peut donc toujours revenir', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1']);
        $ancienne = $rucher->ruches[0];
        $ancienne->delete();
        $this->actingAs($user)->post(route('ruches.store', $rucher), ['nom' => 'Ruche 1'])->assertSessionHasErrors('nom');

        $this->post(route('corbeille.restore', ['ruche', $ancienne->id]))->assertSessionMissing('erreur');

        expect($ancienne->fresh()->trashed())->toBeFalse();
    });

    it('éteint « Compléter le traitement » par la visite liée, même à une autre date', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $visite = visiteDe($ruche, $user, ['traitement_applique' => true, 'date_visite' => now()->subDays(3)->toDateString()]);
        $this->actingAs($user)->get(route('noter.create', ['type' => 'traitement', 'ruche' => $ruche->id, 'visite' => $visite->id]))
            ->assertInertia(fn ($page) => $page->where('visite.date', now()->subDays(3)->toDateString()));

        $this->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'visite_id' => $visite->id, 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar']);

        expect(RucherOverview::aCompleter($user))->toHaveCount(0);
        expect(RegistreService::pourEquipe($user->current_team_id)->firstWhere('type', 'visite')['detail'])->not->toContain('à compléter');
    });

    it('signale une ruche à surveiller même quand tout est à jour', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        visiteDe($ruche, $user, ['estimation_population' => 'faible', 'varroas_observes' => 'ok']);

        $resultat = RucherOverview::ruchers($user)->first();

        expect($resultat['a_visiter'])->toBe(0);
        expect($resultat['a_surveiller'])->toBe(1);
        expect($resultat['ruches'][0]['alertes'])->toBe(['colonie faible', 'varroas']);
    });

    it('oriente vers le tableur un PDF trop long, au lieu de tomber', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $lignes = collect(range(1, App\Http\Controllers\RegistreController::PDF_MAX + 1))->map(fn ($n) => [
            'ruche_id' => $ruche->id, 'team_id' => $user->current_team_id, 'user_id' => $user->id,
            'date_visite' => now()->subDays($n % 300)->toDateString(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($lignes->chunk(200) as $paquet) {
            Visite::insert($paquet->all());
        }

        $this->actingAs($user)->from('/registre/telecharger')
            ->get('/registre/fichier?format=pdf&depuis=2000-01-01&jusqu=2100-01-01')
            ->assertSessionHasErrors('format');
    });

    it('cache qu\'un rucher d\'une autre équipe existe', function () {
        $alice = compte();
        $rucher = rucherAvec($alice);

        $this->actingAs(compte())->get(route('ruchers.show', $rucher))->assertNotFound();
    });

    it('n\'accepte pas une visite datée de demain depuis le PC', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();

        $this->actingAs($user)->post(route('visites.store'), ['ruche_id' => $ruche->id, 'date_visite' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors('date_visite');
    });
});

describe('panel 2 : tour 2', function () {
    it('ne signale pas « reine pas vue » quand la ponte est vue', function () {
        $user = compte();
        $visite = visiteDe(rucherAvec($user)->ruches->first(), $user, ['reine_vue' => 'non_ok', 'ponte_observee' => 'ok']);

        expect($visite->alertes())->toBe([]);
    });

    it('garde la note vocale du matin quand la visite est complétée', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $son = "\x1A\x45\xDF\xA3\x9F\x42\x86\x81\x01\x42\xF7\x81\x01\x42\xF2\x81\x04\x42\xF3\x81\x08\x42\x82\x84webm";
        $matin = ['client_uuid' => (string) Str::uuid(), 'ruche_id' => $ruche->id, 'date_visite' => now()->toDateString()];
        $soir = [...$matin, 'client_uuid' => (string) Str::uuid()];

        $this->actingAs($user)->postJson(route('terrain.sync'), ['visites' => [$matin]]);
        $this->postJson(route('terrain.note', $matin['client_uuid']), ['note' => fichierSon($son)])->assertOk();
        $this->postJson(route('terrain.sync'), ['visites' => [$soir]]);
        $this->postJson(route('terrain.note', $soir['client_uuid']), ['note' => fichierSon($son)])->assertOk();

        $visite = Visite::first();
        expect($visite->cheminsAudio())->toHaveCount(2);
        foreach ($visite->cheminsAudio() as $chemin) {
            Storage::disk('local')->assertExists($chemin);
        }
        $this->get(route('visites.note', ['visite' => $visite->id, 'numero' => 1]))->assertOk();
    });

    it('complète au PC la visite déjà notée ce jour-là', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        visiteDe($ruche, $user, ['humeur_colonie' => 'calme']);

        $this->actingAs($user)->post(route('visites.store'), ['ruche_id' => $ruche->id, 'date_visite' => now()->toDateString(), 'hausse_ajoutee' => true]);

        expect(Visite::count())->toBe(1);
        expect(Visite::first()->hausse_ajoutee)->toBeTrue();
        expect(Visite::first()->humeur_colonie)->toBe('calme');
    });

    it('limite les essais de mot de passe sur le profil', function () {
        $this->actingAs($user = compte());

        foreach (range(1, 10) as $essai) {
            $this->put('/user/profile-information', ['name' => 'X', 'email' => 'autre@exemple.fr', 'current_password' => 'faux'.$essai]);
        }

        $this->put('/user/profile-information', ['name' => 'X', 'email' => 'autre@exemple.fr', 'current_password' => 'faux'])->assertStatus(429);
    });

    it('refuse un PDF dont les notes pèsent trop', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        foreach (range(1, 120) as $n) {
            visiteDe($ruche, $user, ['date_visite' => now()->subDays($n)->toDateString(), 'notes_generales' => str_repeat('a', 1999)]);
        }

        $this->actingAs($user)->get('/registre/fichier?format=pdf&depuis=2000-01-01&jusqu=2100-01-01')
            ->assertRedirect(route('registre.export-form'))
            ->assertSessionHasErrors('format');
    });
});

describe('panel 2 : tour 2, logique', function () {
    it('garde une alerte tant qu\'une visite plus récente n\'a pas regardé ce point', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        visiteDe($ruche, $user, ['date_visite' => now()->subDays(10)->toDateString(), 'varroas_observes' => 'ok']);
        visiteDe($ruche, $user, ['date_visite' => now()->subDays(2)->toDateString(), 'humeur_colonie' => 'calme']);

        expect(RucherOverview::ruchers($user)->first()['ruches'][0]['alertes'])->toBe(['varroas']);

        visiteDe($ruche, $user, ['varroas_observes' => 'non_ok']);
        expect(RucherOverview::ruchers($user)->first()['ruches'][0]['alertes'])->toBe([]);
    });

    it('refuse de remettre un déplacement qui n\'est plus le dernier', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $c = rucherAvec($user, [], 'C');
        $ruche = $a->ruches->first();
        $this->actingAs($user);
        $this->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->subDays(2)->toDateString()]);
        $premier = RucheEmplacement::first();
        $premier->delete();
        $this->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $c->id, 'date_deplacement' => now()->toDateString()]);

        $this->post(route('corbeille.restore', ['deplacement', $premier->id]))->assertSessionHas('erreur');
        expect($premier->fresh()->trashed())->toBeTrue();
    });

    it('n\'affiche plus la tâche d\'une ruche morte', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        tacheDe($user, $ruche);
        $ruche->update(['statut' => 'morte']);

        $this->actingAs($user)->get('/dashboard')->assertInertia(fn ($page) => $page->has('taches', 0));
    });

    it('ne retient pas un numéro trop grand dans le nom', function () {
        expect(RucherOverview::numeroDuNom('Ruche 40000'))->toBeNull();
    });
});

describe('panel 2 : tour 2, Léa', function () {
    it('garde les points d\'un lot dans le CSV', function () {
        $user = compte();
        $ruche = rucherAvec($user)->ruches->first();
        $this->actingAs($user)->post(route('noter.store', 'traitement'), ['ruche_ids' => [$ruche->id], 'date_traitement' => now()->toDateString(), 'type_traitement' => 'varroa', 'produit_utilise' => 'Apivar', 'dosage' => '2.5 g', 'numero_lot' => 'B.24.117']);

        $lignes = array_map(fn ($l) => str_getcsv($l, ';', '"', ''), explode("\n", trim($this->get('/registre/fichier?format=csv&depuis=2000-01-01&jusqu=2100-01-01')->streamedContent())));
        $ligne = array_combine(array_map(fn ($c) => trim($c, "\xEF\xBB\xBF"), $lignes[0]), $lignes[1]);

        expect($ligne['Lot'])->toBe('B.24.117');
        expect($ligne['Dose'])->toBe('2.5 g');
    });

    it('ne donne pas le nom d\'une ruche à la corbeille à une nouvelle', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 24']);
        $rucher->ruches[0]->delete();

        $this->actingAs($user)->post(route('ruches.store', $rucher), ['nom' => 'Ruche 24'])->assertSessionHasErrors('nom');
    });

    it('refuse de corriger une visite vers une ruche déjà visitée ce jour-là', function () {
        $user = compte();
        $rucher = rucherAvec($user, ['Ruche 1', 'Ruche 2']);
        visiteDe($rucher->ruches[1], $user);
        $visite = visiteDe($rucher->ruches[0], $user);

        $this->actingAs($user)->put(route('visites.update', $visite), ['ruche_id' => $rucher->ruches[1]->id, 'date_visite' => now()->toDateString()])
            ->assertSessionHasErrors('ruche_id');

        expect($visite->fresh()->ruche_id)->toBe($rucher->ruches[0]->id);
    });

    it('dit qu\'un déplacement antidaté change le rucher de saisies', function () {
        $user = compte();
        $a = rucherAvec($user, ['Ruche 1'], 'A');
        $b = rucherAvec($user, [], 'B');
        $ruche = $a->ruches->first();
        visiteDe($ruche, $user, ['date_visite' => now()->subDays(2)->toDateString()]);

        $this->actingAs($user)->post(route('noter.store', 'deplacement'), ['ruche_ids' => [$ruche->id], 'vers_rucher_id' => $b->id, 'date_deplacement' => now()->subDays(5)->toDateString()])
            ->assertSessionHas('message', fn ($message) => str_contains($message, 'changé de rucher'));
    });
});
