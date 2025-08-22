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
        return $rucher->team_id === $user->current_team_id;
    }

    public function update(User $user, Rucher $rucher): bool
    {
        return $rucher->team_id === $user->current_team_id;
    }

    public function delete(User $user, Rucher $rucher): bool
    {
        return $rucher->team_id === $user->current_team_id;
    }
}
