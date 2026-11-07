<?php

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\Rucher;
use App\Models\Tache;
use App\Models\Team;
use App\Models\Traitement;
use App\Models\User;
use App\Models\Visite;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function apiculteur(): User
{
    return User::factory()->withPersonalTeam()->create();
}

function rucherDe(User $user, int $ruches = 1): Rucher
{
    $rucher = Rucher::factory()->create(['team_id' => $user->currentTeam->id]);
    Ruche::factory()->count($ruches)->create(['rucher_id' => $rucher->id]);

    return $rucher->load('ruches');
}

function visitePour(Ruche $ruche, User $user, array $attributs = []): Visite
{
    $visite = new Visite(['date_visite' => now()->toDateString(), ...$attributs]);
    $visite->forceFill(['ruche_id' => $ruche->id, 'user_id' => $user->id, 'team_id' => $user->current_team_id])->save();

    return $visite;
}

function editeurDe(User $proprietaire): User
{
    $editeur = apiculteur();
    $proprietaire->currentTeam->users()->attach($editeur, ['role' => 'editor']);
    $editeur->forceFill(['current_team_id' => $proprietaire->current_team_id])->save();

    return $editeur->fresh();
}

beforeEach(function () {
    Http::fake();
});

describe('isolation entre équipes', function () {
    it('refuse à un autre apiculteur tous les écrans privés', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $rucher = rucherDe($alice);
        $ruche = $rucher->ruches->first();
        $visite = visitePour($ruche, $alice);

        $this->actingAs($bob);

        foreach ([
            route('ruchers.show', $rucher),
            route('ruchers.edit', $rucher),
            route('ruches.show', $ruche),
            route('ruches.edit', $ruche),
            route('ruches.create', $rucher),
            route('visites.show', $visite),
            route('visites.note', $visite),
        ] as $url) {
            $this->get($url)->assertNotFound();
        }
    });

    it('refuse les écritures sur les données d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $rucher = rucherDe($alice);
        $ruche = $rucher->ruches->first();
        $visite = visitePour($ruche, $alice);

        $this->actingAs($bob);

        $this->put(route('ruchers.update', $rucher), ['nom' => 'Piraté'])->assertNotFound();
        $this->delete(route('ruchers.destroy', $rucher))->assertNotFound();
        $this->put(route('ruches.update', $ruche), ['nom' => 'Piraté'])->assertNotFound();
        $this->delete(route('ruches.destroy', $ruche))->assertNotFound();
        $this->delete(route('visites.destroy', $visite))->assertNotFound();
        $this->post(route('visites.store'), ['ruche_id' => $ruche->id, 'date_visite' => now()->toDateString()])->assertNotFound();
        $this->post(route('noter.store', 'traitement'), [
            'ruche_ids' => [$ruche->id],
            'date_traitement' => now()->toDateString(),
            'type_traitement' => 'varroa',
            'produit_utilise' => 'X',
        ])->assertNotFound();

        expect($rucher->fresh()->nom)->not->toBe('Piraté');
        expect(Traitement::count())->toBe(0);
    });

    it('n\'accepte pas de déplacer sa ruche vers le rucher d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $destination = rucherDe($bob);

        $this->actingAs($alice)->post(route('noter.store', 'deplacement'), [
            'ruche_ids' => [$ruche->id],
            'date_deplacement' => now()->toDateString(),
            'vers_rucher_id' => $destination->id,
        ])->assertNotFound();

        expect($ruche->fresh()->rucher_id)->not->toBe($destination->id);
    });

    it('n\'empêche pas seulement l\'accès, le registre et la synchronisation ne voient rien', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        visitePour($ruche, $alice);

        $this->actingAs($bob)->getJson(route('terrain.donnees'))->assertOk()->assertJsonPath('ruchers', []);
        $this->actingAs($bob)->get(route('registre.index'))->assertInertia(fn ($page) => $page->where('lignes', []));
    });

    it('répond 404 à un QR code d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();

        $this->actingAs($bob)->get(route('qr.scan', $ruche->qr_token))->assertNotFound();
        $this->actingAs($alice)->get(route('qr.scan', $ruche->qr_token))->assertRedirect(route('terrain', ['ruche' => $ruche->id]));
    });

    it('exige une connexion', function () {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('terrain.donnees'))->assertUnauthorized();
        $this->postJson(route('terrain.sync'), ['visites' => []])->assertUnauthorized();
    });

    it('ne garde plus l\'accès après le retrait de l\'équipe', function () {
        $alice = apiculteur();
        $editeur = editeurDe($alice);
        $rucher = rucherDe($alice);

        $this->actingAs($editeur)->get(route('ruchers.show', $rucher))->assertOk();

        $alice->currentTeam->users()->detach($editeur);

        $this->actingAs($editeur->fresh())->get(route('ruchers.show', $rucher))->assertNotFound();
    });
});

