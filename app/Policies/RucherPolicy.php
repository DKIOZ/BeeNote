<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Rucher;
use Illuminate\Auth\Access\Response;

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

    // Un rucher d'une autre équipe répond « introuvable » : on ne révèle pas qu'il existe.
    public function view(User $user, Rucher $rucher): Response
    {
        return $this->isInTeam($user, $rucher) ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, Rucher $rucher): Response
    {
        if (! $this->isInTeam($user, $rucher)) {
            return Response::denyAsNotFound();
        }

        return $user->hasTeamPermission($rucher->team, 'update') ? Response::allow() : Response::deny();
    }

    public function delete(User $user, Rucher $rucher): Response
    {
        if (! $this->isInTeam($user, $rucher)) {
            return Response::denyAsNotFound();
        }

        return $user->hasTeamPermission($rucher->team, 'delete') ? Response::allow() : Response::deny();
    }

    private function isInTeam(User $user, Rucher $rucher): bool
    {
        return $rucher->team_id === $user->current_team_id
            && $user->belongsToTeam($rucher->team);
    }
}
