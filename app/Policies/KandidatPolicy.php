<?php

namespace App\Policies;

use App\Models\Kandidat;
use App\Models\User;

class KandidatPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Kandidat $kandidat): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('secretary');
    }

    public function update(User $user, Kandidat $kandidat): bool
    {
        return $user->hasRole('admin') || $user->hasRole('secretary');
    }

    public function delete(User $user, Kandidat $kandidat): bool
    {
        return $user->hasRole('admin');
    }
}