describe('droits dans une équipe', function () {
    it('laisse un éditeur écrire mais pas supprimer', function () {
        $alice = apiculteur();
        $editeur = editeurDe($alice);
        $rucher = rucherDe($alice);
        $ruche = $rucher->ruches->first();
        $visite = visitePour($ruche, $alice);

        $this->actingAs($editeur);

        $this->put(route('ruches.update', $ruche), ['nom' => 'Nouveau nom'])->assertRedirect();
        $this->delete(route('ruches.destroy', $ruche))->assertForbidden();
        $this->delete(route('visites.destroy', $visite))->assertForbidden();
        $this->delete(route('ruchers.destroy', $rucher))->assertForbidden();
        $this->delete(route('fiches.destroy', ['visite', $visite->id]))->assertNotFound();

        expect($ruche->fresh()->nom)->toBe('Nouveau nom');
    });
});

describe('synchronisation du mode terrain', function () {
    function envoi(Ruche $ruche, array $attributs = []): array
    {
        return [
            'client_uuid' => (string) Str::uuid(),
            'ruche_id' => $ruche->id,
            'date_visite' => now()->toDateString(),
            'humeur_colonie' => 'calme',
            'reine_vue' => 'non_ok',
            ...$attributs,
        ];
    }

    it('enregistre une visite et la rattache au bon compte', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $visite = envoi($ruche);

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => [$visite]])
            ->assertOk()
            ->assertJsonPath('resultats.0.statut', 'recue');

        $enregistree = Visite::firstWhere('client_uuid', $visite['client_uuid']);
        expect($enregistree->reine_vue)->toBe('non_ok');
        expect($enregistree->ponte_observee)->toBe('non_observe');
        expect($enregistree->team_id)->toBe($alice->current_team_id);
        expect($enregistree->user_id)->toBe($alice->id);
    });

    it('ne crée jamais de doublon quand le même envoi est répété', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $visite = envoi($ruche);

        $this->actingAs($alice);
        $this->postJson(route('terrain.sync'), ['visites' => [$visite]])->assertJsonPath('resultats.0.statut', 'recue');
        $this->postJson(route('terrain.sync'), ['visites' => [$visite]])->assertJsonPath('resultats.0.statut', 'deja_recue');

        expect(Visite::count())->toBe(1);
    });

    it('ne réécrit pas une visite déjà reçue, même modifiée depuis', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $visite = envoi($ruche);

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => [$visite]]);
        Visite::first()->update(['humeur_colonie' => 'agressive']);
        $this->postJson(route('terrain.sync'), ['visites' => [$visite]]);

        expect(Visite::first()->humeur_colonie)->toBe('agressive');
    });

    it('refuse une ruche d\'une autre équipe sans bloquer les autres visites', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $mienne = rucherDe($alice)->ruches->first();
        $autre = rucherDe($bob)->ruches->first();

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => [envoi($autre), envoi($mienne)]])
            ->assertOk()
            ->assertJsonPath('resultats.0.statut', 'refusee')
            ->assertJsonPath('resultats.1.statut', 'recue');

        expect(Visite::where('ruche_id', $autre->id)->count())->toBe(0);
    });

    it('refuse les valeurs inconnues, une date future et un identifiant invalide', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => [
            envoi($ruche, ['humeur_colonie' => "'; DROP TABLE visites; --"]),
            envoi($ruche, ['date_visite' => now()->addDays(2)->toDateString()]),
            envoi($ruche, ['client_uuid' => 'pas-un-uuid']),
            'pas un tableau',
        ]])->assertOk()
            ->assertJsonPath('resultats.0.statut', 'refusee')
            ->assertJsonPath('resultats.1.statut', 'refusee')
            ->assertJsonPath('resultats.2.statut', 'refusee')
            ->assertJsonPath('resultats.3.statut', 'refusee');

        expect(Visite::count())->toBe(0);
    });

    it('ignore les champs non prévus envoyés par le client', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => [envoi($ruche, [
            'team_id' => $bob->current_team_id,
            'user_id' => $bob->id,
            'note_audio_path' => '../../.env',
        ])]]);

        $visite = Visite::first();
        expect($visite->team_id)->toBe($alice->current_team_id);
        expect($visite->user_id)->toBe($alice->id);
        expect($visite->note_audio_path)->toBeNull();
    });

    it('fournit les données pour travailler sans réseau, sans les mettre en cache partagé', function () {
        $alice = apiculteur();
        rucherDe($alice, 3);

        $reponse = $this->actingAs($alice)->getJson(route('terrain.donnees'))->assertOk();

        $reponse->assertJsonCount(3, 'ruchers.0.ruches');
        expect($reponse->headers->get('Cache-Control'))->toContain('no-store');
    });
});

