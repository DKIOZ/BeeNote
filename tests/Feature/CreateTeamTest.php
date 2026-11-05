<?php

use App\Models\User;

test('teams cannot be created while each account keeps its personal team', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $this->post('/teams', ['name' => 'Test Team'])->assertForbidden();

    expect($user->fresh()->ownedTeams)->toHaveCount(1);
});
