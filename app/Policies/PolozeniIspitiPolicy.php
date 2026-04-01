<?php

namespace App\Policies;

use App\Models\PolozeniIspiti;
use App\Models\User;

class PolozeniIspitiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('professor');
    }

    public function view(User $user, PolozeniIspiti $polozeniIspit): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('professor')) {
            $profesor = $user->profesor;
            if ($profesor === null) {
                return false;
            }
            $zapisnik = $polozeniIspit->zapisnik;

            return $zapisnik !== null && $zapisnik->profesor_id === $profesor->id;
        }

        if ($user->hasRole('student')) {
            $kandidat = $user->kandidat;

            return $kandidat !== null && $polozeniIspit->kandidat_id === $kandidat->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('professor');
    }

    public function update(User $user, PolozeniIspiti $polozeniIspit): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('professor')) {
            $profesor = $user->profesor;
            if ($profesor === null) {
                return false;
            }
            $zapisnik = $polozeniIspit->zapisnik;

            return $zapisnik !== null && $zapisnik->profesor_id === $profesor->id;
        }

        return false;
    }

    public function delete(User $user, PolozeniIspiti $polozeniIspit): bool
    {
        return $user->hasRole('admin');
    }
}