describe('notes vocales', function () {
    beforeEach(fn () => Storage::fake('local'));

    // Fichier réel : le serveur doit juger le contenu, pas ce que le client annonce.
    function fichierReel(string $nom, string $contenu): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'bn');
        file_put_contents($chemin, $contenu);

        return new UploadedFile($chemin, $nom, null, null, true);
    }

    function audioWebm(): string
    {
        return hex2bin('1A45DFA39F4286810142F7810142F2810442F381084282847765626D4287810242858102').str_repeat("\0", 256);
    }

    function visiteSynchronisee(User $user, Ruche $ruche): Visite
    {
        $visite = visitePour($ruche, $user);
        $visite->forceFill(['client_uuid' => (string) Str::uuid()])->save();

        return $visite;
    }

    it('accepte un fichier audio et le range hors du dossier public, sous un nom aléatoire', function () {
        $alice = apiculteur();
        $visite = visiteSynchronisee($alice, rucherDe($alice)->ruches->first());
        $fichier = fichierReel('../../evil.webm', audioWebm());

        $this->actingAs($alice)->post(route('terrain.note', $visite->client_uuid), ['note' => $fichier, 'duree' => 12])->assertOk();

        $chemin = $visite->fresh()->note_audio_path;
        expect($chemin)->toStartWith('notes-audio/')->not->toContain('evil')->not->toContain('..');
        Storage::disk('local')->assertExists($chemin);
    });

    it('refuse un script déguisé en audio par son contenu', function () {
        $alice = apiculteur();
        $visite = visiteSynchronisee($alice, rucherDe($alice)->ruches->first());

        $this->actingAs($alice)->postJson(route('terrain.note', $visite->client_uuid), ['note' => fichierReel('note.php', audioWebm())])->assertUnprocessable();
    });

    it('refuse un fichier qui n\'est pas de l\'audio', function () {
        $alice = apiculteur();
        $visite = visiteSynchronisee($alice, rucherDe($alice)->ruches->first());
        $fichier = fichierReel('note.webm', '<?php echo "pirate"; ?>');

        $this->actingAs($alice)->postJson(route('terrain.note', $visite->client_uuid), ['note' => $fichier])->assertUnprocessable();

        expect($visite->fresh()->note_audio_path)->toBeNull();
    });

    it('refuse un fichier trop lourd', function () {
        $alice = apiculteur();
        $visite = visiteSynchronisee($alice, rucherDe($alice)->ruches->first());
        $fichier = UploadedFile::fake()->create('note.webm', 20000, 'audio/webm');

        $this->actingAs($alice)->postJson(route('terrain.note', $visite->client_uuid), ['note' => $fichier])->assertUnprocessable();
    });

    it('n\'autorise ni dépôt ni écoute sur la visite d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $visite = visiteSynchronisee($alice, rucherDe($alice)->ruches->first());
        $fichier = UploadedFile::fake()->create('note.webm', 100, 'audio/webm');

        $this->actingAs($bob)->post(route('terrain.note', $visite->client_uuid), ['note' => $fichier])->assertNotFound();
        $this->actingAs($bob)->get(route('visites.note', $visite))->assertNotFound();
    });

    it('ne divulgue jamais le chemin du fichier', function () {
        $alice = apiculteur();
        $visite = visiteSynchronisee($alice, rucherDe($alice)->ruches->first());
        $visite->forceFill(['note_audio_path' => 'notes-audio/secret'])->save();

        expect(json_encode($visite->fresh()))->not->toContain('notes-audio/secret');
    });
});

