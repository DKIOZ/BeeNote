<?php

use App\Models\User;

test('team member roles cannot be updated while sharing is hidden', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $user->currentTeam->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'admin']
    );

    $this->put('/teams/'.$user->currentTeam->id.'/members/'.$otherUser->id, [
        'role' => 'editor',
    ])->assertForbidden();

    expect($otherUser->fresh()->hasTeamRole($user->currentTeam->fresh(), 'admin'))->toBeTrue();
});
