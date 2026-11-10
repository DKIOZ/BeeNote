<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('académie', function () {
    it('ouvre l\'accueil et une fiche à un apiculteur connecté', function () {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)->get(route('academie.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Academie/Index'));

        $this->actingAs($user)->get(route('academie.show', 'essaimage'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Academie/Fiche')->where('id', 'essaimage'));
    });

    it('exige une connexion', function () {
        $this->get(route('academie.index'))->assertRedirect(route('login'));
        $this->get('/academie/essaimage')->assertRedirect(route('login'));
    });

    it('refuse un identifiant de fiche qui n\'a pas la bonne forme', function () {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)->get('/academie/Essaimage')->assertNotFound();
        $this->actingAs($user)->get('/academie/'.str_repeat('a', 61))->assertNotFound();
    });
});