describe('rucher, ruche et visite', function () {
    it('crée un rucher avec ses ruches numérotées', function () {
        $alice = apiculteur();

        $this->actingAs($alice)->post(route('ruchers.store'), ['nom' => 'Le champ', 'nombre_ruches' => 3])->assertRedirect();

        $rucher = Rucher::firstWhere('nom', 'Le champ');
        expect($rucher->team_id)->toBe($alice->current_team_id);
        expect($rucher->ruches()->pluck('nom')->all())->toBe(['Ruche 1', 'Ruche 2', 'Ruche 3']);
    });

    it('ne laisse pas choisir l\'équipe à la création', function () {
        $alice = apiculteur();
        $bob = apiculteur();

        $this->actingAs($alice)->post(route('ruchers.store'), ['nom' => 'Le champ', 'nombre_ruches' => 0, 'team_id' => $bob->current_team_id]);

        expect(Rucher::firstWhere('nom', 'Le champ')->team_id)->toBe($alice->current_team_id);
    });

    it('enregistre une visite saisie après coup et la corrige', function () {
        $alice = apiculteur();
        $rucher = rucherDe($alice, 2);
        [$premiere, $seconde] = $rucher->ruches->all();

        $this->actingAs($alice)->post(route('visites.store'), [
            'ruche_id' => $premiere->id,
            'date_visite' => now()->subDays(2)->toDateString(),
            'reine_vue' => 'ok',
            'hausse_ajoutee' => true,
        ])->assertRedirect(route('ruches.show', $premiere));

        $visite = Visite::first();
        expect($visite->resume())->toContain('Reine vue')->toContain('Hausse posée');

        // Correction : mauvaise ruche et mauvaise observation.
        $this->put(route('visites.update', $visite), [
            'ruche_id' => $seconde->id,
            'date_visite' => now()->subDays(2)->toDateString(),
            'reine_vue' => 'non_ok',
        ])->assertRedirect(route('ruches.show', $seconde));

        expect($visite->fresh()->ruche_id)->toBe($seconde->id);
        expect($visite->fresh()->reine_vue)->toBe('non_ok');
    });

    it('calcule l\'état des ruches pour l\'accueil', function () {
        $alice = apiculteur();
        $rucher = rucherDe($alice, 3);
        [$faite, $enRetard, $aVenir] = $rucher->ruches->all();

        visitePour($faite, $alice);
        visitePour($enRetard, $alice, ['date_visite' => now()->subDays(20)->toDateString(), 'prochaine_visite_prevue' => now()->subDays(6)->toDateString()]);
        visitePour($aVenir, $alice, ['date_visite' => now()->subDays(3)->toDateString(), 'prochaine_visite_prevue' => now()->addDays(10)->toDateString()]);

        $this->actingAs($alice)->get(route('dashboard'))->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('ruchers.0.ruches.0.etat', 'done')
            ->where('ruchers.0.ruches.1.etat', 'late')
            ->where('ruchers.0.ruches.2.etat', 'next')
            ->where('ruchers.0.a_visiter', 1));
    });

    it('signale un traitement à compléter jusqu\'à ce qu\'il soit noté', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $visite = visitePour($ruche, $alice, ['traitement_applique' => true]);

        $this->actingAs($alice)->get(route('dashboard'))->assertInertia(fn ($page) => $page->has('aCompleter', 1));

        $this->post(route('noter.store', 'traitement'), [
            'ruche_ids' => [$ruche->id],
            'visite_id' => $visite->id,
            'date_traitement' => now()->toDateString(),
            'type_traitement' => 'varroa',
            'produit_utilise' => 'Acide oxalique',
        ])->assertRedirect();

        $this->get(route('dashboard'))->assertInertia(fn ($page) => $page->has('aCompleter', 0));
    });

    it('note un traitement pour plusieurs ruches d\'un coup', function () {
        $alice = apiculteur();
        $ids = rucherDe($alice, 3)->ruches->pluck('id')->all();

        $this->actingAs($alice)->post(route('noter.store', 'traitement'), [
            'ruche_ids' => $ids,
            'date_traitement' => now()->toDateString(),
            'type_traitement' => 'varroa',
            'produit_utilise' => 'Acide oxalique',
        ])->assertRedirect(route('registre.index'));

        expect(Traitement::count())->toBe(3);
    });

    it('garde l\'historique des emplacements quand une ruche est déplacée', function () {
        $alice = apiculteur();
        $depart = rucherDe($alice);
        $arrivee = rucherDe($alice, 0);
        $ruche = $depart->ruches->first();

        $this->actingAs($alice)->post(route('noter.store', 'deplacement'), [
            'ruche_ids' => [$ruche->id],
            'date_deplacement' => now()->toDateString(),
            'vers_rucher_id' => $arrivee->id,
        ])->assertRedirect();

        expect($ruche->fresh()->rucher_id)->toBe($arrivee->id);
        expect($ruche->emplacements()->first()->depuis_rucher_id)->toBe($depart->id);
    });

    it('note une récolte', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();

        $this->actingAs($alice)->post(route('noter.store', 'recolte'), [
            'ruche_ids' => [$ruche->id],
            'date_recolte' => now()->toDateString(),
            'type_miel' => 'toutes_fleurs',
            'quantite_kg' => 14.5,
        ])->assertRedirect();

        expect(Recolte::first()->user_id)->toBe($alice->id);
    });
});

