<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Rucher;

class RucherPolicy
{
    public function create(User $user): bool
    {
        // Un user peut créer s'il a une team
        return $user->current_team_id !== null;
    }

    public function viewAny(User $user): bool
    {
        // Un user peut voir la liste s'il a une team
        return $user->current_team_id !== null;
    }

    public function view(User $user, Rucher $rucher): bool
    {
        return $this->isInTeam($user, $rucher);
    }

    public function update(User $user, Rucher $rucher): bool
    {
        return $this->isInTeam($user, $rucher)
            && $user->hasTeamPermission($rucher->team, 'update');
    }

    public function delete(User $user, Rucher $rucher): bool
    {
        return $this->isInTeam($user, $rucher)
            && $user->hasTeamPermission($rucher->team, 'delete');
    }

    private function isInTeam(User $user, Rucher $rucher): bool
    {
        return $rucher->team_id === $user->current_team_id
            && $user->belongsToTeam($rucher->team);
    }
}
