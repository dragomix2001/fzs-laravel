<?php

namespace App\Policies;

use App\Models\PrijavaIspita;
use App\Models\User;

class PrijavaIspitaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('professor');
    }

    public function view(User $user, PrijavaIspita $prijava): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('professor')) {
            $profesor = $user->profesor;

            return $profesor !== null && $prijava->profesor_id === $profesor->id;
        }

        if ($user->hasRole('student')) {
            $kandidat = $user->kandidat;

            return $kandidat !== null && $prijava->kandidat_id === $kandidat->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('student');
    }

    public function update(User $user, PrijavaIspita $prijava): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('student')) {
            $kandidat = $user->kandidat;

            return $kandidat !== null && $prijava->kandidat_id === $kandidat->id;
        }

        return false;
    }

    public function delete(User $user, PrijavaIspita $prijava): bool
    {
        return $user->hasRole('admin');
    }
}