describe('corbeille', function () {
    it('retire une visite du registre puis la remet', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $visite = visitePour($ruche, $alice);

        $this->actingAs($alice)->delete(route('visites.destroy', $visite))->assertRedirect();
        $this->get(route('registre.index'))->assertInertia(fn ($page) => $page->has('lignes', 0));
        $this->get(route('corbeille.index'))->assertInertia(fn ($page) => $page->has('items', 1));

        $this->post(route('corbeille.restore', ['visite', $visite->id]))->assertRedirect();
        $this->get(route('registre.index'))->assertInertia(fn ($page) => $page->has('lignes', 1));
    });

    it('remet un rucher avec les ruches parties avec lui, mais pas une ruche supprimée avant', function () {
        $alice = apiculteur();
        $rucher = rucherDe($alice, 2);
        [$partieAvant, $partieAvec] = $rucher->ruches->all();

        $this->actingAs($alice);
        $this->delete(route('ruches.destroy', $partieAvant));
        $this->travel(2)->minutes();
        $this->delete(route('ruchers.destroy', $rucher));

        expect(Ruche::find($partieAvec->id))->toBeNull();

        $this->post(route('corbeille.restore', ['rucher', $rucher->id]))->assertRedirect();

        expect(Ruche::find($partieAvec->id))->not->toBeNull();
        expect(Ruche::find($partieAvant->id))->toBeNull();
    });

    it('ne montre ni ne remet rien d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $visite = visitePour(rucherDe($alice)->ruches->first(), $alice);
        $visite->delete();

        $this->actingAs($bob)->get(route('corbeille.index'))->assertInertia(fn ($page) => $page->has('items', 0));
        $this->post(route('corbeille.restore', ['visite', $visite->id]))->assertNotFound();
        $this->post(route('corbeille.restore', ['users', $alice->id]))->assertNotFound();
    });

    it('supprime pour de bon après le délai, avec la note vocale', function () {
        Storage::fake('local');
        Storage::disk('local')->put('notes-audio/vieille', 'son');
        $alice = apiculteur();
        $visite = visitePour(rucherDe($alice)->ruches->first(), $alice);
        $visite->forceFill(['note_audio_path' => 'notes-audio/vieille'])->save();
        $visite->delete();

        $this->travel(31)->days();
        $this->artisan('beenote:vider-corbeille')->assertSuccessful();

        expect(Visite::withTrashed()->find($visite->id))->toBeNull();
        Storage::disk('local')->assertMissing('notes-audio/vieille');
    });
});

describe('registre et export', function () {
    it('neutralise les formules dans le fichier pour tableur', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $ruche->update(['nom' => '=HYPERLINK("http://pirate")']);

        $this->actingAs($alice)->post(route('noter.store', 'traitement'), [
            'ruche_ids' => [$ruche->id],
            'date_traitement' => now()->toDateString(),
            'type_traitement' => 'varroa',
            'produit_utilise' => '@SUM(1+1)',
        ]);

        $csv = $this->get(route('registre.export', ['format' => 'csv', 'depuis' => '2020-01-01', 'jusqu' => now()->toDateString()]))
            ->assertOk()
            ->streamedContent();

        expect($csv)->toContain('\'=HYPERLINK')->toContain('\'@SUM');
    });

    it('génère un PDF et échappe le contenu', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();
        $ruche->update(['nom' => '<script>alert(1)</script>']);
        visitePour($ruche, $alice);

        $vue = view('pdf.registre', [
            'lignes' => \App\Services\RegistreService::pourEquipe($alice->current_team_id),
            'types' => ['visite' => 'Visite'],
            'depuis' => '2020-01-01',
            'jusqu' => '2030-01-01',
            'proprietaire' => $alice->name,
        ])->render();
        expect($vue)->not->toContain('<script>alert')->toContain('&lt;script&gt;');

        $reponse = $this->actingAs($alice)->get(route('registre.export', ['format' => 'pdf', 'depuis' => '2020-01-01', 'jusqu' => now()->toDateString()]));
        $reponse->assertOk();
        expect($reponse->headers->get('Content-Type'))->toContain('pdf');
    });

    it('ne laisse pas exporter le rucher d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $rucher = rucherDe($alice);

        $this->actingAs($bob)->get(route('registre.export', ['format' => 'csv', 'depuis' => '2020-01-01', 'jusqu' => '2030-01-01', 'rucher_id' => $rucher->id]))->assertNotFound();
    });
});

describe('tâches', function () {
    it('ajoute une tâche à une ruche, et la coche', function () {
        $alice = apiculteur();
        $ruche = rucherDe($alice)->ruches->first();

        $this->actingAs($alice)->post(route('taches.store'), [
            'titre' => 'Contrôler la reine',
            'date_prevue' => now()->addWeek()->toDateString(),
            'ruche_id' => $ruche->id,
        ])->assertRedirect();

        $tache = Tache::first();
        $this->patch(route('taches.toggle', $tache))->assertRedirect();

        expect($tache->fresh()->statut)->toBe('terminee');
    });

    it('refuse une tâche sur la ruche ou le rucher d\'une autre équipe', function () {
        $alice = apiculteur();
        $bob = apiculteur();
        $rucher = rucherDe($alice);

        $this->actingAs($bob);
        $this->post(route('taches.store'), ['titre' => 'X', 'date_prevue' => '2030-01-01', 'ruche_id' => $rucher->ruches->first()->id])->assertNotFound();
        $this->post(route('taches.store'), ['titre' => 'X', 'date_prevue' => '2030-01-01', 'rucher_id' => $rucher->id])->assertNotFound();

        $tache = tachePour($alice, $rucher->ruches->first());
        $this->patch(route('taches.toggle', $tache))->assertNotFound();
    });
});

describe('sécurité générale', function () {
    it('envoie les en-têtes de sécurité', function () {
        $reponse = $this->get('/');

        $reponse->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $reponse->headers->get('Content-Security-Policy');
        expect($csp)->toContain("object-src 'none'")->toContain("frame-ancestors 'none'")->toContain('nonce-')->not->toContain("script-src 'self' 'unsafe-inline'");
    });

    it('ne permet l\'évaluation de code que sur les écrans de la voix', function () {
        $alice = apiculteur();
        $this->actingAs($alice);

        expect($this->get(route('dashboard'))->headers->get('Content-Security-Policy'))->not->toContain("'unsafe-eval'");
        expect($this->get(route('registre.index'))->headers->get('Content-Security-Policy'))->not->toContain("'unsafe-eval'");
        expect($this->get(route('terrain'))->headers->get('Content-Security-Policy'))->toContain("'unsafe-eval'");
    });

    it('force un vrai chargement pour arriver sur un écran vocal', function () {
        $alice = apiculteur();

        $this->actingAs($alice)->withHeaders(['X-Inertia' => 'true'])->get(route('terrain'))->assertStatus(409);
    });

    it('limite le débit de la synchronisation', function () {
        $alice = apiculteur();
        $this->actingAs($alice);

        for ($i = 0; $i < 20; $i++) {
            $this->postJson(route('terrain.sync'), ['visites' => []])->assertOk();
        }

        $this->postJson(route('terrain.sync'), ['visites' => []])->assertTooManyRequests();
    });

    it('limite les tentatives de connexion', function () {
        $alice = apiculteur();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $alice->email, 'password' => 'faux']);
        }

        $this->post('/login', ['email' => $alice->email, 'password' => 'faux'])->assertStatus(429);
    });

    it('refuse un lot de synchronisation trop gros', function () {
        $alice = apiculteur();

        $this->actingAs($alice)->postJson(route('terrain.sync'), ['visites' => array_fill(0, 101, [])])->assertUnprocessable();
    });

    it('ne révèle pas de QR code à la création d\'une ruche par le client', function () {
        $alice = apiculteur();
        $rucher = rucherDe($alice, 0);

        $this->actingAs($alice)->post(route('ruches.store', $rucher), ['nom' => 'Ruche', 'qr_token' => 'choisi']);

        expect($rucher->ruches()->first()->qr_token)->not->toBe('choisi')->toHaveLength(32);
    });
});

function tachePour(User $user, Ruche $ruche): Tache
{
    $tache = new Tache(['titre' => 'À faire', 'type' => 'autre', 'date_prevue' => now()->addDay()->toDateString()]);
    $tache->forceFill(['user_id' => $user->id, 'team_id' => $user->current_team_id, 'ruche_id' => $ruche->id, 'rucher_id' => $ruche->rucher_id])->save();

    return $tache;
}
